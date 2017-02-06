<?php 
namespace Partner\Controller;
use Common\Controller\BaseController;

// 单身狗广场控制器
class AcSquareController extends BaseController{

	private $vipMedia = 'gh_aee726515b29';

	// 获取对象信息
	// 1. media_id数组
	// 2. lastTime上次拉取时间,默认不加入条件
	// 3. type是否
	// 4. id按id搜索对象,默认不加入条件
	private function getPartner($medias,$lastTime=0,$type=false,$id=0){
		$flag = 1;
		$page = trim($_GET['page']);
		$firstRow = ($page-1) * 10;
		if(time()>strtotime(date('Y-m-d 00:00:00')) and time()<strtotime(date('Y-m-d 01:11:11'))){
			$date = date('Y-m-d 22:11:11');
			$start = strtotime("$date -1 days");
		}else{
			$date = date('Y-m-d 22:11:11');
			$start = strtotime($date);
		} 
		$end = strtotime("$date +3 hours");
		$where = "p.uid !=0 and is_delete='0'";
		$where_y = "p.uid !=0 and is_delete='0'";
		if($id !=0){
			$where .= " and p.id='".$id."'";
			$where_y .= " and p.id='".$id."'";
		}
		if(time() > $start and time() < $end){ 			
			// 夜间无限制
			$flag = 0;
			if ($type) {
				if(empty($lastTime)){
					$where_y .= " and p.uploadtime>'".$start."'";
				}
				$where .= " and mpt.tag='1'";
				$pMedia = "'".$this->vipMedia."'";
			}else{
				if(empty($lastTime)){
					$where_y .= " and p.uploadtime>'".$start."'";
				}
				$where_y .= " and p.media_id in ('";				
				$where .= " and p.media_id in ('";
				foreach ($medias as  $media) {
					$where .= $media . "','";
					$where_y .= $media . "','";
				}
				$where = rtrim($where,"'");
				$where_y = rtrim($where_y,"'");
				$where = rtrim($where,",") . ")";
				$where_y = rtrim($where_y,",") . ")";
				$pMedia = 'p.media_id';
			}
		}else{
			// 正常状态
			if($type){
				$where .= " and mpt.tag='1'";
				$pMedia = "'".$this->vipMedia."'";
			}else{		
				$where .= " and mpt.tag='1' and p.media_id in ('"; 
				foreach ($medias as  $media) {
					$where .= $media . "','";
				}
				$where = rtrim($where,"'");
				$where = rtrim($where,",") . ")";
				$pMedia = 'p.media_id';
			}
		}
		if(!empty($lastTime)){
			$where .= " and uploadtime >'".$lastTime."'";
			$where_y .= " and uploadtime >'".$lastTime."'";
		}
		if(!empty($page)){
			$limit = $firstRow.",10";
			$limit_y = " limit ".$firstRow.",10"; 
		}
		if($flag){	
			$partners = M('koudai.kdgx_partner')
			->field('distinct(p.id) as id,wxmi.name as public_name,p.sex,p.school as school_name,p.uid,o.nickname,
				u.nick as nick,o.headimgurl,u.header_img as header_img,p.expect,p.photo_src,p.name as home,p.uploadtime,p.media_id')
			->alias('p')->join('koudai.kdgx_m_p_tag as mpt on p.id = mpt.partner and mpt.media_id='.$pMedia)
			->join("koudai.kddx_user_openid as o on p.uid = o.uid and o.public_id='".C('PUBLIC_ID')."'")
			->join('koudai.kdgx_wx_media_info as wxmi on p.media_id=wxmi.media_id')
			->join('koudai.kddx_user_info as u on p.uid=u.uid')->where($where)
			->group('p.id')->order('uploadtime desc')->limit($limit)->select();
		}else{
			$sql = "(SELECT distinct(p.id) as id,p.sex,wxmi.name as public_name,p.school as school_name,
					p.uid,o.nickname,u.nick as nick,o.headimgurl,u.header_img as header_img,p.expect,
					p.photo_src,p.name as home,p.uploadtime,p.media_id FROM koudai.kdgx_partner p 
					INNER JOIN koudai.kddx_user_openid as o on p.uid = o.uid and o.public_id='".C('PUBLIC_ID')."' 
					INNER JOIN koudai.kdgx_wx_media_info as wxmi on p.media_id=wxmi.media_id 
					INNER JOIN koudai.kddx_user_info as u on p.uid=u.uid where ".$where_y." )"."UNION(
					SELECT distinct(p.id) as id,p.sex,wxmi.name as public_name,p.school as school_name,
					p.uid,o.nickname,u.nick as nick,o.headimgurl,u.header_img as header_img,p.expect,p.photo_src,
					p.name as home,p.uploadtime,p.media_id FROM koudai.kdgx_partner p 
					INNER JOIN koudai.kdgx_m_p_tag as mpt on p.id = mpt.partner and mpt.media_id='gh_aee726515b29'
					INNER JOIN koudai.kddx_user_openid as o on p.uid = o.uid and o.public_id='".C('PUBLIC_ID')."' 
					INNER JOIN koudai.kdgx_wx_media_info as wxmi on p.media_id=wxmi.media_id
					 INNER JOIN koudai.kddx_user_info as u on p.uid=u.uid where ".$where.") order by uploadtime desc".$limit_y;
			$partners = M()->query($sql);
		}
		$partnerController = new \Partner\Controller\PartnerController();
		$partners = $partnerController->getPartnerRelation($partners,true);
		foreach ($partners as $key => $partner) {
			$partners[$key]['photo_src'] = 'http://'.C('CDN_SITE').'/'.$partner['photo_src'];
		}
		return $partners;
	}


