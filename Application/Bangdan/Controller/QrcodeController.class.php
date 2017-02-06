<?php
namespace Bangdan\Controller;
use Common\Controller\BaseController;
class QrcodeController extends BaseController{
	Public function index(){
		if(empty($_GET['media_id']))exit('链接失效!');
		$_SESSION['media_id'] = $_GET['media_id'];
		$this->redirect("Index/index");
		exit();
	}
}