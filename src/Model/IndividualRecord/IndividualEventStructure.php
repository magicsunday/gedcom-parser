<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Model\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructureInterface;
use MagicSunday\Gedcom\Model\DataObject;

/**
 * The individual event structure.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
class IndividualEventStructure extends DataObject implements IndividualEventStructureInterface
{
    /**
     * @inheritDoc
     */
    public function getAdoption()
    {
        return $this->getValue(self::TAG_ADOP);
    }

    /**
     * @inheritDoc
     */
    public function getBaptism()
    {
        return $this->getValue(self::TAG_BAPM);
    }

    /**
     * @inheritDoc
     */
    public function getBarMitzvah()
    {
        return $this->getValue(self::TAG_BARM);
    }

    /**
     * @inheritDoc
     */
    public function getBasMitzvah()
    {
        return $this->getValue(self::TAG_BASM);
    }

    /**
     * @inheritDoc
     */
    public function getBirth()
    {
        return $this->getValue(self::TAG_BIRT);
    }

    /**
     * @inheritDoc
     */
    public function getBlessing()
    {
        return $this->getValue(self::TAG_BLES);
    }

    /**
     * @inheritDoc
     */
    public function getBurial()
    {
        return $this->getValue(self::TAG_BURI);
    }

    /**
     * @inheritDoc
     */
    public function getCensus()
    {
        return $this->getValue(self::TAG_CENS);
    }

    /**
     * @inheritDoc
     */
    public function getChristening()
    {
        return $this->getValue(self::TAG_CHR);
    }

    /**
     * @inheritDoc
     */
    public function getAdultChristening()
    {
        return $this->getValue(self::TAG_CHRA);
    }

    /**
     * @inheritDoc
     */
    public function getConfirmation()
    {
        return $this->getValue(self::TAG_CONF);
    }

    /**
     * @inheritDoc
     */
    public function getCremation()
    {
        return $this->getValue(self::TAG_CREM);
    }

    /**
     * @inheritDoc
     */
    public function getDeath()
    {
        return $this->getValue(self::TAG_DEAT);
    }

    /**
     * @inheritDoc
     */
    public function getEmigration()
    {
        return $this->getValue(self::TAG_EMIG);
    }

    /**
     * @inheritDoc
     */
    public function getEvent()
    {
        return $this->getValue(self::TAG_EVEN);
    }

    /**
     * @inheritDoc
     */
    public function getFirstCommunion()
    {
        return $this->getValue(self::TAG_FCOM);
    }

    /**
     * @inheritDoc
     */
    public function getGraduation()
    {
        return $this->getValue(self::TAG_GRAD);
    }

    /**
     * @inheritDoc
     */
    public function getImmigration()
    {
        return $this->getValue(self::TAG_IMMI);
    }

    /**
     * @inheritDoc
     */
    public function getNaturalization()
    {
        return $this->getValue(self::TAG_NATU);
    }

    /**
     * @inheritDoc
     */
    public function getOrdination()
    {
        return $this->getValue(self::TAG_ORDN);
    }

    /**
     * @inheritDoc
     */
    public function getProbate()
    {
        return $this->getValue(self::TAG_PROB);
    }

    /**
     * @inheritDoc
     */
    public function getRetirement()
    {
        return $this->getValue(self::TAG_RETI);
    }

    /**
     * @inheritDoc
     */
    public function getWill()
    {
        return $this->getValue(self::TAG_WILL);
    }
}
