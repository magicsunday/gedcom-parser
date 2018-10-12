<?php
/**
 * See LICENSE.md file for further details.
 */
declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord;

use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail\AdoptionInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail\BirthInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail\ChristeningInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetail\DeathInterface;
use MagicSunday\Gedcom\Interfaces\IndividualRecord\IndividualEventStructure\IndividualEventDetailInterface;

/**
 * The individual event structure tags.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface IndividualEventStructureInterface
{
    /**
     * Pertaining to creation of a legally approved child-parent relationship that does not exist biologically.
     */
    const TAG_ADOP = 'ADOP';

    /**
     * The event of baptism (not LDS), performed in infancy or later.
     */
    const TAG_BAPM = 'BAPM';

    /**
     * The ceremonial event held when a Jewish boy reaches age 13.
     */
    const TAG_BARM = 'BARM';

    /**
     * The ceremonial event held when a Jewish girl reaches age 13, also known as "Bat Mitzvah."
     */
    const TAG_BASM = 'BASM';

    /**
     * The event of entering into life.
     */
    const TAG_BIRT = 'BIRT';

    /**
     * A religious event of bestowing divine care or intercession. Sometimes given in connection with
     * a naming ceremony.
     */
    const TAG_BLES = 'BLES';

    /**
     * The event of the proper disposing of the mortal remains of a deceased person.
     */
    const TAG_BURI = 'BURI';

    /**
     * The event of the periodic count of the population for a designated locality, such as a national
     * or state Census.
     */
    const TAG_CENS = 'CENS';

    /**
     * The religious event (not LDS) of baptizing and/or naming a child.
     */
    const TAG_CHR = 'CHR';

    /**
     * The religious event (not LDS) of baptizing and/or naming an adult person.
     */
    const TAG_CHRA = 'CHRA';

    /**
     * The religious event (not LDS) of conferring the gift of the Holy Ghost and, among protestants,
     * full church membership.
     */
    const TAG_CONF = 'CONF';

    /**
     * Disposal of the remains of a person's body by fire.
     */
    const TAG_CREM = 'CREM';

    /**
     * The event when mortal life terminates.
     */
    const TAG_DEAT = 'DEAT';

    /**
     * An event of leaving one's homeland with the intent of residing elsewhere.
     */
    const TAG_EMIG = 'EMIG';

    /**
     * Pertaining to a noteworthy happening related to an individual, a group, or an organization. An EVENt
     * structure is usually qualified or classified by a subordinate use of the TYPE tag.
     */
    const TAG_EVEN = 'EVEN';

    /**
     * A religious rite, the first act of sharing in the Lord's supper as part of church worship.
     */
    const TAG_FCOM = 'FCOM';

    /**
     * An event of awarding educational diplomas or degrees to individuals.
     */
    const TAG_GRAD = 'GRAD';

    /**
     * An event of entering into a new locality with the intent of residing there.
     */
    const TAG_IMMI = 'IMMI';

    /**
     * The event of obtaining citizenship.
     */
    const TAG_NATU = 'NATU';

    /**
     * A religious event of receiving authority to act in religious matters.
     */
    const TAG_ORDN = 'ORDN';

    /**
     * An event of judicial determination of the validity of a will. May indicate several related
     * court activities over several dates.
     */
    const TAG_PROB = 'PROB';

    /**
     * An event of exiting an occupational relationship with an employer after a qualifying time period.
     */
    const TAG_RETI = 'RETI';

    /**
     * A legal document treated as an event, by which a person disposes of his or her estate, to take effect
     * after death. The event date is the date the will was signed while the person was alive.
     */
    const TAG_WILL = 'WILL';

    /**
     * @return null|AdoptionInterface
     */
    public function getAdoption();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getBaptism();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getBarMitzvah();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getBasMitzvah();

    /**
     * @return null|BirthInterface
     */
    public function getBirth();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getBlessing();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getBurial();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getCensus();

    /**
     * @return null|ChristeningInterface
     */
    public function getChristening();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getAdultChristening();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getConfirmation();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getCremation();

    /**
     * @return null|DeathInterface
     */
    public function getDeath();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getEmigration();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getEvent();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getFirstCommunion();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getGraduation();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getImmigration();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getNaturalization();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getOrdination();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getProbate();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getRetirement();

    /**
     * @return null|IndividualEventDetailInterface
     */
    public function getWill();
}
