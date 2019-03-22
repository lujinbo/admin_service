<?php
/**
 * redis操作类
 */
namespace Lib;
!defined('GREATANTS') && exit('Access Denied');

class Lib_Redis
{
	private $redis = null;
	
	private $connected = false;
	
	/**
	 * @param array $config
	 * $config = array('127.0.0.1','6379')
	 */
	public function __construct($config)
	{
		if(!$this->connected) {
			$this->redis = new \ Redis();
			$this->redis->connect($config[0],$config[1]);
			$this->connected = true;
		}
		return $this->redis;
	}
	
	/**
	 * 设置key
	 * @param string $key key名称
	 * @param mixed $value value数据
	 * @param int $timeout 过期时间
	 * @param bool $serialize 是否序列化
	 * @param bool $gzcompress 是否压缩
	 * @return bool
	 */
	public function set($key, $value, $timeout=86400, $serialize=true, $gzcompress=false)
	{
		//$value = $serialize ? serialize($value) : $value;
		$value = json_encode($value);
        $value = $gzcompress ? gzcompress($value) : $value;
		$res = $this->redis->set($key,$value);
		($timeout > 0) && $this->redis->setTimeout($key,$timeout);
		return $res; 
	}
	
	/**
	 * 获得key数据
	 * @param $key
	 * @param bool $serialize
	 * @param bool $gzcompress
	 * @return bool|mixed|string
	 */
	public function get($key,$serialize=true,$gzcompress=false)
	{
		$value = $this->redis->get($key);
		$value = json_decode($value,true);
		//$value = $serialize ? unserialize($value) : $value;
		$value = $gzcompress ? gzuncompress($value) : $value;
		return $value;
	}
	
	/**
	 * 判断key是否存在
	 * @param $key
	 * @return bool
	 */
	public function exists($key)
	{
		return $this->redis->exists($key);
	}
	
	/**
	 * 删除key数据
	 * @param $key
	 */
	public function del($key)
	{
		return $this->redis->delete($key);
	}
	
	/**
	 * 设置某个key的过期时间
	 * @param string $key 
	 * @param int $expire 过期秒数
	 */
	public function setTimeOut($key,$expire)
	{
		return $this->redis->setTimeout($key,$expire);
	}
	
	/**
	 * 获取某key剩余的时间
	 * @param $key
	 * @return int
	 */
	public function ttl($key)
	{
		return $this->redis->ttl($key);
	}
	
	/**
	 * 数据自增
	 * @param $key
	 * @param int $value
	 * @param int $timeout
	 * @return int
	 */
	public function incr($key,$value=1,$timeout=86400)
	{
		$res = $this->redis->incr($key,$value);
		($timeout > 0) && $this->redis->setTimeout($key,$timeout);
		return $res;
	}
	
	/**
	 * 数据自减
	 * @param $key
	 * @param int $value
	 * @param int $timeout
	 * @return int
	 */
	public function decr($key,$value=1,$timeout=86400)
	{
		$res = $this->redis->decr($key,$value);
		($timeout > 0) && $this->redis->setTimeout($key,$timeout);
		return $res;
	}
	
	/**
	 * hash设置
	 * @param $hashname
	 * @param $key
	 * @param $value
	 * @param int $timeout
	 * @return int
	 */
	public function hset($hashname,$key,$value,$timeout=86400)
	{
		$res = $this->redis->hSet($hashname,$key,$value);
		($timeout > 0) && $this->redis->setTimeout($hashname,$timeout);
		return $res;
	}
	
	/**
	 * 获得hash数据
	 * @param $hashname
	 * @param $key
	 * @return string
	 */
	public function hget($hashname,$key)
	{
		return $this->redis->hGet($hashname,$key);
	}
	
	/**
	 * 删除hash数据
	 * @param $hashname
	 * @param $key
	 * @return int
	 */
	public function hdel($hashname,$key)
	{
		return $this->redis->hDel($hashname,$key);
	}
	
	/**
	 * 获取hash长度
	 * @param $hashname
	 * @return int
	 */
	public function hlen($hashname)
	{
		return $this->redis->hLen($hashname);
	}
	
	/**
	 * 获取hash中所有的key
	 * @param $hashname
	 * @return array
	 */
	public function hkeys($hashname)
	{
		return $this->redis->hKeys($hashname);
	}
	
	/**
	 * 判断hash中是否存在key
	 * @param $hashname
	 * @param $key
	 * @return bool
	 */
	public function hexists($hashname,$key)
	{
		return $this->redis->hExists($hashname,$key);
	}
	
	/**
	 * 获取hash中所有值
	 * @param $hashname
	 * @return array
	 */
	public function hgetall($hashname)
	{
		return $this->redis->hGetAll($hashname);
	}
	
	/**
	 * 数据插入队列表头
	 * @param $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $serialize
	 * @param bool $gzcompress
	 * @return int
	 */
	public function lpush($key,$value,$timeout=86400,$serialize=true,$gzcompress=false)
	{
		//$value = $serialize ? serialize($value) : $value;
		$value = $gzcompress ? gzcompress($value) : $value;
		$res = $this->redis->lPush($key,$value);
		($timeout > 0) && $this->redis->setTimeout($key,$timeout);
		return $res;
	}
	
