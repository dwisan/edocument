<?php
/**
 * @filesource modules/index/views/system.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Index\System;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=system
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มตั้งค่า system
     *
     * @param object $config
     * @param array $login
     *
     * @return string
     */
    public function render($config, $login)
    {
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/index/model/system/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-config',
            'title' => '{LNG_General}'
        ));
        // web_title
        $fieldset->add('text', array(
            'id' => 'web_title',
            'labelClass' => 'g-input icon-home',
            'itemClass' => 'item',
            'label' => '{LNG_Website title}',
            'comment' => '{LNG_Site Name}',
            'maxlength' => 255,
            'value' => isset($config->web_title) ? $config->web_title : self::$cfg->web_title
        ));
        // web_description
        $fieldset->add('text', array(
            'id' => 'web_description',
            'labelClass' => 'g-input icon-home',
            'itemClass' => 'item',
            'label' => '{LNG_Description}',
            'comment' => '{LNG_Short description about your website}',
            'maxlength' => 255,
            'value' => isset($config->web_description) ? $config->web_description : self::$cfg->web_description
        ));
        // timezone
        $datas = array();
        foreach (\DateTimeZone::listIdentifiers() as $item) {
            $datas[$item] = $item;
        }
        $fieldset->add('text', array(
            'id' => 'timezone',
            'labelClass' => 'g-input icon-clock',
            'itemClass' => 'item',
            'label' => '{LNG_Time zone}&nbsp;({LNG_Server time}&nbsp;<em id=server_time>'.date('H:i:s').'</em>&nbsp;{LNG_Local time}&nbsp;<em id=local_time></em>)',
            'comment' => '{LNG_Settings the timing of the server to match the local time}',
            'datalist' => $datas,
            'value' => isset($config->timezone) ? $config->timezone : self::$cfg->timezone
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-users',
            'title' => '{LNG_User}'
        ));
        // member_only
        $fieldset->add('select', array(
            'id' => 'member_only',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'item',
            'label' => '{LNG_Login per one account}',
            'comment' => '{LNG_Limit access to only one account per member. Members who have logged in before will be forced to log out.}',
            'options' => Language::get('BOOLEANS'),
            'value' => isset($config->member_only) ? $config->member_only : self::$cfg->member_only
        ));
        // user_forgot
        $fieldset->add('select', array(
            'id' => 'user_forgot',
            'labelClass' => 'g-input icon-password',
            'itemClass' => 'item',
            'label' => '{LNG_Forgot}',
            'options' => Language::get('BOOLEANS'),
            'value' => isset($config->user_forgot) ? $config->user_forgot : 1
        ));
        // user_register
        $fieldset->add('select', array(
            'id' => 'user_register',
            'labelClass' => 'g-input icon-register',
            'itemClass' => 'item',
            'label' => '{LNG_Register}',
            'options' => Language::get('BOOLEANS'),
            'value' => isset($config->user_register) ? $config->user_register : 1
        ));
        // new_members_active
        $fieldset->add('select', array(
            'id' => 'new_members_active',
            'labelClass' => 'g-input icon-register',
            'itemClass' => 'item',
            'label' => '{LNG_New members}',
            'options' => array(1 => '{LNG_Can login}', 0 => '{LNG_Can&#039;t login} ({LNG_Waiting to check from the staff})'),
            'value' => isset($config->new_members_active) ? $config->new_members_active : 1
        ));
        // login_fields
        $fieldset->add('checkboxgroups', array(
            'id' => 'login_fields',
            'label' => '{LNG_Login by}',
            'comment' => '{LNG_Settings the conditions for member login}',
            'labelClass' => 'g-input icon-signin',
            'options' => Language::get('LOGIN_FIELDS'),
            'value' => isset($config->login_fields) ? $config->login_fields : self::$cfg->login_fields
        ));
        // activate_user
        $fieldset->add('select', array(
            'id' => 'activate_user',
            'labelClass' => 'g-input icon-verfied',
            'itemClass' => 'item',
            'label' => '{LNG_Email address verification}',
            'comment' => '{LNG_When enabled, Members registered with email must also verify their email address. It is not recommended to use in conjunction with other login methods.}',
            'options' => Language::get('BOOLEANS'),
            'value' => isset($config->activate_user) ? $config->activate_user : 0
        ));
        // welcome_email
        $fieldset->add('select', array(
            'id' => 'welcome_email',
            'labelClass' => 'g-input icon-email',
            'itemClass' => 'item',
            'label' => '{LNG_Send a welcome email to new members}',
            'options' => Language::get('BOOLEANS'),
            'value' => isset($config->welcome_email) ? $config->welcome_email : 0
        ));
        $notDemoMode = Login::notDemoMode($login);
        // google_client_id
        $fieldset->add('text', array(
            'id' => 'google_client_id',
            'labelClass' => 'g-input icon-google',
            'itemClass' => 'item',
            'label' => '{LNG_Google client ID} <a class=icon-help href="https://gcms.in.th/index.php?module=howto&id=374" target=_blank></a>',
            'comment' => '<em>xxxxxxxxxx</em>.apps.googleusercontent.com',
            'value' => $notDemoMode && isset($config->google_client_id) ? $config->google_client_id : ''
        ));
        // facebook_appId
        $fieldset->add('text', array(
            'id' => 'facebook_appId',
            'labelClass' => 'g-input icon-facebook',
            'itemClass' => 'item',
            'label' => '{LNG_Facebook App ID} <a class=icon-help href="https://gcms.in.th/index.php?module=howto&id=350" target="_blank"></a>',
            'value' => $notDemoMode && isset($config->facebook_appId) ? $config->facebook_appId : ''
        ));
        // demo_mode
        $fieldset->add('select', array(
            'id' => 'demo_mode',
            'labelClass' => 'g-input icon-design',
            'itemClass' => 'item',
            'label' => '{LNG_Demo Mode}',
            'comment' => '{LNG_When enabled Social accounts can be logged in as an administrator. (Some abilities will not be available)}',
            'options' => Language::get('BOOLEANS'),
            'value' => $notDemoMode && isset($config->demo_mode) ? $config->demo_mode : false
        ));
        $fieldset = $form->add('fieldset', array(
            'titleClass' => 'icon-color',
            'title' => '{LNG_Style}'
        ));
        $groups = $fieldset->add('groups');
        // bg_color
        $groups->add('color', array(
            'id' => 'bg_color',
            'labelClass' => 'g-input icon-color',
            'itemClass' => 'width50',
            'label' => '{LNG_Header background color}',
            'value' => self::$cfg->bg_color
        ));
        // color
        $groups->add('color', array(
            'id' => 'color',
            'labelClass' => 'g-input icon-color',
            'itemClass' => 'width50',
            'label' => '{LNG_Text color}',
            'value' => self::$cfg->color
        ));
        // warpper_bg_color
        $fieldset->add('color', array(
            'id' => 'warpper_bg_color',
            'labelClass' => 'g-input icon-color',
            'itemClass' => 'item',
            'label' => '{LNG_Content background color}',
            'value' => self::$cfg->warpper_bg_color
        ));
        // logo
        if (is_file(ROOT_PATH.DATA_FOLDER.'images/logo.png')) {
            $img = WEB_URL.DATA_FOLDER.'images/logo.png?'.time();
        } else {
            $img = WEB_URL.'skin/img/blank.gif';
        }
        // logo
        $fieldset->add('file', array(
            'id' => 'file_logo',
            'labelClass' => 'g-input icon-image',
            'itemClass' => 'item',
            'label' => '{LNG_Logo}',
            'comment' => '{LNG_Browse image uploaded, type :type} {LNG_size :width*:height pixel}',
            'accept' => array('jpg', 'jpeg', 'png'),
            'dataPreview' => 'logoImage',
            'previewSrc' => $img
        ));
        // delete_logo
        $fieldset->add('checkbox', array(
            'id' => 'delete_logo',
            'itemClass' => 'subitem',
            'label' => '{LNG_Remove} {LNG_Logo}',
            'value' => 1
        ));
        // show_title_logo
        $fieldset->add('checkbox', array(
            'id' => 'show_title_logo',
            'itemClass' => 'subitem',
            'label' => '{LNG_Show web title with logo}',
            'value' => 1,
            'checked' => !empty(self::$cfg->show_title_logo)
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit'
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}'
        ));
        // Javascript
        $form->script('initSystem();');
        \Gcms\Controller::$view->setContentsAfter(array(
            '/:type/' => 'jpg, jpeg, png',
            '/:width/' => 144,
            '/:height/' => 51
        ));
        // คืนค่า HTML
        return $form->render();
    }
}
