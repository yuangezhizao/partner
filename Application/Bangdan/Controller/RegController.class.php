<?php
namespace Bangdan\Controller;
use Bangdan\Controller\BaseController;
class RegController extends BaseController {
	
	/**
	 * 社会号注册界面
	 */
	Public function index(){
		$this->display();
	}
	/**
	 * 社会号等待审核界面
	 */
	Public function check(){
		$this->display();
	}
	
/**
 * 提醒微校扫码
 */
	Public function qrcode(){
		$this->display();
	}
	
	/**
	 * 注册接口数据
	 */
	Public function data(){
		$media_id=$_SESSION['media_id'];
		//头像
		$uid=$_SESSION['uid'];
		$uid = decrypt_url ( $uid, 'pocketuniversity' );			//uid解密
		$rs=M('kddx_user_info')->where("uid='$uid'")->find();
		$arr['header_img']=$rs['header_img'];
		$arr['nick']=$rs['nick'];
		//个人信息
			$rs=M('kdgx_wx_media_info')->where(array('id'=>$media_id))->find();
			$arr['public_id']=$rs['media_id'];
			$arr['public_name']=$rs['name'];
			if(empty($rs['media_number'])){
				$arr['public_num']=$rs['media_id'];
			}else{
				$arr['public_num']=$rs['media_number'];
			}
			$arr['school']=$rs['school_name'];
			$this->ajaxReturn($arr);
	}
	/**
	 * 申请注册
	 */
	Public function reg() {
		if (! empty ( $_POST )) {
			$public_id = $_POST ['public_id'];
			$re = M ( 'kddx_public' )->where ( "public_id='$public_id'" )->find ();
			// 公众号资料入库
			// kddx_public表里没有此数据则入库
			if (! $re) {
				$publicArr ['is_pocket'] = '0';
				$public_id = $_POST ['public_id'];
				$publicArr ['public_id'] = $public_id;
				$publicArr ['public_name'] = $_POST ['public_name'];
				$publicArr ['public_num'] = $_POST ['public_num'];
				$publicArr ['school'] = $_POST ['school'];
				$publicArr ['college_station'] = $_POST ['college_station'] ? $_POST ['college_station'] : '';
				$publicArr ['fans_scale'] = $_POST ['fans_scale'];
				$publicArr ['first_price'] = $_POST ['first_price'] ? $_POST ['first_price'] : '0';
				$publicArr ['second_price'] = $_POST ['second_price'] ? $_POST ['second_price'] : '0';
				$publicArr['public_type']='5';
				$publicArr ['is_pocket'] = '0';
				$re = M ( 'kddx_public' )->data ( $publicArr )->add ();
				if (! $re) {
					$array ['msg'] = '0';
				}
			}
			// 运营者资料入库
			// kdgx_public_operator入库
			$uid = $_SESSION ['uid'];
			$uid = decrypt_url ( $uid, 'pocketuniversity' );			//uid解密
			$operArr ['uid'] = $uid;
			$operArr ['public_id'] = $_POST ['public_id'];
			$operArr ['name'] = $_POST['name'];
			$rs = M ( 'kddx_user' )->field ( 'phone' )->where ( array (
					'uid' => $uid 
			) )->find ();
			$phone=$rs['phone'];		
			$operArr ['phone'] = $phone;
			$operArr ['regtime'] = time ();
			$rs = M ( 'kddx_public' )->where ( "public_id='$public_id'" )->find ();
			$list = $rs ['id'];
			$operArr ['public_list'] = '0-' . $list;
			$re = M ( 'kdgx_operator' )->where ( "uid='$uid'" )->find ();
			if ($re) {
				$listArr = explode ( "-", $re ['public_list'] );
				if (in_array ( $list, $listArr )) {
					$timestamp = time ();	
					$sql = "UPDATE `kdgx_operator` SET timestamp='$timestamp' ) WHERE  uid='$uid' ";
				} else {
					$sql = "UPDATE `kdgx_operator` SET public_list = CONCAT( public_list ,'-$list' ) WHERE  uid='$uid'";
				}
				M ()->execute ( $sql );
				if ($re) {
					$array ['msg'] = '1';
				} else {
					$array ['msg'] = '0';
				}
			} else {
				$re = M ( 'kdgx_operator' )->data ( $operArr )->add ();
				if ($re) {
					$array ['msg'] = '1';
				} else {
					$array ['msg'] = '0';
				}
			}
		} else {
			$array ['msg'] = 0;
		}
		$this->ajaxReturn($array);
	}

	
	
}	
