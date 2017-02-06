<?php
namespace Home\Controller;
use Common\Controller\BaseController;

/**
 * 用户管理
 */

class UserController extends BaseController{

	private $initial = array('a','b','c','d','e','f','g','h','j','k','l','m','n',
		'o','p','q','r','s','t','u','v','w','x','y','z');

	/**
	 * 用户信息页
	 * @return [type] [description]
	 */
	public function user(){
		$uid = I('get.uid');
		$this->assign('uid',$uid);
		$this->display('user/user');
	}

	/**
	 * 拉取微信用户信息
	 * @return [type] [description]
	 */
	private function getWechatinfo($flag,$school,$from,$user_type,$type){
		$cfg = array(
			'flag' 		=> $flag,
			'school' 	=> urlencode($school),
			'from' 		=> $from,
			'user_type' => $user_type,
		);
		$wechat = new \Org\Util\Wechat($cfg);
		$user_info = $wechat->authorize($type);
		return $user_info;
	}




	/**
	 * 用户从kddx_user_openid迁移到kddx_user中
	 * @return 成功返回uid,失败返回false
	 */
	private function moveDataToUser($data){
		$cz = M('kddx_user')->where(array('uid'=>$data['uid']))->find();
		if($cz){
			$result = M('kddx_user')->where(array('uid'=>$data['uid']))
			->setField(array('wxunionid'=>$data['unionid'],'regtime'=>time()));
			if($result){
				$data = M('kddx_user')->where(array('uid'=>$data['uid']))->find();
				return $data;
			}else{
				return $data;
			}
		}else{
			$result = M('kddx_user')
			->data(array('wxunionid'=>$data['unionid'],'regtime'=>time(),'uid'=>$data['uid']))->add();
			if($result){
				$data = M('kddx_user')->where(array('uid'=>$data['uid']))->find();
				return $data;
			}else{
				return $data;
			}
		}
	}

	/**
	 * 获得学校名称
	 * @param  string $public_id 公众号的标识public_id
	 */
	public function getSchool($public_id){
		$result = M('kdgx_wx_media_info')->field('school_name')->where(array('media_id'=>$public_id))->find();
		if(!$result){
			$result = M('kdgx_media_info')->field('school_name')->where(array('media_id'=>$public_id))->find();
			if(!$result){			
				$result = M('kddx_public')->field('school')->where(array('public_id'=>$public_id))->find();
				echo $result['school'];exit();
			}
		}
		if($result['school_name']=='口袋大学'){
			echo '';exit();
		}
		echo $result['school_name'];exit();
	}

	// 维护user_openid
	private function intoOpenid($data = array()){
		$where = array(
			'public_id'=>$data['public_id']|'gh_3347961cee42',
			'openid'=>$data['openid'],
			'uid'=>$data['uid'],
		);
		$result = M('kddx_user_openid')->where($where)->find();
		if(!$result){
			M('kddx_user_openid')->data($where)->add();
		}
	}

	private function intoUserInfo($data){
		$where = array(
			'nick' => $data['nickname'],
			'header_img' => $data['headimgurl'],
		);
		$result = M('kddx_user_info')->where(array('uid'=>$data['uid']))->find();
		if(!empty($result['uid'])){
			M('kddx_user_info')->where(array('uid'=>$data['uid']))->data($where)->save();
		}else{
			$where['uid'] = $data['uid'];
			M('kddx_user_info')->data($where)->add();
		}
	}

