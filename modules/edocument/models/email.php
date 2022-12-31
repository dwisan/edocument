<?php
/**
 * @filesource modules/edocument/models/email.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Edocument\Email;

use Kotchasan\Email;
use Kotchasan\KBase;
use Kotchasan\Language;

/**
 * ส่งอีเมลแจ้งสมาชิก
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends KBase
{
    /**
     * ส่งอีเมลไปยังผู้รับ
     *
     * @param array $reciever
     * @param array $login
     *
     * @return string
     */
    public static function send($reciever, $login)
    {
        $ret = array();
        // ข้อมูลอีเมล
        $subject = Language::replace('There are new documents sent to you at %WEBTITLE%', array('%WEBTITLE%' => self::$cfg->web_title));
        $msg = Language::replace('You received a new document %URL%', array('%URL%' => WEB_URL.'index.php?module=edocument'));
        // ตรวจสอบรายชื่อผู้รับ
        $query = \Kotchasan\Model::createQuery()
            ->select('username', 'name', 'line_uid')
            ->from('user')
            ->where(array(
                array('id', '!=', (int) $login['id']),
                array('status', $reciever),
                array('active', 1)
            ))
            ->cacheOn();
        if (self::$cfg->demo_mode) {
            $query->andWhere(array('social', 0));
        }
        $lines = array();
        foreach ($query->execute() as $item) {
            if (!empty($item->line_uid)) {
                $lines[] = $item->line_uid;
            }
            if (self::$cfg->noreply_email != '' && !empty($item->username)) {
                $err = Email::send($item->name.'<'.$item->username.'>', self::$cfg->noreply_email, $subject, $msg);
                if ($err->error()) {
                    // คืนค่า error
                    $ret[] = strip_tags($err->getErrorMessage());
                }
            }
        }
        // LINE ส่วนตัว
        if (!empty($lines)) {
            $err = \Gcms\Line::sendTo($lines, $msg);
            if ($err != '') {
                $ret[] = $err;
            }
        }
        if (isset($err)) {
            // ส่งอีเมลสำเร็จ หรือ error การส่งเมล
            return empty($ret) ? Language::get('Your message was sent successfully') : implode("\n", array_unique($ret));
        } else {
            // ไม่มีอีเมลต้องส่ง
            return Language::get('Saved successfully');
        }
    }
}
