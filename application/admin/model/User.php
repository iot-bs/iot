<?php
namespace app\admin\model;
use think\Model;

class User extends Model {

    protected $pk = 'c_id';
    //根据id获取用户信息
	public function getUserById($id)
    {
        $res = $this->get($id);
        return $res;
    }
    //根据条件获取用户信息
    public function getUser($where)
    {
        return $this->where($where)->find();
    }
    //增加用户
    public function add($data)
    {
        return $this->save($data);
    }
    //通过openid去获取信息
    public static function getByOpenID($openid){
        $user=self::where('c_openid','=',$openid)
            ->find();
        return $user;


    }

}

