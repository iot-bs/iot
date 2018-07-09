<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/6/11 0011
 * Time: 11:46
 */

namespace app\admin\model;
use think\Model;

class Emergency extends Model
{
    public function add($data)
    {
        return $this->allowField(true)->save($data);
    }
    /**
     * 获取数据
     */
    public function getallEmergency($status)
    {
        return $this->where('c_status',$status)->paginate();
    }
}