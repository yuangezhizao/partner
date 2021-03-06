<?php 
namespace Partner\Controller;
use Common\Controller\BaseController;
/**
 * 帮室友找对象
 * 
 */
class PartnerController extends BaseController{

	private $adminUid = ['10001','10002','98047','94302','87715','91031'];

	// 帮室友找对象首页入口
	public function Index(){
		$agree = session('agree');
		if(empty($agree) || $agree != md5('agree')){
			$from = urlencode($_SERVER['REQUEST_URI']);	// 获得当前路由
			redirect("/index.php/home/user/login?flag=0&from=$from");
		}
		// -----------------------统计应用首页响应请求----------------------------
        $collect = new \Org\Util\DataCollect();
        $collect->CollectResponse('应用首页',decrypt_url(cookie('uid'),C("KEY_URL")),cookie('media_id')|0);
		$this->display('Partner/index');
	}
	// 历史入口
	public function History(){
		$agree = session('agree');
		if(empty($agree) || $agree != md5('agree')){
			$from = urlencode($_SERVER['REQUEST_URI']);	// 获得当前路由
			redirect("/index.php/home/user/login?flag=0&from=$from");
		}
		// -----------------------统计买卖历史响应请求----------------------------
        $collect = new \Org\Util\DataCollect();
        $collect->CollectResponse('买卖历史',decrypt_url(cookie('uid'),C("KEY_URL")),$media_id);
		$this->display('Partner/History');
	}
	// 测试历史入口
	public function History_test(){
		$agree = session('agree');
		if(empty($agree) || $agree != md5('agree')){
			$from = urlencode($_SERVER['REQUEST_URI']);	// 获得当前路由
			redirect("/index.php/home/user/login?flag=0&from=$from");
		}
		// -----------------------统计买卖历史响应请求----------------------------
        $collect = new \Org\Util\DataCollect();
        $collect->CollectResponse('买卖历史',decrypt_url(cookie('uid'),C("KEY_URL")),$media_id);

		$this->display('Partner/History_test');
	}

	// 弹幕管理入口
	public function Barrage(){
		$this->display('Partner/barrage');
	}

	// 当前用户是否是管理员
	public function isAdmin(){
		$uid = decrypt_url(session('uid'),C("KEY_URL"));
		if(in_array($uid, $this->adminUid)){
			echo json_encode(array(
				'error' => 0,
				'msg' => 'admin',
				));exit();
		}else{
			echo json_encode(array(
				'error' => -1,
				'msg' => 'no',
				));exit();
		}
	} 

	/**
	 * 获取个人卖室友记录
	 * @param string uid 个人uid
	 * @return json 记录列表
	 */
	private function getSellPartnerList($uid){
		$partners = M('kdgx_partner')->field('p.introduce,p.expect,p.uid,p.uploadtime,max(p.id) as id,p.photo_src,p.media_id,o.nickname,o.headimgurl')
		->alias('p')->join('kddx_user_openid as o on p.uid=o.uid')->group('p.id')
		->where(array('p.uid'=>$uid,'is_delete'=>'0'))->select();
		return $this->getPartnerRelation($partners,true);
	}

