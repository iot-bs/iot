<?php
namespace model;
use think\Db;
class Device
{
    public $table;
    /**
     * @var null 单例模式
     */
    private static $_instance = null;
    public function __construct()
    {
        $this->table = Db::table('t_device');
    }

    /**
     * 单例模式
     */
    public static function getInstance()
    {
        if(empty(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
	/**
	 * @param    [type]      $where [where condition]
	 * @return   [type]             [return all list]
	 */
	public function getAllDevices() {
	    $res = $this->table->select();
	    print_r($res);
//		return $this->table->select();
	}
	public function getOneColumns($where = [], $column = ''){
		return $this->table->where($where)->column($column);
	}
	/**
	 * @param    [type]      $where [condition]
	 * @return   [type]             [return one res]
	 */
	public function getOneDevice($where) {
		return $this->table->where($where)->find();
	}
	public function updateDevice($data) {
		return $this->table->update($data);
	}
	public function insertDevice($data){
		return $this->table->insertGetId($data);
	}
}