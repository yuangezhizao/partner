<?php
namespace Bangdan\Controller;
use Bangdan\Controller\AdminController;
class AscoreController extends AdminController {
	
	/**
	 * *修改指数界面
	 */
	public function index() {
		$m1 = date ( "m", strtotime ( "-1day" ) );
		$d1 = date ( "d", strtotime ( "-1day" ) );
		$y = date ( "Y", time () );
		$m = ( int ) $_GET ['m'] ? ( int ) $_GET ['m'] : $m1;
		$d = ( int ) $_GET ['d'] ? ( int ) $_GET ['d'] : $d1;
		$this->assign ( "m", $m );
		$this->assign ( "d", $d );
		$timestamp = mktime ( 0, 0, 0, $m, $d, $y );
		$where = "where d.timestamp=$timestamp";
		$sql = "select d.*,p.public_name,s.score,s.weekscore,s.monthscore
				from kdgx_public_data as d
				inner  join kddx_public as p on d.public_num=p.public_num
				inner join kdgx_public_score as s on d.timestamp=s.timestamp && d.public_num=s.public_num
				$where   group by d.public_num
				";
		$arr = M ()->query ( $sql );
		if (! empty ( $arr )) {
			$this->assign ( 'arr', $arr );
			$this->display ( "Ascore/index" );
		} else {
			$sql = "select *	from kdgx_public_adjust 	where timestamp='$timestamp' ";
			$arr = M ()->query ( $sql );
			if (empty ( $arr )) {
				$sql = "select public_num from kddx_public group by public_num";
				$arr = M ()->query ( $sql );
				foreach ( $arr as $v ) {
					$public_num = $v ['public_num'];
					$data = array (
							'public_num' => $public_num,
							'timestamp' => $timestamp 
					);
					M ( 'kdgx_public_adjust' )->data ( $data )->add ();
				}
			}
			$sql = "select p.public_name,p.public_num,timestamp,a.number
						from kddx_public as p
						left join kdgx_public_adjust as a 
						on p.public_num=a.public_num 
						where a.timestamp='$timestamp'
				 		group by p.public_id ";
			$arr = M ()->query ( $sql );
			foreach ( $arr as $k => $v ) {
				$arr [$k] ['time'] = date ( "Y-m-d", $v ['timestamp'] );
			}
			$this->assign ( 'arr', $arr );
			$this->display ( 'Ascore/storage' );
		}
	}

/**
 * 修改头条
 * 其他数据自动修改
 */
	public function update(){
		$id=$_GET['id'];
		$r=M('kdgx_public_data')->where("id='$id'")->find();
		$article_count=$r['article_count'];													//发布篇数
		$article_clicks_count=$r['article_clicks_count'];								//总阅读数
		$avg_article_clicks_count=$r['avg_article_clicks_count'];					//平均阅读					老数据
		$max_article_clicks_count=$r['max_article_clicks_count'];				//最大阅读数
		$article_clicks_count_top_line=$r['article_clicks_count_top_line'];	//头条

		(int)$top = $_POST['article_clicks_count_top_line']; 					// 新头条
		(int)$amount = $article_clicks_count - $article_clicks_count_top_line + $top; 	// 总阅读数
		(int)$avg = ($amount==0)?0:floor($amount/$article_count);					//平均阅读数
		(int)$max = $top >= $max_article_clicks_count ? $top : $max_article_clicks_count;//最大阅读数
		if ($article_count == 0) {
			$max = 0;
		} elseif($article_count==1){
			$max=$top;
		}elseif ($article_clicks_count_top_line == $max_article_clicks_count) {
			$max = $top;
		} else {
			$max = $max_article_clicks_count;
		}
		$data=array(
			"article_clicks_count_top_line"=>$top,								
			"article_count"=>$article_count,								
			"article_clicks_count"=>$amount,
			"avg_article_clicks_count"=>$avg,
			"max_article_clicks_count"=>$max
			);

		$re=M('kdgx_public_data')->where("id='$id'")->data($data)->save();
		if($re){
			$res=M('kdgx_public_data')->where("id=$id")->find();
			$timestamp=$res['timestamp'];
			$public_num=$res['public_num'];
			$y=date("Y",$timestamp);
			$m=date("m",$timestamp);
			$d=date("d",$timestamp);
		$arr=M('kdgx_public_score')
				->where("y='$y'&&m='$m'&&d>='$d'&&public_num='$public_num'")
				->select();
				foreach ($arr as $k => $v) {
					$timestamp1=$v['timestamp'];
					$score=get_score($public_num,$timestamp1);
					$weekscore=get_weekscore($public_num,$timestamp1);
					$monthscore=get_monthscore($public_num,$timestamp1);
					$re=M('kdgx_public_score')
						->where("timestamp='$timestamp1' && public_num= '$public_num' ")
						->data(array(
							'score'=>$score,
							'weekscore'=>$weekscore,
							'monthscore'=>$monthscore
							))
						->save();
				}
				header("location:".$_SERVER['HTTP_REFERER']);
			}else{
				header("location:".$_SERVER['HTTP_REFERER']);
			}

	}
/**
 * 提前修改头条
 * 入库的时候自动修改
 */
	public function save(){
		$number=$_POST['number'];
		$public_num=$_POST['public_num'];
		$timestamp = $_POST['timestamp'];
		$data=array(
			'public_num'=>$public_num,
			'timestamp'=>$timestamp,
			'number'=>$number
			);
		$rs = M('kdgx_public_adjust')
			->where("public_num='$public_num'&& timestamp='$timestamp'")
			->data($data)
			->save();
		header("location:".$_SERVER['HTTP_REFERER']);
	}








}	
