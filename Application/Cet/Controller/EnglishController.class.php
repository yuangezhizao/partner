<?php
namespace Cet\Controller;
use Think\Controller;

class EnglishController extends Controller {

	  private $_msg_template = array(
      // 文本消息模板
      'text'=>"<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime>
              <MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content><FuncFlag>0</FuncFlag></xml>",
      // 图文消息
      'news'=>"<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName>
              <CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><ArticleCount>%s</ArticleCount>
               <Articles><item><Title><![CDATA[%s]]></Title><Description><![CDATA[%s]]></Description>
               <PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item></Articles>
              <FuncFlag>%s</FuncFlag></xml> ", 
    );


	public function reponseMsg($media_id){
		//1.获取到微信推送过来的post数据（xml格式）
	    $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
	    libxml_disable_entity_loader(true);
	    $postObj = simplexml_load_string( $postArr ,'SimpleXMLElement',LIBXML_NOCDATA);
	    $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $msgType = "news";
        $title = '口袋四六级查询';
        $description = '迅速了解四六级成绩详情';
        $PicUrl = 'http://static.pocketuniversity.cn/kdgx/PocketDoc/library/images/cet.png';
	    $from = '/index.php/Cet/English/saveTicketNumber';
        $url = "http://".C('WEB_SITE')."/index.php/home/user/login?flag=3&school=".$media_id."&from=".urlencode($from);
        $FuncFlag = 0;
	    $resultStr = sprintf($this->_msg_template['news'],$fromUsername,$toUsername,$time,$msgType,1,$title,$description,$PicUrl,$url, $FuncFlag);
        echo  $resultStr;
	}


	// 通过media_id获取登记信息
	public function getDataForMedia(){
		if(empty($_POST['media_id'])){
			echo json_encode(array(
				'error' => 50001,
				'msg' => '参数错误',
				));exit();
		}
		$data =M('kdgx_cet_ticket_number')->field('id,media_id,email,phone,uid,username,ticket_number,time,listening_grade,reading_grade,writing_grade')->where(array('media_id'=>$_POST['media_id']))->select();
		echo json_encode($data);exit();
	}


    //保存准考证号码
	public function saveTicketNumber(){
		$getMediaInfo = new \Cet\Model\getMediaInfoModel; 
        $mediaInfo = json_decode($getMediaInfo -> getInfo(),true);
       	$media_name = $mediaInfo['name'];
       	cookie('media_name',$media_name);
       	$uid = decrypt_url(cookie('uid'),C("KEY_URL"));
		$condition = array(
				'media_id' => cookie('media_id'),
				'uid' => $uid,
				);
    		if($id = M('kdgx_cet_ticket_number')->where($condition)->find()){
    			$url = U('checkTicketNo');
    			header("location:".$url);
    		}		
		//echo cookie('media_id');
		$this -> display();
	}
	//保存成功
	public function saveSuccess(){
		$this -> display();
	}
	//核对及修改准考证信息
	public function checkTicketNo(){
		$getMediaInfo = new \Cet\Model\getMediaInfoModel; 
        $mediaInfo = json_decode($getMediaInfo -> getInfo(),true);
       	$media_name = $mediaInfo['name'];
       	cookie('media_name',$media_name);

		$media_id = cookie('media_id');
		$uid = decrypt_url(cookie('uid'),C("KEY_URL"));
		if(!empty($media_id) && !empty($uid)){
			$ticketNo = M('kdgx_cet_ticket_number');
			$condition = array(
				'media_id'=>$media_id,
				'uid' => $uid
				);
			$res = $ticketNo->where($condition)->find();
			if(!empty($res)){
				$userdata = array(
					'username' => $res['username'],
					'phone' => $res['phone'],
					'email' => $res['email'],
					'ticket_number' => $res['ticket_number'],
					);
				$this->assign('userdata',$userdata);
			}
		}
		$this -> display();
	}
	//修改成功
	public function changeSuccess(){
		$this -> display();
	}



	public function ajaxHandle(){
		if(!IS_AJAX){
    		E('页面不存在');
    	}
    	$username = I('username');
    	$email = I('email');
    	$phone = I('phone');
    	$ticketNo = I('ticketNo');
    	if( !empty($username) && !empty($email) && !empty($phone) && !empty($ticketNo) ){
    		$uid = decrypt_url(cookie('uid'),C("KEY_URL"));
			$data = array(
				'media_id' => cookie('media_id'),
				'uid' => $uid,
				'username' => strip_tags(I('username')),
				'email' => strip_tags(I('email')),
				'phone' => I('phone'),
				'ticket_number' => I('ticketNo'),
				'time' => time(),
				);		
			if($id = M('kdgx_cet_ticket_number')->data($data)->add()){
				$this->ajaxReturn(array('status'=>1),'json');
			}else{
				$this->ajaxReturn(array('status'=>0),'json');
			}
    	}
    	$username2 = I('username2');
    	$email2 = I('email2');
    	$phone2 = I('phone2');
    	$ticket_number2 = I('ticket_number2');
    	if( !empty($username2) && !empty($email2) && !empty($phone2) && !empty($ticket_number2) ){
			/*$this->ajaxReturn(array('status'=>1),'json');	*/
    		$data = array(
				'email' => strip_tags(I('email2')),
				'phone' => I('phone2'),
				'ticket_number' => I('ticket_number2'),
				'time' => time(),
				);
    		$condition = array(
    			'media_id' => cookie('media_id'),
				'uid' => decrypt_url(cookie('uid'),C("KEY_URL")),
    			'username' => strip_tags(I('username2'))
    			);
    		if($id = M('kdgx_cet_ticket_number')->data($data)->where($condition)->save()){
    			$this->ajaxReturn(array('status'=>1),'json');
			}else{
				$this->ajaxReturn(array('status'=>0),'json');
			}
    	}

	}
	public function getEnglishGrade(){
		$users = M('kdgx_cet_ticket_number');
		$res = $users->select();
		/*echo "<pre>";
		print_r($res);*/
		foreach ($res as $key => $value) {
			$ticket_number = $value['ticket_number'];
			$username = $value['username'];
			//设置post的数据
			$post = array(
			    'id' => $ticket_number,
			    'name' => $username
			);
			$url = "https://cet.99sushe.com";
			//设置cookie保存路径
			$cookie = dirname(__FILE__) . '/cookie_digital-hdu.txt';
			//登录后要获取信息的地址
			$url2 = "";
			//模拟登录
			$this->login_post($url, $cookie, $post);
			//获取登录页的信息
			$content =$this->get_content($url2, $cookie);
			echo "<pre>";
			var_dump($content);
		}
	}
	function login_post($url, $cookie, $post) {
		$curl = curl_init();//初始化curl模块
		curl_setopt($curl, CURLOPT_URL, $url);//登录提交的地址
		curl_setopt($curl, CURLOPT_HEADER, 0);//是否显示头信息
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);//是否自动显示返回的信息
		curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); //设置Cookie信息保存在指定的文件中
		curl_setopt($curl, CURLOPT_POST, 1);//post方式提交
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));//要提交的信息
		$con = curl_exec($curl);//执行cURL
		curl_close($curl);//关闭cURL资源，并且释放系统资源
		return $con;
	}
	//登录成功后获取数据
	function get_content($url, $cookie) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie); //读取cookie
		$rs = curl_exec($ch); //执行cURL抓取页面内容
		curl_close($ch);
		return $rs;
	}
}