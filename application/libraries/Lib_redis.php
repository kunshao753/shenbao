<?php
class Lib_redis
{
    protected $_config;
    protected $_group = 'default';
    protected $_redis = NULL;
    protected $_w = NULL;
    protected $_r = NULL;

	public function __construct($options = '')
	{
		if(!empty($options) && is_array($options))
		{
		    $this->_group = $options['group'];
		}
        $CI =& get_instance();
		if ($CI->config->load('redis', TRUE, TRUE))
		{
			$config = $CI->config->item('redis');
            $redis_config = $config[$this->_group];
		}
        if(!$redis_config)
        {
            log_message('error', 'redis数据库配置错误！');
        }
        $this->setConfig($redis_config);
        $this->_w = $this->connect('w');
        $this->_r = $this->connect('r');
	}

	/**
	 * 设置配置文件
	 */
	private function setConfig($config = array())
	{
	    if (!is_array($config) || !isset($config['r']['host']) || !isset($config['r']['port']) || !isset($config['r']['auth']) )
	    {
	        log_message('error', 'Redis ReadDatabase Params Wrong');
	    }
	    if (!is_array($config) || !isset($config['w']['host']) || !isset($config['w']['port']) || !isset($config['w']['auth']) )
	    {
            log_message('error', 'Redis WriteDatabase Params Wrong');
	    }
	    $this->_config['r']['host'] = $config['r']['host'];
	    $this->_config['r']['port'] = $config['r']['port'];
	    $this->_config['r']['auth'] = $config['r']['auth'];

	    $this->_config['w']['host'] = $config['w']['host'];
	    $this->_config['w']['port'] = $config['w']['port'];
	    $this->_config['w']['auth'] = $config['w']['auth'];
	}

	/**
	 * 连接redis服务器
	 */
	public function connect($type='w')
	{
        $conn = new Redis();
	    $conn->connect ( $this->_config[$type]['host'], $this->_config[$type]['port'] );
	    if(!empty($this->_config[$type]['auth'])) {
            $conn->auth($this->_config[$type]['auth']);
	    }
		$instance = '_'.$type;
		return $this->$instance = $conn;
	}

	/**
	 * 关闭链接
	 */

	public function close()
	{
        $this->_w->close();
        $this->_r->close();
	}
	/**
	 * 原子操作 自加1
	 * @param unknown $key
	 */
	public function incr($key)
	{
	    return $this->_w->incr($key);
	}
	/**
	 * 原子递减 自加1
	 * @param unknown $key
	 */
	public function decrBy($key,$value=1)
	{
	    return $this->_w->decrBy($key,$value);
	}
	/**
	 * 选择DB
	 * 使用例子
	 * $redis->select(2, 'w');
       $redis->set('h', '600');
       $redis->select(2, 'r');
       $redis->get('h');
	 */
	public function select($db_number=0, $db='w')
	{
        $db_number = intval($db_number);
        $db = '_'.$db;
		return $this->$db->select($db_number);
	}

	/**
	 * set
	 */
	public function set($key ,$value)
	{
	    return $this->_w->set($key, $value);
	}
    /**
	 * set
	 */
	public function setnx($key ,$value)
	{
	    return $this->_w->setnx($key, $value);
	}
	/**
	 * get
	 */
	public function get($key)
	{
	    return $this->_r->get($key);
	}
	/**
	 * set
	 */
	public function hSet($key ,$filed, $value)
	{
		return $this->_w->hSet($key, $filed,$value);
	}

	/**
	 * get
	 */
	public function hGet($key, $filed)
	{
		return $this->_r->hGet($key, $filed);
	}

	/**
	 * hMset
	 */
	public function hMset($key, $hashKeys){
		return $this->_w->hMset($key, $hashKeys);
	}

	/**
	 * hMget
	 */
	public function hMget($key, $hashKeys){
		return $this->_r->hMget($key, $hashKeys);
	}

	/**
	 * hIncrBy
	 */
	public function hIncrBy( $key, $hashKey, $value ) {
		return $this->_w->hIncrBy($key, $hashKey, $value);
	}

	/**
	 * hGetAll
	 */
	public function hGetAll($key)
	{	
		return $this->_r->hGetAll($key);
	}

	public function hDel($key,$filed)
	{
		return $this->_w->hDel($key, $filed);
	}
	
	/**
	 * delete
	 */
	public function delete($key)
	{
	    return $this->_w->delete($key);
	}

