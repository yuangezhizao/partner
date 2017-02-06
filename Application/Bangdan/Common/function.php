<?php

/* //网页授权
function get_code(){
$appid="wx34f6baf7794f38b1";
$secret="66c18a049b225967cf248062078c7bc8";
$redirect_url="http%3A%2F%2Fwww.pocketuniversity.cn%2Findex.php%2Fbangdan";
$code=$_GET['code'];
	if($code){
		$url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
		$json=https_request($url);
		$openid=$json->openid;
		$url="";
		https://open.weixin.qq.com/connect/qrconnect?appid=wxbdc5610cc59c1631&redirect_uri=https%3A%2F%2Fpassport.yhd.com%2Fwechat%2Fcallback.do&response_type=code&scope=snsapi_login&state=3d6be0a4035d839573b04816624a415e#wechat_redirect
		https://open.weixin.qq.com/connect/qrconnect?appid=wx34f6baf7794f38b1&redirect_uri=http%3A%2F%2Fwww.pocketuniversity.cn%2Findex.php%2Fbangdan&response_type=code&scope=snsapi_login&state=3d6be0a4035d839573b04816624a415e#wechat_redirect
	}else{
		echo "<script>location.href='https://open.weixin.qq.com/connect/qrconnect?appid=$appid&redirect_uri=$redirect_url&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect'</script>";
	}
} */
/**
 * 
 * @param unknown $xml
 * @return mixed
 */
function xmlToArray($xml){

	//禁止引用外部xml实体

	libxml_disable_entity_loader(true);

	$xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

	$val = json_decode(json_encode($xmlstring),true);

	return $val;

}
/**
 *	xml转json
 * @param unknown $source
 * @return string
 */
function xml_to_json($source) {
	if(is_file($source)){ //传的是文件，还是xml的string的判断
		$xml_array=simplexml_load_file($source);
	}else{
		$xml_array=simplexml_load_string($source);
	}
	$json = json_encode($xml_array); //php5，以及以上，如果是更早版本，请查看JSON.php
	return $json;
}
function get_access_token($public_id){
	
	     $sql="SELECT * FROM `kddx_public` WHERE public_id='$public_id'";
             $query=mysql_query($sql);
             $rsinfo=mysql_fetch_array($query);
             $public_appid=$rsinfo['public_appid'];
             $puclic_secret=$rsinfo['puclic_secret'];
             $access_token=$rsinfo['access_token'];
             $get_time=$rsinfo['get_time'];
		     $get_time=time()-$get_time;
		 	 
		if($get_time>7200){			
		     $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$public_appid&secret=$puclic_secret";
             $result = https_request($url);
             $jsoninfo = json_decode($result, true);
			 if(!empty($jsoninfo['access_token'])){
           		  $access_token = $jsoninfo['access_token']; 
			 }
	         $time=time();
             $sql="UPDATE `kddx_public` SET  `access_token` =  '$access_token',`get_time` =  '$time' WHERE  public_id='$public_id'";
		     $query=mysql_query($sql);
		}
             return $access_token;
}

