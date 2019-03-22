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
class Model_Api
{
    // 单例对象
    private static $_instance;
    // 后台库
    private $admin;    
    private $redis;

    // 写日志,重要的操作
    private $log;

    const GAME_TABLE = 't_game';
    const API_TABLE  = 't_api';

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
    public function getGameList()
    {
        $table = self::GAME_TABLE;
        $sql = "SELECT * FROM $table";
        $data = $this->admin->getAll($sql);
        return $data;
    }
    public function addGame($data)
    {
        $res = $this->admin->insert(self::GAME_TABLE, $data);
        return $res;
    }

    public function updateGame($data)
    {
        $id = $data['id'];
        unset($data['id']);
        $condition = "id = $id";
        $res = $this->admin->update(self::GAME_TABLE, $data, $condition);
        return $res;
    }

    public function getApiList($game_id)
    {
        $table = self::API_TABLE;
        $sql = "SELECT * FROM $table WHERE game = $game_id";
        $data = $this->admin->getAll($sql);
        return $data;
    }

    public function addApi($data)
    {
        $res = $this->admin->insert(self::API_TABLE, $data);
        return $res;
    }
    public function updateApi($data)
    {
        $id = $data['id'];
        unset($data['id']);
        $condition = "id = $id";
        $res = $this->admin->update(self::API_TABLE, $data, $condition);
        return $res;
    }
    public function getGameName($game_id)
    {
        $table = self::GAME_TABLE;
        $sql  =  "SELECT name FROM $table WHERE id = $game_id LIMIT 1";
        $name = $this->admin->getOne($sql);
        return $name;
    }
    public function getAdminApi($game_id)
    {
        $table = self::API_TABLE;
        $sql = "SELECT * FROM $table WHERE game = $game_id";
        $data = $this->admin->getAll($sql);
        return $data;
    }
    public function changeApiAccess($api_id, $admin_id, $type)
    {
        $table = self::API_TABLE;
        $condition = "id = $api_id";
        $sql = "SELECT api_access FROM $table WHERE id = $api_id LIMIT 1";
        $api_access = $this->admin->getOne($sql);
        $api_access = $api_access['api_access'];
        if ($api_access == null) {
            $api_access = [];
        }else{
            $api_access = json_decode($api_access,true);
        }
        if ($type) {
            $api_access[] = $admin_id;
        }else{
            foreach ($api_access as $k => $v) {
                if ($v == $admin_id) {
                    unset($api_access[$k]);
                }
            }
        }
        $api_access = array_values($api_access);
        $update = [ 'api_access' => json_encode($api_access)];
        $res = $this->admin->update($table,$update, $condition);
        return $res;
    }
}