<?php
namespace Bangdan\Controller;
use Common\Controller\BaseController;
class LoginController extends BaseController{
	public function index(){
		//审核界面
		if($_GET['ss']){
				$uid=$_SESSION['uid'];
				$uid=	decrypt_url ( $uid, 'pocketuniversity' );
				$rs=M("kdgx_operator")->where("uid = '$uid'")->find();
				if($rs){
					if($rs['state']=='1'){
						$this->display("Index/index");
					}elseif($rs['state']=="0"){
						$this->display("Login/check");
					}
				}else{
					$id=$_SESSION['uid'];
				header("Location:/index.php/bangdan/Login/login?uid=$id");exit();
				}
		} else{
		 	$from = '/index.php/bangdan/Login/index?ss=1&b=1';
			$from = urlencode($from);
			header("Location:/index.php/home/user/login?flag=1&from=".$from);exit;
		}
	}
	//注册界面
	public function login(){

		$this->display();
	}
	//提交注册信息
	public function reg(){
		$uid=$_SESSION['uid'];
		$uid=	decrypt_url ( $uid, 'pocketuniversity' );
		$_POST['uid']=$uid;
		$_POST['regtime']=time(); 
		$_POST['ispocket']='1';
		$_POST['public_list']="0-".$_POST['public_list'];
		if($_POST){
			$re=M('kdgx_operator')->data($_POST)->add();
			if($re){
				echo "1";
			}else{
				echo '0';
			}
		}
	}
	function check(){
		$this->display();
	}
	
	public function  accounts(){
		$arr=M("kddx_public")->field("id,public_id,public_name,public_num,school")->select();
		$this->ajaxReturn($arr);
	}
	/**
	 * 头像
	 */
	Public function header(){
		$headimgurl=$_SESSION['header_img'];
		$nickname=$_SESSION['nick'];
		$data['headimgurl']=$headimgurl;
		$data['nickname']=$nickname;
		$this->ajaxReturn($data);
	}
	
}