//组装菜单JSON函数
function get_jsonmenu($public_id){
		 
		 $jsonmenu='{"button":[';
		  for($i=0;$i<=3;$i++){
		 $sql="SELECT * FROM `kddx_public_menu` WHERE fid=0 && public_id='$public_id' && sort='$i'";
         $query=mysql_query($sql);
         while($rsinfo=mysql_fetch_array($query)){
	      if($jsonmenu!='{"button":['){
			  $jsonmenu=$jsonmenu.',';
		  }
          $fid=$rsinfo['id'];
          $type=$rsinfo['type'];
		  $title=$rsinfo['title'];
		  $keyvalues=$rsinfo['keyvalues'];
		  switch($type){
		    case "none":
	        $jsonmenu=$jsonmenu.'{"name":"'.$title.'","sub_button":[';
			$tid=1;
			for($i_2=0;$i_2<=5;$i_2++){
			$sql2="SELECT * FROM `kddx_public_menu` WHERE fid='$fid' && public_id='$public_id' && sort='$i_2'";
            $query2=mysql_query($sql2);
			
            while($rsinfo2=mysql_fetch_array($query2)){
				 if($tid!=1){
				 $jsonmenu=$jsonmenu.',';	 
				 }
				 $type=$rsinfo2['type'];
		         $title=$rsinfo2['title'];
		         $keyvalues=$rsinfo2['keyvalues'];
				 if($type=='click'){
					 $jsonmenu=$jsonmenu.'{"type":"click","name":"'.$title.'","key":"'.$keyvalues.'","sub_button":[]}';
				 }else{
					 $jsonmenu=$jsonmenu.'{"type":"view","name":"'.$title.'","url":"'.$keyvalues.'","sub_button":[]}';
				 }
				 $tid++;
			  }
			}
			$jsonmenu= $jsonmenu.']}'; 
            break;	
			case "click":
	         $jsonmenu=$jsonmenu.'{"type":"click","name":"'.$title.'","key":"'.$keyvalues.'","sub_button":[]}';
            break;		
            case "view":
	         $jsonmenu=$jsonmenu.'{"type":"view","name":"'.$title.'","url":"'.$keyvalues.'","sub_button":[]}';
            break;				
		  }
		 }
		 }
		  $jsonmenu= $jsonmenu.']}';
         return $jsonmenu;
}

/**
 * 微信授权		存储SESSION
 * @return Ambigous <NULL, mixed>
 */

function get_userinfo(){
$appid="wx34f6baf7794f38b1";
$secret="66c18a049b225967cf248062078c7bc8";
$redirect_url="http%3A%2F%2Fwww.pocketuniversity.cn%2Findex.php%2Fbangdan";
$userinfo = null;
	if (! empty ( $_GET ['code'] )) {
		// 获取用户UNIONID
		$code = $_GET ['code'];
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
		$result = https_request ( $url );
		$jsoninfo = json_decode ( $result, true );
		$access_token=$jsoninfo ['access_token'];
		// print_r($jsoninfo);
		if (! empty ( $access_token )) {
			$openid = $jsoninfo ['openid'];
			$access_token = $jsoninfo ['access_token'];
			$url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
			$result = https_request ( $url );
			$userinfo = json_decode ( $result, true );
		} else {
			$refresh_token=$jsoninfo['refresh_token'];
			$url="https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=$appid&grant_type=refresh_token&refresh_token=$refresh_token";
			$result = https_request ( $url );
			$jsoninfo = json_decode ( $result, true );
			$access_token=$jsoninfo['access_token'];
		}
	} else {
		echo "<script>location.href='https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_url&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect'</script>";
	}
	return $userinfo;
}
/**
 * 抓取新榜数据
 * @param 	string		$public_id		公众号ID
 * @return	object		$data			返回的对象资源
 */
function get_newrankdata($public_id){
$url="http://www.newrank.cn/public/info/detail.html?account=".$public_id;
$data=file_get_contents($url);
preg_match("/var esbclf =([\s\S]*?)var fgkcdg/",$data,$table);

$table = str_replace("\n","",$table);
$table = str_replace("\t","",$table);		
$table = str_replace("</font>","",$table);
$table = preg_replace("'([rn])[s]+'","",$table);
$table = preg_replace('/&nbsp;/',"",$table);
//$table = str_replace(" ","",$table);
$table = str_replace(";","",$table);

$json=$table[1];

$data=json_decode($json);
return $data;
}

 function  get_data($public_id,$key){
    $arr=get_newrankdata($public_id);
    if(time()-strtotime($arr[0]->update_time)<=60*60*24){            
            foreach($key as $v ){
                $value[$v] =$arr[0]->$v;
            }
    }else{
             foreach($key as $v ){
                $value[$v] ="0";
            }
    }
        $value['time']=date("Y-m-d H:i:s",time());
		$value['name']=$arr[0]->name;
        return $value;
}


