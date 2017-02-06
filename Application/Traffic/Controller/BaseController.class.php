<?php 
namespace Traffic\Controller;
use \Think\Controller;

// 基础控制器
class BaseController extends Controller{

	public function __construct(){
		parent::__construct();
		$uid = session('uid');
		if(empty($uid)){
			redirect('/index.php/Home/User/login?flag=3&from='.urlencode(__SELF__));
		}
	}
}