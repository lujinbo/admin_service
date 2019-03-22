<?php
namespace App\Controller;
!defined('GREATANTS') && exit('Access Denied');

use App\Model\Model_Admin;
use App\Model\Model_Api;
use Lib\Lib_Openssl;


class Controller_Admin
{
    // admin模型
    private $modelAdmin;
    private $jwt;
    private $game = ['管理员微服务','Dummy_H5','H5_AK'];

    public function __construct()
    {
        $this->modelAdmin = Model_Admin::getInstance();
        $this->modelApi = Model_Api::getInstance();
        $this->openssl = Lib_Openssl::getInstance();
    }

    /**
     * 管理员登录
     * @Author   Terence
     * @DateTime 2018-12-05
     * @return   [array]     [登录结果]
     */
    public function login()
    {
        if (!isset($_REQUEST['user_name']) || !isset($_REQUEST['user_pwd']) || !isset($_REQUEST['game'])) {
            return ['status' => 702];
        }
        $user_name = $_REQUEST['user_name'];
        $user_pwd = $_REQUEST['user_pwd'];
        $game = $_REQUEST['game'];
        $user_info = $this->modelAdmin->getAdminInfo($user_name);
        if (!$user_info) {
            return ['status' => 901];
        }
        $tmp_pwd = md5($user_pwd.$user_info['user_salt']);
        if ($tmp_pwd !== $user_info['user_pwd']) {
            return ['status' => 902];
        }
        $token = $this->getOpensslToken($user_name, $user_info['id'],$user_info['user_group'],$game, 0);
        return ['status' => 200, 'token' => $token];
    }
    /**
     * 获取加密后的token
     * @Author   Terence
     * @DateTime 2019-01-17
     * @param    [type]     $user_name  [description]
     * @param    [type]     $user_id    [description]
     * @param    [type]     $user_group [description]
     * @param    [type]     $game       [description]
     * @param    [int]      $type       [登录方式 0=>号密，1=>邮箱验证码， 2=>扫码]
     * @return   [type]                 [description]
     */
    private function getOpensslToken($user_name, $user_id, $user_group, $game, $type)
    {
        $data = $this->modelApi->getAdminApi($game);
        $access = [];
        foreach ($data as $k => $v) {
            if ($user_group == 0) {
                $access[] = $v['api_name'];
            }else{
                if ($v['api_access'] != null) {
                    $users = json_decode($v['api_access'],true);
                    if (in_array($user_id, $users)) {
                        $access[] = $v['api_name'];
                    }
                }
            }
        }
        $access = array_merge($access,['Admin-changeAdminPassword', 'Admin-getSignature']);//这两个API用于用户修改密码和拉取令牌
        $token = [
            'user' => $user_name,
            'iat' => time(),
            'exp' => time() + 3600*16,
        ];
        $token['signature'] = $this->openssl->sign($user_name, $token['iat'], $token['exp']);
        $token['access'] = $this->openssl->privateEncrypt(json_encode($access));
        $token = json_encode($token);
        //登录记录，不关心存入是否成功.....
        $this->modelAdmin->loginRecord($user_name,$game, $type);
        return base64_encode($token);
    }
    /**
     * 获取管理员列表
     * @Author   Terence
     * @DateTime 2018-12-05
     * @return   [Array]     [管理员信息]
     */
    public function getAdminList()
    {
        $data = $this->modelAdmin->getAdminList();
        return ['status' => 200,'data' => $data];
    }
    /**
     * 添加管理员
     * @Author   Terence
     * @DateTime 2018-12-04
     */
    public function addAdmin()
    {
        $data = $_REQUEST;
        $data['user_salt'] = md5(uniqid('topfun'));
        $raw = md5(uniqid());
        $raw = substr($raw,0,16);
        $data['user_pwd'] = md5($raw.$data['user_salt']);
        $data['create_time'] = time();
        $result = $this->modelAdmin->addAdmin($data);
        if ($result) {
            $subject = '新增管理员邮件通知';
            $body = "<div style='width:100%;padding:2rem;'>";
            $body.=     "<p>管理员".$_REQUEST['user_name'].'|mail:'.$_REQUEST['user_mail']."： 你好！</p>";
            $body.=     "<p>你的密码是:<span style='font-size:1.5rem;color:#ff6600;'>".$raw."</span>,你已被添加为后台管理员，可凭此账号登录所有已接入【管理员微服务】的游戏后台，请妥善保管此密码，以便完成登录验证。</p>";
            $body.=     "<div style='width:100%;height:1px;background:#909399;overflow:hidden;'></div>";
            $body.=     "<p style='font-size:0.6rem;color:#909399;'>此为系统邮件，请勿回复</p>";
            $body.=     "<p style='font-size:0.6rem;color:#909399;'>请保管好邮箱密码，避免账号被他人盗用</p>";
            $body.="</div>";
            $res = $this->modelAdmin->sendMail($_REQUEST['user_mail'], $subject, $body);
            $out_put = ['status' => 200, 'pwd' => $raw];
            $out_put['send_mail'] = !empty($res['s']) ? 1 : 0;
            return $out_put;
        }else{
            return ['status' => 800];
        }
    }
    public function delAdmin()
    {
        if (!isset($_REQUEST['user_name'])) {
            return ['status' => 702];
        }
        $res = $this->modelAdmin->delAdmin($_REQUEST['user_name']);
        return $res ? ['status' => 200] : ['status' => 800];
    }
    /**
     * 修改管理员密码
     * @Author   Terence
     * @DateTime 2019-01-02
     * @return   [array]     [结果]
     */
    public function changeAdminPassword()
    {
        if (!isset($_REQUEST['user_name']) || !isset($_REQUEST['user_pwd']) ||!isset($_REQUEST['new_pwd'])) {
            return ['status' => 702];
        }
        $user_name = $_REQUEST['user_name'];
        $user_pwd = $_REQUEST['user_pwd'];
        $new_pwd = $_REQUEST['new_pwd'];
        $user_info = $this->modelAdmin->getAdminInfo($user_name);
        if (!$user_info) {
            return ['status' => 901];
        }
        $tmp_pwd = md5($user_pwd.''.$user_info['user_salt']);
        if ($tmp_pwd === $user_info['user_pwd']) {
            //更新密码
            $data['user_salt'] = md5(uniqid('topfun'));
            $data['user_pwd'] = md5($new_pwd.$data['user_salt']);
            $res = $this->modelAdmin->changeAdminPassword($user_name, $data);
            return $res ? ['status' => 200,'msg' => '更新密码成功'] : ['status' => 800];
        }else{
            return ['status' => 902];
        }
    }
    /**
     * 获取验证码，发送至邮箱
     * @Author   Terence
     * @DateTime 2019-01-03
     * @return   [type]     [description]
     */
    public function getCode()
    {
        if (!isset($_REQUEST['user_mail'])) {
            return ['status' => 702];
        }
        $num = $this->modelAdmin->checkEmail($_REQUEST['user_mail']);
        if ($num['num'] == 0) {
            return ['status' => 901,'msg' => '该邮箱不存在'];
        }
        $code = rand(100000,999999);
        $res = $this->modelAdmin->setCode($_REQUEST['user_mail'], $code);
        if (!res) {
            return ['status' => 801];
        }
        $subject = 'Dummy_H5 管理后台登录验证';
        $body = "<div style='width:100%;padding:2rem;'>";
        $body.=     "<p>管理员".$_REQUEST['user_mail']."： 你好！</p>";
        $body.=     "<p>验证码:<span style='font-size:1.5rem;color:#ff6600;'>".$code."</span>你正在进行登录验证，请在验证码输入框中依次输入以上验证码，以完成验证操作。</p>";
        $body.=     "<div style='width:100%;height:1px;background:#909399;overflow:hidden;'></div>";
        $body.=     "<p style='font-size:0.6rem;color:#909399;'>此为系统邮件，请勿回复</p>";
        $body.=     "<p style='font-size:0.6rem;color:#909399;'>请保管好邮箱密码，避免账号被他人盗用</p>";
        $body.="</div>";
        $res = $this->modelAdmin->sendMail($_REQUEST['user_mail'], $subject, $body);
        return !empty($res['s']) ? ['status' => 200] : ['status' => 701,'msg' => '发送邮件至<'.$_REQUEST['user_mail'].'>失败!'];
    }
    /**
     * 校验验证码是否正确
     * @Author   Terence
     * @DateTime 2019-01-03
     * @param    string     $value [description]
     * @return   [type]            [description]
     */
    public function checkCode($value='')
    {
        if (!isset($_REQUEST['user_mail']) || !isset($_REQUEST['code']) || !isset($_REQUEST['game'])) {
            return ['status' => 702];
        }
        $res = $this->modelAdmin->checkCode($_REQUEST['user_mail'],$_REQUEST['code']);
        $user_info = $this->modelAdmin->getNameByMail($_REQUEST['user_mail']);
        $token = $this->getOpensslToken($user_info['user_name'], $user_info['id'],$user_info['user_group'],$_REQUEST['game'], 1);
        return $res ? ['status' => 200,'token' => $token,'name' => $user_info['user_name']] : ['status' => 905];
    }
    /**
     * 获取登录二维码的唯一id
     * @Author   Terence
     * @DateTime 2019-01-03
     * @return   [type]     [description]
     */
    public function getUniqueQRCode()
    {
        $id = uniqid('topfun');
        $res = $this->modelAdmin->setUniqueQRCode($id);
        if ($res) {
            $id = base64_encode($id);
            return ['status' => 200 ,'id' => $id];
        }else{
            return ['status' => 801];
        }   
    }
    /**
     * 获取永久签名
     * @Author   Terence
     * @DateTime 2019-01-03
     * @return   [type]     [description]
     */
    public function getSignature()
    {
        if (!isset($_REQUEST['user_name'])) {
            return ['status' => 702];
        }
        $data = $this->modelAdmin->getSignature($_REQUEST['user_name']);
        if (empty($data)) {
            return ['status' => 901];
        }else{
            $signature = md5($data['user_mail'].$data['user_salt'].$data['user_mail']);
        }
        return ['status' => 200,'signature' => $signature];
    }
    /**
     * 校验签名
     * @Author   Terence
     * @DateTime 2019-01-03
     * @return   [type]     [description]
     */
    public function checkSignature()
    {
        if (!isset($_REQUEST['user_name']) || !isset($_REQUEST['signature'])) {
            return ['status' => 702];
        }
        $data = $this->modelAdmin->getSignature($_REQUEST['user_name']);
        if (empty($data)) {
            return ['status' => 901];
        }else{
            $signature = md5($data['user_mail'].$data['user_salt'].$data['user_mail']);
        }
        if ($_REQUEST['signature'] == $signature) {
            return ['status' => 200,'msg' => '合法签名'];
        }else{
            return ['status' => 902,'msg' => '非法签名'];
        }
    }
    /**
     * 校验二维码登录
     * @Author   Terence
     * @DateTime 2019-01-03
     * @return   [type]     [description]
     */
    public function checkLogin()
    {
        if (!isset($_REQUEST['user_name']) || !isset($_REQUEST['signature']) || !isset($_REQUEST['id'])) {
            return ['status' => 702];
        }
        //校验签名
        $data = $this->modelAdmin->getSignature($_REQUEST['user_name']);
        if (empty($data)) {
            return ['status' => 901];
        }else{
            $signature = md5($data['user_mail'].$data['user_salt'].$data['user_mail']);
        }
        if ($_REQUEST['signature'] != $signature) {
            return ['status' => 902,'msg' => '非法签名'];
        }
        $id = base64_decode($_REQUEST['id']);
        $res = $this->modelAdmin->setLoginInfo($id,$_REQUEST['user_name']);
        return $res ? ['status' => 200] : ['status' => 801];
    }
    /**
     * 查询该二维码的登录信息
     * @Author   Terence
     * @DateTime 2019-01-03
     * @return   [type]     [description]
     */
    public function getLoginInfo()
    {
        if (!isset($_REQUEST['id']) || !isset($_REQUEST['game'])) {
            return ['status' => 702];
        }
        //校验id是否还有效
        $id = base64_decode($_REQUEST['id']);
        $res = $this->modelAdmin->getLoginInfo($id);
        if (!empty($res) && $res != -1) {
            $user_name = $res;
            $user_info = $this->modelAdmin->getAdminInfo($user_name);
            if (!$user_info) {
                return ['status' => 901];
            }
            $token = $this->getOpensslToken($user_name, $user_info['id'],$user_info['user_group'],$_REQUEST['game'], 2);
            return ['status' => 200,'name' => $user_name,'token' => $token];
        }elseif($res == -1){
            return ['status' => 906];
        }else{
            return ['status' => 801,'msg' => '二维码已过期'];
        }
    }
    /**
     * 获取管理员的登录记录
     * @Author   Terence
     * @DateTime 2019-01-20
     * @return   [type]     [description]
     */
    public function getAdminLoginRecord()
    {
        if (!isset($_REQUEST['user_name'])) {
            return ['status' => 702];
        }
        $game = $this->game;
        $type = ['账号密码','邮件验证码','扫码登录'];
        $data = $this->modelAdmin->getAdminLoginRecord($_REQUEST['user_name']);
        foreach ($data as $k => $v) {
            $data[$k]['login_time'] = date("Y-m-d H:i:s",$v['login_time']);
            $data[$k]['login_ip']   = long2ip($v['login_ip']);
            $data[$k]['game'] = $game[$v['game']];
            $data[$k]['type'] = $type[$v['type']];
            $data[$k]['login_locate'] = '异步查询中...';
        }
        return ['status' => 200, 'data' => $data];
    }

