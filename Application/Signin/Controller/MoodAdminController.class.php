<?php 
namespace Signin\Controller;
use Think\Controller;

// 心情日记后台
class MoodAdminController extends Controller {

  public function MoodQRCode(){
    $url = 'http://pan.baidu.com/share/qrcode?w=150&h=150&url='.$_GET['url'];
    header('Content-type:image/png');
    $QRcode = requestGet($url);
    echo $QRcode;
  }

  public function getMood(){
    if(empty($_POST['id'])){
      echo json_encode(array(
        'error' => 40026,
        'msg' => '参数错误'
        ));exit();
    }
    $data = M('kdgx_signin_mood')->alias('m')
    ->field('m.id,m.uid,m.create_time,m.content,m.content_img,m.mood,m.thumb_times,o.nickname,o.headimgurl')
    ->join("kddx_user_openid as o on o.uid=m.uid and o.public_id='".C("PUBLIC_ID")."'")
    ->where(array('m.id'=>$_POST['id']))->find();
    $data['SigninCount'] = getSigninCount($data['uid']);
    $data['content'] = emoji_decode($data['content']);
    if(!empty($data['content_img'])){
      $data['content_img'] = "http://".C('CDN_SITE').$data['content_img'];
    }
    echo json_encode(array(
      'error' => 0,
      'msg' => $data
      ));exit();
  }


  // 修改心情状态
  public function deleteMood(){
    $ids = $_POST['ids'];
    if(empty($ids)){
      echo json_encode(array(
        'error' => 40026,
        'msg' => '参数错误'
        ));exit();
    }
    foreach ($ids as $key => $value) {
      M('kdgx_signin_mood')->where(array('id'=>$value['id']))->save(array('is_delete'=>'1'));
      $count = M('kdgx_signin_mood_thumb')->where(array('mood_id'=>$value['id']))->count();
      M('kdgx_signin_user')
      ->where(array('uid'=>M('kdgx_signin_mood')->where(array('id'=>$value['id']))->getField('uid')))
      ->setDec('score',$count);
    }
    echo json_encode(array( 
      'error' => 0,
      'msg' => '成功'
      ));exit(); 
  }

  // 获得公众号日记信息
  public function getMoodsForMedia(){
    $_POST['date'] = 0;
    switch($_POST['date']){
      case 0:
        $conditions['date'] = strtotime('-1 week');
        break;
      case 1:
        $conditions['date'] = strtotime('-30 day');
        break;
      case 2:
        $conditions['date'] = strtotime('-60 day');
        break;
      case 3:
        $conditions['date'] = 0;
        break;
    }
    $media_id = session('media_id');
    $data = M('kdgx_signin_mood')->alias('m')->field('m.id,m.uid,m.create_time,
      m.content,m.content_img,m.mood,m.thumb_times,m.tag,o.nickname,o.headimgurl')
    ->join("kddx_user_openid as o on m.uid=o.uid and o.public_id='".C("PUBLIC_ID")."'")
    ->where(array('m.is_show'=>'1','m.is_delete'=>'0','media_id'=>$media_id,
      'create_time' => array('egt',$conditions['date'])))->order('id desc')->select();
    foreach ($data as $key => $value) {
      $data[$key]['SigninCount'] = getSigninCount($value['uid']);
      $data[$key]['content'] = emoji_decode($value['content']);
      if(!empty($value['content_img'])){
        $data[$key]['content_img'] = "http://".C('CDN_SITE').$value['content_img'];
      }
    }
    echo json_encode(array( 
      'error' => 0,
      'msg' => $data
      ));exit();
  }

  // 标记记录
  public function changeTag(){
    $ids = $_POST['ids'];
    if(empty($ids)){
      echo json_encode(array(
        'error' => 40026,
        'msg' => '参数错误'
        ));exit();
    }
    foreach ($ids as $key => $value) {
      M('kdgx_signin_mood')->where(array('id'=>$value['id']))->save(array('tag'=>$value['tag']));
    }
    echo json_encode(array( 
      'error' => 0,
      'msg' => '成功'
      ));exit(); 
  }

}