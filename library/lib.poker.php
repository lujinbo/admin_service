<?php
/**
 * web程序入口公共文件
 */
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');

class Lib_Poker
{
	private  static $_register = false;
	private  static $_ext = ".php";

	protected function __construct(){}
	
	public function map($name)
	{
		$maps =  array(
			"App"=>"application",
			"Lib"=>"library",
            "Controller"=>"controller",
            "Conf"=>"conf",
            "Model"=>"model",
            "View"=>"view"
		);
		return isset($maps[$name]) ? $maps[$name] : $name;
	}

	/**
	 * 注册自动加载方法
	 */
	public static function register($prepend = false)
	{
		if(self::$_register) return;
		spl_autoload_register(array(new self, 'autoload'), true, $prepend);
		self::$_register = true;
	}

	/**
	 * 自动加载方法实现
	 */
	public function autoload($className)
	{
		$classStrut = explode("\\", $className);
		$filePathStr = "";
		for($i = 0;$i<count($classStrut);$i++) {
			if($i == (count($classStrut)-1)) {
				$classStrut[$i] = strtolower(str_replace("_", ".", $classStrut[$i]));
			}
			$filePathStr .= $this->map($classStrut[$i]).DS;
		}
		$filePathStr = substr($filePathStr, 0,-1);
		$filePath = ROOT_PATH .  $filePathStr . self::$_ext;
		if (file_exists($filePath)) {
			include $filePath;
			return true;
		}
		return false;
	}
	
	/**
	 * php错误异常处理
	*	@param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
	 */
	public static function errorHandler($errno, $errstr, $errfile, $errline)
	{
		$strError = "[Error Info] Error no: $errno, $errstr, error on line $errline in $errfile";
		echo $strError;
		Lib_Log::getInstance()->info($strError);
	}
	
	/**
	 * 致命错误捕获处理或者程序正常执行完处理
	 */
	public static function fatalHandler()
	{
		global $isFinishPage;
		//致命错误日志
		if ($e = error_get_last()) {
			$strError = "[Fatal Error] Error no:".$e['type'].",".$e['message'].",error on line ".$e['line']." in ".$e['file'];
			Lib_Log::getInstance()->error($strError);
		//正常走完程序
		}
	}
	
	/**
	 * 异常错误处理
	 * @param object $e 异常对象
	 */
	public static function exceptionHandler($e)
	{
		$strError = "[Exception Info] ".$e->getMessage();
		echo $strError;
		Lib_Log::getInstance()->info($strError);
	}
}
