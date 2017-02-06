<?php 
namespace Live\Controller;
use \Live\Controller\CommonController;

// 弹幕互动管理端
class AdminController extends CommonController{
	
	// 添加管理员通道
	public function add(){
		if(empty($_COOKIE['media_id'])){
			cookie('media_id',$_GET['media_id']);
		}
		if(empty($_SESSION['uid'])){
			$from = urlencode($_SERVER['REQUEST_URI']);	// 获得当前路由
			redirect("/index.php/home/user/login?flag=0&from=$from");
		}
		$uid = decrypt_url(session('uid'),C('KEY_URL'));
		$room_id = $_GET['room_id'];
		if(empty($uid) || empty($room_id)){
			echo $this->js1.'系统繁忙1217'.$this->js2;exit();
		}
		if($this->isApplayManager($uid,$room_id)){
			echo $this->js1."你已提交过申请".$this->js2;exit();
		}
		if($this->getPrivate($uid,$room_id)){
			echo $this->js1."你已是该房间的管理员".$this->js3;
			$room_id = $_GET['room_id'];
			$this->assign('room_id',$room_id);
			$this->display('Admin/redirect');exit();
		}
		if($this->Manager($uid,$room_id)){
			echo $this->js1."你已成为该房间的管理员".$this->js3;
			$room_id = $_GET['room_id'];
			$this->assign('room_id',$room_id);
			$this->display('Admin/redirect');exit();
		}else{
			echo $this->js1.'系统繁忙1712'.$this->js2;exit();
		}
	}

	// 邀请函入口
	public function redirect(){
		$room_id = $_GET['room_id'];
		$this->assign('room_id',$room_id);
		$this->display();
	}

	// 创建房间接口
	public function createRoom(){
		if(empty($_POST['room_name']) || empty($_POST['title']) || empty($_POST['media_id'])){
			echo json_encode(array(
				'error' => 61001,
				'msg' => '参数错误',
				));exit();
		}
		$data = $_POST;
		if(!empty($data['room_bg'])){			
			$upload = new \Org\Util\UploadBase64(array('rootPath'=>'./Uploads/bgImg/'));
			$info = $upload->upload($data['room_bg']);
			if(empty($info)){
				echo json_encode(array('error'=>61025,'msg'=>'图片上传错误'));exit();
			}else{
				$data['room_bg'] = substr($info, 1);
			}
		}
		$data['create_time'] = time();
		if($room_id = M('kdgx_live_room')->data($data)->add()){ 
			echo json_encode(array(
				'error' => 0,
				'msg' => $room_id,
				));exit();
		}else{
			echo json_encode(array(
				'error' => 61002, 
				'msg' => '房间创建失败',
				));exit();
		}
	}

	// 修改房间配置
	public function updateRoomInfo(){
		$config = $_POST;
		if(empty($config)){
			echo json_encode(array(
				'error' => 61027,
				'msg' => '参数错误',
				));exit();
		}
		if(!empty($config['room_bg'])){			
			$upload = new \Org\Util\UploadBase64(array('rootPath'=>'./Uploads/bgImg/'));
			$info = $upload->upload($config['room_bg']);
			if(empty($info)){
				echo json_encode(array('error'=>61025,'msg'=>'图片上传错误'));exit();
			}else{
				$config['room_bg'] = substr($info, 1);
			}
		}
		if(M('kdgx_live_room')->save($config)){
			echo json_encode(array(
				'error' => 0,
				'msg' => '成功',
				));exit();
		}else{
			echo json_encode(array(
				'error' => 61026,
				'msg' => '修改失败',
				));exit();
		}
	}

