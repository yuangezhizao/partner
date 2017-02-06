<?php 
namespace Traffic\Controller;
use \Traffic\Controller;

// 口袋流量用户控制

class IndexController extends BaseController{

	// 用户首页
	public function Index(){
		$this->display('index');
	}

	// pay页
	public function showPay(){
		$this->display('pay');
	}

	// 获取商品信息接口
	public function getGoods(){

		$data = M('kdgx_traffic_goods')->field('id,goods_name,goods_price,goods_type')
		->where("state !='1'")->order('id asc')->select();
		foreach ($data as $key => $value) {
			switch ($value['goods_type']) {
				case '1':
					$yd[] = $value;
					break;
				case '2':
					$dx[] = $value;
					break;
			}
		}
		$data = array('yd' => $yd,'dx'	=> $dx);
		echo json_encode($data);exit();
	}

	// 获取订单信息接口
	public function getOrder(){
		if(empty($_POST['uid'])){
			echo json_encode(array(
				'error' => 30001,
				'msg'	=> '参数错误',
				));exit();
		}
		$uid = decrypt_url($_POST['uid'],C("KEY_URL"));
		$orders = M('kdgx_traffic_goods')->field('o.id,o.phone,g.goods_name,g.goods_price,o.time,o.state')
		->alias('g')->join('kdgx_Traffic_order as o on o.goods_id=g.id')
		->where(array('uid'=>$uid))->order('time desc')->select();
		foreach ($orders as $key => $order) {
			if($order['time']<(time()-1800) && $order['state']==0){
				if(M('kdgx_traffic_order')->where(array('id'=>$order['id']))->save(array('state'=>'5'))){
					$orders[$key]['state'] = '5';
				}
			}
		}
		echo json_encode($orders);exit();
	}

	// 获取单个订单信息接口
	public function getOnlyOrder(){
		if(empty($_POST['order_id'])){
			echo json_encode(array(
				'error' => 30001,
				'msg'	=> '参数错误',
				));exit();			
		}
		$order = M('kdgx_traffic_goods')->field('o.phone,g.goods_name,g.goods_price,o.time,o.state,g.goods_type')
		->alias('g')->join('kdgx_Traffic_order as o on o.goods_id=g.id')
		->where("o.id='".$_POST['order_id']."' or o.phone='".$_POST['order_id']."'")->find();
		if($order['time']<(time()-1800) && $order['state']==0){
			if(M('kdgx_traffic_order')->where(array('id'=>$order['id']))->save(array('state'=>'5'))){
				$order['state'] = 5;
			}
		}
		$order['0'] = $order;
		echo json_encode($order);exit();
	}

		// 获取单个订单信息接口
	public function getOrders(){
		$order = M('kdgx_traffic_goods')->field('o.phone,g.goods_name,g.goods_price,o.time,o.state,g.goods_type')
		->alias('g')->join('kdgx_Traffic_order as o on o.goods_id=g.id')
		->where("o.phone like '%".$_POST['order_id']."%'")->select();
		if($order['time']<(time()-1800) && $order['state']==0){
			if(M('kdgx_traffic_order')->where(array('id'=>$order['id']))->save(array('state'=>'5'))){
				$order['state'] = 5;
			}
		}
		echo json_encode($order);exit();
	}

	// 提交订单接口
	public function editOrder(){
		$order = $_POST;
		$order['uid'] = decrypt_url($order['uid'],C("KEY_URL"));
		$order['time'] = time();
		if($data = M('kdgx_traffic_order')->create($order)){
			if($order_id = M('kdgx_traffic_order')->data($data)->add()){
				echo json_encode(array(
					'error' => 0,
					'msg' =>  $order_id,
					));exit();
			}
		}
		echo json_encode(array(
			'error' => 30002,
			'msg' => '服务器繁忙,请稍后再试',
			));exit();
	}

}