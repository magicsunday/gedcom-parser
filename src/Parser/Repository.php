<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Parser;

use MagicSunday\Gedcom\AbstractParser;
use MagicSunday\Gedcom\Model\Repository as RepositoryModel;

/**
 * A REPO parser.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class Repository extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    protected function getClassMap(): array
    {
        return [
        ];
    }

    /**
     * Parse a REPO block.
     *
     * @return RepositoryModel
     */
    public function parse(): RepositoryModel
    {
        $repository = new RepositoryModel();
        $repository->setXref($this->reader->identifier());

        while ($this->reader->read() && $this->valid()) {
            switch ($this->reader->tag()) {
            }
        }

        return $repository;
    }
}
