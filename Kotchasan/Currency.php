<?php
/**
 * @filesource Kotchasan/Currency.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * แปลงตัวเลขเป็นจำนวนเงิน บาท ดอลล่าร์
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Currency
{
    /**
     * แปลงจำนวนเงินเป็นตัวหนังสือ
     *
     * @assert (13.00) [==] 'thirteen Baht'
     * @assert (101.55) [==] 'one hundred one Baht and fifty-five Satang'
     * @assert (1234.56) [==] 'one thousand two hundred thirty-four Baht and fifty-six Satang'
     * @assert (12345.67) [==] 'twelve thousand three hundred forty-five Baht and sixty-seven Satang'
     * @assert (-1000000050) [==] 'negative one billion fifty Baht'
     * @assert (1416921) [==] 'one million four hundred sixteen thousand nine hundred twenty-one Baht'
     * @assert (269346000.00) [==] 'two hundred sixty-nine million three hundred forty-six thousand Baht'
     * @assert (1000000000.00) [==] 'one billion Baht'
     * @assert (10000000050.25) [==] 'ten billion fifty Baht and twenty-five Satang'
     * @assert (100000000000.00) [==] 'one hundred billion Baht'
     * @assert (1000000000000) [==] 'one trillion Baht'
     * @assert (999999999999999) [==] 'nine hundred ninety-nine trillion nine hundred ninety-nine billion nine hundred ninety-nine million nine hundred ninety-nine thousand nine hundred ninety-nine Baht'
     * @assert (1000000000000000500) [==] 'one thousand quadrillion five hundred Baht'
     *
     * @param string $thb
     *
     * @return string
     */
    public static function bahtEng($thb)
    {
        if (preg_match('/(-)?([0-9]+)(\.([0-9]+))?/', (string) $thb, $match)) {
            $thb = self::engFormat(intval($match[2])).' Baht';
            if (isset($match[4]) && intval($match[4]) > 0) {
                $thb .= ' and '.self::engFormat(intval(substr($match[4].'00', 0, 2))).' Satang';
            }
            return ($match[1] == '-' ? 'negative ' : '').$thb;
        }
        return '';
    }

    /**
     * ตัวเลขเป็นตัวหนังสือ (ไทย)
     *
     * @assert (13.00) [==] 'สิบสามบาทถ้วน'
     * @assert (101.55) [==] 'หนึ่งร้อยเอ็ดบาทห้าสิบห้าสตางค์'
     * @assert (1234.56) [==] 'หนึ่งพันสองร้อยสามสิบสี่บาทห้าสิบหกสตางค์'
     * @assert (12345.67) [==] 'หนึ่งหมื่นสองพันสามร้อยสี่สิบห้าบาทหกสิบเจ็ดสตางค์'
     * @assert (-1000000050) [==] 'ลบหนึ่งพันล้านห้าสิบบาทถ้วน'
     * @assert (1416921) [==] 'หนึ่งล้านสี่แสนหนึ่งหมื่นหกพันเก้าร้อยยี่สิบเอ็ดบาทถ้วน'
     * @assert (269346000.00) [==] 'สองร้อยหกสิบเก้าล้านสามแสนสี่หมื่นหกพันบาทถ้วน'
     * @assert (1000000000.00) [==] 'หนึ่งพันล้านบาทถ้วน'
     * @assert (10000000050.25) [==] 'หนึ่งหมื่นล้านห้าสิบบาทยี่สิบห้าสตางค์'
     * @assert (100000000000.00) [==] 'หนึ่งแสนล้านบาทถ้วน'
     * @assert (1000000000000) [==] 'หนึ่งล้านล้านบาทถ้วน'
     *
     * @param string $thb
     *
     * @return string
     */
    public static function bahtThai($thb)
    {
        if (preg_match('/(-)?([0-9]+)(\.([0-9]+))?/', (string) $thb, $match)) {
            $isNegative = $match[1] == '-';
            $thb = $match[2];
            $ths = !empty($match[4]) ? substr($match[4].'00', 0, 2) : '';
            $thaiNum = array('', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
            $unitBaht = array('บาท', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');
            $unitSatang = array('สตางค์', 'สิบ');
            $THB = '';
            $j = 0;
            for ($i = strlen($thb) - 1; $i >= 0; $i--, $j++) {
                $num = $thb[$i];
                $tnum = $thaiNum[$num];
                $unit = $unitBaht[$j];
                if ($j == 0 && $num == 1 && strlen($thb) > 1) {
                    $tnum = 'เอ็ด';
                } elseif ($j == 1 && $num == 1) {
                    $tnum = '';
                } elseif ($j == 1 && $num == 2) {
                    $tnum = 'ยี่';
                } elseif ($j == 6 && $num == 1 && strlen($thb) > 7) {
                    $tnum = 'เอ็ด';
                } elseif ($j == 7 && $num == 1) {
                    $tnum = '';
                } elseif ($j == 7 && $num == 2) {
                    $tnum = 'ยี่';
                } elseif ($j != 0 && $j != 6 && $num == 0) {
                    $unit = '';
                }
                $THB = $tnum.$unit.$THB;
            }
            $THB = ($isNegative ? 'ลบ' : '').$THB;
            if ($ths == '' || $ths == '00') {
                $THS = 'ถ้วน';
            } else {
                $j = 0;
                $THS = '';
                for ($i = strlen($ths) - 1; $i >= 0; $i--, $j++) {
                    $num = $ths[$i];
                    $tnum = $thaiNum[$num];
                    $unit = $unitSatang[$j];
                    if ($j == 0 && $num == 1 && strlen($ths) > 1) {
                        $tnum = 'เอ็ด';
                    } elseif ($j == 1 && $num == 1) {
                        $tnum = '';
                    } elseif ($j == 1 && $num == 2) {
                        $tnum = 'ยี่';
                    } elseif ($j != 0 && $j != 6 && $num == 0) {
                        $unit = '';
                    }
                    $THS = $tnum.$unit.$THS;
                }
            }
            return $THB.$THS;
        }
        return '';
    }

    /**
     * ฟังก์ชั่นคำนวณภาษี
     * $vat_ex = true ราคาสินค้ารวม VAT เช่น ราคาสินค้า 100 + VAT 7 = ราคาขาย 107
     * $vat_ex = false ราคาสินค้ารวม VAT เช่น ราคาขาย 100 = ราคาสินค้า 93 + VAT 7
     * คืนค่า VAT จากราคาขาย
     *
     * @assert (1000, 7, true) [==] 70
     * @assert (1000, 7, false) [==] 65.420560747663558
     *
     * @param float $amount ราคาขาย
     * @param float $vat    VAT
     * @param bool  $vat_ex
     *
     * @return float
     */
    public static function calcVat($amount, $vat, $vat_ex = true)
    {
        if ($vat_ex) {
            $result = (($vat * $amount) / 100);
        } else {
            $result = $amount - ($amount * (100 / (100 + $vat)));
        }
        return $result;
    }

    /**
     * ฟังก์ชั่น แปลงตัวเลขเป็นจำนวนเงิน
     * คืนค่าข้อความจำนวนเงิน
     *
     * @assert (1000000.444) [==] '1,000,000.44'
     * @assert (1000000.555) [==] '1,000,000.56'
     * @assert (1000000.55455, 3, ',', false) [==] '1,000,000.554'
     * @assert (1000000.55455, 3) [==] '1,000,000.555'
     *
     * @param float $amount จำนวนเงิน
     * @param int $digit จำนวนทศนิยม (optional) ค่าเริ่มต้น 2 หลัก
     * @param string $thousands_sep (optional) เครื่องหมายหลักพัน (default ,)
     * @param bool $round (optional) true (default) หลังจุดทศนิยมในหลักที่เกินตั้งแต่ 5 ขึ้นไปปัดขึ้น (round), false ตัดหลักที่เกินทิ้ง (floor)
     *
     * @return string
     */
    public static function format($amount, $digit = 2, $thousands_sep = ',', $round = true)
    {
        if (!$round && preg_match('/^([0-9]+)(\.[0-9]{'.$digit.','.$digit.'})[0-9]+$/', (string) $amount, $match)) {
            return number_format((float) $match[1].$match[2], $digit, '.', $thousands_sep);
        } else {
            return number_format((float) $amount, $digit, '.', $thousands_sep);
        }
    }

    /**
     * ตัวเลขเป็นตัวหนังสือ (eng)
     *
     * @param int $number
     *
     * @return string
     */
    private static function engFormat($number)
    {
        $amount_words = array(
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'forty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety'
        );
        if (isset($amount_words[$number])) {
            return $amount_words[$number];
        }
        // 0-99
        if ($number < 100) {
            $prefix = self::engFormat(floor($number / 10) * 10);
            $suffix = self::engFormat($number % 10);
            return $prefix.'-'.$suffix;
        }
        // 100-999,999,999,999,999
        $amount_units = array(
            1000 => [100, ' hundred'],
            1000000 => [1000, ' thousand'],
            1000000000 => [1000000, ' million'],
            1000000000000 => [1000000000, ' billion'],
            1000000000000000 => [1000000000000, ' trillion']
        );
        foreach ($amount_units as $amount => $units) {
            if ($number < $amount) {
                $string = self::engFormat(floor($number / $units[0])).$units[1];
                if ($number % $units[0]) {
                    $string .= ' '.self::engFormat($number % $units[0]);
                }
                return $string;
            }
        }
        // > 999,999,999,999,999
        $string = self::engFormat(floor($number / 1000000000000000)).' quadrillion';
        if ($number % 1000000000000000) {
            $string .= ' '.self::engFormat($number % 1000000000000000);
        }
        return $string;
    }
}
