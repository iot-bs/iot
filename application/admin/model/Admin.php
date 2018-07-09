<?php
namespace app\admin\model;

use think\Model;

class Admin extends Model
{


     /*管理员权限*/
    public function getpri($role_id){
      $role=model("Role");
      $pri=model("Privilege");
      $id = $role_id;
      $roleres = $role->get($id);
      if ($roleres['pri_id_list'] =='*') {
        session('privilege','*');
        //$menu=$pri->where("parent_id=0")->select();
        //foreach ($menu as $k => $v) {
        //$menu[$k]['sub']=$pri->where('parent_id='.$v['id'])->sleect();
        }else{
          $pris=$pri->field('CONCAT(mname,"/",cname,"/",aname) url')->where("id IN({$roleres['pri_id_list']})")->select();
          $_pris=array();
          foreach ($pris as $k => $v) {
            $_pris[]=$v['url'];
          }
          session('privilege',$_pris);
        }
    }
  
}