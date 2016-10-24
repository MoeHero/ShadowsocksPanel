<?php
class UserApi {
    /**
     * 注册
     * @param string $username
     * @param string $password
     * @return bool
     */
    public static function register($did, $username, $password) {
        $userInfo = M('tt_user')->select()->where(['username' => $username])->go();
        if(isset($userInfo['uid'])) return false;
        $info = array(
            'did' => $did,
            'regtime' => time(),
            'username' => $username,
            'password' => $password
        );
        M('tt_user')->insert($info)->go();
        return true;
    }

    /**
     * 登录
     * @return bool
     */
    public static function login() {
        if(I('logininfo') || cookie('logininfo')) {
            $logininfo = explode('|', base64_decode(I('logininfo') ?: cookie('logininfo')));
            $result = M('tt_user')->select()->where(['username' => $logininfo[0]])->go();//查询密码
            if($result['password'] == strtoupper($logininfo[1])) {
                $uid = $result['uid'];
                session('uid', $uid);
                session('username', $result['username']);
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * 登出
     */
    public static function logout() {
        cookie('logininfo', null);
        Session::destroy();
    }

    /**
     * 判断是否登录
     * @return bool
     */
    public static function isLogin() {
        if(session('uid') && session('username')) {
            cookie('uid', session('uid'), 1800);
            return true;
        }
        if(cookie('logininfo')) {
            return UserApi::login();
        }
        return false;
    }

    /**
     * 获取用户等级
     * @return int
     */
    public static function getLevel() {
        $user = M('tt_user')->select('level')->where(['uid' => session('uid')])->go();
        return (int)$user['level'];
    }

    /**
     * 获取用户名
     * @return string
     */
    public static function getUid() {
        return session('uid');
    }

    /**
     * 获取用户名
     * @return string
     */
    public static function getUsername() {
        return session('username');
    }
}