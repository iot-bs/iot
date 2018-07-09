<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/29
 * Time: 16:52
 */

namespace app\home\controller;


class Charge extends Base
{
    protected $beforeActionList=[
        'checkExclusiveScope' =>['only' =>'getchargebytype'],
    ];
    //根据类型获取设备充电价格
    public function getChargeByType()
    {
        $res = $this->charge->getCharges();
        if(empty($res))
        {
            return show(0,'充电价格异常');
        }
        $res->hidden(['c_unit','c_status','c_add_time','c_isdel'])->toArray();
        $list = [];
        foreach ($res as $k =>$v)
        {
            $list[$v['c_type']][] = $v;
        }
        return show(1,'sucess',$list);
    }
}