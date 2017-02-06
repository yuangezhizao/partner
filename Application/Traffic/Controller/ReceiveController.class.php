<?php 
namespace Traffic\Controller;
use \Think\Controller;

// 口袋流量支付通知控制

class ReceiveController extends Controller{

	
	public function notify(){
		$payinfo = file_get_contents('php://input');
		$msg = (array)simplexml_load_string($payinfo, 'SimpleXMLElement', LIBXML_NOCDATA);
		if($msg['result_code'] == 'SUCCESS'){
			$order = M('kdgx_traffic_order')->where(array('id'=>$msg['out_trade_no']))->find();
			if($order['state'] == 0){				
				if(M('kdgx_traffic_order')->where(array('id'=>$msg['out_trade_no']))->save(array('state'=>'1'))){
					$sql = "SELECT g.goods_price,g.goods_name,g.goods_type,t.phone,t.uid from kdgx_traffic_order as t join kdgx_traffic_goods
						as g on g.id=t.goods_id where t.id='".$msg['out_trade_no']."' limit 1";
					$orderInfo = M()->query($sql);
					$openid = M('kddx_user_openid')->field('openid')->where(array('uid'=>$orderInfo['0']['uid'],'public_id'=>C("PUBLIC_ID")))->find();
					$orderInfo['0']['goods_price'] = $msg['total_fee'];
					if($orderInfo['0']['goods_price']<10){
						$orderInfo['0']['goods_price'] = "0.0".$orderInfo['0']['goods_price'];
					}elseif($orderInfo['0']['goods_price']<100 && $orderInfo['0']['goods_price']>=10){
						$orderInfo['0']['goods_price'] = "0.".$orderInfo['0']['goods_price'];
					}else{
						$orderInfo['0']['goods_price'] = substr($orderInfo['0']['goods_price'], 0,-2).".".substr($orderInfo['0']['goods_price'], -2);
					}
					switch ($orderInfo['0']['goods_type']) {
						case '1':
							$type = '移动';
							break;
						case '2':
							$type = '电信';
							break;
					}
					$res = M('kdgx_traffic_order')->where(array('id'=>$msg['out_trade_no']))->find();
					$data = array(
						'type' => 3,
						'first' => '我们已收到您的您的货款,订单号:'.$msg['out_trade_no'],
						'url' =>'',
						'orderMoneySum' => $orderInfo['0']['goods_price'].'元',
						'orderProductName'=>$orderInfo['0']['goods_name'].' 浙江'.$type.'共享流量',
						'openid' => $openid['openid'],
						'Remark' => '如有问题请联系小口袋：easypocket（微信号），小口袋将第一时间为您服务！',
						'color' => '#000000',
						); 
					$sendTemplate = new \Org\Util\Wechat();
					$sendTemplate->sendTemplateMsg($data);
					$openid = M('kddx_user_openid')->field('openid')->where(array('uid'=>'10002','public_id'=>C("PUBLIC_ID")))->find();
					$data = array(
						'type' => 3,
						'first' => '你收到一笔订单,订单号:'.$msg['out_trade_no'],
						'url' =>'http://www.pocketuniversity.cn/index.php/traffic/admin',
						'orderMoneySum' => $orderInfo['0']['goods_price'].'元',
						'orderProductName'=>$orderInfo['0']['goods_name'].' 浙江'.$type.'共享流量',
						'openid' => 'oDDzCtwOUgWT1TRRepBSHIuvUC6I',	// 业务员openid
						'Remark' => '充值号码:'.$orderInfo['0']['phone'],
						'color' => '#000000',
						);
					$response = $sendTemplate->sendTemplateMsg($data);
					echo "<xml> <return_code><![CDATA[SUCCESS]]></return_code> <return_msg><![CDATA[OK]]></return_msg> </xml>";
					exit();
				}
			}
		}
	}
}

