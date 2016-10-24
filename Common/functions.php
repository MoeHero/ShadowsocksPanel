<?php
/**
 * 公共函数库
 */

/**
 * 友好变量输出
 * @param mixed $var    变量
 * @param bool  $isExit 运行完是否不执行后面的代码
 */
function dump($arr, $isExit = false) {
    echo '<pre>' . print_r($arr, true) . '</pre>';
    $isExit && exit();
}

/**
 * URL组装 支持不同URL模式
 * @param string       $url          URL表达式,Model/Controller/Action#Anchor@Domain?Var1=Value2&Var2=Value2...
 * @param string|array $vars         传入的参数,支持数组和字符串
 * @param bool         $relativepath 是否使用相对路径
 * @return string
 */
function U($url = '', $vars = array(), $relativepath = true) {
    //解析URL
    $info = parse_url($url);
    if(isset($info['fragment'])) {//解析锚点
        $anchor = $info['fragment'];
        if(strpos($anchor, '?') !== false) {//解析参数
            //按照?来分割并分别赋值给anchor和info['query']
            list($anchor, $info['query']) = explode('?', $anchor, 2);
        }
        if(strpos($anchor, '@') !== false) {//解析域名
            //按照@来分割并分别赋值给anchor和domain
            list($anchor, $domain) = explode('@', $anchor, 2);
        }
        $url = isset($info['path']) ? $info['path'] : '';
    } else if(isset($info['path']) && strpos($info['path'], '@') !== false) {//解析域名
        //按照@来分割并分别赋值给url和domain
        list($url, $domain) = explode('@', $info['path'], 2);
    }

    //解析参数
    if(is_string($vars)) {
        parse_str($vars, $vars);//将查询字符串解析为数组
    }
    if(isset($info['query'])) {//解析地址的参数并合并到vars
        parse_str($info['query'], $params);
        $vars = array_merge($params, $vars);//合并到vars里
    }

    //URL组装
    $url = trim($info['path'], '/');//移除两端的/
    $path = explode('/', $url);//将url用/分割成数组并赋值给path
    $var = array();
    //给对应变量赋值
    $var['a'] = !empty($path) ? array_pop($path) : UrlParse::$action;
    $var['c'] = !empty($path) ? array_pop($path) : UrlParse::$controller;
    $var['m'] = !empty($path) ? array_pop($path) : UrlParse::$model;

    $URL_MODEL = 1;//临时 URL模式
    switch($URL_MODEL) {
        case 0://普通模式
            $url = '?' . http_build_query(array_reverse($var));
            if(!empty($vars)) {
                $vars = urldecode(http_build_query($vars));
                $url .= '&' . $vars;
            }
        break;
        case 1://PATHINFO模式
            $url = implode('/', array_reverse($var));
            if(!empty($vars)) {//添加参数
                $vars = urldecode(http_build_query($vars));
                $url .= '?' . $vars;
            }
        break;
    }
    if(isset($anchor)) {
        $url .= '#' . $anchor;
    }
    if($relativepath) {
        $url = '/' . $url;
    } else {
        $url = SERVER_URL . $url;
        $url = (is_ssl() ? 'https://' : 'http://') . $url;
    }
    return $url;
}

/**
 * 读取配置项
 * @param string $key        配置项键值
 * @param string $configpath 配置文件路径
 * @return mixed
 */
function C($key, $configpath = null) {
    if($configpath == null) {
        $configpath = __ROOT__ . APP_NAME . '/' . UrlParse::$model . '/Config/config.php';
    }
    if(file_exists($configpath)) {
        $config = require $configpath;
        if(isset($config[$key])) {
            return $config[$key];
        }
    } else if($configpath != __ROOT__ . '/Config/config.php') {
        C(__ROOT__ . '/Config/config.php');
    }
    return '';
}

/**
 * URL重定向
 * @param string $url  重定向的URL地址
 * @param int    $time 重定向的等待时间(秒)
 * @return void
 */
function redirect($url, $time = 0) {
    if(!headers_sent()) {
        //redirect
        if($time == 0) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
        }
        exit;
    } else {
        exit("<meta http-equiv='Refresh' content='{$time};URL={$url}'>");
    }
}

/**
 * 判断是否是SSL协议
 * @return bool
 */
function is_ssl() {
    if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == '1' || strtolower($_SERVER['HTTPS']) == 'on')) return true;
    if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') return true;
    return false;
}

/**
 * 判断是否为Ajax请求
 * @return bool
 */
function is_ajax() {
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') return true; else return false;
}

/**
 * 通过文件名获取Class名称
 * @param string $filename 文件名(可包含路径)
 * @return string Class名称
 */
function getClassName($filename) {
    $filename = basename($filename);
    preg_match('/(.+?)\\.class\\.php/', $filename, $matches);
    if(count($matches) > 1) return $matches[1];
}

/**
 * 缓存管理
 * @param string $name   缓存名称
 * @param mixed  $value  缓存内容
 * @param int    $expire 有效期(秒)
 * @return mixed
 */
function cache($name, $value = '', $expire = 30) {
    if($value == '') {
        return Cache::get($name);
    } else if($value == null) {
        Cache::remove($name);
    } else {
        Cache::set($name, $value, $expire);
    }
}

