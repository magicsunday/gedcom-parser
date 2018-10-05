<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser\SourceRecord;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\SourceRecord\SourceRepositoryCitation as SourceRepositoryCitationModel;
use MagicSunday\Gedcom\Parser\Common\Note\NoteStructure;
use MagicSunday\Gedcom\Parser\SourceRecord\SourceRepositoryCitation\SourceCallNumber;

/**
 * A source REPO record parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SourceRepositoryCitation extends AbstractParser
{
    /**
     * @inheritDoc
     */
    protected function getClassMap(): array
    {
        return [
            SourceRepositoryCitationModel::TAG_CALN => SourceCallNumber::class,
            SourceRepositoryCitationModel::TAG_NOTE => NoteStructure::class,
        ];
    }

    /**
     * Parses a source REPO record block.
     *
     * @return SourceRepositoryCitationModel
     */
    public function parse(): SourceRepositoryCitationModel
    {
        $sourceRepository = new SourceRepositoryCitationModel();
        $sourceRepository->setValue(SourceRepositoryCitationModel::TAG_XREF_REPO, $this->reader->xref());

        $this->process($sourceRepository);

        return $sourceRepository;
    }
}
