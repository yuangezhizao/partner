<?php
namespace Partner\Controller;
use Think\Controller;
/**
 * 微校标准应用接入
 */
class WeixiaoController extends Controller{

    public function index() {
        $method = $_SERVER['REQUEST_METHOD'];
        $http_url = empty($_SERVER['HTTP_X_REWRITE_URL']) ? $_SERVER['REQUEST_URI'] : $_SERVER['HTTP_X_REWRITE_URL'];
        $param = explode('?', $http_url);
        $type = $param[1];
        $url = "http://".C('WEB_SITE')."/index.php/Home/Weixiao/index?".$type;
        $post_data = $GLOBALS['HTTP_RAW_POST_DATA'];
        print_r($url);
        if($method == 'POST'){
            if($this->is_xml($post_data)){      
                $data = $this->post_xml($url,$post_data);
            }else{      
                $data = requestPost($url,$post_data);
            }
        }else{
            $data = $this->https_request($url); 
        }
        print_r($data); 
    }

    public function is_xml($str){ 
        $xml_parser = xml_parser_create(); 
        if(!xml_parse($xml_parser,$str,true)){ 
            xml_parser_free($xml_parser); 
            return false; 
        }else { 
            return true; 
        } 
    }
    public function https_request($url,$data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    public function post_xml($url,$xmlData){
        $header[] = "Content-type: text/xml"; //定义content-type为xml,注意是数组
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            print curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }
}