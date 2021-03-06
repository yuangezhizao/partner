<?php 
namespace Partner\Controller;
use Think\Controller;

// 卖室友会员计划

class VipController extends Controller{
	private $js1 = <<<EOF

	<head>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	</head>
	<body>
	<div class="js_dialog" id="iosDialog2">
      <div class="weui-mask"></div>
      <div class="weui-dialog">
          <div class="weui-dialog__bd">
EOF;

	private $js2 = <<<FOE

</div>
          <div class="weui-dialog__ft">
              <a href="javascript:WeixinJSBridge.call('closeWindow');" class="weui-dialog__btn weui-dialog__btn_primary">确定</a>
          </div>
      </div>
  </div>
  <style media="screen">
  .weui-mask {
    position: fixed;
    z-index: 1000;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
  }
  .weui-dialog__bd:first-child {
    padding: 2.7em 20px 1.7em;
    color: #353535;
  }
  .weui-dialog__bd {
    padding: 0 1.6em 0.8em;
    min-height: 40px;
    font-size: 15px;
    line-height: 1.3;
    word-wrap: break-word;
    word-break: break-all;
    color: #999999;
  }
  .weui-dialog__ft {
    position: relative;
    line-height: 48px;
    font-size: 18px;
    display: -webkit-box;
    display: -webkit-flex;
    display: flex;
  }
  @media screen and (min-width: 1024px){
  .weui-dialog {
      width: 35%;
  }
  }
  .weui-dialog {
      position: fixed;
      z-index: 5000;
      width: 80%;
      max-width: 300px;
      top: 50%;
      left: 50%;
      -webkit-transform: translate(-50%, -50%);
      transform: translate(-50%, -50%);
      background-color: #FFFFFF;
      text-align: center;
      border-radius: 3px;
      overflow: hidden;
  }
  .weui-dialog__btn_primary {
    color: #0BB20C;
  }
  .weui-dialog__btn {
    display: block;
    -webkit-box-flex: 1;
    -webkit-flex: 1;
    flex: 1;
    color: #3CC51F;
    text-decoration: none;
    -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
    position: relative;
  }
  </style>
  </body>
FOE;
	private $xm_public_id = 'gh_243fe4c4141f';

	public function addTakeFee(){
		if(empty($_GET['wx_media_id']))exit('无效的连接');
		if(empty($_SESSION['phone'])){
			$from = urlencode($_SERVER['REQUEST_URI']);	// 获得当前路由
			redirect("/index.php/home/user/login?flag=1&from=$from");
		}
		$media_id = M('kdgx_wx_media_info')->where(array('id'=>$_GET['wx_media_id']))->getField('media_id');
		$uid = decrypt_url(session('uid'),C('KEY_URL'));
		if(!M('kdgx_partner_takefee')->where(array('uid'=>$uid,'media_id'=>$media_id))->find()){
			if($phone = M('kddx_user')->where(array('uid'=>$uid))->getField('phone')){
				if(M('kdgx_partner_takefee')->data(array('uid'=>$uid,'phone'=>$phone,'media_id'=>$media_id))->add()){
					$unionid = M('kddx_user_openid')->where(array('uid'=>$uid,'public_id'=>C('PUBLIC_ID')))->getField('unionid');
					$openid = M('kddx_user_openid')->where(array('unionid'=>$unionid,'public_id'=>$this->xm_public_id))->getField('openid');
					if(empty($openid)){
						$error = '请重新关注口袋新媒以完成绑定';
						echo $this->js1 . $error . $this->js2;
						exit();
					}
					$this->display('m/Vip/add');exit();
				}
			}
		}else{
			$unionid = M('kddx_user_openid')->where(array('uid'=>$uid,'public_id'=>C('PUBLIC_ID')))->getField('unionid');
			$openid = M('kddx_user_openid')->where(array('unionid'=>$unionid,'public_id'=>$this->xm_public_id))->getField('openid');
			if(empty($openid)){
				$error = '请重新关注口袋新媒以完成绑定';
				echo $this->js1 . $error . $this->js2;
				exit();
			}
			$error = '该账户已经绑定';
			echo $this->js1 . $error . $this->js2;
			exit();
		}
		exit('错误的连接');
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
        $api_secret = '46d045ff5190f6ea93739da6c0aa19bc';	// 口袋新媒
        $str = implode('&', $item_array) . '&key=' . $api_secret;
        return strtoupper(md5($str));
    }

