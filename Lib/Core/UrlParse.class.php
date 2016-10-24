<?php

/**
 * Url解析类
 */
class UrlParse {
    /**
     * 记录model值
     * @var string
     */
    static public $model = 'Home';
    /**
     * 记录controller值
     * @var string
     */
    static public $controller = 'Index';
    /**
     * 记录action值
     * @var string
     */
    static public $action = 'index';

    /**
     * 解析Url地址
     */
    static public function parse() {
        //获取PATH_INFO
        $PATH_INFO = @explode('/', $_SERVER['PATH_INFO']);
        //设置Model、Controller、Action
        self::$model = @$PATH_INFO[1] ?: 'Home';
        self::$controller = @$PATH_INFO[2] ?: 'Index';
        self::$action = @$PATH_INFO[3] ?: 'index';
    }

    /**
     * 执行对应控制器
     */
    static public function go() {
        $model = self::$model;
        $controller = self::$controller;
        $action = self::$action;

        $controllerFilename = __ROOT__ . APP_NAME . "/{$model}/Controller/{$controller}Controller.class.php";
        //控制器不存在则返回404
        if(!file_exists($controllerFilename)) {
            header('HTTP/1.1 404 Not Found');
            echo 'Not Found';
            //require './404.html';
            exit;
        }

        //引入项目自定义的Class、Lib以及functions
        Main::requireFile(__ROOT__ . APP_NAME . "/{$model}/Common/functions.php");
        Main::requireFiles(__ROOT__ . APP_NAME . "/{$model}/Class");
        Main::requireFiles(__ROOT__ . APP_NAME . "/{$model}/Lib");

        //引用对应控制器
        require $controllerFilename;
        $classname = $controller . 'Controller';

        //检查是否包含指定Controller和对应的Action
        if(class_exists($classname) && method_exists($classname, $action)) {
            //实例化对应类并调用方法
            $class = new $classname();
            $class->$action();
        } else {
            header('HTTP/1.1 666 Error');
            echo 'Error! Not a class or cannot find action';
            //require './666.html';
            exit;
        }
    }
}