	// 获取管理员列表
	public function getManagerList(){
		if(empty($_POST['room_id'])){
			echo json_encode(array(
				'error' => 61018,
				'msg' => '参数错误',
				));exit();
		}
		$uids = M('kddx_user_openid')->alias('uo')->field('lm.uid,uo.nickname,uo.headImgUrl,lm.is_agree')
		->join('kdgx_live_manager as lm on uo.uid=lm.uid')
		->where(array('uo.public_id'=>C('PUBLIC_ID'),'lm.room_id'=>$_POST['room_id']))->select();
		foreach ($uids as $key => $uid) {
			$uids[$key]['uid'] = encrypt_url($uid['uid'],C('KEY_URL'));
		}
		echo json_encode(array(
			'error' => 0,
			'msg' => $uids,
			));exit();
	}

	// 获取管理员申请列表
	public function getApplyManagerList(){
		if(empty($_POST['room_id'])){
			echo json_encode(array(
				'error' => 61018,
				'msg' => '参数错误',
				));exit();
		}
		$uids = M('kddx_user_openid')->alias('uo')->field('lm.uid,uo.nickname,uo.headImgUrl,lm.is_agree')
		->join('kdgx_live_manager as lm on uo.uid=lm.uid')
		->where(array('uo.public_id'=>C('PUBLIC_ID'),'lm.room_id'=>$_POST['room_id'],'is_agree'=>'0'))->select();
		foreach ($uids as $key => $uid) {
			$uids[$key]['uid'] = encrypt_url($uid['uid'],C('KEY_URL'));
		}
		echo json_encode(array(
			'error' => 0,
			'msg' => $uids,
			));exit();
	}

	// 添加管理员
	private function Manager($uid,$room_id){
		if(M('kdgx_live_manager')->data(array('room_id'=>$room_id,'uid'=>$uid,'is_agree'=>'1'))->add()){
			return true;
		}else{
			return false;
		}
	}

	// 审核管理员接口
	public function addManager(){
		if(empty($_POST['room_id']) || empty($_POST['uid'])){
			echo json_encode(array(
				'error' => 61013, 
				'msg' => '参数错误',
				));exit();
		}
		$uid = decrypt_url($_POST['uid'],C("KEY_URL"));
		$room_id = $_POST['room_id'];
		if($this->getPrivate($uid,$room_id)){
			echo json_encode(array(
				'error' => 61014,
				'msg' => '该用户已经是管理员',
				));exit();
		}else{
			if(M('kdgx_live_manager')->where(array('room_id'=>$room_id,'uid'=>$uid))->save(array('is_agree'=>'1'))){
				echo json_encode(array(
					'error' => 0,
					'msg' => '成功',
					));exit();
			}else{
				echo json_encode(array(
					'error' => 61015,
					'msg' => '添加失败',
					));exit();
			}
		}
	}

	// 删除管理员接口
	public function deleteManager(){
		if(empty($_POST['room_id']) || empty($_POST['uid'])){
			echo json_encode(array(
				'error' => 61011,
				'msg' => '参数错误',
				));exit();
		}
		$uid = decrypt_url($_POST['uid'],C('KEY_URL'));
		if(M('kdgx_live_manager')->where(array('room_id'=>$_POST['room_id'],'uid'=>$uid))->save(array('is_agree'=>'0'))){
			echo json_encode(array(
				'error' => 0,
				'msg' => '成功',
				));exit();
		}else{
			echo json_encode(array(
				'error' => 61012,
				'msg' => '删除失败',
				));exit();
		}
	}

	// 获取房间列表接口
	public function getRoomList(){
		if(empty($_POST['media_id'])){
			echo json_encode(array(
				'error' => 61003,
				'msg' => '参数错误',
				));exit();
		}
		$data = M('kdgx_live_room')->field('room_id,room_name,create_time,title')->where(array('media_id'=>$_POST['media_id'],'is_delete'=>'0'))->select();
		echo json_encode(array(
			'error' => 0,
			'msg' => $data,
			));exit();
	}

