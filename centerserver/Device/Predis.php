<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/6/13 0013
 * Time: 15:36
 */
namespace Device;
class Predis
{
    public $redis = '';
    /**
     * @var null 单例模式
     */
    private static $_instance = null;

    /**
     * 单例模式
     * @return Predis|null
     * @throws \Exception
     */
    public static function getInstance()
    {
        if(empty(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function connect()
    {
        $res = $this->redis->connect(REDIS_HOST,REDIS_PORT,OUT_TIME);
        return $res;
    }
    private function __construct()
    {
        $this->redis = new \Redis();
        $res = $this->connect();
        if($res === false){
            $this->connect();
        }
    }

    /**
     * @param $key
     * @param $value
     * @param int $time
     * @return bool|string
     * 将传递的值存入redis
     */
    public function set($key, $value ,$time = 0 )
    {
        if(!$key)
        {
            return '';
        }
        if(is_array($value)){
            $value = json_encode($value);
        }
        if(!$time)
        {
            return $this->redis->set($key,$value);
        }
        return $this->redis->set($key,$value,$time);
    }

    /**
     * @param $key
     * @return bool|string
     * 通过key获取redis值
     */
    public function get($key)
    {
        if(!$key)
        {
            return '';
        }
        return $this->redis->get($key);
    }

    public function __call($name,$arguments)
    {
        if(count($arguments) !=2){
            return '';
        }
        return $this->redis->$name($arguments[0],$arguments[1]);
    }
}