    public function getIpInfo()
    {
        if (!isset($_REQUEST['ip'])) {
            return ['status' => 702];
        }
        $data = $this->modelAdmin->getIpInfo($_REQUEST['ip']);
        $data = json_decode($data, true);
        return ['status' => 200,'addr' => $data['errno'] == 0 ? $data['data'] : '未知地址'];
    }
    /**
     * 记录请求记录
     * @Author   Terence
     * @DateTime 2019-01-22
     * @return   [type]     [description]
     */
    public function requestRecord()
    {
        if (!isset($_REQUEST['r_ip']) 
            || !isset($_REQUEST['user_name']) 
            || !isset($_REQUEST['game']) 
            || !isset($_REQUEST['r_time']) 
            || !isset($_REQUEST['api']) 
            || !isset($_REQUEST['token'])) {
            return ['status' => 702];
        }
        $key = 'argahrharhyjuyjpdfkamgkh';
        $token = md5($key.$_REQUEST['r_time']);
        if ($token != $_REQUEST['token']) {
            return ['status' => 903];
        }
        $data = [
            'user_name' => $_REQUEST['user_name'],
            'game' => $_REQUEST['game'],
            'api' => $_REQUEST['api'],
            'r_time' => $_REQUEST['r_time'],
            'r_ip' => $_REQUEST['r_ip']
        ];
        $res = $this->modelAdmin->requestRecord($data);
        return ['status' => 200];
    }
    /**
     * 获取某个管理员的访问记录
     * @Author   Terence
     * @DateTime 2019-01-22
     * @return   [type]     [description]
     */
    public function getAdminRequestRecord()
    {
        if (!isset($_REQUEST['user_name'])) {
            return ['status' => 702];
        }
        $game = $this->game;
        $data = $this->modelAdmin->getAdminRequestRecord($_REQUEST['user_name']);
        foreach ($data as $k => $v) {
            $data[$k]['r_time'] = date("Y-m-d H:i:s",$v['r_time']);
            $data[$k]['r_ip']   = long2ip($v['r_ip']);
            $data[$k]['game'] = $game[$v['game']];
        }
        return ['status' => 200, 'data' => $data];
    }
}