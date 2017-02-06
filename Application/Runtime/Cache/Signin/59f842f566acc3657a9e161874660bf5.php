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
    <link rel="stylesheet" href="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/css/school.css">
    <!-- 应用的样式依赖 end-->
  </head>
  <body>
    <div class="page" id="page"> 
        <div class="appWrap appReady">  
          <div class="appLogo"> 
            <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/appLoading.gif" alt="早起打卡" width="100" height="100">
          </div>
        </div> 
        <div class="mySchool">
            <div class="js_dialog" @click="cardCancle" id="iosDialog2" style="display:none;opacity:1;">
                <div class="weui-mask"></div>
                <div class="sharePoint">
                    <img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/wxShare.png" alt="" width="40" height="auto">
                    <p>点击右上角，分享给朋友</p>
                </div> 
            </div>

            <div class="school_title"> 
                <h4><span v-text="nickName | noNickName"></span>&nbsp;参与了</h4> 
            </div>
            <div class="shool_name">
              <span><img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/school.png" alt="学校Logo" width="45" height="auto"></span>
              <span v-text="schoolName"></span>
            </div>
            <div class="school_desc">
              <span>全国高校</span><span>早起打卡计划</span>
            </div>
            <div class="school_content">
              <div class="data_lists">
                <div class="data_list">
                  <span><img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/rank_logo.png" alt="学校排名" width="42" height="auto"></span>
                  <span>当前全国高校排名：<b class="green" v-text="schoolRanking"></b> 名</span>
                </div>
                <div class="data_list">
                  <span><img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/school_logo.png" alt="打卡次数" width="42" height="auto"></span>
                  <span>全校累计打卡数：<b class="green" v-text="schoolCount"></b> 次</span>
                </div> 
                <div class="data_list">
                  <span><img src="http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/energy_logo.png" alt="累计能量" width="42" height="auto"></span>
                  <span>全校累计正能量：<b class="green" v-text="schoolScore"></b> g</span>
                </div>
              </div>
              <div class="lists_foot"> 
                <div class="fans_heads">
                  <span v-for="list in schoolInfo.user"><img :src="list.headimgurl | noHeadImg" alt="粉丝头像" width="36" height="auto"></span>
<!--                   <span><img :src="headimgurl" alt="粉丝头像" width="32" height="auto"></span>
                  <span><img :src="headimgurl" alt="粉丝头像" width="32" height="auto"></span>
                  <span><img :src="headimgurl" alt="粉丝头像" width="32" height="auto"></span>
                  <span><img :src="headimgurl" alt="粉丝头像" width="32" height="auto"></span>
                  <span><img :src="headimgurl" alt="粉丝头像" width="32" height="auto"></span> -->
                </div>
                <div class="fans_foot">
                  <p>...... 等<span v-text="schoolNumber"></span>位校友已加入</p> 
                </div>
              </div>
            </div>
            <div class="school_foot" v-if="type == 1 || type == 2"> 
              <a :href="'http://'+ pocketHost + '/index.php/Signin/index/index?media_id=' + media_id">
                <span>为母校攒能量</span>   
              </a>
            </div>
            <div class="school_foot" v-if="!type"> 
                <span @click="invite">为母校攒能量</span>  
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
            uid:"", 
            media_id:"gh_3347961cee42",
            pocketHost:"www.pocketuniversity.cn",
            schoolInfo:{
              "user":[]
            },
            nickName:"某同学",
            schoolRanking:"0",
            schoolCount:"0",
            schoolNumber:"0",
            schoolName:"杭州电子科技大学",
            schoolScore:"0",
            data:{
              "lists":[]
            },
            count:"0",
            num:"0",
            type:"0",
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
            //this.rankScroll();
          }, 
          attached:function (){ 
            var _this = this;
            _this.type = _this.getUrlStr('type');
            _this.uid = _this.getUrlStr('uid');
            _this.media_id = _this.getUrlStr('media_id');
            _this.pocketHost = window.location.host;
            _this.getSchoolInfo();
          },
          methods:{ 
            invite:function (){ 
                var _this = this;
                // _this.state = true; 
                $("#iosDialog2").show();
                this.sharePoint = true;
                console.log("邀请好友"); 
            },
            //声明应用回调 
            appCallback:function (argument) {
              setTimeout(function (){ 
                $('.mySchool').fadeIn(); 
                $('.appWrap').fadeOut();   
              },500);
            },
            getSchoolInfo:function (appCallback){
              var _this = this;
              // _this.uid = _this.getUrlStr('uid');
              // _this.media_id = _this.getUrlStr('media_id');
              var taUid = _this.getUrlStr("uid"); 
              $.ajax({
                url:"/index.php/Signin/share/schoolShare",
                type:"POST",
                dataType:"JSON",
                data:{
                  "uid":_this.uid
                }, 
                success:function (data){
                  _this.diyShare(); 
                  _this.appCallback();
                  if (data.errcode == 0) {
                    console.log(data);
                    _this.schoolInfo.user = data.errmsg.user;
                    _this.schoolCount = data.errmsg.count;
                    _this.schoolNumber = data.errmsg.number;
                    _this.schoolName = data.errmsg.school;
                    _this.schoolScore = data.errmsg.score;
                    _this.schoolRanking = data.errmsg.ranking;
                    _this.nickName = data.errmsg.nickname;
                  } else{
                    console.log("error");
                  };
                },
                error:function (msg){ 
                  alert("error");
                }
              });
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
                          title: '我和' + _this.schoolNumber + '个小伙伴都在打卡,为' + _this.schoolName + '积攒正能量，现全国排名' + _this.schoolRanking + '！', // 分享标题
                          link: pocketHost + '/index.php/Signin/share/school?uid=' + _this.uid + '&media_id=' + _this.media_id + '&type=1', // 分享链接
                          imgUrl: 'http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/logo.jpg', // 分享图标
                          success: function () { 
                              alert('分享朋友圈成功');
                              _this.cardCancle();
                              $.ajax({
                                url:"/index.php/Signin/index/setBehavior",
                                type:"POST",
                                dataType:"JSON",
                                data:{ 
                                  "id":"22"
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
                          title: '我和' + _this.schoolNumber + '个小伙伴都在打卡,为' + _this.schoolName + '积攒正能量，现全国排名' + _this.schoolRanking + '！', // 分享标题
                           desc: ShareDesc, // 分享描述
                           link: pocketHost + '/index.php/Signin/share/school?uid=' + _this.uid + '&media_id=' + _this.media_id + '&type=2', // 分享链接
                           imgUrl: 'http://static.pocketuniversity.cn/kdgx/EveryDay/1.0/images/logo.jpg', // 分享图标
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
                                   "id":"21"
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