	// 通过对象获取与此对象关联的用户的交互信息
	// 1.要过此对象的人的个人信息
	// 2.表白过此对象的表白信息与表白者信息
	// 3.点赞过此对象的点赞者信息
	public function getPartnerRelation($partners,$flag=false){
		foreach ($partners as $key => $partner) {
			// 获取要联系方式的信息
			$uids = M('kdgx_partner_limit')->field('distinct(uid) as uid')
			->where('formid like "#'.$partner['id'].',%" or formid like "%,'.$partner['id'].',%" or formid like "%,'.$partner['id'].'"')->group('uid')->select();
			$users = array();
			foreach ($uids as $uid) {
				$user = M('kddx_user_openid')->field('nickname,headimgurl')
				->where(array('uid'=>$uid['uid'],'public_id'=>C("PUBLIC_ID")))->find();
				if(!empty($user)){
					$user['uid'] = encrypt_url($uid['uid'],C('KEY_URL'));
					$users[] = $user;
				}
			}
			if($flag){
				// 获取表白信息
				$comments = M('kdgx_partner_comment')->field('id as comment_id,uid,content,type')
				->where(array('partner_id'=>$partner['id'],'type'=>'1','is_delete'=>'0'))->order('time asc')->select();	
				foreach ($comments as $key1 => $comment) {
					$res = M('kddx_user_openid')->field('nickname,headimgurl')
					->where(array('uid'=>$comment['uid'],'public_id'=>C("PUBLIC_ID")))->find();
					if(!empty($res)){
						$res['uid'] = encrypt_url($comment['uid'],C('KEY_URL'));
						$comments[$key1]['user'] = $res;
						unset($comments[$key1]['uid']);
					}
				}
				// 获取点赞信息
				$praises = M('kdgx_partner_praise')->where(array('partner_id'=>$partner['id']))->select();
				foreach ($praises as $keyp => $praise) {
					$resp = M('kddx_user_openid')->field('nickname,headimgurl')
					->where(array('uid'=>$praise['uid'],'public_id'=>C("PUBLIC_ID")))->find();
					if(!empty($resp)){
						$resp['uid'] = encrypt_url($praise['uid'],C('KEY_URL'));
						$praises[$keyp]['nickname'] = $resp['nickname'];
						$praises[$keyp]['headimgurl'] = $resp['headimgurl'];
						unset($praises[$keyp]['uid']);
					}					
				}
			}
			// 加密解密处理
			$partners[$key]['formid'] = uidencode($partner['id']);
			if($partner['uid']==decrypt_url(cookie('uid'),C("KEY_URL"))){
				$partners[$key]['self'] = 1;
			}
			$partners[$key]['uid'] = encrypt_url($partners[$key]['uid'],C("KEY_URL"));		
			$partners[$key]['nickname'] = empty($partner['nickname']) ? $partner['nick'] : $partner['nickname'];
			$partners[$key]['headimgurl'] = empty($partner['headimgurl']) ? $partner['header_img'] : $partner['headimgurl'];
			$partners[$key]['IsAcquired'] = $users;
			$partners[$key]['comment'] = $comments;
			$partners[$key]['praise'] = $praises;
			$partners[$key]['flag'] = 0;	// 个人买出去的对象
			$partners[$key]['time'] = $partner['uploadtime'];
		}
		return $partners;
	}
	/**
	 * 获取个人要联系方式的记录
	 */
	private function getPartnerList($uid){
		$partners = M('kdgx_partner_limit')->field('today,formid')->where(array('uid'=>$uid))->select();
		$list = array();
		foreach ($partners as $key => $partner) {
			$formids = trim($partner['formid'],'#');
			$formids = explode(',', $formids);
			foreach ($formids as $key => $formid) {
				$form = M('kdgx_partner')->field('p.id,o.nickname,o.headimgurl,p.photo_src,p.media_id,p.expect,p.introduce,p.uid')
				->alias('p')->join("kddx_user_openid as o on p.uid=o.uid and o.public_id='".C('PUBLIC_ID')."'")
				->where(array('p.id'=>$formid,'is_delete'=>'0'))->find();
				if(!empty($form)){
					$form['time'] = $partner['today'];
					$form['formid'] = uidencode($form['id']);
					$form['uid'] = encrypt_url($form['uid'],C('KEY_URL'));
					// $form['photo_src'] = 'http://'.C('CDN_SITE').'/'.$form['photo_src'];
					$form['flag'] = 1; // 个人要过联系方式的对象
					unset($form['id']);
					$list[] = $form;
				}
			}
		}
		return $list;
	}

	// 个人表白记录
	private function getCommentList($uid){
		$comments =  M('kdgx_partner_comment')->field('id,content,time,partner_id,is_anonymous')
		->where(array('uid'=>$uid,'type'=>'1','is_delete'=>'0'))->select();
		$list = array();
		foreach ($comments as $key => $comment) {
			$partner = M('kdgx_partner')->field('p.id,o.nickname,o.headimgurl,p.photo_src,p.media_id,p.expect,p.introduce,p.uid')
			->alias('p')->join('kddx_user_openid as o on p.uid=o.uid')
			->where(array('p.id'=>$comment['partner_id'],'is_delete'=>'0'))->find();
			if(!empty($partner)){
				$partner['time'] = $comment['time'];
				$partner['formid'] = uidencode($partner['id']);
				$partner['uid'] = encrypt_url($partner['uid'],C("KEY_URL"));
				$partner['content'] = $comment['content'];
				$partner['is_anonymous'] = $comment['is_anonymous'];
				$partner['comment_id'] = $comment['id'];
				// $partner['photo_src'] = 'http://'.C('CDN_SITE').'/'.$partner['photo_src'];
				$partner['flag'] = 2; // 个人表白过的对象
				unset($partner['id']);
				$list[] = $partner;
			}
		}
		return $list;
	}

