<?php
namespace Home\Controller;
use Common\Controller\BaseController;
/**
 * 微校标准应用接入
 */
class WeixiaoController extends BaseController{
    
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
    /**
     * 开启应用
     */
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
                
                $data = array(
                    'media_id' => $media_id,
                );
                // 公众号media_id入库
                if(M('kdgx_partner_site')->where(array('media_id'=>$media_id))->find()){
                }else{
                    M('kdgx_partner_site')->data($data)->add();
                }
                /** OK返回正确的json格式数据 */
                echo json_encode(array(
                    'errcode' => 0,
                    'errmsg' => '',
                    'token' => 'pocketuniversity',
                    'is_config' => 1,       //0:无配置页;1:有配置页
                ));
            } else {
				/*echo json_encode(array(
                    'errcode' => 0,
                    'errmsg' => '',
                    'token' => 'pocketuniversity',
                    'is_config' => 1,       //0:无配置页;1:有配置页
                ));*/
               echo json_encode(array('errcode' => 5003,'errmsg' => '请求接口失败')); //可能是重放攻击
            }
        } else {
            echo json_encode(array('errcode' => 5004,'errmsg' => '签名错误')); //签名错误
        }
    }

    public function keyword(){
        //$post_data = file_get_contents('php://input');
        // $param_array = json_decode($post_data, true);
        // file_put_contents('./test/log','ok'.print_r($post_data,true),FILE_APPEND);
        // $sign = $param_array['sign'];
        // $keyword = $param_array['keyword'];
        // $keyword = trim($keyword,'[');
        // $keyword = trim($keyword,']');
        // $keyword = json_decode($keyword,true);
        // //修改关键字
        // $data =  $keyword['keyword'];
        M('kdgx_partner_site')->where(array('media_id'=>$param_array['media_id']))
        ->save(array('keyword'=>$data));
        echo '{"errcode":0,"errmsg":"OK"}';
    }

    /**
     * 关闭应用
     */
    public function close() {
        $post_data = file_get_contents('php://input');

        $param_array = json_decode($post_data, true);

        $sign = $param_array['sign'];
        unset($param_array['sign']);
        $cal_sign = $this->_cal_sign($param_array);

        /** 验证签名 */
        if ($cal_sign == $sign) {
            $interval = time() - $param_array['timestamp'];

            /** 检查时间戳 */
            if ($interval >= 0 && $interval < 10) {
                // 此处做自己想做的操作...
                return json_encode(array('errcode' => 0,'errmsg' => 'OK'));
            } else {
                return json_encode(array('errcode' => 5003,'errmsg' => '请求接口失败')); //可能是重放攻击
            }
        } else {
            return json_encode(array('errcode' => 5004,'errmsg' => '签名错误')); //签名错误
        }
    }

    /**
     * 应用配置页
     */
    public function config() {
        $param_array = $_GET;
        $sign = $param_array['sign'];
        //type和sign不参与计算
        unset($param_array['type'], $param_array['sign']);
        $cal_sign = $this->_cal_sign($param_array);


        /** 验证签名 */
        if ($cal_sign == $sign) {
            $media_id = trim($param_array['media_id']);
            $token = $this->get_session_token($media_id);
            //将$token放入cookie
            setcookie($media_id, $token);
            $post_data = array(
                'media_id' => $param_array['media_id'],
                'api_key'  => C("PARTNER_API_KEY"),
                'timestamp'=> time(),
                'nonce_str'=> md5(rand()), 
            );
            $post_data['sign'] = $this->_cal_sign($post_data);
            $post_data = json_encode($post_data);
            $data = $this->get_meida_keywords($post_data);
            // ----------------统计配置页响应请求-------------
            $collect = new \Org\Util\DataCollect(array('module'=>'partner'));
            $collect->CollectResponse('配置首页',$param_array['media_id'],$param_array['media_id']);
            // ----------------加载配置页面------------------
            $this->assign('media_id',$param_array['media_id']);
            $this->assign('avatar_image',$data['avatar_image']);
            $this->display('Partner@partner/config');
            return array(
                'errcode'=> 0,
            );
        } else {
            return json_encode(array('errcode' => 5004,'errmsg' => '签名错误')); //签名错误
        }
    }

    /**
     * 保存应用配置
     * 页面post数据过来
     */
    public function save_config() {
        session_start();

        $media_id = $_GET['media_id'];
        $cookie_token = $_COOKIE[$media_id];
        $session_token = $_SESSION[$media_id];

        //按文档检查令牌的合法性
        if ($cookie_token == $session_token) {
            //保存公众号的配置

            //清除SESSION令牌
            unset($_SESSION[$media_id]);
        } else {
            //令牌不合法
        }
    }

    /**
     * 应用监控
     */
    public function monitor() {
        echo $_GET['echostr'];
        exit();
    }

    /**
     * 应用触发
     */
    public function trigger() {
        $media_id = I('get.media_id');
        $data = array(
            'media_id'  =>  $media_id,
            'api_key'   =>  C("PARTNER_API_KEY"),
            'timestamp'=> time(),
            'nonce_str'=> md5(rand()),
            );
        $data['sign'] = $this->_cal_sign($data);
        $data = json_encode($data);
        $media_info = $this->get_media_info($data);
        $media_keyword = $this->get_meida_keywords($data);
        if(!empty($media_keyword)){
            M('kdgx_partner_site')->where(array('media_id'=>$media_keyword['media_id']))
            ->save(array('keyword'=>$media_keyword['keyword']));
        }
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
            M('kdgx_partner_site')->where(array('media_id'=>$media_info['media_id'],'school_id'=>'0'))
                                  ->data(array('school_id'=>$school_id))->save();
        }
        $keyword = $media_keyword['keyword'];
        // -----------------------响应用户请求------------------------------------
        $wechatObj = new \Partner\Common\wechatCallback($media_id,$keyword);
        $wechatObj->responseMsg();
    }

    /**
     * 生成Session令牌TOKEN
     */
    public function get_session_token($media_id) {
        session_start();
        $token = "";
        
        if ($_SESSION[$media_id] == $token) {
            $_SESSION[$media_id] = $this->gen_token($media_id); //没有值赋新值
        }

        return $_SESSION[$media_id];
    }

    /**
     * 生成TOKEN
     */
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

    /**
     * 计算签名
     * @param array $param_array
     */
    private static function _cal_sign($param_array) {
        $names = array_keys($param_array);
        sort($names, SORT_STRING);
        
        $item_array = array();
        foreach ($names as $name) {
            $item_array[] = "{$name}={$param_array[$name]}";
        }
        $api_secret = C("PARTNER_API_SECRET");
        $str = implode('&', $item_array) . '&key=' . $api_secret;
        return strtoupper(md5($str));
    }

    /**
     * 获取公众号关键字
     */
    public function get_meida_keywords($data){
        $url = 'http://weixiao.qq.com/common/get_media_keywords';
        $result = requestPost($url, $data);
        if (!$result) {
            return false;
        }
        //处理响应数据
        $result = json_decode($result,true);
        return $result;
    }

    /**
     * 获取公众号信息
     * @param json 请求接口的token数据包
     * @return array 公众号信息的数据
     */
    private function get_media_info($data){
        $url = 'http://weixiao.qq.com/common/get_media_info';
        $result = requestPost($url,$data);
        if(!$result){
            return false;
        }
        $result = json_decode($result,true);
        return $result;
    }

}