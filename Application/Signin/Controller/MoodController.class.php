<?php 
namespace Signin\Controller;
use Signin\Controller\BaseController;

// 心情日记
class MoodController extends BaseController {
  
  private $adminUid = ['10001','10002','98047','94302','87715','91031'];

  // 进入心情日记路由
  public function index(){
    if(empty($_SESSION['school']) || empty($_SESSION['uid'])){
      $from = urlencode($_SERVER['REQUEST_URI']); // 获得当前路由
      redirect("/index.php/home/user/login?flag=1&from=$from");
    }
    $this->display();
  }

  // 进入心情日记举报路由
  public function report(){
    if(empty($_SESSION['school']) || empty($_SESSION['uid'])){
      $from = urlencode($_SERVER['REQUEST_URI']); // 获得当前路由
      redirect("/index.php/home/user/login?flag=1&from=$from");
    }
    $this->display();
  }

  // 添加心情
  public function addMood(){
    $uid = decrypt_url(session('uid'),C('KEY_URL'));
    $time = strtotime(date('Y-m-d'));
    if(M('kdgx_signin_mood')->where("uid='".$uid."' and create_time>".$time)->count()>2){
      echo json_encode(array(
        'error' => 40030,
        'msg' => '今日发表心情超过上限'
        ));exit();
    }
    if(!empty($_POST['content_img'])){
      $upload = new \Org\Util\UploadBase64(array('rootPath'=>'D:\VirtualHost\Uploads/Signin/moodImg/'));
      $info = $upload->upload($_POST['content_img']);
      if(empty($info)){
        echo json_encode(array('error'=>40025,'msg'=>'图片上传错误'));return;
      }else{
        $info = substr($info, strlen("D:\VirtualHost\Uploads"));
        $url = "http://".C('CDN_SITE').$info;
        requestGet($url);
        $_POST['content_img'] = $info;
      }      
    }
    $_POST['uid'] = decrypt_url(session('uid'),C('KEY_URL'));
    $_POST['media_id'] = cookie('media_id');
    $_POST['create_time'] = time();
    $_POST['content'] = emoji_encode($_POST['content']);
    if(M('kdgx_signin_mood')->data($_POST)->add()){
      $count = M('kdgx_signin_mood')->where(array('uid'=>$_POST['uid'],'create_time'=>array('gt',strtotime(date('Y-m-d')))))->count();
      if($count == 1){
        M('kdgx_signin_user')->where(array('uid'=>$_POST['uid']))->setInc('score',10);
        echo json_encode(array(
          'error' => 0,
          'msg' => 1
          ));exit();
      }
      echo json_encode(array(
        'error' => 0,
        'msg' => '成功'
        ));exit();
    }else{
      echo json_encode(array(
        'error' => 40026,
        'msg' => '系统繁忙,心情发布失败'
        ));exit();
    }
  }

  // 修改心情状态
  public function updateMood(){
    if(empty($_POST['id'])){
      echo json_encode(array(
        'error' => 40026,
        'msg' => '参数错误'
        ));exit();
    }
    $uid = decrypt_url(session('uid'),C('KEY_URL'));
    $muid = M('kdgx_signin_mood')->where(array('id'=>$_POST['id']))->getField('uid');
    if(M('kdgx_signin_mood')->save($_POST)){
      if(isset($_POST['is_delete'])){
        if(!in_array($uid, $this->adminUid) && $muid != $uid){
          echo json_encode(array(
            'error' => 40027,
            'msg' => '没有权限'
            ));exit();
        }
        $count = M('kdgx_signin_mood_thumb')->where(array('mood_id'=>$_POST['id']))->count();
        M('kdgx_signin_user')
        ->where(array('uid'=>M('kdgx_signin_mood')->where(array('id'=>$_POST['id']))->getField('uid')))
        ->setDec('score',$count);
      }
      echo json_encode(array(
        'error' => 0,
        'msg' => '成功'
        ));exit();
    }else{
      echo json_encode(array(
        'error' => 40027,
        'msg' => '系统繁忙,修改失败'
        ));exit();
    }
  }