	/**
	 * 判断用户是否注册
	 * @return 用户uid或false
	 */
	private function checkreg($user_info){
		$data['type']=1;
		try{
			if(empty($user_info['openid']))exit('网络繁忙,请稍后重试');
			$resCache = M('kddx_user_cache')->where(array('openid'=>$user_info['openid']))->find();
			if(!empty($resCache)){
				$resCache['type']=0;
				return $resCache;
			}elseif($user_info['scope']=='snsapi_base'){
				return $data;
			}
			$resO = M('kddx_user_openid')->field('uid,subscribe,language,openid')->where(array('openid'=>$user_info['openid']))->find();
			if(empty($resO)||empty($resO['language'])){
				if($user_info['scope']=='snsapi_base'){
					return $data;
				}
				if(empty($resO)||empty($resO['language'])){
					$data = array(
						'openid'=>$user_info['openid'],
						'nickname'=>$user_info['nickname'],
						'sex'=>$user_info['sex'],
						'language'=>$user_info['language'],
						'city'=>$user_info['city'],
						'province'=>$user_info['province'],
						'country'=>$user_info['country'],
						'headimgurl'=>$user_info['headimgurl'],
						'unionid'=>$user_info['unionid'],
						'public_id'=>C('PUBLIC_ID'),
						);
					if(empty($resO)){
						M('kddx_user_openid')->add($data,array(),true);
					}else{
						M('kddx_user_openid')->where(array('openid'=>$user_info['openid']))->data($data)->save();
					}
					$resO = M('kddx_user_openid')->where(array('openid'=>$user_info['openid']))->find();
				}
			}
			if(empty($resO['uid'])){
				$resU = M('kddx_user')->field('uid')->where(array('wxunionid'=>$user_info['unionid']))->find();
				$uid = $resU['uid'];
				if(empty($resU['uid'])){				
					$uid = M('kddx_user')->add(array('wxunionid'=>$user_info['unionid']),array(),true);
					M('kddx_user_info')->add(array('uid'=>$uid),array(),true);
				}
				$ret = M('kddx_user_openid')->where(array('openid'=>$user_info['openid']))->save(array('uid'=>$uid));
				$data1 = M()->query("SELECT uid,phone from kddx_user where uid=".$uid);
				$data2 = M()->query("SELECT school from kddx_user_info where uid=".$uid);
				$data1['0']['school'] = $data2['0']['school'];
			}else{
				$data1 = M()->query("SELECT uid,phone from kddx_user where uid=".$resO['uid']);
				$data2 = M()->query("SELECT school from kddx_user_info where uid=".$resO['uid']);
				$data1['0']['school'] = $data2['0']['school'];
			}
			$data = $data1['0'];
			$data['type']=0;
			$data['subscribe'] = $resO['subscribe'];
			$data['openid'] = $resO['openid'];
			// 用户信息进入缓存库
			M('kddx_user_cache')->add($data,array(),true);
			return $data;
		}catch(Exception $e){
			file_put_contents('./Logs/user.log',date('Y-m-d H:i:s')."Home->User->checkreg:".sprintf($e->getMessage));
			exit('系统错误,请稍后重试!');
		}
	}
	
	// 初始化消息回复类应用接口
	public function Initialize(){
		if(!empty($_GET['public_id']) && !empty($_GET['openid']))		
		session('getInfo',serialize((Object)array('public_id'=>$_GET['public_id'],'openid'=>$_GET['openid'])));
		cookie('commitList',$_GET['commit']);
		$this->login();
	}
	
