<?php
namespace App\Model;
!defined('GREATANTS') && exit('Access Denied');

use Lib\Lib_Load;
use Lib\Lib_Log;
use Lib\Lib_Mail;

/**
 * Class Model_Admin
 * @package App\Model
 * @author terence
 * 2018-12-4
 */
class Model_Admin
{
    // 单例对象
    private static $_instance;
    // 后台库
    private $admin;    
    private $redis;

    // 写日志,重要的操作
    private $log;

    /**
     * @return Model_Admin
     */
    public static function getInstance()
    {
        if(!self::$_instance instanceof self){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        // 加载admin库
        $this->admin = Lib_Load::dbadmin();
        $this->redis = Lib_Load::redisAdmin();
        $this->log =  Lib_Log::getInstance();
    }
    /**
     * [获取管理员列表]
     * @Author   Terence
     * @DateTime 2018-12-05
     * @return   [array]     [管理员信息]
     */
    public function getAdminList()
    {
        $sql = "SELECT id,user_name,user_group,user_mail,status,create_time FROM t_admin_user";
        $res = $this->admin->getAll($sql);
        foreach ($res as $k => $v) {
            $res[$k]['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
            $res[$k]['status'] = ($v['status'] == 1) ? '正常' : '禁用';
            $res[$k]['user_group'] = ($v['user_group'] == 0) ? '超级管理员' : '普通管理员';
        }
        return $res;
    }
    /**
     * 新增管理员
     * @Author   Terence
     * @DateTime 2018-12-05
     * @param    [array]     $data [管理员信息]
     * @return   [mysql插入结果]
     */
    public function addAdmin($data)
    {
        $res = $this->admin->insert('t_admin_user', $data);
        return $res;
    }
    /**
     * 删除管理员
     * @Author   Terence
     * @DateTime 2019-01-08
     * @param    [type]     $user_name [description]
     * @return   [type]                [description]
     */
    public function delAdmin($user_name)
    {
        $condition = "user_name = '$user_name'";
        $res = $this->admin->delete('t_admin_user', $condition);
        return $res;
    }
    /**
     * 登录时获取管理员密码
     * @Author   Terence
     * @DateTime 2018-12-05
     * @param    [type]     $user_name [description]
     * @return   [type]                [description]
     */
    public function getAdminInfo($user_name)
    {
        $sql = "SELECT id,user_pwd,user_salt,user_group FROM t_admin_user WHERE binary user_name = '$user_name' AND status = 1 LIMIT 1";
        $data = $this->admin->getOne($sql);
        return $data;
    }
    /**
     * 更改管理员密码
     * @Author   Terence
     * @DateTime 2018-12-05
     * @param    [string]     $user_name [管理员名称]
     * @param    [array]      $data      [要更新的数据]
     * @return   [type]                  [更新结果]
     */
    public function changeAdminPassword($user_name, $data)
    {
        $condition = "user_name = '$user_name'";
        $res = $this->admin->update('t_admin_user', $data, $condition);
        return $res;
    }
    /**
     * 将生成的验证码存在缓存
     * @Author   Terence
     * @DateTime 2019-01-02
     * @param    [type]     $mail [description]
     * @param    [type]     $code [description]
     */
    public function setCode($mail,$code)
    {
        $res = $this->redis->set($mail,$code,300);
        return $res;
    }
    /**
     * 检查是否存在该邮箱
     * @Author   Terence
     * @DateTime 2019-01-03
     * @param    [type]     $mail [description]
     * @return   [type]           [description]
     */
    public function checkEmail($mail)
    {
        $sql = "SELECT count(*) as num FROM t_admin_user WHERE user_mail = '$mail' LIMIT 1";
        $num = $this->admin->getOne($sql);
        return $num;
    }
    /**
     * 发送邮件
     * @Author   Terence
     * @DateTime 2019-01-02
     * @param    [type]     $to      [description]
     * @param    [type]     $subject [description]
     * @param    [type]     $body    [description]
     * @return   [type]              [description]
     */
    public function sendMail($to, $subject, $body)
    {
        $smtp = new Lib_Mail();
        $res = $smtp->sendmail($to, 'Dummy安全中心', $subject, $body, 'HTML');
        return $res;
    }
    /**
     * 校验验证码
     * @Author   Terence
     * @DateTime 2019-01-03
     * @param    [type]     $mail [description]
     * @param    [type]     $code [description]
     * @return   [type]           [description]
     */
    public function checkCode($mail, $code)
    {
        $right_code = $this->redis->get($mail);
        if ($right_code != false && $right_code == $code) {
            $this->redis->del($mail);
            return true;
        }else{
            return false;
        }
    }
    /**
     * 通过邮箱获取管理员名称
     * @Author   Terence
     * @DateTime 2019-01-03
     * @param    [type]     $mail [description]
     * @return   [type]           [description]
     */
    public function getNameByMail($mail)
    {
        $sql = "SELECT id,user_group,user_name FROM t_admin_user WHERE user_mail = '$mail' LIMIT 1";
        $name = $this->admin->getOne($sql);
        return $name;
    }
    /**
     * 获取用户签名所需要的信息
     * @Author   Terence
     * @DateTime 2019-01-03
     * @param    [type]     $user_name [description]
     * @return   [type]                [description]
     */
    public function getSignature($user_name)
    {
        $sql = "SELECT user_pwd,user_salt,user_mail FROM t_admin_user WHERE user_name = '$user_name' LIMIT 1";
        $data = $this->admin->getOne($sql);
        return $data;
    }
    /**
     * 存一分钟的唯一ID
     * @Author   Terence
     * @DateTime 2019-01-03
     * @param    [type]     $id [description]
     */
    public function setUniqueQRCode($id)
    {
        $res = $this->redis->set($id,-1,60*5);
        return $res;
    }
    /**
     * 设置登录信息，表示是谁扫了二维码
     * @Author   Terence
     * @DateTime 2019-01-03
     * @param    [type]     $id        [description]
     * @param    [type]     $user_name [description]
     */
    public function setLoginInfo($id,$user_name)
    {
        $res = $this->redis->set($id,$user_name);
        return $res;
    }
    /**
     * 查询该唯一ID的状态，即是否有人扫码登录
     * @Author   Terence
     * @DateTime 2019-01-03
     * @param    [type]     $id [description]
     * @return   [type]         [description]
     */
    public function getLoginInfo($id)
    {
        $data = $this->redis->get($id);
        return $data;
    }
    /**
     * 登录记录
     * @Author   Terence
     * @DateTime 2019-01-20
     * @param    [type]     $user_name [description]
     * @param    [type]     $game      [description]
     * @return   [type]                [description]
     */
    public function loginRecord($user_name, $game, $type)
    {
        $data = [
            'user_name' => $user_name,
            'game' => $game,
            'login_ip' => ip2long($_SERVER['REMOTE_ADDR']),
            'login_time' => time(),
            'type' => $type
        ];
        $res = $this->admin->insert('t_login_record',$data);
        return $res;
    }
    /**
     * 获取某个用户的登录记录
     * @Author   Terence
     * @DateTime 2019-01-20
     * @param    [type]     $user_name [description]
     * @return   [type]                [description]
     */
    public function getAdminLoginRecord($user_name)
    {
        $sql = "SELECT * FROM t_login_record WHERE user_name = '$user_name' ORDER BY login_time DESC";
        $data = $this->admin->getALl($sql);
        return $data;
    }
    /**
     * 获取某个用户的登录记录
     * @Author   Terence
     * @DateTime 2019-01-20
     * @param    [type]     $user_name [description]
     * @return   [type]                [description]
     */
    public function getAdminRequestRecord($user_name)
    {
        $sql = "SELECT * FROM t_request_record WHERE user_name = '$user_name' ORDER BY r_time DESC";
        $data = $this->admin->getALl($sql);
        return $data;
    }
    /**
     * 根据ip获取位置信息
     * @Author   Terence
     * @DateTime 2019-01-21
     * @param    [type]     $ip [description]
     * @return   [type]         [description]
     */
    public function getIpInfo($ip)
    {
        $ch = curl_init();               
        curl_setopt($ch, CURLOPT_URL,"http://ip.360.cn/IPQuery/ipquery?ip=".$ip);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    /**
     * 记录请求记录
     * @Author   Terence
     * @DateTime 2019-01-22
     * @param    [type]     $data [description]
     * @return   [type]           [description]
     */
    public function requestRecord($data)
    {
        $res = $this->admin->insert('t_request_record', $data);
        return $res;
    }
}