  // 近7天的心情记录和打卡记录
  public function getDaysMood(){
    $arr = getWeek();
    $end_time = $arr['1'];
    $uid = decrypt_url(session('uid'),C('KEY_URL'));
    $moods = array();
    for($i = 0; $i < 7; $i++){
      $start_time = $end_time;
      $end_time = $start_time + 86400;
      $mood = array();
      $mood['signin'] = 0;
      if(M('kdgx_signin')->where(array('uid'=>$uid,'timestamp'=>array('between',array($start_time,$end_time))))->find()){
        $mood['signin'] = 1;
      }
      $mood['mood'] = M('kdgx_signin_mood')->where(array('uid'=>$uid,'is_delete'=>'0','create_time'=>array('between',array($start_time,$end_time))))->getField('mood');
      if(empty($mood['mood'])){
        $mood['mood'] = 0;
      }
      $mood['time'] = $start_time;
      $moods[] = $mood;
    }
    $data['mood'] = $moods;
    $data['num'] = M('kdgx_signin')->where(array('uid'=>$uid))->order('id desc')->getField('count');
    $data['total'] = M('kdgx_signin')->where(array('uid'=>$uid))->count();
/*    $data['media_info'] = A('Index')->getPublicInfo();
    if (date('H') < 5) {
        $data['state'] = 0;
    }else{
    $start = mktime(0,0,0);
    $end = time();
    $re = M('kdgx_signin')->where("uid='$uid' && timestamp>=$start && timestamp <=$end")->find();
    if($re){
      $data['state'] =2 ;
      $data['card'] = A('Index')->card();
    }else{
      $data['state'] =1;
      $data['key'] = A('Index')->key();
    }}*/
    echo json_encode(array(
      'error' => 0,
      'msg' => $data
      ));exit();
  }

  // 获取一个月的心情记录和打卡记录
  public function getMonthMood(){
    $end_time = strtotime($_GET['date']);
    $days = date('t', strtotime($_GET['date']));
    $uid = decrypt_url(session('uid'),C('KEY_URL'));
    $moods = array();
    for($i = 0; $i < $days; $i++){
      $start_time = $end_time;
      $end_time = $start_time + 86400;
      $mood = array();
      $mood['signin'] = 0;
      if(M('kdgx_signin')->where(array('uid'=>$uid,'timestamp'=>array('between',array($start_time,$end_time))))->find()){
        $mood['signin'] = 1;
      }
      $mood['mood'] = M('kdgx_signin_mood')->where(array('uid'=>$uid,'is_delete'=>'0','create_time'=>array('between',array($start_time,$end_time))))->getField('mood');
      if(empty($mood['mood'])){
        $mood['mood'] = 0;
      }
      $moods[] = $mood;
    }
    $data['mood'] = $moods;
    $data['num'] = M('kdgx_signin')->where(array('uid'=>$uid))->order('id desc')->getField('count');
    $data['total'] = M('kdgx_signin')->where(array('uid'=>$uid))->count();
    echo json_encode(array(
      'error' => 0,
      'msg' => $data
      ));exit();
  }

  // 获取个人心情列表
  public function getSelfMoodList(){
    $uid = decrypt_url(session('uid'),C('KEY_URL'));
    $moods = M('kdgx_signin_mood')->alias('m')
    ->field('m.id,m.uid,m.create_time,m.content,m.content_img,m.mood,m.thumb_times,o.nickname,o.headimgurl')
    ->join("kddx_user_openid as o on o.uid=m.uid and o.public_id='".C("PUBLIC_ID")."'")
    ->where(array('m.uid'=>$uid,'is_delete'=>'0'))->order('id desc')->select();
    foreach ($moods as $key => $value) {
      $moods[$key]['SigninCount'] = getSigninCount($value['uid']);
      $moods[$key]['uid'] = encrypt_url($value['uid'],C('KEY_URL'));
      $moods[$key]['content'] = emoji_decode($value['content']);
      if(!empty($value['content_img'])){
        $moods[$key]['content_img'] = "http://".C('CDN_SITE').$value['content_img'];
      }
      $moods[$key]['thumb'] = 0;
      if(M('kdgx_signin_mood_thumb')->where(array('uid'=>$uid,'mood_id'=>$value['id']))->find()){
        $moods[$key]['thumb'] = 1;
      }
      $moods[$key]['isSelf'] = 1;
    }
    echo json_encode(array(
      'error' => 0,
      'msg' => $moods
      ));exit();
  }

