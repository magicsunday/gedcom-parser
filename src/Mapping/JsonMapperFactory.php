<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Mapping;

use MagicSunday\Gedcom\Exception\MappingException;
use MagicSunday\Gedcom\Model\Note;
use MagicSunday\Gedcom\Model\NoteTranslation;
use MagicSunday\Gedcom\Parse\GedcomNode;
use MagicSunday\Gedcom\ValueObject\AgeValue;
use MagicSunday\Gedcom\ValueObject\DateValue;
use MagicSunday\Gedcom\ValueObject\MapCoordinates;
use MagicSunday\Gedcom\ValueObject\PlaceValue;
use MagicSunday\JsonMapper;
use MagicSunday\JsonMapper\Converter\CamelCasePropertyNameConverter;
use MagicSunday\JsonMapper\Value\ClosureTypeHandler;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

use function array_key_exists;
use function get_debug_type;
use function is_array;
use function is_string;
use function sprintf;
use function trim;

/**
 * Builds a {@see JsonMapper} configured for the typed GEDCOM model.
 *
 * The mapper is wired with reflection- and PHPDoc-based type extraction so it can read the typed
 * model's constructor parameters and collection annotations, and constructs the immutable
 * `final readonly` records through their constructors (the constructor-hydration support added in
 * jsonmapper 3.1).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class JsonMapperFactory
{
    /**
     * The place-structure tag whose header instance declares the default hierarchy FORM.
     */
    private const string TAG_PLAC = 'PLAC';

    /**
     * The place FORM tag naming the jurisdiction hierarchy.
     */
    private const string TAG_FORM = 'FORM';

    /**
     * Private constructor; use {@see create()}.
     */
    private function __construct()
    {
    }

    /**
     * Builds a mapper whose place handler inherits the header-declared default hierarchy FORM
     * (HEAD.PLAC.FORM) for every place that carries none of its own — the common GEDCOM 5.5.1 case
     * where the jurisdiction format is declared once in the header.
     *
     * @param GedcomNode|null $header The parsed HEAD record, or NULL when the document has none.
     *
     * @return JsonMapper The configured mapper.
     */
    public static function fromHeader(?GedcomNode $header): JsonMapper
    {
        return self::create($header?->firstChild(self::TAG_PLAC)?->firstChild(self::TAG_FORM)?->value);
    }

    /**
     * Creates a mapper configured for the typed GEDCOM model.
     *
     * @param string|null $defaultPlaceForm The header-declared default hierarchy FORM (HEAD.PLAC.FORM)
     *                                      threaded into the place handler, or NULL.
     *
     * @return JsonMapper The configured mapper.
     */
    public static function create(?string $defaultPlaceForm = null): JsonMapper
    {
        $mapper = new JsonMapper(
            new PropertyInfoExtractor([new ReflectionExtractor()], [new PhpDocExtractor()]),
            PropertyAccess::createPropertyAccessor(),
            new CamelCasePropertyNameConverter(),
        );

        // A GEDCOM value-object leaf is parsed from its raw payload through its own grammar rather
        // than mapped field by field, so each is registered as a custom type. A leaf that also
        // declares substructures is shaped as an array carrying its own line value under the
        // `value` key (a GEDCOM 7.0 DATE/AGE carries PHRASE/TIME; PLAC carries FORM), so each
        // handler resolves the leaf value from either a bare string or that shaped array.
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                DateValue::class,
                static fn (mixed $value): DateValue => DateValue::fromGedcom(
                    self::leafValue($value, 'DATE'),
                    self::phraseOf($value),
                ),
            ),
        );
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                AgeValue::class,
                static fn (mixed $value): AgeValue => AgeValue::fromGedcom(
                    self::leafValue($value, 'AGE'),
                    self::phraseOf($value),
                ),
            ),
        );
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                PlaceValue::class,
                static fn (mixed $value): PlaceValue => self::placeFromShaped($value, $defaultPlaceForm),
            ),
        );

        // A registered handler intercepts every Note conversion — including each element of a
        // `list<Note>` — so a single handler covers a record's inline change-notes. The resolution
        // of the bare-string and shaped-array forms itself lives in noteFromShaped().
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                Note::class,
                static fn (mixed $value): Note => self::noteFromShaped($value),
            ),
        );

        return $mapper;
    }

    /**
     * Resolves the string leaf payload of a value-object node. A leaf that also declares
     * substructures is shaped as an array carrying its own line value under the `value` key (a
     * GEDCOM 7.0 DATE/AGE carries PHRASE/TIME), so the value is taken from that key; a value-less
     * leaf resolves to the empty string. A non-string, non-array payload is a mis-shape and fails
     * loud.
     *
     * @param mixed  $value The shaped payload (a bare string, or a shaped array)
     * @param string $label The tag name for the error message.
     *
     * @return string
     *
     * @throws MappingException When the value is neither a string nor a shaped array.
     */
    private static function leafValue(mixed $value, string $label): string
    {
        // A value-less substructure (e.g. an empty FORM line) is shaped as a null leaf; it resolves
        // as absent (the empty string), the same as a shaped leaf with no `value` key.
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            // A value-less leaf (e.g. a 7.0 DATE carrying only a PHRASE) has no `value` key and
            // resolves to the empty string; a `value` key that is present but not a string is a
            // genuine mis-shape and fails loud rather than being silently coerced away.
            if (!array_key_exists('value', $value)) {
                return '';
            }

            $inner = $value['value'];

            if (!is_string($inner)) {
                throw new MappingException(sprintf('Expected a string %s value, got %s.', $label, get_debug_type($inner)));
            }

            return $inner;
        }

        throw new MappingException(sprintf('Expected a string or shaped %s payload, got %s.', $label, get_debug_type($value)));
    }

    /**
     * Resolves the GEDCOM 7.0 PHRASE substructure of a shaped leaf. A DATE or AGE that carries a
     * PHRASE is shaped as an array with a `phrase` key; the phrase text is resolved through the same
     * leaf helper, and an absent or empty phrase yields NULL so the value object is not handed a
     * meaningless empty string.
     *
     * @param mixed $value The shaped leaf payload (an array when it carries substructures)
     *
     * @return string|null The PHRASE text, or NULL when the leaf carries no phrase.
     *
     * @throws MappingException When the PHRASE leaf is itself mis-shaped.
     */
    private static function phraseOf(mixed $value): ?string
    {
        if (!is_array($value) || !array_key_exists('phrase', $value)) {
            return null;
        }

        $phrase = self::leafValue($value['phrase'], 'PHRASE');

        return $phrase === '' ? null : $phrase;
    }

    /**
     * Builds a PlaceValue from a shaped PLAC node. PLAC carries both a place-name value and a FORM
     * substructure, so its shaped node is an array; the place name is resolved as a leaf value and
     * the FORM hierarchy passed through so the value object can map the jurisdiction labels.
     *
     * @param mixed       $value       The shaped PLAC payload (an array, or a plain string when form-less).
     * @param string|null $defaultForm The header-declared default hierarchy FORM (HEAD.PLAC.FORM), or NULL.
     *
     * @return PlaceValue
     *
     * @throws MappingException When the value is neither a string nor a shaped array.
     */
    private static function placeFromShaped(mixed $value, ?string $defaultForm): PlaceValue
    {
        $name        = self::leafValue($value, 'PLAC');
        $form        = null;
        $coordinates = null;

        if (is_array($value)) {
            if (array_key_exists('form', $value)) {
                // Resolve the FORM through the same leaf helper as the place name, so a shaped FORM
                // is handled and a mis-shaped one fails loud consistently rather than coerced away.
                // An explicitly empty local FORM counts as absent (as PlaceValue::fromGedcom treats
                // it), so the place still inherits the header default rather than suppressing it.
                $localForm = self::leafValue($value['form'], 'FORM');
                $form      = trim($localForm) === '' ? null : $localForm;
            }

            if (array_key_exists('map', $value)) {
                $coordinates = self::coordinatesFromShaped($value['map']);
            }
        }

        // A place carrying no FORM of its own inherits the header default: in GEDCOM 5.5.1 the
        // hierarchy is normally declared once as HEAD.PLAC.FORM, so a per-place FORM is the exception.
        return PlaceValue::fromGedcom($name, $form ?? $defaultForm, $coordinates);
    }

    /**
     * Builds MapCoordinates from a shaped MAP node. MAP carries no value of its own but declares the
     * required LATI/LONG leaves, so it is shaped as an array; each axis is resolved as a leaf value
     * and handed to the value object, which returns NULL when either axis is malformed or absent.
     *
     * @param mixed $map The shaped MAP payload (an array carrying the LATI/LONG leaves)
     *
     * @return MapCoordinates|null The parsed coordinates, or NULL when the MAP is incomplete or
     *                             malformed
     *
     * @throws MappingException When a LATI/LONG leaf is itself mis-shaped.
     */
    private static function coordinatesFromShaped(mixed $map): ?MapCoordinates
    {
        if (!is_array($map)) {
            return null;
        }

        if (!array_key_exists('lati', $map) || !array_key_exists('long', $map)) {
            return null;
        }

        return MapCoordinates::fromGedcom(
            self::leafValue($map['lati'], 'LATI'),
            self::leafValue($map['long'], 'LONG'),
        );
    }

    /**
     * Builds an inline {@see Note} from its shaped payload. A GEDCOM 5.5.1 note — and a shared-note
     * pointer — is a bare string carried directly as the note value; a GEDCOM 7.0 note is shaped as
     * an array carrying its own text under the `value` key alongside its LANG/MIME/TRAN
     * substructures. Only the modelled keys are read, so an unmodelled child (a `SOURCE_CITATION`,
     * which is not yet mapped) is ignored rather than dropping the whole note.
     *
     * @param mixed $value The shaped payload (a bare string, a shaped array, or NULL).
     *
     * @return Note
     */
    private static function noteFromShaped(mixed $value): Note
    {
        if (is_string($value)) {
            return new Note($value);
        }

        // A value-less note (or any non-array payload) carries no text.
        if (!is_array($value)) {
            return new Note();
        }

        $translations = [];
        $tran         = $value['tran'] ?? [];

        if (is_array($tran)) {
            foreach ($tran as $translation) {
                if (!is_array($translation)) {
                    continue;
                }

                $translations[] = new NoteTranslation(
                    self::nullableString($translation['value'] ?? null),
                    self::nullableString($translation['lang'] ?? null),
                    self::nullableString($translation['mime'] ?? null),
                );
            }
        }

        return new Note(
            self::nullableString($value['value'] ?? null),
            self::nullableString($value['lang'] ?? null),
            self::nullableString($value['mime'] ?? null),
            $translations,
        );
    }

    /**
     * Narrows a shaped payload value to a string, treating any non-string (an absent key, a nested
     * array, NULL) as absent so a mis-shaped leaf is dropped rather than coerced.
     *
     * @param mixed $value The shaped value to narrow.
     *
     * @return string|null The value when it is a string, or NULL otherwise.
     */
    private static function nullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
