<?php
namespace	Bangdan\Controller;
use Bangdan\Controller\AdminController;
class DataController extends AdminController {
	

	
	public function  index(){
			$this->display();
		}
		/**
		 * @return 管理页
		 */
	public function score(){
/* 			$m1=date("m",strtotime("-1 day -13hours"));
			$d1=date("d",strtotime("-1 day -13hours")); */
			$y=date("Y",time());
			$str=$_POST['searchVal']; 
			$val = substr ( $str, 4, 1 );
			if ($val == '.') {
				$time = explode ( '.', $str );
			} elseif ($val == '/') {
				$time = explode ( '/', $str );
			} elseif ($val == '-') {
				$time = explode ( '-', $str );
			}
			$y = $time ['0'];
			$m = $time ['1'];
			$d = $time ['2'];
			$timestamp=mktime(0,0,0,$m,$d,$y);
			$re=M("kdgx_public_score")->where("timestamp='$timestamp'")->find();
			if(!$re){
				$rs=M("kdgx_public_score")->field("timestamp")->group("timestamp")->order("timestamp desc")->find();
				$timestamp=$rs['timestamp'];
			}
			$where = "where d.timestamp=$timestamp";
			$sql = "select d.*,p.id,p.public_name,s.score,s.isoriginal,s.isviolate,s.isofficial,ismoney from kdgx_public_data as d
				inner  join kddx_public as p on d.public_num=p.public_num
				left join kdgx_operator as o on o.public_id=p.public_id
				inner join kdgx_public_score as s on d.timestamp=s.timestamp && d.public_num=s.public_num
				$where   group by d.public_num
				order by s.score desc ";
			$arr = M()->query ( $sql );
			foreach ( $arr as $k => $v ){
				$arr [$k] ['id'] = $k+1;
				$public_num = $v ['public_num'];
				$timestamp = $v ['timestamp'];
				$fans = get_fans ( $public_num, $timestamp );
				$timestamps = mktime ( 0, 0, 0, $m, $d, $y );
				$timestamps = get_time ( $timestamp, "-1 day" );
				$fanss = get_fans ( $public_num, $timestamps );
				$arr [$k] ['fans'] = $fans;
				$arr [$k] ['fanss'] = $fans - $fanss;
				// 运营者资料 $data
			$operArr = M ( 'kdgx_operator' )->select ();
			foreach ( $operArr as $v2 ) {
				$leadArr = explode ( "-", $v2 ['public_lead'] );
				foreach ($leadArr as $v3){
					if($v3==$v['id']){
						$arr [$k] ['name'] = $v2 ['name'];
						$arr [$k] ['lead'] = $v2 ['lead'];
					}
				}
			}
				
/* 				$data=M()->table('kdgx_operator')
						->field('o.name,o.lead')
						->where("p.public_num='$public_num' && o.lead='1'")
						->alias('o')
						->join("kddx_public as p on o.public_manager=p.public_id")
						->find();
				$arr [$k] ['name'] = $data['name'] ;
				$arr[$k]['lead']=$data['lead'];
				 */
				
			}
			$this->ajaxReturn($arr);
		}
		

		/**
		* ajax修改原创状态
		* 
		*/
		function update_original(){
			$public_num=$_POST['public_num'];
			$timestamp=$_POST['timestamp'];
			$isoriginal=$_POST['isoriginal'];
			$re=M('kdgx_public_score')
				->where("public_num='$public_num' && timestamp='$timestamp'")
				->data(array('isoriginal'=>$isoriginal))
				->save();
			echo $re;
		}
		/**
		*罚款参考金额
		*/
		function update_violate(){
			$public_num=$_POST['public_num'];
			$timestamp=$_POST['timestamp'];
			$isviolate=$_POST['isviolate'];
			$Ob=M('kdgx_public_score');
			$y=date('Y',$timestamp);
			$m=date('m',$timestamp);
			$d=date('d',$timestamp);
			if($isviolate=='1'){
					(int)$num=$Ob->where("public_num='$public_num' &&isviolate='1' && y='$y'&& m='$m'&& d<'$d'")->count();
					if($num==0){
							$punish=20;
					}elseif($num==1){
							$punish=50;
					}elseif($num==2){
							$punish=100;
					}elseif($num==3){
							$punish=150;
					}elseif($num>=4){
							$punish='0';
					}
			//情况三
			}elseif($isviolate=='3'){
					(int)$num=$Ob->where("public_num='$public_num' &&isviolate='3' && y='$y' && m='$m' && d<'$d'")->count();
					if($num==0){
						$punish=100;
					}elseif($num==1){
						$punish=200;
					}elseif($num==2){
						$punish=300;
					}elseif($num>=3){
						$punish='0';
					}
				}
				$array['num']=$num;
				$array['punish']=$punish;
				$this->ajaxReturn($array);
		}
		
