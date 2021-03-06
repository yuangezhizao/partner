<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
  <head> 
    <meta charset="UTF-8">
    <meta name="author" content="Sure" email="Sure@tuiyilin.com">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <meta content="yes" name="apple-mobile-web-app-capable" />
    <meta content="black" name="apple-mobile-web-app-status-bar-style" />
    <meta content="telephone=no" name="format-detection" />
    <title>早起打卡</title><!-- 后期可自定义 -->
    <!-- 应用的样式依赖 start-->
    <link rel="stylesheet" href="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/css/lib/weui.min.css">
	<link rel="stylesheet" href="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/css/main.css">
    <style>
        html,body,.page,.myCard{
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        body:before{
            content:"";
            width:100%;
            /*height:100%;*/
            font-family:"微软雅黑";
            position: absolute;
            top: 0;
            bottom:0;
            left:0;
            right: 0;
            z-index: -999;
            /*color:rgba(0,0,0,.6);*/
            background:url(http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/myCard_bg3.png) no-repeat;
            background-position: center center;
            background-size: cover;
            /*background-attachment: fixed;*/
        }
        .cardHeadImg{
            width: 70px;
            height: 70px; 
            top:40px; 
            margin-left: -35px;
        }
        .cardContent{
            top:85px; 
            height:130px;
        }
        .tabBox{
            padding: 20px 4%;
            /*width: 26%;*/
            padding-right: 1%;
            box-sizing:border-box;
            text-align: center;
        }
        .tabBox span:nth-child(1){
            margin-bottom: 10px;
            font-size:.8rem; 
            color:rgba(0,0,0,.6);
        }
        .tabBox span:nth-child(2){ 
            font-size: 1.5rem;
        }
        .tabBox:nth-child(1){ 
            width: 31%;
        }
        .tabBox:nth-child(2){
            width:34%;
        }
        .tabBox:nth-child(3){
            width: 23%;
        }
        .tabBox:nth-child(3){
            padding-right: 0;
        }
        .myCard{
            width: 100%;
            height: 100%;
            position: absolute;
            background:rgba(0,0,0,0);
        }
        .keywords_one{
        	width:100%;
        	height: auto;
        	position: absolute;
        	top:260px;
        	left:0;
        	/*z-index: -999;*/
        }
        .keywords_one img{
        	display: block;
        	width: 270px;
        	height: auto;
        	margin:0 auto;
        }
        .keywords_two{
        	width:100%;
        	height: auto;
        	position: absolute;
        	bottom:130px;
        	left:0;
        	/*z-index: -999;*/
        }
        .keywords_two img{
        	display: block;
        	width: 150px;
        	height: auto;
        	margin:0 auto;
        }
        .footerBtn{
            width: 140px;
            height: 30px;
            line-height: 30px;
            position: absolute;
            bottom: 40px;
            left: 50%;
            padding: 5px;
            font-size: 1.2rem;
            margin-left: -75px;
            text-align: center;
            border: 1px solid rgba(255,255,255,1);
            border-radius: 10px;
            color: rgba(255,255,255,1);
            background-color: rgba(255,255,255,.5);
        }
        footer{
            width:100%; 
            height: 1px;
            padding:0;
            position:absolute; 
            left: 0;
            bottom: 0px;
            background-color:rgba(7,64,55,1);
        }
        .appWrap {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background-color: #fff;
            top: 0; 
            left: 0;
            bottom: 0;
            right: 0;
        }
        .appLogo{
            width: 100px;
            height: 100px;
            position: relative;
            top:50%; 
            left: 50%;
            margin-left:-50px;
            margin-top:-70px;
            /*transform:translate(-50%, -50%);*/
        }
        .appWrap img{
            display: block;
            width: 100px;
            height: 100px;
        }
        .page{
            display: none;
            width:100%;
            height: auto;
            overflow-y: scroll;
        }
    </style>
	<!-- 应用的样式依赖 end-->
  </head>
  <body>
    <div class="appWrap appReady">  
      <div class="appLogo"> 
        <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/appLoading.gif" alt="早起打卡" width="100" height="100">
      </div>
    </div> 
    <div class="page" id="page">
        <div class="myCard">
            <div class="cardHeadImg">
                <img :src="ShareData.headimgurl | noHeadImg" alt="" width="70" height="70">
            </div>
            <div class="cardContent">
                <div class="tabBox">
                    <span>起床时间</span>
                    <span v-if="ShareData.timestamp != 'null'" v-text="ShareData.timestamp | timer"></span>
                    <span v-if="ShareData.timestamp == 'null'">尚未打卡</span>
                </div>
                <div class="tabBox">
                    <span>连续早起天数</span> 
                    <span v-if="ShareData.count" v-text="ShareData.count"></span>
                    <span v-if="!ShareData.count">--</span>
                </div>
                <div class="tabBox">
                    <span>今日排名</span> 
                    <span v-if="ShareData.count" v-text="ShareData.ranking"></span> 
                    <span v-if="!ShareData.count">--</span> 
                </div> 
            </div> 
        </div>
        <div class="keywords_one" v-if="1">
        	<img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/keywords_1.png" alt="每次坚持和努力都是为了梦想" width="270" height="auto">
        </div>
        <div class="keywords_two">
        	<img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/keywords_2.png" alt="每次坚持和努力都是为了梦想" width="150" height="auto">

        </div>
        <div class="footerBtn">
            <a :href="'http://' + pocketHost + '/index.php/Signin/index/index?media_id=' + ShareData.media_id"> 
                <span style="color:#fff">立刻加入打卡</span>
            </a>
        </div>
        <footer></footer> 
    </div>  
    <!-- 应用脚本依赖 start-->
    <script type="text/javascript" src="http://tajs.qq.com/stats?sId=59591163" charset="UTF-8"></script>
    <script src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/js/lib/jquery.min.js"></script>
    <script src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/js/lib/vue.min.js"></script>
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script>
    	/*日期过滤器 A*/
    	Vue.filter('thatDate', function (value) { 
    	  var timestemp = parseInt(value);
    	  var date = new Date(timestemp);
    	  return date.getFullYear() + '年' + (date.getMonth()+1) + '月' + date.getDate() + '日';
    	});

    	/*头像过滤器 A*/
    	Vue.filter('noHeadImg', function (value) {
    	  if (!value) {
    	    return 'http://static.pocketuniversity.cn/kdgx/Lib/commonImg/nohead.jpg';
    	  } else{
    	    return value; 
    	  }
    	});

    	/*昵称过滤器 A*/
    	Vue.filter('noNickName', function (value) {
    	  if (!value) {
    	    return '某同学';
    	  } else{
    	    return value;
    	  }
    	});

    	/*星期过滤器 B*/
    	Vue.filter('thatDay', function (value) {
    	  var timestemp = parseInt(value*1000);
    	  var date = new Date(timestemp);
    	  if (date.getDay() == 0) {
    	    return '日';
    	  } else if(date.getDay() == 1){
    	    return '一';
    	  }else if (date.getDay() == 2) {
    	    return '二';
    	  } else if(date.getDay() == 3){
    	    return '三';
    	  }else if (date.getDay() == 4) {
    	    return '四';
    	  } else if(date.getDay() == 5){
    	    return '五';
    	  }else if (date.getDay() == 6) {
    	    return '六';
    	  };
    	}); 

    	/*秒钟 过滤器*/
    	Vue.filter('timer', function (value) {
    	  var timestemp = parseInt(value)*1000;
    	  var date = new Date(timestemp);
    	  var HH = date.getHours();
    	  var MM = date.getMinutes();
    	  var SS = date.getSeconds();
    	  if (date.getHours() < 10) {
    	    HH = '0' + date.getHours();
    	  }
    	  if (date.getMinutes() < 10) {
    	    MM = '0' + date.getMinutes();
    	  };
    	  if (date.getSeconds() < 10) {
    	    SS = '0' + date.getSeconds();
    	  };
    	  // console.log(date.getFullYear() + '.' + date.getMonth() + '.' + date.getDate());
    	  return HH + ':' + MM + ':' + SS;
    	});

    	/*分钟 过滤器*/
    	Vue.filter('markTime', function (value) {
    	  var timestemp = parseInt(value)*1000;
    	  var date = new Date(timestemp);
    	  var HH = date.getHours();
    	  var MM = date.getMinutes();
    	  if (date.getHours() < 10) {
    	    HH = '0' + date.getHours();
    	  }
    	  if (date.getMinutes() < 10) {
    	    MM = '0' + date.getMinutes();
    	  };
    	  return HH + ':' + MM;
    	});
    	 
    	/*状态过滤器*/
    	Vue.filter('state', function (value) { 
    	  console.log(value);
    	});

    	/*Vue 数据注入*/
    	var pageData = new Vue({
    	  el: '#page',
    	  data: {
    	    outTime:false, 
    	    showSchool:false,
    	    sharePoint:false,
            host:"http://www.pocketuniversity.cn",
            pocketHost:"www.pocketuniversity.cn",
            key:"",
    	    isOutTime:"签到已结束",
    	    cardMessage:{},
            ShareData:{},
    	    timeMsg:"", 
    	    state:"",
    	    energy:"",
    	    nowDate:"",
    	    media_id:"",
    	    headurl:"",
    	    timestamp:"",
    	    count:"",
    	    ranking:"",
    	    public_name:""
    	  },
    	  ready:function (){
    	    var _this = this;
            _this.getShareData();
    	    console.log('ready'); 
    	    var shareType = _this.getUrlStr('type');
    	    if (shareType == 'onMenuShareTimeline') {
    	      console.log('分享朋友圈');
    	    }else if(shareType == 'onMenuShareAppMessage'){
    	      console.log('分享给好友');
    	    }else{
    	      console.log('正常进入');
    	    };
    	    // this.diyShare(); 
    	    //this.rankScroll();
    	  },
    	  attached:function (){  
    	  	var _this = this;
            _this.pocketHost = window.location.host;
            _this.key = _this.getUrlStr('key'); 
    	  	// encodeURIComponent(location.href.split('#')[0])
    	  	_this.media_id = _this.getUrlStr('media_id');
    	    _this.headurl = _this.getUrlStr('headurl');
    	  	_this.timestamp = _this.getUrlStr('timestamp');
            console.log(_this.timestamp);
    	  	_this.count = _this.getUrlStr('count');
    	  	_this.ranking = _this.getUrlStr('ranking'); 
            // if(_this.media_id){

            // }
    	    // this.getKeywords();
    	    // this.getDayScore(); 
    	    // this.getPublicIfo(); 
    	    // this.getRanks();
    	    // this.getMyCard();
    	  },
    	  methods:{ 
            //声明应用回调 
            appCallback:function (argument) {
              setTimeout(function (){ 
                $('.page').fadeIn(); 
                $('.appWrap').fadeOut();   
              },500);
            },
            /*获取分享数据*/
            getShareData:function (){
                var _this = this;
              $.ajax({
                url:"/index.php/Signin/share/share",
                type:"GET", 
                data:{
                    "key":_this.key 
                },
                dataType:"JSON",
                success:function (data){
                  _this.appCallback(); 
                  _this.diyShare();
                  // console.log(data);
                  if (data.errcode == 0) {
                    _this.ShareData = data.errmsg;
                    console.log(data);
                  } else{
                    console.log("error");
                  };
                },
                error:function (msg){ 
                  alert("error");
                }
              });
            }, 
    	    /*获取URL参数*/
    	    getUrlStr:function (name){
    	      var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    	      var r = window.location.search.substr(1).match(reg);
    	      if(r!=null)return  unescape(r[2]); 
    	      return null;
    	    },
    	    /*微信自定义分享*/
    	    diyShare:function (){
    	      var _this = this;
              var shareUrl = location.href;
              if (!_this.ShareData.nickname) {
                _this.ShareData.nickname = "某同学"
              };
              if (!_this.ShareData.headimgurl) {
                _this.ShareData.headimgurl = "http://static.pocketuniversity.cn/kdgx/Lib/commonImg/nohead.jpg"
              };
              var ShareDesc = "感谢现在奋斗的自己！";
              var ShareRandom = Math.random();
              if (ShareRandom < 0.2) {
                ShareDesc = "一起来打卡，永远元气满满！";
                console.log(ShareDesc);
              } else if(ShareRandom > 0.2 && ShareRandom < 0.4){
                ShareDesc = "全新一天，我们一起加油!"; 
                console.log(ShareDesc);
              }else if(ShareRandom > 0.4 && ShareRandom < 0.6){
                ShareDesc = "fighting，新的一天!";
                console.log(ShareDesc);
              }else if(ShareRandom > 0.6 && ShareRandom < 0.8){
                ShareDesc = "生命不止，奋斗不息!";
                console.log(ShareDesc);
              }else{
                ShareDesc = "越努力，越幸福!";
                console.log(ShareDesc);
              };
    	      $.ajax({
    	        url:"/wechatShare.php",
    	        type:"POST",
    	        data:{
    	          "url":location.href.split('#')[0]
    	        },
    	        dataType:"JSON",
    	        success:function (data){  
    	          console.log(data); 
    	          wx.config({
                      debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                      appId: data.appId, // 必填，公众号的唯一标识
                      timestamp: data.timestamp, // 必填，生成签名的时间戳
                      nonceStr: data.nonceStr, // 必填，生成签名的随机串
                      signature: data.signature,// 必填，签名，见附录1
                      jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','showMenuItems','hideAllNonBaseMenuItem'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
                  });  
    	          wx.ready(function(){
                      wx.onMenuShareTimeline({
                          title: '今天' + _this.ShareData.nickname + '是全国高校第' + _this.ShareData.ranking + '位起床的，准备好迎接新的一天吧', // 分享标题
                          link: shareUrl, // 分享链接
                          imgUrl: 'http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/logo.jpg', // 分享图标
                          success: function () { 
                              alert('分享朋友圈成功');
                          },
                          cancel: function () {   
                              alert("取消分享"); 
                          }
                      }); 
                      wx.onMenuShareAppMessage({ 
                          title: '今天' + _this.ShareData.nickname + '是全国高校第' + _this.ShareData.ranking + '位起床的，敢不敢接受挑战呢？', // 分享标题 
                           desc: ShareDesc, // 分享描述
                           link: shareUrl,// 分享链接
                           imgUrl: _this.ShareData.headimgurl, // 分享图标
                           type: '', // 分享类型,music、video或link，不填默认为link
                           dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                           success: function () { 
                               alert('分享好友成功'); 
                           },  
                           cancel: function () { 
                               alert("取消分享"); 
                           } 
                      });
                      wx.hideAllNonBaseMenuItem();
                      console.log(_this.ShareData.uid);
                      if (!_this.ShareData.uid) { 
                        wx.showMenuItems({
                            menuList: ['menuItem:share:appMessage','menuItem:share:timeline'] // 要隐藏的菜单项，只能隐藏“传播类”和“保护类”按钮，所有menu项见附录3
                        });
                      } else{
                        console.log("无");
                      };
                  }); 
    	        },
    	        error:function (msg){ 
    	          alert("error");
    	        }
    	      });
    	    },
    	    /*彩蛋 测试专用*/
    	    colorEgg:function (){
    	      console.log('hhh');
    	      // $('#iosDialog2').show();
    	    },
    	    wxShare:function (){
    	      console.log('share');
    	      this.sharePoint = true;
    	    },
    	    getMyCard:function (){
    	      var _this = this;
    	      $.ajax({
    	        url:"/index.php/Signin/index/card",
    	        type:"GET",
    	        dataType:"JSON",
    	        success:function (data){
    	          // console.log(data);
    	          if (data.errcode == 0) {
    	            _this.cardMessage = data.errmsg;
    	            console.log(_this.cardMessage);
    	          } else{
    	            console.log("error");
    	          };
    	        },
    	        error:function (msg){ 
    	          alert("error");
    	        }
    	      });
    	    },
    	    /*取消成就卡蒙板*/
    	    cardCancle:function (){ 
    	      $("#iosDialog2").hide();
    	      this.sharePoint = false;
    	    }
    	  }
    	})
    </script>
  </body>
</html>