/**
 * Session管理
 * @param string $name   Session名称
 * @param mixed  $value  Session内容
 * @param int    $expire 有效期(秒)
 * @return mixed
 */
function session($name, $value = '', $expire = 1800) {
    if($value == '') {
        return Session::get($name, $expire);
    } else if($value == null) {
        Session::clear($name);
    } else {
        Session::set($name, $value, $expire);
    }
}

/**
 * Cookie管理
 * @param string $name   Cookie名称
 * @param mixed  $value  Cookie内容
 * @param int    $expire 有效期(秒)
 * @return mixed
 */
function cookie($name, $value = '', $expire = 3600) {
    if($value === '') {
        if(isset($_COOKIE[$name])) return $_COOKIE[$name]; else return null;
    } else if($value == null) {
        setcookie($name, '', time() - 3600, '/');
    } else {
        setcookie($name, $value, time() + $expire, '/');
    }
}

/**
 * 获取输入参数 支持过滤和默认值
 * I('id', 0); 获取id参数 自动判断get或者post 如不存在则返回默认值0
 * I('post.name', '', 'htmlspecialchars'); 获取$_POST['name']并且使用函数htmlspecialchars
 * I('get.name'); 获取$_GET['name']
 * @param string $name    变量的名称 支持指定类型
 * @param mixed  $default 默认值
 * @param mixed  $filter  参数过滤方法
 * @return mixed
 */
function I($name, $default = null, $filter = null) {
    if(strpos($name, '.')) {//指定参数来源
        list($method, $name) = explode('.', $name, 2);
    } else {//默认为自动判断
        $method = 'param';
    }
    switch(strtolower($method)) {
        case 'get':
            $input =& $_GET;
        break;
        case 'post':
            $input =& $_POST;
        break;
        case 'request':
            $input =& $_REQUEST;
        break;
        case 'session':
            $input =& $_SESSION;
        break;
        case 'cookie':
            $input =& $_COOKIE;
        break;
        case 'server':
            $input =& $_SERVER;
        break;
        case 'globals':
            $input =& $GLOBALS;
        break;
        case 'put':
            parse_str(file_get_contents('php://input'), $input);
        break;
        case 'param':
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input =& $_POST;
                break;
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $input);
                break;
                default:
                    $input =& $_GET;
                break;
            }
        break;
        default:
            return null;
        break;
    }
    $filters = isset($filter) ? $filter : '';
    if(empty($name)) {//获取全部变量
        $data = $input;
        if($filters) {
            $filters = explode(',', $filters);
            foreach($filters as $filter) {
                $data = array_map($filter, $data);//参数过滤
            }
        }
    } else if(isset($input[$name])) {//取值操作
        $data = $input[$name];
        if($filters) {
            $filters = explode(',', $filters);
            foreach($filters as $filter) {
                if(function_exists($filter)) {
                    $data = is_array($data) ? array_map($filter, $data) : $filter($data);//参数过滤
                } else {
                    $data = filter_var($data, is_int($filter) ? filter_id($filter) : $filter);
                    $data = $data ?: isset($default) ? $default : null;
                }
            }
        }
    } else {//变量默认值
        $data = isset($default) ? $default : null;
    }
    return $data;
}

/**
 * 错误处理
 * @param int    $errno   错误级别
 * @param string $errstr  错误信息
 * @param string $errfile 错误文件
 * @param int    $errline 错误行号
 */
function errorHandler($errno, $errstr, $errfile, $errline) {
    echo '<p>ErrorMessage: ' . $errstr . '<br>';
    echo '#1 .' . DIRECTORY_SEPARATOR;
    echo substr(str_replace(substr(__ROOT__, 0, -1), '', $errfile), 1);
    echo ':' . $errline . ' {main}()<br>';
    foreach(debug_backtrace() as $key => $value) {
        if($key < 1) continue;
        echo '#' . ($key + 1) . ' .' . DIRECTORY_SEPARATOR;
        echo substr(str_replace(substr(__ROOT__, 0, -1), '', $value['file']), 1);
        echo ':' . $value['line'] . ' ';
        if(isset($value['class'])) {
            echo $value['class'] . $value['type'];
        }
        echo $value['function'] . '()<br>';
    }
    echo '</p>';
}

/**
 * 判断数组是否为关联数组
 * @param array $arr 待判断的数组
 * @return bool
 */
function is_assoc($arr) {
    return array_keys($arr) !== range(0, count($arr) - 1);
}

/**
 * 获取中间文本
 * @param string $str      带获取的文本
 * @param string $leftStr  左边文本
 * @param string $rightStr 右边文本
 * @return string
 */
function getSubstr($str, $leftStr, $rightStr) {
    $left = strpos($str, $leftStr);
    $right = strpos($str, $rightStr, $left);
    if($left < 0 or $right < $left) return '';
    return substr($str, $left + strlen($leftStr), $right - $left - strlen($leftStr));
}

/**
 * 获取实例化的数据库类
 * @param string $table 表
 * @return DBDriver
 */
function M($table) {
    return new DBDriver($table);
}