	// 用户身份验证,静默授权
	public function login(){
	
		$state = I('get.state');	//public_id
		$media_id = I('get.school');		//学校信息
		$user_type = I('get.user_type');//用户模块类型
		$flag = I('get.flag',0);			//是否进行二级验证
		$from = I('get.from');			//页面来源用于分发
		// $from = urldecode($from);
		// $from = str_replace('&amp;','&',$from);
		$user_info = session('loginInfo');
		$user_info = unserialize($user_info);

		//$checkdata = session('checkdata');
		if(!empty($user_info) && !empty($checkdata)){
			$checkdata['flag'] = $flag;
			$checkdata['from'] = $from;
			$checkdata['user_type'] = $user_type;
			session('header_img',$user_info['headimgurl']);		
			session('nick',$user_info['nickname']);
			session('unionid',$user_info['unionid']);
			cookie('media_id',$media_id);
			$checkdata = $this->checkreg($user_info);
			$this->directUid($checkdata);
		}
		$cfg = array(
			'flag' 		=> $flag,
			'school' 	=> urlencode($media_id),
			'from' 		=> $from,
			'user_type' => $user_type,
		);
		$wechat = new \Org\Util\Wechat($cfg);
		$wechat->authorize();
	}
	// 静默授权回调地址
	public function authlogin(){
		$state = I('get.state');	//public_id
		$media_id = I('get.school');		//学校信息
		$user_type = I('get.user_type');//用户模块类型
		$flag = I('get.flag',0);			//是否进行二级验证
		$from = I('get.from');			//页面来源用于分发
		// $from = urldecode($from);
		// $from = str_replace('&amp;','&',$from);
		$cfg = array(
			'flag' 		=> $flag,
			'school' 	=> urlencode($media_id),
			'from' 		=> $from,
			'user_type' => $user_type,
		);
		$wechat = new \Org\Util\Wechat($cfg);
		if($state=='snsapi_base'){
			$user_info = $wechat->get_access_token();
		}else{
			$user_info = $wechat->get_user_info();
			session('loginInfo',serialize($user_info));
			$user_info = json_decode($user_info,true);
		}
		// 用户身份验证逻辑
		$checkdata = $this->checkreg($user_info);
		if($checkdata['type']==1 && $state!='snsapi_userinfo'){
			$this->scopelogin();
		}else{			
			$checkdata['flag'] = $flag;
			$checkdata['from'] = $from;
			$checkdata['user_type'] = $user_type;
			session('checkdata',$checkdata);
			session('header_img',$user_info['headimgurl']);		
			session('nick',$user_info['nickname']);
			session('unionid',$user_info['unionid']);
			cookie('media_id',$media_id);
			$this->directUid($checkdata);
		}
	}
	// 非静默授权
	private function scopelogin(){
		$state = I('get.state');	//public_id
		$media_id = I('get.school');		//学校信息
		$user_type = I('get.user_type');//用户模块类型
		$flag = I('get.flag',0);			//是否进行二级验证
		$from = I('get.from');			//页面来源用于分发
		// $from = urldecode($from);
		// $from = str_replace('&amp;','&',$from);
		$cfg = array(
			'flag' 		=> $flag,
			'school' 	=> urlencode($media_id),
			'from' 		=> $from,
			'user_type' => $user_type,
		);
		$wechat = new \Org\Util\Wechat($cfg);
		$wechat->authorize_v();
	}
	// 用户分发
	private function directUid($user_data){
		$uid = $user_data['uid'];
		$user_data['from'] = urldecode($user_data['from']);
		$user_data['from'] = str_replace(';','gh&',$user_data['from']);
		$user_data['from'] = str_replace('//','gh&',$user_data['from']);
		// 补全from_media信息
		$from_media =  M('kddx_user_openid')->where(array('uid'=>$uid,'public_id'=>C('PUBLIC_ID')))->getField('from_media');
		if(empty($from_media)){
			M('kddx_user_openid')->where(array('uid'=>$uid,'public_id'=>C('PUBLIC_ID')))->save(array('from_media'=>cookie('media_id')));
		}

		// 非H5应用账号绑定
		$getInfo = session('getInfo');
		if(!empty($getInfo)){
			$getInfo = unserialize(session('getInfo'));
			session('getInfo',null);
			$getres = M('kddx_user_openid')->field('uid,openid')->where(array('openid'=>$getInfo->openid,'public_id'=>$getInfo->public_id))->find();
			if(empty($getres['uid']) && empty($getres['openid'])){
				M('kddx_user_openid')->data(array('openid'=>$getInfo->openid,'public_id'=>$getInfo->public_id,'uid'=>$uid))->add();
			}elseif(empty($getres['uid'])){
				M('kddx_user_openid')->where(array('openid'=>$getInfo->openid,'public_id'=>$getInfo->public_id))->save(array('uid'=>$uid));
			}
			$user_data['from'] = "/index.php/home/index/closePage?uid=$uid";
		}
		cookie('from',$user_data['from']);
		$uid = encrypt_url($uid,C("KEY_URL"));
		cookie('uid',$uid);
		session('uid',$uid);
		session('agree',md5('agree'));
		session('school',$user_data['school']);
		switch ($user_data['flag']) {
			case 1:#用户需要二级验证
				cookie('flag',$user_data['flag']);
				if(!empty($user_data['school'])){#用户学校为空
					if(!empty($user_data['phone'])){
						// if($user_data['school']=='杭州电子科技大学' && $user_data['subscribe']==0){
						// 	$id = M('kddx_action')->data(array('action'=>$user_data['from'],'time'=>time()))->add();
						// 	$this->assign('id',$id);
						// 	$this->display('User/authlogin');exit();
						// }
						session('phone',$user_data['phone']);
						redirect($user_data['from']);
					}else{#用户进行二级验证
						session('ok',md5('ok'));
						redirect("/index.php/home/login/info?user_type=".$user_data['user_type']);
					}
				}else{#用户进行一级验证
					redirect("/index.php/home/login/index?&user_type=".$user_data['user_type']);
				}
				break;
			case 3:#用户不需要验证
				redirect($user_data['from']);
				break;
			default:#用户不需要二级验证
				cookie('flag',0);
				if(!empty($user_data['school'])){#用户注册过
					// if($user_data['school']=='杭州电子科技大学' && $user_data['subscribe']==0){
					// 	$id = M('kddx_action')->data(array('action'=>$user_data['from'],'time'=>time()))->add();
					// 	$this->assign('id',$id);
					// 	$this->display('User/authlogin');exit();
					// }
					redirect($user_data['from']);
				}else{#用户进行一级验证
					redirect("/index.php/home/login/index?&user_type=".$user_data['user_type']);
				}
				break;
		}
	}

