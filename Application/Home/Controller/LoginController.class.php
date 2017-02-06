<?php 
namespace Home\Controller;
use Common\Controller\HomeBaseController;
/**
 * 登录管理控制器
 */
class LoginController extends HomeBaseController{

	public function __construct(){
		parent::__construct();

	}

	//一级验证补全学校信息
	public function index(){
		$uid = decrypt_url(cookie('uid'),C("KEY_URL"));
		if(empty($uid)){
			exit("<p style='color:red;font-size:26px;'>非法访问1!</p>");
		}
		if(M('kddx_user_cache')->where(array('uid'=>decrypt_url(session('uid'),C('KEY_URL'))))->getField('school')){
			exit("<p style='color:red;font-size:26px;'>非法访问2</p>");
		}
		$this->display('login/index');
	}

	//二级验证补全手机信息
	public function info(){
		$ok = session('ok');
		if($ok == md5('ok')){
			$this->display('login/info');
		}else{
			exit("<p style='color:red;font-size:26px;'>非法访问3</p>");
		}
	}

	//登录
	public function login(){
		$this->display('login/login');
	}
	//生成验证码
	public function showVerify(){
	    	$config = array(
	    		'codeSet'=>'1234567890',
	    		'useCurve'=>false,
	    		'imageH'=>60,
	    		'imageW'=>240,
	    		'length'=>4,
	    		'fontttf'=>'4.ttf',
	    	);
	    	$verify = new \Think\Verify($config);
	    	$verify->entry();
	}

	// 检测验证码
	function check_verify(){
		$code = I('get.code');
	    $verify=new \Think\Verify();
	    if($verify->check($code)){
	    	echo 1;
	    }else{
	    	echo 0;
	    }
	}

	/**
 	* 判断手机号
	*/
	private function if_phone($phone){
		$row = M('kddx_user')->where(array('phone'=>$phone))->find();
		if($row){
			if(empty($row['wxunionid'])){
				return array(
					'code' => 2,  //'code' => 2,
					'data' => $row,
				);
			}
	        return array(
	        	'code'	=> 1,
	        	'data' 	=>	null,
	        );
		}else{
		    return array(
		    	'code'	=>	0,
		    	'data'	=>	null,
		    );
		}
	}

	// 发送手机验证码
	private function sendCode($phone){
		$number = rand(100000,999999);
//cookie('test',$number);	// 测试服务器专有,正式上线注释掉
		$username="duyuan";
		$password="24f9c146c2ab1a2ffbea82b1c2d27e07";
		$time=time();
		$mobileids=$phone.$time;
		$content="【口袋高校】你本次操作的验证码为:".$number."，不要告诉别人哦，若非本人操作，请忽略。";
		$content = charsetToGBK($content);
		$url="http://api.sms.cn/mt/?uid=$username&pwd=$password&mobile=$phone&mobileids=$mobileids&content=$content";
		$data= file_get_contents($url);
		$data= explode("&",$data);
		$resault=explode("=",$data[1]);
//$resault[1] = '100';//测试服务器专有,正式上线注释掉
		if($resault[1]=='100'){
			session('phoneVerify',md5($phone.$number));
			echo json_encode(array(
				'error'	=> 0,
				'msg'	=> '成功',
				));exit();
		}else{
			echo json_encode(array(
				'error'	=> 1046,
				'手机验证码发送失败',
				));exit();
		}
		return;
	}

	//获取手机验证码
	public function showPhone(){
		$phone = $_POST['phone_number'];
		if(empty($phone)){
			echo json_encode(array(
				'error'	=> 1047,
				'msg' => '参数错误',
				));return;
		}
		$ifPhone = $this->if_phone($phone);
		switch ($ifPhone['code']) {
			case '0':
				$this->sendCode($phone);
			case '1':#电话号码已经存在
				echo json_encode(array(
					'error'	=> '-1',
					'msg' => '手机号码已经存在',
					));
				return;
			case '2':#信息需要merge
				session('merge',$ifPhone['data']['uid']);
				echo json_encode(array(
					'error' => 2,
					'msg' => '信息需要融合'.session('merge'),
					));
				return;
			default:
				break;
		}
	}

	// 获取用户信息融合手机验证码
	public function showMerge(){
		$phone = $_POST['phone_number'];
		$uid = session('merge');
		if(empty($phone) || empty($uid)){
			echo json_encode(array(
				'error'	=> '1051',
				'msg' => '恶意请求',
				));return;
		}
		$this->sendCode($phone);
	}

	// 用户信息融合验证
	public function checkMerge(){
		$phonecode = $_POST['phonecode'];
		$phone = $_POST['phone_number'];
		$secode = session('phoneVerify');
		if(md5($phone.$phonecode)==$secode){
			$uid = session('merge');
			if(empty($uid)){
				echo json_encode(array(
					'error'	=> '1051',
					'msg' => '恶意请求',
					));exit();
			}else{
				$newuid = decrypt_url(cookie('uid'),C("KEY_URL"));
				$arrOpenid = M('kddx_user_openid')->field('openid')->where(array('uid'=>$newuid,'public_id'=>C("PUBLIC_ID")))->find();
				if(!empty($arrOpenid['openid'])){
					if(M('kddx_user_openid')->where(array('openid'=>$arrOpenid['openid'],'public_id'=>C("PUBLIC_ID")))
					->save(array('uid'=>$uid))){
						// 清空缓存重新登录
						M('kddx_user_cache')->where(array('uid'=>$newuid))->delete();
						M('kddx_user')->where(array('uid'=>$newuid))->delete();
						M('kddx_user_info')->where(array('uid'=>$newuid))->delete();
						session(null);
						cookie(null);
						echo json_encode(array(
							'error'  => '0',
							'msg' => '成功',
						));return;
					}
				}
				echo json_encode(array(
					'error' => 1055,
					'msg' => '服务器繁忙,请稍后重试',
					));exit();
			}
		}else{
			echo json_encode(array(
				'error'	=> 10010,
				'msg'	=> '验证码错误!',
				));exit;
		}
	}


	//验证手机验证码
	public function checkPhone(){
		$phonecode = $_POST['phonecode'];
		$phone = $_POST['phone_number'];
		$secode = session('phoneVerify');
		if(md5($phone.$phonecode)==$secode){
			echo json_encode(array(
				'error' => 0,
				'msg'	=> '成功',
				));exit;
		}else{
			echo json_encode(array(
				'error'	=> 10010,
				'msg'	=> '验证码错误!',
				));exit;
		}
	}
}