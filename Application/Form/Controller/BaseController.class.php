<?php
namespace Form\Controller;
use Think\Controller;
class BaseController extends Controller {


    Public function __construct()
    {
        if( ! isset($_SESSION['uid']))
        {
	       	$from = "/index.php/Form/index/index?form_id=".$_GET['form_id'];
			$from = urlencode ( $from );
			$to = "Location:/index.php/Home/user/login?flag=1&from=" . $from;
			header ( $to );exit();
        }        
    }



}