	// 个人点赞记录
	private function getPraiseList($uid){
		$praises = M('kdgx_partner_praise')->field('id,partner_id,time')->where(array('uid'=>$uid))->select();
		$list = array();
		foreach ($praises as $key => $praise) {
			$partner = M('kdgx_partner')->field('p.id,o.nickname,o.headimgurl,p.media_id,p.photo_src,p.expect,p.introduce,p.uid')
			->alias('p')->join('kddx_user_openid as o on p.uid=o.uid')
			->where(array('p.id'=>$praise['partner_id'],'is_delete'=>'0'))->find();
			if(!empty($partner)){
				$partner['time'] = $praise['time'];
				$partner['formid'] = uidencode($partner['id']);
				$partner['uid'] = encrypt_url($partner['uid'],C("KEY_URL"));
				$partner['is_anonymous'] = $praise['is_anonymous'];
				$partner['comment_id'] = $praise['id'];
				// $partner['photo_src'] = 'http://'.C('CDN_SITE').'/'.$partner['photo_src'];
				$partner['flag'] = 3; // 个人点赞过的对象
				unset($partner['id']);
				$list[] = $partner;
			}
		}
		return $list;		
	}


	// 修改表白匿名状态
	public function updateAnonymous(){
		if(empty($_POST['comment_id']) || empty($_POST['is_anonymous'])){
			echo json_encode(array(
				'error' => 2024,
				'msg' => '参数错误',
				));exit();
		}
		if(M('kdgx_partner_comment')->where(array('id'=>$_POST['comment_id']))
			->save(array('is_anonymous'=>$_POST['is_anonymous']))){
			echo json_encode(array(
				'error' => 0,
				'msg' => '修改成功',
				));exit();
		}else{
			echo json_encode(array(
				'error' => 2025,
				'msg' => '修改失败',
				));exit();
		}
	}

	/**
	 * 获取个人交互记录
	 * @param string uid 个人uid
	 * @return json 记录json数据
	 */
	public function getList(){
		$uid = $_POST['uid'];
		if($uid == 'undefined' || empty($uid))exit('登录过期!');
		$uid = decrypt_url($uid,C("KEY_URL"));
		$sellList = $this->getSellPartnerList($uid);
		$partnerlist = $this->getPartnerList($uid);
		$CommentList = $this->getCommentList($uid);
		$praiseList = $this->getPraiseList($uid);
		$list = array_merge($sellList,$partnerlist,$CommentList,$praiseList);
		foreach ($list as $key => $value) {
			$list[$key]['photo_src'] = 'http://'.C('CDN_SITE').'/'.$value['photo_src'];
		}
		usort($list, 'desc_sort');
		// -----------------------统计个人历史响应请求----------------------------
        $collect = new \Org\Util\DataCollect();
        $collect->CollectResponse('个人历史',decrypt_url(cookie('uid'),C('KEY_URL')),cookie('media_id'));
		print_r(json_encode($list));
	}

	// 判断公众号是否用自定义权限
    private function isHavePermissions($media_id,$formid){
    	$result = M('kdgx_partner')->alias('p')
    	->join('LEFT JOIN kdgx_wx_media_info m ON p.media_id = m.media_id')
    	->where(array('p.id'=>$formid))->field('m.id')->find();
    	$permissions = M('kdgx_partner_site')->field('permissions')->where(array('school_id'=>$result['id'],'media_id'=>$media_id))->find();
    	return $permissions['permissions'];
    }