	/**
	 * 数据插入队列表尾
	 * @param $key
	 * @param $value
	 * @param int $timeout
	 * @param bool $serialize
	 * @param bool $gzcompress
	 * @return int
	 */
	public function rpush($key,$value,$timeout=86400,$serialize=true,$gzcompress=false)
	{
		//$value = $serialize ? serialize($value) : $value;
		$value = $gzcompress ? gzcompress($value) : $value;
		$res = $this->redis->rPush($key,$value);
		($timeout > 0) && $this->redis->setTimeout($key,$timeout);
		return $res;
	}
	
	/**
	 * 移除并返回列表 key 的头元素。
	 * @param $key
	 * @return string
	 */
	public function lpop($key)
	{
		return $this->redis->lPop($key);
	}
	
	/**
	 * 移除并返回列表 key 的尾元素。
	 * @param $key
	 * @return string
	 */
	public function rpop($key)
	{
		return $this->redis->rPop($key);
	}

	/**
	 * 返回列表 key 的长度
	 * @param $key
	 */
	public function lsize($key)
	{
		return $this->redis->lSize($key);
	}
	
	/**
	 * 取出队列的某一段
	 * @param $key
	 * @param $start
	 * @param $end
	 * @return array
	 */
	public function lrange($key,$start,$end)
	{
		return $this->redis->lrange($key,$start,$end);
	}
	
	/**
	 * 将一个或多个 member 元素及其 score 值加入到有序集 key 当中
	 * @param $key
	 * @param $score
	 * @param $member
	 * @param int $timeout
	 * @return int
	 */
	public function zadd($key,$score,$member,$timeout=86400)
	{
		$res = $this->redis->zAdd($key,$score,$member);
		($timeout > 0) && $this->redis->setTimeout($key,$timeout);
		return $res;
	}
	
	/**
	 * 返回有序集 key 中，成员 member 的 score 值
	 * @param $key
	 * @param $member
	 * @return float
	 */
	public function zscore($key,$member)
	{
		return $this->redis->zScore($key,$member);
	}
	
	/**
	 * 获取有序集合中指定成员的排名，其中成员的位置按 score 值递减(从大到小)来排列
	 * @param $key
	 * @param $member
	 * @return int
	 */
	public function zrevrank($key,$member)
	{
		return $this->redis->zrevrank($key,$member);
	}
	
	/**
	 * 有序集 key 中，指定区间内的成员，其中成员的位置按 score 值递减(从小到大)来排列
	 * @param $key
	 * @param $start
	 * @param $end
	 * @param bool $flag
	 * @return array
	 */
	public function zrange($key,$start,$end,$flag=false)
	{
		return $this->redis->zrange($key,$start,$end,$flag);
	}
	
	/**
	 * 有序集 key 中，指定区间内的成员，其中成员的位置按 score 值递减(从大到小)来排列
	 * @param $key
	 * @param $start
	 * @param $end
	 * @param bool $flag
	 * @return array
	 */
	public function zrevrange($key,$start,$end,$flag=false)
	{
		return $this->redis->zrevrange($key,$start,$end,$flag);
	}
	
	/**
	 * 移除有序集合中的元素
	 * @param $key
	 * @param $member
	 * @return int
	 */
	public function zrem($key,$member)
	{
		return $this->redis->zrem($key,$member);
	}

    /**
     * @param $key
     * @param $member
     * @return int
     */
	public function zrank($key,$member){
	    return $this->redis->zRank($key,$member);
    }

    /**
     * 返回集合中key中的成员数量
     * @param $key
     * @return int
     */
    public function zcard($key){
	    return $this->redis->zCard($key);
    }
	
	/**
	 * 返回有序集合中 指定score区间中的个数
	 * @param $key
	 * @param $start
	 * @param $end
	 * @return int
	 */
    public function zcount($key,$start,$end)
	{
		return $this->redis->zCount($key,$start,$end);
	}

    /**
     * 返回有序集合中 在区间score中的数据
     * @param $key
     * @param $start
     * @param $end
     * @param $options
     * @return array
     */
    public function zRangeByScore($key,$start,$end,$options = array('withscores'=>TRUE)){
        return $this->redis->zRangeByScore($key,$start,$end,$options);
    }

    /**
     * 无序集合中添加元素
     * @param $key
     * @param $value
     * @return int
     */
    public function sAdd($key,$value){
        return $this->redis->sAdd($key,$value);
    }

    /**
     * 取得无序集合中的元素个数
     * @param $key
     * @return int
     */
    public function sCard($key){
        return $this->redis->sCard($key);
    }

    /**
     * 返回集合中的所有元素
     * @param $key
     * @return array
     */
    public function sMembers($key){
        return $this->redis->sMembers($key);
    }

    /**
     * 删除集合中的某个元素
     * @param $key
     * @param $val
     * @return int
     */
    public function sRem($key,$val){
        return $this->redis->sRem($key,$val);
    }

    /**
     * 检测链接是否断开
     * @return bool
     */
    public function ping(){
        $redis = $this->redis->ping();
        if($redis != '+PONG'){
            Lib_Log::getInstance()->logDebug('redis is disconnected');
            return false;
        }
        return true;
    }

    public function keys($pattern){
        return $this->redis->keys($pattern);
    }
}