function get_data_new($public_id,$array,$date){
    $arr=get_newrankdata($public_id);
    	foreach ($arr as  $val1 ) {
    		foreach($val1 as $val){
        if(strtotime($val->rank_date)==$date){
               foreach($array as $v){
                    $value[$v] = $val->$v;
                } 
            break;
         }else{
            foreach($array as $v){
                    $value[$v] = "0";
                }
         }   
            $value['name']=$val->name;
    	}
    	}
    return $value;
}

//获取上个月的最后一天的时间戳
function get_month($y="",$m=""){
	$m>12||$m<1?$m=1:$m;
	if($y=="") $y=date("Y");
 if($m=="") $m=date("m");
 $m=sprintf("%02d",intval($m));
 $y=str_pad(intval($y),4,"0",STR_PAD_RIGHT);
 $firstday=strtotime($y.$m."01000000 +1 month");
 $firstdaystr=date("Y-m-01",$firstday);
 $lastday = strtotime(date('Y-m-d 0:0:0', strtotime("$firstdaystr  -1 day")));
 return $lastday;
}

/**
 * 日榜数据
 * @param string $public_num			公众号ID
 * @param tiemstamp $timestamp		时间戳
 * @return score
 */
function get_score($public_num,$timestamp){
	$y=date("Y",$timestamp);
	$m=date("m",$timestamp);
	$d=date("d",$timestamp);
	
 	$r1_max = 7 + 1; // 总篇数
	$r2_max = 93477 + 1; // 总阅读数
	$r3_max = 43000 + 1; // 头条阅读
	$r4_max = 18695 + 1; // 平均阅读
	$r5_max = 43000 + 1; // 最高阅读
	$r6_max = 388 + 1; // 总点赞数
	$r7_max = 1+1;	//每日发布次数
	$p1 = 0.05; // 总篇数
	$p2 = 0.35; // 总阅读数
	$p3 = 0.1; // 头条阅读
	$p4 = 0.2; // 平均阅读
	$p5 = 0.1; // 最高阅读
	$p6 = 0.1; // 总点赞数
	$p7=0.1;//每日发布次数
	$e = 2.718281828459; 
	$re = M ( "kdgx_public_data" )->where ( "public_num='$public_num' && y=$y && m=$m && d=$d" )->find ();
	if($re['article_clicks_count']!=0){
		$article_count=$re['article_count']+1;
		$article_clicks_count=$re['article_clicks_count']+1;
		$article_clicks_count_top_line=$re['article_clicks_count_top_line']+1;
		$avg_article_clicks_count=$re['avg_article_clicks_count']+1;
		$max_article_clicks_count=$re['max_article_clicks_count']+1;
		$article_likes_count=$re['article_likes_count']+1;
		$release_times=1+1;
	}else{
		$article_count=1;
		$article_clicks_count=1;
		$article_clicks_count_top_line=1;
		$avg_article_clicks_count=1;
		$max_article_clicks_count=1;
		$article_likes_count=1;
		$release_times=1;
	}
	$r1=log($article_count,$e)*1000/log($r1_max,$e);
	$r2=log($article_clicks_count,$e)*1000/log($r2_max,$e);
	$r3=log($article_clicks_count_top_line,$e)*1000/log($r3_max,$e);
	$r4=log($avg_article_clicks_count,$e)*1000/log($r4_max,$e);
	$r5=log($max_article_clicks_count,$e)*1000/log($r5_max,$e);
	$r6=log($article_likes_count,$e)*1000/log($r6_max,$e);
	$r7=log($release_times,$e)*1000/log($r7_max,$e);
	// 生成指数
	$score = $r1 * $p1 + $r2 * $p2 + $r3 * $p3 + $r4 * $p5 + $r5 * $p5 + $r6 * $p6 +$r7 * $p7;
	$score = round ( $score );
	return $score;
}
/**
 *月榜指数
 *@param $public_num		公众号ID
 *@param $timestamp			时间戳
 *@return $score   				月榜指数
*/
function get_monthscore($public_num,$timestamp){
	$y=date("Y",$timestamp);
	$m=date("m",$timestamp);
	$d=date("d",$timestamp);
	$r1_max = 7*$d + 1; // 总篇数
	$r2_max = 93477*$d + 1; // 总阅读数
	$r3_max = 43000*$d + 1; // 头条阅读
	$r4_max = 18695*$d + 1; // 平均阅读
	$r5_max = 43000*$d + 1; // 最高阅读
	$r6_max = 388*$d + 1; // 总点赞数
	$r7_max = $d +1;			//总发布数
	$p1 = 0.05; // 总篇数
	$p2 = 0.35; // 总阅读数
	$p3 = 0.1; // 头条阅读
	$p4 = 0.2; // 平均阅读
	$p5 = 0.1; // 最高阅读
	$p6 = 0.1; // 总点赞数
	$p7=0.1;//每日发布次数
	$e = 2.718281828459;
	$update=0;
	$times=1;
	$article_count=1;
	$article_clicks_count=1;
	$article_clicks_count_top_line=1;
	$avg_article_clicks_count=1;
	$max_article_clicks_count=1;
	$article_likes_count=1;
	$arr=M("kdgx_public_data")->where("public_num = '$public_num' && y=$y && m=$m && d<=$d")->select();
	foreach($arr as $rs){
		//计算当月更新次数
		if($rs['article_count']!=0){
			$update++;
		}
		$article_count=$article_count+$rs['article_count'];
		$article_clicks_count=$article_clicks_count+$rs['article_clicks_count'];
		$article_clicks_count_top_line=$article_clicks_count_top_line+$rs['article_clicks_count_top_line'];
		$avg_article_clicks_count=$avg_article_clicks_count+$rs['avg_article_clicks_count'];
		$max_article_clicks_count=$max_article_clicks_count+$rs['max_article_clicks_count'];
		$article_likes_count=$article_likes_count+$rs['article_likes_count'];
	}
	if($update!=0){
			$times=$update;
			$t = $times +1 ;
	}else{
		$t=1;
	}	
	$avg_article_clicks_count=$article_clicks_count/$times;			//  每月总阅读数/总次数
	$r1=log($article_count,$e)*1000/log($r1_max,$e);
	$r2=log($article_clicks_count,$e)*1000/log($r2_max,$e);
	$r3=log($article_clicks_count_top_line,$e)*1000/log($r3_max,$e);
	$r4=log($avg_article_clicks_count,$e)*1000/log($r4_max,$e);
	$r5=log($max_article_clicks_count,$e)*1000/log($r5_max,$e);
	$r6=log($article_likes_count,$e)*1000/log($r6_max,$e);
	$r7=log($t,$e)*1000/log($r7_max,$e);
	//计算当月指数
	$score=$r1*$p1+$r2*$p2+$r3*$p3+$r4*$p5+$r5*$p5+$r6*$p6+$r7*$p7;
	$score=round($score);
	return $score;
}
/**
 * 获得周的时间戳
 * @param 	timestamp	$timestamp		时间戳
 * @param 	tinyint			$i						几周
 * @return 	timestamp	$date				返回时间戳
 */
