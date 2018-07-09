<?php
namespace app\admin\model;
use think\Model;
class Privilege extends Model
{
	public function pritree() {
		$data=$this->select();
		return $this->resort($data);
	}

	public function resort($data,$parent_id=0,$level=0) {
		static $ret=array();
		foreach ($data as $k => $v) 
		{
			if($v['parent_id']==$parent_id)
			{
				$v['level']=$level;
				$ret[]=$v;
				$this->resort($data,$v['id'],$level+1);
			}
		}
		return $ret;
	}
}




?>