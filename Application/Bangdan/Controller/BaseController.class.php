<?php
namespace Bangdan\Controller;
use \Common\Controller;
class BaseController extends \Common\Controller\BaseController {
	/** 
	 * 榜单运营者授权界面
	 * 没有注册过自动跳转注册界面
	 */  
	public function _initialize(){
	//		$_SESSION['uid']='BWxSZQViUTUJbA%3D%3D';
 	   	 	if (empty ( $_SESSION ['uid'] ) || empty( $_SESSION['phone'])) {
				$from = "/index.php/bangdan/index";
				$from = urlencode($from);
				$to = "Location:/index.php/home/user/login?flag=1&from=".$from;
				header($to);exit;
			}
			
	}
}
