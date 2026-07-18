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
use MagicSunday\Gedcom\ValueObject\RawSubstructure;
use MagicSunday\JsonMapper;
use MagicSunday\JsonMapper\Converter\CamelCasePropertyNameConverter;
use MagicSunday\JsonMapper\Value\ClosureTypeHandler;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

use function array_key_exists;
use function array_map;
use function get_debug_type;
use function in_array;
use function is_array;
use function is_int;
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
     * The value-object leaf types whose handler parses substructures the type does NOT expose as
     * same-named constructor properties (a `DATE`'s `PHRASE`, a `PLAC`'s `MAP`/`LATI`/`LONG`). Which of
     * a leaf's children its grammar actually reads is named in {@see HANDLER_CONSUMED_TAGS}; the rest
     * are preserved on that leaf's own `$unknown` — a GEDCOM 7.0 `DATE`'s `TIME` among them.
     * The object mapper must not shape these class-aware, or a class-aware pass would divert that
     * handler input as an "unmodelled" tag and lose it. A modelled container that carries its own
     * `$unknown` list and whose handler reads it (such as {@see Note}) is deliberately NOT listed:
     * it IS shaped class-aware so its unmodelled substructures are preserved.
     *
     * @var list<class-string>
     */
    public const array LEAF_VALUE_TYPES = [
        DateValue::class,
        AgeValue::class,
        PlaceValue::class,
        RawSubstructure::class,
    ];

    /**
     * Every type hydrated from a bare-string (or shaped-array) payload by a registered handler,
     * rather than field-by-field from its constructor — the {@see LEAF_VALUE_TYPES} plus {@see Note}
     * (which is handler-backed yet deliberately shaped class-aware). The object mapper must NOT force
     * one of these into an object shape for a structureless tag: its handler already resolves the
     * bare payload, so shaping it as `{xref}`/`{value}` would strip the value the handler expects. A
     * constructor-hydrated model (such as an {@see \MagicSunday\Gedcom\Model\Substructure\Common\AliasLink})
     * is NOT listed — it cannot accept a bare scalar, so a structureless pointer/value tag targeting
     * it must be shaped.
     *
     * @var list<class-string>
     */
    public const array HANDLER_BACKED_TYPES = [
        ...self::LEAF_VALUE_TYPES,
        Note::class,
    ];

    /**
     * The substructure tags each value-object leaf's grammar reads out of the shape it is given,
     * keyed by that leaf.
     *
     * Such a leaf is shaped without knowing its target class, so the mapper cannot derive from the
     * model which of its children the grammar will consume. Naming them here lets a child the
     * grammar does not read be preserved on the leaf's own `$unknown` instead of falling away with
     * the rest of the shape — a GEDCOM 7.0 place carries a language, translations and notes that the
     * place grammar has no use for, and they must survive all the same.
     *
     * This keys {@see LEAF_VALUE_TYPES} only. A handler-backed type that IS shaped class-aware — a
     * {@see Note} — derives its consumed tags from its own constructor and must not be listed here,
     * or the two would drift apart. {@see RawSubstructure} is deliberately absent: it is never the
     * target of a shaped child, only the carrier the preserved ones are rebuilt into.
     *
     * Adding a leaf to {@see LEAF_VALUE_TYPES} without an entry here would divert its grammar's own
     * input away from it, so `LeafSubstructurePreservationTest` pins the two lists against each
     * other.
     *
     * @var array<class-string, list<string>>
     */
    public const array HANDLER_CONSUMED_TAGS = [
        PlaceValue::class => ['form', 'map'],
        DateValue::class  => ['phrase', 'time'],
        AgeValue::class   => ['phrase'],
    ];

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
        // `value` key (a GEDCOM 7.0 DATE/AGE carries PHRASE; PLAC carries FORM), so each
        // handler resolves the leaf value from either a bare string or that shaped array.
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                DateValue::class,
                static fn (mixed $value): DateValue => DateValue::fromGedcom(
                    self::leafValue($value, 'DATE'),
                    self::phraseOf($value),
                    self::timeOf($value),
                    self::unknownFromShaped($value),
                ),
            ),
        );
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                AgeValue::class,
                static fn (mixed $value): AgeValue => AgeValue::fromGedcom(
                    self::leafValue($value, 'AGE'),
                    self::phraseOf($value),
                    self::unknownFromShaped($value),
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

        // A registered handler intercepts every RawSubstructure conversion — including each element
        // of a `list<RawSubstructure> $unknown` — so the mapper rebuilds a preserved, unconsumed
        // substructure subtree verbatim from the raw shape the object mapper emitted.
        $mapper->addTypeHandler(
            new ClosureTypeHandler(
                RawSubstructure::class,
                static fn (mixed $value): RawSubstructure => self::rawFromShaped($value),
            ),
        );

        return $mapper;
    }

    /**
     * Rebuilds a preserved {@see RawSubstructure} from the raw shape emitted by the object mapper: a
     * `tag`, an optional `value`/`xref`, a nested `children` list and the `level` the line was
     * written at. The handler owns the recursion so the whole preserved subtree is rebuilt in one
     * pass.
     *
     * @param mixed $value The raw shape (an array with `tag`/`value`/`xref`/`children`/`level`).
     *
     * @return RawSubstructure The rebuilt preserved substructure.
     */
    private static function rawFromShaped(mixed $value): RawSubstructure
    {
        if (!is_array($value)) {
            // The object mapper always emits a shaped array; a non-array here is a mis-shape and
            // preserves nothing rather than failing the whole parse.
            return new RawSubstructure('');
        }

        $tag   = $value['tag'] ?? null;
        $level = $value['level'] ?? null;

        return new RawSubstructure(
            is_string($tag) ? $tag : '',
            self::nullableString($value['value'] ?? null),
            self::nullableString($value['xref'] ?? null),
            self::rawListFromShaped($value['children'] ?? []),
            is_int($level) ? $level : null,
        );
    }

    /**
     * Rebuilds a `list<RawSubstructure>` from a shaped list, mapping each element through
     * {@see rawFromShaped()} and treating a non-array input as an empty list.
     *
     * @param mixed $list The shaped list of raw substructures.
     *
     * @return list<RawSubstructure> The rebuilt substructures, or an empty list.
     */
    private static function rawListFromShaped(mixed $list): array
    {
        if (!is_array($list)) {
            return [];
        }

        $result = [];

        foreach ($list as $raw) {
            $result[] = self::rawFromShaped($raw);
        }

        return $result;
    }

    /**
     * Rebuilds the `list<RawSubstructure>` preserved under a shaped payload's `unknown` key, used by
     * the closure-built {@see Note}/{@see NoteTranslation} which JsonMapper does not hydrate through
     * their constructors.
     *
     * @param mixed $value The shaped payload that may carry an `unknown` list.
     *
     * @return list<RawSubstructure> The preserved substructures, or an empty list when none.
     */
    private static function unknownFromShaped(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return self::rawListFromShaped($value['unknown'] ?? []);
    }

    /**
     * Resolves the string leaf payload of a value-object node. A leaf that also declares
     * substructures is shaped as an array carrying its own line value under the `value` key (a
     * GEDCOM 7.0 DATE/AGE carries PHRASE), so the value is taken from that key; a value-less
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
     * Resolves the wall-clock time a GEDCOM 7.0 date carries, from the shaped array its structure
     * produces. A bare payload carries none.
     *
     * @param mixed $value The shaped leaf, or its bare payload.
     *
     * @return string|null The TIME text, or NULL when the date carries none.
     */
    private static function timeOf(mixed $value): ?string
    {
        return is_array($value) ? self::nullableString($value['time'] ?? null) : null;
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
     * A MAP that could not be typed is preserved on the place's own `$unknown`. It is appended
     * there rather than kept in source order among the other diverted substructures: the shape does
     * not record where a child stood, so its position among its siblings is not recoverable.
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
        $unreadable  = null;

        if (is_array($value)) {
            if (array_key_exists('form', $value)) {
                // Resolve the FORM through the same leaf helper as the place name, so a shaped FORM
                // is handled and a mis-shaped one fails loud consistently rather than coerced away.
                // An explicitly empty local FORM counts as absent (as PlaceValue::fromGedcom treats
                // it), so the place still inherits the header default rather than suppressing it.
                $localForm = self::leafValue($value['form'], 'FORM');
                $form      = trim($localForm) === '' ? null : $localForm;
            }

            // A MAP always shapes to an array: it declares the axis substructures, so the shaping
            // recurses into it even when the line carries a payload where the axes belong.
            if (array_key_exists('map', $value) && is_array($value['map'])) {
                $coordinates = self::coordinatesFromShaped($value['map']);

                // The substructures a MAP carries are preserved on the coordinates it builds, so a
                // MAP that yields none — a missing or malformed axis, or a payload where the axes
                // belong — would take everything it carried with it. Divert it to the place whole
                // instead, the same contract any other unmodelled structure has. When the axes DO
                // parse, only a payload on the MAP line itself is left over, since the specification
                // gives MAP none; that alone is diverted, so the axes are not repeated beside the
                // position they built.
                $unreadable = $coordinates instanceof MapCoordinates
                    ? self::strayMapPayload($value['map'])
                    : self::unreadableCoordinates($value['map']);
            }
        }

        $unknown = self::unknownFromShaped($value);

        if ($unreadable instanceof RawSubstructure) {
            $unknown[] = $unreadable;
        }

        // A place carrying no FORM of its own inherits the header default: in GEDCOM 5.5.1 the
        // hierarchy is normally declared once as HEAD.PLAC.FORM, so a per-place FORM is the exception.
        return PlaceValue::fromGedcom($name, $form ?? $defaultForm, $coordinates, $unknown);
    }

    /**
     * Builds MapCoordinates from a shaped MAP node. MAP carries no value of its own but declares the
     * required LATI/LONG leaves, so it is shaped as an array; each axis is resolved as a leaf value
     * and handed to the value object, which returns NULL when either axis is malformed or absent.
     *
     * @param array<array-key, mixed> $map The shaped MAP payload (an array carrying the LATI/LONG leaves)
     *
     * @return MapCoordinates|null The parsed coordinates, or NULL when the MAP is incomplete or
     *                             malformed
     *
     * @throws MappingException When a LATI/LONG leaf is itself mis-shaped.
     */
    private static function coordinatesFromShaped(array $map): ?MapCoordinates
    {
        if (!array_key_exists('lati', $map) || !array_key_exists('long', $map)) {
            return null;
        }

        return MapCoordinates::fromGedcom(
            self::leafValue($map['lati'], 'LATI'),
            self::leafValue($map['long'], 'LONG'),
            self::unknownFromShaped($map),
        );
    }

    /**
     * Rebuilds a MAP that yielded no coordinates as a raw substructure, so what it carried survives
     * on the place instead of falling away with the position that could not be built (#188).
     *
     * The axes are put back whether they were absent, malformed or out of range: an axis the grammar
     * rejected is still what the file said, and on this path nothing else preserves it. An axis
     * rebuilt from its typed key comes first, in the canonical latitude-then-longitude order rather
     * than the order the file used — the shape does not record the position of a child, so the
     * source order cannot be recovered here. An axis that was already diverted as a carrier keeps
     * its place among the preserved substructures that follow.
     *
     * @param array<array-key, mixed> $map The shaped MAP payload.
     *
     * @return RawSubstructure The MAP as written.
     *
     * @throws MappingException When an axis leaf is itself mis-shaped.
     */
    private static function unreadableCoordinates(array $map): RawSubstructure
    {
        $level     = self::levelOf($map);
        $preserved = self::unknownFromShaped($map);
        $carried   = array_map(
            static fn (RawSubstructure $entry): string => $entry->tag . "\0" . ($entry->value ?? ''),
            $preserved
        );
        $children = [];

        foreach (['lati' => 'LATI', 'long' => 'LONG'] as $key => $tag) {
            if (!array_key_exists($key, $map)) {
                continue;
            }

            // A value-less axis carries no line value; the grammar helper resolves it to the empty
            // string, which is not what a raw substructure means by "no value".
            $axis  = self::leafValue($map[$key], $tag);
            $value = $axis === '' ? null : $axis;

            // An axis line bearing substructures of its own was already diverted whole by the
            // shaping, and that carrier repeats the axis value; rebuilding the axis from its typed
            // key as well would write the line twice. The match is on the value too, not the tag
            // alone: when the same axis appears more than once, the carrier belongs to a different
            // occurrence than the typed key holds, and skipping on the tag would delete that one.
            if (in_array($tag . "\0" . ($value ?? ''), $carried, true)) {
                continue;
            }

            // An axis sits exactly one level below the MAP it belongs to, which the grammar fixes
            // even though the shape does not record where the line stood among its siblings.
            $children[] = new RawSubstructure($tag, $value, null, [], $level === null ? null : $level + 1);
        }

        foreach ($preserved as $entry) {
            $children[] = $entry;
        }

        return new RawSubstructure(
            'MAP',
            self::nullableString($map['value'] ?? null),
            self::nullableString($map['xref'] ?? null),
            $children,
            $level,
        );
    }

    /**
     * Preserves a payload a MAP line carries of its own, for the case where the axes beneath it DO
     * build a position.
     *
     * The specification gives MAP no payload, so a value or a pointer on that line is malformed
     * input the coordinates have nowhere to hold. It would otherwise be the one part of a MAP that
     * survives when the axes fail and is dropped when they succeed.
     *
     * @param array<array-key, mixed> $map The shaped MAP payload.
     *
     * @return RawSubstructure|null The payload as a raw MAP, or NULL when the line carried none.
     */
    private static function strayMapPayload(array $map): ?RawSubstructure
    {
        $value = self::nullableString($map['value'] ?? null);
        $xref  = self::nullableString($map['xref'] ?? null);

        if (($value === null) && ($xref === null)) {
            return null;
        }

        // The axes were consumed into the position, so only the stray payload is left to preserve.
        return new RawSubstructure('MAP', $value, $xref, [], self::levelOf($map));
    }

    /**
     * Reads the level a shaped payload was read from.
     *
     * Only a shape built without a target class carries one — those are the payloads a value-object
     * handler consumes, and the only ones from which an entry is ever rebuilt.
     *
     * @param array<array-key, mixed> $shape The shaped payload.
     *
     * @return int|null The level of the line the shape was built from, or NULL when it carries none.
     */
    private static function levelOf(array $shape): ?int
    {
        $level = $shape['level'] ?? null;

        return is_int($level) ? $level : null;
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
                    self::unknownFromShaped($translation),
                );
            }
        }

        return new Note(
            // A GEDCOM 5.5.1 note is either inline text or a pointer to a shared note, and both
            // arrive in the same property. Fall back to the pointer when the note carries no line
            // value, mirroring the plain-payload path the mapper uses for a childless note.
            self::nullableString($value['value'] ?? $value['xref'] ?? null),
            self::nullableString($value['lang'] ?? null),
            self::nullableString($value['mime'] ?? null),
            $translations,
            self::nullableString($value['xref'] ?? null),
            self::unknownFromShaped($value),
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
