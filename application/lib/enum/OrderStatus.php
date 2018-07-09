<?php
namespace app\lib\enum;
class OrderStatus{
    //未支付
    const UNPAID=0;
    //已支付
    const PAID = 1;
    //已结束
    const DELIVERED = 2;
    //已支付，但设备异常
    const PAID_BUT_OUT_OF = 4;
}