	private function plat_authorizer($uid,$from){
		$public_id = cookie('media_id');
		$from = urlencode('http://'.C('WEB_SITE').$from);
		$url = "http://www.koudaidaxue.com/index.php/base/WetchatApi/h5Authorize?public_id=$public_id&uid=$uid&from=$from";
		redirect($url);
	}

	//获取数据库用户信息
	public function getUserInfo(){
		$uid = $_POST['uid'];
		if(!empty($uid)){
			$uid = decrypt_url($uid,C('KEY_URL'));
		}
		$user_info = M('kddx_user_info')->field('i.uid,name,sex,birth_type,birth_y,birth_m,birth_d,school,college,subject,grade,living_area,apartment,room,qq,weixin,weibo,stuid,phone,email,text2')
		->alias('i')->join('LEFT JOIN kddx_user u ON i.uid=u.uid')->where(array('i.uid'=>$uid))->find();
		$user_data = M('kddx_user_openid')->field('nickname,headimgurl')->where(array('uid'=>$uid))->find();
		if(!empty($user_info)){
			$user_info['nick'] = empty($user_info['nick'])? $user_data['nickname']:$user_info['nick'];
			$user_info['header_img'] = empty($user_info['header_img']) ?$user_data['headimgurl']:$user_info['header_img'];
			$user_info['uid'] = encrypt_url($user_info['uid'],C('KEY_URL'));
		}
		$user_info = urldecode(json_encode($user_info));
		print_r($user_info);
	}

