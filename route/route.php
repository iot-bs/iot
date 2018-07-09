<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//首页获取所有的设备
Route::get('home/device/all','home/Device/getAllDevices');
//根据设备id获取详细信息
Route::group('home/device',function() {
    Route::get('/all', 'home/Device/getAllDevices');
    Route::get('/:id', 'home/Device/getDetailById');
});
Route::get('home/device/:id','home/Device/getDetailById');
//用户获取已经租用的设备
Route::get('home/user/hire','home/User/getHireByUserId');
Route::get('home/user/rent','home/User/getRentByUserId');
Route::get("home/rent/status",'home/Rent/getRentStatus');
//获取充电价格
Route::get('home/charge/price','home/Charge/getChargeByType');
//获取充电状态
Route::post('home/charge/status','home/OrderStatus/getStatus');
//支付接口
Route::post('home/pay/preorder','home/Order/PayOrder');
//微信授权接口
Route::get('home/token/user','home/Wechat/getToken');
//验证令牌
Route::get('home/token/verify/:token','home/Wechat/verifyToken');
//获取用户信息g
Route::get('home/user/info/:id','home/User/getUserInfo');
Route::get('home/user/scan', 'home/User/getWxConfig');
//获取设备状态
Route::get('home/status/:deviceid', 'home/Device/getDeviceStatus');
//提交紧急充电信息
Route::post('home/emergency/service','home/Emergency/saveEmergencyServer');
//获取设备展示
Route::get('home/show/all', 'home/Deviceshow/getAllDevices');
Route::get('home/show/:id', 'home/Deviceshow/getDeviceById');
//租用设备控制接口
Route::post('home/control/relay','home/Rent/controlRelay');
Route::post('home/control/active','home/Rent/active');
