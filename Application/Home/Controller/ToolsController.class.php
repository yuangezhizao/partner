<?php 
namespace Home\Controller;
use Common\Controller\HomeBaseController;

class ToolsController extends HomeBaseController{

	// 写入日志
	public function Collect(){
		$request_name = $_POST['request_name'];
		$module = $_POST['module'];
		$collect = new \Org\Util\DataCollect(array('module'=>$module));
        $collect->CollectResponse($request_name,decrypt_url(cookie('uid'),C("KEY_URL")),cookie('media_id'));
	}

	// 卖室友月数据统计
	public function StatisticalMonthPartner(){
		$date = $_GET['date'];
		// $date = '2016-11';
		if(empty($date)){
			exit('参数错误');
		}
		$first_time = strtotime($date);
		$days = date('t', strtotime($date));
		$Statistical = self::StatisticalPartner($first_time,86400,$days);
		echo json_encode($Statistical);exit();
	}

	// 卖室友日数据统计
	public function StatisticalDayPartner(){
		$date = $_GET['date'];
		// $date = '2016-12-18';
		if(empty($date)){
			exit('参数错误');
		}
		$first_time = strtotime($date);
		$Statistical = self::StatisticalPartner($first_time,7200,12);
		echo json_encode($Statistical);exit();
	}

	// 统计卖室友数据
	// $start_time 开始时间
	// $time_interval 时间间隔
	// $times 次数
	private function StatisticalPartner($first_time,$time_interval,$times){
		$uvarray = ['1','6','8'];
		$Statistical = M('kdgx_data_request')->select();
		foreach ($Statistical as $key => $value) {
			$Statistical[$key]['count']=array();
			$Statistical[$key]['countuv']=array();
			$end_time = $first_time;
			for($j=0;$j<$times;$j++){
				$start_time = $end_time;
				if($start_time<strtotime('2016-12-19')){
					$table_name = 'kdgx_data_response20161218';
				}else{
					$table_name = 'kdgx_data_response';
				}
				$end_time = $start_time+$time_interval;
				$data = M($table_name)->field('request_id,count(request_id) as count')
				->where(array('module_id'=>'1','response_time'=>array('between',array($start_time,$end_time))))
				->group('request_id')->select();
				$sql = "select request_id,count(request_id) as count from (select distinct uid,request_id from $table_name 
		 		where module_id='1' and response_time>$start_time and response_time<$end_time group by uid,request_id) as table_a group by request_id";
				$datauv = M()->query($sql);
				$sql = "select request_id,count(request_id) as count from (select distinct request_from,request_id from $table_name 
		 		where module_id='1' and response_time>$start_time and response_time<$end_time group by request_from,request_id) as table_a group by request_id";
				$datauv1 = M()->query($sql);
				$count = 0;
				$countuv = 0;
				foreach ($data as $akey => $arr) {
					if($value['id']==$arr['request_id']){
						$count = $arr['count'];
						break;
					}	
				}				
				foreach ($datauv as $auvkey => $arruv) {
					if($value['id']==$arruv['request_id']){
						if(in_array($value['id'], $uvarray)){
							$countuv = $datauv1[$auvkey]['count'];
						}else{
							$countuv = $arruv['count'];
						}
						break;
					}	
				}
				array_push($Statistical[$key]['count'],$count);
				array_push($Statistical[$key]['countuv'],$countuv);
			}
			$Statistical[$key]['total'] = array_sum($Statistical[$key]['count']);
			$Statistical[$key]['totaluv'] = array_sum($Statistical[$key]['countuv']);
		}
		return $Statistical;
	}


	// 获取公告
	public function getNotice(){
		if(empty($_POST['id'])){
			echo json_encode(array(
				'error' => 40001,
				'msg' => '参数错误',
				));exit();
		}
		$notice = M('kdgx_notice')->where(array('id'=>$_POST['id']))->find();
		echo json_encode($notice);exit();
	}

	// 更新公告
	public function updateNotice(){
		if(empty($_POST['id']) || empty($_POST['content'])){
			echo json_encode(array(
				'error' => 40001,
				'msg' => '参数错误',
				));exit();
		}
		if(M('kdgx_notice')->where(array('id'=>$_POST['id']))
			->save(array('content'=>$_POST['content'],'time'=>time()))){
			echo json_encode(array(
				'error' => 0,
				'msg'=> '成功',
				));exit();
		}else{
			echo json_encode(array(
				'error'=>40002,
				'msg'=>'更新失败',
				));exit();
		}
	}

	// 上次图片
	public function uploadImg(){
		$upload = new \Org\Util\UploadBase64(array('rootPath'=>'D:\VirtualHost\Uploads/Uploads/'));
    $info = $upload->upload($_POST['img']);
    if(empty($info)){
      echo json_encode(array('error'=>40025,'msg'=>'图片上传错误'));return;
    }else{
      $info = substr($info, strlen("D:\VirtualHost\Uploads"));
      $url = "http://".C('CDN_SITE').$info;
      requestGet($url);
      $_POST['mood_img'] = $info;
      echo json_encode(array(
      	'error' => 0,
      	'msg'	=>$url
      	));exit();
    }
	}
}