  // 获取所有人心情列表
  public function getAllMoodList(){
    $firstRow = ($_GET['page']-1) * 20;
    $uid = decrypt_url(session('uid'),C('KEY_URL'));
    $data = M('kdgx_signin_mood')->alias('m')
    ->field('m.id,m.uid,m.create_time,m.content,m.content_img,m.mood,m.is_show,m.thumb_times,o.nickname,o.headimgurl')
    ->join("kddx_user_openid as o on o.uid=m.uid and o.public_id='".C("PUBLIC_ID")."'")
    ->where(array('is_delete'=>'0'))->order('id desc')->group('m.id')->limit($firstRow,20)->select();
    foreach ($data as $key => $value) {
      if($value['is_show']='0' && $value['uid'] != $uid){
        unset($data[$key]);
        break;
      }
      $data[$key]['content'] = emoji_decode($value['content']);
      $data[$key]['SigninCount'] = getSigninCount($value['uid']);
      $data[$key]['uid'] = encrypt_url($value['uid'],C('KEY_URL'));
      if(!empty($value['content_img'])){
        $data[$key]['content_img'] = "http://".C('CDN_SITE').$value['content_img'];
      }
      $data[$key]['thumb'] = 0;
      if(M('kdgx_signin_mood_thumb')->where(array('uid'=>$uid,'mood_id'=>$value['id']))->find()){
        $data[$key]['thumb'] = 1;
      }
      $data[$key]['isSelf'] = 0;
      if($value['uid'] == $uid){
        $data[$key]['isSelf'] = 1;
      }
    }
    echo json_encode(array(
      'error' => 0,
      'msg' => $data
      ));exit();
  }