	// 通过media获取所属学校所有号的media_id
	private function getSchoolsformedia($media_id){
		$res = M('kdgx_wx_media_info')->field('school_name')->where(array('media_id'=>$media_id))->find();
		$medias = M('kdgx_wx_media_info')->where(array('school_name'=>$res['school_name']))->getField('media_id',true);
		return $medias;
	}

	// 通过楼号获取简单对象信息
	public function getPartnerforid(){
		$id = $_GET['id'];
		$media_id = $_POST['media_id'];
		if(empty($id) || empty($media_id) || $media_id=='undefined'){
			echo json_encode(array(
				'error' => 20014,
				'msg' => '参数错误',
				));exit();
		}
		switch ($_POST['from']) {
			case '1': //全站对象信息
				$medias = array($media_id);
				$partners = $this->getPartner($medias,0,false,$id);
				break;
				
			case '2'://全校对象信息
				$medias = $this->getSchoolsformedia($media_id,0,false,$id);
				$partners = $this->getPartner($medias,0,false,$id);
				break;
			case '3': //全国对象信息
				$partners = $this->getPartner(array(),0,true,$id);
				break;
			default:
				echo json_encode(array(
					'error'	=> 20012,
					'msg'	=> '参数错误',
					));
				return;
		}	
		$partner = $partners['0'];
		if(empty($partner)){
			echo json_encode(array(
				'error' => -1,
				'msg' => '没有查到该对象',
				));exit();
		}
		echo json_encode($partner);exit();
	}

	// 获取区域站点的对象信息接口
	public function getPartnerformedia(){
		$media_id = $_POST['media_id'];

		if(empty($media_id) || $media_id=='undefined')exit('登录已超时!');
		switch ($_POST['from']) {
			case '1': //全站对象信息
				$medias = array($media_id);
				// -----------------------统计全站动态响应请求----------------------------
		        $collect = new \Org\Util\DataCollect();
		        $collect->CollectResponse('全站动态',decrypt_url(cookie('uid'),C('KEY_URL')),cookie('media_id'));
				break;
			case '2'://全校对象信息
				$medias = $this->getSchoolsformedia($media_id);
				// -----------------------统计全校动态响应请求----------------------------
		        $collect = new \Org\Util\DataCollect();
		        $collect->CollectResponse('全校动态',decrypt_url(cookie('uid'),C('KEY_URL')),cookie('media_id'));
				break;
			case '3'://全国对象信息
				if(empty($_POST['lastTime'])){
					$partners = $this->getPartner(array(),0,true);
				}else{
					$partners = $this->getPartner(array(),$_POST['lastTime'],true);
				}
				// -----------------------统计全国动态响应请求----------------------------
		        $collect = new \Org\Util\DataCollect();
		        $collect->CollectResponse('全国动态',decrypt_url(cookie('uid'),C('KEY_URL')),cookie('media_id'));
				echo json_encode($partners);return;
			default:
				echo json_encode(array(
					'error'	=> 20022,
					'msg'	=> '参数错误',
					));
				return;
		}
		if(empty($_POST['lastTime'])){
			$partners = $this->getPartner($medias);
		}else{
			$partners = $this->getPartner($medias,$_POST['lastTime']);
		}

		echo json_encode($partners);return;
	}

