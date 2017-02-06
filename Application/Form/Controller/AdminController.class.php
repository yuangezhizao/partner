<?php
/**
 * 运营者管理表单
 */
namespace Form\Controller;
use Think\Controller;
class AdminController extends Controller {

    Public function index()
    { 
        $this->display();
    }

/**----------------------------------------【获取公众号信息】---------------------------------------*/
    Public function mediaName()
    {
        if (isset($_SESSION['media_id'])) {
            $media_id = $_SESSION['media_id'];
            $_POST['media_id'] = $media_id;
            $mediaInfo = new \Form\Controller\MediaInfoController();
            $json = $mediaInfo->getInfo($media_id);
            $json = json_decode($json);
            echo json_encode(array('errcode' => 0,'errmsg' => $json->name));exit();
        } else {
            echo json_encode(array('errcode' => 1,'errmsg' => '公众号信息错误'));exit();
        }
    }
    
/**------------------------------------------【管理已定制的表单】----------------------------------------*/
    Public function forms()
    {
        if(isset($_SESSION['media_id'])){
            $media_id = $_SESSION['media_id'];
            $arr = M('kdgx_forms')->where("state='0'&& media_id='$media_id'")->order('date desc')->select();
            foreach ($arr as $k=>$v){
                $arr[$k]['date']=date("Y-m-d",$v['date']);
            }
            if($arr)
                echo json_encode(array('errcode'=>0,'errmsg'=>$arr));
            else
                echo json_encode(array('errcode'=>2,'errmsg'=>'数据为空'));exit();
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }

    }
    
/**------------------------------------------【表单的信息】----------------------------------------*/
    Public function forminfo()
    {
        if($_GET){
            $form_id = $_GET['form_id'];
            $media_id = $_SESSION['media_id'];
            $arr = M('kdgx_forms')->where("form_id='$form_id' &&state ='0' && media_id='$media_id'")->find();
            if($arr){
                echo json_encode(array('errcode'=>0,'errmsg'=>$arr));
            }else{
                echo json_encode(array('errcode'=>2,'errmsg'=>'表单已失效或不存在'));exit();
            }
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
        
    }

/**------------------------------------------【导出界面的数据】----------------------------------------*/
    Public function data()
    {
        if(isset($_GET)){
            $form_id = I('get.form_id');
            $table =  M('kdgx_forms_content');
            $arr = $table->where("form_id='$form_id'")->group('uid,timestamp')->select();
            foreach ($arr as $k=>$v){
                $sql = "SELECT id,aid,content,uid,timestamp FROM `kdgx_forms_content` WHERE   form_id='{$v['form_id']}' && uid='{$v['uid']}' && timestamp='{$v['timestamp']}'  ";
                $data = M()->query($sql);
                foreach ($data as $k1=>$v1){
                    $re = M('kdgx_forms_control')->where("aid='{$v1['aid']}'")->find();
                    $array['title']['text'.$v1['aid']]=$re['name'];
                    $array['data'][$k]['uid']=$v1['uid'];
                    $array['data'][$k]['timestamp']=$v1['timestamp'];
                    $array['data'][$k]['date']=date("Y-m-d",$v1['timestamp']);
                    $array['data'][$k]['list']['text'.$v1['aid']]=$v1['content'];
                }
            }
             if($array){
                echo json_encode(array('errcode'=>0,'errmsg'=>$array));exit();
            }else{
                echo json_encode(array('errcode'=>2,'errmsg'=>'数据为空'));exit();
            } 
        } else {
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
    }




/**------------------------------------------【添加字段】----------------------------------------*/
    Public function add_control()
    {     
        if($_POST['arr']){
            foreach ($_POST['arr'] as $k=>$v)
            {
                $re = M('kdgx_forms_control')->data($v)->add();
                if ($re) {
                    $id = M('kdgx_forms_control')->getLastInsID();
                    $array['aid_list'][$k] = [
                        'aid' => $id,
                        'cid' => $v['cid'],
                        'type' => $v['type'],
                        'name' => $v['name'],
                        'is_need' => $v['is_need'],
                        'describe'=>$v['describe'],
                    ];
                } else {
                    echo json_encode(array('errcode'=>3,'errmsg'=>'表单生成失败'));exit();
                }
            }
            echo json_encode(array('errcode'=>0,'errmsg'=>$array));exit();
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
         
    }
    
    
    
/**------------------------------------------【图片上传】----------------------------------------*/
    Public function upload()
    {    
        if(DEV){//测试服务器
            $files = $_POST ['image'];
            $upload = new \Org\Util\UploadBase64 ();
            $upload->rootPath  =  './Public/Uploads/';//保存根路径
            $info = $upload->upload ( $files );
            $info = substr ( $info, 1 );
            if ($info) {
                echo json_encode(array('errcode'=>0,'errmsg'=>$info));exit();
            }else{
                echo json_encode(array('errcode'=>6,'errmsg'=>'图片上传失败'));exit();
            }

        }else{//真实服务器
            $files = $_POST ['image'];
            $upload = new \Org\Util\UploadBase64(array('rootPath'=>'D:\VirtualHost\Uploads/Form/img/'));
            $info = $upload->upload($files);
            $info = substr($info, strlen('D:\VirtualHost\Uploads'));
            if ($info){
                echo json_encode(array('errcode'=>0,'errmsg'=>$info));exit();
            }else{
                echo json_encode(array('errcode'=>6,'errmsg'=>'图片上传失败'));exit();
            }
        }

    }

/**--------------------------------------【删除用户提交的数据】---------------------------------- */
    Public function detele_data()
    {
        if($_POST){
            M()->startTrans();//开启事务
            foreach ($_POST['data'] as $v){
                $timestamp=$v['timestamp'];
                $uid = $v['uid'];
                $re = M('kdgx_forms_content')->where("uid='$uid' && timestamp='$timestamp'")->delete();
                if(!$re){
                    M()->rollback();//事务回滚
                    echo json_encode(array('errcode'=>3,'errmsg'=>'删除失败'));exit();
                }
            }
            M()->commit();//提交事务
            echo json_encode(array('errcode'=>0,'errmsg'=>'OK'));exit();            
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
    }

/**-----------------------------------------【修改表单】--------------------------------------- */
    
    Public function update()
    {
        
        if($_POST){
            $form_id = $_POST['form_id'];
            $arr = M('kdgx_forms')->where("form_id='$form_id'")->find();
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
        
    }
   
/* -----------------------------------------【表单生成】--------------------------------------- */
    Public function create()
    {        
        M()->startTrans();//开启事务
        if($_POST){
            if(!isset($_SESSION['media_id'])){
                    echo json_encode(array('errcode'=>1,'errmsg'=>'公众号信息错误'));exit();
            }
            $media_id = $_SESSION['media_id'];
            $_POST['media_id']=$media_id;
            $mediaInfo = new \Form\Controller\MediaInfoController();
            $json = $mediaInfo->getInfo($media_id);
            $json = json_decode($json);
            $_POST['media_name']=$json->name;
            $_POST['date'] = time();
            $str='';
            foreach ($_POST['aid_arr'] as $v){
                $str .=$v['aid'].'-';
            }
            $str = substr($str, 0, -1);
            $_POST['aid_list']=$str;
            $re = M('kdgx_forms')->add($_POST);
            if($re){
                $form_id = M()->getLastInsID();
                $_POST['form_id'] = $form_id;
                $html = html($_POST);
                $url = '/Form/url/'.time().mt_rand(0001, 9999).'.html';
                $re = file_put_contents('D:\VirtualHost\Uploads'.$url, $html, FILE_APPEND);
                if($re){
                    $re = M('kdgx_forms')->where("form_id='$form_id'")->save(array('form_url'=>$url));
                    if($re){
                        M()->commit();
                    echo json_encode(array('errcode'=>0,'errmsg'=>$form_id,));exit();
                    }else{
                        M()->rollback();
                        echo json_encode(array('errcode'=>10010,'errmsg'=>'数据库修改失败'));exit();
                    }
                }else{
                    M()->rollback();
                    echo json_encode(array('errcode'=>9,'errmsg'=>'生成文件失败'));exit();
                }
            } else {
                    M()->rollback();
                    echo json_encode(array('errcode'=>3,'errmsg'=>'数据库写入失败'));exit();
             }
                $_POST['date'] = time();
                $re = M('kdgx_forms')->add($_POST);
                if ($re) {
                    M()->commit();
                    $form_id = M('kdgx_forms')->getLastInsID();
                    echo json_encode(array('errcode'=>0,'errmsg'=>$form_id,));exit();
                } else {
                    M()->rollback();
                    echo json_encode(array('errcode'=>3,'errmsg'=>'数据库写入失败'));exit();
                }
        } else {
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
    }

/**--------------------------------------------【删除表单】----------------------------------------- */
    Public function delete()
    {
        if($_GET['form_id']){
            $form_id = $_GET['form_id'];
            $re = M('kdgx_forms')->where("form_id='$form_id'")->save(array('state' => '1'));
            if ($re) {
                echo json_encode(array('errcode'=>0,'errmsg'=>'删除成功'));exit();
            } else {
                echo json_encode(array('errcode'=>3,'errmsg'=>'数据库操作失败'));exit();
            }
        } else {
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
    }
    

    
    
}





