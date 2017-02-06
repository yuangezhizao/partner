<?php
namespace Form\Controller;
use Form\Controller\BaseController;
class IndexController extends BaseController {
    
    

/*----------------------------------------【表单链接地址】---------------------------------------*/
	  Public function index()
      {
	      if($_GET['form_id']){
	          $form_id = $_GET['form_id'];
	          $re = M('kdgx_forms')->where("form_id='$form_id' && state='0'")->find();
	          if($re) {
	              require 'D:\VirtualHost\Uploads'.$re['form_url'];exit();
	          }else {
	              echo returnError('表单不存在或已失效!');exit();
	          }
	      }else{
	              echo returnError('表单不存在或已失效!');exit();
	      }
	  }


/*----------------------------------------【生成表单】---------------------------------------*/
	  Public function form()
      {
	      if(isset($_POST['form_id'])) {
	          $form_id = $_POST['form_id'];
	          $arr = M('kdgx_forms')->where("form_id='$form_id'")->find();
                  if($arr) {
	              $listArr = explode('-',$arr['aid_list']);
	              foreach ($listArr as $k=>$v){
	                  $re = M('kdgx_forms_control')->where("aid='{$v}'")->find();
	                  $arr['ziduan'][$k] = $re;
	              }
	              echo json_encode(array('errcode'=>0, 'errmsg'=>$arr));exit();
	          } else {
	              echo json_encode(array('errcode'=>2, 'errmsg'=>'数据为空'));exit();
	          }
	      } else {
	          echo json_encode(array('errcode'=>1, 'errmsg'=>'参数为空')); 
	      }
	  }



/*----------------------------------------【提交表单】---------------------------------------*/
	  Public function add()
	  {
	      $timestamp = time();
        if ($_POST['data']) {
	      foreach ($_POST['data'] as $k=>$v)
            {
                 $uid = $_SESSION['uid'];
                $uid = decrypt_url($uid,'pocketuniversity');
                $data=array('aid'=>$k,'content'=>$v,'uid'=>$uid,'form_id'=>$_POST['form_id'],'timestamp'=>$timestamp);
                $re = M('kdgx_forms_content')->add($data);
                if(!$re){
                    M()->rollback();//时间回滚
                    echo json_encode(array('errcode'=>6,'errmsg'=>'数据写入失败'));exit();
                } 
            }
            M()->commit();
            echo json_encode(array('errcode'=>0,'errmsg'=>'OK'));exit();
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }

    }
	

   
   
   
   
   

   
   
   
   
   
   
   
   
   
   
}