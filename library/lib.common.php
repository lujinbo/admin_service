<?php
/**
 * 通用函数
 */
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');

class Lib_Common
{
	/**
	 * 获取请求地址
	 */
	public static function getRequestUrl()
	{
		if (isset($_SERVER['REQUEST_URI'])) {
			$url = $_SERVER['REQUEST_URI'];
		} else {
			if (isset($_SERVER['argv'])) {
				$url = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
			} else {
				$url = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
			}
		}
		$strPost = "";
		if($_POST) {
			foreach ($_POST as $k => $v) {
				$strPost .= "&".$k."=$v";
			}
		}
		return $url.$strPost;
	}
	
	/**
	 * 获取客户端IP
	 */
	public static function getClientIp()
	{
		$ip=false;
		if(!empty($_SERVER["HTTP_CLIENT_IP"])){
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
			if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
			for ($i = 0; $i < count($ips); $i++) {
				if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
					$ip = $ips[$i];
					break;
				}
			}
		}
		return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
	}
	
	/**
	 * @param 将需要格式化的数据格式化
	 */
	public static function formatStr()
	{
		$paramNum = func_num_args();
		$paramArray = func_get_args();
		for ($i=1;$i<$paramNum;$i++) {
			$paramArray[0] = str_replace("{".($i-1)."}", $paramArray[$i], $paramArray[0]);
		}
		return $paramArray[0] ;
	}


}