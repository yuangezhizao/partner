<?php
namespace Test\Controller;
use Think\Controller;
/**
 * 微校标准应用接入DEMO
 * 有问题请参考以下地址：
 * http://open.weixiao.qq.com/app/index.html#/api?content=problem
 */
class WeixiaoController extends Controller
{
    // 由微校提供
    const API_KEY = '28A57D53572D83E2';
    const API_SECRET = '914DDBEAD29F1F4DC021AF02A68B5735';

    public function index() {
        $type = $_GET['type'];
        switch($type) {
            case 'open' :
                echo $this->open();
                break;
            case 'close' :
                echo $this->close();
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
                echo $this->keyword();
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
		$cal_sign = $this->calSign($param_array);

		/** 验证签名 */
		if ($cal_sign == $sign) {
			$interval = time() - $param_array['timestamp'];
            
			/** 检查时间戳 */
			if ($interval >= 0 && $interval < 10) {
                
				/** OK返回正确的json格式数据 */
                return json_encode(array(
                    'errcode' => 0,
                    'errmsg' => '',
                    'token' => 'koudai',
                    'is_config' => 1,
                ));
			} else {
                return json_encode(array('errcode' => 5003,'errmsg' => '请求接口失败')); //可能是重放攻击
			}
		} else {
            return json_encode(array('errcode' => 5004,'errmsg' => '签名错误')); //签名错误
		}
	}

	/**
	 * 关闭应用
	 */
    public function close() {
		$post_data = file_get_contents('php://input');
		$param_array = json_decode($post_data, true);

		$sign = $param_array['sign'];
		unset($param_array['sign']);
		$cal_sign = $this->calSign($param_array);

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
		/**
        echo json_encode($param_array):
        {
          "media_id":"gh_560e659c5877",
          "platform":"weixiao",
          "timestamp":1464077179,
          "nonce_str":"EE32BC3CB1C1FDEBDB32E9D2CEF56894",
          "sign":"0C7F5A8A8CA77C6818DFED09A4924674"
        }
         */
		$sign = $param_array['sign'];
        //type和sign不参与计算
		unset($param_array['type'], $param_array['sign']);
		$cal_sign = $this->calSign($param_array);

		/** 验证签名 */
		if ($cal_sign == $sign) {
            $media_id = trim($param_array['media_id']);
            $token = $this->getSessionToken($media_id);

            //将$token放入cookie
            setcookie($media_id, $token);

            //加载配置页面
		} else {
			return json_encode(array('errcode' => 5004,'errmsg' => '签名错误')); //签名错误
		}
	}

	/**
	 * 保存应用配置
	 * 页面post数据过来
	 */
	public function saveConfig() {
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

        // 加载应用的页面
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];                   //全局的xml取出
        //extract post data
        if (!empty($postStr)){
            /* libxml_disable_entity_loader         是XML实体加载器是防止外部实体注入 */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA); // XML转object
            
            echo $this->returnText($postObj);
            
        }else{
            echo '';
        }
    }

	/**
	 * 消息回复类支持模糊匹配的应用需提供此接口
	 */
	public function keyword() {
		$post_data = file_get_contents('php://input');
		$param_array = json_decode($post_data, true);

		$sign = $param_array['sign'];
		unset($param_array['sign']);
		$cal_sign = $this->calSign($param_array);

		/** 验证签名 */
		if ($cal_sign == $sign) {
			$interval = time() - $param_array['timestamp'];

			/** 检查时间戳 */
			if ($interval >= 0 && $interval < 10) {
                //对新关键词进行记录和同步用来做消息处理
				/** OK返回正确的json格式数据 */
                return json_encode(array(
                    'errcode' => 0,
                    'errmsg' => '',
                ));
			} else {
                return json_encode(array('errcode' => 5003,'errmsg' => '请求接口失败')); //可能是重放攻击
			}
		} else {
            return json_encode(array('errcode' => 5004,'errmsg' => '签名错误')); //签名错误
		}
	}

	/**
	 * 生成Session令牌TOKEN
	 */
    public function getSessionToken($media_id) {
        session_start();
        $token = "";
        
        if ($_SESSION[$media_id] == $token) {
            $_SESSION[$media_id] = $this->genToken($media_id); //没有值赋新值
        }

        return $_SESSION[$media_id];
    }

	/**
	 * 生成TOKEN
	 */
    public function genToken($media_id) {
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
	public function calSign($param_array) {
		$names = array_keys($param_array);
		sort($names, SORT_STRING);
        
		$item_array = array();
		foreach ($names as $name) {
			$item_array[] = "{$name}={$param_array[$name]}";
		}

		$str = implode('&', $item_array) . '&key=' . self::API_SECRET;
		return strtoupper(md5($str));
	}
	
	
	/**
	 * 发送文本消息
	 * @param object   $object
	 * @return string
	 */
	Private  function returnText($object)
	{
	    $textTpl = '<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%d</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
                    </xml>';
	    $resultStr = sprintf ( $textTpl, $object->FromUserName, $object->ToUserName, $object->CreateTime , $object->FromUserName );
	    return $resultStr;
	}
}