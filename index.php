<?php
ini_set("max_execution_time",8); 
set_time_limit(8); 
header('Access-Control-Allow-Origin:*');
header('Content-type:text/html;charset=utf-8');
//程序入口文件
date_default_timezone_set('Asia/Shanghai');		//设置时区

define('APP_PATH', './Application/');
//开启调试模式
//define('APP_DEBUG', true);
// 生产模式
define('DEV',false);


// 定义模板主题
define("DEFAULT_THEME","default");
// 定义模板文件默认目录
define("TMPL_PATH","./Template/".DEFAULT_THEME."/");

ini_set('max_execution_time','100');	
//引入TP入口文件
include './ThinkPHP/ThinkPHP.php';