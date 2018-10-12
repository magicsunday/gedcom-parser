<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Model\Gedcom;
use MagicSunday\Gedcom\Parser\Custom;
use MagicSunday\Gedcom\Parser\IndividualRecord;
use MagicSunday\Gedcom\Parser\FamilyRecord;
use MagicSunday\Gedcom\Parser\HeaderRecord;
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
     * @param null|LoggerInterface $logger
     */
    public function __construct(
        string $fileName,
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();

        parent::__construct(new Reader($fileName), $this->logger);
    }

    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            Gedcom::TAG_HEAD => HeaderRecord::class,
            Gedcom::TAG_FAM  => FamilyRecord::class,
            Gedcom::TAG_INDI => IndividualRecord::class,
            Gedcom::TAG_OBJE => MultimediaRecord::class,
            Gedcom::TAG_NOTE => NoteRecord::class,
            Gedcom::TAG_REPO => RepositoryRecord::class,
            Gedcom::TAG_SOUR => SourceRecord::class,
            Gedcom::TAG_SUBM => SubmitterRecord::class,
            Gedcom::TAG_SUBN => SubmissionRecord::class,
            Gedcom::TAG_TRLR => Custom::class,
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
