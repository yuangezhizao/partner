<?php
namespace	Bangdan\Controller;
use Bangdan\Controller\AdminController;
class CheckController extends AdminController {
	
	/**
	*审核界面
	*/
	Public function index(){
		$sql="select o.*,p.public_num,p.public_name from kdgx_operator as o 
				inner join kddx_public as p 
				on o.public_id=p.public_id
				order by  o.id desc
				";
		$arr=M()->query($sql);
		$this->assign("arr",$arr);
		$this->display();
	}
	
	/**
	*审核修改
	*/
 	public function check(){
		$uid=$_POST['uid'];
		$Ob=M("kdgx_operator");
		$re=$Ob->where("uid=$uid")->data($_POST)->save();
/* 		if($re){
		$re=M('kdgx_operator')->where("uid='$uid'")->find();
		$public_id=$re['public_id'];
			M('kddx_public')->where("public_id='$public_id'")->save(array('is_show'=>'1'));
		} */
		header("location:".$_SERVER['HTTP_REFERER']);
	} 


	/**
	*	修改后的数据入库
	*/
	public  function new_data(){
			// 取得时间
		$step = 1;
		for($i = 7; $i >= 1; $i --) {
			$y = date ( "Y", strtotime ( "-$i day " ) );
			$m = date ( "m", strtotime ( "-$i day " ) );
			$d = date ( "d", strtotime ( "-$i day " ) );
			$date = strtotime ( "$y-$m-$d" );
			$timestamp = mktime ( 0, 0, 0, $m, $d, $y );
			$arr = M ( "kddx_public" )->field ( "public_num" )->group ( 'public_num' )->select ();
			foreach ( $arr as $rs ) {
				$public_num = $rs ['public_num'];
				$key = array (
						'article_count',
						'article_clicks_count',
						'article_clicks_count_top_line',
						'avg_article_clicks_count',
						'max_article_clicks_count',
						'article_likes_count' 
				);
				$new = get_data_new ( $public_num, $key, $date );

				if(empty($new)){
					$new = array (
						'article_count'=>0,
						'article_clicks_count'=>0,
						'article_clicks_count_top_line'=>0,
						'avg_article_clicks_count'=>0,
						'max_article_clicks_count'=>0,
						'article_likes_count'=>0 
					);
				}
	//			print_r($public_num);
	//			print_r($new);
				(int)$article_count = $new ['article_count']; 											// 发布条数
				(int)$article_clicks_count = $new ['article_clicks_count']; 							// 总阅读
				(int)$article_clicks_count_top_line = $new ['article_clicks_count_top_line']; 			// 头条阅读  新榜数据
				(int)$avg_article_clicks_count = $new ['avg_article_clicks_count']; 					// 平均阅读
				(int)$max_article_clicks_count = $new ['max_article_clicks_count']; 					// 最高阅读
				(int)$article_likes_count = $new ['article_likes_count']; 								// 点赞数
				$re = M ( 'kdgx_public_adjust' )->where ( "timestamp='$timestamp' && public_num='$public_num'" )->find ();
				(int)$number=$re['number']?$re['number']:0;
		// 修改头条后的新数据
				(int)$top = $article_clicks_count_top_line + $number; 							// 新头条
				(int)$amount = $article_clicks_count - $article_clicks_count_top_line + $top; 	// 总阅读数
				(int)$avg = ($amount==0)?0:floor($amount/$article_count);							//平均阅读数
				(int)$max = $top >= $max_article_clicks_count ? $top : $max_article_clicks_count;//最大阅读数

				if ($article_count == 0) {
					$max = 0;
				} elseif($article==1){
					$max=$top;
				}elseif ($article_clicks_count_top_line = $max_article_clicks_count) {
					$max = $top;
				} else {
					$max = $max_article_clicks_count;
				}
				$data = array (
						'article_count' => $article_count,
						'article_clicks_count' => $amount,
						'article_clicks_count_top_line' => $top,
						'avg_article_clicks_count' => $avg,
						'max_article_clicks_count' => $max,
						'article_likes_count' => $article_likes_count 
				);
	//			print_r($data);
	//			echo "<hr/>";
	 			if ($i == 1) {
					$data1 ['article_count'] = $data ['article_count'];
					$data1 ['article_clicks_count'] = $data ['article_clicks_count'];
					$data1 ['article_clicks_count_top_line'] = $data ['article_clicks_count_top_line'];
					$data1 ['avg_article_clicks_count'] = $data ['avg_article_clicks_count'];
					$data1 ['max_article_clicks_count'] = $data ['max_article_clicks_count'];
					$data1 ['article_likes_count'] = $data ['article_likes_count'];
					$re = M ( "kdgx_public_data" )->where ( "public_num = '$public_num' && y='$y' && m='$m' && d='$d'" )->data ( $data1 )->save ();
					if ($re) {
					$score = get_score ( $public_num, $timestamp );
					$month = get_monthscore ( $public_num, $timestamp );
					$week = get_weekscore ( $public_num, $timestamp );
						$data11 = array (
								'score' => $score,
								'monthscore' => $month,
								'weekscore' => $week,
								'public_num' => $public_num,
								'y' => $y,
								'm' => $m,
								'd' => $d,
								'timestamp' => $timestamp 
						);
						$re2 = M ( "kdgx_public_score" )->where ( "public_num = '$public_num' && y='$y' && m='$m' && d='$d'" )->data ( $data11 )->save ();
						if ($re) {
							$this->success ( "今日数据已更新", U ( "Check/index" ) );
						}
					}
				}
				$re = M ( "kdgx_public_data" )->where ( "public_num = '$public_num' && y='$y' && m='$m' && d='$d'" )->find ();
				if (empty ( $re )) {
					// 取得榜单数据
					$data ['y'] = $y;
					$data ['m'] = $m;
					$data ['d'] = $d;
					$data ['public_num'] = $public_num;
					$data ['timestamp'] = $timestamp;
					
					$re = M ( "kdgx_public_data" )->data ( $data )->add ();
					if ($re) {
						$this->success ( "成功入库" . $step . "条信息&nbsp;", U ( "Check/index" ) );
						$step ++;
					}
					$score = get_score ( $public_num, $timestamp );
					$month = get_monthscore ( $public_num, $timestamp );
					$week = get_weekscore ( $public_num, $timestamp );
					$data2 = array (
							'score' => $score,
							'monthscore' => $month,
							'weekscore' => $week,
							'public_num' => $public_num,
							'y' => $y,
							'm' => $m,
							'd' => $d,
							'timestamp' => $timestamp 
					);
					$re2 = M ( "kdgx_public_score" )->data ( $data2 )->add ();
					if ($re) {

						$this->success ( "评分已入库", U ( "Check/index" ) );
					}
				} else {
					// 取得榜单数据
					if ($re ['article_count'] == 0) {
						$data ['y'] = $y;
						$data ['m'] = $m;
						$data ['d'] = $d;
						$data ['public_num'] = $public_num;
						$data ['timestamp'] = $timestamp;
						$re = M ( "kdgx_public_data" )->where ( "public_num = '$public_num' && y='$y' && m='$m' && d='$d'" )->data ( $data )->save ();
						if ($re) {
							$this->success ( "成功修改" . $step . "条信息&nbsp;", U ( "Check/index" ) );
							$step ++;
						}
						$score = get_score ( $public_num, $timestamp );
						$month = get_monthscore ( $public_num, $timestamp );
						$week = get_weekscore ( $public_num, $timestamp );
						$data2 = array (
								'score' => $score,
								'monthscore' => $month,
								'weekscore' => $week,
								'public_num' => $public_num,
								'y' => $y,
								'm' => $m,
								'd' => $d,
								'timestamp' => $timestamp 
						);
						$re2 = M ( "kdgx_public_score" )->where ( "public_num = '$public_num' && y='$y' && m='$m' && d='$d'" )->data ( $data2 )->save ();
						if ($re) {
							$this->success ( "评分已更新", U ( "Check/index" ) );
						}
					}
				} 
			}
		} 
	
 		if($step<=1) {
			$this->success("已没有可执行的入库操作",U("Check/index"));
		} 
	}

