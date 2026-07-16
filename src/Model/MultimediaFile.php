<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\ValueObject\RawSubstructure;

/**
 * A typed multimedia file reference (the `FILE` substructure of an `OBJE` record).
 *
 * References an external multimedia file by its path or URL — carried as the line value —
 * together with its {@see MediaFormat} and an optional descriptive title. The reference is
 * nullable so that a malformed, reference-less FILE does not drop the whole file list of an
 * otherwise valid record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final readonly class MultimediaFile
{
    /**
     * @param string|null           $value   The file reference — a path or URL (the FILE line value), or
     *                                       NULL when the FILE line carries no reference.
     * @param MediaFormat|null      $form    The file format (FORM), or NULL when absent.
     * @param string|null           $titl    The descriptive title (TITL), or NULL when absent.
     * @param list<RawSubstructure> $unknown Substructures the typed model did not consume (extension and out-of-schema tags), preserved verbatim.
     */
    public function __construct(
        public ?string $value = null,
        public ?MediaFormat $form = null,
        public ?string $titl = null,
        public array $unknown = [],
    ) {
    }
}
