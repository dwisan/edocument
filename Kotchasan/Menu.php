<?php
/**
 * @filesource Kotchasan/Menu.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * คลาสสำหรับแสดงผลเมนูมาตรฐานของ Kotchasan
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Menu
{
    /**
     * แสดงผลเมนู
     *
     * @param array $items รายการเมนู
     *
     * @return string
     */
    public static function render($items, $select)
    {
        $menus = array();
        foreach ($items as $alias => $values) {
            if (isset($values['text']) && $values['text'] !== null) {
                if (isset($values['url'])) {
                    $menus[] = self::getItem($alias, $values, false, $select).'</li>';
                } elseif (isset($values['submenus'])) {
                    $menus[] = self::getItem($alias, $values, true, $select).'<ul>';
                    $menus[] = self::render($values['submenus'], $select);
                    $menus[] = '</ul>';
                }
            }
        }
        return implode('', $menus);
    }

    /**
     * ฟังก์ชั่น แปลงเป็นรายการเมนู
     * คืนค่า HTML ของเมนู
     *
     * @param string|int $name   ชื่อเมนู
     * @param array      $item   แอเรย์ข้อมูลเมนู
     * @param bool       $arrow  true แสดงลูกศรสำหรับเมนูที่มีเมนูย่อย
     * @param string     $select ชื่อเมนูที่ถูกเลือก
     *
     * @return string
     */
    protected static function getItem($name, $item, $arrow, $select)
    {
        if (empty($name) && !is_int($name)) {
            $c = '';
        } else {
            $c = array($name);
            if ($name == $select) {
                $c[] = 'select';
            }
            $c = ' class="'.implode(' ', $c).'"';
        }
        if (!empty($item['url'])) {
            $a = array('href="'.$item['url'].'"');
            if (!empty($item['target'])) {
                $a[] = 'target="'.$item['target'].'"';
            }
        }
        if (!empty($item['text'])) {
            $a[] = 'title="'.$item['text'].'"';
        }
        if ($arrow) {
            $a[] = 'class=menu-arrow';
        }
        $a = isset($a) ? ' '.implode(' ', $a) : '';
        if (empty($item['url'])) {
            return '<li'.$c.'><span '.$a.'><span>'.(empty($item['text']) ? '&nbsp;' : strip_tags(htmlspecialchars_decode($item['text']))).'</span></span>';
        } else {
            return '<li'.$c.'><a'.$a.'><span>'.(empty($item['text']) ? '&nbsp;' : strip_tags(htmlspecialchars_decode($item['text']))).'</span></a>';
        }
    }
}
