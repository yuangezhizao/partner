<?php
namespace Signin\Controller;
use Think\Controller;
class BaseController extends Controller{
    

    Public function __construct(){
        parent::__construct();
         if(isset($_GET['media_id']) && $_GET['media_id']!=''){
            $_SESSION['media_id']=$_GET['media_id'];
         }
        if (! isset($_SESSION['uid']) || ! isset($_SESSION['school'])) {
            $from = $_SERVER['REQUEST_URI'];
            $from = urlencode($from);
            $to = 'Location:http://'.C('WEB_SITE').'/index.php/home/user/login?flag=0&from='.$from.'&school='.$_GET['media_id'];
            header($to);
            exit();
        }
    }
    
}