	// 发送弹幕或表白接口
	public function sendComment(){
		$comment = $_POST;
		$comment['uid'] = decrypt_url($comment['uid'],C('KEY_URL')); // 解密uid
		$comment['partner_id'] = uiddecode($comment['partner_id']);	// 解密对象id

		// $comment = array(
		// 	'uid' => '10007',
		// 	'content' => '<script>这是一条弹幕3</script>',
		// 	'type'	=> 0,
		// 	'partner_id' => 268,
		// 	'media_id' => 'gh_c75321282c18',
		//  'nickname' => '摇了摇头',
		// 	'connect_id'=>1,
		// 	); 

		$connect_id = $comment['connect_id'];
		$comment['time'] = time();
		$comment['content'] = htmlspecialchars($comment['content']);
		if(M('kdgx_partner_comment')->data($comment)->add()){
			$this->updateStateConnect($connect_id);
			echo json_encode(array(
				'error'=>0,
				'msg'=>'成功',
				));return;
		}else{
			echo json_encode(array(
				'error'=>20001,
				'msg'=>'弹幕发送失败',
				));return;
		}
	}

	// 点赞接口
	public function thumpPraise(){
		if(empty($_POST['uid']) || empty($_POST['id'])){
				echo json_encode(array(
					'error'	=> 20028,
					'msg'	=> '参数错误',
					));exit();		
		}
		$uid = decrypt_url($_POST['uid'],C("KEY_URL"));
		$partner_id = $_POST['id'];
		if(M('kdgx_partner_praise')->where(array('uid'=>$uid,'partner_id'=>$partner_id))->find()){
				echo json_encode(array(
					'error'	=> 10049,
					'msg'	=> '您已经点过赞',
					));exit();			
		}else{
			if(M('kdgx_partner_praise')->data(array('partner_id'=>$partner_id,'uid'=>$uid,'time'=>time()))->add()){
				echo json_encode(array(
					'error'	=> 0,
					'msg'	=> '成功',
					));exit();				
			}else{
				echo json_encode(array(
					'error'	=> 10050,
					'msg'	=> '系统繁忙',
					));exit();
			}
		}
	}

	// 更新某连接获取弹幕的状态
	private function updateStateConnect($connect_id){
		$res = M('kdgx_partner_connect')->field('type,medias')->where(array('connect_id'=>$connect_id))->find();
		switch ($res['type']) {
			case '1':
			case '2':
				M('kdgx_partner_connect')->where(array('medias'=>$res['medias'],'type'=>$res['type']))->save(array('state'=>'1'));
				break;
			case '3':
				M('kdgx_partner_connect')->where(array('type'=>$res['type']))->save(array('state'=>'1'));
				break;
			
		}
	}

	// 打开连接池获取连接ID接口
	public function openConnect(){
		// $_POST['type'] = 0;
		// $media_id = 'gh_c75321282c18';
		
		$media_id = $_POST['media_id'];
		$type = $_POST['from'];
		switch ($type) {
			case '1': #站点连接
			case '3': #全国连接
				echo M('kdgx_partner_connect')->data(array('medias'=>$media_id,'type'=>$type,'time'=>time(),'lastRequestTime'=>time()))->add();
				return;
			case '2': #校园连接
				$res = M('kdgx_wx_media_info')->field('school_name')->where(array('media_id'=>$media_id))->find();
				echo M('kdgx_partner_connect')->data(array('medias'=>$res['school_name'],'type'=>$type,'time'=>time(),'lastRequestTime'=>time()))->add();
				return;
		}
	}