function get_week($timestamp,$i="0"){
	$i+=1;
	$day=$i*7*60*60*24;
	$date=$timestamp-$day;
	return $date;
}

/**
 * 		周榜指数
 *@param	string			$public_num   公众号ID
* @param	timestamp	$timestamp   	时间戳
* @return		tinyint			$score   		前七日时间戳
 */

function get_weekscore($public_num,$timestamp){
	$r1_max = 7*7 + 1; // 总篇数
	$r2_max = 93477*7 + 1; // 总阅读数
	$r3_max = 43000*7 + 1; // 头条阅读
	$r4_max = 18695*7 + 1; // 平均阅读
	$r5_max = 43000*7 + 1; // 最高阅读
	$r6_max = 388*7 + 1; 	// 总点赞数
	$r7_max = 7 +1;			//总发布数
	$p1 = 0.05; // 总篇数
	$p2 = 0.35; // 总阅读数
	$p3 = 0.1; // 头条阅读
	$p4 = 0.2; // 平均阅读
	$p5 = 0.1; // 最高阅读
	$p6 = 0.1; // 总点赞数
	$p7=0.1;//每日发布次数
	$e = 2.718281828459;
	$update=0;
	$times=1;
	$article_count=1;
	$article_clicks_count=1;
	$article_clicks_count_top_line=1;
	$avg_article_clicks_count=1;
	$max_article_clicks_count=1;
	$article_likes_count=1;
	$time = get_week ( $timestamp );   //前七日的是时间戳
	$arr=M("kdgx_public_data")->where("public_num = '$public_num' && timestamp<='$timestamp' && timestamp>='$time' ")->select();
	foreach($arr as $rs){
		//每周计算更新次数
		if($rs['article_count']!=0){
			$update++;
		}
		$article_count=$article_count+$rs['article_count'];
		$article_clicks_count=$article_clicks_count+$rs['article_clicks_count'];
		$article_clicks_count_top_line=$article_clicks_count_top_line+$rs['article_clicks_count_top_line'];
		$avg_article_clicks_count=$avg_article_clicks_count+$rs['avg_article_clicks_count'];
		$max_article_clicks_count=$max_article_clicks_count+$rs['max_article_clicks_count'];
		$article_likes_count=$article_likes_count+$rs['article_likes_count'];
	}
	if($update!=0){
		$times=$update;
		$t = $times +1 ;
	}else{
		$t=1;
	}
	$avg_article_clicks_count=$article_clicks_count/$times;

	$r1=log($article_count,$e)*1000/log($r1_max,$e);
	$r2=log($article_clicks_count,$e)*1000/log($r2_max,$e);
	$r3=log($article_clicks_count_top_line,$e)*1000/log($r3_max,$e);
	$r4=log($avg_article_clicks_count,$e)*1000/log($r4_max,$e);
	$r5=log($max_article_clicks_count,$e)*1000/log($r5_max,$e);
	$r6=log($article_likes_count,$e)*1000/log($r6_max,$e);
	$r7=log($t,$e)*1000/log($r7_max,$e);
	//计算当月指数
	$score=$r1*$p1+$r2*$p2+$r3*$p3+$r4*$p5+$r5*$p5+$r6*$p6+$r7*$p7;
	$score=round($score);
	return $score;
}

/**
 * @param	 string 			$public_num			公众号
 * @param	 timestamp 	$timestamp			时间戳
 * @return 	tinyint 			$people  				预计活跃粉丝数
 */
function get_fans($public_num,$timestamp){
	
	$timestamp1=$timestamp-24*60*60*31;
//	$a=date("Y-m-d H:i:s",$timestamp);		
	$arr1 = M ( "kdgx_public_data" )->where ( " public_num = '$public_num' && timestamp>=$timestamp1 && timestamp<=$timestamp " )->select ();
	$s=0;
	$clicks=0;
	foreach($arr1 as $v){
		if($v['article_count']!=0){
			$s++;
			$clicks=$clicks+$v['avg_article_clicks_count'];
		}
	}
	$clicks=$clicks/$s;
	$people = $clicks / 0.07;
	$people = round ( $people );
	return $people;
}
/**
 * 获得时间戳 
 * @param	tiemstamp	$timestamp	时间戳
 * @param	string			$str				描述加减的时间
 * @return	timestamp	$timestamps		最后获得的时间戳
 */
function get_time($timestamp,$str){
	$i=time()-$timestamp;
	$j=strtotime("$str");
	$timestamps=$j-$i;
	return $timestamps;
}

