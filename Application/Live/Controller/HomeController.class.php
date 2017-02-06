<?php 
namespace Live\Controller;
use \Live\Controller\CommonController;

// 弹幕互动用户端
class HomeController extends CommonController{
	
	private $adminUid = ['10001','10002','98047','94302','66191','91031'];

	// 用户端应用入口
	public function index(){
		if(empty($_COOKIE['media_id'])){
			cookie('media_id',$_GET['media_id']);
		}
		if(empty($_SESSION['uid'])){
			$from = urlencode($_SERVER['REQUEST_URI']);	// 获得当前路由
			redirect("/index.php/home/user/login?flag=0&from=$from");
		}
		if(!empty($_GET['room_id'])){
			$room= M('kdgx_live_room')->where(array('room_id'=>$_GET['room_id']))->find();
			if(time()<$room['start_time']){
				echo $this->js1.'直播将于'.date('m-d H:i:s',$room['start_time']).'开始'.$this->js2;exit();
			}
		}
		$uid = decrypt_url($_SESSION['uid'],C('KEY_URL'));
		if(in_array($uid, $this->adminUid)){
			cookie('is_vip','1');
		}
		// -----------------------统计应用首页响应请求----------------------------
        $collect = new \Org\Util\DataCollect(array('module'=>'live'));
        $collect->CollectResponse('应用首页',decrypt_url(cookie('uid'),C("KEY_URL")),cookie('media_id'));

		$this->display();
	}

	// 获取全国正在播的房间列表
	public function getCountyRoomListing(){
		$data = M('kdgx_live_room')->alias('room')->field('room_id,title,create_time,wxmi.name')
		->join('kdgx_wx_media_info as wxmi on wxmi.media_id=room.media_id')
		->where(array('room.start_time'=>array('lt',time()),'room.is_delete'=>'0','room.end_time'=>array('gt',time())))->select();
		foreach ($data as $key => $value) {
			$data[$key]['OnlineCounts'] = $this->getRoomOnlineCounts($value['room_id']);
			$data[$key]['CommentCounts'] = $this->getRoomCommentCounts($value['room_id']);
		}
		echo json_encode(array(
			'error' => 0,
			'msg' => $data,
			));exit();
	}

	// 获取全站正在播的房间列表
	public function getSchoolRoomListing(){
		if(empty($_POST['media_id'])){
			echo json_encode(array(
				'error' => 62001,
				'msg' => '参数错误',
				));exit();
		}
		// $medias = $this->getSchoolsForMedia($_POST['media_id']);
		$medias[0] = $_POST['media_id'];
		$data = M('kdgx_live_room')->alias('room')->field('room_id,title,create_time,wxmi.name')
		->join('kdgx_wx_media_info as wxmi on wxmi.media_id=room.media_id')
		->where(array('room.start_time'=>array('lt',time()),'room.is_delete'=>'0','room.media_id'=>array('in',$medias),'room.end_time'=>array('gt',time())))->select();
		foreach ($data as $key => $value) {
			$data[$key]['OnlineCounts'] = $this->getRoomOnlineCounts($value['room_id']);
			$data[$key]['CommentCounts'] = $this->getRoomCommentCounts($value['room_id']);
		}
		echo json_encode(array(
			'error' => 0,
			'msg' => $data,
			));exit();
	}