    // 获取公众号自定义权限
	private function getPrivate($public_id,$flag=false){
		if(empty($public_id)){
			if($flag)echo json_encode(array('error'=>10034,'msg'=>'参数错误'));
			return false;
		}
		$res = M('kddx_public')->where(array('public_id'=>$public_id))->find();
		if(empty($res)){
			if($flag)echo json_encode(array('error'=>1,'msg'=>'未加入'));return false;
		}
		switch ($res['auth']) {
			case '0':
				if($flag)echo json_encode(array('error'=>2,'msg'=>'审核中'));return false;
			case '1':
				if($flag)echo json_encode(array('error'=>0,'msg'=>'通过'));return true;
			default:
				if($flag)echo json_encode(array('error'=>100034,'msg'=>'参数错误'));return false;
		}
	}

	// 获取会员数量
	private function getVipCounts($media_id){
		return M('kdgx_vips')->alias('v')
		->join("kddx_user_openid as o on v.uid=o.uid and o.public_id='".C('PUBLIC_ID')."'")
		->where(array('from_media'=>$media_id))->count();
	}

	// 获取管理员平台充值总金额
	private function getTotalFee($media_id){
		$total_fee = M('kdgx_order')->field('take_fee')->where(array('from_media'=>$media_id,'order_state'=>'1'))->select();
		foreach ($total_fee as $key => $value) {
			$fees[] = $value['take_fee'];
		}
		if($total = array_sum($fees)){
			return $total;
		}else{
			return 0;
		}
	}

	// 获取管理员已提款金额
	private function getTakeFee($media_id){
		$take_fee = M('kdgx_take_apply')->field('amount')->where(array('to_media'=>$media_id,'result_code'=>'1'))->select();
		foreach ($take_fee as $key => $value) {
			$fees[] = $value['amount'];
		}
		if($take = array_sum($fees)){
			return $take;
		}else{
			return 0;
		}
	}

	// 获取管理员已申请金额
	private function getApplyTakeFee($media_id){
		$take_fee = M('kdgx_take_apply')->field('amount')->where(array('to_media'=>$media_id))->select();
		// print_r($take_fee);
		foreach ($take_fee as $key => $value) {
			$fees[] = $value['amount'];
		}
		if($take = array_sum($fees)){
			return $take;
		}else{
			return 0;
		}
	}

	// 获取会员计划金额数据
	public function getDataForMedia(){
		// $_POST['media_id'] = 'gh_bcc64f2e1a74';
		if(empty($_POST['media_id'])){
			echo json_encode(array(
				'error' => 22001,
				'msg' => '参数错误',
				));exit();
		}
		$media_info_id = M('kdgx_wx_media_info')->where(array('media_id'=>$_POST['media_id']))->getField('id');
		$qrcode_img = M('kdgx_wx_partner_config')->where(array('wx_media_info_id'=>$media_info_id))->getField('qrcode_img');
		echo json_encode(array(
			'error' => 0,
			'msg'	=> array(
				'vips' => $this->getVipCounts($_POST['media_id']),
				'total_fee' => $this->getTotalFee($_POST['media_id']),
				'rest_fee' => $this->getTotalFee($_POST['media_id'])-$this->getApplyTakeFee($_POST['media_id']),
				'take_fee' 	=> $this->getTakeFee($_POST['media_id']),
				'qrcode_img'=> "http://".C('CDN_SITE').$qrcode_img
				)
			));exit();
	}