	// 修改弹幕接口
	public function updateComment(){
		if(empty($_POST['comment_id'])){
			echo json_encode(array(
				'error' => 61005,
				'msg' => '参数错误',
				));exit();
		}
		if(M('kdgx_live_comment')->save($_POST)){
			if(!empty($_POST['is_select'])){  // 精选留言
				$comment = M('kdgx_live_comment')->field('room_id,uid')->where(array('comment_id'=>$_POST['comment_id']))->find();
				$from_uid = decrypt_url($_POST['from_uid'],C('KEY_URL'));
				$action = array(
					'room_id' => $comment['room_id'],
					'from_uid' => $from_uid,
					'to_uid' => $comment['uid'],
					'create_time' => time(),
					'type' => '1',
					);
				$action_id = M('kdgx_live_action')->data($action)->add();
				if($action_id and $this->updateConnect(4)){
					echo json_encode(array(
						'error' => 0,
						'msg' => '成功',
						));exit();
				}else{
					echo json_encode(array(
						'error' => 61009,
						'msg' => '广播错误',
						));exit();					
				}
			}
			echo json_encode(array(
				'error' => 0,
				'msg' => '成功',
				));exit();
		}else{
			echo json_encode(array(
				'error' => 61006,
				'msg' => '修改失败',
				));exit();
		}
	}

	// 解除禁言接口
	public function removeBlackList(){
		$uid = decrypt_url($_POST['uid'],C('KEY_URL'));
		$black_uid = decrypt_url($_POST['black_uid'],C('KEY_URL'));
		$room_id = $_POST['room_id'];
		if(!$this->getPrivate($uid,$room_id)){
			echo json_encode(array(
				'error' => 61007,
				'msg' => '无修改权限',
				));exit();
		}
		if(M('kdgx_live_blacklist')->where(array('room_id'=>$room_id,'black_uid'=>$black_uid))->save(array('end_time'=>time()))){
			echo json_encode(array(
				'error' => 0,
				'msg' =>'解除禁言成功',
				));exit();
		}else{
			echo json_encode(array(
				'error' => 61018,
				'msg' =>'解除失败',
				));exit();
		}
	}

	// 禁言接口
	public function addBlackList(){
		$uid = decrypt_url($_POST['uid'],C("KEY_URL"));
		$black_uid = decrypt_url($_POST['black_uid'],C('KEY_URL'));
		$room_id = $_POST['room_id'];
		if(!$this->getPrivate($uid,$room_id)){
			echo json_encode(array(
				'error' => 61007,
				'msg' => '无修改权限',
				));exit();
		}
		if(M('kdgx_live_blacklist')->where(array('room_id'=>$_POST['room_id'],'black_uid'=>$black_uid,'end_time'=>array('gt',time())))->find()){
			echo json_encode(array(
				'error' => 61009,
				'msg' =>'该用户已经被禁言',
				));exit();
		}
		if(M('kdgx_live_manager')->where(array('room_id'=>$room_id,'uid'=>$black_uid,'is_agree'=>'1'))->find()){
			echo json_encode(array(
				'error' => 61008,
				'msg' => '该用户无法被禁言',
				));exit();
		}
		$data = array(
			'room_id'=>$_POST['room_id'],
			'black_uid'=>$black_uid,
			'manager_uid'=>decrypt_url($_POST['uid'],C('KEY_URL')),
			'start_time' => time(),
			'end_time'=>strtotime("+3 days"),
			);
		if(M('kdgx_live_blacklist')->data($data)->add()){
			$action = array(
				'room_id' => $data['room_id'],
				'from_uid' => $data['manager_uid'],
				'to_uid' => $data['black_uid'],
				'create_time' => time(),
				'type' => '2',
				);
			if(M('kdgx_live_action')->data($action)->add() && $this->updateConnect(4)){
				echo json_encode(array(
					'error' => 0,
					'msg' => '成功',
					));exit();
			}else{
				echo json_encode(array(
					'error' => 0,
					'msg' => '广播错误',
					));exit();
			}
		}
	}

}