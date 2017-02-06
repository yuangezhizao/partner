<?php 

function html($arr){
    
    $html=<<<HTML
    <!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="description" content="口袋高校，大学生内容创业服务平台专注打造高校新媒体矩阵，最具性价比的校园营销渠道">
    <meta name="description" content="口袋高校，隶属于苏州扶摇信息科技有限公司，是国内领先新媒体运营机构，专注于全国高校新媒体运营和发展。公司旗下拥有近百个矩阵式高校新媒体账号，高校数量及用户数量每天都在稳步上增。口袋高校旗下有“口袋”“微在”及“圈圈”三大校园微信品牌，得天独厚的的高校新媒体资源是开展各项业务的保证，我们希望并将带领全国众多大学生新媒体运营者一起迈向“低成本小型创业”之路。">
    <meta name="keyword" content="口袋, 口袋高校, 内容创业服务平台">
    <title>{$arr['title']}</title>
    <!-- <link rel="stylesheet" type="text/css" href="css/main.css"> -->
    <link rel="stylesheet" type="text/css" href="http://static.pocketuniversity.cn/kdgx/LoveWallTemplete/1.0/css/main.css">
  </head>
  <body>
    <div class="page" id="page">
      <header>
        <img src="http://cdn.pocketuniversity.cn{$arr['image']}">
      </header>
        <div class="content">
          <span>{$arr['titletop']}</span>
          <span>{$arr['titlecon']}</span>
HTML;

    foreach ($arr['aid_arr'] as $v){
        
        switch ($v['type']){
            case 'text':          
                $html .='<p>';
                $html .='<span>'.$v['name'];
                if($v['is_need']==1){
                    $html .='<i></i>';
                }
                $html .='</span><span class="spanInp">';
                $html .='<input type="text" class="inputVal ';
                if($v['is_need']==1){
                        $html .='  need';
                }
                $html .='" name="'.$v['aid'].'" placeholder="'.$v['describe'].' "/></span>';
                $html .='</p>';
                break;
            case 'textarea':
                $html .='<p>';
                $html .='<span>'.$v['name'];
                if($v['is_need']==1){
                    $html .='<i></i>';
                }
                $html .='</span><textarea  class="inputVal ';
                if($v['is_need']=='1'){
                        $html .='need';
                }
                $html .='"  name="'.$v['aid'].'" placeholder="'.$v['describe'].'" ></textarea>';
                $html .='</p>';     
                break;
        }

    }
    $html.=<<<HTML
        <input type='hidden' class='hidden'  value="{$arr['form_id']}">
        </div>
        <footer>
          <span class="confirm1" @click="conMessage"><input type="button" value='发布表白'></span>
          <span id="submit"><input type="submit"  style="position: absolute; opacity: 0" value='发布表白'></span>
          <p>
            <span></span>
            <span style="font-family:arial;">&copy;{$arr['media_name']}</span>
          </p>
        </footer>
      <div class="popUp">
        <div class="popUpContent conLove">
          <span class="delete" @click="cancel"></span>
          <h3>To <span></span></h3>
          <p></p>
          <!-- <button class="conLoveButton conLoveButton0" @click="goConLove">去填写</button>
          <button class="conLoveButton conLoveButton1" @click="confirm">去发布</button> -->
          <button class="conLoveButton conLoveButton0">去填写</button>
          <button class="conLoveButton conLoveButton1">去发布</button>
        </div>
        <div class="popUpContent confirmLove">
          <span class="delete" @click="cancel"></span>
          <h4>温馨提示</h4>
          <span>您不要再预览一遍了吗？</span>
         <!--  <p>
            <button class="popConfirm popConfirm1" @click="confirm">确认发布</button>
            <button class="popVeiw" @click="preview">去预览</button>
    
          </p> -->
        </div>
        <div class="popUpContent confirmLove message">
          <span class="delete" @click="cancel"></span>
          <h4>温馨提示</h4>
          <p>您要表白的信息还未填全哦</p>
        </div>
      </div>
     <!--  <div class="loading">
        <img src="http://static.pocketuniversity.cn/OperatorsApply/src_form/static/images/loading.gif">
      </div> -->
    </div>  
    <script src="http://static.pocketuniversity.cn/kdgx/LoveWallTemplete/1.0/js/lib/vue.js"></script>
    <script src="http://static.pocketuniversity.cn/kdgx/LoveWallTemplete/1.0/js/lib/jquery.min.js"></script>
    <script src="http://static.pocketuniversity.cn/kdgx/LoveWallTemplete/1.0/js/main.js"></script>
  </body>
</html>
    
HTML;
    return $html;
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






