	/**
	 * deletes
	 * 删除多条,未测试
	 */
	public function deletes($keys)
	{
		$keys_str = implode(',', $keys);
	    return $this->_w->delete($keys_str);
	}

	/**
	 * move
	 */
	public function move($key, $db)
	{
	    return $this->_w->set($key, $db);
	}

	/**
	 * zAdd
	 * Sorted sets
	 */
	public function zAdd($key, $score, $value)
	{
	    return $this->_w->zAdd($key, $score, $value);
	}

	/**
	 * zDelete
	 * Sorted sets
	 */
	public function zDelete($key, $value)
	{
		return $this->_w->zDelete($key, $value);
	}

	/**
	 * zRange
	 * Sorted sets
	 * 返回名称为 key 的 zset 中 member 元素的排名(按 score 从小到大排序)即下标
	 */
	public function zRange($key, $start=0, $end=10, $withscores=true)
	{
	    return $this->_r->zRange($key, $start, $end, $withscores);
	}

	/**
	 * zRevRange
	 * Sorted sets
	 * 返回名称为 key 的 zset 中 member 元素的排名(按 score 从大到小排序)即下标
	 */
	public function zRevRange($key, $start=0, $end=10, $withscores=true)
	{
		return $this->_r->zRevRange($key, $start, $end, $withscores);
	}

	/**
	 * zRevRangeByScore
	 */
	public function zRevRangeByScore($key, $start, $end, array $options = array())
	{
		return $this->_r->zRevRangeByScore($key, $start, $end, $options);
	}

	/**
	 * zCard
	 * 计算有序集合中指定字典区间内成员数量
	 */
	public function zCard($key)
	{
		return $this->_r->zCard($key);
	}

	/**
	 * zcount
	 * 计算有序集合中指定字典区间内成员数量
	 */
	public function zcount($key, $min, $max)
	{
		return $this->_r->zcount($key, $min, $max);
	}


	/**
	 * lPush
	 * List
	 */
	public function lPush($key, $value)
	{
	    return $this->_w->lPush($key, $value);
	}
	/**
	 * POP
	 */
	public function rPop($key)
	{
	    return $this->_w->rPop($key);
	}
	/**
	 * size
	 */
	public function lSize($key)
	{
		return $this->_w->lSize($key);
	}

	/**
	 * rpoplpush
	 * list
	 */
	public function rpoplpush($key, $value)
	{
	    return $this->_w->rpoplpush($key, $value);
	}

	/**
	 * 获取指定起始与结束的队列元素
	 * @param string $key
	 * @param int $start
	 * @param int $stop
	 */
	public function lRange($key, $start, $stop)
	{
	    return $this->_r->lRange($key, $start, $stop);
	}

	/**
     * 设置过期时间
     * @param string $key
     * @param int $time 单位秒
     */
    public function setTimeout($key, $time)
    {
    	return $this->_w->setTimeout($key, $time);
    }

    /**
     * 检测键是否存在[xuchen]
     * @param string $key
     */
    public function exists($key)
    {
    	return $this->_r->exists($key);
    }

    /**
     * 列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除[xuchen]
     * @param string $key
     * @param int $start
     * @param int $end
     */
    public function ltrim($key, $start, $end)
    {
    	return $this->_w->ltrim($key, $start, $end);
    }
    /**
     * 列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除[xuchen]
     * @param string $key
     * @param int $start
     * @param int $end
     */
    public function keys($keys)
    {
    	return $this->_r->keys($keys);
    }

	public function sAdd($key, $value)
	{
		return $this->_w->sAdd($key, $value);
	}

	public function sMembers($key)
	{
		return $this->_w->sMembers($key);
	}
	public function ttl($key)
	{
		return $this->_w->ttl($key);
	}

	public function sIsMember($key,$value)
	{
		return $this->_r->sIsMember($key,$value);
	}

	public function sRem($key,$value)
	{
		return $this->_w->sRem($key,$value);
	}
    public function multi($mode = Redis::MULTI,$t = 'w')
	{
	    if($t == 'w')
        {
            $res =$this->_w->multi($mode);
        }
		elseif($t == 'r')
        {
            $res =$this->_r->multi($mode);
        }
        return $res;
	}
    public function exec($t = 'w')
	{
        if($t == 'w')
        {
            $this->_w->exec();
        }
		elseif($t == 'r')
        {
            $this->_r->exec();
        }
	}


}