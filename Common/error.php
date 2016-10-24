<?php
$GLOBALS['Error'] = array(
    'Unused' => array('state' => -888, 'msg' => 'Unused!'),//未使用
    'Code_error' => array('state' => -999, 'msg' => 'Code error!'),//代码出错

    'Request_error' => array('state' => -1, 'msg' => 'Wrong request!'),//请求错误
    'Connect_database' => array('state' => -2, 'msg' => 'Can not connect to the database!'),//连接数据库失败
    'Parameter_error' => array('state' => -3, 'msg' => 'Parameter error!'),//请求参数错误
    'Query_failure' => array('state' => -4, 'msg' => 'Query failure!'),//查询失败
    'Read_Flie' => array('state' => -5, 'msg' => 'Read file failure!'),//读取文件失败
    'No_permissions' => array('state' => -6, 'msg' => 'No permissions!'),//没有权限访问

);

function getErrorMsg($index, $msg = null) {//获取错误信息
    $error = $GLOBALS['Error'];//从全局变量获取
    if(!$error[$index])//没有对应的则返回错误信息
        return getErrorMsg('Code_error', 'getErrorMsg');
    if($msg) {
        $returnMsg = $error[$index];
        $returnMsg = array_merge_recursive($returnMsg, array('ErrorInfo' => $msg));
        return json_encode($returnMsg);
    } else {
        return json_encode($error[$index]);
    }
}