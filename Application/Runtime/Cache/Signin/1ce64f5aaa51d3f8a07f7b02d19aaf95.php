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
    <link rel="stylesheet" href="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/css/friends.css">
	<!-- 应用的样式依赖 end-->
  </head>
  <body>
    <div class="page" id="page">
        <div class="appWrap appReady">  
          <div class="appLogo"> 
            <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/appLoading.gif" alt="早起打卡" width="100" height="100">
          </div>
        </div> 

		<div class="myFriends">
            <div class="js_dialog" id="iosDialog2" @click="cardCancle" style="display:none;opacity:1;">
                <div class="weui-mask"></div>
                <div class="sharePoint">
                    <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/wxShare.png" alt="" width="40" height="auto">
                    <p>点击右上角，分享给朋友</p>
                </div> 
            </div>
            <div class="head_img">
                <img :src="headimgurl" alt="用户头像" width="50" height="auto">
            </div>
            <div class="title_desc">
                <p>我已坚持打卡
                    <span style="padding:0 5px;font-size:1rem;color:rgba(58,190,100,1);text-decoration: underline;" v-text="count"></span>
                    天，来和我一起早起吧!
                </p>
            </div>
            <div class="content">
                <div class="content_head">
                    <h4>当前TA的早起好友数量: <span style="color:rgba(58,190,100,1)" v-text="num"></span></h4> 
                </div>
                <div class="content_body maxHeight">
                    <div class="friends_list" v-for="list in data.lists">
                        <span class="headImg">
                            <img :src="list.headimgurl | noHeadImg" alt="头像" width="36" height="auto">
                        </span>
                        <span class="nickName" v-text="list.nickname | noNickName"></span> 
                    </div>
<!--                     <div class="friends_list">
                        <span class="headImg">
                            <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/sure.jpg" alt="头像" width="36" height="auto">
                        </span>
                        <span class="nickName">少年·Sure</span>
                    </div>
                    <div class="friends_list">
                        <span class="headImg">
                            <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/sure.jpg" alt="头像" width="36" height="auto">
                        </span>
                        <span class="nickName">少年·Sure</span>
                    </div> -->
<!--                     <div class="friends_list">
                        <span class="headImg">
                            <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/sure.jpg" alt="头像" width="36" height="auto">
                        </span>
                        <span class="nickName">少年·Sure</span>
                    </div> -->
