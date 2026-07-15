<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Exception;

use RuntimeException;

/**
 * Thrown when a GEDZIP (`.gdz`) archive does not contain the mandated `gedcom.ged` data entry that
 * carries the GEDCOM dataset.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class MissingGedcomEntryException extends RuntimeException implements ExceptionInterface
{
}
