<?php



/**
 * 获取本月的全部的签到状态
 * @return array
 */
function getMonth($y=NULL, $m=NULL,$uid){

    $y = $y==NULL?date('Y'):$y;
    $m = $m==NULL?date('m'):$m;
    $begin = mktime(0,0,0,$m,1,$y);
    $end = mktime(0,0,0,$m+1,1,$y);
    for($i=$begin;$i<$end;$i+=24*60*60){
      $j = mktime(23,59,59,date('m',$i),date('d',$i),date('Y',$i));
      $re = M('kdgx_signin')->field('id')->where("uid='$uid' && timestamp>='$i' && timestamp <='$j'")->find();
      if ($re){
        $arr['months'][] = array('timestamp'=>$i,'state'=>1);
      } else {
        $arr['months'][] = array('timestamp'=>$i,'state'=>0);
      }
    }
    return $arr;
}


/**
 * 获取本周全部的时间戳
 * @return array
 */
function getWeek()
{
    $w = date("w");
    switch ($w) {
        case 1:
            $arr[1] = strtotime("Monday");
            $arr[2] = strtotime("Tuesday");
            $arr[3] = strtotime("Wednesday");
            $arr[4] = strtotime("Thursday");
            $arr[5] = strtotime("Friday");
            $arr[6] = strtotime("Saturday");
            $arr[7] = strtotime("Sunday");
            return $arr;
        case 2:
            $arr[1] = strtotime("-1week Monday");
            $arr[2] = strtotime("Tuesday");
            $arr[3] = strtotime("Wednesday");
            $arr[4] = strtotime("Thursday");
            $arr[5] = strtotime("Friday");
            $arr[6] = strtotime("Saturday");
            $arr[7] = strtotime("Sunday");
            return $arr;
        case 3:
            $arr[1] = strtotime("-1 week Monday");
            $arr[2] = strtotime("-1 weekTuesday");
            $arr[3] = strtotime("Wednesday");
            $arr[4] = strtotime("Thursday");
            $arr[5] = strtotime("Friday");
            $arr[6] = strtotime("Saturday");
            $arr[7] = strtotime("Sunday");
            return $arr;
        case 4:
            $arr[1] = strtotime("-1 week Monday");
            $arr[2] = strtotime("-1 weekTuesday");
            $arr[3] = strtotime("-1 week Wednesday");
            $arr[4] = strtotime("Thursday");
            $arr[5] = strtotime("Friday");
            $arr[6] = strtotime("Saturday");
            $arr[7] = strtotime("Sunday");
            return $arr;
        case 5:
            $arr[1] = strtotime("-1 week Monday");
            $arr[2] = strtotime("-1 weekTuesday");
            $arr[3] = strtotime("-1 week Wednesday");
            $arr[4] = strtotime("-1 week Thursday");
            $arr[5] = strtotime("Friday");
            $arr[6] = strtotime("Saturday");
            $arr[7] = strtotime("Sunday");
            return $arr;
        case 6:
            $arr[1] = strtotime("-1week Monday");
            $arr[2] = strtotime("-1 week Tuesday");
            $arr[3] = strtotime("-1 week Wednesday");
            $arr[4] = strtotime("-1 week Thursday");
            $arr[5] = strtotime("-1 week Friday");
            $arr[6] = strtotime("Saturday");
            $arr[7] = strtotime("Sunday");
            return $arr;
        case 0:
            $arr[1] = strtotime("-1week Monday");
            $arr[2] = strtotime("-1 week Tuesday");
            $arr[3] = strtotime("-1 week Wednesday");
            $arr[4] = strtotime("-1 week Thursday");
            $arr[5] = strtotime("-1 week Friday");
            $arr[6] = strtotime("-1 week Saturday");
            $arr[7] = strtotime("Sunday");
            return $arr;
    }
}

  function getWeeks(){
        $w = date("w");
    switch ($w) {
        case 1:
            $arr = array(strtotime('Monday'),strtotime('Sunday'));
            return $arr;
        case 2:
            $arr = array(strtotime('-1week Monday'),strtotime('Sunday'));
            return $arr;
        case 3:
            $arr = array(strtotime('-1week Monday'),strtotime('Sunday'));
            return $arr;
        case 4:
            $arr = array(strtotime('-1week Monday'),strtotime('Sunday'));
            return $arr;
        case 5:
            $arr = array(strtotime('-1week Monday'),strtotime('Sunday'));
            return $arr;
        case 6:
            $arr = array(strtotime('-1week Monday'),strtotime('Sunday'));
            return $arr;
        case 0:
            $arr = array(strtotime('-1week Monday'),strtotime('Sunday'));
            return $arr;
    }
  }

/**
 * 获取前七天的时间戳
 * @param   timestamp   $time  时间戳
 * @return  $array
 */
  function getSevenDayTimes($times)
  {
    for($k=6;$k>=0;$k--){
      $arr[] = strtotime("-$k days",$times);
    }
    return $arr;
  }

