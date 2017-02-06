<?php
namespace Home\Controller;
use Common\Controller\HomeBaseController;
class IndexController extends HomeBaseController {

private $js1 = <<<EOF

	<head>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	</head>
	<body>
	<div class="js_dialog" id="iosDialog2">
      <div class="weui-mask"></div>
      <div class="weui-dialog">
          <div class="weui-dialog__bd">
EOF;

private $js2 = <<<FOE

</div>
          <div class="weui-dialog__ft">
              <a href="javascript:WeixinJSBridge.call('closeWindow');" class="weui-dialog__btn weui-dialog__btn_primary">确定</a>
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
    public function index(){
        $this->display();
    }
    public function closePage(){
    	echo $this->js1.'注册成功,您的口袋账号是:'.$_GET['uid'].$this->js2;
    	exit();
    }
}