<?php
//记录开始运行时间
$GLOBALS['_beginTime'] = microtime(true);
//记录内存初始使用
define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) {
    $GLOBALS['_beginUseMems'] = memory_get_usage();
}

//DEBUG开关
define('DEBUG', false);
//设置APP_NAME
define('APP_NAME', 'ShadowsocksPanel');
//设置SERVER_URL
define('SERVER_URL', $_SERVER['HTTP_HOST'] . '/');
//设置__ROOT__ 临时
define('__ROOT__', dirname(__FILE__) . '/');

//设置时区
date_default_timezone_set('Asia/Shanghai');

//定义部分类库目录
define('CORE_PATH', __ROOT__ . 'Lib/Core/');//系统核心类库目录
define('TPL_PATH', __ROOT__ . 'Lib/Template/');//系统模版处理类库目录
define('COMMON_PATH', __ROOT__ . 'Common/');//系统公共文件目录
define('CLASS_PATH', __ROOT__ . 'Class/');//公共类目录
//引用主类
require CORE_PATH . 'Main.class.php';
//将类库目录添加到引用目录
Main::setIncludePaths(array(CORE_PATH, TPL_PATH, COMMON_PATH, CLASS_PATH));
//引用公共文件
Main::requireFile(COMMON_PATH . 'functions.php');//公共函数文件

//开始运行主程序
Main::start();
