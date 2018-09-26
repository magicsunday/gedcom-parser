<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Parser\Header;
use MagicSunday\Gedcom\Parser\Individual;
use MagicSunday\Gedcom\Parser\Submission;
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
            switch ($reader->tag()) {
                // Header
                case 'HEAD':
                    $headerParser = new Header($reader, $this->logger);
                    $gedcom->setHeader($headerParser->parse());
                    break;

                // Submission record
                case 'SUBN':
                    $submissionParser = new Submission($reader, $this->logger);
                    $gedcom->setSubmission($submissionParser->parse());
                    break;

                // Records
                case 'INDI':
                    $individualParser = new Individual($reader, $this->logger);
                    $individual       = $individualParser->parse();

                    $gedcom->addIndividual($individual);
                    break;
            }
        }

var_dump($gedcom);
exit;

        return $gedcom;
    }
}
