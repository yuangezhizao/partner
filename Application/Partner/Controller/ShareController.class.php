<?php 
namespace Partner\Controller;
use Common\Controller\BaseController;
/**
 * 分享页面管理
 * 
 */
class ShareController extends BaseController{
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

	// 查看室友信息页
	public function look(){
		$formid = I('get.id');
		$id = uiddecode($formid); 
		$partner = M('kdgx_partner')->where(array('id'=>$id,'is_delete'=>'0'))->find();
		if(empty($partner['id'])){
			$error = "该联系方式已被删除!*.*!";
			echo $this->js1 . $error . $this->js2;
			return;
		}
		$uid = decrypt_url(cookie('uid'),C("KEY_URL"));
		if(empty($id) || empty($uid)){
			$error = "错误码:10001,请联系小口袋微信号EasyPocket";
			echo $this->js1 . $error . $this->js2;
			return;
		}
		$partner = M('kdgx_partner')->where(array('id'=>$id,'is_delete'=>'0'))->find();
		if(empty($partner['id'])){
			$error = "该联系方式已被删除!*.*!";
			echo $this->js1 . $error . $this->js2;
			return;
		}
		// 判断用户权限与权限更新
		$ret_arr = $this->checkLimit($uid,$id);	
		$limitdata  = array(
			'uid'	=>	$uid,
			'formid'=>	'#'. $id .',',
			'today'	=>	time(),
		);
		switch ($ret_arr['flag']) {
			case '0':#用户第一次写入
				//写入用户限制
				if($limitdata = M('kdgx_partner_limit')->create($limitdata)){
					M('kdgx_partner_limit')->data($limitdata)->add();
					M('kdgx_partner')->where(array('id'=>$id))->setInc('times',1);
				}
				break;
			case '2':# 0<用户<权限次数写入
				//更新用户限制
				unset($limitdata['formid']);
				$limitdata['today'] = array('between',array(strtotime(date('Y-m-d'))-10,strtotime(date('Y-m-d'))+86400));
				$oldformid = M('kdgx_partner_limit')->field('formid')->where($limitdata)->find();
				$formid = $oldformid['formid'] . $id.',';
				if(M('kdgx_partner_limit')->where($limitdata)->data(array('formid'=>$formid))->save()){
					M('kdgx_partner_limit')->where($limitdata)->setInc('times',1);
					unset($limitdata['today']);
					M('kdgx_partner')->where(array('id'=>$id))->setInc('times',1);
				}
				break;
			case '4':# 用户大于权限次数拒绝写入
				cookie('shareurl',$_SERVER['REQUEST_URI']);
				redirect("LookShare?formid=$formid&media_id=".$partner['media_id']);
				// header('Location:http://www.pocketuniversity.cn/WxpayJSAPI/application/break.html');
			case '5':
				// '权限系统错误';
				$error = "错误码:10002,请联系小口袋微信号EasyPocket";
				echo $this->js1 . $error . $this->js2;
				return;
			case '1':
			case '3':
			case '6':
			case '7':#用户访问曾经要过的历史
				break;
			default:
				// echo '写入系统错误';
				$error = "错误码:10003,请联系小口袋微信号EasyPocket";
				echo $this->js1 . $error . $this->js2;
				return;
		}

		// 分类联系方式
		switch ($partner['contype']) {
			case '1':
				$partner['contype'] = '手机';
				break;
			case '2':
				$partner['contype'] = '微信';
				break;
			case '3':
				$partner['contype'] = 'QQ';
				break;
		}

		// 发送通知消息
		if($ret_arr['flag']=='0' || $ret_arr['flag']=='2'){
			$partner['times']++;		
			$array_nick = M('kddx_user_info')->field('nick')->where(array('uid'=>$uid))->find();
			$from = '/index.php/Partner/partner/History';
			$senddata = array(
				'first' => '『卖室友』动态有了更新',
				'uid' 	=> $partner['uid'],
				'keyword1'=> $array_nick['nick'].'索要了您的联系方式',
				'keyword2'=>'当前被索要联系方式'.$partner['times'].'次',
				'remark' => '点击查看详情',
				'msgType' => '卖室友通知',
				'type'	=> '2',
				'url' => 'http://'.C("WEB_SITE").'/index.php/Home/User/login?from='.urlencode($from)
				);
			$url="http://www.pocketuniversity.cn/index.php/home/Wechat/sendTemplate";
			$response =  requestPost($url,$senddata);
			if($ret_arr['daylimits']<=$ret_arr['todaytimes']){			
				$freelimits = M('kdgx_partner_limits')->field('free,havefree,production,consumption')->where(array('uid'=>$uid))->find();
				if($freelimits['free']==1 && $freelimits['havefree']==0){
					M('kdgx_partner_limits')->where(array('uid'=>$uid))->setInc('havefree',1);
				}elseif($freelimits['consumption']<$freelimits['production']){
					M('kdgx_partner_limits')->where(array('uid'=>$uid))->setInc('consumption',1);
				}
			}
			// -----------------------统计买室友响应请求----------------------------
	        $collect = new \Org\Util\DataCollect();
	        $collect->CollectResponse('买室友',$uid,cookie('media_id'));
			if($response.errcode != 0){
				file_put_contents('./Logs/test.log', date('Y-m-d H:i:m').'|'.$flag.'|'.print_r($response,true),FILE_APPEND);
			}
		}
		// -----------------------统计室友信息页响应请求----------------------------
        $collect = new \Org\Util\DataCollect();
        $collect->CollectResponse('室友信息页',$uid,cookie('media_id'));

		// 渲染视图 
		$partner['photo_src'] = 'http://'.C("CDN_SITE") .'/'. $partner['photo_src'];
		if (!empty($partners['age']))
		$partner['age'] = date('y年n月',strtotime($partner['age']));
		$this->assign('data',$partner);
		$this->display('Share/look');
	}

