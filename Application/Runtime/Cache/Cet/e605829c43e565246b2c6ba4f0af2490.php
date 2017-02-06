<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="author" content="Sure" email="Sure@tuiyilin.com">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta content="black" name="apple-mobile-web-app-status-bar-style" />
    <meta content="telephone=no" name="format-detection" />
    <title>四六级管家</title>
    <link rel="stylesheet" href="http://static.pocketuniversity.cn/kdgx/PocketTraffic/1.0/src/css/lib/weui.min.css">
    <style>
      *{
        margin:0;
        padding:0;
      }
      body{
        font-family: "微软雅黑";
        background-color: #f8f8f8;
      }
      h1,h2,h3,h4,h5{
        font-weight: normal;
      }
      header{
        padding-top: 30px;
      }
      header .page__title{
        text-align: center;
        color: rgba(4,190,1,1);
      }
      header .page__desc{
        font-size: .8rem;
        text-align: center;
        color: rgba(0,0,0,.6);
      }
      .info .weui-cell{
        background-color: #fff;
      }
      .tips .weui-media-box__desc{
        padding:10px 15px;
        font-size: .8rem;
      }
      .tips .weui-icon_msg{
        font-size: 1.5rem;
      }
      .tips_info{
        padding:10px 15px;
      }
      .weui-footer__link{
      	color: #586c94;
      }
    </style>
  </head>
  <body>
    <div class="page" id="page">
      <header class="page__hd">
        <h2 class="page__title">四六级准考证预存</h2>
        <p class="page__desc">请完善个人信息</p>
      </header>
      <div class="info">
        <div class="weui-cells__title">准考证信息</div>
        <div class="weui-cell">
          <div class="weui-cell__hd"><label class="weui-label">姓 名</label></div> 
          <div class="weui-cell__bd">
            <input class="weui-input" type="text" name="username" placeholder="请填写你的准考证姓名" value="">
          </div>
        </div>
        <div class="weui-cell">
          <div class="weui-cell__hd"><label class="weui-label">证 号</label></div>
          <div class="weui-cell__bd">
            <input class="weui-input" type="number" name="ticketNo" placeholder="请输入你的15位准考证号" value="">
          </div>
        </div>
        <div class="weui-cells__title">通知方式</div>
        <div class="weui-cell">
          <div class="weui-cell__hd"><label class="weui-label">手 机</label></div>
          <div class="weui-cell__bd">
            <input class="weui-input" type="number" name="phone" placeholder="用于接收成绩提醒" value="">
          </div>
        </div>
        <div class="weui-cell"> 
          <div class="weui-cell__hd"><label class="weui-label">邮 箱</label></div>
          <div class="weui-cell__bd">
            <input class="weui-input" type="email" name="email" placeholder="用于接收成绩分析报告" value="">
          </div>
        </div>
      </div> 
      <div class="tips">
        <div class="tips_info">
          <i class="weui-icon-info weui-icon_msg"></i> 
          <span>管家提醒:</span>
        </div>
        <p class="weui-media-box__desc">绑定成功后在公众号回复"四六级管家"，即可查询预留信息</p>
      </div>
      <div class="weui-btn-area">
        <a class="weui-btn weui-btn_primary" href="javascript:" id="submit">确定预存</a>
      </div>
      <div class="weui-footer" style="margin-top:20px;">
        <p class="weui-footer__links">
            <span class="weui-footer__link"><a class="qrShow"><?php echo cookie('media_name')?></a> | <a href="http://www.pocketuniversity.cn">口袋高校</a></span>
        </p>
        <p class="weui-footer__text">Copyright © 2014-2016 pocketuniversity.cn</p>
      </div>
      <div class="js_dialog" id="dialog2" style="opacity: 1;display:none;" >
        <div class="weui-mask"></div>
        <div class="weui-dialog">
            <div class="weui-dialog__hd"><strong class="weui-dialog__title"><?php echo cookie('media_name')?></strong></div>
            <div class="weui-dialog__bd" style="padding:0;min-height:24px;"><p>把你的烦恼，交给四六级管家吧</p></div>
            <div class="hide">
              <img src="http://open.weixin.qq.com/qr/code/?username=<?php echo cookie('media_id')?>" alt="<?php echo cookie('media_name')?>" width="70%" height="auto">
            </div>
            <div class="weui-dialog__ft">
                <a id="cancel" class="weui-dialog__btn weui-dialog__btn_default">夸 我</a>
            </div>
        </div>
      </div>
    </div>
    <script type="text/javascript" src="http://tajs.qq.com/stats?sId=59591163" charset="UTF-8"></script>
    <script type="text/javascript" src="http://static.pocketuniversity.cn/kdgx/PocketTraffic/1.0/src/js/lib/jquery.min.js"></script>
    <script type="text/javascript">
      $('.qrShow').click(function (){
        // alert("嘿，戳我干嘛");
        $("#dialog2").show();
      });
      $('#cancel').click(function (){
        $("#dialog2").hide();
      });
      $("#submit").bind("click",function(){
        var isBind = 1;
        var name = $("input[name=username]");
        var email = $("input[name=email]");
        var phone = $("input[name=phone]");
        var ticketNo = $("input[name=ticketNo]");
        console.log(email.val());
        console.log(ticketNo.val());
        var check_ticket = /^\d{15}$/.test(ticketNo.val());
        var check_email = /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/.test(email.val());
        var check_phone = /^1\d{10}$/.test(phone.val());
        console.log(check_email);
        console.log(check_ticket);
        console.log(check_phone);
        if(name.val() == ''){
          alert("姓名不能为空");
          name.focus();
          isBind = 0; 
          return;
        };
        if(ticketNo.val() == ''||check_ticket == false){
          alert("请填写正确的准考证号");
          ticketNo.focus();
          isBind = 0;
          return;
        };
        if (check_phone == false || phone.val() == '') {
          alert("请填写正确的手机号"); 
          phone.focus();
          isBind = 0;
          return;
        };
        if(email.val() == '' && phone.val() == ''){
          alert("邮件或邮件请至少填一个");
          email.focus();
          isBind = 0;
          return;
        };
        if (check_email == false) {
          alert("请填写正确的邮箱格式");
          email.focus();
          isBind = 0;
          return;
        };
        if(isBind == 1){
          $(this).unbind("click");
        };
        $.ajax({
          type: "post",
          url: "<?php echo U(ajaxHandle);?>",
          data:{username:name.val(),email:email.val(),phone:phone.val(),ticketNo:ticketNo.val()},
          dataType:"json",
          success: function(res){
            if(res.status == 1){
            window.location.href = "<?php echo U(saveSuccess);?>";
            }else{
              alert("错误");
            }
          }
        })
      })
    </script>
  </body>
</html>