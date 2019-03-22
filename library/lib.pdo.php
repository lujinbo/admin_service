<?php
/**
 * mysql操作类
 */
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');

class Lib_Pdo
{
    //链接id
    private $linkId;
    private $db_config;
    private $dsn;
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
        $this->dsn = "mysql:host={$dbhost};dbname=$dbname;charset=".$charset;
        try {
            if($pconnect == 0) {
                $this->linkId = new \PDO($this->dsn, $dbuser, $dbpwd); //初始化一个PDO对象
            } else {
                $this->linkId = new \PDO($this->dsn, $dbuser, $dbpwd, array(\PDO::ATTR_PERSISTENT => true));
            }
            $this->linkId->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die ("Error!: " . $e->getMessage() . "<br/>");
        }
    }

    /**
     * 执行语句
     * @param $strsql
     * @return bool|resource
     */
    public function query($strsql)
    {
        try{
            $sqlData = $this->linkId->query($strsql);
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
    public function getOne($strsql,$type=\PDO::FETCH_ASSOC)
    {
        $data = $this->query($strsql);
        if(empty($data))
            return false;
        $ret = $data->fetch($type);
        return $ret;
    }

    /**
     * 获取全部记录
     * @param string $strsql 执行的语句
     * @param $strsql
     * @param int $type
     * @return array|bool
     */
    public function getAll($strsql,$type=\PDO::FETCH_ASSOC)
    {
        $data = $this->query($strsql);
        if(empty($data))
            return false;
        $ret = $data->fetchAll($type);
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
        $insert_data = array();
        foreach ($data as $key=>$val) {
            $field .= "$key,";
            $value .= ":$key,";
            $insert_data[$key] = $val;
        }
        $field = substr($field,0,-1);
        $value = substr($value, 0,-1);
        $strsql = "INSERT INTO $table($field) VALUES ($value)";
        $stmt = $this->linkId->prepare($strsql);
        if(!$stmt->execute($insert_data)) return false;
        return true;
    }

    /**
     * 获取最后插入的id
     */
    public function insertId()
    {
        return $this->linkId->lastInsertId();
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
        $update_data = array();
        foreach ($data as $key=>$val) {
            $value .= "$key = :$key,";
            $update_data[$key] = $val;
        }
        $value = substr($value, 0, -1);
        $strsql = "UPDATE $table SET $value WHERE 1=1 AND $condition ";
        $stmt = $this->linkId->prepare($strsql);
        if(!$stmt->execute($update_data)) return false;
        return $stmt->rowCount();
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
        $stmt = $this->linkId->prepare($strsql);
        if(!$stmt->execute()) return false;
        return $stmt->rowCount();
    }

    /**
     * 关闭数据库链接
     */
    public function close()
    {
        $this->linkId = null;
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

}