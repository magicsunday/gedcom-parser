<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Schema;

use MagicSunday\Gedcom\Exception\InvalidCardinalityException;

use function preg_match;
use function trim;

/**
 * The occurrence constraint of a GEDCOM substructure, parsed from a registry cardinality token.
 *
 * The GEDCOM registries express how often a substructure may appear under its superstructure as
 * a `{minimum:maximum}` token, where the maximum is either a bound or the literal `M` for
 * "many" (unbounded) — for example `{0:1}` (optional, at most one) or `{1:M}` (required, one or
 * more).
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class Cardinality
{
    /**
     * @param int      $minimum The minimum number of occurrences
     * @param int|null $maximum The maximum number of occurrences, or NULL when unbounded (`M`)
     */
    public function __construct(
        public int $minimum,
        public ?int $maximum,
    ) {
    }

    /**
     * Parses a `{minimum:maximum}` registry cardinality token.
     *
     * @param string $token The raw cardinality token, e.g. `{0:1}` or `{1:M}`
     *
     * @throws InvalidCardinalityException When the token is not of the expected form
     */
    public static function fromToken(string $token): self
    {
        $matches = [];

        if (preg_match('/^\{(\d+):(\d+|M)\}$/', trim($token), $matches) !== 1) {
            throw new InvalidCardinalityException('Invalid GEDCOM cardinality token <' . $token . '>.');
        }

        return new self(
            (int) $matches[1],
            $matches[2] === 'M' ? null : (int) $matches[2],
        );
    }

    /**
     * Returns whether at least one occurrence is required.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->minimum >= 1;
    }

    /**
     * Returns whether more than one occurrence is allowed (an unbounded or greater-than-one
     * maximum), i.e. the substructure maps to a collection rather than a single value.
     *
     * @return bool
     */
    public function isCollection(): bool
    {
        return ($this->maximum === null) || ($this->maximum > 1);
    }
}
