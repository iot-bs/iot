<?php

function deviceStatus($data){

        foreach ($data as &$value)
        {
            $value["c_status"] = $value["c_status"] == 1?"暂停":"正常";
            $value["isregister"] = $value["isregister"] == 1?"已连接":"未连接";
        }
        return $data;
}
//分页函数
function pagination($obj)
{
    if($obj)
    {
        $params = request()->param();
        return '<div class="row">'.$obj->appends($params)->render().'</div>';
    }
    else
    {
        return '';
    }
}