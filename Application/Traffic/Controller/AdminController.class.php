<?php 
namespace Traffic\Controller;
use \Traffic\Controller;

// 口袋流量管理者控制

class AdminController extends BaseController{

	private $vipUid = array('10001','10002','10007','98047');
	// 后台首页
	public function Index(){
		$uid = decrypt_url(session('uid'),C("KEY_URL"));
		if(in_array($uid, $this->vipUid)){
			$this->display('admin');
		}else{
			exit('没有访问权限!');
		}
	}

	// 获取订单信息接口
	public function getOrder(){
		$data = M('kdgx_traffic_order')->alias('o')
		->field('g.goods_name,g.goods_price,o.phone,o.id,o.time,o.state,g.goods_type')
		->join('kdgx_traffic_goods as g on o.goods_id=g.id')
		->order("o.time desc")->select();
		foreach ($data as $key => $value) {
			switch ($value['goods_type']) {
				case '1':
					$data[$key]['goods_name'] .= '移';
					break;
				case '2':
					$data[$key]['goods_name'] .= '电';
			}
			if($value['time']<(time()-1800) && $value['state']==0){
				if(M('kdgx_traffic_order')->where(array('id'=>$value['id']))->save(array('state'=>'5'))){
					$data[$key]['state'] = 5;
				}
			}
		}
		echo json_encode($data);exit();
	}
	

	// 获取商品信息接口
	public function getGoods(){
		$data = M('kdgx_traffic_goods')->order('id asc')->select();
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

	// 修改订单状态接口
	public function editOrder(){
		$order = $_POST;
		if(empty($order['id'])||empty($order['state'])){
			echo json_encode(array(
				'error' => 31001,
				'msg'  => '参数错误',
				));exit();
		}
		if(M('kdgx_traffic_order')->where(array('id'=>$order['id']))->save(array('state'=>$order['state']))){
			if($order['state']==4){
				$res = M('kdgx_traffic_order')->where(array('id'=>$order['id']))->find();
				$orderInfo = M('kddx_user_openid')->where(array('uid'=>$res['uid'],'public_id'=>C("PUBLIC_ID")))->find();
				$data = array(
					'type' => 4,
					'first' => '您好，您的订单【订单号】状态已更新',
					'OrderSn' => $res['id'],
					'OrderStatus'=>'充值成功',
					'OrderStatus_color' => '#0000ff',
					'url' =>'http://mp.weixin.qq.com/s/qB0bqjiT5YRUYFR9vo58Pg',
					'openid' => $orderInfo['openid'],
					'remark' => '点此查看流量共享查询方法',
					'color' => '#000000',);
				$sendTemplate = new \Org\Util\Wechat();
				$response = $sendTemplate->sendTemplateMsg($data);
			}
			echo json_encode(array(
				'error' => 0,
				'msg'	=> '成功',
				));exit();
		}else{
			echo json_encode(array(
				'error'	=> 31002,
				'msg' => '修改失败',
				));exit();
		}
	}

	public function editGood(){
		if(empty($_POST['id'])){
			echo json_encode(array(
				'error' => 31002,
				'msg'  => '参数错误',
				));exit();
		}
		if(M('kdgx_traffic_goods')->save($_POST)){
			echo json_encode(array(
				'error' => 0,
				'msg'	=> '成功',
				));exit();			
		}else{
			echo json_encode(array(
				'error'	=> 31002,
				'msg' => '修改失败',
				));exit();
		}
	}

	// 退款接口
	public function refund(){
		if(empty($_POST['order_id'])){
			echo json_encode(array(
				'error' => 31003,
				'msg'	=> '参数错误',
				));exit();			
		}
		$res = M('kdgx_traffic_order')->alias('o')
		->field('g.goods_price,o.id,o.uid')
		->join('kdgx_Traffic_goods as g on o.goods_id=g.id')
		->where(array('o.id'=>$_POST['order_id'],'o.state'=>'1'))->find();
		$url = 'http://www.pocketuniversity.cn/WxpayAPI/pay/refund.php';
		$data = array(
			'out_trade_no' =>$res['id'],
			'total_fee' => $res['goods_price'],
			'refund_fee' => $res['goods_price'],
			);
		$response = requestPost($url,$data);
		if($response['result_code']==$response['return_code']){
			if(M('kdgx_traffic_order')->where(array('id'=>$_POST['order_id']))->save(array('state'=>'3'))){
				$res = M('kdgx_traffic_order')->where(array('id'=>$_POST['order_id']))->find();
				$orderInfo = M('kddx_user_openid')->where(array('uid'=>$res['uid'],'public_id'=>C("PUBLIC_ID")))->find();
				$data = array(
					'type' => 4,
					'first' => '您好，您的订单【订单号】状态已更新',
					'OrderSn' => $res['id'],
					'OrderStatus'=>已退款,
					'OrderStatus_color' => '#000000',
					'openid' => $orderInfo['openid'],
					'url' =>'',
					'Remark' => '如有问题请联系小口袋：easypocket（微信号），小口袋将第一时间为您服务！',
					'color' => '#000000',);
				$sendTemplate = new \Org\Util\Wechat();
				$sendTemplate->sendTemplateMsg($data);
				echo json_encode(array(
					'error' => 0,
					'msg' => '成功',
					));exit();
			}
		}else{			
			echo json_encode(array(
				'error' => 31004,
				'msg' => '退款失败1',
				));exit();
		}
	}
}