		/**
		* 参考违规罚款金额
		*/		
		function violate(){
			$public_num=$_POST['public_num'];
			$timestamp=$_POST['timestamp'];
			$isviolate=$_POST['isviolate'];
			$punish=$_POST['punish'];
			$Ob=M('kdgx_public_score');
			$re= $Ob ->where("public_num='$public_num'&& timestamp='$timestamp'")
				->data(array('isviolate'=>"$isviolate",'punish'=>"$punish"))
				->save();
			$array['msg']=$re;
			$this->ajaxReturn($array);
		}

/**
*参考日结工资
*/
		function daywage(){
			$public_num=$_POST['public_num'];
			$timestamp =$_POST['timestamp'];
			$Ob=M('kdgx_public_score');
			$y=date("Y",$timestamp);
			$m=date("m",$timestamp);
			$d=date("d",$timestamp);
			$re=$Ob->where("public_num='$public_num'&&timestamp='$timestamp'")->find();
			$score=$re['score'];
		//$score 	日榜指数
			$data['score']=$score;
		//$dayAticalMoney 	该日口袋高校指数分档设定运营者底薪
			if($score <400){
				$dayAticalMoney=0;
			}elseif($score >400 && $score <=500){
				$dayAticalMoney=5;
			}elseif($score >500 && $score <=600){
				$dayAticalMoney=15;
			}elseif($score >600 && $score <=700){
				$dayAticalMoney=30;
			}elseif($score>700){
				$dayAticalMoney=50;
			}
			$data['dayAticalMoney']=$dayAticalMoney;

		//$specialAticalMoney  专享
			if($re['isoriginal']==3){
				$num=$Ob ->where("isoriginal='3'  &&public_num ='$public_num' &&y='$y' &&m='$m' &&d<='$d'")
					->count();
				if($num==1){
					$specialAticalMoney=10;
				}elseif($num==2){
					$specialAticalMoney=15;
				}elseif($num==3){
					$specialAticalMoney=20;
				}elseif($num==4){
					$specialAticalMoney=25;
				}elseif($num==5){
					$specialAticalMoney=30;
				}else{
					$specialAticalMoney=0;
				}
			}else{
				$num=0;
				$specialAticalMoney=0;
			}
			$data['num']=$num;
			$data['specialAticalMoney']=$specialAticalMoney;
			$times=get_time($timestamp,"-1 month");
			$y1=date("Y",$times);
			$m1=date("m",$times);
			$d1=date("d",$times);
			//当月累计罚款
			$rs=M("kdgx_public_finance")
				->where("public_num ='$public_num' &&y='$y1' &&m='$m1' &&d<='$d1'")
				->select();
			$punish=0;
			foreach($rs as $v){
				$punish+=$v['day_fine'];
			}
			$data['punish']=$punish;
			$data['repay'] =0;
			
			//更新次数 $num
			$y=date("Y",$timestamp);
			$m=date("m",$timestamp);
			$d=date("d",$timestamp);
			$update=M("kdgx_public_score")
			->where(" y='$y'&&m='$m' && d<='$d' && score!='0' && public_num='$public_num'")
			->count();
			$data ['update'] = $update;
			//违规次数 $violate;
			$sql="select isviolate from kdgx_public_score
			where y='$y'&&m='$m'  && public_num='$public_num' && isviolate!='0'  && isviolate !='5'
			group by d
			";
			$re=M()->query($sql);
			$violate=0;
			foreach($re as $key=>$v){
			$violate++;
			}
			$data ['violate'] =$violate;
			//本月收益 $daywage
				$sql="select day_pay from kdgx_public_finance
							where y='$y'&&m='$m' && public_num='$public_num'
							";
							$arr1=M()->query($sql);
							$monthwage=0;
							foreach ($arr1 as $key => $v) {
							$daywage=$monthwage+$v['daywage'];
			}
			$data['monthwage']=$monthwage;
			//总收益 $wage
			$sql="select day_pay,month_pay from kdgx_public_finance
			where  public_num='$public_num'	 ";
			$arr2=M()->query($sql);
			$wage=0;
			foreach ($arr1 as $key => $v) {
			$wage=$wage+$v['monthwage'];
			$wage=$wage+$v['daywage'];
			}
			$data['wage']=$wage;
			$this->ajaxReturn($data);
		}



/*
*
*月榜参考工资
*/
	public function monthwage(){
		$public_num=$_POST['public_num'];
		$timestamp =$_POST['timestamp'];
		$times=get_time($timestamp,"-1 month");
		$y=date("Y",$times);
		$m=date("m",$times);
		$d=date("d",get_month($y,$m));
		$Ob=M("kdgx_public_score");
		$arr=$Ob->field("monthscore")
			->where("public_num='$public_num' && y='$y' && m='$m'  ")
			->group("d desc")
			->find();
		(int)$monthscore=$arr['monthscore'];
	//$monthscore 月榜指数
	//$month_pay 月榜底薪
		if($monthscore>800){
			$month_pay=700;
		}elseif($monthscore>650 && $monthscore <=800){
			$month_pay=500;
		}elseif($monthscore>550 && $monthscore <=650){
			$month_pay=400;
		}elseif($monthscore>450 && $monthscore <=550){
			$month_pay=300;
		}else{
			$month_pay=0;
		}
		$array['monthscore']=$monthscore;
		$array['month_pay']=$month_pay;

	//更新篇数
		(int)$update_num=$Ob->where("public_num='$public_num' && y='$y' &&m='$m' && score!='0'")
				->count();
		if($update_num<3){
			$month_punish=0;
		}elseif($update_num>=3 &&$update_num<=7){
			$month_punish=300;
		}elseif($update_num>=8 && $update_num<=9){
			$month_punish=100;
		}elseif($public_num>=10 && $update_num<=12){
			$month_punish=20;
		}else{
			$month_punish=0;
		}
		if($update_num>=13 && $update_num<=15){
			$month_fulfil_pay=50;
		}elseif($update_num>=16 && $update_num<=20){
			$month_fulfil_pay=100;
		}elseif($update_num>=20){
			$re=$Ob->where("public_num='$public_num' && y='$y' &&m='$m' && d<'$d' && score='0'")->count();
			if($re==0){
				$month_fulfil_pay=300;
			}else{
				$month_fulfil_pay=150;
			}
		}else{
			$month_fulfil_pay=0;
		}
		$array['update_num']=$update_num;
		$array['month_punish']=$month_punish;
		$array['month_fulfil_pay']=$month_fulfil_pay                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  ;
	//月榜排名
		(int)$ranking=$Ob->where("y='$y' && m='$m' && d='$d' && monthscore>'$monthscore' ")
				->count();
		$ranking+=1;
		if($ranking==1){
			$month_ranking_pay=500;
		}elseif($ranking==2){
			$month_ranking_pay=300;
		}elseif($ranking==3){
			$month_ranking_pay=100;
		}else{
			$month_ranking_pay=0;
		}
		$array['ranking']=$ranking;
		$array['month_ranking_pay']=$month_ranking_pay;
	//累计罚款
		$arr = M('kdgx_public_finance')
			->field('day_fine')
			->where("public_num='$public_num' && y='$y' && m='$m' ")
			->select();
		$fine=0;
		foreach($arr as $v){
			$fine+=$v['day_fine'];
		}
		$array['fine']=$fine;
		$re=M('kdgx_public_score')->where("public_num='$public_num' && y='$y' && m='$m' && isviolate='4' ")->find();
		if($re){
			$array['severity']=1;
		}else{
			$array['severity']=0;
		}
		$this->ajaxReturn($array);
	}