/**
 * 连更次数
 * @param unknown $uid
 * @return number
 */
  function continuous($uid)
  {    
      $num = 0;
      do{
          $num ++;
          $times = strtotime("-$num day");
          $start = mktime(0, 0, 0, date("m", $times), date("d", $times), date("Y", $times));
          $end = mktime(23, 59, 59, date("m", $times), date("d", $times), date("Y", $times));
          $re = M('kdgx_signin')->where("timestamp>='$start' && timestamp<='$end' && uid='$uid'")->find();
  /*         var_dump($re);
          echo date("Y-m-d H:i:s",$re['timestamp']); */

       } while ($re); 
      
      return $num;

  }

/**
 * 获取额外积分
 * @return number
 */
function getExtraScore()
{    
    $time = time();
    switch (date('H')){
        case 5:
/*            $begin = mktime(5, 0, 0, date('m'), date('d'), date('Y'));
            $times = ($time - $begin);
            $i = floor($times/360);
            if ($i == 0)            $score = 23;
            elseif ($i == 1)     $score = 22;
            elseif ($i == 2)     $score = 21;
            elseif ($i == 3)     $score = 20;
            elseif ($i == 4)     $score = 19;
            elseif ($i == 5)     $score = 18;
            elseif ($i == 6)     $score = 17;
            elseif ($i == 7)     $score = 16;
            elseif ($i == 8)     $score = 15;
            elseif ($i == 9)     $score = 14;*/
            $score = 20;
            break;
        case 6:
            $begin = mktime(6, 0, 0, date('m'), date('d'), date('Y'));
            $times = ($time - $begin);
            $i = floor($times/600);
            if ($i == 0)            $score = 13;
            elseif ($i == 1)     $score = 12;
            elseif ($i == 2)     $score = 11;
            elseif ($i == 3)     $score = 10;
            elseif ($i == 4)     $score = 9;
            elseif ($i == 5)     $score = 8;
            break;
        case 7:
            $begin = mktime(7, 0, 0, date('m'), date('d'), date('Y'));
            $times = ($time - $begin);
            $i = floor($times/900);
            if ($i == 0)            $score = 7;
            elseif ($i == 1)     $score = 6;
            elseif ($i == 2)     $score = 5;
            elseif ($i == 3)     $score = 4;
            break;
        case 8:
            $begin = mktime(8, 0, 0, date('m'), date('d'), date('Y'));
            $times = ($time - $begin);
            $i = floor($times/1200);
            if ($i == 0)            $score =3;
            elseif ($i == 1)     $score = 2;
            elseif ($i == 2)     $score = 1;
            break;
         default:
             $score=0;
             break;
    }
    return $score;
}




/**
 * 微信错误弹框
 * @param unknown $str
 * @return string
 */
function returnError($str){

    
   $js1 = <<<EOF
    
	<head>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	</head>
	<body>
	<div class="js_dialog" id="iosDialog2">
      <div class="weui-mask"></div>
      <div class="weui-dialog">
          <div class="weui-dialog__bd">
EOF;
    $js2 = <<<FOE
    
          </div>
          <div class="weui-dialog__ft">
              <a href="javascript:WeixinJSBridge.call('closeWindow');" class="weui-dialog__btn weui-dialog__btn_primary">确定</ a>
          </div>
      </div>
  </div>
  <style media="screen">
  .weui-mask {
    position: fixed;
    z-index: 1000;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
  }
  .weui-dialog__bd:first-child {
    padding: 2.7em 20px 1.7em;
    color: #353535;
  }
  .weui-dialog__bd {
    padding: 0 1.6em 0.8em;
    min-height: 40px;
    font-size: 15px;
    line-height: 1.3;
    word-wrap: break-word;
    word-break: break-all;
    color: #999999;
  }
  .weui-dialog__ft {
    position: relative;
    line-height: 48px;
    font-size: 18px;
    display: -webkit-box;
    display: -webkit-flex;
    display: flex;
  }
  @media screen and (min-width: 1024px){
  .weui-dialog {
      width: 35%;
  }
  }
  .weui-dialog {
      position: fixed;
      z-index: 5000;
      width: 80%;
      max-width: 300px;
      top: 50%;
      left: 50%;
      -webkit-transform: translate(-50%, -50%);
      transform: translate(-50%, -50%);
      background-color: #FFFFFF;
      text-align: center;
      border-radius: 3px;
      overflow: hidden;
  }
  .weui-dialog__btn_primary {
    color: #0BB20C;
  }
  .weui-dialog__btn {
    display: block;
    -webkit-box-flex: 1;
    -webkit-flex: 1;
    flex: 1;
    color: #3CC51F;
    text-decoration: none;
    -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
    position: relative;
  }
  </style>
  </body>
FOE;
   
   return  $js1.$str.$js2;
}



