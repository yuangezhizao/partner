<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="description" content="口袋高校，大学生内容创业服务平台专注打造高校新媒体矩阵，最具性价比的校园营销渠道">
    <meta name="description" content="口袋高校，隶属于苏州扶摇信息科技有限公司，是国内领先新媒体运营机构，专注于全国高校新媒体运营和发展。公司旗下拥有近百个矩阵式高校新媒体账号，高校数量及用户数量每天都在稳步上增。口袋高校旗下有“口袋”“微在”及“圈圈”三大校园微信品牌，得天独厚的的高校新媒体资源是开展各项业务的保证，我们希望并将带领全国众多大学生新媒体运营者一起迈向“低成本小型创业”之路。">
    <meta name="keyword" content="口袋, 口袋高校, 内容创业服务平台">
    <meta name="format-detection" content="telephone=no">
    <title></title>
    <link rel="stylesheet" href="https://res.wx.qq.com/open/libs/weui/1.0.1/weui.min.css">
    <script src="http://static.pocketuniversity.cn/kdgx/lib/utils/utils.js"></script>
    <script type="text/javascript" src="http://tajs.qq.com/stats?sId=59591163" charset="UTF-8"></script>
  </head>
  <body>
    <app></app>
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
  <script type="text/javascript" src="http://static.pocketuniversity.cn/kdgx/RoommateHistoryTest/publish/js/bundle.js"></script>
  <script>

    var xhr = new XMLHttpRequest()
    xhr.onreadystatechange = function () {
      if(xhr.readyState != 4) {
        return
      }
      if(xhr.status != 200) {
        alert('error')
        return
      }

      var res = JSON.parse(xhr.responseText)
      // window.innerWx = wx
      wx.config({
          debug: false,
          appId: res.appId,
          timestamp: res.timestamp,
          nonceStr: res.nonceStr,
          signature: res.signature,
          jsApiList: [
            'onMenuShareTimeline',
              'onMenuShareAppMessage',
            'showMenuItems',
              'hideAllNonBaseMenuItem'
          ]
      })

      wx.ready(function () {
          
          console.log('success')
      })
      wx.error(function () {
          console.log('error')
      })
    }
    xhr.open("POST", "/wechatShare.php")
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded;")
    xhr.send("url=" + encodeURIComponent(location.href.split('#')[0]))
  
  </script>
  </body>
</html>