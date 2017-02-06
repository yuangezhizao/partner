<?php 
namespace Common\Controller;
use Think\Controller;
/**
 * 控制器的基类
 */
class BaseController extends Controller{

	//初始化方法
	public function __construct(){
		parent::__construct();
		C('DEFAULT_THEME','m');
		if(MODULE_NAME=='Home' && CONTROLLER_NAME=='Index' && ACTION_NAME=='index'){	
			if (ismobile()) {
	            C('DEFAULT_THEME','m');
	        }else{
	        	C('DEFAULT_THEME','c');
	        }
		}
	}
}