<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Traits\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail\AdoptionInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail\BirthInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail\ChristeningInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail\DeathInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetailInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructureInterface;

/**
 * The individual event structure methods.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
trait IndividualEventStructure
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    abstract public function getValue(string $key);

    /**
     * @return null|AdoptionInterface
     */
    public function getAdoption()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_ADOP);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getBaptism()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_BAPM);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getBarMitzvah()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_BARM);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getBasMitzvah()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_BASM);
    }

    /**
     * @return null|BirthInterface
     */
    public function getBirth()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_BIRT);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getBlessing()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_BLES);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getBurial()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_BURI);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getCensus()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_CENS);
    }

    /**
     * @return null|ChristeningInterface
     */
    public function getChristening()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_CHR);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getAdultChristening()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_CHRA);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getConfirmation()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_CONF);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getCremation()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_CREM);
    }

    /**
     * @return null|DeathInterface
     */
    public function getDeath()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_DEAT);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getEmigration()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_EMIG);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getEvent()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_EVEN);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getFirstCommunion()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_FCOM);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getGraduation()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_GRAD);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getImmigration()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_IMMI);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getNaturalization()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_NATU);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getOrdination()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_ORDN);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getProbate()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_PROB);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getRetirement()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_RETI);
    }

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getWill()
    {
        return $this->getValue(IndividualEventStructureInterface::TAG_WILL);
    }
}
