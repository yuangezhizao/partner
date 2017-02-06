<?php
namespace Bangdan\Controller;
use Common\Controller\BaseController;
/** 
 *榜单管理员授权界面及跳转
 */
class AbaseController extends BaseController {
	function _initialize(){
		if (empty ( $_SESSION ['uid'] )) {
			$from = "/index.php/bangdan/message";
			$from = urlencode($from);
			$to = "Location:/index.php/home/user/login?flag=1&from=".$from;
			header($to);exit;
		}
		//判断是否管理员
		$uid=$_SESSION['uid'];
		$uid=	decrypt_url ( $uid, 'pocketuniversity' );
		$re=M("kdgx_operator")->where("ispocket=0 && uid=$uid")->find();
		if($re){
			$this->redirect("Login/check");
		}
	}



		
	
		
	
}