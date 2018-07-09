<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/7 0007
 * Time: 18:33
 */

namespace app\admin\model;
use think\Model;


class SafeLimit extends Model
{
    protected $pk = 'c_deviceid';
    public function getSafeLimitList($where =[], $order = 'c_deviceid asc', $limit = 15)
    {
        return $this->where($where)
            ->order($order)
            ->paginate($limit);
    }

    public function doUpdate($data,$id)
    {
        return $this->save($data,$id);
//        print_r($this->getLastSql());exit;

    }
    public function getOneSafeLimit($id)
    {
        return $this->get($id);
    }
}