	/**
	 * 检查用户是否有写入权限
	 * @param  [type] $uid 用户id
	 * @param  用户索要对象的formid
	 * @return [type] 返回用户写入权限的标志:0:还没有写入过;1:写入过1次;2:写入过2次;3:错误!
	 */
	private function checkLimit($uid,$formid){
		$time = strtotime(date('Y-m-d'));
		$res = M('kdgx_partner')->where(array('uid'=>$uid,'id'=>$formid))->find();
		if(!empty($res['id'])){
			return array('flag'=>6);	// 查看自己卖的对象信息类型
		}
		$ishave = M('kdgx_partner_limit')->where(array('uid'=>$uid))
		->where('formid like "#'.$formid.',%" or formid like "%,'.$formid.',%"')->find();
		if(!empty($ishave['id'])){
			return array('flag'=>7);  //  查看自己曾经已经要过的对象
		}
		$result = M('kdgx_partner_limit')->where(array('today'=>array('between',array($time-10,$time+86400)),'uid'=>$uid))->find();
		if(empty($result)){
			$result['times'] = 0;
		}
		$times = $result['times'];
		$daylimit = 2;
		$freelimit = 0;
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
		$ret_arr = array(
			'daylimits'=>$daylimit,	//每日可要次数
			'todaytimes'=>$times    //今日已要次数
			);
		if(!$result['times']){
			$type = 0;
		}elseif($limitTimes<=0){
			$type = 2;
		}else{
			$type = 1;
		}
		switch ($type) {
			case '0':
				$ret_arr['flag'] = 0;break;		// 0 + new类型
			case '1':
				if(strpos($result['formid'],$formid)){
					$ret_arr['flag'] = 1;break;	// <限制 +old类型
				}else{
					$ret_arr['flag'] = 2;break;	// <限制 +new类型
				}
			case '2':
				if(strpos($result['formid'], $formid)){
					$ret_arr['flag'] = 3;break;	// >=限制 +old类型
				}else{
					$ret_arr['flag'] = 4;break;	// >=限制 +new类型
				}
			default:
				$ret_arr['flag'] = 5;break;		// 系统错误
		}
		return $ret_arr;
	}

	/**
	 * 加载分享页视图
	 */
	public function Share(){
		$partner = M('kdgx_partner')->where(array('id'=>uiddecode($_GET['formid']),'is_delete'=>'0'))->find();
		if(empty($partner['id'])){
			$error = "该联系方式已被删除!*.*!";
			echo $this->js1 . $error . $this->js2;
			return;
		}
		// -----------------------统计从分享响应请求----------------------------
        $collect = new \Org\Util\DataCollect();
        $collect->CollectResponse('获取分享',0,cookie('media_id')|0);
		$this->display('Share/partner');
	}

	/**
	 * 加载分享页视图
	 */
	public function LookShare(){
		$partner = M('kdgx_partner')->where(array('id'=>uiddecode($_GET['formid']),'is_delete'=>'0'))->find();
		if(empty($partner['id'])){
			$error = "该联系方式已被删除!*.*!";
			echo $this->js1 . $error . $this->js2;
			return;
		}
		$this->display('Share/break');
	}

}