<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types = 1);

namespace MagicSunday\Gedcom;

use MagicSunday\Gedcom\Parser\Header;
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
            // Header
            if ($reader->type() === 'HEAD') {
                $headerParser = new Header($reader, $this->logger);
                $header       = $headerParser->parse();

                $gedcom->setHeader($header);
            }

            // Submission record
            if ($reader->value() === 'SUBN') {
                $submissionParser = new Submission($reader, $this->logger);
                $submission       = $submissionParser->parse();

                $gedcom->setSubmission($submission);
            }
        }

var_dump($gedcom);
exit;

        return $gedcom;
    }
}
