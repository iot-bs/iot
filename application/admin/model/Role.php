<?php
namespace app\admin\model;
use think\Model;
class Role extends Model
{
    public function getRoles()
    {
        return $this->where('isdel','<>',0)->select();
    }
    public function getallRoles()
    {
        return $this->where('isdel','<>',0)->paginate();
    }
}





?>