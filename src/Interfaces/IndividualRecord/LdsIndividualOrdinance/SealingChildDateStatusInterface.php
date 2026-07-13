<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Interfaces\IndividualRecord\LdsIndividualOrdinance;

/**
 * The LDS child-sealing (SLGC) ordinance date status interface. It specialises
 * {@see CommonDateStatusInterface} for the child-sealing context and inherits its
 * TAG_DATE_STATUS constant and getStatus() unchanged. Per the GEDCOM specification the
 * child-sealing status vocabulary differs from the baptism one (it additionally allows
 * BIC and DNS); see the tracking issue for wiring this type into the parser or removing it.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
interface SealingChildDateStatusInterface extends CommonDateStatusInterface
{
}
