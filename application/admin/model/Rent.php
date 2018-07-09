<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/6/11 0011
 * Time: 11:46
 */

namespace app\admin\model;
use think\Model;

class Rent extends Model
{
    protected $pk = 'c_id';
    public function devices()
    {
        return $this->belongsTo('Device','c_deviceid','c_deviceid');
    }
    public function add($data)
    {
        $data['c_status']  = 0;
        return $this->allowField(true)->save($data);
    }
    /**
     * 获取数据
     */
    public function getAllRents($where = [])
    {
        return $this->where($where)->paginate();
    }
    public function getRentByUserId($id,$page = 1,$size = 15)
    {
        $where[] = ['c_userid','=',$id];
//        $where[] = ['c_status','neq',0];
//        $where['c_status'] = array('neq',0);
        return $this->with('devices')
            ->where($where)
            ->order('c_id','desc')
            ->paginate($size,false,['page'=>$page]);
    }
    public function updateTime($id, $where = [])
    {
        return $this->save($where,['c_id' => $id]);
    }
    public function getRentByStatus($where = [])
    {
        return $this->where($where)->select();
    }
}