	// 用户信息入库
	public function editUserInfo(){	
		$user_info = I('post.');
		if(empty($user_info['uid'])){
			echo json_encode(array(
				'error'	=> 1,
				'msg'	=> '失败!',
				));exit();
		}
		$a = ['兰州理工','廊坊师范','南昌理工','沈阳理工','太原师范','唐山师范','牡丹江师范'];
		$b = array(
						'兰州理工' => '兰州理工大学',
						'廊坊师范' => '廊坊师范学院',
						'南昌理工' => '南昌理工学院',
						'沈阳理工' => '沈阳理工大学',
						'太原师范' => '太原师范学院',
						'唐山师范' => '唐山师范学院',
						'牡丹江师范' =>'牡丹江师范学院');
		$str = $user_info['school'];
		if(in_array($user_info['school'], $a)){
			$str = $b[$user_info['school']];
		}
		$user_info['uid'] = decrypt_url($user_info['uid'],C('KEY_URL'));
		$p = '/^[\x{4e00}-\x{9fa5}]+[大学|学院]/u';
    preg_match($p, $str, $matches, PREG_OFFSET_CAPTURE);
		switch ($user_info['type']) {
			case '1':
				$data['school'] = $user_info['school'];
				$data['uid'] = $user_info['uid'];
				$data['new_school'] = $matches['0']['0'];
				try{				
					$data = M('kddx_user_info')->create($data);
					$find = M('kddx_user_info')->where(array('uid'=>$user_info['uid']))->find();
					if(empty($find['uid'])){
						M('kddx_user_info')->data($data)->add();
					}else{
						M('kddx_user_info')->where(array('uid'=>$user_info['uid']))
						->data($data)->save();
					}
					M('kddx_user')->where(array('uid'=>$user_info['uid']))
						->data(array('regtime'=>time()))->save();
					// 用户信息进入缓存库
					$cache = M('kddx_user_cache')->where(array('uid'=>$data['uid']))->find();
					if(!empty($cache)){
						M('kddx_user_cache')->data(array('school'=>$user_info['school']))
						->where(array('uid'=>$user_info['uid']))->save();
					}
					  
						echo 1;
					}catch(Exception $e){
						echo 0;
					}
				break;
			case '2':
				$user = array(
					'phone'		=>	$user_info['phone'],
					'email'		=>	$user_info['email'],
					'uid'		=>	$user_info['uid'],
					'password'	=> md5($user_info['phone']),
					'logintime'	=> time(),
				);
				$user = M('kddx_user')->create($user);
				if(M('kddx_user')->where(array('uid'=>$user['uid']))->find()){	
					$result1 = M('kddx_user')->data($user)->save();	
				}else{
					$result1 = M('kddx_user')->data($user)->add();
				}
				if($result1){
					session('ok',null);	//删除二级验证许可标记
					// 用户信息进入缓存库
					$cache = M('kddx_user_cache')->where(array('uid'=>$user['uid']))->find();
					if(!empty($cache)){
						M('kddx_user_cache')->data(array('phone'=>$user['phone']))
						->where(array('uid'=>$user['uid']))->save();
						session('phone',$user['phone']);
					}
				}else{
					echo json_encode(array(
						'error'	=> 1,
						'msg'	=> '失败!',
						));exit();
				}
				$user1 = array(
					'uid'		=>	$user_info['uid'],
					'nick'		=>	$user_info['nick'],
					'sex'		=>	$user_info['sex'],
					'birth_type'=> 	$user_info['birth_type'],
					'birth_y'	=> 	$user_info['birth_y'],
					'birth_m'	=> 	$user_info['birth_m'],
					'birth_d'	=> 	$user_info['birth_d'],
					'college'	=>	$user_info['college'],
					'subject'	=>	$user_info['subject'],
					'grade'		=>	$user_info['grade'],
					'living_area'=>	$user_info['living_area'],
					'apartment'	=>	$user_info['apartment'],
					'room'		=>	$user_info['room'],
					'qq'		=>	$user_info['qq'],
					'weixin'	=>	$user_info['weixin'],
					'weibo'		=>	$user_info['weibo'],
					'stuid'		=>	$user_info['stuid'],
					'text2'		=>	$user_info['text2'],
					'header_img'=>	$user_info['header_img'],
				);
				$user1 = M('kddx_user_info')->create($user1);
				if(M('kddx_user_info')->where(array('uid'=>$user1['uid']))->find()){
					$result2 = M('kddx_user_info')->data($user1)->save();
				}else{
					$result2 = M('kddx_user_info')->data($user1)->add();
				}
				session('ok');
				echo json_encode(array(
					'error'=> 0,
					'msg' => '成功'
					));
				break;
			default :
				echo json_encode(array(
					'error'	=> 1,
					'msg'	=> '参数错误',
					));exit();	
		}
	}

	// 更新用户信息
	public function updateUserInfo(){
		$user_info = $_POST;
		if(empty($user_info['uid'])){
			echo json_encode(array(
				'error'	=> 1011,
				'msg'	=> '参数错误'
				));exit();
		}
		$user_info['uid'] = decrypt_url($user_info['uid'],C('KEY_URL'));
		if(!empty($user_info['phone'])){
			if(!M('kddx_user')->save(array('uid'=>$user_info,'phone'=>$user_info['phone']))){
				echo json_encode(array(
					'error' => 1012,
					'msg'	=> '电话修改失败'
					));exit();
			}
			if(!M('kddx_user_cache')->save(array('uid'=>$user_info,'phone'=>$user_info['phone']))){
				echo json_encode(array(
					'error' => 1012,
					'msg'	=> '电话修改失败'
					));exit();				
			}
		}else{
			unset($user_info['phone']);
		}
		foreach ($user_info as $key => $value) {
			if(empty($value)){
				unset($user_info[$key]);
			}
		}
		if(!M('kddx_user_info')->save($user_info)){
				echo json_encode(array(
					'error' => 1013,
					'msg'	=> '信息修改失败'
					));exit();			
		}
		if(empty($user_info['school']) || !M('kddx_user_cache')->save(array('uid'=>$user_info,'school'=>$user_info['school']))){
			echo json_encode(array(
				'error' => 1013,
				'msg'	=> '信息修改失败'
				));exit();				
		}	
	}
	/**
	 * 获取省份列表
	 */
	public function getProvince(){
		$provinces = M('kdgx_province')->select();
		print_r(json_encode($provinces));
	}

