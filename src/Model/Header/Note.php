<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\Header;

use MagicSunday\Gedcom\Model\DataObject;

/**
 * A note structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Note extends DataObject
{
    /**
     * A note that a user enters to describe the contents of the lineage-linked file in terms of
     * "ancestors or descendants of" so that the person receiving the data knows what genealogical information the
     * transmission contains.
     */
    const TAG_GEDCOM_CONTENT_DESCRIPTION = 'GEDCOM_CONTENT_DESCRIPTION';

    /**
     * Gedcom content description.
     *
     * @var null|string
     */
    private $content;

    /**
     * @return null|string
     */
    public function getNote()
    {
        return $this->getValue(self::TAG_GEDCOM_CONTENT_DESCRIPTION);
    }

    /**
     * @return null|string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
}
