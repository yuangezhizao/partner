<?php 
namespace Debug\Controller;
use \Think\Controller;

class EmailController extends Controller{

	private $content1 = <<<EOF

<!DOCTYPE html><html lang="en"><head>
    <meta charset="UTF-8">
    <meta name="author" content="Sure" email="Sure@tuiyilin.com">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta content="black" name="apple-mobile-web-app-status-bar-style" />
    <meta content="telephone=no" name="format-detection" />
    <title>四六级管家</title>
</head>
<body>
    <div class="page" style="font-family:'微软雅黑';width: 100%;height: auto;max-width:600px;margin: 0 auto;margin-top: 20px;">
        <section class="RankEditor">
            <section style="border: 1px solid rgb(226, 226, 226); box-shadow: rgb(226, 226, 226) 0em 0em 0.1em -0.6em;">
                <section style="padding:8px; margin:0 17px;text-align: center;background-color: rgb(53,179,106);">
                    <div class="title white" style="font-size:18px;color: rgb(255, 255, 255);min-width:1px;">
                        &nbsp;&nbsp;&nbsp;&nbsp;四六级管家考前提醒
                    </div>
                </section>
                <section class="RankEditor" style="margin-top:20px;">
                    <section style="border: 1px dashed rgb(220, 216, 217);margin: 5px 17px;padding: 20px 10px;box-shadow: rgb(200, 200, 200) 0px 0px 4px;">
                        <p class="active brush" style="text-align: center; line-height: 2em;color: rgb(121,121,121);min-width:1px;">
                            亲爱的
EOF;

    private $content2 = <<<TWO

                        同学，
                        </p>
                        <p class="active brush" style="line-height: 2em; color: rgb(121, 121, 121); min-width: 1px; text-align: center;">
                            您的预留准考证号为：
TWO;

    private $content3 = <<<THREE

</p>
                        <p class="active brush" style="line-height: 2em; color: rgb(121, 121, 121); min-width: 1px; text-align: center;margin-bottom：30px;font-weight:bold;">

    </p>
    <p class="active brush" style="line-height: 2em; color: rgb(121, 121, 121); min-width: 1px; text-align: center;">
                            明天就是你的四六级考试时间
                        </p>
                        <p class="active brush" style="line-height: 2em; color: rgb(121, 121, 121); min-width: 1px; text-align: center;">
                            在此之前请确认带好准考证
                        </p>
                        <p class="active brush" style="line-height: 2em; color: rgb(121, 121, 121); min-width: 1px; text-align: center;">
                            身份证等证件入场考试
                        </p>
                        <section style="margin-top:-25px;display:flex;text-align:center;">
                            <section style="flex:1;margin-left: 0.5em;">
                                <p class="active brush">
                                    <div style="margin-left:10px;width:32px;height:32px;display:inline-block;">
                                        <img src="http://static.pocketuniversity.cn/kdgx/PocketDoc/library/images/clock.png"/>
                                    </div>
                                    <span style="display:inline-block;height:32px;margin-top:-20px;font-size:14px; color: rgb(121,121,121);min-width:1px;">考试时间 &nbsp;: &nbsp;2016年12月17日 周六</span>
                                </p>
                                <div class="active brush">
                                    <span style="display:inline-block;height:32px;margin-top:-20px;font-size:14px; color: rgb(121,121,121);min-width:1px;">    英语四级 &nbsp;: &nbsp;09:00-11:20 上午</span>
                                </div>
                                <p class="active brush">
                                    <span style="display:inline-block;height:32px;margin-top:-20px;font-size:14px; color: rgb(121,121,121);min-width:1px;">    英语六级 &nbsp;: &nbsp;15:00-17:25 下午</span>
                                </p>
                            </section>
                        </section>
                        <div style="margin-top:-10px;font-size: 14px; color: rgb(121, 121, 121); min-width: 1px; line-height: normal; text-align: center;">
                            <img src="
THREE;

