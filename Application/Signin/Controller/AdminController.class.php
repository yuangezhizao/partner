<?php
namespace Signin\Controller;

use Think\Controller;

class AdminController extends Controller
{

/**----------------------------------【导出周排行】---------------------------------------*/
    Public function weekData()
    {
        if(!isset($_SESSION['media_id'])){
            echo json_encode(array('errcode'=>3,'errmsg'=>'公众号信息为空'));exit();
        }
        $media_id = $_SESSION['media_id'];
        switch (date('w')) {
            case 1:
                $start = strtotime('-0 week Monday');
                $end = strtotime('+1 week Monday');
                break;
            case 0:
                $start = strtotime('-1 week Monday');
                $end = strtotime('+0 week Monday');
                break;
            default:
                $start = strtotime('-1 week Monday');
                $end = strtotime(' +0 week Monday');
                break;
        }
        $i = $_GET['select'];
        switch ($i){
            case 2:
                $limit='LIMIT 0,10';
                break;
            case 3:
                $limit ='LIMIT 0,15';
                break;
            case 4:
                $limit ='LIMIT 0,20';
                break;
            default:
                break;
        }
        $sql="SELECT *FROM `kdgx_signin`  AS  d INNER JOIN `kdgx_signin_user` AS u ON u.`uid`=d.`uid`
                    WHERE d.`media_id`='$media_id' && d.`timestamp`>='$start' && d.`timestamp` <='$end'
                    GROUP BY d.`uid` ORDER BY u.`score` DESC,d.`timestamp` ASC $limit
            ";
        $arr = M()->query($sql);
        if (! $arr) {
            echo json_encode(array('errcode'=>2,'errmsg'=>'数据为空'));exit();
        } else {
            foreach ($arr as $k => $v) {
                $data[$k]['ranking'] = $k + 1;
                $re = M('kddx_user_openid')->field('nickname,headimgurl')
                    ->where("uid='{$v['uid']}' && public_id='gh_3347961cee42'")
                    ->find();
                $data[$k]['nickname'] = $re['nickname'];
                $data[$k]['headimgurl'] = $re['headimgurl'];
                $count = M('kdgx_signin')->where("uid='{$v['uid']}' && timestamp>='$start' && timestamp <='$end' ")->count();
                $data[$k]['weekcount'] = $count;
                $data[$k]['score'] = $v['score'];
            }
            echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
        }
    }


/**----------------------------------【日排行】---------------------------------------*/
    Public function dayData(){
        
        if(!isset($_SESSION['media_id'])){
            echo json_encode(array('errcode'=>3,'errmsg'=>'公众号信息为空'));exit();
        }else{
            $media_id = $_SESSION['media_id'];
            $sql = "SELECT *FROM `kdgx_signin`  AS  d INNER JOIN `kdgx_signin_user` AS u ON u.uid=d.uid   WHERE d.media_id='$media_id'  ORDER BY d.timestamp DESC";
            $arr = M()->query($sql);
            if($arr){
                foreach ($arr as $k=>$v){
                    $data[$k]['date']=$v['timestamp'];
                    $re = M('kddx_user_openid')->field('nickname')->where("uid='{$v['uid']}'&& public_id='gh_3347961cee42'")->find();
                    $data[$k]['nickname'] = $re['nickname'];
                }
                echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
            }else{
                echo json_encode(array('errcode'=>2,'errmsg'=>'数据为空'));exit();
            }
        }
    }

/**----------------------------------【公众号信息】---------------------------------------*/
    Public function getMedia()
    {
        if(isset($_SESSION['media_id'])){
            $media_id = $_SESSION['media_id'];
            $mediaInfo = new \Signin\Controller\MediaInfoController();
            $json = $mediaInfo->getInfo($media_id);
            $json = json_decode($json);
            $media_name = $json->name;
            $media_id = $json->media_id;
            $data=[
                'media_name'=>  $media_name,
                'media_id'  =>  $media_id,
            ];
            echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
    }
    
/**--------------------------------------【打卡记录】-----------------------------------------*/
    Public function data()
    {
        if(!isset($_SESSION['media_id'])){
            echo json_encode(array('errcode'=>3,'errmsg'=>'公众号信息为空'));exit();
        }
        $media_id = $_SESSION['media_id'];
        if($_POST){
            $where = "WHERE media_id='$media_id'";

            if(isset($_POST['begin'])){
                    $begin = mktime(0,0,0,date('m',$_POST['begin']),date('d',$_POST['begin']),date('Y',$_POST['begin']));
                $where .= "&& timestamp>='$begin'";
            }
            if(isset($_POST['end'])){
                if($_POST['end']=='946656000')
                    $end = mktime(23,59,59,date('m'),date('d'),date('Y'));
                else
                    $end = mktime(23,59,59,date('m',$_POST['end']),date('d',$_POST['end']),date('Y',$_POST['end']));
                $where .= "&& timestamp<'$end'";
            }
            $p = $_POST['page']*20;
            $sql = "SELECT id,uid,timestamp FROM `kdgx_signin` $where ORDER BY `timestamp`DESC LIMIT $p,20";
            $arr = M()->query($sql);
            if(!$arr){
                echo json_encode(array('errcode'=>3,'errmsg'=>'数据为空'));exit();
            }
            foreach($arr as $k=>$v){
                $re = M('kdgx_signin_user')->field('score')->where("uid='{$v['uid']}'")->find();
                $data[$k]['score'] = $re['score'];
                $data[$k]['id'] = $v['id'];
                $data[$k]['uid'] = $v['uid'];
                $data[$k]['timestamp'] = $v['timestamp'];
                $re = M('kddx_user_openid')->field('nickname,headimgurl')->where("uid='{$v['uid']}'&& public_id='gh_3347961cee42'")->find();
                $data[$k]['nickname'] = $re['nickname'];
                $data[$k]['headimgurl'] = $re['headimgurl'];
            }
            echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
    }




/**--------------------------------------【打卡排行】------------------------------------*/
    Public function rankings()
    {
        if(!isset($_SESSION['media_id'])){
            echo json_encode(array('errcode'=>3,'errmsg'=>'公众号信息为空'));exit();
        }
        $media_id = $_SESSION['media_id'];
        if($_POST){
            $where = "WHERE s.media_id='$media_id'";
            if(isset($_POST['begin'])){
                $begin = mktime(0,0,0,date('m',$_POST['begin']),date('d',$_POST['begin']),date('Y',$_POST['begin']));
                $where .= "&& timestamp>='$begin'";
            }
            if(isset($_POST['end'])){
                if($_POST['end']=='946656000')
                    $end = mktime(23,59,59,date('m'),date('d'),date('Y'));
                else
                    $end = mktime(23,59,59,date('m',$_POST['end']),date('d',$_POST['end']),date('Y',$_POST['end']));
                $where .= "&& timestamp<'$end'";
            }
            $i = isset($_POST['page'])?$_POST['page']:20;
            $sql = "SELECT u.`uid`,u.`score` FROM `kdgx_signin` AS s INNER JOIN `kdgx_signin_user` AS u  ON s.uid=u.uid $where GROUP BY u.`uid` ORDER BY u.`score`DESC  LIMIT 0,$i";

            $arr = M()->query($sql);
            if(!$arr){
                echo json_encode(array('errcode'=>3,'errmsg'=>'数据为空'));exit();
            }
            foreach($arr as $k=>$v){
                $count = M('kdgx_signin')->where("uid='{$v['uid']}'")->count();
                $re = M('kddx_user_openid')->field('nickname,headimgurl')->where("uid='{$v['uid']}'&& public_id='gh_3347961cee42'")->find();
                $data[$k] = array(
                    'ranking' => $k+1,
                    'uid' => $v['uid'],
                    'nickname' => $re['nickname'],
                    'headimgurl' => $re['headimgurl'],
                    'count' => $count,
                    'score' => $v['score'],
                );
            }
            echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
    }

/**--------------------------------------【打卡排行】------------------------------------*/
    Public function ranking()
    {
        if(!isset($_SESSION['media_id'])){
            echo json_encode(array('errcode'=>3,'errmsg'=>'公众号信息为空'));exit();
        }
        $media_id = $_SESSION['media_id'];
        //$_POST=array('begin'=>1483203661,'end'=>time());
        if($_POST){
            $where = "WHERE media_id='$media_id'";
            if(isset($_POST['begin'])){
                $begin = mktime(0,0,0,date('m',$_POST['begin']),date('d',$_POST['begin']),date('Y',$_POST['begin']));
                $where .= "&& timestamp>='$begin'";
            }
            if(isset($_POST['end'])){
                if($_POST['end']=='946656000')
                    $end = mktime(23,59,59,date('m'),date('d'),date('Y'));
                else
                    $end = mktime(23,59,59,date('m',$_POST['end']),date('d',$_POST['end']),date('Y',$_POST['end']));
                $where .= "&& timestamp<'$end'";
            }
            $i = isset($_POST['page'])?$_POST['page']:20;

            $sql = "SELECT `uid` FROM `kdgx_signin` $where GROUP BY `uid` ";
            $array = M()->query($sql);
            if(!$array){
                echo json_encode(array('errcode'=>3,'errmsg'=>'数据为空'));exit();
            }
            foreach($array AS $key=>$value){
                $uid = $value['uid'];
                $re = M('kddx_user_openid')->field('uid,nickname,headimgurl')->where("uid='$uid' && public_id='gh_3347961cee42'")->find();
                $data[$key] = array(
                    'uid' => $re['uid'],
                    'nickname' => $re['nickname'],
                    'headimgurl' => $re['headimgurl'],
                    'count' => 0,
                    'score' => 0,
                );
                $sql = "SELECT `dayscore` FROM `kdgx_signin` $where && `uid`='$uid' ";
                $arr = M()->query($sql);
                foreach($arr AS $v){
                    $data[$key]['count'] += 1;
                    $data[$key]['score'] += $v['dayscore'];
                }
            }
            $data = bubbleSort($data,'score','desc');
            foreach($data as $k=>$v){
                $data[$k]['ranking'] = $k+1;
            }
            $data = array_slice($data, 0, $i); 
            echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
    }
    
    /**
     * 是否打广告
     */
    Public function ad()
    {
        if(!isset($_SESSION['media_id'])){
            echo json_encode(array('errcode'=>3,'errmsg'=>'公众号信息为空'));exit();
        }
        $media_id = $_SESSION['media_id'];
        $arr = M('kdgx_signin_media')->field('ad')->where("media_id='$media_id'")->find();
        if($arr['ad']==1){
            echo json_encode(array('errcode'=>0,'errmsg'=>'已接广告'));exit();
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'未接广告'));exit();
        }
    }

    /**
     * 修改状态
     */
    Public function update()
    {
        if(!isset($_SESSION['media_id'])){
            echo json_encode(array('errcode'=>3,'errmsg'=>'公众号信息为空'));exit();
        }
        $media_id = $_SESSION['media_id'];
        $arr = M('kdgx_signin_media')->field('ad')->where("media_id='$media_id'")->find();
        if($arr['ad']=='1'){
            $re = M('kdgx_signin_media')->where("media_id='$media_id'")->save(array('ad'=>0));
            if($re){
                echo json_encode(array('errcode'=>0,'errmsg'=>'关闭成功！'));exit();
            }else{
                echo json_encode(array('errcode'=>1,'errmsg'=>'开启失败，请稍后重试！'));exit();
            }
        }elseif($arr['ad']=='0'){
            $re = M('kdgx_signin_media')->where("media_id='$media_id'")->save(array('ad'=>1));
            if($re){
                echo json_encode(array('errcode'=>0,'errmsg'=>'开启成功！'));exit();
            }else{
                echo json_encode(array('errcode'=>1,'errmsg'=>'开启失败，请稍后重试！'));exit();
            }
        }
    }
    
}

