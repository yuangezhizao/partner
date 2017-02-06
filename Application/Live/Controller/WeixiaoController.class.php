<?php 
namespace Live\Controller;
use \Think\Controller;

// 微校接口管理
class WeixiaoController extends Controller{
    
    public function index() {
        $type = $_GET['type'];
        switch($type) {
            case 'open' :
                return $this->open();
                break;
            case 'close' :
                return $this->close();
                break;
            case 'config' :
                $this->config();
                break;
            case 'monitor' :
               $this->monitor();
                break;
            case 'trigger' :
               $this->trigger();
                break;
            case 'keyword' :
               $this->keyword();
                break;
            default:
                break;
        }
    }
    // 开启应用
    public function open() {
	    $post_data = file_get_contents('php://input');
	    $param_array = json_decode($post_data, true);
	    $sign = $param_array['sign'];
	    unset($param_array['sign']);
	    $cal_sign = $this->_cal_sign($param_array);
	    /** 验证签名 */
	    if ($cal_sign == $sign) {
	        $interval = time() - $param_array['timestamp'];
	        $media_id = trim($param_array['media_id']);
	        /** 检查时间戳 */
	        if ($interval >= 0 && $interval < 10) {
	            echo json_encode(array(
	                'errcode' => 0,
	                'errmsg' => '',
	                'token' => 'pocketuniversity',
	                'is_config' => 1,       //0:无配置页;1:有配置页
	            ));
	        } else {
	            echo json_encode(array('errcode' => 5003,'errmsg' => '请求接口失败')); //可能是重放攻击
	        }
	    } else {
	        echo json_encode(array('errcode' => 5004,'errmsg' => '签名错误')); //签名错误
	    }
	}

	// 关闭应用
    public function close() {
        $post_data = file_get_contents('php://input');
        $param_array = json_decode($post_data, true);
        $sign = $param_array['sign'];
        unset($param_array['sign']);
        $cal_sign = $this->_cal_sign($param_array);
        if ($cal_sign == $sign) {
            $interval = time() - $param_array['timestamp'];
            if ($interval >= 0 && $interval < 10) {
                $sql = "update  kdgx_wx_media_info set appState = appState & b'1111111101' where media_id='".$param_array['media_id']."'";
                M()->execute($sql);
                return json_encode(array('errcode' => 0,'errmsg' => 'OK'));
            } else {
                return json_encode(array('errcode' => 5003,'errmsg' => '请求接口失败')); //可能是重放攻击
            }
        } else {
            return json_encode(array('errcode' => 5004,'errmsg' => '签名错误')); //签名错误
        }
    }

    // 监控应用
    public function monitor() {
        echo $_GET['echostr'];exit();
    }

    // 触发应用
    public function trigger() {
        $media_id = I('get.media_id');
        $data = array(
            'media_id'  =>  $media_id,
            'api_key'   =>  C("LIVE_API_KEY"),
            'timestamp'=> time(),
            'nonce_str'=> md5(rand()),
            );
        $data['sign'] = $this->_cal_sign($data);
        $data = json_encode($data);
        $media_info = $this->get_media_info($data);
        if(!empty($media_info['name'])){   
            if(empty($media_info['school_name'])){
                $media_info['school_name'] = '口袋大学';
            }
            $result=M('kdgx_wx_media_info')->where(array('media_id'=>$media_info['media_id']))->find();
            $school_id=$result['id'];
            if($school_id){
                M('kdgx_wx_media_info')->where(array('media_id'=>$media_info['media_id']))
                    ->data($media_info)->save();
            }else{
                $school_id = M('kdgx_wx_media_info')->data($media_info)->add(); 
            }
            $sql = "update  kdgx_wx_media_info set appState = appState | b'10' where id='".$school_id."'";
            M()->execute($sql);
        }   
    	$from = '/index.php/Live/Home/index';
        $url = "http://".C('WEB_SITE')."/index.php/home/user/login?flag=0&school=".$media_id."&from=".urlencode($from);
        redirect($url);
    }

    // 配置应用
    public function config() {
        $param_array = $_GET;
        $sign = $param_array['sign'];
        //type和sign不参与计算
        unset($param_array['type'], $param_array['sign']);
        $cal_sign = $this->_cal_sign($param_array);
        cookie('media_id',$param_array['media_id']);
        $this->display('Live@Admin/index');
        echo json_encode(array('errcode'=>0));exit();
    }

    // 获取公众号信息
    private function get_media_info($data){
        $url = 'http://weixiao.qq.com/common/get_media_info';
        $result = requestPost($url,$data);
        if(!$result){
            return false;
        }
        $result = json_decode($result,true);
        return $result;
    }

	// 生成Session令牌TOKEN
    public function get_session_token($media_id) {
        session_start();
        $token = "";
        
        if ($_SESSION[$media_id] == $token) {
            $_SESSION[$media_id] = $this->gen_token($media_id); //没有值赋新值
        }

        return $_SESSION[$media_id];
    }

	// 生成TOKEN
    public function gen_token($media_id) {
        $key = 'SECRET'; //自己定义SECRET值
        $string = strlen($media_id) . $media_id;

        $b = 64;
        if (strlen($key) > $b) {
            $key = pack("H*", md5($key));
        }

        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack("H*" ,md5($k_ipad . $string)));
    }

	// 计算签名
    private static function _cal_sign($param_array) {
        $names = array_keys($param_array);
        sort($names, SORT_STRING);
        
        $item_array = array();
        foreach ($names as $name) {
            $item_array[] = "{$name}={$param_array[$name]}";
        }
        $api_secret = C("LIVE_API_SECRET");
        $str = implode('&', $item_array) . '&key=' . $api_secret;
        return strtoupper(md5($str));
    }
}