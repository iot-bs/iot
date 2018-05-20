<?php
namespace model;

class Device
{

    public $table;
    public static $_instance = null;
    public function __construct(){
        $this->table = table('t_device');
    }
    public static function getInstance(){
        if(empty(self::$_instance)){
            self::$_instance = new self();
            }
            return self::$_instance;
    }

	/**
	 * @param    [type]      $where [where condition]
	 * @return   [type]             [return all list]
	 */
	public function getAllDevices($data = []) {
	    $where['where'] = $data;
	    $where['order'] = 'c_deviceid desc';
	    $res = $this->table->gets($where);
	    return $res;
	}
	public function getOneColumns($where = [], $column = ''){
	    $data['select'] = $column;
	    $data['where'] = $where;
		return $this->table->gets($data);
	}
	/**
	 * @param    [type]      $where [condition]
	 * @return   [type]             [return one res]
	 */
	public function getOneDevice($id,$where = 'c_deviceid')
    {
		return $this->table->get($id,$where);
	}
	public function updateDevice($id,$data,$where='c_deviceid') {
		return $this->table->set($id,$data,$where);
	}
	public function insertDevice($data){
		return $this->table->put>($data);
	}
	/*
	 * 默认使用主键作为条件，可修改$field指定其他字段
	 * del方法只会删除一条记录
	 */
	public function delDevice($id, $field = 'c_deviceid')
    {
        $this->table->del($id,$field);
    }
}