  // 获取好友心情列表
  public function getFriendMoodList(){
    $firstRow = ($_GET['page']-1) * 20;
    $uid = decrypt_url(session('uid'),C('KEY_URL'));
    $fuids = M('kdgx_friend_info')->where(array('uid'=>$uid))->select();
    $where = array($uid);
    foreach ($fuids as $key => $value) {
      array_push($where, $value['fuid']);
    }
    $data = M('kdgx_signin_mood')->alias('m')->field('m.id,m.uid,m.create_time,
      m.content,m.content_img,m.mood,m.is_show,m.thumb_times,o.nickname,o.headimgurl')
    ->join("kddx_user_openid as o on o.uid=m.uid and o.public_id='".C("PUBLIC_ID")."'")
    ->where(array('m.uid'=>array('in',$where),'is_delete'=>'0','is_show'=>'1'))
    ->order('id desc')->limit($firstRow,20)->select();
    foreach ($data as $key => $value) {
      if($value['is_show']='0' && $value['uid'] != $uid){
        unset($data[$key]);
        break;
      }
      $data[$key]['content'] = emoji_decode($value['content']);
      $data[$key]['SigninCount'] = getSigninCount($value['uid']);
      $data[$key]['uid'] = encrypt_url($value['uid'],C('KEY_URL'));
      if(!empty($value['content_img'])){
        $data[$key]['content_img'] = "http://".C('CDN_SITE').$value['content_img'];
      }
      $data[$key]['thumb'] = 0;
      if(M('kdgx_signin_mood_thumb')->where(array('uid'=>$uid,'mood_id'=>$value['id']))->find()){
        $data[$key]['thumb'] = 1;
      }
      if($value['uid'] == $uid){
        $data[$key]['isSelf'] = 1;
      }
    }
    echo json_encode(array(
      'error' => 0,
      'msg' => $data
      ));exit();
  }

  // 当前用户是否是管理员
  public function isAdmin(){
    $uid = decrypt_url(session('uid'),C("KEY_URL"));
    if(in_array($uid, $this->adminUid)){
      echo json_encode(array(
        'error' => 0,
        'msg' => 'admin'
        ));exit();
    }else{
      echo json_encode(array(
        'error' => -1,
        'msg' => 'no'
        ));exit();
    }
  }


  // 点赞心情
  public function thumbMood(){
    if(empty($_POST['id'])){
      echo json_encode(array(
        'error' => 40026,
        'msg' => '参数错误'
        ));exit();      
    }
    $uid = decrypt_url(session('uid'),C('KEY_URL'));
    if(M('kdgx_signin_mood_thumb')->where(array('uid'=>$uid,'mood_id'=>$_POST['id']))->find()){
      echo json_encode(array(
        'error' => 40026,
        'msg' => '你已经为它点过赞了'
        ));exit();      
    }
    $muid = M('kdgx_signin_mood')->where(array('id'=>$_POST['id']))->getField('uid');
    if($muid==$uid){
      echo json_encode(array(
        'error' => 40026,
        'msg' => '不能为自己点赞!'
        ));exit();      
    }
    if(M('kdgx_signin_mood')->where(array('id'=>$_POST['id']))->setInc('thumb_times',1)){
      M('kdgx_signin_user')
      ->where(array('uid'=>$muid))
      ->setInc('score',1);
      M('kdgx_signin_mood_thumb')->data(array('uid'=>$uid,'mood_id'=>$_POST['id']))->add();
      echo json_encode(array( 
        'error' => 0,
        'msg' => '成功'
        ));exit();      
    }else{
      echo json_encode(array(
        'error' =>40027,
        'msg' => '失败' 
        ));exit();      
    }    
  }

  // 获取举报信息
  public function reportList(){
    $uid = decrypt_url(session('uid'),C('KEY_URL'));
    if(in_array($uid, $this->adminUid)){
      $data = M('kdgx_signin_mood_report')->field('distinct(m.id) as mood_id,m.uid,create_time,content,content_img,mood,mr.id as report_id')
      ->alias('mr')->join('kdgx_signin_mood as m on mr.mood_id=m.id')
      ->where(array('mr.is_delete'=>'0','m.is_delete'=>'0'))->group('m.id')->select();
      foreach ($data as $key => $value) {
        $data[$key]['uid'] = encrypt_url($value['uid'],C('KEY_URL'));
        if(!empty($value['content_img'])){
          $data[$key]['content_img'] = "http://".C('CDN_SITE').$value['content_img'];
        }        
      }
      echo json_encode(array(
        'error' => 0,
        'msg' => $data
        ));exit();       
    }else{
      echo json_encode(array(
        'error' => -1,
        'msg' => '没有权限'
        ));exit();
    }
  } 

  // 举报心情
  public function reportMood(){
    if(empty($_POST['id']) && empty($_POST['uid'])){
      echo json_encode(array(
        'error' => 40026,
        'msg' => '参数错误'
        ));exit();      
    }
    $data = array(
      'uid' => decrypt_url($_POST['uid'],C('KEY_URL')),
      'report_time' => time(),
      'mood_id' => $_POST['id']
      );
    M('kdgx_signin_mood_report')->data($data)->add(); 
    echo json_encode(array( 
      'error' => 0,
      'msg' => '成功'
      ));exit();
  }

  // 忽略举报信息
  public function recycMood(){
    if(empty($_POST['id'])){
      echo json_encode(array(
        'error' => 40026,
        'msg' => '参数错误'
        ));exit();      
    }
    if(M('kdgx_signin_mood_report')->save(array('mood_id'=>$_POST['id'],'is_delete'=>'1'))){
      echo json_encode(array(
        'error' => 0,
        'msg' => '成功'
        ));exit();
    }else{
      echo json_encode(array(
        'error' =>40027,
        'msg' => '失败' 
        ));exit();
    }
  }
}