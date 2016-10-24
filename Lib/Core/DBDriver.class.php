<?php
class DBDriver {
    private $db_config;//数据库配置
    private $lastsql = '';//最后一次执行的sql语句
    private $pre_data = array();//预编译参数
    private $fetch_type = PDO::FETCH_ASSOC;//查询语句返回的数据集类型
    private $sql_stmt = '';//组装的sql语句
    private $query_type = '';//当前正在执行语句类型
    private $error_info = null;//错误信息
    private $log_path = './sql-error.log';//日志存储路径
    private $pdo = null;//数据库连接
    private $table = '';//操作的数据库

    /**
     * 构造函数
     * @param array $config mysql数据库连接信息
     */
    public function __construct($table, $config = array()) {
        $this->db_config = array(
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'admin_moehero',
            'password' => '691229',
            'dbname' => 'admin_moehero',
            'charset' => 'utf8'
        );
        $this->db_config = array_merge($this->db_config, $config);
        $this->table = $table;
        try {
            $dsn = 'mysql:host=' . $this->db_config['host'] . ';port=' . $this->db_config['port'] . ';dbname=' . $this->db_config['dbname'];
            $this->pdo = new PDO($dsn, $this->db_config['username'], $this->db_config['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->db_config['charset']));
            $this->pdo->exec('set names ' . $this->db_config['charset']);
        } catch(PDOException $e) {
            echo '<p style="color:red">db connect has error!</p><br/><b>错误原因:</b>' . $e->getMessage() . '<br/><b>错误报告:</b>';
            echo '<pre>';
            var_dump($e);
            echo '</pre>';
            die;
        }
    }

    /**
     * 执行一条SQL语句,适用于比较复杂的SQL语句
     * 如果是增删改查的语句,建议使用下面进一步封装的语句
     * @param string $sql
     * @param array  $data
     * @return object 执行后的结果对象
     */
    public function queryObj($sql, $data = array()) {
        $this->lastsql = $sql;
        $this->addData($data);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->pre_data) ? true : $this->error_info = $stmt->errorInfo();
        return $stmt;
    }

    /**
     * 查询语句,返回单条结果
     * @param string $sql
     * @param array  $data
     * @param string $type
     * @return mixed array|null 一维数组
     */
    public function queryOne($sql, $data = array(), $type = '') {
        $type = !empty($type) ? $type : $this->fetch_type;
        return $this->queryObj($sql, $data)->fetch($type);
    }

    /**
     * 查询语句,返回所有结果
     * @param string $sql
     * @param array  $data
     * @param string $type
     * @return mixed array|null 二维数组
     */
    public function queryAll($sql, $data = array(), $type = '') {
        $type = !empty($type) ? $type : $this->fetch_type;
        return $this->queryObj($sql, $data)->fetchAll($type);
    }

    /**
     * 执行结果为影响到的行数,只能是insert/delete/update语句
     * @param string $sql
     * @param array  $data
     * @return int
     */
    public function querySql($sql, $data = array()) {
        return $this->queryObj($sql, $data)->rowCount();
    }

    /**
     * 查询链式起点
     * @param string|array $field
     */
    public function select($field = '*') {
        $this->sql_stmt = 'SELECT ';
        $this->query_type = 'select';
        $_field = $field;
        if(is_array($field)) {
            $_field = '';
            foreach($field as $value) $_field .= $value . ',';
            $_field = trim($_field, ',');
        }
        $this->sql_stmt .= $_field . ' FROM `' . $this->table . '`';
        return $this;
    }

    /**
     * 插入链式起点
     * @param array $data 键值对数组,如array('name'=>'test','age'=>18);其中键为表字段,值为数值
     */
    public function insert($data) {
        $this->sql_stmt = 'INSERT INTO `' . $this->table . '` ';
        $this->query_type = 'insert';
        $_key = $_value = '';
        if(is_assoc($data)) {
            foreach($data as $key => $value) $_value .= $this->addData(array($value)) . ',';
            $_value = trim($_value, ',');
            $_key = implode(',', array_keys($data));
        } else {
            foreach($data as $value) $_value .= $this->addData(array($value)) . ',';
            $_value = trim($_value, ',');
        }
        $this->sql_stmt .= (!empty($_key) ? '(' . $_key . ')' : '') . ' VALUES (' . $_value . ')';
        return $this;
    }

    /**
     * 更新链式起点
     * @param array $data 键值对数组,如array('name'=>'test','age'=>18);其中键为表字段,值为数值
     */
    public function update($data) {
        $this->sql_stmt = 'UPDATE `' . $this->table . '` ';
        $this->query_type = 'update';
        $_data = '';
        foreach($data as $key => $value) {
            $_data .= $key . '=' . $this->addData(array($key => $value)) . ',';;
        }
        $_data = trim($_data, ',');
        $this->sql_stmt .= 'SET ' . $_data;
        return $this;
    }

    /**
     * 删除链式起点
     */
    public function delete() {
        $this->sql_stmt = 'DELETE FROM `' . $this->table . '`';
        $this->query_type = 'delete';
        return $this;
    }

    /**
     * 查询数量链式起点
     * @param string $field
     */
    public function count($field = '*') {
        $this->sql_stmt = 'SELECT COUNT(';
        $this->query_type = 'count';
        $_field = $field;
        if(is_array($field)) {
            $_field = '';
            foreach($field as $value) $_field .= $value . ',';
            $_field = trim($_field, ',');
        }
        $this->sql_stmt .= $_field . ') AS result FROM `' . $this->table . '`';
        return $this;
    }

    /**
     * 查询平均值链式起点
     * @param string $field
     */
    public function avg($field = '*') {
        $this->sql_stmt = 'SELECT AVG(';
        $this->query_type = 'avg';
        $_field = $field;
        if(is_array($field)) {
            $_field = '';
            foreach($field as $value) $_field .= $value . ',';
            $_field = trim($_field, ',');
        }
        $this->sql_stmt .= $_field . ') AS result FROM `' . $this->table . '`';
        return $this;
    }

    /**
     * 链式执行结点,使用链式方法必须以此结尾
     * @param bool   $multi      返回数据是多条还是一条,只适用于select查询,默认一条
     * @param array  $data       占位符对应参数
     * @param string $fetch_type 返回数据集的格式,默认索引
     * @return mixed
     */
    public function go($multi = false, $data = array(), $fetch_type = '') {
        switch($this->query_type) {
            case 'select':
                if($multi) {
                    return $this->queryAll($this->sql_stmt, $data, $fetch_type);
                } else {
                    return $this->queryOne($this->sql_stmt, $data, $fetch_type);
                }
            break;
            case 'insert':
            case 'delete':
                return $this->querySql($this->sql_stmt, $data) > 0 ? true : false;
            break;
            case 'update':
                $r = $this->queryObj($this->sql_stmt, $data)->errorInfo();
                return isset($r[2]) ? false : true;
            break;
            case 'count':
            case 'avg':
                $r = $this->queryOne($this->sql_stmt, $data, $fetch_type);
                return isset($r['result']) ? (int)$r['result'] : 0;
            break;
        }
    }

    public function where($where = '', $data = array()) {
        $this->sql_stmt .= ' WHERE ';
        if(is_array($where)) {
            $this->sql_stmt .= $this->where_array($where);
        } else {
            $this->sql_stmt .= $where;
        }
        foreach($data as $key => $value) $this->addData(array($key => $value));
        return $this;
    }

    private function where_array($where = array(), $operator = '') {
        foreach($where as $key => $value) {
            if($operator != '') $operator = ' ' . $operator . ' ';
            if(strpos($key, '#') !== false) $key = trim(substr($key, 0, -(strlen($key) - strpos($key, '#'))));
            if(strtoupper($key) == 'AND' || strtoupper($key) == 'OR') {
                $temp = '';
                foreach($value as $_key => $_value) {
                    $temp .= $this->where_array(array($_key => $_value), strtoupper($key));
                }
                return '(' . trim(trim($temp, ' OR '), ' AND ') . ')' . $operator;
            }
            $_operator = getSubstr($key, '[', ']');
            $key = str_replace('[' . $_operator . ']', '', $key);
            switch($_operator) {
                case '<':
                case '>':
                case '<=':
                case '>=':
                    $this->addData(array($key => $value));
                    return $key . $_operator . ':' . $key . $operator;
                break;
                case '!':
                    if(is_null($value)) {
                        return $key . ' IS NOT NULL' . $operator;
                    } else if(is_array($value)) {
                        $temp = $key . ' NOT IN (';
                        foreach($value as $_value) $temp .= $this->addData(array($key => $_value)) . ',';

                        $temp = trim($temp, ',') . ')' . $operator;
                        return $temp;
                    } else {
                        return $key . '!=' . $this->addData(array($key => $value)) . $operator;
                    }
                break;
                case '<>':
                case '><':
                    if(is_array($value) && count($value) == 2) {
                        return $key . ($_operator == '><' ? ' NOT' : '') . ' BETWEEN ' . $this->addData(array($key => $value[0])) . ' AND ' . $this->addData(array($key => $value[1])) . $operator;
                    }
                break;
                case '~':
                case '!~':
                    if(is_array($value)) {
                        $temp = '';
                        foreach($value as $_value) {
                            if(strpos($_value, '%') === false && strpos($_value, '_') === false && strpos($_value, '[') === false) $_value = '%' . $_value . '%';
                            $temp .= $key . ($_operator == '!~' ? ' NOT' : '') . ' LIKE ' . $this->addData(array($key => $_value)) . ' OR ';
                        }
                        $temp = trim($temp, ' OR ') . $operator;
                        return $temp;
                    } else {
                        if(strpos($value, '%') === false && strpos($value, '_') === false && strpos($value, '[') === false) $value = '%' . $value . '%';
                        return $key . ($_operator == '!~' ? ' NOT' : '') . ' LIKE ' . $this->addData(array($key => $value));
                    }
                break;
                default:
                    if(is_null($value)) {
                        return $key . ' IS NULL' . $operator;
                    } else if(is_array($value)) {
                        $temp = $key . ' IN (';
                        for($i = 0; $i < count($value); $i++) $temp .= $this->addData(array($key => $value[$i])) . ',';
                        return trim($temp, ',') . ')' . $operator;
                    } else {
                        return $key . '=' . $this->addData(array($key => $value)) . $operator;
                    }
                break;
            }
        }
        return '';
    }

    public function order($order) {
        $this->sql_stmt .= ' ORDER BY ' . $order;
        return $this;
    }

    public function group($group) {
        $this->sql_stmt .= ' GROUP BY ' . $group;
        return $this;
    }

    public function limit($limit) {
        $this->sql_stmt .= ' LIMIT ' . implode(',', $limit);
        return $this;
    }

    /**
     * 获取正在执行的sql语句
     * @param bool $real_query_string 是否返回真实执行的sql语句,默认是
     * @return string
     */
    public function getLastSql($real_query_string = true) {
        return $real_query_string ? $this->realQuery($this->lastsql, $this->pre_data) : $this->lastsql;
    }

    /**
     * 设置查询结果集类型
     * @param string $type PDO::FETCH_ASSOC | FETCH_BOTH | PDO::FETCH_NUM
     */
    public function setFetchType($type) {
        $this->fetch_type = $type;
    }

    /**
     * 获取错误信息
     * @param bool $writeLog 是否将信息写入文件,默认否
     * @return mixed
     */
    public function getErrorInfo($writeLog = false) {
        return $writeLog ? $this->log() : array_merge($this->error_info, array('sql' => $this->getLastSql()));
    }

    //获取真实执行的查询语句
    private function realQuery($q, $r) {
        $i = 0;
        $ret = preg_replace_callback('/:([0-9a-z_]+)|\?+/i', function($m) use ($r, &$i) {
            $k = array_keys($r);
            $v = $m[0] == '?' ? $r[$i] : (substr($k[$i], 0, 1) == ':' ? $r[$m[0]] : $r[$m[1]]);
            if($v === null) {
                return 'NULL';
            }
            if(!is_numeric($v)) {
                $v = "'{$v}'";
            }
            $i++;
            return $v;
        }, $q);
        return $ret;
    }

    //记录日志
    private function log() {
        try {
            $log = '[' . date('Y-m-d H:i:s') . ']\n';
            $log .= '执行语句:' . $this->getLastSql() . '\n';
            $log .= '错误代码:' . $this->error_info[0] . '\n';
            $log .= '错误类型:' . $this->error_info[1] . '\n';
            $log .= '错误描述:' . $this->error_info[2] . '\n\n';
            file_put_contents($this->log_path, $log, FILE_APPEND);
            return '';
        } catch(Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    private function addData($data) {
        foreach($data as $key => $value) {
            if(is_assoc($data)) {
                $_key = ':' . $key;
                if(!isset($this->pre_data[$_key])) {
                    $this->pre_data = array_merge($this->pre_data, array($_key => $value));
                    return $_key;
                }
                $i = 1;
                $_key = ':' . $key . '_' . $i;
                while(isset($this->pre_data[$_key])) {
                    $i++;
                    $_key = ':' . $key . '_' . $i;
                }
                $this->pre_data = array_merge($this->pre_data, array($_key => $value));
                return $_key;
            } else {
                $this->pre_data = array_merge($this->pre_data, array($value));
                return '?';
            }
        }
    }
}