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
trait IndividualEventStructureTrait
{
    /**
     * @param string $key
     *
     * @return array
     */
    abstract public function getArrayValue(string $key): array;

    /**
     * @return AdoptionInterface[]
     */
    public function getAdoption(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_ADOP);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getBaptism(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_BAPM);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getBarMitzvah(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_BARM);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getBasMitzvah(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_BASM);
    }

    /**
     * @return BirthInterface[]
     */
    public function getBirth(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_BIRT);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getBlessing(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_BLES);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getBurial(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_BURI);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getCensus(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_CENS);
    }

    /**
     * @return ChristeningInterface[]
     */
    public function getChristening(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_CHR);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getAdultChristening(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_CHRA);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getConfirmation(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_CONF);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getCremation(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_CREM);
    }

    /**
     * @return DeathInterface[]
     */
    public function getDeath(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_DEAT);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getEmigration(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_EMIG);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getEvent(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_EVEN);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getFirstCommunion(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_FCOM);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getGraduation(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_GRAD);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getImmigration(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_IMMI);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getNaturalization(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_NATU);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getOrdination(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_ORDN);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getProbate(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_PROB);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getRetirement(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_RETI);
    }

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getWill(): array
    {
        return $this->getArrayValue(IndividualEventStructureInterface::TAG_WILL);
    }
}