<!--                     <div class="friends_list">
                        <span class="headImg">
                            <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/sure.jpg" alt="头像" width="36" height="auto">
                        </span>
                        <span class="nickName">少年·Sure</span>
                    </div> -->
                    <div class="tips">
                        <div class="tips_logo">
                            <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/less.png" alt="好友太少" width="30" height="auto"> 
                        </div>
                        <div class="tips_desc">
                            <p v-if="!type">你的好友太少啦！快来分享给好友一起早起吧~</p>
                            <p v-if="type">他的好友太少啦！快来和他一起早起吧~</p> 
                        </div>
                    </div>
                </div> 
                <div class="content_foot" @click="showMore">
                    <div class="showMore">
                        <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/down.png" alt="展开更多" width="24" height="auto">
                    </div>
                </div> 
            </div>
            <div class="footer" v-if="type">
                <a :href="'http://'+ pocketHost + '/index.php/Signin/index/index?media_id=' + media_id">
                    <div class="joinUs">
                        <span><img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/morning.png" alt="logo" width="20" height="auto"></span>
                        <span>一起打卡</span>
                    </div>
                </a>
            </div>
            <div class="footer" v-if="!type">
                <div class="joinUs" @click="invite">
                    <span><img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/morning.png" alt="logo" width="20" height="auto"></span>
                    <span>邀请好友</span>
                </div>
            </div>
        </div>
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

    	/*头像过滤器 A*/
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
            showAll:false,
            state:false,
            pocketHost:"www.pocketuniversity.cn",
            uid:"",
            media_id:"gh_3347961cee42",
    	    data:{
    	      "lists":[]
    	    },
            count:"0",
            num:"0",
            type:"", 
            headimgurl:"",
    	    dayRank:{
    	      "lists":[] 
    	    }
    	  },
    	  ready:function (){ 
    	    var _this = this;
    	    console.log('ready');
    	    var shareType = _this.getUrlStr('type');
    	    if (shareType == 'onMenuShareTimeline') {
    	      console.log('分享朋友圈');
    	    }else if(shareType == 'onMenuShareAppMessage'){
    	      console.log('分享给好友');
    	    }else{
    	      console.log('正常进入'); 
    	    };
    	    this.diyShare(); 
    	    //this.rankScroll(); 
    	  },
    	  attached:function (){
            var _this = this;
            _this.pocketHost = window.location.host;
            this.getFriends();
    	    // this.getKeywords();
    	    // this.getDayScore(); 
    	    // this.getPublicIfo(); 
    	    // this.getRanks();
    	    // this.getMyCard();
    	  },
    	  methods:{ 
            invite:function (){
                var _this = this;
                // _this.state = true; 
                $("#iosDialog2").show();
                this.sharePoint = true;
                console.log("邀请好友"); 
            },
            showMore:function (){
                var _this = this;
                _this.showAll = !_this.showAll;
                console.log(_this.showAll);
                if (_this.showAll) {
                    $('.content .content_body').removeClass('maxHeight');
                    $('.content_foot .showMore').css({
                        "transform":"rotate(180deg)",
                        "-webkit-transform":"rotate(180deg)"
                    });
                } else{
                    $('.content .content_body').addClass('maxHeight');
                    $('.content_foot .showMore').css({ 
                        "transform":"rotate(360deg)",
                        "-webkit-transform":"rotate(360deg)"
                    });
                };
            },
    	    /*获取URL参数*/
    	    getUrlStr:function (name){
    	      var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    	      var r = window.location.search.substr(1).match(reg);
    	      if(r!=null)return  unescape(r[2]); 
    	      return null;
    	    }, 
            //声明应用回调 
            appCallback:function (argument) {
              setTimeout(function (){ 
                $('.myFriends').fadeIn();
                $('.appWrap').fadeOut();   
              },500);
            },
            getFriends:function (){
                var _this = this;
                // _this.getUrlStr('uid');
                _this.type = _this.getUrlStr('type');
                _this.uid = _this.getUrlStr('uid');
                _this.media_id = _this.getUrlStr('media_id');
                var taUid = _this.getUrlStr("uid"); 
                $.ajax({
                  url:"/index.php/Signin/Share/friendshare",
                  type:"POST",
                  dataType:"JSON",
                  data:{
                    "uid":taUid
                  }, 
                  success:function (data){
                    _this.appCallback();
                    if (data.errcode == 0) {
                      console.log(data);
                      _this.data.lists = data.errmsg.data;
                      _this.count = data.errmsg.count;
                      _this.num = data.errmsg.num;
                      _this.headimgurl = data.errmsg.headimgurl;
                    } else{
                      console.log("error");
                    };
                  },
                  error:function (msg){ 
                    alert("error");
                  }
                });
            },
    	    /*微信自定义分享*/
    	    diyShare:function (){ 
    	      var _this = this;
              if (!_this.headimgurl) {
                _this.headimgurl = "http://static.pocketuniversity.cn/kdgx/Lib/commonImg/nohead.jpg"
              };
    	      console.log('diyShare');
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
                  var pocketHost = window.location.host;
                  console.log(pocketHost); 
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
    	                  title: '我已经加入寒假早起计划，你也加入我们吧，别再颓废了!', // 分享标题
    	                  link: pocketHost + '/index.php/Signin/share/friends?uid=' + _this.uid + '&media_id=' + _this.media_id + '&type=1', // 分享链接
    	                  imgUrl: _this.headimgurl, // 分享图标
    	                  success: function () { 
    	                      alert('分享朋友圈成功');
                              _this.cardCancle();
                              $.ajax({
                                url:"/index.php/Signin/index/setBehavior",
                                type:"POST",
                                dataType:"JSON",
                                data:{ 
                                  "id":"20"
                                },
                                success:function (data){
                                  console.log(data); 
                                },
                                error:function (msg){
                                  console.log(msg);
                                }
                              }); 
    	                  },
    	                  cancel: function () {    
    	                      alert("取消分享"); 
    	                  }
    	              }); 
    	              wx.onMenuShareAppMessage({
    	                  title: '我已经加入寒假早起计划，你也加入我们吧，别再颓废了!', // 分享标题
    	                   desc: ShareDesc, // 分享描述
    	                   link: pocketHost + '/index.php/Signin/share/friends?uid=' + _this.uid + '&media_id=' + _this.media_id + '&type=2', // 分享链接
    	                   imgUrl: _this.headimgurl, // 分享图标
    	                   type: '', // 分享类型,music、video或link，不填默认为link
    	                   dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
    	                   success: function () { 
    	                       alert('分享好友成功'); 
                               _this.cardCancle();
                               $.ajax({
                                 url:"/index.php/Signin/index/setBehavior",
                                 type:"POST",
                                 dataType:"JSON", 
                                 data:{
                                   "id":"19"
                                 },
                                 success:function (data){
                                   console.log(data);
                                 },
                                 error:function (msg){
                                   console.log(msg);
                                 }
                               }); 
    	                   },
    	                   cancel: function () { 
    	                       alert("取消分享"); 
    	                   } 
    	              });
                      wx.hideAllNonBaseMenuItem();
                      // if (_this.uid != null) { 
                        wx.showMenuItems({
                            menuList: ['menuItem:share:appMessage','menuItem:share:timeline'] // 要隐藏的菜单项，只能隐藏“传播类”和“保护类”按钮，所有menu项见附录3
                        });
                      // } else{
                      //   console.log("无");
                      // };
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