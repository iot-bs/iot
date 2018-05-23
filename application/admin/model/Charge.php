<?php

namespace app\admin\model;

use think\Model;

class Charge extends Model
{
	protected $autoWriteTimestamp = true;
	protected $pk = 'c_chargeid';
	/**
	 * @param  [type]
	 * @param  string
	 * @param  integer
	 * @return [list]
	 * 获取charge数据
	 */
	public function getChargeList($where , $order = 'c_chargeid asc' ,$limit = 15)
    {
		return $this->where($where)
					->order($order)
					->paginate($limit);
	}
	//通过id获取charge;
    public function getChargeById($id)
    {
	    $res = $this->get($id);
	    return $res;
    }

}