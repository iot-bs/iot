<?php
/**
 * Created by PhpStorm.
 * User: liuxiaodong
 * Date: 2018/5/22
 * Time: 18:09
 */

namespace app\home\validate;
use think\Validate;

class Order extends Validate
{
    protected $rule = [
        ['c_sn','require'],
        ['c_transaction_id','require'],
        ['c_user_id','number'],
        ['c_username','require'],
        ['c_pay_time','require'],
        ['c_payment_id','number'],
        ['c_device_id','number'],
        ['c_charge_id','number'],
        ['c_charge_time','number'],
        ['c_total_price','number'],
        ['c_type','requrire'],
    ];
    protected $scene = [
        'checkPost' => ['c_user_id','c_device_id','c_charge_id','c_charge_time'],
    ];

}