	//获取城市信息
	public function getCity(){
		if(empty($_GET['province_id'])){		
			$data = array();
			$city_data = array();
			foreach ($this->initial as $v) {
				$data['initial'] = strtoupper($v);
				$city = M('kdgx_city')->where("spell_short like '".$v."%'")->select();
				foreach ($city as $key => $value) {
					$province = M('kdgx_province')->field('province_name')->find($value['province_id']);
					$city[$key]['province'] = urlencode($province['province_name']);
					$city[$key]['city'] = urlencode($city[$key]['city']);
					$data['cities'] = $city;
				}
				$city_data[] = $data;
			}
			$city_data = urldecode(json_encode($city_data));
			print_r($city_data);
		}else{
			$citys = M('kdgx_city')->where(array('province_id'=>$_GET['province_id']))->select();
			print_r(json_encode($citys));
		}
	}

	//获取站点信息
	public function getSite($city_id){
		$data = array();
		$area = M('kdgx_area')->where(array('city_id'=>$city_id))->select();
		foreach ($area as $key => $v) {
			$area1['id'] = $v['id'];
			$area1['name'] = urlencode($v['name']);
			$cityname = M('kdgx_city')->where(array('city_id'=>$city_id))->getField('city');
			$site = M('kdgx_site')->where(array('area_id'=>$v['id']))->select();
			$shname = $cityname.'社会人士';
			array_unshift($site, array('site_id'=>'x','name'=>$shname,'area_id'=>'xx'));
			foreach ($site as $key => $value) {
				$site[$key]['name'] = urlencode($value['name']);
			}
			$area1['sites'] = $site;
			$data[] = $area1;
		}
		$data = urldecode(json_encode($data));
		print_r($data);
	}

	/**
	 * @param string $site_id 区域id
	 * 获取宿舍信息
	 */
	public function getDormentries($site_id){
		$hash = array();
		$data = array();
		$i = 0;
		$dormentries = M('kdgx_dormentries')->where(array('site_id'=>$site_id))->select();
		foreach ($dormentries as $key => $dormentry) {
			$temp = array(
				'dormentry_id' => $dormentry['dormentry_id'],
				'name'		   => $dormentry['name'],
				);
			if(array_key_exists($dormentry['group_name'], $hash)){
				$data[$hash[$dormentry['group_name']]]['dormentries'][] = $temp;
			}else{
				$data[$i]['group_name'] = $dormentry['group_name'];
				$data[$i]['dormentries'][] = $temp;
				$hash[$dormentry['group_name']]=$i;
				$i++;
			}
		}
		print_r(json_encode($data));
	}

	/**
	 * 学校信息补全提交
	 *
	 */
	public function editAddress(){
		$apply = I('post.');
		$log = '--------------------学校信息补全提交------------'."\r\n";
		$log .= '---------提交时间:' . date('Y-m-d H:m:s') . "\r\n";
		$log .= '---------提交province_id:' . $apply['province_id'] . "\r\n";
		$log .= '---------提交city_id:' . $apply['city_id'] . "\r\n";
		$log .= '---------区域:' . $apply['address'] . "\r\n";
		$log .= '---------提交的学校:' . $apply['school_name'] . "\r\n";
		$log .= '------------------------------------------------------------' . "\r\n";
		file_put_contents('./Logs/schoolApplay.log',$log,FILE_APPEND);
	}
}
