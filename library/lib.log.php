<?php
/**
 * 日志系统
 */
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');

class Lib_Log 
{
	private static $_instance;
	
	//文件日志系统是否开启
	private static $logEnabled = 1;
	
	/**
	 * 获取单例
	 * @return Lib_log
	 */
	public static function getInstance()
	{
		if (!self::$_instance instanceof self) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
     * 日志文件的写入
     * @param $log_level
     * @param $message
     * @param null $var
     * @return bool
     */
	private function log($log_level,$message,$var = null){
        if(!self::$logEnabled) return false;
        $path = ROOT_PATH.LOG_PATH."php".DS.date("Y").DS.date("m").DS;
        if(!is_dir($path)) {
            if(!$isDir = mkdir($path,0777,true)) {
                trigger_error("创建日志目录失败".$path);
                return false;
            }
            chmod($path, 0777);
        }
        $remote_addr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "unknown";

        $message = json_encode($message,JSON_UNESCAPED_UNICODE);
        $file = $path.date("d").'_'.$log_level.".log";
        if(!$fp = fopen($file, 'a+')){
            $fp = fopen($file, 'a+');
        }
        if (!$fp){
            trigger_error("fopen $file failed");
            return false;
        }
        $title = "[$log_level][".date('Y-m-d H:i:s').']['.$remote_addr.']';
        $msg = $title.$message . (isset($var) ? (" : ".var_export($var,true)) : "")."\n";
        fwrite($fp,$msg);
        if ($fp) @fclose($fp);
        return true;

    }

    /**
     * 系统致命错误写的日志
     * @param string $message 输入的消息
     * @param  $var
     */
	public  function error($message,$var = null)
	{
        self::log('error',$message,$var);
	}

    /**
     * 系统debug的时候写的日志
     * @param string $message 输入的消息
     * @param $var
     * @return bool
     */
	public function debug($message,$var = null){
	    return self::log('debug',$message,$var);
    }

    /**
     * 一般的错误日志,或者exception错误
     * @param string $message 输入的消息
     * @param $var
     */
	public  function info($message,$var = null)
	{
		self::log('info',$message,$var);
	}
	
}