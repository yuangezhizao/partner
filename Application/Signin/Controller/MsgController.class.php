<?php
namespace Signin\Controller;
use Think\Controller;
class MsgController extends Controller{
    

    
    /**
     * 接收微信消息
     * @param unknown $postStr
     * @param unknown $media_id
     */
    Public function responseMsg($postStr,$media_id)
    {
        if (!empty($postStr)){
            /* libxml_disable_entity_loader         是XML实体加载器是防止外部实体注入 */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA); // XML转object
            $content = $postObj->Content;
            $mediaInfo = A('mediaInfo');
            $Info = $mediaInfo->get_media_keywords($media_id);
            $keywords = $Info->keyword;
            $keyArr = explode(',', $keywords);
            $keyword = $keyArr[0];
            if (in_array($content, $keyArr)) {

                $object = (object) [
                    'ToUserName' => "$postObj->FromUserName",
                    'FromUserName' => "$postObj->ToUserName",
                    'CreateTime' => time(),
                ];
                switch (date('w')) {
                    case 1:
                        $week = '星期一';  break;
                    case 2:
                        $week = '星期二';  break;
                    case 3:
                        $week = '星期三';  break;
                    case 4:
                        $week = '星期四';  break;
                    case 5:
                        $week = '星期五';  break;
                    case 6:
                        $week = '星期六';  break;
                    case 0:
                        $week = '星期天';  break;
                }
                
                $array = [
                    [
                        'Title' => '点击开始早起打卡',
                        'Description' => "早安，今天是".date('m月d日').$week."！\n点此开启全新的一天！",
                        'Picurl' => 'http://'.C('WEB_SITE').'/Public/Signin/img/SigninHeader.jpg',
                        'Url' =>  'http://'.C('WEB_SITE').'/index.php/Signin/index/index?media_id=' . $postObj->ToUserName,
                    ],
                ];
                /*--------【记录打卡触发日志】---------*/
                //M('kdgx_signin_trigger')->add(['media_id'=> "$postObj->ToUserName",'openid'=> "$postObj->FromUserName",'type'=>1,'timestamp'=>time(),]);
                
                echo $this->returnNews($object, $array);
                
            } else {
                /*--------【记录打卡触发日志】---------*/
                //M('kdgx_signin_trigger')->add(['media_id'=> "$postObj->ToUserName",'openid'=> "$postObj->FromUserName",'type'=>2,'timestamp'=>time(),]);
                
                
                /*--------【打卡关键词和口令分离】---------*/
                $length = mb_strlen($keyword,'utf-8');
                $key = mb_substr($content,$length,8,'utf-8');
                $json = $this->sign( $key, $media_id);
                $json = json_decode($json);
                if ($json->errcode == 0) {
                
                    //成功返回图文消息
                    $re = M('kdgx_signin_user')->field('uid')->where("`key`='$key' ")->find();
                    $rs = M('kddx_user_openid')->field('nickname')->where("uid='{$re['uid']}' && public_id='gh_3347961cee42'")->find();
                    $object = (object) [
                        'ToUserName' => "$postObj->FromUserName",
                        'FromUserName' => "$postObj->ToUserName",
                        'CreateTime' => time(),
                    ];
                    $array = [
                        [
                            'Title' => '亲爱的' . $rs['nickname'] . '，点此获取成就卡！',
                            'Description' => $json->errmsg."\n每一次坚持都是为了梦想，谢谢奋斗的自己！",
                            'Picurl' => 'http://'.C('WEB_SITE').'/Public/Signin/img/SuccessLogo.jpg',
                            'Url' =>  'http://'.C('WEB_SITE').'/index.php/Signin/index/index?media_id=' . $postObj->ToUserName,
                        ],
                        [
                            'Title' => '今日获取'.$json->errmsg->dayscore.'g早起能量！（查看规则）',
                            'Description' => $json->errmsg."\n每一次坚持都是为了梦想，谢谢奋斗的自己！",
                            'Picurl' => 'http://'.C('WEB_SITE').'/Public/Signin/img/SigninLogo.png',
                            'Url' =>  'http://mp.weixin.qq.com/s/u10iOCwRPXM0_R-ekI__4g',
                        ],
                        [
                            'Title' => "你今天是全国第".$json->errmsg->ranking."名签到的哦！\n5:00-9:00打卡会有额外积分哦",
                            'Description' => $json->errmsg."\n每一次坚持都是为了梦想，谢谢奋斗的自己！",
                            'Picurl' => '',
                            'Url' =>  'http://'.C('WEB_SITE').'/index.php/Signin/index/index?media_id=' . $postObj->ToUserName,
                        ],
                    ];
/**------------------------【7~9点腾讯的广告-开始】--------------------------------*/    
                    $time = time();
                    if(($time>=1484949600 && $time< 1484960400) || ($time>=1485208800 && $time< 1485219600)){
                        $re = M('kdgx_signin_media')->where("media_id='$media_id' && ad='1'")->find();
                        if($re){
                            $ad = [
                                'Title' => '恭喜获得早起打卡福利一份',
                                'Description' => '',
                                'Picurl' => '',
                                'Url' =>  'https://ke.qq.com/course/139248?from=116',
                            ];
                            array_push($array,$ad);
                        }
                    }elseif(($time>=1485036000 && $time< 1485046800) || ($time>=1485295200 && $time< 1485306000)){
                        $re = M('kdgx_signin_media')->where("media_id='$media_id' && ad='1'")->find();
                        if($re){
                            $ad = [
                                'Title' => '恭喜获得早起打卡福利一份',
                                'Description' => '',
                                'Picurl' => '',
                                'Url' =>  'https://ke.qq.com/course/181183?from=116',
                            ];
                            array_push($array,$ad);
                        }
                    }elseif(($time>=1485122400 && $time< 1485133200) || ($time>=1485381600 && $time< 1485392400)){
                        $re = M('kdgx_signin_media')->where("media_id='$media_id' && ad='1'")->find();
                        if($re){
                            $ad = [
                                'Title' => '恭喜获得早起打卡福利一份',
                                'Description' => '',
                                'Picurl' => '',
                                'Url' =>  'https://ke.qq.com/course/180796?from=116',
                            ];
                            array_push($array,$ad);
                        }
                    }
                /**------------------------【7~9点腾讯的广告-结束】--------------------------------*/  
                /**------------------------【六点之后显示心情日记-开始】----------------------------*/
                    if(date('H')>=6){
                    $mood = [
                            'Title' => '写心情日记获额外积分奖励',
                            'Description' => '',
                            'Picurl' => '',
                            'Url' =>  'http://'.C('WEB_SITE').'/index.php/Signin/Mood/index?media_id=' . $postObj->ToUserName,
                    ];
                    array_push($array,$mood);
                    }
                /**------------------------【六点之后显示心情日记-结束】----------------------------*/

                    echo $this->returnNews($object, $array);
                }elseif($json->errcode == 3){
                    //成功返回图文消息
                    $re = M('kdgx_signin_user')->field('uid')->where("`key`='$key' ")->find();
                    $rs = M('kddx_user_openid')->field('nickname')->where("uid='{$re['uid']}' && public_id='gh_3347961cee42'")->find();
                    $object = (object) [
                        'ToUserName' => "$postObj->FromUserName",
                        'FromUserName' => "$postObj->ToUserName",
                        'CreateTime' => time(),
                    ];
                    $array = [
                        [
                            'Title' => '亲爱的' . $rs['nickname'] . '，你今天已经打过卡了哦！',
                            'Description' => '',
                            'Picurl' => 'http://'.C('WEB_SITE').'/Public/Signin/img/SuccessLogo.jpg',
                            'Url' =>  'http://'.C('WEB_SITE').'/index.php/Signin/index/index?media_id=' . $postObj->ToUserName,
                        ],
                    ];
                /**------------------------【7~9点腾讯的广告-开始】--------------------------------*/    
                    $time = time();
                    if(($time>=1484949600 && $time< 1484960400) || ($time>=1485208800 && $time< 1485219600)){
                        $re = M('kdgx_signin_media')->where("media_id='$media_id' && ad='1'")->find();
                        if($re){
                            $ad = [
                                'Title' => '恭喜获得早起打卡福利一份',
                                'Description' => '',
                                'Picurl' => '',
                                'Url' =>  'https://ke.qq.com/course/139248?from=116',
                            ];
                            array_push($array,$ad);
                        }
                    }elseif(($time>=1485036000 && $time< 1485046800) || ($time>=1485295200 && $time< 1485306000)){
                        $re = M('kdgx_signin_media')->where("media_id='$media_id' && ad='1'")->find();
                        if($re){
                            $ad = [
                                'Title' => '恭喜获得早起打卡福利一份',
                                'Description' => '',
                                'Picurl' => '',
                                'Url' =>  'https://ke.qq.com/course/181183?from=116',
                            ];
                            array_push($array,$ad);
                        }
                    }elseif(($time>=1485122400 && $time< 1485133200) || ($time>=1485381600 && $time< 1485392400)){
                        $re = M('kdgx_signin_media')->where("media_id='$media_id' && ad='1'")->find();
                        if($re){
                            $ad = [
                                'Title' => '恭喜获得早起打卡福利一份',
                                'Description' => '',
                                'Picurl' => '',
                                'Url' =>  'https://ke.qq.com/course/180796?from=116',
                            ];
                            array_push($array,$ad);
                        }
                    }
                /**------------------------【7~9点腾讯的广告-结束】--------------------------------*/  
                    
                /**------------------------【六点之后显示心情日记-开始】----------------------------*/
                    if(date('H')>=6){
                    $mood = [
                            'Title' => '写心情日记获额外积分奖励',
                            'Description' => '',
                            'Picurl' => '',
                            'Url' =>  'http://'.C('WEB_SITE').'/index.php/Signin/Mood/index?media_id=' . $postObj->ToUserName,
                    ];
                    array_push($array,$mood);
                    }
                /**------------------------【六点之后显示心情日记-结束】----------------------------*/

                    ##输出最终图文消息
                    echo $this->returnNews($object, $array);
                }else{
                    ##失败返回状态
                    $postObj->Content = $json->errmsg;
                    echo $this->returnText($postObj);
                }
            }
        }else{
            echo  '';
        }
    }
    
    /**
     * 打卡验证口令
     * @param unknown $key              口令
     * @param unknown $media_id     公众号ID
     * @return string|boolean
     */
    Private function sign( $key, $media_id)
    {   
        if(date('H') < 5){
            return json_encode(array('errcode'=>4,'errmsg'=>'还没到签到时间哦！'));exit();
        }
        //M('kdgx_signin_media')->where("media_id='$media_id'")->find();
        $rs = M('kdgx_signin_user')->field('uid')->where("`key`='$key'")->find();
        if(!$rs){
            $content = "口令错误或口令已失效，\n<a href='http://".C('WEB_SITE').'/index.php/Signin/index/index?media_id='.$media_id."'>".'点击重新生成口令！' ."</a>";
            return json_encode(array('errcode'=>2,'errmsg'=>$content));exit();
        }
        $start = strtotime(date('Y-m-d'));
        $end = strtotime(date('Y-m-d',strtotime('+1 day')));
        $re = M('kdgx_signin')->field('id')->where("uid='{$rs['uid']}' && timestamp>='$start' && timestamp<'$end'")->find();
        if($re){
            return json_encode(array('errcode'=>3,'errmsg'=>'您今天已经签过到了哟！'));exit();
        }
        /*----------------【计算签到次数增加积分】------------------*/
        $count = continuous($rs['uid']);//签到连更次数
        //$h = M('kdgx_signin_media')->where("media_id='$media_id'")->find();
        $score = getExtraScore();//计算5-9点额外积分
        if($count>=10){
            $dayscore = $score + 10 ;
        }else{
            $dayscore = $score + $count ;
        }
        /*-----------------【计算今日第几名】---------------*/
        $start = mktime(0, 0, 0);
        $end = time();
        $num = M('kdgx_signin')->where("timestamp>='$start' && timestamp<='$end'")->count();
        /*-----------------【签到信息记录入库】----------------*/
        $data = [
            'uid' => $rs['uid'],
            'timestamp' => time(),
            'media_id' => $media_id,
            'count' => $count,
            'extrascore' => $score,
            'dayscore' => $dayscore,
        ];
        $re = M('kdgx_signin')->data($data)->add();
        if($re){
            M('kdgx_signin_user')->where("uid='{$rs['uid']}'")->setInc('score',$dayscore);//加当日积分
            $data=[
                'dayscore' => $dayscore,//当日积分
                'ranking' => $num+1,//当日排行
            ];
            return json_encode(array('errcode'=>0,'errmsg'=>$data));
        }else{
            return false;
        }
    }
    
    



    /**
     * 发送文本消息
     * @param object   $object
     * @return string
     */
    Private  function returnText($object)
    {
        $textTpl = '<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%d</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
                    </xml>';
        $resultStr = sprintf ( $textTpl, $object->FromUserName, $object->ToUserName, $object->CreateTime , $object->Content );
        return $resultStr;
    }
    
    /**
     * 发送
     * @param object    $object     回复者信息
     * @param array      $array       图文消息
     * @return string
     */
     Private  function returnNews($object, $array )
    {
        $str='<item>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <PicUrl><![CDATA[%s]]></PicUrl>
                <Url><![CDATA[%s]]></Url>
                </item>';
        $item = null ;
        foreach($array as $v)
            $item .= sprintf( $str, $v['Title'], $v['Description'], $v['Picurl'], $v['Url'] );

        $textTpl='<xml>
        <ToUserName><![CDATA[%s]]></ToUserName>
        <FromUserName><![CDATA[%s]]></FromUserName>
        <CreateTime>%d</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <ArticleCount>%d</ArticleCount>
        <Articles>'
        . $item .
        '</Articles>
        </xml>';
        $resultStr = sprintf ( $textTpl, $object->ToUserName, $object->FromUserName, $object->CreateTime , count($array) );
        return $resultStr;
    }
    
    
    
    
}