	// 获得vip群二维码
	public function getVipQRcode(){
		if(empty($_POST['media_id'])){
			echo json_encode(array(
				'error' => 22001,
				'msg' => '参数错误',
				));exit();
		}
		$media_info_id = M('kdgx_wx_media_info')->where(array('media_id'=>$_POST['media_id'] ))->getField('id');
		if($qrcode_img = M('kdgx_wx_partner_config')->where(array('wx_media_info_id'=>$media_info_id))->getField('qrcode_img')){			
			echo json_encode(array(
				'error' => 0,
				'msg' => "http://".C('CDN_SITE').$qrcode_img
				));exit();	
		}else{
			echo json_encode(array(
				'error' => 0,
				'msg' => ''
				));exit();			
		}
	}

	// 管理员申请提款
	public function takeFeeApply(){
		if(empty($_POST['media_id']) || empty($_POST['uid']) || empty($_POST['amount'])){
			echo json_encode(array(
				'error' => 22001,
				'msg' => '参数错误'
				));exit();
		}
		if($_POST['amount']>($this->getTotalFee($_POST['media_id'])-$this->getApplyTakeFee($_POST['media_id']))){
			echo json_encode(array(
				'error' => 22003,
				'msg'	=> '提现金额超过上限'
				));exit();
		}
		$uid = decrypt_url($_POST['uid'],C('KEY_URL'));
		$unionid = M('kddx_user_openid')->where(array('uid'=>$uid,'public_id'=>C('PUBLIC_ID')))->getField('unionid');
		$openid = M('kddx_user_openid')->where(array('unionid'=>$unionid,'public_id'=>$this->xm_public_id))->getField('openid');
		if(empty($openid)){
			echo json_encode(array(
				'error' => 22002,
				'msg' => '请取关口袋新媒后重新关注'
				));exit();
		}
		$data = array(
			'mch_billno' => '1413849902'.date("Ymd").time(),
			'openid' => $openid,
			'uid'	=> $uid,
			'amount' => $_POST['amount'],
			'payment_time' => time(),
			'to_media' => $_POST['media_id']
			);
		if(M('kdgx_take_apply')->data($data)->add()){
			echo json_encode(array(
				'error' => 0,
				'msg'	=> '提交成功'
				));exit();
		}else{
			echo json_encode(array(
				'error' => 22004,
				'msg'	=> '系统繁忙请稍后重试'
				));exit();
		}
	}

	// 操作员打款接口
	public function takePay(){
		if(empty($_POST['uid']) || empty($_POST['apply_id'])){
			echo json_encode(array(
				'error' => 22001,
				'msg'	=> '参数错误'
				));exit();
		}
		$adminUid = ['10001','10002','98047','94302'];
		if(!in_array(decrypt_url($_POST['uid'],C('KEY_URL')), $adminUid)){
			echo json_encode(array(
				'error' => 22005,
				'msg'	=> '无操作权限'
				));exit();
		}
		$take_apply = M('kdgx_take_apply')->where(array('apply_id'=>$_POST['apply_id'],'result_code'=>array('neq','1')))->find();
		$send_name1 = $take_apply['amount']/100;
		$send_name2 = $take_apply['amount']%100;
		if($send_name2==0){
			$send_name2 = '00';
		}
		// 口袋新媒的企业红包
		$post = array(
			'wxappid' => 'wx3f6b8b2eb0483f2f',
			'mch_id'	=> '1413849902',
			'nonce_str' => md5(rand()),
			'mch_billno' => $take_apply['mch_billno'],
			're_openid' => $take_apply['openid'],
			'total_amount' => $take_apply['amount'],
			'send_name'	=>	"$send_name1.".$send_name2."元",
			'total_num'	=> 1,
			'wishing'	=> '卖室友会员计划提现',
			'act_name'	=> '卖室友会员计划提现',
			'remark' => '备注',
			'client_ip' => '121.41.36.196',
			// 'scene_id'	=> 'PRODUCT_4'
			);
		$post['sign'] = $this->_cal_sign($post);
		$xml = array_to_xml($post);
		$url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
		$response = FromXml(curl_post_ssl1($url,$xml));
		$response = print_r($response,true);
		if($response['err_code']==0){
			$result_code = '1';
			$save = array(
				'result_code' => $result_code,
				'payment_no'  => $response['payment_no'],
				'payment_time'=> time()
				);
			if(M('kdgx_take_apply')->where(array('apply_id'=>$_POST['apply_id']))->save($save)){					
				echo json_encode(array(
					'error' => 0,
					'msg'	=> '付款成功'
					));exit();
			}					
		}
		echo json_encode(array(
			'error' => 22008,
			'msg'	=> $response['err_code_des'],
			));exit();
	}

