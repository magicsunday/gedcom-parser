<?php

/**
 * This file is part of the package magicsunday/gedcom-parser.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MagicSunday\Gedcom\Encoding;

use Normalizer;

use function array_key_exists;
use function ord;
use function strlen;

/**
 * Decodes ANSEL (ANSI Z39.47-1985, the GEDCOM 5.5.1 default character set, a subset of
 * MARC-8) to UTF-8. ANSEL has no `ext-mbstring`/`ext-iconv` support, so the repertoire is
 * mapped by hand; combining diacritics — which ANSEL stores BEFORE their base character —
 * are reordered after the base and composed to NFC.
 *
 * @author  Rico Sonntag <mail@ricosonntag.de>
 * @license https://opensource.org/licenses/MIT
 * @link    https://github.com/magicsunday/gedcom-parser/
 */
final class AnselDecoder
{
    /**
     * ANSEL graphic (non-combining) bytes 0xA1–0xCF mapped to their Unicode scalar. Gaps in
     * the range are undefined and fall through to the replacement character.
     *
     * @var array<int, string>
     */
    private const BASE = [
        0xA1 => "\u{0141}", // Ł  latin capital letter l with stroke
        0xA2 => "\u{00D8}", // Ø  latin capital letter o with stroke
        0xA3 => "\u{0110}", // Đ  latin capital letter d with stroke
        0xA4 => "\u{00DE}", // Þ  latin capital letter thorn
        0xA5 => "\u{00C6}", // Æ  latin capital ligature ae
        0xA6 => "\u{0152}", // Œ  latin capital ligature oe
        0xA7 => "\u{02B9}", // ʹ  modifier letter prime (miagkii znak)
        0xA8 => "\u{00B7}", // ·  middle dot
        0xA9 => "\u{266D}", // ♭  music flat sign
        0xAA => "\u{00AE}", // ®  registered sign (patent mark)
        0xAB => "\u{00B1}", // ±  plus-minus sign
        0xAC => "\u{01A0}", // Ơ  latin capital letter o with horn
        0xAD => "\u{01AF}", // Ư  latin capital letter u with horn
        0xAE => "\u{02BE}", // ʾ  modifier letter right half ring (alif)
        0xB0 => "\u{02BF}", // ʿ  modifier letter left half ring (ayn)
        0xB1 => "\u{0142}", // ł  latin small letter l with stroke
        0xB2 => "\u{00F8}", // ø  latin small letter o with stroke
        0xB3 => "\u{0111}", // đ  latin small letter d with stroke
        0xB4 => "\u{00FE}", // þ  latin small letter thorn
        0xB5 => "\u{00E6}", // æ  latin small ligature ae
        0xB6 => "\u{0153}", // œ  latin small ligature oe
        0xB7 => "\u{02BA}", // ʺ  modifier letter double prime (tverdyi znak)
        0xB8 => "\u{0131}", // ı  latin small letter dotless i
        0xB9 => "\u{00A3}", // £  pound sign
        0xBA => "\u{00F0}", // ð  latin small letter eth
        0xBC => "\u{01A1}", // ơ  latin small letter o with horn
        0xBD => "\u{01B0}", // ư  latin small letter u with horn
        0xC0 => "\u{00B0}", // °  degree sign
        0xC1 => "\u{2113}", // ℓ  script small l
        0xC2 => "\u{2117}", // ℗  sound recording copyright
        0xC3 => "\u{00A9}", // ©  copyright sign
        0xC4 => "\u{266F}", // ♯  music sharp sign
        0xC5 => "\u{00BF}", // ¿  inverted question mark
        0xC6 => "\u{00A1}", // ¡  inverted exclamation mark
        0xCF => "\u{00DF}", // ß  latin small letter sharp s (es zet)
    ];

    /**
     * ANSEL combining diacritic bytes 0xE0–0xFE mapped to their Unicode combining mark.
     * These precede the base character in ANSEL and are reordered after it on decode.
     *
     * @var array<int, string>
     */
    private const COMBINING = [
        0xE0 => "\u{0309}", // hook above
        0xE1 => "\u{0300}", // grave
        0xE2 => "\u{0301}", // acute
        0xE3 => "\u{0302}", // circumflex
        0xE4 => "\u{0303}", // tilde
        0xE5 => "\u{0304}", // macron
        0xE6 => "\u{0306}", // breve
        0xE7 => "\u{0307}", // dot above
        0xE8 => "\u{0308}", // diaeresis
        0xE9 => "\u{030C}", // caron
        0xEA => "\u{030A}", // ring above
        0xEB => "\u{FE20}", // ligature left half
        0xEC => "\u{FE21}", // ligature right half
        0xED => "\u{0315}", // comma above right
        0xEE => "\u{030B}", // double acute
        0xEF => "\u{0310}", // candrabindu
        0xF0 => "\u{0327}", // cedilla
        0xF1 => "\u{0328}", // ogonek
        0xF2 => "\u{0323}", // dot below
        0xF3 => "\u{0324}", // diaeresis below
        0xF4 => "\u{0325}", // ring below
        0xF5 => "\u{0333}", // double low line
        0xF6 => "\u{0332}", // line below
        0xF7 => "\u{0326}", // comma below
        0xF8 => "\u{031C}", // left half ring below
        0xF9 => "\u{032E}", // breve below
        0xFA => "\u{FE22}", // double tilde left half
        0xFB => "\u{FE23}", // double tilde right half
        0xFE => "\u{0313}", // comma above
    ];

    /**
     * Static-only utility.
     */
    private function __construct()
    {
    }

    /**
     * Decodes an ANSEL byte string to composed (NFC) UTF-8. ASCII bytes (0x00–0x7F) pass
     * through unchanged, so a whole GEDCOM line can be decoded without disturbing its
     * structural framing.
     *
     * @param string $ansel the raw ANSEL bytes
     *
     * @return string the decoded, NFC-normalised UTF-8 string
     */
    public static function decode(string $ansel): string
    {
        $result = '';
        $marks  = '';
        $length = strlen($ansel);

        for ($i = 0; $i < $length; ++$i) {
            $byte = ord($ansel[$i]);

            if ($byte < 0x80) {
                $result .= $ansel[$i] . $marks;
                $marks = '';

                continue;
            }

            if (array_key_exists($byte, self::COMBINING)) {
                // ANSEL stores the mark before its base; buffer it until the base arrives.
                $marks .= self::COMBINING[$byte];

                continue;
            }

            if (array_key_exists($byte, self::BASE)) {
                $result .= self::BASE[$byte] . $marks;
                $marks = '';

                continue;
            }

            if ($byte <= 0x9F) {
                // Control region (non-sorting delimiters, fill character) — no output.
                continue;
            }

            // Any other undefined byte becomes the replacement character so the output is
            // never invalid UTF-8 with a stray raw byte.
            $result .= "\u{FFFD}" . $marks;
            $marks = '';
        }

        if ($marks !== '') {
            // A combining mark with no following base attaches to a space.
            $result .= ' ' . $marks;
        }

        $normalised = Normalizer::normalize($result, Normalizer::FORM_C);

        return $normalised !== false ? $normalised : $result;
    }
}
