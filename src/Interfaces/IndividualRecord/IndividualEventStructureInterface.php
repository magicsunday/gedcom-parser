<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
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
     * Pertaining to the creation of a legally approved child-parent relationship that does not exist biologically.
     */
    public const TAG_ADOP = 'ADOP';

    /**
     * The event of baptism (not LDS), performed in infancy or later.
     */
    public const TAG_BAPM = 'BAPM';

    /**
     * The ceremonial event held when a Jewish boy reaches age 13.
     */
    public const TAG_BARM = 'BARM';

    /**
     * The ceremonial event held when a Jewish girl reaches age 13, also known as "Bat Mitzvah".
     */
    public const TAG_BASM = 'BASM';

    /**
     * The event of entering into life.
     */
    public const TAG_BIRT = 'BIRT';

    /**
     * A religious event of bestowing divine care or intercession. Sometimes given in connection with
     * a naming ceremony.
     */
    public const TAG_BLES = 'BLES';

    /**
     * The event of the proper disposing of the mortal remains of a deceased person.
     */
    public const TAG_BURI = 'BURI';

    /**
     * The event of the periodic count of the population for a designated locality, such as a national
     * or state Census.
     */
    public const TAG_CENS = 'CENS';

    /**
     * The religious event (not LDS) of baptizing and/or naming a child.
     */
    public const TAG_CHR = 'CHR';

    /**
     * The religious event (not LDS) of baptizing and/or naming an adult person.
     */
    public const TAG_CHRA = 'CHRA';

    /**
     * The religious event (not LDS) of conferring the gift of the Holy Ghost and, among protestants,
     * full church membership.
     */
    public const TAG_CONF = 'CONF';

    /**
     * Disposal of the remains of a person's body by fire.
     */
    public const TAG_CREM = 'CREM';

    /**
     * The event when mortal life terminates.
     */
    public const TAG_DEAT = 'DEAT';

    /**
     * An event of leaving one's homeland with the intent of residing elsewhere.
     */
    public const TAG_EMIG = 'EMIG';

    /**
     * Pertaining to a noteworthy happening related to an individual, a group, or an organization. An EVENt
     * structure is usually qualified or classified by a subordinate use of the TYPE tag.
     */
    public const TAG_EVEN = 'EVEN';

    /**
     * A religious rite, the first act of sharing in the Lord's supper as part of church worship.
     */
    public const TAG_FCOM = 'FCOM';

    /**
     * An event of awarding educational diplomas or degrees to individuals.
     */
    public const TAG_GRAD = 'GRAD';

    /**
     * An event of entering into a new locality with the intent of residing there.
     */
    public const TAG_IMMI = 'IMMI';

    /**
     * The event of obtaining citizenship.
     */
    public const TAG_NATU = 'NATU';

    /**
     * A religious event of receiving authority to act in religious matters.
     */
    public const TAG_ORDN = 'ORDN';

    /**
     * An event of judicial determination of the validity of a will. May indicate several related
     * court activities over several dates.
     */
    public const TAG_PROB = 'PROB';

    /**
     * An event of exiting an occupational relationship with an employer after a qualifying time period.
     */
    public const TAG_RETI = 'RETI';

    /**
     * A legal document treated as an event, by which a person disposes of his or her estate, to take effect
     * after death. The event date is the date the will was signed while the person was alive.
     */
    public const TAG_WILL = 'WILL';

    /**
     * @return AdoptionInterface[]
     */
    public function getAdoption(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getBaptism(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getBarMitzvah(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getBasMitzvah(): array;

    /**
     * @return BirthInterface[]
     */
    public function getBirth(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getBlessing(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getBurial(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getCensus(): array;

    /**
     * @return ChristeningInterface[]
     */
    public function getChristening(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getAdultChristening(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getConfirmation(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getCremation(): array;

    /**
     * @return DeathInterface[]
     */
    public function getDeath(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getEmigration(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getEvent(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getFirstCommunion(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getGraduation(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getImmigration(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getNaturalization(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getOrdination(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getProbate(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getRetirement(): array;

    /**
     * @return IndividualEventDetailInterface[]
     */
    public function getWill(): array;
}
