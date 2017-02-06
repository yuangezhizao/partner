<?php
return array(
  //常用路径设置
  'TMPL_PARSE_STRING'   =>  array(            //静态资源路径
    '__HOME_CSS__'    =>  __ROOT__.trim(TMPL_PATH,'.').'Home/Public/css',   //home主题的css
    '__HOME_JS__'   =>  __ROOT__.trim(TMPL_PATH,'.').'Home/Public/js',    //home主题的js
    '__HOME_IMG__'    =>  __ROOT__.trim(TMPL_PATH,'.').'Home/Public/images',  //Admin主题的img
    '__HOME_M_CSS__'  =>  __ROOT__.trim(TMPL_PATH,'.').'Home/m/Public/css',   //home主题的css
    '__HOME_M_JS__'   =>  __ROOT__.trim(TMPL_PATH,'.').'Home/m/Public/js',    //home主题的js
    '__HOME_M_IMG__'  =>  __ROOT__.trim(TMPL_PATH,'.').'Home/m/Public/images',  //Admin主题的img
  ),
  // 'SHOW_PAGE_TRACE'    =>  true,
  'WEB_SITE'        => 'test.pocketuniversity.cn',    //站点域名
  'WEB_STATIC'      => 'static.pocketuniversity.cn',  //静态资源站点域名
  'CDN_SITE'        => 'test.pocketuniversity.cn',    //CDN站点域名
  //微信配置文件
  'PUBLIC_ID'       =>  'gh_243fe4c4141f',
  'THINK_WECHAT'    => array(
    'APP_ID'        =>  'wx3f6b8b2eb0483f2f', //微信分配的appID
    'APP_SECRET'    =>  '6861d005542696913ed31a9645690595', //微信分配的key
    'REDIRECT_URI'  =>  'http%3a%2f%2ftest.pocketuniversity.cn%2findex.php%2fhome%2fuser%2fauthlogin',  //微信重定向地址
    'REDIRECT'      =>  'http%3a%2f%2ftest.pocketuniversity.cn%2findex.php%2fhome%2fuser%2fscopelogin', //微信非静默重定向地址
    'PUBLIC_ID'     =>  'gh_243fe4c4141f',  //公众号的原始public_id
    ),
  'DEFAULT_MODULE' => 'Home',
  'KEY_URL' => 'pocketuniversity',
);