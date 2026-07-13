<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\PersonalNameStructureInterface;
use MagicSunday\Gedcom\Model\IndividualRecord\PersonalNameStructure\PersonalNamePieces;

use function preg_match;
use function preg_replace;
use function str_replace;
use function trim;

/**
 * The personal name structure model.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class PersonalNameStructure extends PersonalNamePieces implements PersonalNameStructureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->getValue(self::TAG_NAME_PERSONAL);
    }

    /**
     * {@inheritDoc}
     *
     * Falls back to the given name derived from the NAME slash convention when no explicit
     * GIVN sub-tag is present.
     */
    public function getGivenName(): ?string
    {
        return $this->getValue(self::TAG_GIVN) ?? $this->nameParts()['given'];
    }

    /**
     * {@inheritDoc}
     *
     * Falls back to the surname derived from the NAME slash convention when no explicit
     * SURN sub-tag is present.
     */
    public function getSurname(): ?string
    {
        return $this->getValue(self::TAG_SURN) ?? $this->nameParts()['surname'];
    }

    /**
     * {@inheritDoc}
     *
     * Falls back to the suffix derived from the NAME slash convention when no explicit
     * NSFX sub-tag is present.
     */
    public function getNameSuffix(): ?string
    {
        return $this->getValue(self::TAG_NSFX) ?? $this->nameParts()['suffix'];
    }

    /**
     * {@inheritDoc}
     */
    public function getDisplayName(): ?string
    {
        $name = $this->getName();

        if ($name === null) {
            return null;
        }

        return trim((string) preg_replace('/\s+/', ' ', str_replace('/', ' ', $name)));
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): ?string
    {
        return $this->getValue(self::TAG_TYPE);
    }

    /**
     * {@inheritDoc}
     */
    public function getPhoneticVariation(): array
    {
        return $this->getArrayValue(self::TAG_FONE);
    }

    /**
     * {@inheritDoc}
     */
    public function getRomanizedVariation(): array
    {
        return $this->getArrayValue(self::TAG_ROMN);
    }

    /**
     * Splits the raw NAME value on the GEDCOM surname-slash convention
     * (`<given> /<surname>/ <suffix>`) into its parts, tolerating a missing trailing slash.
     *
     * @return array{given: string|null, surname: string|null, suffix: string|null}
     */
    private function nameParts(): array
    {
        $name = $this->getName();

        if ($name === null) {
            return ['given' => null, 'surname' => null, 'suffix' => null];
        }

        if (preg_match('#^(.*?)/([^/]*)/?(.*)$#', $name, $matches) === 1) {
            return [
                'given'   => $this->trimToNull($matches[1]),
                'surname' => $this->trimToNull($matches[2]),
                'suffix'  => $this->trimToNull($matches[3]),
            ];
        }

        // No surname slashes present: the whole value is the given name.
        return ['given' => $this->trimToNull($name), 'surname' => null, 'suffix' => null];
    }

    /**
     * Trims a value and normalises the result to NULL when it is empty.
     *
     * @param string $value The value to normalise.
     *
     * @return string|null
     */
    private function trimToNull(string $value): ?string
    {
        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
