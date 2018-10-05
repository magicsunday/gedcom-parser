<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\SourceRecord;

use MagicSunday\Gedcom\Interfaces\SourceRecord\SourceRepositoryCitationInterface;
use MagicSunday\Gedcom\Model\DataObject;
use MagicSunday\Gedcom\Traits\Common\Note;

/**
 * The SOUR (source), REPO (repository) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SourceRepositoryCitation extends DataObject implements SourceRepositoryCitationInterface
{
    use Note;

    /**
     * @inheritDoc
     */
    public function getXref()
    {
        return $this->getValue(self::TAG_XREF_REPO);
    }

    /**
     * @inheritDoc
     */
    public function getCallNumber()
    {
        return $this->getValue(self::TAG_CALN);
    }
}
