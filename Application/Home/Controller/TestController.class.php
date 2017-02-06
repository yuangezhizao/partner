<?php 
namespace Home\Controller;
use Common\Controller\HomeBaseController;

/**
 * 测试用控制器可删除
 */
class TestController extends HomeBaseController{
	//测试用方法
	// public function test(){
	// 	$keyword = '卖室友2Dh68dXA';
	//     $con=mysql_connect("121.41.36.196","koudai","vmW35XnAv8h2xCTD");
	//     mysql_select_db("koudai", $con);
	//     $sql = "SELECT id FROM kdgx_partner WHERE secret='" . $keyword . "'";
	//     $result = mysql_query($sql);
	//     // echo $sql;
	//     $data =array();
	//     while ($row = mysql_fetch_assoc($result)) {
	//     	$data[] = $row;
	//     }
	//     print_r($data);
	// 	 if(!empty($data)){
	// 	 	echo 1;
	// 	 }else{
	// 	 	echo 2;
	// 	 }
	// }

	// //测试模板消息
	// public function testTemplate(){
	// 	$public_id = 'gh_3347961cee42';
	// 	$cfg = array(
	// 		'app_id' =>	'wx3cdae4e674abafd2',
	// 		'app_secret' => 'd4624c36b6795d1d99dcf0547af5443d',
	// 	);
	// 	$sendTemplate = new \Org\Util\Wechat($cfg);
	// 	$openid = 'oA7YgwVup-GEmigTgNOjE9xu5jlU';
	// 	$result = $sendTemplate->sendTemplateMsg($openid);
	// 	print_r($result);
	// }
	//删除数据库uid用户信息
	public function deleteUid(){
		$uid = $_GET['uid'];
		$public_id = $_GET['public_id'];
		$re1 = M('kddx_user_info')->where(array('uid'=>$uid))->delete();
		if($re1){
			$re2 = M('kddx_user')->where(array('uid'=>$uid))->delete();
			if($re2){
				$re3 = M('kddx_user_openid')->where(array('uid'=>$uid,'public_id'=>$public_id))->delete();
				if($re3){
					echo "成功";
				}else{
					exit("删除openid失败!");
				}
			}else{
				exit("删除user信息失败!");
			}
		}else{
			exit("删除user_info信息失败!");
		}
	}
}