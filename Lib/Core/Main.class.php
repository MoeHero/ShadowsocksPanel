<?php
/**
 * 主类
 */
class Main {
    //记录引用过的实例
    static private $instance = array('Main');

    /**
     * 开始运行程序
     */
    static public function start() {
        //设置异常处理函数
        set_error_handler('errorHandler', E_ERROR | E_WARNING | E_PARSE);

        //启动session
        Session::start();

        //启动debug
        Debug::start();

        //解析地址
        UrlParse::parse();

        //定义缓存路径
        define('CAHCE_PATH', APP_NAME . '/' . UrlParse::$model . '/Cache/');
        //定义PUBLIC路径
        define('__PUBLIC__', APP_NAME . '/' . UrlParse::$model . "/Public");
        //定义用户自定义类库路径
        define('USER_LIB_PATH', __ROOT__ . APP_NAME . '/' . UrlParse::$model . '/Lib/');

        //将类库目录添加到引用目录
        Main::setIncludePaths(array(USER_LIB_PATH));

        //执行
        UrlParse::go();

        //输出Debug信息
        Debug::output();
    }

    /**
     * 引入指定文件
     * @param string $name 名称
     * @param string $path 路径
     */
    static public function import($name, $path = '') {
        self::requireFile($path . $name . '.class.php');//引用文件
    }

    /**
     * 获取指定实例
     * @param string $name 名称
     * @param string $path 路径
     * @return object 实例化的对象
     */
    static public function getInstance($name, $path = '') {
        //引入指定文件
        self::import($name, $path);
        //如果类存在则返回新实例
        if(class_exists($name)) return new $name();
        return null;
    }

    /**
     * 引用指定文件
     * @param string $filename 指定文件名称
     */
    static public function requireFile($filename) {
        //获取无扩展名的文件名称
        $name = preg_replace('/.+[\\\\\/](.+)\\.class\\.php/is', '\\1', $filename);
        //如果未引用过指定文件则记录并引用
        if(!in_array($name, self::$instance)) {
            require $filename;
            self::$instance[] = $name;
        }
    }

    /**
     * 引用指定路径内所有文件
     * @param string $path 路径
     */
    static public function requireFiles($path) {
        if(is_dir($path)) {
            //如果是目录则遍历目录所有文件并引用
            foreach(glob($path . '/*.*') as $file) {
                self::requireFile($file);
            }
        }
    }

    /**
     * 批量添加指定引用路径
     * @param string $paths 路径数组
     */
    static public function setIncludePaths($paths) {
        //遍历数组添加引用目录
        foreach($paths as $path) {
            set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        }
    }

    /**
     * 写缓存文件
     * @param string $filename 文件名称
     * @param string $content  文件内容
     * @return string 文件路径
     */
    static public function writeCacheFile($filename, $content) {
        $filename = __ROOT__ . CAHCE_PATH . $filename;
        if(!is_dir(__ROOT__ . CAHCE_PATH)) {
            mkdir(__ROOT__ . CAHCE_PATH, 0755, true);
        }
        file_put_contents($filename, $content);
        return $filename;
    }
}

/**
 * 自动加载类
 */
function __autoload($classname) {
    Main::import($classname);
}
