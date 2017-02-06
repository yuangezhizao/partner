<?php 
namespace Common\Controller;
use Common\Controller\BaseController;
/**
 * Home模块的基类
 */
class HomeBaseController extends BaseController{

	//初始化方法
	public function __construct(){
		parent::__construct();
		if(ismobile()){	
			//$agree = session('agree');
			//if(empty($agree) || $agree != md5('agree')){
			//	redirect("/index.php/home/user/login");
			//}
		}
	}
}