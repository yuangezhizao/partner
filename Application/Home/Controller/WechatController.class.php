<?php 
namespace Home\Controller;
use Common\Controller\HomeBaseController;

/**
 * 微信功能控制
 */
class WeChatController extends HomeBaseController{

	private $public_id = 'gh_3347961cee42';	//口袋高校助手的public_id

	/**
	 * 获取openid
	 * @param string uid 用户id
	 * @return string 用户对应的openid
	 */
	private function getOpenid($uid){
		$result = M('kddx_user_openid')->where(array('uid'=>$uid,'public_id'=>$this->public_id))
		->field('openid')->find(); 
		return $result['openid'];
	}
	
	/**
     * 计算签名
     * @param array $param_array
     */
    private static function _cal_sign($param_array) {
        $names = array_keys($param_array);
        sort($names, SORT_STRING); 
        $item_array = array();
        foreach ($names as $name) {
            $item_array[] = "{$name}={$param_array[$name]}";
        }      
        $api_secret = 'D5BA23194ED27CADBAAE786DE805F40B';
        $str = implode('&', $item_array) . '&key=' . $api_secret;
        return strtoupper(md5($str));
    }

	public function Pay(){
		$post = array(
			'mch_appid' => 'wx34f6baf7794f38b1',
			'mchid'	=>	'1264021501',
			'nonce_str' => md5(rand()),
			'partner_trade_no' => '100000' . rand() . time(),
			'openid' => $this->getOpenid($_POST['uid']),
			'check_name' => 'NO_CHECK',
			're_user_name' => $_POST['re_user_name'],
			'amount' => $_POST['amount'],
			'desc' => $_POST['desc'],
			'spbill_create_ip' => '121.41.36.196',
		);
		$post['sign'] = $this->_cal_sign($post);
		$xml = array_to_xml($post);
		$url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
		$response = curl_post_ssl($url,$xml);
		print_r($response);
	}

	/**
	 * 获取jsSign
	 */
	public function getsignPackage(){
		 $url = $_POST['url'];
		 $jssdk = new \Org\Util\JSSDK('wx3cdae4e674abafd2','d4624c36b6795d1d99dcf0547af5443d',$url);
		 $signPackage = $jssdk->GetSign();
		 print_r(json_encode($signPackage));
	}

	public function getQRcode(){
		// $_GET['scene'] = 'gh_bcc64f2e1a74';
		switch ($_GET['type']) {
			case '1':
				if(empty($_GET['scene']))exit('请传递场景值');
				$res = M('kdgx_wx_media_info')->where(array('media_id'=>$_GET['scene']))->find();
				if(empty($res)){
					echo json_encode(array('error'=>20023,'msg'=>'请初始化应用!'));return;
				}
				$wechat = new \Org\Util\Wechat();
				$data = $wechat->getQRcode('1'.$res['id']);
				break;	
			case '2':
				$cfg = array(
					'app_id'=>'wx3f6b8b2eb0483f2f',
					'app_secret'=>'6861d005542696913ed31a9645690595',
					'public_id'=>'gh_243fe4c4141f',
				);
				$wechat = new \Org\Util\Wechat($cfg);
				$data = $wechat->getQRcode('2'.$res['id']);
				break;
			case '3':
				if(empty($_GET['scene']))exit('请传递场景值');
				$wechat = new \Org\Util\Wechat();
				$data = $wechat->getQRcode('3'.$_GET['scene']);
				break;
			case '4':
				if(empty($_GET['scene']))exit('请传递场景值');
				$wechat = new \Org\Util\Wechat();
				$data = $wechat->getQRcode("4".$_GET['scene']);
				break;
			case '5':
				if(empty($_GET['scene']))exit('请传递场景值');
				$wechat = new \Org\Util\Wechat();
				$data = $wechat->getQRcode("5".$_GET['scene']);
				print_r($data);exit('2');
				break;
			case '6':
				if(empty($_GET['scene']))exit('请传递场景值');
				$res = M('kdgx_wx_media_info')->where(array('media_id'=>$_GET['scene']))->find();
				if(empty($res)){
					echo json_encode(array('error'=>20023,'msg'=>'请初始化应用!'));return;
				}
				$cfg = array(
					'app_id'=>'wx3f6b8b2eb0483f2f',
					'app_secret'=>'6861d005542696913ed31a9645690595',
					'public_id'=>'gh_243fe4c4141f',
				);
				$wechat = new \Org\Util\Wechat($cfg);
				$data = $wechat->getQRcode('6'.$res['id']);
				break;
		}
		return;
	}

		//发送模板消息
	public function sendTemplate(){
		$post = $_POST;
		$post['openid'] = $this->getOpenid($post['uid']);
		switch($post['msgType']){
			case "广告单子" : 
				$post['color'] = "#f8bb37";break;
			case "实时热点" :
				$post['color'] = "#fb8181";break;
			case "通知消息"	:
				$post['color'] = "#81fbc0";break;
			default :
				$post['color'] = "#535353";break;
		}
		$sendTemplate = new \Org\Util\Wechat();
		$result = $sendTemplate->sendTemplateMsg($post);
		print_r($result);
	}
}