<?php
namespace App\Controller;
!defined('GREATANTS') && exit('Access Denied');

use App\Model\Model_Api;
use App\Model\Model_Admin;
use Lib\Lib_Jwt;


class Controller_Api
{
    // admin模型
    private $modelApi;
    private $modelAdmin;

    public function __construct()
    {
        $this->modelApi = Model_Api::getInstance();
        $this->modelAdmin = Model_Admin::getInstance();
    }
    /**
     * 获取游戏|项目列表
     * @Author   Terence
     * @DateTime 2019-01-17
     * @return   [type]     [description]
     */
    public function getGameList()
    {
        $data = $this->modelApi->getGameList();
        return ['status' => 200, 'data' => $data];
    }
    /**
     * 添加游戏|项目
     * @Author   Terence
     * @DateTime 2019-01-17
     */
    public function addGame()
    {
       if (!isset($_REQUEST['name']) || !isset($_REQUEST['code']) || !isset($_REQUEST['game_desc'])) {
           return ['status' => 702];
       }
       $res = $this->modelApi->addGame($_REQUEST);
       return $res ? ['status' => 200] : ['status' => 802];
    }
    /**
     * 更新游戏|项目
     * @Author   Terence
     * @DateTime 2019-01-17
     * @return   [type]     [description]
     */
    public function updateGame()
    {
        if (!isset($_REQUEST['name']) || !isset($_REQUEST['code']) || !isset($_REQUEST['game_desc']) || !isset($_REQUEST['id'])) {
           return ['status' => 702];
       }
       $res = $this->modelApi->updateGame($_REQUEST);
       return $res ? ['status' => 200] : ['status' => 802, 'msg' => '数据库操作失败，或更新前后没有变化'];
    }
    /**
     * 根据游戏id获取API列表
     * @Author   Terence
     * @DateTime 2019-01-17
     * @return   [type]     [description]
     */
    public function getApiList()
    {
        if (!isset($_REQUEST['id'])) {
            return ['status' => 702];
        }
        $data = $this->modelApi->getApiList($_REQUEST['id']);
        return ['status' => 200, 'data' => $data];
    }
    /**
     * 给指定id的game添加api
     * @Author   Terence
     * @DateTime 2019-01-17
     */
    public function addApi()
    {
        if (!isset($_REQUEST['api_name']) || !isset($_REQUEST['api_desc']) || !isset($_REQUEST['game'])) {
            return ['status' => 702];
        }
        $data = $this->modelApi->addApi($_REQUEST);
        return ['status' => 200, 'data' => $data];
    }
    /**
     * 更新指定id的api
     * @Author   Terence
     * @DateTime 2019-01-17
     * @return   [type]     [description]
     */
    public function updateApi()
    {
        if (!isset($_REQUEST['api_name']) || !isset($_REQUEST['api_desc']) || !isset($_REQUEST['id'])) {
           return ['status' => 702];
       }
       $res = $this->modelApi->updateApi($_REQUEST);
       return $res ? ['status' => 200] : ['status' => 802, 'msg' => '数据库操作失败，或更新前后没有变化'];
    }
    /**
     * 获取游戏的中文信息
     * @Author   Terence
     * @DateTime 2019-01-17
     * @return   [type]     [description]
     */
    public function getGameName()
    {
        if (!isset($_REQUEST['id'])) {
            return ['status' => 702];
        }
        $name = $this->modelApi->getGameName($_REQUEST['id']);
        if (empty($name)) {
            return ['status' => 404];
        }else{
            return ['status' => 200,'name' => $name['name']];
        }
    }
    /**
     * 获取某个管理员在某个游戏里的api权限信息
     * @Author   Terence
     * @DateTime 2019-01-17
     * @return   [type]     [description]
     */
    public function getAdminApi()
    {
        if (!isset($_REQUEST['user_name']) || !isset($_REQUEST['game_id'])) {
            return ['status' => 702];
        }
        $admin_info = $this->modelAdmin->getAdminInfo($_REQUEST['user_name']);
        if (empty($admin_info)) {
            return ['status' => 901];
        }
        $id = $admin_info['id'];
        $data = $this->modelApi->getAdminApi($_REQUEST['game_id']);
        foreach ($data as $k => $v) {
            if ($admin_info['user_group'] == 0) {
                $data[$k]['status'] = TRUE;
            }else{
                if ($v['api_access'] == null) {
                    $data[$k]['status'] = FALSE;
                }else{
                    $users = json_decode($v['api_access'],true);
                    $data[$k]['status'] = in_array($id, $users) ? TRUE : FALSE;
                }
            }            
            unset($data[$k]['api_access']);
        }
        return ['status' => 200, 'data' => $data];
    }
    /**
     * 改变某个管理员在某个游戏里某个api的权限
     * @Author   Terence
     * @DateTime 2019-01-17
     * @return   [type]     [description]
     */
    public function changeApiAccess()
    {
        if (!isset($_REQUEST['api_id']) || !isset($_REQUEST['user_name']) || !isset($_REQUEST['type'])) {
            return ['status' => 702];
        }
        $admin_info = $this->modelAdmin->getAdminInfo($_REQUEST['user_name']);
        if (empty($admin_info)) {
            return ['status' => 901];
        }
        $id = $admin_info['id'];
        $res = $this->modelApi->changeApiAccess($_REQUEST['api_id'], $id, $_REQUEST['type']);
        return $res ? ['status' => 200] : ['status' => 800, 'msg' => '数据库操作失败，或更新前后没有变化'];
    }
}