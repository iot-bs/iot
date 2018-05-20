<?php
namespace model;
use think\Db;
/**
 * @Author   liuxiaodong
 * @DateTime 2018-04-09
 * @return   [type]      [封装的monitor数据库类]
 */

class Monitor
{
    public $table;
    /**
     * 单例模式
     * @var null
     */
    private static $_instance = null;
    public function __construct()
    {
        $this->table = Db::table('t_monitor');
    }

    public static function getInstance(){
        if(empty(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
	/**
	 * @param    [type]      $where [where condition]
	 * @return   [type]             [return all list]
	 */
	public  function getAllMonitors($where = []) {
		return $this->table->where($where)->select();
	}
	/**
	 * @param    [type]      $where [condition]
	 * @return   [type]             [return one res]
	 */
	public function getOneMonitor($where) {
		return $this->table->where($where)->find();
	}
	public function updateMonitor($data) {
		return $this->table->update($data);
	}
	public function insertMonitor($data){
	    echo "insert model monitor  ----------".PHP_EOL;
	    $data['create_time'] = time();
	    try{
            $this->table->insert($data);
        }catch (\Exception $e){
	        echo "monitor data chongfu l ";
        }
	}
}