<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/22
 * Time: 17:28
 */

namespace app\admin\model;
use think\Model;

class OrderStatus extends Model
{
    public function getOrderStatus($where)
    {
        $res = $this->where($where)->paginate();
        return $res;

    }
}