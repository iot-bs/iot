<?php
namespace app\admin\controller;

class Order extends Base {
	public $order;
	public $orderStatus;
	public function initialize() {
		$this->order = model("Order");
		$this->orderStatus = model('OrderStatus');
		$this->type = config('device.deviceType');
	}
	/**
	 * 充值订单
	 */
	public function index() {
		$so = input('get.so');
		$type = input('get.type','1');
		if (!empty($so)) {
			$where[] = ['c_username','like', "%".$so."%"];
		}
		$where[] = ['c_pay_status','=',$type];
        $list = $this->order->getOrder($where);
//		var_dump($list);exit;
        $this->assign([
			'title' => '订单列表',
			'list' => $list,
			'so' => input('get.so'),
            'type' => $type,
            'types' => [0,1,2],
		]);
		return $this->fetch();
	}
	/*
	 * 订单状态表
	 */
	public function orderStatus()
    {
        if(request()->isGet()){
            $orderId = input('get.id');
            if(empty($orderId)){
                $this->error('缺少订单id');
            }
            $list = $this->orderStatus->getOrderStatus(['c_order_id' => $orderId]);
            return $this->fetch('',[
                'title' => '充电状态',
                'list' => $list,
            ]);
        }
        $this->error('非法请求');
    }
	/**
	 * 订单详情
	 * @return xmind
	 */
	public function orderDetail() {
		if (request()->isGet()) {
			$id = input('get.id');
			$list = $this->order->field('o.*,u.c_nickname,d.c_name')
				->alias('o')
				->join('LEFT JOIN __USER__ u ON o.c_uid=u.c_id')
				->join('LEFT JOIN __DEVICE__ d ON o.c_device_id=d.c_deviceid')
				->where(['o.c_orderid' => $id, 'o.c_isdel' => 0])->find();
			//获取租用类型配置文件
			$type = $this->type;
			if ($list) {
				$list['c_status'] = model("Order")->orderStatus($list['c_status']);
				$list['c_type'] = $type[$list['c_type']];
				$list['c_start_time'] = date('Y-m-d H:i:s', $list['c_start_time']);
				$list['c_shop_time'] = date('Y-m-d H:i:s', $list['c_shop_time']);
				$list['c_add_time'] = date('Y-m-d H:i:s', $list['c_add_time']);
				$list['c_pay_time'] = date('Y-m-d H:i:s', $list['c_pay_time']);
				switch ($list['c_charge_type']) {
				case 0:
					$list['c_charge_type'] = '未知';
					break;
				case 1:
					$list['c_charge_type'] = '手机充电';
					break;
				case 1:
					$list['c_charge_type'] = '电动车充电';
					break;
				}
				switch ($list['c_pay_status']) {
				case 0:
					$list['c_pay_status'] = '未支付';
					break;
				case 1:
					$list['c_pay_status'] = '已支付';
					break;
				}
				return json([
					'msg' => '',
					'data' => $list,
					'status' => 0,
				]);
			}
			return json([
				'msg' => '没有数据',
				'status' => 1,
			]);
		}
	}
	/**
	 * 带走租用设备审核
	 * @return minx
	 */
	public function audit() {
		if (request()->isGet()) {
			$orderId = input("get.orderId");
			$status = input("get.status");
			if (empty($orderId)) {
				return json([
					'msg' => '请求参数不得为空.',
					'status' => 1,
				]);
			}
			if ($status == 1) {
				return json([
					'msg' => '审核成功',
					'status' => 0,
				]);
			}
			return json([
				'msg' => '审核失败',
				'status' => 1,
			]);
		}
	}

	/**
	 * 删除
	 */
	public function del() {

		if (request()->isGet()) {
			if ($this->order->destroy(input('get.id'))) {
			    if($this->orderStatus->where('c_order_id',input('get.id'))->delete())
                {
                    return json(array(
                        'msg' => '删除成功！',
                        'status' => 1,
                    ));
                }

			} else {
				return json(array(
					'msg' => '删除失败！',
					'status' => 0,
				));
			}
		}
	}

}

?>