/**
 *冒泡排序（从大到小）
 * @param array $array
 * @return array
 */
function bubbleSort($array,$str,$operator='asc')
{  
  $len=count($array);
  //该层循环控制 需要冒泡的轮数
    for($i=1;$i<$len;$i++)
    { //该层循环用来控制每轮 冒出一个数 需要比较的次数
        for($k=0;$k<$len-$i;$k++)
        {
          switch ($operator) {
            case 'asc':
               if($array[$k][$str] > $array[$k+1][$str])
                {
                    $tmp = $array[$k+1];
                    $array[$k+1] = $array[$k];
                    $array[$k] = $tmp;
                }
              break;
            case 'desc':
               if($array[$k][$str] < $array[$k+1][$str])
                {
                    $tmp = $array[$k+1];
                    $array[$k+1] = $array[$k];
                    $array[$k] = $tmp;
                }
              break;
            default:
              break;
          }

        }
    }
    return $array;
}







    /**
     * 用户行为记录
     * @param $uid  用户uid
     * @param $id   请求行为ID
     */
    function behaviorRecord($uid,$id,$media_id)
    {
        //M('kdgx_data_response')->add(array('uid'=>$uid,'response_time'=>time(),'module_id'=>5,'request_id'=>$id,'request_from'=>$media_id));
    }





    /**
     * 两个用户互加好友
     * @param $uid
     * @param $fuid
     */
    function  addFriends($uid,$fuid)
    {
        if($uid != $fuid){
            M()->startTrans();//开启事务
            $rs = M('kdgx_friend_info')->field('id')->where("uid='$uid' && fuid='$fuid'")->find();
            if(!$rs){
                $arr = M('kddx_user_openid')->field('nickname')->where("uid='$fuid' && public_id='gh_3347961cee42'")->find();
                $remark = $arr?$arr['nickname']:'';
                $s = M('kdgx_friend_info')->add(array('uid'=>$uid,'fuid'=>$fuid,'remark'=>$remark,'type'=>1));
                if(!$s){
                    M()->rollback();//事务回滚
                }
            }
            $rs = M('kdgx_friend_info')->field('id')->where("uid='$fuid' && fuid='$uid' ")->find();
            if(!$rs){
                $arr = M('kddx_user_openid')->field('nickname')->where("uid='$uid' && public_id='gh_3347961cee42'")->find();
                $remark = $arr?$arr['nickname']:'';
                $s = M('kdgx_friend_info')->add(array('uid'=>$fuid,'fuid'=>$uid,'remark'=>$remark,'type'=>1));
                if(!$s){
                    M()->rollback();//事务回滚
                }
                $count = M('kdgx_friend_info')->where(array('uid'=>$uid))->count();
                if($count<=50){
                  M('kdgx_signin_user')->where(array('uid'=>$uid))->setInc('score',10);
                }
            }
            M()->commit();//提交事务
        }
    }



    /**
     * 
     */
    function getSigninCount($uid)
    {
      $count = M('kdgx_signin')->where("uid='$uid'")->count();
      return $count;
    }





    /**
     * vip等级
     */

    function vipSigninRank($uid)
    {
      $rs = M('kdgx_signin_user')->field('score')->where("uid='$uid'")->find();
      if ($rs['score'] < 100){
        return 0;
      }elseif ($rs['score'] >= 100 && $rs['score'] < 1000 ){
        return 1;
      }elseif ($rs['score'] >= 1000 && $rs['score'] < 3000 ){
        return 2;
      }elseif ($rs['score'] >= 3000 && $rs['score'] < 5000){
        return 3;
      }elseif ($rs['score'] >= 5000 && $rs['score'] < 10000){
        return 4;
      }elseif ($rs['score'] >= 10000){
        return 5;
      }else{
        return 0;
      }
    }


  /**
   * 根据打卡时间显示颜色
   *
   */
    function SigninColor($timestamp)
    {
      $h = date('H',$timestamp);
      if ($h >= 5 && $h < 9){
        return 1;
      }elseif ($h >= 9 && $h < 12){
        return 2;
      }elseif ($h >= 12){
        return 3;
      }else{
        return 0;
      }
    }

//对emoji表情转义
function emoji_encode($str){
    $strEncode = '';

    $length = mb_strlen($str,'utf-8');

    for ($i=0; $i < $length; $i++) {
        $_tmpStr = mb_substr($str,$i,1,'utf-8');    
        if(strlen($_tmpStr) >= 4){
            $strEncode .= '[[EMOJI:'.rawurlencode($_tmpStr).']]';
        }else{
            $strEncode .= $_tmpStr;
        }
    }
    return $strEncode;
}    
//对emoji表情转反义
function emoji_decode($str){
    $strDecode = preg_replace_callback('|\[\[EMOJI:(.*?)\]\]|', function($matches){  
        return rawurldecode($matches[1]);
    }, $str);

    return $strDecode;
} 








?>