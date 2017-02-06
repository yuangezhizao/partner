<?php
namespace Bangdan\Controller;
use Bangdan\Controller\BaseController;
class IndexController extends BaseController {
   public function index(){
   	$uid= $_SESSION['uid'];
   $uid=	decrypt_url ( $uid, 'pocketuniversity' );
   	$media_id = $_SESSION ['media_id'];
//--------------无码-------------------//
   	if(empty($media_id)){
   		$re=	M('kdgx_operator')->where("uid='$uid'")->find();
   	
   		if($re){
   			if ($re ['state'] == '0') {
   				$this->redirect ( "Reg/qrcode" );exit ();
   			} else {
   				$public_id = $re ['public_id'];
   				$re = M ( 'kddx_public' )->where ( "public_id='$public_id'" )->find ();
   				if ($re ['is_show'] == '1') {
   					$_SESSION ['public_num'] = $re['public_num'];
   					$this->display("Index/index");
   				} elseif ($re['is_show']=='0'){
   					$this->display("Reg/check");exit();
   				}
   			}
   		}else{
   				$this->display("Reg/qrcode");
   			exit ();
   		}
   	}else{
// ----------有码---------------//
			$rs = M ( 'kdgx_wx_media_info' )->field ( "media_id" )->where ( "id='$media_id'" )->find ();
			$public_id = $rs ['media_id'];
			$rs = M ( 'kddx_public' )->field ( "id,public_id" )->where ( "public_id='$public_id'" )->find ();
			$pid = $rs ['id'];
			$rs = M ( 'kdgx_operator' )->field ( "public_list" )->where ( "uid='$uid'" )->find ();
			if($rs){
				$public_list = $rs ['public_list'];
				$public_list = substr ( $public_list, 2 );
				$listArr = explode ( "-", $public_list );
				if(in_array($pid, $listArr)){
					unset($_SESSION['media_id']);
					$this->redirect("Index/index");exit();
				}else{
					$this->redirect("Reg/index");exit();
				}
			}else{
				$this->redirect("Reg/index");exit();
			}
   	}
	}
	
	
	
	/**----------------------------------【每日详细指数】---------------------------------*/
	Public function points() {
 		$public_num=$_SESSION['public_num'];
 		
 		$time=strtotime("-1 day -13 hours");
		$re=M('kdgx_public_score')
				->where("public_num='$public_num'  && timestamp<='$time'")
				->group('timestamp')
				->order('timestamp desc')
				->limit(0,7)
				->select();
		$a=7;
		$r=M('kdgx_public_data')->field("timestamp")->group("timestamp")->order("timestamp desc")->find();	
		$time8=$r['timestamp'];
		$array['id8']['month']=date("m",get_time($time8, "+1day"));
		$array['id8']['theDate']=date("d",get_time($time8, "+1day "));

		foreach($re as $k=>$v){
			$y=$v['y'];
			$m=$v['m'];
			$d=$v['d'];
			$array['id'.$a]['month']=$m;
			$array['id'.$a]['theDate']=$d;
			$array['id'.$a]['data']['data_b']=$v['score'];
			$array['id'.$a]['data']['data_a']=$v['monthscore'];
			$num = M('kdgx_public_score')->where("public_num='$public_num' && y='$y' && m='$m' &&d<='$d' && score!='0'")->count();
			$array['id'.$a]['data']['data_c']=$num;
			$array['id'.$a]['data']['data_d']=$v['fans'];
			$re = M('kdgx_public_data')->field('article_clicks_count,article_likes_count')->where("public_num='$public_num' && y='$y' && m='$m' &&d='$d'")->find();
			$array['id'.$a]['data']['data_e']=$re['article_clicks_count'];
			$array['id'.$a]['data']['data_f']=$re['article_likes_count'];
			$a--;
		} 

		$this->ajaxReturn($array);
	}


	/**--------------------------------------------【七天指数】-------------------------------------------*/
	public function data() {
		$public_num = $_SESSION ['public_num'];
		for($i = 7; $i >= 1; $i --) {
			$for_y = date ( "Y", strtotime ( "-$i day  -13 hours" ) );
			$for_m = date ( "m", strtotime ( "-$i day  -13 hours" ) );
			$for_d = date ( "d", strtotime ( "-$i day  -13 hours" ) );
			$array [0] [] = $for_m . "." . $for_d;
			$timestamp = mktime ( 0, 0, 0, $for_m, $for_d, $for_y );
			$re = M ( "kdgx_public_score" )->where ( " public_num = '$public_num' && timestamp='$timestamp'" )->find ();
			if (! empty ( $re )) {
				$array [1] [] = $re ['score'];
			} else {
				$array [1] [] = 0;
			}
		}
		$re = M ( "kddx_public" )->where ( "public_num='$public_num'" )->find ();
		$array ['public_name'] = $re ['public_name'];
		$this->ajaxReturn ( $array );
	}
	
	public function  accounts(){
		$arr=M("kddx_public")->field("id,public_id,public_name,public_num,school")->select();
		$this->ajaxReturn($arr);
	}
	/**
	 * 运营的所有公众号
	 */
	Public function publics(){
		$uid=$_SESSION['uid'];
  		$uid=	decrypt_url ( $uid, 'pocketuniversity' );
		$rs = M ( 'kdgx_operator' )->field ( 'public_list,public_id' )->where ( "uid='$uid'" )->find ();
		$public_list = $rs ['public_list'];
		$public_list = substr ( $public_list, 2 );
		$listArr = explode ( "-", $public_list );
	 	foreach ($listArr as $k=>$v){
			$re=M('kddx_public')->field('public_id,public_num,public_name')->where("id=$v")->find();
			$data[$k]['public_id']=$re['public_id'];
			$data[$k]['public_num']=$re['public_num'];
			$data[$k]['public_name']=$re['public_name'];
			if($rs['public_id']==$re['public_id']){
				$data[$k]['state']=1;
			}else{
				$data[$k]['state']=0;
			}
		} 
  		$this->ajaxReturn($data);
	}
	
	/**
	 * 修改默认公众号
	 */
	Public function update(){ 
		$public_id=$_POST['public_id'];
		$uid=$_SESSION['uid'];
  		$uid=	decrypt_url ( $uid, 'pocketuniversity' );
  		$re=M('kdgx_operator')->where("uid='$uid'")->save(array('public_id'=>$public_id));
		if($re){
			$array['msg']='1';
			$rs=M('kddx_public')->field("public_num")->where("public_id='$public_id'")->find();
			$_SESSION['public_num']=$rs['public_num'];
		}else{
			$array['msg']='0';
		}
		$this->ajaxReturn($array);
	}
	
	
	
	
	
	
	
	
	
	
	
}