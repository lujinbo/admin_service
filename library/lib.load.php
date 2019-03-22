<?php
/**
 * 操作数据(数据库,redis,memcache)的工厂
 */
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');

use App\Conf as Conf;

class Lib_Load
{
	//存储数据库对象
	private static $db = array('dbadmin'=>null);
	
	//存储redis链接对象
	private static $redis = array('redisPayment'=>null,'redisCoins'=>null,'redisUser'=>null,'redisRank'=>null,'redisAdmin'=>null);


	/**
	 * 后台数据库链接
     * @return Lib_Mysqli
	 */
	public static function dbadmin()
	{
		if(!self::$db['dbadmin'] instanceof Lib_Mysqli) {
			self::$db['dbadmin'] = new Lib_Mysqli(Conf\Config_Mysql::$dbadmin);
		}
		return self::$db['dbadmin'];
	}

	/**
	 * 支付相关
	 * @return Lib_Redis
	 */
	public static function redisPayment()
	{
        $instance = 'redisPayment';
        if((!self::$redis[$instance] instanceof Lib_Redis) || (!self::$redis[$instance]->ping())) {
            self::$redis[$instance] = new Lib_Redis(Conf\Config_Redis::$redisPayment);
        }
        return self::$redis[$instance];
	}

    /**
     * 用户金币相关
     * @return Lib_Redis
     */
    public static function redisCoins()
    {
        $instance = 'redisCoins';
        if((!self::$redis[$instance] instanceof Lib_Redis) || (!self::$redis[$instance]->ping())) {
            self::$redis[$instance] = new Lib_Redis(Conf\Config_Redis::$redisCoins);
        }
        return self::$redis[$instance];
    }
	
	/**
	 * 用户数据相关
	 * @return Lib_Redis
	 */
	public static function redisUser()
	{
        $instance = 'redisUser';
        if((!self::$redis[$instance] instanceof Lib_Redis) || (!self::$redis[$instance]->ping())) {
            self::$redis[$instance] = new Lib_Redis(Conf\Config_Redis::$redisUser);
        }
        return self::$redis[$instance];
	}
    /**
     * 活动任务排行榜相关
     * @return Lib_Redis
     */
    public static function redisRank()
    {
        $instance = 'redisRank';
        if((!self::$redis[$instance] instanceof Lib_Redis) || (!self::$redis[$instance]->ping())) {
            self::$redis[$instance] = new Lib_Redis(Conf\Config_Redis::$redisRank);
        }
        return self::$redis[$instance];
    }
    /**
	 * 后台数据统计相关
	 * @return Lib_Redis
	 */
	public static function redisAdmin()
	{
        $instance = 'redisAdmin';
        if((!self::$redis[$instance] instanceof Lib_Redis) || (!self::$redis[$instance]->ping())) {
            self::$redis[$instance] = new Lib_Redis(Conf\Config_Redis::$redisAdmin);
        }
        return self::$redis[$instance];
	}
}