	// 获取可提款微信号列表
	public function getOperatorUid(){
		if(empty($_POST['media_id'])){
			echo json_encode(array(
				'error' => 22001,
				'msg'	=> '参数错误'
				));exit();
		}
		$data = M('kdgx_partner_takefee')->field('uid,phone')->where(array('media_id'=>$_POST['media_id']))->select();
		foreach ($data as $key => $value) {
			$data[$key]['uid'] = encrypt_url($value['uid'],C('KEY_URL'));
			$data[$key]['phone'] = substr($value['phone'], -4);
		}
		echo json_encode(array(
			'error' => 0,
			'msg'	=> $data
			));exit();	
	}

	// 操作员获取提款申请列表接口
	public function getTakeApplyList(){
		$data = M('kdgx_take_apply')->alias('ta')->field('distinct(apply_id),phone,ta.uid,amount,wmi.name as media_name,result_code,payment_time,to_media as media_id')->join('kdgx_partner_takefee as oper on oper.uid=ta.uid')->join("kdgx_wx_media_info as wmi on ta.to_media=wmi.media_id")->select();
		echo json_encode(array(
			'error' => 0,
			'msg' => $data
			));exit();
	}

	// 微校后台获取提款申请列表接口
	public function getTakeList(){
		if(empty($_POST['media_id'])){
			echo json_encode(array(
				'error' => 22001,
				'msg'	=> '参数错误'
				));exit();			
		}
		$data = M('kdgx_take_apply')->alias('ta')->field('distinct(apply_id),oper.uid,amount,result_code,phone,payment_time')->join('kdgx_partner_takefee as oper on ta.uid=oper.uid')->where(array('to_media'=>$_POST['media_id']))->select();
		foreach ($data as $key => $value) {
			$unionid = M('kddx_user_openid')->where(array('uid'=>$value['uid'],'public_id'=>C('PUBLIC_ID')))->getField('unionid');
			$openid = M('kddx_user_openid')->where(array('unionid'=>$unionid,'public_id'=>$this->xm_public_id))->getField('openid');
			if(empty($openid)){
				unset($data[$key]);
			}
			$data[$key]['uid'] = decrypt_url($value['uid'],C('KEY_URL'));
			$data[$key]['phone'] = substr($value['phone'], -4);
		}
		echo json_encode(array(
			'error' => 0,
			'msg' => $data
			));exit();
	}

	// vip群二维码上传
	public function uploadQRcodeImg(){
		if(empty($_POST['media_id']) || empty($_POST['QRcodeImg'])){
			echo json_encode(array(
				'error' => 22001,
				'msg'	=> '参数错误'
				));exit();			
		}
		$config = $_POST;
		if(!$this->getPrivate($config['media_id'])){
			echo json_encode(array('error'=>10023,'msg'=>'没有权限'));return;
		}
		$media_info = M('kdgx_wx_media_info')->field('id')->where(array('media_id'=>$config['media_id']))->find();
		if(empty($media_info['id'])){
			echo json_encode(array('error'=>10025,'msg'=>'请初始化应用'));return;
		}
		$upload = new \Org\Util\UploadBase64(array('rootPath'=>'D:\VirtualHost\Uploads/Uploads/bgImg/'));
		$info = $upload->upload($_POST['QRcodeImg']);
		if(empty($info)){
			echo json_encode(array('error'=>10025,'msg'=>'图片上传错误'));return;
		}else{
			$info = substr($info, strlen("D:\VirtualHost\Uploads"));
			$url = "http://".C('CDN_SITE').$info;
			requestGet($url);
			if(M('kdgx_wx_partner_config')->where(array('wx_media_info_id'=>$media_info['id']))->find()){
				if(M('kdgx_wx_partner_config')->where(array('wx_media_info_id'=>$media_info['id']))->save(array('qrcode_img'=>$info))){
					echo json_encode(array('error'=>0,'msg'=>$url));return;
				}
			}else{
				echo json_encode(array('error'=>22024,'msg'=>'先自定义背景'));return;
			}
		}
	}

