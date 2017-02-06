<?php
/*
 * 分享接口
 */
namespace Signin\Controller;
use Think\Controller;
class ShareController extends Controller{


/**-----------------------------------【分享成就卡界面】----------------------------------------------*/
    Public function index()
    {
        if(!isset($_GET['key'])){
            echo returnError('分享链接错误!');exit();
        }
        $id = decrypt_url($_GET['key'],'pocketuniversity');
        $re = M('kdgx_signin')->field('uid,media_id')->where("id='$id'")->find();
        if(!$re){
            echo returnError('分享链接错误!');exit();
        }
        if (! isset($_SESSION['uid'])) {
            $from = '/index.php/Signin/share/index?key='.$_GET['key'];
            $from = urlencode($from);
            $to = 'Location:http://'.C('WEB_SITE').'/index.php/home/user/login?flag=0&from=' .$from;
            header($to);
            exit();
        }
        $fuid = decrypt_url($_SESSION['uid'],'pocketuniversity');
        behaviorRecord($fuid, 23, $re['media_id']);//行为记录
        addfriends($re['uid'], $fuid);//把两者互相加为好友
        $this->display();
    }


/**-----------------------------------【分享成就卡接口】----------------------------------------------*/
    Public function share()
    {
        if($_GET['key']){
            $key = $_GET['key'];
            $id = decrypt_url($key,'pocketuniversity');
            $arr = M('kdgx_signin')->where("id='$id'")->find();
            if(!$arr){
                echo json_encode(array('errcode'=>2,'errmsg'=>'数据错误或为空'));exit();
            }
            $re = M('kddx_user_openid')->field('uid,nickname,headimgurl')->where("uid='{$arr['uid']}' && public_id='gh_3347961cee42'")->find();
            $data['nickname'] = $re['nickname'];
            $data['headimgurl'] = $re['headimgurl'];
            $data['count'] = $arr['count'];
            $data['media_id'] = $arr['media_id'];
            $data['timestamp'] = $arr['timestamp'];
            $start = mktime(0, 0, 0, date('m', $arr['timestamp']), date('d', $arr['timestamp']), date('Y', $arr['timestamp']));
            $end = mktime(23, 59, 59, date('m', $arr['timestamp']), date('d', $arr['timestamp']), date('Y', $arr['timestamp']));
            $count = M('kdgx_signin')->where("  timestamp>='$start' && timestamp <'{$arr['timestamp']}'")->count();
            $data['ranking'] = $count+1;
            echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
    }




/**-----------------------------------【好友分享页面】----------------------------------------------*/
    Public function friends()
    {
        /*-----验证GET参数-----*/
        if(!isset($_GET['uid'])){
            echo returnError('分享链接错误!');exit();
        }
        $uid = $_GET['uid'];
        $re = M('kdgx_signin_user')->field('uid')->where("uid='$uid'")->find();
        if(!$re){
            echo returnError('分享链接错误!');exit();
        }


        if (! isset($_SESSION['uid'])) {
            $from = '/index.php/Signin/share/friends?uid='.$_GET['uid'];
            $from = urlencode($from);
            $to = 'Location:http://'.C('WEB_SITE').'/index.php/home/user/login?flag=0&from=' . $from;
            header($to);
            exit();
        }
        $fuid = decrypt_url($_SESSION['uid'],'pocketuniversity');
        addfriends($uid, $fuid);//互加好友
        $this->display();
    }


/**-----------------------------------【好友分享接口】----------------------------------------------*/
    Public function friendShare()
    {
        /*-----验证GET参数----*/
        if(!isset($_POST['uid'])){
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
        $uid = $_POST['uid'];
        $re = M('kdgx_signin_user')->field('uid')->where("uid='$uid'")->find();
        if(!$re){
            echo json_encode(array('errcode'=>3,'errmsg'=>'参数错误'));exit();
        }
        $re = M('kddx_user_openid')->field('headimgurl')->where("uid='$uid' && public_id='gh_3347961cee42'")->find();
        $data['headimgurl'] = $re['headimgurl'];
        $count = M('kdgx_signin')->where("uid='$uid'")->count();
        $arr = M('kdgx_friend_info')->field('fuid')->where("uid='$uid'")->select();
        foreach($arr as $k=>$v){
            $re = M('kddx_user_openid')->field('uid,nickname,headimgurl')->where("uid='{$v['fuid']}' && public_id='gh_3347961cee42'")->find();
            $data['data'][$k] = array('uid'=>$re['uid'],'nickname'=>$re['nickname'],'headimgurl'=>$re['headimgurl']);
        }
        $data['count'] = $count;
        $data['num'] = count($arr);
        echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
    }





/**-----------------------------------【学校榜分享界面】----------------------------------------------*/
    Public function school()
    {
        if(!isset($_GET['uid'])){
            echo returnError('分享链接错误!');exit();
        }
        $uid = $_GET['uid'];
        $re = M('kdgx_signin_user')->field('uid')->where("uid='$uid'")->find();
        if(!$re){
            echo returnError('分享链接错误!');exit();
        }
        if ( !isset($_SESSION['uid'])) {
            $from = '/index.php/Signin/share/school?uid='.$_GET['uid'];
            $from = urlencode($from);
            $to = 'Location:http://'.C('WEB_SITE').'/index.php/home/user/login?flag=0&from='.$from;
            header($to);
            exit();
        }
        $fuid = decrypt_url($_SESSION['uid'],'pocketuniversity');
        addfriends($uid, $fuid);//互加好友
        $this->display();
    }







/**-----------------------------------【学校榜分享接口】----------------------------------------------*/
    Public function schoolShare()
    {   

        if(!isset($_POST['uid'])){
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
        $uid = $_POST['uid'];

        $sql = "SELECT i.`new_school`,s.`score`,s.`count` FROM `kddx_user_info` AS i INNER JOIN `kdgx_signin_school` AS s ON s.`school_name`=i.`new_school` 
                WHERE uid='$uid'  LIMIT 0,1 ";
        $arr = M()->query($sql);
        $rs = $arr[0];
        $score = $arr[0]['score'];
        
        $count = M('kdgx_signin_school')->where("score>'$score'")->count();
        $data['ranking'] = $count+1;
        $sql = "SELECT s.`uid` FROM `kdgx_signin_user` AS s INNER JOIN `kddx_user_info` AS i ON i.`uid`=s.`uid` WHERE i.`new_school`='{$rs['new_school']}'";
        $arr = M()->query($sql);
        $data['number'] = count($arr);
        $sql = "SELECT o.`nickname`,o.`headimgurl`,s.`school_name`,s.`score`,s.`count` FROM `kddx_user_info` AS i INNER JOIN `kddx_user_openid` AS o ON o.`uid`=i.`uid` INNER JOIN `kdgx_signin_school` AS s ON s.`school_name`=i.`new_school` WHERE i.`uid`='$uid' && o.`public_id`='gh_3347961cee42'";
        $user = M()->query($sql);
        $data['nickname'] = $user[0]['nickname'];
        $data['headimgurl'] = $user[0]['headimgurl'];
        $data['score'] = $user[0]['score'];
        $data['count'] = $user[0]['count'];
        $data['school'] = $user[0]['school_name'];
        //学校里六个用户
        $sql = "SELECT s.`uid`,o.`nickname`,o.`headimgurl` FROM `kdgx_signin_user` AS s INNER JOIN `kddx_user_info` AS i ON i.`uid`=s.`uid` INNER JOIN `kddx_user_openid` AS o ON o.uid=i.uid   WHERE i.`new_school`='{$rs['new_school']}'&& o.public_id='gh_3347961cee42' GROUP BY s.`uid` ORDER BY s.id DESC LIMIT 0,6";
        $arr = M()->query($sql);
        $data['user'] = $arr;
        echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();

    }



















}