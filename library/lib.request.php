<?php
/**
 * http request请求类
 * @todo [RSA加密传输]
 */
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');

use App\Conf\Config_Status;
use Lib\Lib_Load;

class Lib_Request
{	
	private static $_instance;
    private $openssl;
    private $admin;
    const ALLOW_API = [
        'Admin-login',
        'Admin-getCode',
        'Admin-checkCode',
        'Admin-getUniqueQRCode',
        'Admin-checkSignature',
        'Admin-checkLogin',
        'Admin-getLoginInfo',
        'Admin-requestRecord',
    ];
	/**
	 * [构造函数]
	 * @Author   Terence
	 * @DateTime 2018-11-19
	 */
	function __construct()
	{
		self::getRequest();
        $this->openssl = \Lib\Lib_Openssl::getInstance();
        $this->admin = Lib_Load::dbadmin();
	}
	/**
	 * 获取单例
	 * @return self::$_instance
	 */
	public static function getInstance()
	{
		if (!self::$_instance instanceof self) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	/**
	 * 获取请求的数据，合并合法的file_get_contents("php://input")进$_REQUEST
	 * @return array $_REQUEST
	 */
	public static function getRequest()
	{
		//有些是Content-Type: application/json;charset=UTF-8
		$raw_json = file_get_contents("php://input");
		if (!empty($raw_json)) {
			$data = json_decode($raw_json,true);
			//不为空且必须是json格式
			if (0 === json_last_error()) {
				$_REQUEST = array_merge($_REQUEST,$data);
			}
		}
		if (!empty($_REQUEST)) {
			foreach ($_REQUEST as $k => $v) {
                if (!is_array($v)) {
                    $_REQUEST[$k] = trim($v);
                }				
			}
		}	
		return $_REQUEST;
	}
    /**
     * 验证token，目前能考虑到的错误情况都考虑
     * @Author   Terence
     * @DateTime 2019-01-17
     * @param    [type]     $api [description]
     * @return   [type]          [description]
     */
    public function checkToken($api)
    {
        if (in_array($api, self::ALLOW_API)) {
            return true;
        }
        $token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
        $token = base64_decode($token);
        if (!$token) {
            $this->outPut(['status' => 905,'msg' => '验证Token时，base64解析出错']);
        }
        $token = json_decode($token,true);
        if (0 !==json_last_error()) {
            $this->outPut(['status' => 904,'msg' => '验证Token时，json解析出错']);
        }
        $access = $token['access'];
        foreach ($access as $k => $v) {
            $access[$k] = $this->openssl->publicDecrypt($v);
        }
        $access = implode('', $access);
        $access = json_decode($access,true);
        if (0 !==json_last_error()) {
            $this->outPut(['status' => 904,'msg' => '解析JSON权限列表出错']);
        }
        if ($token['exp'] < time()) {
            $this->outPut(['status' => 903, 'msg' => 'Token已过期']);
        }
        if (!in_array($api, $access)) {
            $this->outPut(['status' => 403]);
        }
        $sign = $this->openssl->sign($token['user'], $token['iat'], $token['exp']);
        if ($sign !== $token['signature']) {
            $this->outPut(['status' => 903, 'msg' => '签名校验失败']);
        }
        $data = [
            'user_name' => $token['user'],
            'game' => APPID,
            'api' => $api,
            'r_time' => time(),
            'r_ip' => ip2long($_SERVER['REMOTE_ADDR'])
        ];
        if (APPID == 0) {
            $this->admin->insert('t_request_record',$data);
        }else{
            $data = json_encode($data);
            Lib_Load::redisAdmin()->rpush('admin_service_request_record',$data);
        }
    }	
	/**
     * 框架统一返回输出函数
     * @param int       $status     [状态码]
     * @param string    $msg        [状态信息]
     * @param array     $data       [数据]
     */
    public static function outPut(array $data = [])
    {
        if (empty($data) || !isset($data['status'])) {
            $data['status'] = 700;//数据为空
        }
        $status = (int)$data['status'];
        $_status = Config_Status::$_status;
        if (!isset($_status[$status])) {
            $data['status'] = 701;//不存在的状态码
        }
        //以上代码保证status的值在预期之内
        $data['msg'] = isset($data['msg']) ? $data['msg'] : $_status[$data['status']];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        if (json_last_error() == 0) {
            echo $data;
        }else{
            echo json_encode(['status' => 907,'msg' => json_last_error_msg()], JSON_UNESCAPED_UNICODE);
        }
        exit();
    }
	
}