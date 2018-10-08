<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom;

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
class Parser
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param null|LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param string $fileName The name of the file to parse
     *
     * @return Gedcom
     */
    public function parse(string $fileName): Gedcom
    {
        $reader = new Reader($fileName);
        $gedcom = new Gedcom();

        while ($reader->read()) {
            if ($reader->level() !== 0) {
                continue;
            }

            switch ($reader->tag()) {
                // Header
                case 'HEAD':
                    $headerParser = new HeaderRecord($reader, $this->logger);
                    $gedcom->setHeader($headerParser->parse());

                    if ($gedcom->getHeader()->getGedcomInfo()
                        && ($gedcom->getHeader()->getGedcomInfo()->getVersion() !== '5.5.1')
                    ) {
                        // TODO Implement GEDCOM version check
//                        throw new RuntimeException('Wrong gedcom version. Must be 5.5.1');
                    }

                    // TODO Use correct GEDCOM char encoding for reading the file

                    break;

                // Family record
                case 'FAM':
                    $familyParser = new FamilyRecord($reader, $this->logger);
                    $gedcom->addFamily($familyParser->parse());
                    break;

//                // Individual record
//                case 'INDI':
//                    $individualParser = new Individual($reader, $this->logger);
//                    $gedcom->addIndividual($individualParser->parse());
//                    break;

                // Multimedia record
                case 'OBJE':
                    $mediaParser = new MultimediaRecord($reader, $this->logger);
                    $gedcom->addMedia($mediaParser->parse());
                    break;

                // Note record
                case 'NOTE':
                    $noteParser = new NoteRecord($reader, $this->logger);
                    $gedcom->addNote($noteParser->parse());
                    break;

                // Repository record
                case 'REPO':
                    $repoParser = new RepositoryRecord($reader, $this->logger);
                    $gedcom->addRepository($repoParser->parse());
                    break;

                // Source record
                case 'SOUR':
                    $sourceParser = new SourceRecord($reader, $this->logger);
                    $gedcom->addSource($sourceParser->parse());
                    break;

                // Submitter record
                case 'SUBM':
                    $submitterParser = new SubmitterRecord($reader, $this->logger);
                    $gedcom->setSubmitter($submitterParser->parse());
                    break;

                // Submission record
                case 'SUBN':
                    $submissionParser = new SubmissionRecord($reader, $this->logger);
                    $gedcom->setSubmission($submissionParser->parse());
                    break;
            }
        }

        return $gedcom;
    }
}
