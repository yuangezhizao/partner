<?php
namespace Bangdan\Controller;
use \Common\Controller;
class LixinController extends \Common\Controller\BaseController {
		

	
	
	Public function index(){
		$sql = "SELECT * FROM `kdgx_signin`  as s INNER JOIN `kddx_user_openid` as u  ON u.uid=s.uid WHERE  s.`timestamp`>='1484755200' && s.`timestamp`<='1484841599' && u.`public_id` = 'gh_3347961cee42' ORDER BY `timestamp` ASC,`dayscore` DESC  LIMIT 0,30";
		//$sql = "SELECT * FROM `kdgx_signin` WHERE `timestamp`>='1484755200' && `timestamp`<='1484841599' ORDER BY `timestamp` ASC LIMIT 0,100";
		$arr = M()->query($sql);
				var_dump($arr);
	}




	function sql(){

/*
		$sql = "select s.media_id,m.name,s.ad from kdgx_signin_media as s inner join kdgx_media_info as m on s.media_id=m.media_id where s.media_id='gh_3347961cee42' ";
		$arr = M()->query($sql);
		var_dump($arr);*/
		$sql = "select s.media_id,m.name,s.ad from kdgx_signin_media as s inner join kdgx_media_info as m on s.media_id=m.media_id ";
		$arr = M()->query($sql);
		var_dump($arr);exit();
		$arr = M('kdgx_signin')->where("uid=182000 && timestamp>1484931600")->delete();
		if($arr){
			echo M()->getlastsql();
		}
	}


	}