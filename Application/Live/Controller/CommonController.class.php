<?php 
namespace Live\Controller;
use \Think\Controller;

 // 直播互动公用类
 class CommonController extends Controller{

	protected $js1 = <<<EOF

	<head>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	</head>
	<body>
	<div id="hide" class="js_dialog">
      <div class="weui-mask"></div>
      <div class="weui-dialog">
          <div class="weui-dialog__bd">
EOF;

	protected $js2 = <<<FOE

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

  protected $js3 = <<<EFOF

</div>
          <div class="weui-dialog__ft">
              <a id="operate" class="weui-dialog__btn weui-dialog__btn_primary">确定</a>
          </div>
          <script>
             document.querySelector('#operate').onclick=function(){
               document.querySelector('#hide').style.display='none'
             }
          </script>

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
EFOF;


 	// 打开连接
 	public function openConnect(){
 		if(empty($_POST['room_id'])){
 			echo json_encode(array(
 				'error' => 60001,
 				'msg' => '参数错误',
 				));exit();
 		}
 		$_POST['connect_time'] = time();
 		$_POST['last_time'] = time();
 		$_POST['last_get_comment_time'] = time();
 		$_POST['last_get_action_time'] = time();
 		if($connect_id = M('kdgx_live_connect')->data($_POST)->add()){
 			$data = array(
 				'room_id' => $_POST['room_id'],
 				'from_uid' => decrypt_url($_POST['uid'],C('KEY_URL')),
 				'create_time' => time(),
 				);
 			M('kdgx_live_action')->data($data)->add();
      M('kdgx_live_connect')->where(array('last_time'=>array('egt',strtotime("-3 seconds")),'room_id'=>$_POST['room_id']))->save(array('action_state'=>'1'));
      // echo M('kdgx_live_action')->getLastSql();
 			echo json_encode(array(
 				'error' => 0,
 				'msg' => $connect_id,
 				));exit();
 		}else{
 			echo M('kdgx_live_connect')->getError();
 			echo M('kdgx_live_connect')->getLastSql();
 			echo json_encode(array(
 				'error' => 60002,
 				'msg' => '打开失败',
 				));exit();
 		}
 	}

 	// 更新连接
 	// type 0:上次请求时间;1:上次拉取弹幕数据时间;2:上次拉取动作数据时间;
 	// 3:弹幕更新;4:动作更新;
 	protected function updateConnect($type=0){
 		if(empty($_POST['connect_id'])){
 			echo json_encode(array(
 				'error' => 60003,
 				'msg' => '参数错误',
 				));exit();
 		}
		$data = array(
			'connect_id'=>$_POST['connect_id'],
			'last_time'=>time()
		);
 		switch ($type) {
 			case '0':
 				break;
 			case '1':
 				$data['last_get_comment_time'] = time();
 				$data['comment_state'] = 0;
 				break;
 			case '2':
 				$data['last_get_action_time'] = time();
 				$data['action_state'] = 0;
 				break;
 			case '3':
 				$save = array('comment_state'=>'1');
        $room_id = M('kdgx_live_connect')->where(array('connect_id'=>$data['connect_id']))->getField('room_id');
        if(M('kdgx_live_connect')->where(array('last_time'=>array('egt',strtotime("-3 seconds")),'room_id'=>$room_id))->save($save)){
            return true;
        }else{
            return false;
        }
 			case '4':
        $save = array('action_state'=>'1');
        $room_id = M('kdgx_live_connect')->where(array('connect_id'=>$data['connect_id']))->getField('room_id');
        if(M('kdgx_live_connect')->where(array('last_time'=>array('egt',strtotime("-3 seconds")),'room_id'=>$room_id))->save($save)){
            return true;
        }else{
            return false;
        }
 		}
 		if(M('kdgx_live_connect')->save($data)){
 			return true;
 		}else{
 			return false;
 		}

 	}

 	// 获得连接心跳包
 	public function getHeartpacket(){
 		if(empty($_POST['connect_id'])){
 			echo json_encode(array(
 				'error' => 60005,
 				'msg' => '参数错误',
 				));exit();
 		}
 		$data = M('kdgx_live_connect')->field('room_id,comment_state,action_state')->where(array('connect_id'=>$_POST['connect_id']))->find();
 		$data['OnlineCounts'] = $this->getRoomOnlineCounts($data['room_id']);
 		$data['CommentCounts'] = $this->getRoomCommentCounts($data['room_id']);
 		$res = M('kdgx_live_room')->field('end_time')->where(array('is_delete'=>'0','room_id'=>$data['room_id']))->find();
 		$data['end_time'] = $res['end_time'];
 		unset($data['room_id']);
 		$this->updateConnect(0);
 		echo json_encode(array(
 			'error' => 0,
 			'msg' => $data,
 			));exit();
 	}

 	// 获取房间在线人数
 	protected function getRoomOnlineCounts($room_id){
 		return M('kdgx_live_connect')->where(array('last_time'=>array('egt',strtotime("-1 seconds")),'room_id'=>$room_id))->count('connect_id');
 	}

 	// 获取房间弹幕数量
 	protected function getRoomCommentCounts($room_id){
 		return M('kdgx_live_comment')->where(array('room_id'=>$room_id))->count('comment_id');
 	}

 	// 获取房间信息
 	public function getRoomInfo(){
 		if(empty($_POST['room_id'])){
 			echo json_encode(array(
 				'error' => 60006,
 				'msg' => '参数错误',
 				));exit();
 		}
 		if($data = M('kdgx_live_room')->where(array('room_id'=>$_POST['room_id'],'is_delete'=>'0'))->find()){
 			echo json_encode(array(
 				'error' => 0,
 				'msg' => $data,
 				));exit();
 		}else{
 			echo json_encode(array(
 				'error' => 60007,
 				'msg' => '获取失败',
 				));exit();
 		}
 	}

 	// 通过media获取所属学校所有号的media_id
	protected function getSchoolsForMedia($media_id){
		$res = M('kdgx_wx_media_info')->field('school_name')->where(array('media_id'=>$media_id))->find();
		$medias = M('kdgx_wx_media_info')->where(array('school_name'=>$res['school_name']))->getField('media_id',true);
		return $medias;
	}

  // 是否提交申请
  protected function isApplayManager($uid,$room_id){
    if(M('kdgx_live_manager')->where(array('room_id'=>$room_id,'uid'=>$uid,'is_agree'=>'0'))->find()){
      return true;
    }else{
      return false;
    }
  }

	// 获取管理员权限
	protected function getPrivate($uid,$room_id){
		if(M('kdgx_live_manager')->where(array('room_id'=>$room_id,'uid'=>$uid,'is_agree'=>'1'))->find()){
			return true;
		}else{
			return false;
		}
	}

	// 是否是管理员
	public function isManager(){
		$uid = decrypt_url($_POST['uid'],C("KEY_URL"));
		$room_id = $_POST['room_id'];
		$msg = $this->getPrivate($uid,$room_id) ? 1:0;
		echo json_encode(array(
			'error' => 0,
			'msg' => $msg,
			));exit();
	}

	// 是否是黑名单
	protected function isBlack($uid,$room_id){
		if(M('kdgx_live_blacklist')->where(array('room_id'=>$room_id,'end_time'=>array('gt',time()),'black_uid'=>$uid))->find()){
			return true;
		}else{
			return false;
		}
	}

	// 房间是否关闭
	protected function isClose($room_id){
		$room = M('kdgx_live_room')->field('room_id,end_time')->where(array('room_id'=>$room_id))->find();
		if($room['end_time']<time()){
			return true;
		}else{
			return false;
		}
	}
}