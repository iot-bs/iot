<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/6/12 0012
 * Time: 15:05
 */

namespace app\home\controller;

/**
 * 查看租用设备接口
 * Class Deviceshow
 * @package app\home\controller
 */
class Deviceshow extends Base
{
    protected $beforeActionList=[
        'checkExclusiveScope' =>['only' => 'getalldevices,getdevicebyid'],
    ];
    public function getAllDevices()
    {
        $list = $this->deviceshow->getAllDevices();
        if(empty($list))
        {
            return show(0,'没有设备~');
        }
        return show(1,'sucess',$list);
    }
    public function getDeviceById($id)
    {
        $device = $this->deviceshow->getDeviceById($id);
        if(empty($device))
        {
            return show(0,'没有设备~');
        }
        return show(1,'sucess',$device);

    }
}