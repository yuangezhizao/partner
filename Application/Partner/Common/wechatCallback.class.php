<?php
namespace Partner\Common;

class wechatCallback
{
  private $media_id;
  private $key;

  private $vipMedia =array('gh_aee726515b29');

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

  public function __construct($media_id,$key){
    $this->media_id = $media_id;
    $this->key = $key;
  }

	public function valid(){
      $echoStr = $_GET["echostr"];
      if($this->checkSignature()){
      	echo $echoStr;
      	exit;
      }
    }

  public function is_exists($secret){
    $sql = "SELECT id,photo_src,expect,media_id,introduce FROM kdgx_partner WHERE secret='" . $secret . "' AND is_delete='0'" ;
    $data = $this->parse_sql($sql);
    if($this->media_id=='gh_aee726515b29'){#vip(单身狗展览馆)
      return $data;
    }
    if(empty($data[0])){
      return $data;
    }elseif(!strcmp($this->media_id, $data[0]['media_id'])){
      return $data;
    }else{
      $sql = "SELECT id FROM kdgx_wx_media_info WHERE media_id='".$data[0]['media_id']."' limit 1";
      $result = $this->parse_sql($sql);
      
      if(empty($result[0]['id'])){
        exit();
      }else{
        $sql = "SELECT id FROM kdgx_partner_site WHERE school_id='".$result[0]['id']."' AND media_id='".$this->media_id."' limit 1";
        $result = $this->parse_sql($sql);
        if(empty($result)){
          return $result;
        }else{
          return $data;
        }
      }
    }
  }

  public function parse_sql($sql){
    // $con=mysql_connect("127.0.0.1","testkoudai","testkoudai");    //测试服务器
    $con=mysql_connect("rm-bp1k7d5393250snh0.mysql.rds.aliyuncs.com","koudai","vmW35XnAv8h2xCTD");  //真实服务器
    mysql_select_db("koudai", $con);
    $result = mysql_query($sql);
    $data =array();
    while ($row = mysql_fetch_assoc($result)) {
      $data[] = $row;
    }
    return $data;
  }

  // 消息处理
  public function responseMsg(){
    $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
    if (!empty($postStr)){
        libxml_disable_entity_loader(true);
      	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $key = mb_substr($keyword,0,-8,'UTF-8');
        $secret = mb_substr($keyword,-8,null,'UTF-8');
        if(strpos($this->key,$keyword)!==false){
            $from = "/index.php/Partner/Partner/index?media_id=".$this->media_id;
            $from = urlencode($from);
            $msgType = "news";
            $title = '点此开卖室友';
            $description = '真诚找对象,误黑己室友';
            $PicUrl = "http://".C('WEB_SITE').'/Static/logo.jpg';
            $url = "http://".C('WEB_SITE')."/index.php/home/User/login?school=".$this->media_id."&from=".$from;
            $FuncFlag = 0;
            $resultStr = sprintf($this->_msg_template['news'],$fromUsername,$toUsername,$time,$msgType,1,$title,$description,$PicUrl,$url, $FuncFlag);
            echo  $resultStr;
            exit;
        }
        if(!empty( $keyword )){
            if(strpos($this->key,$key)!==false){            
                $data = $this->is_exists($secret);
                if(!empty($data)){
                  $msgType = "news";
                  $title = $data[0]['expect'];
                  $description = $data[0]['introduce'];
                  $PicUrl = "http://".C('CDN_SITE').'/'.$data[0]['photo_src'];
                  $from = "/index.php/Partner/Share/look?id=".uidencode($data[0]['id']);
                  $url = "http://".C('WEB_SITE')."/index.php/home/user/login?user_type=msy&flag=1&school=".$this->media_id."&from=".urlencode($from);
                  $FuncFlag = 0;
                  $resultStr = sprintf($this->_msg_template['news'],$fromUsername,$toUsername,$time,$msgType,1,$title,$description,$PicUrl,$url, $FuncFlag);
                  echo  $resultStr;
                }else{
                    $msgType = "text";
                    $contentStr = '是想要联系方式吗?您的暗号错误或经本人要求暗号已失效,换个暗号试试吧!^-^!';
                    $resultStr = sprintf($this->_msg_template['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }
            }else{
                $msgType = "text";
                $contentStr = $key.','.$secret.','.$this->key.','.$keyword;
                $resultStr = sprintf($this->_msg_template['text'], $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }
          }else{
          	echo "你好!";
          }
      }else {
      	echo "欢迎!";
      	exit;
      }
  }
      /**
   * 发送文本信息
   * @param  [type] $to      目标用户ID
   * @param  [type] $from    来源用户ID
   * @param  [type] $content 内容
   * @return [type]          [description]
   */
  private function _msgText($to, $from, $content) {
      $response = sprintf($this->_msg_template['text'], $to, $from, time(), $content);
      die($response);
  }
	private function checkSignature()
	{
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}

?>