    // 回收对象
	public function recycPartner(){
		$formids = $_POST;
		$media_id = $_COOKIE['permission'];
		if(empty($formids)){
			return;
		}
		$total = 0;
		try{	
			foreach ($formids as $key => $formid) {
				$formid = json_decode($formid,true);
				$formid = uiddecode($formid['formid']);
				$uid = decrypt_url(session('uid'),C("KEY_URL"));
				if(!empty($uid)){
					if(!in_array($uid, $this->adminUid)){
						$fuid = M('kdgx_partner')->field('uid')->where(array('id'=>$formid))->find();
						if($uid!=$fuid['uid']){
							echo json_encode(array(
								'error'	=>	100008,
								'msg'	=>	'删除失败!',
							));return;
						}
					}
				}else{				
					if(!empty($media_id)){
						if($this->isHavePermissions($media_id,$formid) != '1'){
							echo json_encode(array(
								'error'	=>	100007,
								'msg'	=>	'删除失败!',
							));return;
						}
					}
				}
				if(M('kdgx_partner')->where(array('id'=>$formid))->data(array('is_delete'=>'1'))->save()){
					$total++;
				}
			}
			cookie('permission',null);
			if($total>0){
				echo json_encode(array(
					'error' => 0,
					'msg'	=> $total.'条删除成功!',
				));	
			}else{
				echo json_encode(array(
					'error'	=>	100007,
					'msg'	=>	'删除失败!',
				));	
			}
		}catch(Exception $e){
			file_put_contents('./Logs/partner.log',date('Y-m-d H:i:s'). "Partner->Partner->recycPartner:".$e->getMessage());
			echo json_encode(array(
				'error'	=>	100007,
				'msg'	=>	'删除失败!',
			));
		}
	}
	/**
	 * 图片处理函数
	 * @param  array $files 图片数组
	 * @return 处理结果     图片存储地址或false
	 */
	private function uploadimg($files){
		if($files['photo_src']['error']===0){
			$upload = new \Think\Upload();
			$info = $upload->uploadOne($_FILES['photo_src']);
			if(!$info){
				return false;
			}else{
				$path = $upload->rootPath . $info['savepath'] . $info['savename'];
				$image = new \Think\Image();
				$image->open($path);
				$image->thumb(414,414)->save($path);
				return $path;
			}
		}else{
			return false;
		}
	}	

	/**
	 * base64图片处理函数
	 * @param  array $files 图片数组
	 * @return 处理结果         图片存储地址或false
	 */
	private function uploadBaseImg($files){
		$upload = new \Org\Util\UploadBase64(array('rootPath'=>'D:\VirtualHost\Uploads/Uploads/'));
		$info = $upload->upload($files);
		$info = substr($info, strlen("D:\VirtualHost\Uploads/")-2);
		if($info){
			$url = "http://".C('CDN_SITE')."/$info";
			requestGet($url);
			return $info;
		}else{
			return false;
		}
	}

	/**
	 * 生成暗号
	 * @return string 生成的暗号
	 */
	private function getSecret(){
		$codeSet = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
		$secret = ''; 	// 暗号
		for($i=0;$i<8;$i++){
			$secret .= $codeSet[mt_rand(0, strlen($codeSet)-1)];
		}
		return $secret;
	}
	/**
	 * 对象数据处理
	 * @return [type] 写入数据库后的id
	 */
	public function editData(){
		$partnerInfo = I('post.');
		$partnerInfo['uploadtime'] = time();
		$partnerInfo['secret'] = $this->getSecret();		//生成暗号
		// $photo_src = $this->uploadimg($_FILES);	//图像处理
		$photo_src = $this->uploadBaseImg($partnerInfo['photo_src']);	//图像处理
		if($photo_src){#图像处理成功
			$partnerInfo['photo_src'] =substr($photo_src, 2);
			$partnerInfo['uid'] = decrypt_url($partnerInfo['uid'],C("KEY_URL"));
			if(empty($partnerInfo['uid'])){
				echo "msgerr";return;
			}
			if($data = M('kdgx_partner')->create($partnerInfo)){
				$result = M('kdgx_partner')->data($data)->add();
				if($result){
					$result = uidencode($result);
					$photo_src = "http://".C('CDN_SITE').'/'.$partnerInfo['photo_src'];

					// -----------------------统计卖室友响应请求----------------------------
			        $collect = new \Org\Util\DataCollect();
			        $collect->CollectResponse('卖室友',$partnerInfo['uid'],$partnerInfo['media_id']);

					echo json_encode(array(
						'formid' => $result,
						'photo_src' => $photo_src,
						));
				}else{
					echo "msgerr";return;	//数据写入失败!
				}
			}
		}else{
			echo "picerr";return;	//图片处理失败!
		}
	}

