<?php
namespace App\Conf;
!defined('GREATANTS') && exit('Access Denied');
/**
 * host:172.31.45.29
 * 7900 支付相关
 * 7901 用户金币相关
 * 7902 用户数据相关
 * 7903 活动任务排行榜相关
 * 7904 后台数据统计相关
 */
class Config_Redis
{
    public static $redisPayment = array(REDIS_IP,'6379');

    public static $redisCoins   = array(REDIS_IP,'6379');

    public static $redisUser    = array(REDIS_IP,'6379');

    public static $redisRank    = array(REDIS_IP,'6379');

    public static $redisAdmin   = array(REDIS_IP,'6379');
}
