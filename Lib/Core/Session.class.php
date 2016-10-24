<?php
/**
 * Session类
 */
class Session {
    /**
     * 启动Session
     * @return void
     */
    static public function start() {
        session_start();
    }

    /**
     * 设置Session
     * @param  string $name  Session名称
     * @param  string $value Session值
     * @param  int    $time  超时时间(秒)
     * @return void
     */
    static public function set($name, $value, $time = 1800) {
        $_SESSION[$name] = $value;
        $_SESSION[$name . '_User'] = md5($_SERVER['REMOTE_ADDR']);
        $_SESSION[$name . '_Expires'] = time() + $time;
    }

    /**
     * 判断Session是否存在
     * @param  string $name Session名称
     * @param  int    $time 超时时间(秒)
     * @return bool         是否存在
     */
    static public function has($name, $time = 1800) {
        //检查Session是否已过期
        if(isset($_SESSION[$name]) && isset($_SESSION[$name . '_User']) && $_SESSION[$name . '_User'] == md5($_SERVER['REMOTE_ADDR']) && isset($_SESSION[$name . '_Expires']) && $_SESSION[$name . '_Expires'] > time()) {
            $_SESSION[$name . '_User'] = md5($_SERVER['REMOTE_ADDR']);
            $_SESSION[$name . '_Expires'] = time() + $time;
            return true;
        } else {
            Session::clear($name);
            return false;
        }
    }

    /**
     * 获取Session值
     * @param  string $name Session名称
     * @param  int    $time 超时时间(秒)
     * @return string       Session值
     */
    public static function get($name, $time = 1800) {
        //检查Session是否已过期
        if(isset($_SESSION[$name]) && isset($_SESSION[$name . '_User']) && $_SESSION[$name . '_User'] == md5($_SERVER['REMOTE_ADDR']) && isset($_SESSION[$name . '_Expires']) && $_SESSION[$name . '_Expires'] > time()) {
            $_SESSION[$name . '_User'] = md5($_SERVER['REMOTE_ADDR']);
            $_SESSION[$name . '_Expires'] = time() + $time;
            return $_SESSION[$name];
        } else {
            Session::clear($name);
            return null;
        }
    }

    /**
     * 设置Session Domain
     * @param  string $sessionDomain 域
     * @return string                域
     */
    public static function setDomain($sessionDomain = null) {
        $return = ini_get('session.cookie_domain');
        if(!empty($sessionDomain)) {
            //跨域访问Session
            ini_set('session.cookie_domain', $sessionDomain);
        }
        return $return;
    }

    /**
     * 清除Session值
     * @param  string $name Session名称
     * @return void
     */
    public static function clear($name) {
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_User']);
        unset($_SESSION[$name . '_Expires']);
    }

    /**
     * 重置销毁Session
     * @return void
     */
    public static function destroy() {
        unset($_SESSION);
        if(isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600);
        }
        session_destroy();
    }

    /**
     * 获取或设置Session id
     * @param  string $id Session id
     * @return string     Session id
     */
    public static function sessionid($id = null) {
        return session_id($id);
    }
}