	// 获取连接的心跳包
	public function getConnectState(){
		$connect_id = $_POST['connect_id'];
		$res = M('kdgx_partner_connect')->field('state')->where(array('connect_id'=>$connect_id))->find();
		M('kdgx_partner_connect')->where(array('connect_id'=>$connect_id))->save(array('lastRequestTime'=>time()));
		echo json_encode(array(
			'state' => $res['state'],	// 连接刷新状态
			'count' => $this->getConnectCounts(),	// 连接数量
			'partnerCount'=> $this->getNewPartnerCount(),	//动态数量
			'commentCount'=> $this->getCommentCount(),	//弹幕数量
			));exit();
	}

	// 获取弹幕数量
	private function getCommentCount(){
		$media_id = $_POST['media_id'];
		switch ($_POST['from']) {
			case '1':	#个人站弹幕	
			case '3':	#全国站弹幕
				return M('kdgx_partner_comment')->where(array('media_id'=>$media_id,'from'=>$_POST['from']))->count('id');
			case '2':	#全校站弹幕
				$medias = $this->getSchoolsformedia($media_id);
				return M('kdgx_partner_comment')->where(array('media_id'=>array('in',$medias),'from'=>$_POST['from']))->count('id');
		}
	}
	
	// 获取新动态数量
	private function getCounts($medias,$lastTime,$type=false){
		if ($type) {
			$where = "uid !=0 and uploadtime>'".$lastTime."' and is_delete='0'";
		}else{				
			$where = "uid!=0 and uploadtime>'".$lastTime."' and is_delete='0' and media_id in ('";
			foreach ($medias as  $media) {
				$where .= $media . "','";
			}
			$where = rtrim($where,"'");
			$where = rtrim($where,",") . ")";
		}
		$count =  M('kdgx_partner')->cache(false)->field('count(id) as count')->where($where)->select();
		return $count['0']['count'];
	}
	// 根据参数分发房间
	private function getNewPartnerCount(){
		if(time()>strtotime(date('Y-m-d 00:00:00')) and time()<strtotime(date('Y-m-d 01:11:11'))){
			$date = date('Y-m-d 22:11:11');
			$start = strtotime("$date -1 days");
		}else{
			$date = date('Y-m-d 22:11:11');
			$start = strtotime($date);
		} 
		$end = strtotime("$date +3 hours");
		if(time()<$start || time() > $end){
			return -1;	// 不在开放时间
		}
		$media_id = $_POST['media_id'];
		$lastTime = $_POST['lastTime'];
		if(empty($media_id) || $media_id=='undefined')exit('登录已超时');
		switch ($_POST['from']) {
			case '1': // 个人站动态
				$medias = array($media_id);
				break;
			case '2':	// 校园动态
				$medias = $this->getSchoolsformedia($media_id);
				break;
			case '3':	// 全国动态
				$count = $this->getCounts(array(),$lastTime,true);
				return $count;
			default:
				echo json_encode(array(
					'error'	=> 20002,
					'msg'	=> '参数错误',
					));
				return;
		}
		$count = $this->getCounts($medias,$lastTime);
		return $count;
	}

	// 获取当前连接数接口
	private function getConnectCounts(){
		$media_id = $_POST['media_id'];
		$type = $_POST['from'];
		switch ($type) {
			case '1':	#站点连接
			case '3':	#全国连接
				return M('kdgx_partner_connect')->where(array('medias'=>$media_id,'lastRequestTime'=>array('egt',strtotime("-2 seconds")),'type'=>$type))->count('connect_id');
			case '2':	#校园连接
				$res = M('kdgx_wx_media_info')->field('school_name')->where(array('media_id'=>$media_id))->find();
				return M('kdgx_partner_connect')->where(array('medias'=>$res['school_name'],'lastRequestTime'=>array('egt',strtotime("-2 seconds")),'type'=>$type))->count('connect_id');
		}
	}

	// 通过 media_id数组 获取弹幕
	private function getComment($medias,$connect_id,$from=1){
		$res = M('kdgx_partner_connect')->alias('time')->where(array('connect_id'=>$connect_id))->find();
		$comments = M('kdgx_partner_comment')->field('content,type,nickname,partner_id')
		->where(array('media_id'=>array('in',$medias),'from'=>$from,'is_delete'=>'0','time'=>array('gt',$res['time'])))
		->order('time desc')->select();
		M('kdgx_partner_connect')->where(array('connect_id'=>$connect_id))->save(array('state'=>'0','time'=>time()));
		return $comments;
	}