	/**
	 * 获取对象信息
	 * @return json 对象信息的json数据
	 */
	public function getData(){
		$id = $_GET['formid'];
		$media_id = I('get.media_id');
		$id = uiddecode($id);
		$mp_tag = M('kdgx_m_p_tag')->field('tag')
			->where(array('media_id'=>$media_id,'partner'=>$id))->find();
		$data = M('kdgx_partner')->field('id,name,school,introduce,expect,secret,photo_src,star,uid,contype,contact,
			sex,subject,grade,uploadtime,tag,is_export,times,media_id,allow,age,height')
		->where(array('id'=>$id,'is_delete'=>'0'))->find();
		$result = M('kdgx_partner_site')->where(array('media_id'=>$media_id))->find();
		if(!empty($mp_tag['tag'])){
			$data['tag'] = $mp_tag['tag'];
		}
		$secret = explode(',', $result['keyword']); 	// 暗号
		$data['secret'] = $secret['0'] . $data['secret'];
		$data['photo_src'] = 'http://'.C('CDN_SITE') ."/". $data['photo_src'];
		$res = M('kddx_user_openid')->field('nickname,headimgurl')->where(array('uid'=>$data['uid'],'public_id'=>C("PUBLIC_ID")))->find();
		$data['uid'] = encrypt_url($data['uid'],C('KEY_URL'));
		$data['nickname'] = $res['nickname'];
		$data['headimgurl'] = $res['headimgurl'];
		$data = urldecode(json_encode($data));
		print_r($data);
	}

	/**
	 * PC端通过条件获得对象信息
	 * @return json 对象信息
	 */
	public function getPartnerToPC(){
		$conditions = $_POST;
		$page = I('get.page',1,'intval');
		$public_id = $_GET['media_id'];
		$firstRow = ($page-1) * 20;
		switch($conditions['date']){
			case 0:
				$conditions['date'] = strtotime('-1 week');
				break;
			case 1:
				$conditions['date'] = strtotime('-30 day');
				break;
			case 2:
				$conditions['date'] = strtotime('-60 day');
				break;
			case 3:
				$conditions['date'] = 0;
				break;
		}
		if($conditions['media_id']=='all'){
			$schooldata = $this->ManageSchool(1);
			foreach ($schooldata as $k => $v) {
				$schooldata[$k] = $v['media_id'];
			}
		}else{
			$schooldata[] = $conditions['media_id'];
		}
		if(!empty($conditions)){
			$conditions['content'] = '%'.$conditions['content'].'%';
			$contentWhere = array(
			'_logic' => 'or',
			'name' => array('like',$conditions['content'],'OR'),
			'contact'=> array('like',$conditions['content'],'OR'),
			'introduce'=> array('like',$conditions['content'],'OR'),
			'expect' => array('like',$conditions['content'],'OR'),
			'secret' => array('like',$conditions['content'],'OR'),
			'school' => array('like',$conditions['content'],'OR'),
			);
		}else{
			$contentWhere  = '1';
		}
		$where = array(
			'media_id' => array('in',$schooldata),
			'uploadtime' => array('egt',$conditions['date']),
			'is_delete'	=>	'0',
			'_complex' => $contentWhere,
		);
		$data = M('kdgx_partner')->field('id,uploadtime,name,school,sex,tag,times,allow,is_allshow')
				->where($where)->order('uploadtime desc')
				->limit($firstRow,20)->select();
		$result = M('kdgx_partner_site')->where(array('media_id'=>$media_id))->find();
		$secret = explode(',', $result['keyword']); 	// 暗号
		foreach ($data as $k => $v) {
			$data[$k]['secret'] = $secret['0'] . $v['secret'];
			$mp_tad = M('kdgx_m_p_tag')->field('tag')
			->where(array('media_id'=>$public_id,'partner'=>$data[$k]['id']))->find();
			$data[$k]['id'] = uidencode($v['id']);
			if(!empty($mp_tad['tag'])){
				$data[$k]['tag'] = $mp_tad['tag'];
			}
		}
		$data = urldecode(json_encode($data));
		print_r($data);
	}
 
	/**
	 * 获取用户管理学校
	 */
	public function ManageSchool($flag=0){
		$media_id = $_GET['media_id'];
		if($media_id == 'gh_aee726515b29' || $media_id == 'gh_c75321282c18'){#vip(单身狗展览馆)
			$data = M('kdgx_wx_media_info')->field('name,media_id')->select();
		}else{			
			$data = M('kdgx_partner_site')->field('name,max(m.media_id) as media_id')->alias('p')
				->join('LEFT JOIN kdgx_wx_media_info m ON p.school_id = m.id')
				->where(array('p.media_id'=>$media_id,'permissions'=>'1'))
				->group('m.media_id')->select();
		}
		if(empty($data[0]['name'])){
			$data[0]['name'] = '请初始化应用';
		}
		if($flag){
			return $data; 
		}
		print_r(json_encode($data));
	}
	/**
	 * 管理者申请日志写入
	 * @return [type] [description]
	 */
	public function commitApply(){
		$apply = I('post.');
		$data  = array(
			'media_id'	=> $apply['media_id'],
			'school_media'=> $apply['school'],
			'reason'	=> $apply['reason'],
			'apply_time' => time()
			);
		if(M('kdgx_partner_apply_school')->where(array('media_id'=>$apply['media_id'],'school_media'=>$apply['school']))->find()){
			M('kdgx_partner_apply_school')->where(array('media_id'=>$apply['media_id'],'school_media'=>$apply['school']))
			->data(array('reason'=>$apply['reason'],'apply_time'=>time()))->save();
		}else{
			M('kdgx_partner_apply_school')->data($data)->add();
		}
	}

	// 获得申请列表
	public function getCommitApply(){
		$data = M('kdgx_partner_apply_school')->where(array('state'=>'0'))->select();
		foreach ($data as $key => $value) {		
			$data[$key]['apply_name'] = M('kdgx_wx_media_info')->where(array('media_id'=>$value['media_id']))->getField('name');
			$data[$key]['school_name'] = M('kdgx_wx_media_info')->where(array('media_id'=>$value['school_media']))->getField('name');
		}
		$this->assign('data',$data);
		$this->display('Partner/showapply');
	}
	// 修改申请状态
	public function updateCommitApply(){
		if(empty($_GET['apply_id'])||empty($_GET['state'])){
			$this->redirect('Partner/getCommitApply');
		}
		if($_GET['state']=='1'){
			$apply = M('kdgx_partner_apply_school')->field('media_id,school_media')->where(array('apply_id'=>$_GET['apply_id']))->find();
			$key = M('kdgx_partner_site')->where(array('media_id'=>$apply['media_id']))->getField('keyword');
			$school_id = M('kdgx_wx_media_info')->where(array('media_id'=>$apply['school_media']))->getField('id');
			if(!M('kdgx_partner_site')->data(array('media_id'=>$apply['media_id'],'school_id'=>$school_id,'keyword'=>$key))->add()){
				$this->redirect('Partner/getCommitApply');
			}
		}
		$sql = "update koudai.kdgx_partner_apply_school set state='".$_GET['state']."' where apply_id=".$_GET['apply_id'];
		M()->execute($sql);
		$this->redirect('Partner/getCommitApply');
	}



	// 导出并标记对象
	public function exportAndTag(){
		$this->changeTag();
		$this->exportPartner();
	}

	/**
	 * 标记对象记录
	 * @return [type] 标记成功的数据的数量
	 */
	public function changeTag(){
		$formids = $_POST;
		$media_id = $_GET['media_id'];
		if(empty($formids))return;
		foreach ($formids as $key => $value) {
			$value = json_decode($value,true);
			$formid = uiddecode($value['formid']);
			$mp_tad = M('kdgx_m_p_tag')->where(array('media_id'=>$media_id,'partner'=>$formid))->find();
			if($mp_tad){
				$result = M('kdgx_m_p_tag')->where(array('media_id'=>$media_id,'partner'=>$formid))
					->setField(array('tag'=>$value['tag']));
			}else{
				M('kdgx_m_p_tag')->data(array('media_id'=>$media_id,'partner'=>$formid,'tag'=>$value['tag']))->add();
			}
		}
	}

	/**
	 * 对象信息导出
	 * 
	 */
	public function exportPartner(){
		$formids = $_POST; // 导出对象id集
		$media_id = $_GET['media_id'];	// 公众号id
		$temp_id = I('get.temp_id',1,'intval');	// 模板编号
		if(empty($formids))return;
		foreach ($formids as $k => $v) {
			$v = json_decode($v,true);
			$where[] = uiddecode($v['formid']);
		}
		$partners = M('kdgx_partner')->field('sex,photo_src,secret,name,school,subject,grade,introduce,expect,star,height,age')
		->where(array('id'=>array('in',$where),'is_delete'=>'0'))->order('uploadtime desc')->select();
		$result = M('kdgx_partner_site')->where(array('media_id'=>$media_id))->find();
		$secret = explode(',', $result['keyword']); 	// 暗号
		$wechat = new \Org\Util\Wechat();
		foreach ($partners as $k => $v) {
			$partners[$k]['secret'] = $secret['0'] . $v['secret'];
			if (!empty($v['age']))
			$partners[$k]['age'] = date('y年n月',strtotime($v['age']));
			//$partners[$k]['wx_photo_src'] = $wechat->getMediaUrl("./".$partners[$k]['photo_src']);
			$partners[$k]['photo_src'] = 'http://'.C("CDN_SITE")."/".$partners[$k]['photo_src'];
			$partners[$k]['wx_photo_src'] = empty($partners[$k]['wx_photo_src']) ? $partners[$k]['photo_src'] : $partners[$k]['wx_photo_src']; 
		} 
		// -----------------------统计导出模板响应请求----------------------------
        $collect = new \Org\Util\DataCollect();
        $collect->CollectResponse('导出',$media_id,$media_id);
		$this->assign('partners',$partners); 

        switch ($temp_id) {
        	case '1':
				$this->display('Partner/export_1');
        		break;
          	case '2':
				$this->display('Partner/export_2');
        		break;        	
        	case '3':
				$this->display('Partner/export_3');
        		break;
        	case '4':
				$this->display('Partner/export_1_1');
        		break;         	
        	case '5':
				$this->display('Partner/export_2_2');
        		break;         	
        	case '6':
				$this->display('Partner/export_3_3');
        		break;        		
        }
	}
	/**
	 * 获取公众号的关键字
	 * @return string 公众号的关键字
	 */
	public function getKey(){
		$media_id = $_GET['media_id'];
		$result = M('kdgx_partner_site')->where(array('media_id'=>$media_id))->find();
		$secret = $result['keyword']; 	// 暗号
		$secrets = explode(',', $secret);
		echo $secret['0'].'#';
	}

	/**
	 * 获取公众号的名称或学校
	 * @param string 公众号public_id
	 * @return array 公众号的名称或学校
	 */
	public function getMediaName(){

		if(empty($_GET['media_id'])){
			$where = 1;
		}else{
			$where = array(
				'media_id' => $_GET['media_id'],
			);
		}
		$medias = M('kdgx_wx_media_info')->field('name,school_name,media_id')->where($where)->select();
		$data = array();
		$hash = array();
		$pockets = array(
			'school_name' => '口袋大学',
		);
		$i = 0;
		foreach ($medias as $key => $media) {
			$publics = array(
				'media_id'	=> $media['media_id'],
				'name'		=> $media['name'],
			);
			if($media['school_name']!='口袋大学'){
				if(array_key_exists($media['school_name'], $hash)){
					$j = $hash[$media['school_name']];
					$data[$j]['publics'][] = $publics;
				}else{
					$hash[$media['school_name']] = $i;
					$schools = array(
						'school_name' => $media['school_name'],
					);
					$schools['publics'][] = $publics;
					$data[$i]=$schools;
					$i++;
				}
			}else{
				$pockets['publics'][] = $publics;
			}
		}
		$data[] = $pockets;
		print_r(json_encode($data));
	}

	// 获取公众号自定义权限接口
	public function IsPrivate(){
		$this->getPrivate($_POST['media_id'],true);
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

	// 保存配置
	public function editConfig(){
		$config = I('post.');
		if(empty($config))return;
		if(!$this->getPrivate($config['media_id'])){
			echo json_encode(array('error'=>10023,'msg'=>'没有权限'));return;
		}
		$media_info = M('kdgx_wx_media_info')->field('id')->where(array('media_id'=>$config['media_id']))->find();
		if(empty($media_info['id'])){
			echo json_encode(array('error'=>10025,'msg'=>'请初始化应用'));return;
		}
		if(!empty($config['bg_img'])){			
			$upload = new \Org\Util\UploadBase64(array('rootPath'=>'D:\VirtualHost\Uploads/Uploads/bgImg/'));
			$info = $upload->upload($config['bg_img']);
			if(empty($info)){
				echo json_encode(array('error'=>10025,'msg'=>'图片上传错误'));return;
			}else{
				$info = substr($info, strlen("D:\VirtualHost\Uploads"));
				$url = "http://".C('CDN_SITE').$info;
				requestGet($url);
				$config['bg_img'] = $info;
			}
		}
		$sql = 'INSERT INTO kdgx_wx_partner_config (wx_media_info_id,share_title_man,share_title_girl,bg_img,bg_mask,copyright,button_mask) VALUES ('.
			$media_info['id'].',"'.$config['share_title_man'].'","'.$config['share_title_girl'].'","'.$config['bg_img'].'","'.$config['bg_mask'].'","'.
			$config['copyright'].'","'.$config['button_mask'].'") ON duplicate key UPDATE ';
		unset($config['media_id']);
		if(empty($config)){
			echo json_encode(array('error'=>10026,'msg'=>'参数错误'));return;
		}
		foreach ($config as $key => $value) {
			$sql.=$key.'="'.$value.'",';
		}
		$sql = rtrim($sql,',');
		if(!M()->execute($sql)){
			echo json_encode(array('error'=>10024,'msg'=>'保存配置错误'));return;
		}else{
			echo json_encode(array('error'=>0,'msg'=>'保存配置成功'));return;
		}
	}

	// 修改单身狗广场按钮文字接口
	public function updateDsgText(){
		$config = I('post.');
		if(empty($config['media_id']) || empty($config['dsg_text'])){
			echo json_encode(array(
				'error' => 10125,
				'msg' => '参数错误',
				));exit();
		}
		$media_info = M('kdgx_wx_media_info')->field('id')->where(array('media_id'=>$config['media_id']))->find();
		if(empty($media_info['id'])){
			echo json_encode(array('error'=>10028,'msg'=>'请初始化应用'));return;
		}
		$res = M('kdgx_wx_partner_config')->field('id')->where(array('wx_media_info_id'=>$media_info['id']))->find();
		if(empty($res['id'])){
			$result = M('kdgx_wx_partner_config')->data(array('id'=>$res['id'],'dsg_text'=>$config['dsg_text']))->add();
		}else{
			$result = M('kdgx_wx_partner_config')->where(array('id'=>$res['id']))->save(array('dsg_text'=>$config['dsg_text']));
		}
		if($result){
			echo json_encode(array('error'=>0,'msg'=>'保存配置成功'));return;
		}else{
			echo json_encode(array('error'=>10124,'msg'=>'保存配置错误'));return;
		}
	}

	// 开关会员计划接口
	public function updateVipPlan(){
		$config = $_POST;
		if(empty($config['media_id']) || !isset($config['openvip'])){
			echo json_encode(array('error'=>10127,'msg'=>'参数错误'));return;
		}
		if($wx_media_info = M('kdgx_wx_media_info')->where(array('media_id'=>$config['media_id']))->find()){
			$save = array('openvip'=>$config['openvip']);
			if($wx_media_info['vipopentime']==0 and $wx_media_info['openvip']==0){
				$save['vipopentime'] = time();
			}
			if(M('kdgx_wx_media_info')->where(array('media_id'=>$config['media_id']))->save($save)){
				echo json_encode(array('error'=>0,'msg'=>'修改成功'));return;
			}else{
				echo json_encode(array('error'=>10128,'msg'=>'修改失败'));return;
			}
		}else{
			echo json_encode(array('error'=>10128,'msg'=>'修改失败,请初始化应用'));return;
		}
	}

	// 开关单身狗广场接口
	public function updateDsgSquare(){
		$config = $_POST;
		if(empty($config['media_id']) || !isset($config['acsquare'])){
			echo json_encode(array('error'=>10126,'msg'=>'参数错误'));return;
		}
		$res = M('kdgx_wx_media_info')->where(array('media_id'=>$config['media_id']))->save(array('acsquare'=>$config['acsquare']));
		if($res){
			echo json_encode(array('error'=>0,'msg'=>'修改成功'));return;
		}else{
			echo json_encode(array('error'=>10125,'msg'=>'修改失败,请初始化应用'));return;
		}
	}

	// 获取会员计划/单身狗广场开启状态接口
	public function getDsgSquare(){
		$config = $_POST;
		if(empty($config['media_id'])){
			echo json_encode(array('error'=>10127,'msg'=>'参数错误'));return;
		}
		$media_info = M('kdgx_wx_media_info')->field('media_id,acsquare,openvip')->where(array('media_id'=>$config['media_id']))->find();
		echo json_encode($media_info);
	}


	// 读取配置
	public function getConfig(){
		$media_id = $_POST['media_id'];
		// if(!$this->getPrivate($media_id)){
		// 	echo json_encode(array('error'=>10023,'msg'=>'没有权限'));return;
		// }
		$media_info = M('kdgx_wx_media_info')->field('id')->where(array('media_id'=>$media_id))->find();
		$configs = M('kdgx_wx_partner_config')->where(array('wx_media_info_id'=>$media_info['id']))->find();
		if(empty($configs)){
			$configs = array(
				'share_title_man'=>'',
				'share_title_girl'=>'',
				'bg_img'=>'',
				'bg_mask'=>'',
				'copyright'=>'',
				'button_mask'=>'',
				'dsg_text'=>'',
				);
		}
		if(!empty($configs['bg_img'])){
			$configs['bg_img'] = 'http://'.C('CDN_SITE').$configs['bg_img'];
		}
		echo json_encode($configs);return;
	}

	// 提交反馈
	public function commitAdvice(){
		if(empty($_POST))return;
		if(M('kdgx_partner_commit')->data(array('commit_from'=>$_POST['media_id'],'commit'=>$_POST['commit']))->add()){
			echo json_encode(array('error'=>0,'msg'=>'提交成功'));return;
		}else{
			echo json_encode(array('error'=>10027,'msg'=>'提交失败'));return;
		}
	}


}

