<?php
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');
/**
 * 安全加密与解密
 */
class Lib_Validation
{

    private static $_instance;

    /**
     * 获取单例
     * @return Lib_Validation
     */
    public static function getInstance()
    {
        if(!self::$_instance instanceof self){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 加密key
     * @param string $k
     * @return string
     */
    public function getKey($k = '')
    {
        $k = $k ? $k : "greatants";
        $key = hash('md5', $k);
        $key = substr($key, 0, 16);
        return $key;
    }

    /**
     * 加密
     * @param $content
     * @param string $key
     * @return string
     */
    public function encrypt($content,$key = "")
    {
        $key =  $this->getKey($key);
        return  $this->AES_ecb128_encrypt($content,$key);
    }

    public function encrypt_aes_base64($content,$key = "")
    {
        $key =  $this->getKey($key);
        return  $this->AES_ecb128_encrypt_base64($content,$key);
    }

    /**
     * 解密
     * @param $content
     * @param string $key
     * @return string
     */
    public function decrypt($content,$key = "")
    {
        $key =  $this->getKey($key);
        return $this->AES_ecb128_decrypt($content,$key);
    }

    public function decrypt_aes_base64($content,$key = "")
    {
        $key =  $this->getKey($key);
        return $this->AES_ecb128_decrypt_base64($content,$key);
    }
    /**
     * 填充
     * @param $text
     * @param $padLen
     * @return string
     */
    public function pad2Length($text, $padLen){
        $len = strlen($text)%$padLen;
        if ($len == 0)
        {
            return $text;
        }
        $res = $text;
        $span = $padLen-$len;
        for($i = 0; $i < $span; $i++){
            $res .= chr(0);
        }
        return $res;
    }

    /**
     * aes 128 ecb 加密
     * @param $content
     * @param $key
     * @return string
     */
    private  function AES_ecb128_encrypt($content,$key){
        $content = $this->pad2Length($content,16);
        $cipher_text = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key,$content,MCRYPT_MODE_ECB);
        # 对密文进行 base64 编码
        $cipher_text_hex = bin2hex($cipher_text);
        return $cipher_text_hex;
    }

    private  function AES_ecb128_encrypt_base64($content,$key){
        $content = $this->pad2Length($content,16);
        $cipher_text = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key,$content,MCRYPT_MODE_ECB);
        # 对密文进行 base64 编码
        $cipher_text_base64 = base64_encode($cipher_text);
        return $cipher_text_base64;
    }

    /**
     * aes 128 ecb 解密
     * @param $content
     * @param $key
     * @return string
     */
    private function AES_ecb128_decrypt($content,$key){
        $cipher_text_dec = hex2bin($content);
        $cipher_text = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key,$cipher_text_dec,MCRYPT_MODE_ECB);
        return rtrim($cipher_text,"\0");
    }

    private function AES_ecb128_decrypt_base64($cipher_text_base64,$key){
        $cipher_text_dec = base64_decode($cipher_text_base64);
        $cipher_text = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key,$cipher_text_dec,MCRYPT_MODE_ECB);
        return rtrim($cipher_text,"\0");
    }

}