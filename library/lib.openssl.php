<?php
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');
/**
 * OpenSSL  非对称加解密实现
 * @author  Terence [terecne@mytopfun.com]
 * @time    2019/01/17
 */
class Lib_Openssl
{
    //单例
    private static $_instance;
    private $config = [
        "digest_alg" => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];
    private $private_key;
    private $public_key;

    private $unique_key = 'Avgtetokd6tp789d2da56rtash34Kodw';

    public function __construct()
    {
        $this->private_key = file_get_contents(SHELL_PATH.'pem/private_key.pem');
        $this->public_key  = file_get_contents(SHELL_PATH.'pem/public_key.pem');
    }
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * 生成私钥和公钥，在SHELL_PATH.'pem目录下，若失败，一般是因为没有写入权限
     * @Author   Terence
     * @DateTime 2019-01-17
     * @return   [Bool]     [是否成功]
     */
    public function generateKey()
    {
        $res = openssl_pkey_new($this->config);
        //提取私钥
        openssl_pkey_export($res, $private_key);
        //生成公钥
        $public_key=openssl_pkey_get_details($res);
        $public_key=$public_key["key"];
        $res = file_put_contents(SHELL_PATH.'pem/public_key.pem',$public_key);
        $res2 = file_put_contents(SHELL_PATH.'pem/private_key.pem',$private_key);
        if ($res && $res2) {
            return true;
        }else{
            return false;
        }
    }
    /**
     * 私钥加密
     * @Author   Terence
     * @DateTime 2019-01-17
     * @param    [string]     $data [原始数据]
     * @return   [string]           [私钥加密后数据]
     */
    public function privateEncrypt($data)
    {
        $data = str_split($data,500);
        foreach ($data as $k => $v) {
            //私钥加密后的数据
            openssl_private_encrypt($v,$encrypted,$this->private_key);
            //加密后的内容通常含有特殊字符，需要base64编码转换下
            $data[$k] = base64_encode($encrypted);
        }
        return $data;
    }
    /**
     * 公钥解密
     * @Author   Terence
     * @DateTime 2019-01-17
     * @param    [string]     $encrypted [私钥加密后的数据]
     * @return   [string]                [公钥解密后的数据]
     */
    public function publicDecrypt($encrypted)
    {
        openssl_public_decrypt(base64_decode($encrypted), $decrypted, $this->public_key);//公钥解密
        return $decrypted;
    }
    /**
     * 公钥加密
     * @Author   Terence
     * @DateTime 2019-01-17
     * @param    [string]     $data [原始数据]
     * @return   [string]           [公钥加密后的数据]
     */
    public function publicEncrypt($data)
    {
        //公钥加密后的数据
        openssl_public_encrypt($data, $encrypted, $this->public_key);
        return base64_encode($encrypted);
    }
    /**
     * 私钥解密
     * @Author   Terence
     * @DateTime 2019-01-17
     * @param    [string]     $encrypted [公钥加密后的数据]
     * @return   [string]                [私钥解密后的数据]
     */
    public function privateDecrypt($encrypted)
    {
        openssl_private_decrypt(base64_decode($encrypted), $decrypted, $this->private_key);//私钥解密
        return $decrypted;
    }
    /**
     * md5签名
     * @Author   Terence
     * @DateTime 2019-01-17
     * @param    [string]     $user_name [管理员名称]
     * @param    [int]        $iat       [开始时间戳]
     * @param    [int]        $exp       [过期时间戳]
     * @return   [string]                [哈希值]
     */
    public function sign($user_name, $iat, $exp)
    {
        return md5($user_name.$iat.$exp.$this->unique_key);
    }
}