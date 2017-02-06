<?php
namespace Bangdan\Controller;
use Bangdan\Controller\BaseController;
class RankingController extends BaseController{

	public function index(){
		$this->display();
	}
	
	public function ranking() {
		
/* 		请求格式：
		data : {

			dateType : {
				listType : 'type_all' (所有) / 'type_pocket' (口袋高校榜),
				type : 'day_list' (日榜)/ 'week_list' (周榜) 'month_list' (月榜),
				date : 后台所要的标识时间戳：写在 select > option > value
			}
		} */
		
		//判断是联盟号或矩阵号
/*  		$_GET['dateType']['listType']='type_pocket';
		$_GET['dateType']['type']='day_list';
		$_GET['dateType']['date']=1;  */

		
/* 	$this->ajaxReturn($_GET);
		exit(); */
		if($_GET['dateType']['listType']=='type_pocket'){
				$where=" && o.is_pocket='1' ";
		}else{
			$where='';
		}
		$public_num = $_SESSION ['public_num'];
		if ($_GET ['dateType'] ['type'] == "day_list") {
			$day = $_GET ["dateType"] ['date'];
			$day += 1;
			$y = date ( "Y", strtotime ( "-$day day  -13 hours" ) );
			$m = date ( "m", strtotime ( "-$day day  -13 hours" ) );
			$d = date ( "d", strtotime ( "-$day day  -13 hours" ) );
			$timestamp = mktime ( 0, 0, 0, $m, $d, $y );
			//---------------当前SESSION里公众号的排名及分数-----------------------//
			$sql = "SELECT o.public_id,o.public_num,o.public_name,s.score	
						FROM  kdgx_public_score  s	
						INNER JOIN  kddx_public  o		
						ON  o.public_num=s.public_num		
						WHERE  s.public_num='$public_num' && s.timestamp='$timestamp' && o.is_show='1' $where	
						GROUP BY  o.public_num
						";
			$re = M ()->query ( $sql );
			$re = $re [0];
			if ($re) {
				$score = $re ['score'];
				$sql = "SELECT o.public_id,o.public_num,s.score FROM `kdgx_public_score`   s	
								INNER JOIN  `kddx_public`  o  ON  s.public_num=o.public_num	
								WHERE  s.timestamp='$timestamp' && s.score>'$score' && o.is_show='1' $where	
								GROUP BY  o.public_num";
				$a = M ()->query ( $sql );
				$num = 0;
				foreach ( $a as $v ) {
					$num ++;
				}
				$array [0] ["id"] = $num + 1;
				$array [0] ["weChatId"] = $re ['public_num'];
				$array [0] ["public_id"] = $re ['public_id'];
				$array [0] ["name"] = $re ['public_name'];
				$array [0] ["score"] = $score;
			} else {
				$array [0] ["id"] = 0;
				$array [0] ["weChatId"] = $public_num;
				$array [0] ["public_id"] = $re ['public_id'];
				$array [0] ["name"] = '暂无排名';
				$array [0] ["score"] = '0';
			}
			//--------------所有公众号的排名与分数--------------------//
			$sql = "SELECT o.public_id,o.public_num,o.public_name,s.score
							FROM  `kdgx_public_score`   s
							INNER JOIN  `kddx_public`   o
							ON  s.public_num=o.public_num
							WHERE  s.timestamp='$timestamp' && o.is_show='1' $where
							GROUP BY  o.public_num
							ORDER  BY  score DESC	";
			$arr = M ()->query ( $sql );
			foreach ( $arr as $k => $v ) {
				$array [$k + 1] ["id"] = $k + 1; // 排名
				$array [$k + 1] ["weChatId"] = $v ['public_num'];
				$array [$k + 1] ["public_id"] = $v ['public_id'];
				$array [$k + 1] ["name"] = $v ['public_name'];
				$array [$k + 1] ["score"] = $v ['score'];
			}
		} else if ($_GET ['dateType'] ['type'] == "month_list") {
			$month = $_GET ["dateType"] ['date'];
			if ($month == 0) {
				$y = date ( "Y", strtotime ( "-1 day -13 hours" ) );
				$m = date ( "m", strtotime ( "-1 day -13 hours" ) );
				$d = date ( "d", strtotime ( "-1 day  -13 hours" ) );
			} else {
				$y = date ( "Y", strtotime ( "-$month month  -13 hours" ) );
				$m = date ( "m", strtotime ( "-$month month  -13 hours" ) );
				$d = date ( "d", get_month ( $y, $m ) );
			}
			$timestamp = mktime ( 0, 0, 0, $m, $d, $y );
			$sql = "SELECT o.public_id,o.public_num,o.public_name,s.monthscore
								FROM  `kdgx_public_score`  AS  s
								INNER JOIN  `kddx_public`  AS  o
								ON  o.public_num=s.public_num
								WHERE  s.public_num='$public_num' && s.timestamp='$timestamp'&& o.is_show='1' $where
								GROUP BY  s.public_num
				 ";
			$re = M ()->query ( $sql );
			$re = $re [0];
			if ($re) {
				$score = $re ['monthscore'];
				$sql = "SELECT o.public_id,o.public_num,s.monthscore FROM `kdgx_public_score`  s
							INNER JOIN  `kddx_public`  o  ON s.public_num=o.public_num
							WHERE timestamp='$timestamp' && s.monthscore>'$score'&& o.is_show='1' $where
							GROUP BY  o.public_num";
				$a = M ()->query ( $sql );
				$num = 0;
				foreach ( $a as $v ) {
					$num ++;
				}
				$array [0] ["id"] = $num + 1;
				$array [0] ["weChatId"] = $re ['public_num'];
				$array [0] ["public_id"] = $re ['public_id'];
				$array [0] ["name"] = $re ['public_name'];
				$array [0] ["score"] = $score;
			} else {
				$array [0] ["id"] = 0;
				$array [0] ["weChatId"] = $public_num;
				$array [0] ["public_id"] = $re ['public_id'];
				$array [0] ["name"] = '暂无排名';
				$array [0] ["score"] = '0';
			}
			$sql = "SELECT  o.public_id,o.public_num,o.public_name,s.monthscore
							FROM  `kdgx_public_score`  s
							INNER JOIN  `kddx_public`  o
							ON s.public_num=o.public_num
							WHERE timestamp='$timestamp'  && o.is_show='1' $where
							GROUP BY  o.public_num
							ORDER BY  monthscore DESC	";
			$arr = M ()->query ( $sql );
			foreach ( $arr as $k => $v ) {
				$array [$k + 1] ["id"] = $k + 1; // 排名
				$array [$k + 1] ["weChatId"] = $v ['public_num'];
				$array [$k + 1] ["public_id"] = $v ['public_id'];
				$array [$k + 1] ["name"] = $v ['public_name'];
				$array [$k + 1] ["score"] = $v ['monthscore'];
			}
		} else if ($_GET ['dateType'] ['type'] == "week_list") {
			$week = $_GET ["dateType"] ['date'];
			$y = date ( "Y", strtotime ( "-$week week  -1 day -13 hours" ) );
			$m = date ( "m", strtotime ( "-$week week -1 day -13 hours" ) );
			$d = date ( "d", strtotime ( "-$week week -1 day -13 hours" ) );
			$timestamp = mktime ( 0, 0, 0, $m, $d, $y );
			$time = get_week ( $timestamp, $week );
			$sql = "SELECT  o.public_id,o.public_num,o.public_name,s.weekscore
						FROM `kdgx_public_score`  s
						INNER JOIN `kddx_public`  o
						ON  o.public_num=s.public_num
						WHERE  s.public_num='$public_num' && s.timestamp='$timestamp' && o.is_show='1' $where
						GROUP BY  o.public_num";
			$re = M ()->query ( $sql );
			$re = $re [0];
			if ($re) {
				$score = $re ['weekscore'];
				$sql = " SELECT o.public_id, o.public_num, s.weekscore
								 FROM `kdgx_public_score`  s
								INNER JOIN  `kddx_public`  o ON  s.public_num=o.public_num
								WHERE  s.timestamp='$timestamp' && s.weekscore>'$score'&& o.is_show='1' $where
								GROUP BY  o.public_num ";
				$a = M ()->query ( $sql );
				$num = 0;
				foreach ( $a as $v ) {
					$num ++;
				}
				$array [0] ["id"] = $num + 1;
				$array [0] ["weChatId"] = $re ['public_num'];
				$array [0] ["public_id"] = $re ['public_id'];
				$array [0] ["name"] = $re ['public_name'];
				$array [0] ["score"] = $score;
			} else {
				$array [0] ["id"] = 0;
				$array [0] ["weChatId"] = $public_num;
				$array [0] ["name"] = '暂无排名';
				$array [0] ["score"] = '0';
			}
			$sql = "SELECT  o.public_id , o.public_num , o.public_name , s.weekscore
						FROM  `kdgx_public_score`  s
						INNER JOIN  `kddx_public`  o
						ON s.public_num=o.public_num
						WHERE  timestamp='$timestamp' && o.is_show='1'   $where
						GROUP BY  o.public_num
						ORDER BY  weekscore  DESC	";
			$arr = M ()->query ( $sql );
			foreach ( $arr as $k => $v ) {
				$array [$k + 1] ["id"] = $k + 1; // 排名
				$array [$k + 1] ["weChatId"] = $v ['public_num'];
				$array [$k + 1] ["public_id"] = $v ['public_id'];
				$array [$k + 1] ["name"] = $v ['public_name'];
				$array [$k + 1] ["score"] = $v ['weekscore'];
			}
		}
		 $this->ajaxReturn($array);
	}

function date(){
		$sql = "SELECT y,m FROM `kdgx_public_score` GROUP BY  y,m  ORDER BY  timestamp DESC  LIMIT  0,6 ";
		$arr = M ()->query ( $sql );
		foreach ( $arr as $k => $v ) {
			$month = $v ['y'] . '年' . $v ['m'] . '月';
			$array ['month_list'] [$k] = $month;
		}
		for($i = 1; $i <= 7; $i ++) {
			$day = date ( "m月d日", strtotime ( "-$i day  -13 hours" ) ); // 日榜
			$array ['day_list'] [$i - 1] = $day;
		}
		for($i = 1; $i <= 4; $i ++) {
			$j = $i - 1;
			$a = date ( "m月d日", strtotime ( "-$i week -13 hours" ) );
			$b = date ( "m月d日", strtotime ( "-$j week -1 day -13 hours" ) );
			$array ['week_list'] [$i - 1] = $a . '---' . $b;
		}
		$this->ajaxReturn ( $array );
}










}