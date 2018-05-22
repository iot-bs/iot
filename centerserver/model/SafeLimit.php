<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/8 0008
 * Time: 15:51
 */

namespace model;

class SafeLimit
{

    public $table;
    /**
     * 单例模式
     */
    public static $_instance = null;
    public static function getInstance()
    {
        if(empty(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct()
    {
        $this->table = table('t_safe_limit');
    }

    /**
     * 通过条件获取查询，如果主键非id 则必须填写where条件
     * @param $id
     * @param $where
     */
    public function getSafeLimit($id,$where ='c_deviceid')
    {
        return $this->table->get($id,$where);
    }
    /**
     * 将设备传上来的数据更新数据库安全值范围
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function updateSafeLimit($data){
        echo "--update safe set -------".PHP_EOL;
        echo "Model ------ db  SafeLimit ----------updateSafeLimit\n" . PHP_EOL;
        $sn = $data['DeviceSn'];
        $res = $this->table->get($sn,'c_devicesn');
        switch ($data['RequestControl'])
        {
            case '8':
                echo "this is current set".PHP_EOL;
                $current = unserialize($res['c_currentcon']);
                foreach ($current as $v){
                    if($v['No'] == $data['CurrentCon']['No']){
                        $v = $data['CurrentCon'];
                    }
                }
                $res = $this->updateOne($sn,['c_currentcon' => serialize($current)]);
                print_r("res:is ".$res);
                break;
            case '9':
                $voltage = unserialize($res['c_vdccon']);
                foreach ($voltage as $v){
                    if($v['No'] == $data['VdcCon']['No']){
                        $v = $data['VdcCon'];
                    }
                }
                $this->updateOne($sn,['c_vdccon' => serialize($voltage)]);
                break;
            case '10':
                $temp = unserialize($res['c_tempcon']);
                $temp = $data['TempCon'];
                $this->updateOne($sn,['c_tempcon' => serialize($temp)]);
                break;
            default:
                break;
        }

    }
    public function updateOne($id,$data,$where='c_devicesn') {
        return $this->table->set($id,$data,$where);
    }
}