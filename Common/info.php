<?php
$GLOBALS['Info'] = array(
    'Success' => array('state' => 0, 'msg' => 'Success!'),//成功
    'Query' => array('state' => 1, 'msg' => 'Success!', 'Count' => ''),//查询成功
    'Login_successful' => array('state' => 2, 'msg' => 'Login successful!'),//登录成功
    'Login_failed' => array('state' => 3, 'msg' => 'Login failed!'),//登录失败
    'User_notfound' => array('state' => 4, 'msg' => 'User does not exist!'),//用户不存在
    'Exit_successful' => array('state' => 5, 'msg' => 'Exit successful!'),//退出成功
    'Upload_successful' => array('state' => 6, 'msg' => 'File upload successful!'),//文件上传成功
    'Version_number' => array('state' => 7, 'msg' => 'Success!', 'versionnumber' => ''),//版本号查询成功
    'Software_name' => array('state' => 8, 'msg' => 'Success!', 'softwarename' => '', 'softwareid' => ''),//软件名称查询成功
    'Version_info' => array('state' => 9, 'msg' => 'Success!', 'version' => '', 'changelog' => '', 'filename' => '', 'timestamp' => ''),//软件信息查询成功

);

function getMsg($index, $msg = null) {//获取信息
    $info = $GLOBALS['Info'];//从全局变量获取
    if(!$info[$index])//没有对应的则返回错误信息
        return getErrorMsg('Code_error', 'getErrorMsg');
    if($msg) {
        if(is_array($msg)) {//是否为数组
            $returnMsg = $info[$index];
            $key = array_keys($returnMsg);//获取returnMsg的下标
            $msg = array_values($msg);//将下标重置为0开始
            for($i = 2; $i < count($key) && $i < count($msg) + 2; $i++)//添加元素
                $returnMsg[$key[$i]] = $msg[$i - 2];
            return json_encode($returnMsg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $returnMsg = $info[$index];
            $key = array_keys($returnMsg);
            $returnMsg[$key[2]] = $msg;
            return json_encode($returnMsg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    } else {
        return json_encode($info[$index]);
    }
}