	// 获取最新弹幕信息接口
	public function getCurrentComment(){
		if(empty($_POST['connect_id'])){
			echo json_encode(array(
				'error' => 62002,
				'msg' => '参数错误',
				));exit();
		}
		$datas = M('kdgx_live_connect')->distinct(true)->field('uo.nickname,uo.headimgurl,com.is_select,
			com.room_id,com.comment,com.uid,comment_id,com.create_time,com.type')
		->alias('con')->join('kdgx_live_comment as com on con.room_id=com.room_id and com.create_time>con.last_get_comment_time')
		->join("kddx_user_openid as uo on uo.uid=com.uid and uo.public_id='".C('PUBLIC_ID')."'")
		->where(array('con.connect_id'=>$_POST['connect_id'],'com.uid'=>array('neq',1),'com.is_delete'=>'0'))->order('comment_id desc')->select();
		foreach ($datas as $key => $data) {
			$data[$key]['is_manager'] = '0';
			if($this->getPrivate($data['uid'],$data['room_id'])){
				$datas[$key]['is_manager'] = '1';
			}
			$datas[$key]['uid'] = encrypt_url($data['uid'],C('KEY_URL'));
			if(M('kdgx_live_blacklist')->where(array('room_id'=>$_POST['room_id'],'black_uid'=>$data['uid'],'end_time'=>array('gt',time())))->find()){
				$datas[$key]['is_black'] = '1';
			}else{
				$datas[$key]['is_black'] = '0';
			}
			$datas[$key]['comment'] = emoji_decode($data['comment']); 
		}
		$this->updateConnect(1);
		echo json_encode(array(
			'error' => 0,
			'msg' => $datas
			));exit();
	}

	// 获得精选弹幕
	public function getCommentSelect(){
		if(empty($_POST['room_id'])){
			echo json_encode(array(
				'error' => 62022,
				'msg' => '参数错误',
				));exit();
		}
		$datas = M('kdgx_live_comment')->distinct(true)->field('uo.nickname,uo.headimgurl,com.is_select,
			com.comment,com.room_id,com.uid,comment_id,com.create_time,com.type')->alias('com')
		->join("kddx_user_openid as uo on uo.uid=com.uid and uo.public_id='".C('PUBLIC_ID')."'")
		->where(array('com.room_id'=>$_POST['room_id'],'com.uid'=>array('neq',1),'com.is_delete'=>'0','com.is_select'=>'1'))
		->order('create_time desc')->select();
		foreach ($datas as $key => $data) {
			$datas[$key]['uid'] = encrypt_url($data['uid'],C('KEY_URL'));
			$datas[$key]['comment'] = emoji_decode($data['comment']); 
		}
		echo json_encode(array(
			'error'=>0,
			'msg'=>$datas,
			));exit();
	}

	// 获取所有弹幕接口
	public function getComment(){
		$page = trim($_GET['page']);
		$firstRow = ($page-1) * 10;
		if(empty($page)){
			$limit = '';
		}else{
			$limit = $firstRow.',10';
		}
		if(empty($_POST['room_id'])){
			echo json_encode(array(
				'error' => 62021,
				'msg' => '参数错误',
				));exit();
		}
		$datas = M('kdgx_live_comment')->distinct(true)->field('uo.nickname,uo.headimgurl,com.is_select,
			com.comment,com.room_id,com.uid,comment_id,com.create_time,com.type')->alias('com')
		->join("kddx_user_openid as uo on uo.uid=com.uid and uo.public_id='".C('PUBLIC_ID')."'")
		->where(array('com.room_id'=>$_POST['room_id'],'com.uid'=>array('neq',1),'com.is_delete'=>'0'))->order('create_time desc')
		->limit($limit)->select();
		$comments = array();
		foreach ($datas as $key => $data) {
			$datas[$key]['is_manager'] = '0';
			if($this->getPrivate($data['uid'],$_POST['room_id'])){
				$datas[$key]['is_manager'] = '1';
			}
			$datas[$key]['uid'] = encrypt_url($data['uid'],C('KEY_URL'));
			if(M('kdgx_live_blacklist')->where(array('room_id'=>$_POST['room_id'],'black_uid'=>$data['uid'],'end_time'=>array('gt',time())))->find()){
				$datas[$key]['is_black'] = '1';
			}else{
				$datas[$key]['is_black'] = '0';
			}
			$datas[$key]['comment'] = emoji_decode($data['comment']); 
			array_unshift($comments, $datas[$key]);
		}
		echo json_encode(array(
			'error'=>0,
			'msg'=>$comments, 
			));exit();
	}

	// 发送弹幕接口
	public function sendComment(){
		if(empty($_POST['room_id'])||empty($_POST['comment'])||empty($_POST['uid'])){
			echo json_encode(array(
				'error' => 62003,
				'msg' => '参数错误',
				));exit();
		}
		if($this->isClose($_POST['room_id'])){
			echo json_encode(array(
				'error' => 62006,
				'msg' => '该房间已经关闭!',
				));
		}
		$_POST['create_time'] = time();
		$_POST['uid'] = decrypt_url($_POST['uid'],C('KEY_URL'));
		if($this->isBlack($_POST['uid'],$_POST['room_id'])){
			echo json_encode(array(
				'error' => 62005,
				'msg' => '你已经被管理员禁言',
				));exit();
		}
		$_POST['comment'] = emoji_encode($_POST['comment']);
		if($comment_id = M('kdgx_live_comment')->data($_POST)->add() && $this->updateConnect(3)){
			echo json_encode(array(
				'error' => 0,
				'msg' => $comment_id,
				));exit();
		}else{
			echo json_encode(array(
				'error' => 62004,
				'msg' => '发送失败',
				));exit();
		}
	}

	// 获取最新动作信息
	public function getCurrentAction(){
		if(empty($_POST['connect_id'])){
			echo json_encode(array(
				'error' => 62005,
				'msg' => '参数错误',
				));exit();
		}
		$data = M('kdgx_live_connect')->field('ac.from_uid,ac.to_uid,ac.type')->alias('con')
		->join('kdgx_live_action as ac on con.room_id=ac.room_id and ac.create_time>=con.last_get_action_time')
		->where(array('con.connect_id'=>$_POST['connect_id']))->order('action_id desc')->find();
		$res = M('kddx_user_openid')->where(array('uid'=>$data['from_uid'],'public_id'=>C('PUBLIC_ID')))->getField('nickname');
		$data['from_nickname'] = $res;
		unset($data['from_uid']);
		if(!empty($data['to_uid'])){
			$res = M('kddx_user_openid')->where(array('uid'=>$data['to_uid'],'public_id'=>C('PUBLIC_ID')))->getField('nickname');
			$data['to_nickname'] = $res;
		}
		unset($data['to_uid']);
		$this->updateConnect(2);
		echo json_encode(array(
			'error' => 0,
			'msg' => $data,
			));exit();	
	}

	// 获取最近进入的5人头像
	public function getCurrentIntoFiveHeadImg(){
		if(empty($_POST['room_id'])){
			echo json_encode(array(
				'error' => 62006,
				'msg' => '参数错误',
				));exit();
		}
		$counts = $this->getRoomOnlineCounts($_POST['room_id']);
		if($counts==0){
			echo json_encode(array(
				'error' => 0,
				'msg' => array(),
				));exit();			
		}
		$counts = ($counts>5)? 5 : $counts;
		$from_uids = M('kdgx_live_action')->distinct(true)->field('from_uid')
		->where(array('type'=>'0','room_id'=>$_POST['room_id']))->order('action_id desc')
		->limit(0,$counts)->select();
		$uids = array();
		foreach ($from_uids as $from_uid) {
			array_push($uids, $from_uid['from_uid']);
		}
		$HeadImgs = M('kddx_user_openid')->field('headimgurl,nickname')
		->where(array('public_id'=>C('PUBLIC_ID'),'uid'=>array('in',$uids)))
		->select();
		echo json_encode(array(
			'error' => 0,
			'msg' => $HeadImgs,
			));exit();
	}	
}