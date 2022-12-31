<?php
/**
 * @filesource Gcms/Login.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Gcms;

use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * คลาสสำหรับตรวจสอบการ Login
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Login extends \Kotchasan\Login
{
    /**
     * ฟังก์ชั่นตรวจสอบการ login และบันทึกการเข้าระบบ
     * เข้าระบบสำเร็จคืนค่าแอเรย์ข้อมูลสมาชิก, ไม่สำเร็จ คืนค่าข้อความผิดพลาด
     *
     * @param array $params ข้อมูลการ login ที่ส่งมา $params = array('username' => '', 'password' => '');
     *
     * @return string|array
     */
    public function checkLogin($params)
    {
        // ตรวจสอบสมาชิกกับฐานข้อมูล
        $login_result = self::checkMember($params);
        if (is_array($login_result)) {
            // ip ที่ login
            $ip = self::$request->getClientIp();
            // current session
            $session_id = session_id();
            // ลบ password
            unset($login_result['password']);
            unset($login_result['salt']);
            // เวลานี้
            $mktime = time();
            if (self::$request->post('login_username')->exists()) {
                // จำการเข้าระบบ (มาจากการ submit)
                $login_remember = self::$request->post('login_remember')->toBoolean();
                setcookie('login_remember', $login_remember, $mktime + 2592000, '/', HOST, HTTPS, true);
            } else {
                $login_remember = self::$request->cookie('login_remember')->toBoolean();
            }
            if ((self::$cfg->member_only && $session_id != $login_result['session_id']) || empty($login_result['token']) || $mktime - $login_result['lastvisited'] > 86400) {
                // อัปเดต token
                $login_result['token'] = \Kotchasan\Password::uniqid(40);
                $save = array('token' => $login_result['token']);
            }
            if ($login_remember) {
                // บันทึก token สำหรับจำการเข้าระบบ
                setcookie('login_token', $login_result['token'], $mktime + 2592000, '/', HOST, HTTPS, true);
            } else {
                setcookie('login_token', '', $mktime, '/', HOST, HTTPS, true);
            }
            if ($session_id != $login_result['session_id']) {
                // อัปเดตการเยี่ยมชม
                ++$login_result['visited'];
                $save = array(
                    'session_id' => $session_id,
                    'visited' => $login_result['visited'],
                    'lastvisited' => $mktime,
                    'ip' => $ip,
                    'token' => $login_result['token']
                );
            }
            if (!empty($save)) {
                // บันทึกการเข้าระบบ
                \Kotchasan\Model::createQuery()
                    ->update('user')
                    ->set($save)
                    ->where((int) $login_result['id'])
                    ->execute();
                // log
                \Index\Log\Model::add($login_result['id'], 'index', 'User', '{LNG_Login}', $login_result['id']);
            }
        }
        return $login_result;
    }

    /**
     * ฟังก์ชั่นตรวจสอบสมาชิกกับฐานข้อมูล
     * คืนค่าข้อมูลสมาชิก (array) ไม่พบคืนค่าข้อความผิดพลาด (string)
     *
     * @param array $params
     *
     * @return array|string
     */
    public static function checkMember($params)
    {
        // query Where
        $where = array();
        foreach (self::$cfg->login_fields as $field) {
            $k = $field == 'email' || $field == 'username' ? 'username' : $field;
            $where[$k] = array("U.{$k}", $params['username']);
        }
        if (isset($params['token']) && strlen($params['token']) == 40) {
            $where['token'] = array("U.token", $params['token']);
        }
        $query = \Kotchasan\Model::createQuery()
            ->select('U.*')
            ->from('user U')
            ->where(array_values($where), 'OR')
            ->order('U.status DESC')
            ->toArray();
        $login_result = null;
        foreach ($query->execute() as $item) {
            if (isset($params['password']) && $item['password'] === sha1(self::$cfg->password_key.$params['password'].$item['salt'])) {
                // ตรวจสอบรหัสผ่าน
                $login_result = $item;
            } elseif (isset($params['token']) && $params['token'] === $item['token'] && time() - $item['lastvisited'] < self::$cfg->remember_expired) {
                // ตรวจสอบ token
                $login_result = $item;
            }
            if ($login_result && ($login_result['id'] == 1 || ($login_result['active'] == 1 && empty($login_result['activatecode'])))) {
                // permission
                $login_result['permission'] = empty($login_result['permission']) ? array() : explode(',', trim($login_result['permission'], " \t\n\r\0\x0B,"));
                break;
            } else {
                $login_result = null;
            }
        }
        if ($login_result === null && !empty($params['username'])) {
            // Authentication แบบอื่นๆ
            $authentication_methods = array('apiAuthentication');
            foreach ($authentication_methods as $className) {
                $login_result = self::$className($params, isset($item) ? $item : null);
                if (is_array($login_result)) {
                    // เข้าระบบสำเร็จ
                    return $login_result;
                    break;
                }
            }
            if (isset($item)) {
                if (self::$cfg->new_members_active == 0 && $item['active'] == 0) {
                    // ยังไม่ได้ Approve
                    self::$login_input = 'username';
                    return Language::get('Your account has not been approved, please wait or contact the administrator.');
                } elseif (empty($item['activatecode'])) {
                    // password ไม่ถูกต้อง
                    self::$login_input = 'password';
                    return Language::replace('Invalid :name', array(':name' => Language::get('Password')));
                } else {
                    // ยังไม่ได้ verfied email
                    self::$login_input = 'username';
                    return Language::get('You haven&#039;t verified your email address. Please check your email. and click the link in the email');
                }
            } else {
                // user ไม่ถูกต้อง
                self::$login_input = 'username';
                return Language::get('not a registered user');
            }
        }
        return $login_result;
    }

    /**
     * ตรวจสอบข้อมูลกับ API
     * คืนค่าแอเรย์ ถ้าสำเร็จ
     * ไม่สำเร็จคืนค่า null
     *
     * @param array $params
     * @param array $user
     *
     * @return array|null
     */
    private static function apiAuthentication($params, $user)
    {
        if (
            !empty(self::$cfg->api_secret) &&
            !empty(self::$cfg->api_token) &&
            !empty(self::$cfg->api_url) &&
            !empty($params['username']) &&
            !empty($params['password']) &&
            stripos(self::$cfg->api_url, HOST) === false
        ) {
            // ตรวจสอบกับ API
            $login_result = \Gcms\Api::login($params['username'], $params['password']);
            if (is_array($login_result) && isset($login_result['code']) && $login_result['code'] == 0) {
                // Model
                $model = \Kotchasan\Model::create();
                if ($user === null) {
                    // login ผ่าน API สำเร็จ ลงทะเบียน user ใหม่
                    $user = array(
                        'username' => $login_result['email'],
                        'password' => $params['password'],
                        'name' => $login_result['name'],
                        'social' => 0,
                        'visited' => 1,
                        'lastvisited' => time(),
                        'status' => 0,
                        'token' => \Kotchasan\Password::uniqid(40),
                        'active' => 1
                    );
                    // คืนค่าข้อมูล login
                    return \Index\Register\Model::execute($model, $user, array());
                } else {
                    // อัปเดทรหัสผ่าน
                    $salt = \Kotchasan\Password::uniqid();
                    $model->db()->update($model->getTableName('user'), $user['id'], array(
                        'salt' => $salt,
                        'password' => sha1(self::$cfg->password_key.$params['password'].$salt)
                    ));
                    // permission
                    $user['permission'] = empty($user['permission']) ? array() : explode(',', trim($user['permission'], " \t\n\r\0\x0B,"));
                    // คืนค่าข้อมูลจากฐานข้อมูลสมาชิกที่พบ
                    return $user;
                }
            }
        }
        return null;
    }

    /**
     * ตรวจสอบความสามารถในการตั้งค่า
     * คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าไม่สามารถทำรายการได้คืนค่า null
     *
     * @param array        $login
     * @param array|string $permission
     * @param bool        $admin true (default) รวมแอดมินด้วย, false ไม่รวมแอดมิน
     *
     * @return array|null
     */
    public static function checkPermission($login, $permission, $admin = true)
    {
        if (!empty($login)) {
            if ($admin && $login['status'] == 1) {
                // แอดมิน
                return $login;
            } elseif (!empty($permission)) {
                if (is_array($permission)) {
                    foreach ($permission as $item) {
                        if (in_array($item, $login['permission'])) {
                            // มีสิทธิ์
                            return $login;
                        }
                    }
                } elseif (in_array($permission, $login['permission'])) {
                    // มีสิทธิ์
                    return $login;
                }
            }
        }
        // ไม่มีสิทธิ
        return null;
    }

    /**
     * ฟังก์ชั่นส่งอีเมลลืมรหัสผ่าน
     *
     * @param Request $request
     *
     * @return void
     */
    public function forgot(Request $request)
    {
        // ค่าที่ส่งมา
        $username = $request->post('login_username')->url();
        if (empty($username)) {
            if ($request->post('action')->toString() === 'forgot') {
                self::$login_message = Language::get('Please fill in');
            }
        } else {
            self::$login_params['username'] = $username;
            // ชื่อฟิลด์สำหรับตรวจสอบอีเมล ใช้ฟิลด์แรกจาก config
            $field = reset(self::$cfg->login_fields);
            // ถ้าชื่อฟิลด์เป็น email ให้ใช้ username แทน
            $field = $field == 'email' ? 'username' : $field;
            // Model
            $model = new \Kotchasan\Model();
            // ตาราง user
            $table = $model->getTableName('user');
            // Database
            $db = $model->db();
            // ค้นหา username
            $search = $db->first($table, array(
                array($field, $username),
                array('social', 0),
                array('active', 1)
            ));
            if ($search === false) {
                self::$login_message = Language::get('not a registered user');
            } else {
                // ขอรหัสผ่านใหม่
                $err = \Index\Forgot\Model::execute($search->id, $search->$field);
                if ($err == '') {
                    // คืนค่า
                    self::$login_message = Language::get('Your message was sent successfully');
                    self::$request = $request->withQueryParams(array('action' => 'login'));
                } else {
                    // ไม่สำเร็จ
                    self::$login_message = $err;
                }
            }
        }
    }

    /**
     * ฟังก์ชั่นตรวจสอบว่า เป็นสมาชิกตัวอย่างหรือไม่
     * คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าไม่ใช่สมาชิกตัวอย่าง, null ถ้าเป็นสมาชิกตัวอย่างและเปิดโหมดตัวอย่างไว้
     *
     * @param array|null $login
     *
     * @return array|null
     */
    public static function notDemoMode($login)
    {
        return $login && !empty($login['social']) && self::$cfg->demo_mode ? null : $login;
    }
}
