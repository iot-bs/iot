<?php
namespace app\admin\model;

use think\Model;

class Deviceshow extends Model
{
    protected $pk = 'c_id';
    public function getAllDevices()
    {
        return $this->select();
    }
    public function getDeviceById($id)
    {
        return $this->get($id);
    }
}