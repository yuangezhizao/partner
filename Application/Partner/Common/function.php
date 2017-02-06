<?php 

/**
 * 输出xml字符
 * @throws WxPayException
**/
function ToXml()
{   
    $xml = "<xml>";
    foreach ($this->values as $key=>$val)
    {
        if (is_numeric($val)){
            $xml.="<".$key.">".$val."</".$key.">";
        }else{
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
    }
    $xml.="</xml>";
    return $xml; 
}

/**
 * 将xml转为array
 * @param string $xml
 * @throws WxPayException
 */
function FromXml($xml)
{   
    //将XML转为array
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
    return $values;
}

function curl_post_ssl1($url,$vars,$aHeader=array(),$second=30){
    $aHeader[] = "Content-type:text/xml";   //定义content-type
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_TIMEOUT, $second); //设置超时时间
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果
    curl_setopt($ch, CURLOPT_URL, $url);    //需要获取的url地址
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
    //1.检查SSL证书中是否存在一个common name 
    //2.检查common是否存在,且是否与提供的主机匹配
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
    curl_setopt($ch, CURLOPT_SSLCERT,'./cert/xmcert/apiclient_cert.pem');
    curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
    curl_setopt($ch, CURLOPT_SSLKEY,'./cert/xmcert/apiclient_key.pem');

    // curl_setopt($ch, CURLOPT_SSLCERT, getcwd().'/all.pem');

    if(count($aHeader)>=1){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
    }

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);

    $data = curl_exec($ch);
    if($data){
        curl_close($ch);
        return $data;
    }else{
        $error = curl_error($ch);
        echo "call faild, errorCode:$error\n";
        curl_close($ch);
        return false;
    }
}