<?php
namespace Signin\Controller;
use Signin\Controller\BaseController;
class IndexController extends BaseController {

    public function index()
    {
        //behaviorRecord(decrypt_url($_SESSION['uid'], 'pocketuniversity'),14,$_SESSION['media_id']);
        $this->display();
    }



    Public function setBehavior()
    {
        if($_POST['id']){
            //behaviorRecord(decrypt_url($_SESSION['uid'], 'pocketuniversity'),$_POST['id'],$_SESSION['media_id']);
        }
        echo json_encode(array('errcode'=>0,'errmsg'=>'OK'));exit();
    }

    
/**---------------------------------【获取公众号信息生成二维码】-----------------------------------*/
    Public function getPublicInfo()
    {
            $media_id = $_SESSION['media_id'];
            $mediaInfo = new \Signin\Controller\MediaInfoController();
            $json = $mediaInfo->getInfo($media_id);
            $json = json_decode($json);
            $data = array('media_id'=>$json->media_id,'name'=>$json->name);
            return $data;
    }

    /**
     * 基础数据
     */
    Public function user(){

        $data['media_info'] = $this->getPublicInfo();
        $uid = decrypt_url($_SESSION['uid'], 'pocketuniversity');
        $data['uid'] = $uid; 
        if (date('H') < 5) {
            $data['state'] = 0;
        }else{
            $start = mktime(0,0,0);
            $end = time();
            $re = M('kdgx_signin')->where("uid='$uid' && timestamp>=$start && timestamp <=$end")->find();
            if($re){
              $data['state'] =2 ;
              $data['card'] = $this->card();
            }else{
              $data['state'] =1;
              $data['key'] = $this->key();
            }
        }
        echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
    }
    

/**----------------------------------------【获取签到口令】---------------------------------------*/
    Public function key()
    {
            $media_id = $_SESSION['media_id'];
            /*-----------打卡时间段----------------*/
            /*$re = M('kdgx_signin_media')->field('start')->where("media_id='$media_id'")->find();
            if($re){
                if ($timeH < $re['start'] ) {
                    echo json_encode(array('errcode'=>2,'errmsg'=>'今天打卡时间还没有到！'));exit();
                }
            } else {
                $re = M('kdgx_signin_media')->where("media_id='%'")->find();
                if ($timeH < $re['start']) {
                    echo json_encode(array('errcode'=>2,'errmsg'=>'今天打卡时间还没有到！'));exit();
                }
            }*/
            /*----------获取公众号关键字---------*/
            $mediaInfo = A("mediaInfo");
            $Info = $mediaInfo->get_media_keywords($media_id);
            $keywords = $Info->keyword;
            $keyArr = explode(',', $keywords);
            $keyword = $keyArr[0];
            /*---------------------------------*/
            
            $uid = decrypt_url($_SESSION['uid'], 'pocketuniversity');
            
            $rs = M('kdgx_signin_user')->field('time,`key`')->where("uid='$uid'")->find();
            if($rs){
                $times = time()-$rs['time'];
                if($times>=18000){
                    $key = $this->echoStr();
                    M('kdgx_signin_user')->where(array('uid'=>$uid))->save(array('key'=>$key,'time'=>time()));
                }else{
                     $key = $rs['key'];
                }
                return $keyword.$key;
                echo json_encode(array('errcode'=>0,'errmsg'=>$keyword.$key));exit();
            }else{
                $key = $this->echoStr();
                $re = M('kdgx_signin_user')->add(array('uid'=>$uid,'key'=>$key,'time'=>time()));
                if($re){
                    return $keyword.$key;
                    echo json_encode(array('errcode'=>0,'errmsg'=>$keyword.$key));exit();
                }else{
                    return false;
                    echo json_encode(array('errcode'=>2,'errmsg'=>'口令生成失败'));exit();
                }
            }
    }
    