    private $content4 = <<<FOUR
                        " alt="口袋高校助手:PocketUniversity" width="150" height="auto"/><br/>
                        </div>
                        <section style="display:flex;justify-content: center;margin:20px auto;margin-top:10px;">
                            <section style="padding:8px 25px; border: 1px solid rgb(168, 130, 99);">
                                <div class="active brush" style="color: rgb(168, 130, 99);font-weight:bold;line-height:20px;min-width:1px;">
                                    不要迟到哦
                                </div>
                            </section>
                        </section>
                        <p style=" font-size: 14px; color: rgb(121, 121, 121); min-width: 1px; line-height: normal; text-align: center;">
                            <span style="color: rgb(121, 121, 121); line-height: 32px;">管家会在成绩出来之后及时为大家</span>
                        </p>
                        <p style=" font-size: 14px; color: rgb(121, 121, 121); min-width: 1px; line-height: normal; text-align: center;">
                            <span style="color: rgb(121, 121, 121); line-height: 32px;">以邮件或短信的方式为你推送成绩和分析报告</span>
                        </p>
                    </section>
                </section>
                <section style="width:100px;background-color:  rgb(53,179,106);;padding:6px 25px; margin:0 auto;margin-top:30px;margin-bottom:10px;">
                    <div class="white title" style="font-size:16px;color: rgb(255,255,255);min-width:1px;min-width:1px;text-align:center;">
                        小提示
                    </div>
                </section>
                <p style=" font-size: 14px; color: rgb(121, 121, 121); min-width: 1px; line-height: normal; text-align: center;">
                    <span style="color: rgb(121, 121, 121); line-height: 32px;">如需第一时间获得成绩结果及更多便捷服务</span>
                </p>
                <p style=" font-size: 14px; color: rgb(121, 121, 121); min-width: 1px; line-height: normal; text-align: center;">
                    <span style="color: rgb(121, 121, 121); line-height: 32px;">关注管家官方公众号:<b>PocketUniversity</b> 即可订阅</span>
                </p></section></section></div></body></html>
FOUR;
		// 群发邮件
		public function sendEmail(){
            exit();
            $emails = M('kdgx_cet_ticket_number')->where("(send_state | 110) = 110 and (error | 110) = 110 ")->limit(0,20)->order('id asc')->getField('id,email,username,ticket_number,media_id,send_state');
			// exit();

            // $emails = M('kdgx_cet_ticket_number')->where('uid in (10001,10002,10007,98047)')->order('id asc')->getField('id,email,username,ticket_number,media_id,send_state');
			$count = 0;$fail = 0;
            foreach ($emails as $key => $email) {
				$flag = $email['send_state'] | 110;
				if($flag == 110){
                    $imgurl = 'http://open.weixin.qq.com/qr/code/?username='.$email['media_id'];
					$content = $this->content1.$email['username'].$this->content2.$email['ticket_number'].$this->content3.$imgurl.$this->content4;	
					$result = send_email($email,'考前提醒|四六级管家',$content);
					if($result['error']!=1){
						$sql = "update  koudai.kdgx_cet_ticket_number set send_state = send_state | 001 where id= '".$email['id']."'";
						if(M()->execute($sql)){
							$count ++;	
						}
					}else{
                        print_r($result['message']);
                       $sql = "update  koudai.kdgx_cet_ticket_number set error = (error | 001) where id= '".$email['id']."'";
                        if(M()->execute($sql)){
                            $fail ++;  
                        }
                    }
				}

			}
			print_r($fail.'条邮件发送失败!'.$count.'条邮件发送成功!');	
		}

        public function sendPhone(){
            exit();
            $emails = M('kdgx_cet_ticket_number')->where("(send_state | b'101') = b'101' and (error | b'101') = b'101' ")->limit(0,50)->order('id asc')->getField('id,phone,username,send_state');
            // $emails = M('kdgx_cet_ticket_number')->where("(send_state | b'101') = b'101' and (error | b'101') = b'101' and uid=98047")->limit(0,20)->order('id asc')->getField('id,phone,username,send_state');
            $count = 0;$fail = 0;
            foreach ($emails as $key => $email) {
                $username="duyuan";
                $password="24f9c146c2ab1a2ffbea82b1c2d27e07";
                $phone = $email['phone'];
                $time=time();
                $mobileids=$phone.$time;
                $content="【四六级管家】提示：".$email['username']."同学你好，你的四六级考试时间为12月17日，在此之前请确认带好准考证、身份证等证件入场考试，不要迟到哦。管家会在成绩出来的第一时间为你推送成绩结果，请注意查收。";
                $content = charsetToGBK($content);
                $url="http://api.sms.cn/mt/?uid=$username&pwd=$password&mobile=$phone&mobileids=$mobileids&content=$content";
                $data= file_get_contents($url);
                $data= explode("&",$data);
                $resault=explode("=",$data[1]);
                if($resault[1]=='100'){
                    $sql = "update  koudai.kdgx_cet_ticket_number set send_state = send_state | b'010' where id= '".$email['id']."'";
                    if(M()->execute($sql)){
                        $count ++;  
                    }
                }else{
                    print_r($data);
                    $sql = "update  koudai.kdgx_cet_ticket_number set error = error | b'010' where id= '".$email['id']."'";
                    if(M()->execute($sql)){
                        $fail ++;  
                    }
                }
            }
            print_r($fail.'条短信发送失败'.$count.'条短信发送成功');
        }
}