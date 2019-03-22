<?php
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');

/**
 * socket对象链接（与server交互）
 */
class Lib_Socket
{
	
	/**
	 * 发送socket
	 * @param string $ip 发送的ip
	 * @param string $port 端口
	 * @param array $msg 发送的消息包
	 */
	public static function sendSocket($ip,$port,$msg)
	{
		$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>3,"usec"=>0));
		$msg = json_encode($msg);
		$len = strlen($msg);
		socket_sendto($socket, $msg, $len, 0, $ip,$port);
		$from = "";
		$port = 0;
		socket_recvfrom($socket, $buf, 1024, 0, $from,$port);
		socket_close($socket);
		$array = unpack('a*json',$buf);
		$response = json_decode($array["json"], true);
		return $response;
	}
}