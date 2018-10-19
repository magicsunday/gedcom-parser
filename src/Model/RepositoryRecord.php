<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model;

use MagicSunday\Gedcom\Interfaces\RepositoryRecordInterface;
use MagicSunday\Gedcom\Traits\Common\AddressStructureTrait;
use MagicSunday\Gedcom\Traits\Common\ChangeDateTrait;
use MagicSunday\Gedcom\Traits\Common\NoteTrait;

/**
 * The REPO (repository) record.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class RepositoryRecord extends DataObject implements RepositoryRecordInterface
{
    use AddressStructureTrait;
    use ChangeDateTrait;
    use NoteTrait;

    /**
     * @inheritDoc
     */
    public function getXref(): string
    {
        return $this->getValue(self::TAG_XREF_REPO);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->getValue(self::TAG_NAME);
    }

    /**
     * @inheritDoc
     */
    public function getReferenceNumber()
    {
        return $this->getValue(self::TAG_REFN);
    }

    /**
     * @inheritDoc
     */
    public function getRecordIdNumber()
    {
        return $this->getValue(self::TAG_RIN);
    }
}
