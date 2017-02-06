<?php 
namespace Debug\Controller;
use \Think\Controller;

class IndexController extends Controller{
	public function Index(){
		$current_date = date('Y-m-d');
		$data = array();
		for ($i=0; $i < 7; $i++) {
			switch ($i) {
			 	case 0:
					$time[1] = strtotime(date('Y-m-d'));
					$time[2] = strtotime("$current_date +1 day");
			 		break;			 	
		 		case 1:
					$time[1] = strtotime("$current_date -1 day");
					$time[2] = strtotime(date('Y-m-d'));
		 			break;			 	
		 		case 2:
					$time[1] = strtotime("$current_date -2 day");
					$time[2] = strtotime("$current_date -1 day");
			 		break;			 	
		 		case 3:
					$time[1] = strtotime("$current_date -3 day");
					$time[2] = strtotime("$current_date -2 day");
			 		break;			 	
		 		case 4:
					$time[1] = strtotime("$current_date -4 day");
					$time[2] = strtotime("$current_date -3 day");
			 		break;			 	
		 		case 5:
					$time[1] = strtotime("$current_date -5 day");
					$time[2] = strtotime("$current_date -4 day");
			 		break;			 	
		 		case 6:
					$time[1] = strtotime("$current_date -6 day");
					$time[2] = strtotime("$current_date -5 day");
			 		break;			 	
		 		case 7:
					$time[1] = strtotime("$current_date -7 day");
					$time[2] = strtotime("$current_date -6 day");
			 		break;
			 } 
			$total = M('kddx_user')->where(array('regtime'=>array('between',array($time[1],$time[2]))))->count();
			$ta = M('kddx_user')->alias('u')->join('JOIN kddx_user_info as ui ON u.uid=ui.uid')->where(array('ui.school'=>array('neq','杭州电子科技学院'),'u.regtime'=>array('between',array($time[1],$time[2]))))->count();
			$to = M('kddx_user')->where(array('regtime'=>array('between',array($time[1],$time[2])),'phone'=>array('NEQ','')))->count();
			$data[] = array(
				'time' => date('Y-m-d',$time[1]),
				'total' => $total,
				'ta'	=> $ta,
				'to'	=> $to,
			);
		}
		$this->assign('data',$data);
		$this->display();
	}
	public function test(){
		$media = "./Uploads/2016-10-31/7b00f128321690849d72d3c91bac0ac0.png";
		$wechat = new \Org\Util\Wechat();
		$data = $wechat->getQRcode();
	}
	public function simulaWechat(){
		switch($_GET['flag']){
			case 'hyp':
				$user_info = '{"openid":"oDDzCtzrliybi0Nbg2BL7l3WoCIc","nickname":"Karen","sex":2,"language":"zh_CN","city":"","province":"","country":"中国","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/klp1vKAA8eEiboyEMXQsJX8vCbQbIErfMweV14Bfmppl7fibEkcKNicY5ozOGVLNGrDdpR96pVibUHGFH2GqjM7D6S4QReHWxvvU\/0","privilege":[],"unionid":"ohU1js1E2wFz4QoDwXrGMDKGpYiU"}';
				echo 'hyp';
				break;
			case 'dsm':
				$user_info = '{"openid":"oDDzCt0BvwLd01WMst62JtN0XUqs","nickname":"摇了摇头","sex":1,"language":"zh_CN","city":"南平","province":"福建","country":"中国","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/licJweB18pXia2VwBnib6XmibVdzXia1P4jNGawlXqosicg9u0EI6DJD3nYHLJicWP1Gr9QIK1Ayp1AoM81I9Eia2wWR8rjg4WHL4yIO\/0","privilege":[],"unionid":"ohU1js56uR7Pu12JB0qEutXYfla4"}';
				echo "dsm";
				break;
			case 'Sure':
				$user_info = '{"openid":"oDDzCt8vjRkjkOf6cRtsDAsAUjFU","nickname":"少年·Sure","sex":1,"language":"zh_CN","city":"杭州","province":"浙江","country":"中国","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/68S8BPMLu7Nx3qiamAcS8CVpuhGDOL0h3ZuRia6ErhQoQhjRQYkr4qDDSpycX2bHwPdQ4z52vrIRXZh2iaibyicZjnaQOyKsL7kkia\/0","privilege":[],"unionid":"ohU1js-vCW-EzoAzoqJlRqPkDOM0"}';
				echo 'Sure';
				break;
			case 'cy':
				$user_info = '{"openid":"oDDzCtza5EhpJQMKAGKWuuQDE_FY","nickname":"陈友","sex":1,"language":"zh_CN","city":"扬州","province":"江苏","country":"中国","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/68S8BPMLu7Nx3qiamAcS8Ca1kRVFIHVgXS7EImibIPQ2zEGIdX66hUEN8TpcXykte4ZKXnKnLiaFRSOD9Nd9tngibSj2iaR8gPGEc\/0","privilege":[],"unionid":"ohU1js_Kx4pTaaJxMPlvu5efN5ZY"}';
				echo 'cy';
				break;
			case 'lx':
				$user_info = '{"openid":"oDDzCt4k9IeNNSssBMG_rt67PJSI","nickname":"你有病啊","sex":1,"language":"zh_CN","city":"浦东新区","province":"上海","country":"中国","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/licJweB18pXia2VwBnib6XmibS9guicyNGlAA4ib19YyIvN1WYt9dxORMWLSwxXficG0SHt1LZuV7GL27VaiarFP9DZzRF3LhtwicqIKq\/0","privilege":[],"unionid":"ohU1js3eLxLGvQW_Jq2pBkKC42Dk"}';
				echo 'lx';
				break;
			default:
				echo "error";
				break;
		}
		session('loginInfo',serialize($user_info));
	}

	public function commitView(){
		$data = M('kdgx_partner_commit')->field('m.name,c.commit')->alias('c')->join('__JOIN__ kdgx_wx_media_info as m ON c.commit_from=m.media_id')->select();
		echo M('kdgx_partner_commit')->getError();
		$this->assign('data',$data);
		$this->display();
	}

	public function getCollect(){
		$data = M()->execute("SELECT count(q.request_name)as'count',q.request_name FROM koudai.kdgx_data_response as p join koudai.kdgx_data_request as q on p.request_id=q.id group by q.request_name");
		$this->assign('data',$data);
		$this->display();
	}
}