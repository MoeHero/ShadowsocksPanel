<?php
/**
 * 缓存管理类
 */
class Cache {
    //缓存内容暂存数组
    private static $cache = array();
    //缓存文件路径
    private static $cacheFilename = CAHCE_PATH . 'Cache.cache';

    //读取缓存文件
    private static function read() {
        static $isopen = false;
        if(!$isopen && file_exists(self::$cacheFilename)) {
            self::$cache = unserialize(require self::$cacheFilename);
            $isopen = true;
        }
    }

    //写入缓存文件
    private static function write() {
        $content = '<?php /*自动生成缓存文件,请勿删除*/';
        $content .= 'return \'' . serialize(self::$cache) . '\';';
        file_put_contents(self::$cacheFilename, $content);
    }

    /**
     * 获取缓存内容
     * @param  string $name 缓存名称
     * @return mixed
     */
    public static function get($name) {
        self::read();
        if(isset(self::$cache[$name]) && (self::$cache[$name]['expire'] > time() || self::$cache[$name]['expire'] == -1)) {
            return self::$cache[$name]['data'];
        } else {
            self::remove($name);
            return null;
        }
    }

    /**
     * 设置缓存内容
     * @param string $name    缓存名称
     * @param mixed  $content 缓存内容
     * @param int    $expire  有效期(秒)
     */
    public static function set($name, $content, $expire) {
        self::$cache[$name]['data'] = $content;
        self::$cache[$name]['expire'] = $expire == -1 ? $expire : time() + $expire;
        self::write();
    }

    /**
     * 删除缓存内容
     * @param string $name 缓存名称
     */
    public static function remove($name) {
        unset(self::$cache[$name]);
        self::write();
    }
}