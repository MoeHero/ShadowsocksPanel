<?php
/**
 * 调试类
 */
class Debug {
    public static $debug = false;

    /**
     * 开始调试
     * @return void
     */
    public static function start() {
        if(DEBUG && !is_ajax()) {
            self::$debug = true;

            //开启错误显示 临时
            error_reporting(E_ALL);
            ini_set('display_errors', true);
        }
    }

    /**
     * 输出Debug信息(必须Define过Debug并且为true)
     * @return void
     */
    public static function output() {
        if(self::$debug) {
            $var = array();
            //获取Template实例
            $template = Main::getInstance('Template');
            //获取引用文件
            $files = get_included_files();
            $requsetfile = array();
            foreach($files as $file) {
                $requsetfile[] = $file . ' (' . number_format(filesize($file) / 1024, 2) . ' KBytes)';
            }
            $var += array('requsetfile' => $requsetfile);
            //请求信息
            $var += array(
                'requsetinfo' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ' . $_SERVER['SERVER_PROTOCOL'] . ' ' . $_SERVER['REQUEST_METHOD']
            );
            //运行时间
            $var += array('runtime' => round(microtime(true) - $GLOBALS['_beginTime'], 7) . ' s');
            //使用内存
            $var += array('memory' => self::getMemory());
            echo $template->getTemplate(__ROOT__ . 'Tpl/Debug.tpl', $var);
        }
    }

    public static function offDebugInfo() {
        Debug::$debug = false;
    }

    //获取内存使用情况(包括单位)
    private static function getMemory() {
        if(MEMORY_LIMIT_ON) {
            $memory = memory_get_usage() - $GLOBALS['_beginUseMems'];
            $unit = ' Bytes';
            if($memory > 1024) {
                $memory /= 1024;
                $unit = ' KBytes';
            }
            return round($memory, 3) . $unit;
        } else {
            return 'Not Support';
        }
    }
}