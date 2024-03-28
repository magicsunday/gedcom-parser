<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Interfaces\GedcomInterface;
use MagicSunday\Gedcom\Model\Gedcom;
use MagicSunday\Gedcom\Parser\Custom;
use MagicSunday\Gedcom\Parser\FamilyRecord;
use MagicSunday\Gedcom\Parser\HeaderRecord;
use MagicSunday\Gedcom\Parser\IndividualRecord;
use MagicSunday\Gedcom\Parser\MultimediaRecord;
use MagicSunday\Gedcom\Parser\NoteRecord;
use MagicSunday\Gedcom\Parser\RepositoryRecord;
use MagicSunday\Gedcom\Parser\SourceRecord;
use MagicSunday\Gedcom\Parser\SubmissionRecord;
use MagicSunday\Gedcom\Parser\SubmitterRecord;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * A gedcom 5.5.1 parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Parser extends AbstractParser
{
    /**
     * @param string               $fileName
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        string $fileName,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();

        parent::__construct(new Reader($fileName), $this->logger);
    }

    /**
     * {@inheritDoc}
     */
    protected function getClassMap(): array
    {
        return [
            GedcomInterface::TAG_HEAD => HeaderRecord::class,
            GedcomInterface::TAG_FAM  => FamilyRecord::class,
            GedcomInterface::TAG_INDI => IndividualRecord::class,
            GedcomInterface::TAG_OBJE => MultimediaRecord::class,
            GedcomInterface::TAG_NOTE => NoteRecord::class,
            GedcomInterface::TAG_REPO => RepositoryRecord::class,
            GedcomInterface::TAG_SOUR => SourceRecord::class,
            GedcomInterface::TAG_SUBM => SubmitterRecord::class,
            GedcomInterface::TAG_SUBN => SubmissionRecord::class,
            GedcomInterface::TAG_TRLR => Custom::class,
        ];
    }

    /**
     * Parses a GEDCOM file.
     *
     * @return Gedcom
     */
    public function parse(): Gedcom
    {
        $gedcom = new Gedcom();

        $this->process($gedcom);

        return $gedcom;
    }
}
