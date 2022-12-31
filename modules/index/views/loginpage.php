<?php
/**
 * @filesource modules/index/views/loginpage.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Index\Loginpage;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=index-loginpage
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มตั้งค่า
     *
     * @return string
     */
    public function render()
    {
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/index/model/loginpage/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-signin',
            'title' => '{LNG_Login page}'
        ));
        // login_show_title_logo
        $fieldset->add('checkbox', array(
            'id' => 'login_show_title_logo',
            'itemClass' => 'subitem',
            'label' => '{LNG_Show web title with logo}',
            'value' => 1,
            'checked' => !empty(self::$cfg->login_show_title_logo)
        ));
        // new_line_title
        $fieldset->add('checkbox', array(
            'id' => 'new_line_title',
            'itemClass' => 'subitem',
            'label' => '{LNG_Start a new line with the web name}',
            'value' => 1,
            'checked' => !empty(self::$cfg->new_line_title)
        ));
        // login_header_color
        $fieldset->add('color', array(
            'id' => 'login_header_color',
            'labelClass' => 'g-input icon-color',
            'itemClass' => 'item',
            'label' => '{LNG_Header font color}',
            'value' => self::$cfg->login_header_color
        ));
        // login_footer_color
        $fieldset->add('color', array(
            'id' => 'login_footer_color',
            'labelClass' => 'g-input icon-color',
            'itemClass' => 'item',
            'label' => '{LNG_Footer font color}',
            'value' => self::$cfg->login_footer_color
        ));
        // bg_image
        if (is_file(ROOT_PATH.DATA_FOLDER.'images/bg_image.png')) {
            $img = WEB_URL.DATA_FOLDER.'images/bg_image.png?'.time();
        } else {
            $img = WEB_URL.'skin/img/blank.gif';
        }
        // bg_image
        $fieldset->add('file', array(
            'id' => 'file_bg_image',
            'labelClass' => 'g-input icon-image',
            'itemClass' => 'item',
            'label' => '{LNG_Background image}',
            'comment' => '{LNG_Browse image uploaded, type :type} {LNG_no larger than :size}',
            'accept' => array('jpg', 'jpeg', 'png'),
            'dataPreview' => 'bgImage',
            'previewSrc' => $img
        ));
        // delete_bg_image
        $fieldset->add('checkbox', array(
            'id' => 'delete_bg_image',
            'itemClass' => 'subitem',
            'label' => '{LNG_Remove} {LNG_Background image}',
            'value' => 1
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-comments',
            'title' => '{LNG_Message displayed on login page}'
        ));
        // login_message
        $fieldset->add('textarea', array(
            'id' => 'login_message',
            'labelClass' => 'g-input icon-file',
            'itemClass' => 'item',
            'label' => '{LNG_Message}',
            'rows' => 5,
            'value' => isset(self::$cfg->login_message) ? self::$cfg->login_message : ''
        ));
        // login_message_style
        $fieldset->add('select', array(
            'id' => 'login_message_style',
            'labelClass' => 'g-input icon-color',
            'itemClass' => 'item',
            'label' => '{LNG_Style}',
            'options' => array('hidden' => Language::find('BOOLEANS', 'Disabled', 0), 'tip' => 'Tip', 'warning' => 'Warning', 'message' => 'Message'),
            'value' => self::$cfg->login_message_style
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}'
        ));
        \Gcms\Controller::$view->setContentsAfter(array(
            '/:type/' => 'jpg, jpeg, png',
            '/:size/' => \Kotchasan\Http\UploadedFile::getUploadSize()
        ));
        // คืนค่า HTML
        return $form->render();
    }
}