		/*
	 * 实发 日月工资
	 */
	function day_pay() {
 
/*    		$_POST=array(
				'public_num'=>'koudaishida',
				'timestamp'=>1478361600,
				'day_real_pay'=>0,
				'day_remark'=>'ssss',
				'day_pay'=>0,
				'day_bours'=>0,
				'repay'=>0,
				'ismoney'=>'2'
		);      */
		$public_num = $_POST ['public_num'];
		$timestamp = $_POST ['timestamp'];
		$s=M('kddx_public')->where("public_num='$public_num'")->find();
		$pid=$s['id'];
		if ($_POST ['ismoney'] == 1) {
			$re = M ( 'kdgx_public_score' )
					->where ( "public_num='$public_num' && timestamp='$timestamp'" )
					->data ( array (	'ismoney' => '1' ) )
					->save ();
			if ($re) {
				$re = M ( 'kdgx_public_finance' )
						->where ( "public_num='$public_num' && timestamp='$timestamp'" )
						->data ( $_POST )->save ();
				if ($re) {
					$array['msg']=1;
				}else{
					$array['msg']=0;
				}
			} else {
				$array ['msg'] = 0;
			}
		} else {
			$re = M ( "kdgx_public_score" )
						->where ( "public_num='$public_num' && timestamp='$timestamp'" )
						->data ( array (	'ismoney' => '2') )
						->save ();
			if ($re) {
				$Ob = M ( "kdgx_public_finance" );
				$rs = $Ob->where ( "public_num='$public_num'&&timestamp='$timestamp'" )->data ( $_POST )->save ();
				 if ($rs) {
////////////
				 	$operArr=M('kdgx_operator')->where("lead='1'")->select();
				 	foreach ($operArr as $v){
				 		$leadArr=explode('-', $v['public_lead']);
				 		foreach ($leadArr as $v2){
				 			if($v2 == $pid ){
				 				$name=$v['name'];
				 				$uid=$v['uid'];
				 			}
				 		}
				 	}
/////////////
/* 					$sql = "select o.uid,o.name from kdgx_operator as o
						inner join kddx_public as p
						on p.public_id=o.public_manager
						where p.public_num='$public_num' && o.lead='1'
						";
					$arr = M ()->query ( $sql );
					$name = $arr [0] ['name'];
					$uid = $arr [0] ['uid']; */
					$day_real_pay = $_POST ['day_real_pay'];
					$amount = $day_real_pay*100;
					$desc ='日榜红包';
					$data = array (
							're_user_name' => $name,
							'uid' => $uid,
							'amount' => $amount,
							'desc' => $desc 
					);
					$url = "www.pocketuniversity.cn/index.php/home/wechat/pay";
					$xml = requestPost ( $url, $data );				//获取xml
					$arr=	xmlToArray($xml);								//xml转数组			
		//			var_dump($arr);
					if($arr['result_code']=='SUCCESS'){				//判断状态result
						$array['msg']=1;
						//模板消息
						$url="http://www.pocketuniversity.cn/index.php/home/Wechat/sendTemplate";
						$remark=$_POST['day_remark'];
						$data=array(
								'first' => '口袋高校@你查收今日日榜奖金',
								'uid' => $uid,
								'keyword1' => date ( "Y年m月", $timestamp ),
								'keyword2' => '日榜奖金:' . $day_real_pay . '元',
								'keyword3' => date ( "Y年m月d日", $timestamp ),
								'remark' => $remark,
								'msgType' => '日榜奖金发放',
								'type' => '1',
								'url' => "http://" . C ( 'WEB_SITE' ) . "/index.php/bangdan?get=index",
						);
						$a =requestPost($url, $data);
					//	file_put_contents('./Logs/bangdan.log', date('Y-m-d H:i:m').'|'.print_r($response,true),FILE_APPEND);
						
					}elseif($arr['result_code']=='FAIL'){
						M('kdgx_public_score')
							->where("public_num='$public_num' && timestamp='$timestamp'")
							->data(array('ismoney'=>'0'))
							->save();
						M('kdgx_public_finance')
								->where("public_num='$public_num' && timestamp='$timestamp'")
								->data(array(
										'day_pay'=>0,
										'day_bours'=>0,
										'repay'=>0,
										'day_real_pay'=>0,
										'day_remark'=>''  ))
								->save();
							$array['msg']=0;
					}
				} else {
					$array ['msg'] = 0;
				}
			}
		}
		$this->ajaxReturn ( $array );
	}
	/**
	 * 
	 */
		function month_pay(){
			$public_num=$_POST['public_num'];
			$timestamp = $_POST['timestamp'];
			$re = M ( "kdgx_public_score" ) 
			->where ( "public_num='$public_num' && timestamp='$timestamp'" )
			->data ( $_POST )
			->save (); 
			if ($re) {
				$Ob = M ( "kdgx_public_finance" );
				$re = $Ob->where ( "public_num='$public_num'&&timestamp='$timestamp'" )->find ();
				if ($re) {
					$rs = $Ob->where ( "public_num='$public_num'&&timestamp='$timestamp'" )->data ( $_POST )->save ();
					if ($rs) {
						$array ['msg'] = 1;
					} else {
						$array ['msg'] = 0;
					}
				} else {
					$rs = $Ob->data ( $_POST )->add ();
					if ($rs) {
						$array ['msg'] = 1;
					} else {
						$array ['msg'] = 0;
					}
				}
			}else{
				$array['msg'] = 0;
			}
			$this->ajaxReturn($array);
		}

	/**
	 * 罚款及模板消息
	 */
	function fine(){ 
		$public_num=$_POST['public_num'];
		$timestamp=$_POST['timestamp'];
		$isviolate=$_POST['isviolate'];
		
		if($isviolate=='1' || $isviolate=='3'){
			$re= M("kdgx_public_score")
			->where("public_num='$public_num' && timestamp='$timestamp'")
			->data(array('isviolate'=>$isviolate))
			->save();
			if($re){
					$rs=M('kdgx_public_finance')
					->where("public_num='$public_num' && timestamp='$timestamp'")
					->data($_POST)
					->save();
					if($rs){
						$array['msg']='1';
					}else{
						$array['msg']='0';
					}
			}else{
				$array['msg']='0';
			}
		}else{
 		$re=M('kdgx_public_score')->where("public_num='$public_num' && timestamp='$timestamp'")
				->data(array('isviolate'=>"$isviolate"))
				->save();
			if ($re) {
				$array ['msg'] = '1';
			} else {
				$array ['msg'] = '0';
			} 
		}

	 		$this->ajaxReturn($array);
	}
		
		
		
		

		
}