	/**
	 *	新榜原始数据入库
	 */
	public  function old_data(){

	    ini_set("max_execution_time", 0);
		// 取得时间
		$step = 1;
		//抓取七天数据
		for($i = 7; $i >= 1; $i --) {
			$y = date ( "Y", strtotime ( "-$i day -12hours" ) );
			$m = date ( "m", strtotime ( "-$i day  -12 hours" ) );
			$d = date ( "d", strtotime ( "-$i day  -12 hours" ) );
			$date = strtotime ( "$y-$m-$d " );
			$timestamp=mktime(0,0,0,$m,$d,$y);
 			$arr=M("kddx_public")->field("public_num")->group('public_num')->select();
			foreach ($arr as  $rs){
				$public_num = $rs['public_num'];
				// 检测当日当账号数据是否已录入，若已录入则跳出本轮循环
				$key = array (
						'article_count',
						'article_clicks_count',
						'article_clicks_count_top_line',
						'avg_article_clicks_count',
						'max_article_clicks_count',
						'article_likes_count',
				);
				//$data 新榜抓取的原始数据
				$data = get_data_new ( $public_num, $key, $date );
		//判断昨天的数据是否入库
		 		if($i==1){
						$data1['article_count']=$data['article_count'];
						$data1['article_clicks_count']=$data['article_clicks_count'];
						$data1['article_clicks_count_top_line']=$data['article_clicks_count_top_line'];
						$data1['avg_article_clicks_count']=$data['avg_article_clicks_count'];
						$data1['max_article_clicks_count']=$data['max_article_clicks_count'];
						$data1['article_likes_count']=$data['article_likes_count'];
						$re=M("kdgx_public_data")->where("public_num = '$public_num' && y='$y' && m='$m' && d='$d'")->data($data1)->save();
						if($re){
							$score=get_score($public_num, $timestamp);
							$month=get_monthscore($public_num, $timestamp); 
							$week=get_weekscore($public_num, $timestamp);
							$fans=get_fans($public_num, $timestamp);
							$data11=array(
									'score'=>$score,
									'monthscore'=>$month,
									'weekscore'=>$week,
									'public_num'=>$public_num,
									'y'=>$y,'m'=>$m,'d'=>$d,
									'timestamp'=>$timestamp,
									'fans'=>$fans
							);
							$re2=M("kdgx_public_score")->where("public_num = '$public_num' && y='$y' && m='$m' && d='$d'")->data($data11)->save();
							if($re){
								$this->success("今日数据已更新",U("Check/index"));
							}
						}
					}
			//根据循环的时间戳和公众号ID
			//判断数据库是否有数据
			//没有就入库   有就更新 确保数据是最真实
				$re=M("kdgx_public_data")->where("public_num = '$public_num' && y='$y' && m='$m' && d='$d'")->find();
				if (empty ( $re )) {
					// 取得榜单数据
						$data['y']=$y;
						$data['m']=$m;
						$data['d']=$d;
						$data['public_num']=$public_num;
						$data['timestamp']=$timestamp;
						$re=M("kdgx_public_data")->data($data)->add();
						if ($re) {
							$this->success("成功入库" . $step . "条信息&nbsp;",U("Check/index"));
							$step ++;
						}
						$score=get_score($public_num, $timestamp);
						$month=get_monthscore($public_num, $timestamp);
						$week=get_weekscore($public_num, $timestamp);
						$fans=get_fans($public_num, $timestamp);
						$data2=array('score'=>$score,
								'monthscore'=>$month,
								'weekscore'=>$week,
								'public_num'=>$public_num,
								'y'=>$y,'m'=>$m,'d'=>$d,
								'timestamp'=>$timestamp,
								'fans'=>$fans
						);
						$re2=M("kdgx_public_score")->data($data2)->add();
						if($re){
							$this->success("评分已入库",U("Check/index"));
						}
					} else{
						// 取得榜单数据
						$data['y']=$y;
						$data['m']=$m;
						$data['d']=$d;
						$data['public_num']=$public_num;
						$data['timestamp']=$timestamp;
						$re=M("kdgx_public_data")->where("public_num = '$public_num' && y='$y' && m='$m' && d='$d'")->data($data)->save();
						if ($re) {
							$this->success("成功修改" . $step . "条信息&nbsp;",U("Check/index"));
							$step ++;
						}
						$score=get_score($public_num, $timestamp);
						$month=get_monthscore($public_num, $timestamp);
						$week=get_weekscore($public_num, $timestamp);
						$fans=get_fans($public_num, $timestamp);
						$data2=array('score'=>$score,
								'monthscore'=>$month,
								'weekscore'=>$week,
								'public_num'=>$public_num,
								'y'=>$y,'m'=>$m,'d'=>$d,
								'timestamp'=>$timestamp,
								'fans'=>$fans
						);
						$re2=M("kdgx_public_score")->where("public_num = '$public_num' && y='$y' && m='$m' && d='$d'")->data($data2)->save();
						if($re){
							$this->success("评分已更新",U("Check/index"));
						}
					}
		
			}
	 	} 
	
 		if($step<=1) {
			$this->success("已没有可执行的入库操作",U("Check/index"));
		} 
	}
	

	
	

/* 	public  function old_data(){
		// 取得时间
		$step = 1;
		for($i = 7; $i >= 1; $i --) {
			$y = date ( "Y", strtotime ( "-$i day -13hours" ) );
			$m = date ( "m", strtotime ( "-$i day -13hours" ) );
			$d = date ( "d", strtotime ( "-$i day -13hours" ) );
			$date = strtotime ( "$y-$m-$d" );
			$timestamp=mktime(0,0,0,$m,$d,$y);
			$arr=M("kddx_public")->field("public_num")->group('public_num')->select();
			foreach ($arr as  $rs){
				$public_num = $rs['public_num'];
				//	$public_num = "koudaishida";
				// 检测当日当账号数据是否已录入，若已录入则跳出本轮循环
				$key = array (
						'article_count',
						'article_clicks_count',
						'article_clicks_count_top_line',
						'avg_article_clicks_count',
						'max_article_clicks_count',
						'article_likes_count',
				);
							$new = get_data_new ( $public_num, $key, $date );
				$article_count = $new ['article_count']; 												// 发布条数
				$article_clicks_count = $new ['article_clicks_count']; 								// 总阅读
				$article_clicks_count_top_line = $new ['article_clicks_count_top_line']; 	// 头条阅读 	新榜数据
				$avg_article_clicks_count = $new ['avg_article_clicks_count']; 				// 平均阅读
				$max_article_clicks_count = $new ['max_article_clicks_count']; 				// 最高阅读
				$article_likes_count = $new ['article_likes_count']; 								// 点赞数
				$re = M ( 'kdgx_public_adjust' )->where ( "timestamp='$timestamp' && public_num='$public_num'" )->find ();
				$number=$re['number']?$re['number']:0;
				// 修改头条后的新数据
				$top = $article_clicks_count_top_line + $number; // 新头条
				$amount = $article_clicks_count - $article_clicks_count_top_line + $top; // 总阅读数
				$avg = floor ( $amount / ($article_count=0?$article_count:1) ); // 平均阅读数
				$max = $top >= $max_article_clicks_count ? $top : $max_article_clicks_count;
				if ($article_count == 1) {
					$max = $top;
				} elseif ($article_clicks_count_top_line = $max_article_clicks_count) {
					$max = $top;
				} else {
					$max = $max_article_clicks_count;
				}
				$data = array (
						'article_count' => $article_count,
						'article_clicks_count' => $amount,
						'article_clicks_count_top_line' => $top,
						'avg_article_clicks_count' => $avg,
						'max_article_clicks_count' => $max,
						'article_likes_count' => $article_likes_count 
				);
				
				if($i==1){
					$data1['article_count']=$data['article_count'];
					$data1['article_clicks_count']=$data['article_clicks_count'];
					$data1['article_clicks_count_top_line']=$data['article_clicks_count_top_line'];
					$data1['avg_article_clicks_count']=$data['avg_article_clicks_count'];
					$data1['max_article_clicks_count']=$data['max_article_clicks_count'];
					$data1['article_likes_count']=$data['article_likes_count'];
					$re=M("kdgx_public_data1")->where("public_num = '$public_num' && y='$y' && m='$m' && d='$d'")->data($data1)->save();
					if($re){
						$score=get_score($public_num, $y, $m, $d);
						$month=get_monthscore($public_num, $y, $m, $d);
						$week=get_weekscore($public_num, $timestamp);
						$data11=array('score'=>$score,
								'monthscore'=>$month,
								'weekscore'=>$week,
								'public_num'=>$public_num,
								'y'=>$y,'m'=>$m,'d'=>$d,
								'timestamp'=>$timestamp
						);
						$re2=M("kdgx_public_score1")->where("public_num = '$public_num' && y='$y' && m='$m' && d='$d'")->data($data11)->save();
						if($re){
							$this->success("今日数据已更新",U("Check/index"));
						}
					}
				}
				$re=M("kdgx_public_data1")->where("public_num = '$public_num' && y='$y' && m='$m' && d='$d'")->find();
				if (empty ( $re )) {
					// 取得榜单数据
					$data['y']=$y;
					$data['m']=$m;
					$data['d']=$d;
					$data['public_num']=$public_num;
					$data['timestamp']=$timestamp;
					$re=M("kdgx_public_data1")->data($data)->add();
					if ($re) {
						$this->success("成功入库" . $step . "条信息&nbsp;",U("Check/index"));
						$step ++;
					}
					$score=get_score($public_num, $timestamp);
					$month=get_monthscore($public_num, $timestamp);
					$week=get_weekscore($public_num, $timestamp);
					$data2=array('score'=>$score,
							'monthscore'=>$month,
							'weekscore'=>$week,
							'public_num'=>$public_num,
							'y'=>$y,'m'=>$m,'d'=>$d,
							'timestamp'=>$timestamp
					);
					$re2=M("kdgx_public_score1")->data($data2)->add();
					if($re){
						$this->success("评分已入库",U("Check/index"));
					}
				} else{
					// 取得榜单数据
					$data['y']=$y;
					$data['m']=$m;
					$data['d']=$d;
					$data['public_num']=$public_num;
					$data['timestamp']=$timestamp;
					$re=M("kdgx_public_data1")->where("public_num = '$public_num' && y='$y' && m='$m' && d='$d'")->data($data)->save();
					if ($re) {
						$this->success("成功修改" . $step . "条信息&nbsp;",U("Check/index"));
						$step ++;
					}
					$score=get_score($public_num, $timestamp);
					$month=get_monthscore($public_num, $timestamp);
					$week=get_weekscore($public_num, $timestamp);
					$data2=array('score'=>$score,
							'monthscore'=>$month,
							'weekscore'=>$week,
							'public_num'=>$public_num,
							'y'=>$y,'m'=>$m,'d'=>$d,
							'timestamp'=>$timestamp
					);
					$re2=M("kdgx_public_score1")->where("public_num = '$public_num' && y='$y' && m='$m' && d='$d'")->data($data2)->save();
					if($re){
						$this->success("评分已更新",U("Check/index"));
					}
				}
	
			}
		}
	
		if($step<=1) {
			$this->success("已没有可执行的入库操作",U("Check/index"));
		}
	} */
}