	// 获取最近弹幕,默认50条
	public function getRecentComment(){
		$media_id = $_POST['media_id'];
		$from = $_POST['from'];
		$connect_id = $_POST['connect_id'];
		$count = 50;
		if(empty($media_id) || $media_id=='undefined')exit('登录超时!!');
		switch ($from) {
			case '1':
				$medias = array($media_id);
				break;
			case '3':
				$count = 150;
				$medias = array($media_id);
				break;
			case '2':
				$medias = $this->getSchoolsformedia($media_id);
				break;
			default:
				echo json_encode(array(
					'error' => 20003,
					'msg'	=> '参数错误',
					));return;
		}
		$comments = M('kdgx_partner_comment')->field('content,type,nickname,partner_id')
		->where(array('media_id'=>array('in',$medias),'from'=>$from,'is_delete'=>'0'))
		->order('time desc')->limit(0,$count)->select();
		M('kdgx_partner_connect')->where(array('connect_id'=>$connect_id))->save(array('state'=>'0','time'=>time()));
		echo json_encode($comments);return;
	}

	// 获取不同区域站点的弹幕信息接口
	public function getCommentformedia(){
		$media_id = $_POST['media_id'];
		$from = $_POST['from'];
		$connect_id = $_POST['connect_id'];
		
		// $media_id = 'gh_c75321282c18';
		// $from = 1;
		// $connect_id =1;
		
		if(empty($media_id) || $media_id=='undefined')exit('登录超时!!');
		switch ($from) {
			case '1':
			case '3':
				$medias = array($media_id);
				break;
			case '2':
				$medias = $this->getSchoolsformedia($media_id);
				break;
			default:
				echo json_encode(array(
					'error' => 20003,
					'msg'	=> '参数错误',
					));return;
		}
		$comments = $this->getComment($medias,$connect_id,$from);
		echo json_encode($comments);return;
	}

	// 获取可管理弹幕接口
	public function getCommentToAdmin(){
		$page = I('get.page',1,'intval');
		$firstRow = ($page-1) * 100;
		$media_id = $_POST['media_id'];
		$from = $_POST['from'];
		$content = trim($_POST['content']);
		if(!empty($content)){
			$contentWhere = array(
				'content' => array('like',"%".$content."%",'AND'),
				);
		}else{
			$contentWhere  = '1';
		}
		if(empty($media_id) || $media_id=='undefined')exit('登录超时!!');
		switch ($from) {
			case '1':
			case '3':
				$medias = array($media_id);
				break;
			case '2':
				$medias = $this->getSchoolsformedia($media_id);
				break;
			default:
				echo json_encode(array(
					'error' => 20003,
					'msg'	=> '参数错误',
					));return;
		}

		$where = array(
			'media_id'=>array('in',$medias),
			'from'=>$from,
			'is_delete'=>'0',
			'_complex' => $contentWhere,
			);
		$comments = M('kdgx_partner_comment')->field('id as comment_id,content,type,nickname,partner_id')
		->where($where)->order('time desc')->limit($firstRow,100)->select();
		echo json_encode($comments);			
	}

	// 删除弹幕接口
	public function deleteComment(){
		$id = $_POST['comment_id'];
		if(empty($id)){
			echo json_encode(array(
				'error' => 20003,
				'msg'	=> '参数错误',
				));exit();
		}
		if(M('kdgx_partner_comment')->where(array('id'=>$id))->data(array('is_delete'=>'1'))->save()){
			echo json_encode(array(
				'error' => 0,
				'msg' => '删除成功',
				));exit();
		}else{
			echo json_encode(array(
				'error' => 20004,
				'msg' => '删除失败',
				));exit();
		}
	}

	// 获取站点名称列表接口
	public function getAllMedia(){
		$mediaNames = M('kdgx_wx_media_info')->field('name')->select();
		echo json_encode($mediaNames);return;
	}	

}