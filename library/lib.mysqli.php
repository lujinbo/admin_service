<?php
/**
 * mysql操作类
 */
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');

class Lib_Mysqli
{
    //链接id
    private $linkId;
    private $db_config;

    /**
     * @param array $config config配置
     */
    public function __construct($config)
    {
        $this->db_config = $config;
        if(is_array($config)) {
            $this->connect($config[0],$config[1],$config[2],$config[3]);
        } else {
            $this->errorLog("初始化数据库错误".json_encode($config));
        }
    }

    /**
     * 数据库链接
     * @param string $dbhost host地址
     * @param string $dbuser	用户
     * @param string $dbpwd	 密码
     * @param string $dbname 数据库名
     * @param int 	  $pconnect 是否长链接
     * @param string  $charset 字符集
     */
    public function connect($dbhost,$dbuser,$dbpwd,$dbname,$pconnect=0,$charset='UTF8')
    {
        if($pconnect == 0) {
            $this->linkId = mysqli_connect($dbhost,$dbuser,$dbpwd,$dbname);
            if(!$this->linkId) $this->errorLog("数据库连接失败");
        } else {
            $this->linkId = mysqli_connect('p:'.$dbhost,$dbuser,$dbpwd);
            if(!$this->linkId) $this->errorLog("数据库持久连接失败");
        }
        mysqli_query($this->linkId,"set names ".$charset);
    }

    /**
     * 执行语句
     * @param $strsql
     * @return bool|resource
     */
    public function query($strsql)
    {
        try{
            if(!mysqli_ping($this->linkId)) {
                $this->errorLog("reconnect db for ping failed");
                $this->reconnect();
            }
            $sqlData = mysqli_query($this->linkId,$strsql);
        }catch (\Exception $e){
            $sqlData = false;
            $this->errorLog("****** Exception  mysql_query sql is  :".$strsql);
            $this->errorLog("****** Exception  msg is  :".$e->getMessage());
        }
        if(!$sqlData) {
            $this->errorLog("sql_exec_failed:".$strsql);
        }

        return $sqlData;
    }

    /**
     *  获取一条记录
     * @param string $strsql 执行的语句
     * @param int $type
     * @return array|bool
     */
    public function getOne($strsql,$type=MYSQLI_ASSOC)
    {
        $data = $this->query($strsql);
        if(empty($data))
            return false;
        $ret = mysqli_fetch_array($data,$type);
        return $ret;
    }

    /**
     * 获取全部记录
     * @param string $strsql 执行的语句
     * @param $strsql
     * @param int $type
     * @return array|bool
     */
    public function getAll($strsql,$type=MYSQLI_ASSOC)
    {
        $data = $this->query($strsql);
        if(empty($data))
            return false;
        $i = 0;
        $ret = array();
        while ($row = mysqli_fetch_array($data,$type)) {
            $ret[$i] = $row;
            $i++;
        }
        return $ret;
    }

    /**
     * 插入一条记录
     * @param string $table 表名
     * @param array  $data 要插入的数据
     * @return bool
     */
    public function insert($table,$data)
    {
        $field = "";
        $value = "";
        if(!is_array($data) || count($data) <=0) {
            $this->errorLog("没有要插入的数据");
            return false;
        }
        foreach ($data as $key=>$val) {
            $val = mysqli_real_escape_string($this->linkId, $val);
            $field .= "$key,";
            $value .= "'$val',";
        }
        $field = substr($field,0,-1);
        $value = substr($value, 0,-1);
        $strsql = "INSERT INTO $table($field) VALUES ($value)";
        if(!$this->query($strsql)) return false;
        return true;
    }

    /**
     * 获取最后插入的id
     */
    public function insertId()
    {
        return mysqli_insert_id($this->linkId);
    }

    /**
     * 更新记录
     * @param string $table 表名
     * @param array $data 更新的数据
     * @param string $condition 条件
     * @return int 返回影响的记录行数
     */
    public function update($table,$data,$condition="")
    {
        if(!is_array($data) || count($data) <=0) {
            $this->errorLog("没有要更新的数据");
            return false;
        }
        $value = "";
        foreach($data as $key=>$val)
        {
            $val = mysqli_real_escape_string($this->linkId, $val);
            $value .= "$key = '$val',";
        }
        $value = substr($value, 0, -1);
        $strsql = "UPDATE $table SET $value WHERE 1=1 AND $condition ";
        if(!$this->query($strsql)) return false;
        return mysqli_affected_rows($this->linkId);
    }

    /**
     * 删除记录
     * @param string $table 表名
     * @param string $condition 条件
     * @return bool|int
     */
    public function delete($table,$condition="")
    {
        if(empty($condition)) {
            $this->errorLog("没有设置删除的条件");
            return false;
        }
        $strsql = "DELETE FROM $table WHERE 1=1 AND $condition";
        if(!$this->query($strsql)) return false;
        return mysqli_affected_rows($this->linkId);
    }

    /**
     * 返回影响行数
     */
    public function affectedRows()
    {
        return mysqli_affected_rows($this->linkId);
    }

    /**
     * 关闭数据库链接
     */
    public function close()
    {
        return mysqli_close($this->linkId);
    }

    /**
     * 错误日志
     */
    public function errorLog($message)
    {
        $path = ROOT_PATH.LOG_PATH."mysql".DS.date("Y").DS.date("m").DS;
        if(!is_dir($path)) {
            if(!$isDir = mkdir($path,0777,true)) {
                trigger_error("创建日志目录失败".$path);
                return false;
            }
            chmod($path, 0777);
        }
        $message = iconv("utf-8","gb2312",$message);
        $fileName = $path.date("d")."_error.log";
        $title = '[error]['.date('Y-m-d H:i:s').']';
        $msg = $title.$message."\n";
        return file_put_contents($fileName, $msg, FILE_APPEND);
    }

    /**
     * 执行日志
     */
    public function queryLog($message,$runtime)
    {
        $path = ROOT_PATH.LOG_PATH."mysql".DS.date("Y").DS.date("m").DS;
        if(!is_dir($path)) {
            if(!$isDir = mkdir($path,0777,true)) {
                trigger_error("创建日志目录失败".$path);
                return false;
            }
            chmod($path, 0777);
        }
        $message = iconv("utf-8","gb2312",$message);
        $fileName = $path.date("d")."_query.log";
        $title = '[query]['.date('Y-m-d H:i:s').'][runtime:'.$runtime.']';
        $msg = $title.$message."\n";
        return file_put_contents($fileName, $msg, FILE_APPEND);
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * 数据库wait_timeout时间内，没有访问数据库的话，链接会被断开，所以重新链接
     */
    private function reconnect()
    {
        mysqli_close($this->linkId);
        $this->connect($this->db_config[0],$this->db_config[1],$this->db_config[2],$this->db_config[3]);
    }

    /**
     * @param $table_name
     * @param string $where
     * @param int $limit
     * @param string $fields
     * @param string $order_by
     * @param string $sort
     * @return string
     */
    public function sqlSelect($table_name, $where = "", $limit = 0, $fields = "*", $order_by = "", $sort = "DESC")
    {
        $where = $where ? " WHERE " . $where : "";
        $order_by = $order_by ? " ORDER BY " . $order_by . " " . $sort : "";
        $limit = $limit ? " limit " . $limit : "";
        $sql = "SELECT " . $fields . " FROM " . $table_name . $where . $order_by . $limit;
        return $sql;
    }
}