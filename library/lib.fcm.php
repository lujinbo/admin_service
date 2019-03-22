<?php
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');
/**
 * 邮件类
 */
class Lib_Fcm
{
	private static $_instance;
	private
//		$GOOGLE_FCM_URL = "https://fcm.googleapis.com/fcm/send",	//推送地址
		$GOOGLE_FCM_URL = "https://gcm-http.googleapis.com/gcm/send",	//推送地址
		$GOOGLE_API_KEY = "AAAAZHtHmXs:APA91bFWC1XLvBzKuhG4MDd1l21isA_N1bxmIWirQ6OwXj5ySZ3UHNGP-kTYEtF4LTu3kQ8sXJZDbgu1QJNjxqNFlPLq_fmgQ8SzxuHVxTppMs8b3UH8fs5QJfdlUjOc0QFGWpYW4m8a";//KEY
	
	public static function getInstance()
	{
		if(!self::$_instance instanceof self){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * @param $reg_ids array(google_token,google_token)
	 * @param $title
	 * @param $message
	 * @param $money
	 * @return bool|mixed
	 */
	public function send_fcm_notify($reg_ids ,$title, $message,$money)
	{
		$fields = array(
			'registration_ids'  => $reg_ids,
			'notification'	=> array(
				"title" => $title,
				"text" => $message
			)
		);
		if( !empty($money) )
		{
			$fields['data']['d'] = $money;
		}
		$headers = array(
			'Authorization:key='.$this->GOOGLE_API_KEY,
			'Content-Type:application/json'
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->GOOGLE_FCM_URL);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		
		$result = curl_exec($ch);//result的格式为 '{multicast_id: 5381354139383071000, success: 1, failure: 0, canonical_ids: 0, results: [{message_id: "0:1377835083519762%2adac3a0f9fd7ecd"}]}'
		Lib_Log::getInstance()->debug('send_fcm_notify $fields',json_encode($fields));
		Lib_Log::getInstance()->debug('send_fcm_notify $result',$result);

		if ($result === FALSE) {
			Lib_Log::getInstance()->info('send_gcm_notify',curl_error($ch));
			return false;
		}
		
		curl_close($ch);
		
		return $result;
	}
	
	
}