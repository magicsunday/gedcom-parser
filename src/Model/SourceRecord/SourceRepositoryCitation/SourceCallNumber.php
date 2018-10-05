<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\SourceRecord\SourceRepositoryCitation;

use MagicSunday\Gedcom\Interfaces\SourceRecord\SourceRepositoryCitation\SourceCallNumberInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The SOUR (source), REPO (repository), CALN (call number) structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class SourceCallNumber extends DataObject implements SourceCallNumberInterface
{
    /**
     * @inheritDoc
     */
    public function getNumber()
    {
        return $this->getValue(self::TAG_SOURCE_CALL_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function getMediaType()
    {
        return $this->getValue(self::TAG_MEDI);
    }
}
