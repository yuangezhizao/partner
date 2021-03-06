<?php
namespace Partner\Controller;
use \Think\Controller;

// 卖室友订单支付回调

class ReceiveController extends Controller{

	
	public function notify(){
		// if(empty($_GET['test']))exit();
		$payinfo = file_get_contents('php://input');
		$msg = FromXml($payinfo);
		// $msg = array(
		//     'appid' => 'wx34f6baf7794f38b1',
		//     'attach' => 'gh_c75321282c18',
		//     'bank_type' => 'CFT',
		//     'cash_fee' => 19,
		//     'fee_type' => 'CNY',
		//     'is_subscribe' => 'Y',
		//     'mch_id' => '1264021501',
		//     'nonce_str' => 'capy5ulp5coi4ap3kza6pioad8cgcgtp',
		//     'openid' => 'oDDzCt4iFHZjUc1qClCHIvm8UWGg',	
		//     'out_trade_no' => '126402150120161229134255',
		//     'result_code' => 'SUCCESS',
		//     'return_code' => 'SUCCESS',
		//     'sign' => '66366A1B06FCAFF10CFF8A62B7EF935D',
		//     'time_end' => '20161223162727',
		//     'total_fee' => 19,
		//     'trade_type' => 'JSAPI',
		//     'transaction_id' => '4008362001201612291342556916',
		// );
		$return ='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
		if(empty($msg) || $msg['return_code']=='SUCCESS'){
			if(M('kdgx_order')->where(array('transaction_id'=>$msg['transaction_id']))->getField('order_id')){
				echo $return;exit();
			}
			$uid = M('kdgx_order')->where(array('out_trade_no'=>$msg['out_trade_no']))->getField('uid');
			$from_media = M('kddx_user_openid')->where(array('uid'=>$uid,'public_id'=>C('PUBLIC_ID')))->getField('from_media');
			$order = $msg;
			unset($order['openid']);
			$order['take_fee'] = 0;
			if($msg['result_code']=='SUCCESS'){
				$order['order_state'] = '1';
				if(($uid=='98047' || $uid=='94302') && $msg['attach']!='5'){
				   $order['total_fee'] *= 100;
				   $order['cash_fee'] *= 100;
				}
				$pro = A('Vip')->getPro($from_media);
				switch ($msg['attach']) {
					case '4':#三个月会员
						$addtime = 7776000;
						$order['take_fee'] = $order['cash_fee'] * $pro;
						break;					
					case '3':#两个月会员
						$addtime = 5184000;
						$order['take_fee'] = $order['cash_fee'] * $pro;
						break;
					case '2':#一个月会员
						$addtime = 2592000;
						$order['take_fee'] = $order['cash_fee'] * $pro;
						break;
					case '5':#随机一次
						M()->execute("update koudai.kdgx_partner_limits set randstate=b'11' where uid=$uid");
					case '1':#一元一次
						$order['take_fee'] = $order['total_fee'] * $pro;
						if(M('kdgx_partner_limits')->where(array('uid'=>$uid))->find()){
							M('kdgx_partner_limits')->where(array('uid'=>$uid))->setInc('production',1);
						}else{
							M('kdgx_partner_limits')->data(array('uid'=>$uid,'production'=>1))->add();
						}
						$order['uid']=$uid;
						$order['time_end'] = time();
						$order['from_media']= $from_media;
						if(M('kdgx_order')->where(array('out_trade_no'=>$msg['out_trade_no']))->save($order)){
							echo $return;exit();
						}
						exit();			
				}
				if($vip_id = M('kdgx_vips')->field('vip_id,end_time')->where("uid=$uid")->find()){
					if($vip_id['end_time']<time()){
						$save = array(
							'vip_id' => $vip_id['vip_id'],
							'open_time' => time(),
							'end_time' => time()+$addtime
							);
					}else{
						$save = array(
							'vip_id' => $vip_id['vip_id'],
							'end_time' => $vip_id['end_time']+$addtime
							);
					}
					M('kdgx_vips')->save($save);
				}else{
					M('kdgx_vips')->add(array('uid'=>$uid,'open_time'=>time(),'end_time'=>time()+$addtime));
				}
			}
			$order['time_end'] = time();
			$order['from_media']=$from_media;
			if(M('kdgx_order')->where(array('out_trade_no'=>$msg['out_trade_no']))->save($order)){
				echo $return;exit();
			}
		}
	}
}