	// 获取剩余查看次数
	private function getRemainlimit($uid){
		$time = strtotime(date('Y-m-d'));
		$result = M('kdgx_partner_limit')->where(array('today'=>array('between',array($time-10,$time+86400)),'uid'=>$uid))->find();
		if(empty($result)){
		    $result['times'] = 0;
		}
		$daylimit = 2;
		$freelimit = 0;
		$times = $result['times'];
		// 1.获取用户来源公众号会员开通情况
		$openvip = M('kdgx_wx_media_info')->alias('wxm')->join('kddx_user_openid as uo on uo.from_media=wxm.media_id')
		->where(array('uid'=>$uid,'public_id'=>C('PUBLIC_ID')))->getField('openvip');
		if($openvip){#加入会员计划
		    $daylimit = 0;
		    $freelimit = 1;
		}
		// 2.获取用户会员情况
		if(M('kdgx_vips')->where(array('end_time'=>array('gt',time()),'uid'=>$uid))->find()){
		    $daylimit = 6;

		}elseif($openvip){
			$times = 0;
		}
		// 3.刷新用户限制总数
		if($limits_id = M('kdgx_partner_limits')->where(array('uid'=>$uid))->getField('limits_id')){
			M('kdgx_partner_limits')->where(array('limits_id'=>$limits_id))->save(array('uid'=>$uid,'free'=>$freelimit));
		}else{
			M('kdgx_partner_limits')->add(array('uid'=>$uid,'free'=>$freelimit));
		}
		// echo M('kdgx_partner_limits')->getLastSql();
		// 4.获取用户消费点
		$limits = M('kdgx_partner_limits')->where(array('uid'=>$uid))->find();
		// 5.计算用户限制总数
		if($limits['free']==0){
			$limits['havefree']=0;
		}	
		$limitTotal = $daylimit+$limits['free']+$limits['production']-$limits['consumption']-$limits['havefree'];
		// 6.计算剩余查看数
		$limitTimes = $limitTotal - $times;
		if($limitTimes<0){
			$limitTimes = 0;
		}
		return $limitTimes;
	}

	// 会员中心接口
	public function vipCenter(){
		if(empty($_POST['uid'])){
			echo json_encode(array(
				'error' => 22001,
				'msg'	=> '参数错误'
				));exit();			
		}
		$uid = decrypt_url($_POST['uid'],C('KEY_URL'));
		$user = M('kddx_user_openid')->field('nickname,headimgurl')->where(array('uid'=>$uid,'public_id'=>C('PUBLIC_ID')))->find();
		$school = M('kddx_user_info')->where(array('uid'=>$uid))->getField('school');
		if($end_time = M('kdgx_vips')->where(array('end_time'=>array('gt',time()),'uid'=>$uid))->getField('end_time')){
			$remainday=round(($end_time-time())/86400)+1;
		}else{
			$remainday = 0;
		}
		$remainlimit = $this->getRemainlimit($uid);
		echo json_encode(array(
			'error' => 0,
			'msg' => array(
				'nickname' => $user['nickname'],
				'headimgurl'=> $user['headimgurl'],
				'school' => $school,
				'remainday' => $remainday,
				'remainlimit'=> $remainlimit,
				'randamount'=> $this->getRandAmount($uid,false)
				)
			));exit();
	}

