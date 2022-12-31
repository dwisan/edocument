<?php
/**
 * @filesource modules/index/models/loginpage.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Index\Loginpage;

use Gcms\Config;
use Gcms\Login;
use Kotchasan\File;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=index-loginpage
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * รับค่าจากฟอร์ม (loginpage.php)
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = array();
        // session, token, can_config, ไม่ใช่สมาชิกตัวอย่าง
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_config')) {
                try {
                    // โหลด config
                    $config = Config::load(ROOT_PATH.'settings/config.php');
                    $config->login_show_title_logo = $request->post('login_show_title_logo')->toBoolean();
                    $config->new_line_title = $request->post('new_line_title')->toBoolean();
                    $config->login_message = $request->post('login_message')->textarea();
                    $config->login_message_style = $request->post('login_message_style')->filter('a-z');
                    foreach (array('login_header_color', 'login_footer_color') as $key) {
                        $config->$key = $request->post($key)->filter('#ABCDEF0-9');
                    }
                    if (empty($ret)) {
                        // อัปโหลดไฟล์
                        $dir = ROOT_PATH.DATA_FOLDER.'images/';
                        foreach ($request->getUploadedFiles() as $item => $file) {
                            if (preg_match('/^file_(bg_image)$/', $item, $match)) {
                                /* @var $file \Kotchasan\Http\UploadedFile */
                                if (!File::makeDirectory($dir)) {
                                    // ไดเรคทอรี่ไม่สามารถสร้างได้
                                    $ret['ret_file_'.$item] = Language::replace('Directory %s cannot be created or is read-only.', DATA_FOLDER.'images/');
                                } elseif ($request->post('delete_'.$match[1])->toBoolean() == 1) {
                                    // ลบ
                                    if (is_file($dir.$match[1].'.png')) {
                                        unlink($dir.$match[1].'.png');
                                    }
                                } elseif ($file->hasUploadFile()) {
                                    if (!$file->validFileExt(array('jpg', 'jpeg', 'png'))) {
                                        // ชนิดของไฟล์ไม่รองรับ
                                        $ret['ret_file_'.$match[1]] = Language::get('The type of file is invalid');
                                    } else {
                                        try {
                                            $file->moveTo($dir.$match[1].'.png');
                                        } catch (\Exception $exc) {
                                            // ไม่สามารถอัปโหลดได้
                                            $ret['ret_file_'.$match[1]] = Language::get($exc->getMessage());
                                        }
                                    }
                                } elseif ($file->hasError()) {
                                    // ข้อผิดพลาดการอัปโหลด
                                    $ret['ret_file_'.$match[1]] = Language::get($file->getErrorMessage());
                                }
                            }
                        }
                    }
                    // save config
                    if (Config::save($config, ROOT_PATH.'settings/config.php')) {
                        // log
                        \Index\Log\Model::add(0, 'index', 'Save', '{LNG_Settings} {LNG_Login page}', $login['id']);
                        // คืนค่า
                        $ret['alert'] = Language::get('Saved successfully');
                        $ret['location'] = 'reload';
                        // เคลียร์
                        $request->removeToken();
                    } else {
                        // ไม่สามารถบันทึก config ได้
                        $ret['alert'] = Language::replace('File %s cannot be created or is read-only.', 'settings/config.php');
                    }
                } catch (\Kotchasan\InputItemException $e) {
                    $ret['alert'] = $e->getMessage();
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }
}