    /**
     * 八位随机字符
     * @return string
     */
    Private function echoStr(){
        $string='QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm';
        $key='';
        for($i=0;$i<8;$i++){
            $key .=substr($string, mt_rand(0, 51 ), 1);
        }
        return $key;
    }
    
    

/**----------------------------------------【每天排行】---------------------------------------*/
    Public function dayRanking()
    {
        if($_SESSION['media_id'] && $_SESSION['uid']){
            $uid = $_SESSION['uid'];
            $uid = decrypt_url($uid, 'pocketuniversity'); 
            $media_id = $_SESSION['media_id'];
            $start = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $end = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
/*            if(S('dayRanking')){
                $arr = S('dayRanking');
            }else{*/
                $sql = "SELECT u.uid,u.nickname,u.headimgurl,s.dayscore,s.timestamp FROM `kdgx_signin`  as s INNER JOIN `kddx_user_openid` as u  ON u.uid=s.uid WHERE  s.`timestamp`>='$start' && s.`timestamp`<='$end' && u.`public_id` = 'gh_3347961cee42' ORDER BY `timestamp` ASC,`dayscore` DESC  LIMIT 0,100";
                $arr = M()->query($sql);
                //S('dayRanking',$arr,60);
                $data['m'] = M()->getlastsql();
 //           }
            if(!$arr){
                echo json_encode(array('errcode'=>2,'errmsg'=>'数据为空'));exit();
            }



            $re = M('kddx_user_openid')->field('uid,nickname,headimgurl')->where("uid='$uid' && public_id='gh_3347961cee42'")->find();
            $data['data'][0]['headimgurl'] = $re['headimgurl'];
            $data['data'][0]['nickname'] = $re['nickname'];
            $data['data'][0]['uid'] = $uid;
            $data['data'][0]['vip'] = vipSigninRank($uid);
            $rs = M('kdgx_signin')->where("timestamp>='$start' && timestamp<='$end'&& uid='{$re['uid']}' ")->find();
            if($rs){
                $num = M('kdgx_signin')->where("timestamp>='$start' && timestamp<'{$rs['timestamp']}' ")->count();
                $data['data'][0]['ranking'] = $num+1;
                $data['data'][0]['timestamp'] = $rs['timestamp'];
                $data['data'][0]['dayscore'] = $rs['dayscore']; 
            }else{
                $data['data'][0]['ranking'] = 0;
                $data['data'][0]['timestamp'] = 0;
                $data['data'][0]['dayscore'] = 0; 
            }
            foreach($arr as $k=>$v){


                $data['data'][$k+1] = array(
                            'ranking' => $k+1,
                            'headimgurl' => $v['headimgurl'],
                            'nickname' => $v['nickname'],
                            'uid' => $v['uid'],
                            'dayscore' => $v['dayscore'],
                            'timestamp' => $v['timestamp'],
                            'vip' => vipSigninRank($v['uid']),
                    );
            }


            echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();


        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }

    }








/**----------------------------------------【我的最近七天的签到】---------------------------------------*/
    Public function myWeekSign()
    {
        if($uid = $_SESSION['uid']){
            $uid = decrypt_url($_SESSION['uid'], 'pocketuniversity');
            $time = getWeek();
            $arr = M('kdgx_signin')->field('timestamp')->where("uid='$uid' && timestamp>='{$time[1]}' && timestamp <='{$time[7]}' ")->select();
            foreach($time as $k=>$v){
                if(!$arr){
                    $data['weeks'][$k] = array('timestamp'=>$v,'state'=>0);
                }
                $end = mktime(23,59,59,date('m',$v),date('d',$v),date('Y',$v));
                foreach($arr as $k1=>$v1){
                    if ($v <= $v1['timestamp'] && $end >= $v1['timestamp']){
                        $data['weeks'][$k] = array('timestamp'=>$v1['timestamp'],'state'=>1);
                    } else {
                        $data['weeks'][$k] = array('timestamp'=>$v,'state'=>0);
                    }
                }
            }
            $start = mktime(0, 0, 0);
            $end = mktime(23, 59, 59);
            $re = M('kdgx_signin')->field('timestamp,dayscore')->where("uid='$uid' && timestamp>='$start' && timestamp <='$end'")->find();
            if($re){
                $data['days'] = array('timestamp'=>$re['timestamp'],'state'=>1,'score'=>$re['dayscore'],'uid'=>$uid);
            }else{
                $data['days'] = array('timestamp'=>$start,'state'=>0,'score'=>0,'uid'=>$uid);
            }
            $count = M('kdgx_signin')->where("uid='$uid'")->count();
            $data['totalTimes'] = $count;
            echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
        }else{
            echo json_encode(array('errcode'=>1,'errmsg'=>'没有参数'));exit();
        }
    }
    

/**-----------------------------------【我的本月签到】------------------------------------------*/
    Public function monthRanking()
    {
        $uid = decrypt_url($_SESSION['uid'], 'pocketuniversity');
        $arr = getMonth($_POST['y'],$_POST['m']);
        foreach($arr as $k=>$v){
            $end = mktime(23,59,59,date('m',$v),date('d',$v),date('Y',$v));
            $re = M('kdgx_signin')->where("uid='$uid' && timestamp>='$v' && timestamp <='$end'")->find();
            if ($re){
                $data['months'][$k] = array('timestamp'=>$v,'state'=>1);
            } else {
                $data['months'][$k] = array('timestamp'=>$v,'state'=>0);
            }
        }
        $count = M('kdgx_signin')->where("uid='$uid'")->count();
        $data['total'] = $count;
        $data['num'] = continuous($uid);//连更次数
        echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
    }



    
/**----------------------------------------【本校全部排行】---------------------------------------*/
    Public function ranking()
    {
        $uid = decrypt_url($_SESSION['uid'], 'pocketuniversity');
        $media_id = $_SESSION['media_id'];
        $sql = "SELECT o.uid,o.nickname,o.headimgurl,u.score FROM `kdgx_signin` AS s INNER JOIN `kdgx_signin_user` as u ON s.uid=u.uid INNER JOIN `kddx_user_openid` as o ON u.uid=o.uid  WHERE s.media_id='$media_id' && o.public_id='gh_3347961cee42' GROUP BY u.uid  ORDER BY u.score DESC   LIMIT 0,100    ";
        $arr = M()->query($sql);
        if(!$arr){
            echo json_encode(array('errcode'=>2,'errmsg'=>'数据为空'));exit();
        }
        $re = M('kddx_user_openid')->field('nickname,headimgurl')->where("uid='$uid' && public_id='gh_3347961cee42'")->find();
        $count = M('kdgx_signin')->where("uid='$uid'")->count();
        $data ['data'][0] = array(
                'ranking' => 0,
                'uid' => $uid,
                'nickname' => $re['nickname'],
                'headimgurl' => $re['headimgurl'],
                'score' => 0,
                'count' => $count,
                'vip' => vipSigninRank($uid),
            );
        foreach($arr as $k=>$v){
            $count = M('kdgx_signin')->where("uid='{$v['uid']}'")->count();
            $data ['data'][$k+1] = array(
                        'ranking' => $k+1,
                        'uid' => $v['uid'],
                        'nickname' => $v['nickname'],
                        'headimgurl' => $v['headimgurl'],
                        'score' => $v['score'],
                        'count' => $count,
                        'vip' => vipSigninRank($v['uid']),
                );
            if($v['uid']==$uid){
                $data['data'][0]['ranking'] = $k+1;
                $re = M('kdgx_signin_user')->field('score')->where("uid='{$v['uid']}'")->find();
                $data['data'][0]['score'] = $re['score'];
            }
        }
            $arr = M('kdgx_signin')->field('uid')->where("media_id='$media_id'")->group('uid')->select();
            $count = count($arr);
            $data['count'] = $count;
            echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();

    }


/**------------------------------------【学校排行榜】------------------------------------------*/
    Public function schoolRankings()
    {
        $sql = 'SELECT `school_name`,`score`,`count` FROM `kdgx_signin_school` ORDER BY `score` DESC ';
        $arr = M()->query($sql);
        $uid = decrypt_url($_SESSION['uid'], 'pocketuniversity');
        $rs = M('kddx_user_info')->field('new_school')->where("uid='$uid'")->find();
        $data['data'][0] = array(
            'school_name'=>$rs['school_name'],
            'score' => 0,
            'count' => 0,
            'ranking' => 0,
        );
        foreach($arr as $k=>$v){
            $data['data'][$k+1] = array(
                    'school_name'=>$v['school_name'],
                    'score' => $v['score'],
                    'count' => $v['count'],
                    'ranking' => $k+1,
            );
            if($v['school_name']==$rs['new_school']){
                $data['data'][0]['school_name'] = $v['school_name'];
                $data['data'][0]['score'] = $v['score'];
                $data['data'][0]['count'] = $v['count'];
                $data['data'][0]['ranking'] = $k+1;
            }

        }
        //$data['data'] = $arr;
        $data['count'] = M('kdgx_signin_media')->count();
        $data['school'] = $rs['new_school'];
        echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
    }


/**------------------------------------【时间段--学校排行榜】------------------------------------------*/
    Public function schoolRanking()
    {
        if(S('school_arr')){
            $array = S('school_arr');
        }else{
        $sql = "SELECT i.`new_school` FROM `kdgx_signin` AS s INNER JOIN `kddx_user_info` AS i ON s.`uid`=i.`uid` WHERE i.`new_school`!='' && i.`new_school` NOT LIKE '社会人士' GROUP BY i.`new_school` ";
        $arr = M()->query($sql);
        foreach($arr as $k => $v){
            $sql = "SELECT u.`dayscore`,u.`uid`,i.`new_school` FROM `kdgx_signin` AS u INNER JOIN `kddx_user_info` AS i ON i.`uid` = u.`uid` 
                    WHERE u.`timestamp`>='1484409600' && u.`timestamp`<='1487088000' && i.`new_school`='{$v['new_school']}'";
            $rs = M()->query($sql);
            $data[$k]['score'] = 0;
            $data[$k]['count'] = 0;
            foreach($rs as $vt){
                $data[$k]['score'] += $vt['dayscore'];
                $data[$k]['count'] ++;
            }
            $data[$k]['school_name'] = $v['new_school'];
        }
        $array['data'] = bubbleSort($data,'score','desc');
        $array['count'] = M('kdgx_signin_media')->count();
        S('school_arr',$array,7200);
        unset($cache);
        }
        $uid = decrypt_url($_SESSION['uid'], 'pocketuniversity');
        $rs = M('kddx_user_info')->field('new_school')->where("uid='$uid'")->find();
        $array['school'] = $rs['new_school'];
        echo json_encode(array('errcode'=>0,'errmsg'=>$array));exit();
    }






/**------------------------------------------------------------------【好友排行榜】----------------------------------------------------------------*/
    Public function friendRanking()
    {
        if(!isset($_SESSION['uid'])){
            echo json_encode(array('errcode'=>1,'errmsg'=>'参数为空'));exit();
        }
        $uid = decrypt_url($_SESSION['uid'], 'pocketuniversity');
        $sql = "SELECT `fuid` FROM `kdgx_friend_info` WHERE `uid`='$uid'";
        $arr = M()->query($sql);
        if(!$arr){
            echo json_encode(array('errcode'=>2,'errmsg'=>'数据为空'));exit();
        }
        array_push($arr,['fuid'=>$uid]);
        foreach ($arr as $k => $v) {
            $start = mktime(0, 0, 0);
            $end = mktime(23, 59, 59);
            $rs = M('kdgx_signin')->where("uid='{$v['fuid']}' && timestamp>='$start' && timestamp <='$end'")->find();
            if($rs){
                $re = M('kddx_user_openid')->field('nickname,headimgurl')->where("uid='{$v['fuid']}' && public_id='gh_3347961cee42'")->find();
                $count = M('kdgx_signin')->where("uid='{$rs['uid']}'")->count();
                $score = M('kdgx_signin_user')->field('score')->where("uid='{$v['fuid']}'")->find();
                $score = $score?$score['score']:0;
                $data[] = array(
                        'nickname' => $re['nickname'],
                        'headimgurl' => $re['headimgurl'],
                        'dayscore' => $rs['dayscore'],
                        'timestamp' => $rs['timestamp'],
                        'count' => $count,
                        'vip' => vipSigninRank($v['fuid']),
                        'score' => $score, 
                        'color' => SigninColor($rs['timestamp']),
                    );
            }else{
                $re = M('kddx_user_openid')->field('nickname,headimgurl')->where("uid='{$v['fuid']}' && public_id='gh_3347961cee42'")->find();
                $count = M('kdgx_signin')->where("uid='{$rs['uid']}'")->count();
                $score = M('kdgx_signin_user')->field('score')->where("uid='{$v['fuid']}'")->find();
                $score = $score?$score['score']:0;
                $data[] = array(
                        'nickname' => $re['nickname'],
                        'headimgurl' => $re['headimgurl'],
                        'dayscore' => 0,
                        'timestamp' =>mktime(0,0,0,date('m'),date('d')+1),
                        'count' => 0,
                        'vip' => vipSigninRank($v['fuid']),
                        'score' => $score,
                        'color' => 0
                    );
            }
        }
        $array['lasttime'] = mktime(0,0,0,date('m'),date('d')+1);
        $array['data'] = bubbleSort($data,'timestamp');
        echo json_encode(array('errcode'=>0,'errmsg'=>$array));exit();
    }

    
/**---------------------------------------【卡片】-----------------------------------------*/
    Public function card()
    {
        $uid = decrypt_url($_SESSION['uid'], 'pocketuniversity');
        $media_id = $_SESSION['media_id'];
        $start = mktime(0, 0, 0);
        $end = mktime(23, 59, 59);
        $re = M('kdgx_signin')->where("uid='$uid' && timestamp>='$start' && timestamp <='$end'")->find();
        $data['uid'] = $re['uid'];
        $data['timestamp'] = $re['timestamp'];
        $data['dayscore'] = $re['dayscore'];
        $data['key'] = encrypt_url($re['id'],'pocketuniversity');
        $data['count'] = $re['count'];
        $data['number'] = M('kdgx_signin')->field('id')->where("uid='{$re['uid']}'")->count();
        $count = M('kdgx_signin')->where("  timestamp>='$start' && timestamp <'{$re['timestamp']}'")->count();
        $data['ranking'] = $count+1;
        $re = M('kddx_user_openid')->field('nickname,headimgurl')->where("uid='$uid' && public_id='gh_3347961cee42'")->find();
        $data['nickname'] = $re['nickname'];
        $data['headimgurl'] = $re['headimgurl'];
        return $data;
        echo json_encode(array('errcode'=>0,'errmsg'=>$data));exit();
    }
    
    
    
    
    
    
    
}