	// 获取公众号认证信息
	public function getVerify_type(){
		if(empty($_POST['media_id'])){
			echo json_encode(array(
				'error' => 22001,
				'msg'	=> '参数错误'
				));exit();			
		}
		$verify_type = M('kdgx_wx_media_info')->where(array('media_id'=>$_POST['media_id']))->getField('verify_type');
		echo json_encode(array(
			'error' => 0,
			'msg'	=> $verify_type
			));exit();
	}

	// 获取随机金额接口
	public function getRand(){
		if(empty($_POST['uid'])){
			echo json_encode(array(
				'error' => 22001,
				'msg'	=> '参数错误'
				));exit();			
		}
		$uid = decrypt_url($_POST['uid'],C('KEY_URL'));
		echo json_encode(array(
			'error' => 0,
			'msg' => $this->getRandAmount($uid,true)
			));exit();		
	}

	// 获取随机金额
	private function getRandAmount($uid,$falg){
		$limits = M('kdgx_partner_limits')->where(array('uid'=>$uid))->find();
		if($limits['randtime']<strtotime(date('Y-m-d'))){
			if($falg){
				return $this->RandAmount($uid);
			}else{
				return 0;
			}
		}elseif(($limits['randstate'] | 0b01)==0b11){#消费过rand
			return $limits['randamount'];
		}elseif(($limits['randstate'] | 0b10)==0b11){#生成过rand
			return $limits['randamount'];			
		}
	}
	// 生成随机金额
	private function RandAmount($uid){
		$rand = [1,11,23,25,50,66,85,87,88,97,98,100];
		$randamount = $rand[mt_rand(0,11)];
		if(M()->execute("update koudai.kdgx_partner_limits set randamount=$randamount,randtime=".time().",randstate=b'01' where uid=$uid")){
			return $randamount;
		}else{
			echo json_encode(array(
				'error' => 2212,
				'msg' => '无法生成随机金额'
				));exit();
		}
	}

	// 获取分成比例接口
	public function getProForMedia(){
		if(empty($_POST['media_id'])){
			echo json_encode(array(
				'error' => 22001,
				'msg'	=> '参数错误'
				));exit();			
		}
		echo json_encode(array(
			'error' => 0,
			'msg'	=> $this->getPro($_POST['media_id'])
			));exit();
	}

	// 获取分成比例
	public function getPro($media_id){
		$vipopentime = M('kdgx_wx_media_info')->where(array('media_id'=>$media_id))->getField('vipopentime');
		if((time()-$vipopentime)>604800){
			return 0.6;
		}else{
			return 0.8;
		}
	}

	// 获取开启vip计划数量+200
	public function getOpenVipCount(){
		echo json_encode(array(
			'error' => 0,
			'msg'	=> M('kdgx_wx_media_info')->where(array('openvip'=>'1'))->count()+200
			));
	}

	public function tongji(){
		$sql = "SELECT count(uid) as count,sum(cash_fee) as cash_fee,sum(take_fee) as take_fee,
i.name as public_name,from_media,openvip FROM koudai.kdgx_order as o
join koudai.kdgx_wx_media_info as i on i.media_id=o.from_media
 where order_state='1' group by from_media order by cash_fee desc";
		$data = M()->query($sql);
		foreach ($data as $key => $value) {
			$user = M('kdgx_operator')->field('name,phone')->where(array('public_id'=>$value['from_media']))->find();
			$data[$key]['name'] = $user['name'];
			$data[$key]['phone'] = $user['phone'];
			$sql1 = "select count(*) as precount from(SELECT count(order_id) FROM koudai.kdgx_order where from_media='".$value['from_media']."' and order_state='1' group by uid)as talbe";
			$pre = M()->query($sql1);
			$data[$key]['precount'] = $pre['0']['precount'];
		}
		$this->assign('data',$data);
		$this->display('m/vip/tongji');
	}
}