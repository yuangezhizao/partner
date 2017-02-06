<?php 
/**
 * 应用程序函数库
 */
header("Content-type:text/html;charset=utf-8");


/**
 * 检测当前使用设备类型
 * @return [type] [description]
 */
function ismobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        return true;
    
    //此条摘自TPM智能切换模板引擎，判断是否为客户端
    if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT'])
        return true;
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array(
            'nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'
        );
        //从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}


/**
 * 将非GBK字符集的编码转为GBK
 * @param mixed $mixed 源数据
 * @return mixed GBK格式数据
 */
function charsetToGBK($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $k => $v) {
            if (is_array($v)) {
                $mixed[$k] = charsetToGBK($v);
            } else {
                $encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
                if ($encode == 'UTF-8') {
                    $mixed[$k] = iconv('UTF-8', 'GBK', $v);
                }
            }
        }
    } else {
        $encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
        //var_dump($encode);
        if ($encode == 'UTF-8') {
            $mixed = iconv('UTF-8', 'GBK', $mixed);
        }
    }
    return $mixed;
}


/**
 * 拼接url
 * @param string $url 原url
 * @param array $params 要拼接的参数
 * @return string       拼接后的url
 */
function joinUrl($url,$params=array()){ 
    if(stripos($url, '?')==false){
        foreach ($params as $key => $value) {
            $url .= '?' . $key . '=' . $value;
            unset($params[$key]);
            break;
        }
        foreach ($params as $key => $value) {
            $url .= '&' . $key . '=' . $value;
        }
    }else{
        foreach ($params as $key => $value) {
            $url .= '&' . $key . '=' . $value;
        }
    }
    return $url;
}

/**
 * 发送post请求的方法
 * @param  string  $url  URL
 * @param  [type]  $data [description]
 * @param  bool $ssl  是否为https协议
 * @return string        响应主体
 */
function requestPost($url, $data, $ssl=false) {
    // curl完成
    $curl = curl_init();

    //设置curl选项
    curl_setopt($curl, CURLOPT_URL, $url);//URL
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';
    curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
    curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间
    //SSL相关
    if ($ssl) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。
    }
    // 处理post相关选项
    curl_setopt($curl, CURLOPT_POST, true);// 是否为POST请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// 处理请求数据
    // 处理响应结果
    curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果

    // 发出请求
    $response = curl_exec($curl);
    if (false === $response) {
        echo curl_error($curl);
        return false;
    }
    curl_close($curl);
    return $response;
}

/**
 * 发送GET请求的方法
 * @param string $url URL
 * @param bool $ssl 是否为https协议
 * @return string 响应主体Content
 */
function requestGet($url, $ssl=false) {
    // curl完成
    $curl = curl_init();

    //设置curl选项
    curl_setopt($curl, CURLOPT_URL, $url);//URL
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '
Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0 FirePHP/0.7.4';
    curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);//user_agent，请求代理信息
    curl_setopt($curl, CURLOPT_AUTOREFERER, true);//referer头，请求来源
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);//设置超时时间

    //SSL相关
    if ($ssl) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//禁用后cURL将终止从服务端进行验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//检查服务器SSL证书中是否存在一个公用名(common name)。
    }
    curl_setopt($curl, CURLOPT_HEADER, false);//是否处理响应头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);//curl_exec()是否返回响应结果

    // 发出请求
    $response = curl_exec($curl);
    if (false === $response) {
        echo '<br>', curl_error($curl), '<br>';
        return false;
    }
    curl_close($curl);
    return $response;
}


function curl_post_ssl($url,$vars,$aHeader=array(),$second=30){
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
    curl_setopt($ch, CURLOPT_SSLCERT,'./cert/apiclient_cert.pem');
    curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
    curl_setopt($ch, CURLOPT_SSLKEY,'./cert/apiclient_key.pem');

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

/**
 * 关联数组转换为xml文件
 * @param 关联数组
 * @return xml字符串
 */
function array_to_xml($arr_data){
    $xml = "<xml>";
    foreach ($arr_data as $key => $value) {
        $xml .= '<' . $key . '>' . $value . '</' . $key . '>';
    }
    $xml .= '</xml>';
    return $xml;
}



/**
 * 加密uid
 * @param  string $uid 待加密的字符串
 * @return string      加密后的字符串
 */
function uidencode($uid){
    $code = '0123456789';
    $length = strlen($uid);
    $uid = $length . $uid;
    $secrect = '';
    for($i=0;$i<6;$i++){
        if($i==2){
            $secret .= $uid;
        }else{
            $secret .= $code[mt_rand(0, strlen($code)-1)];
        }
    }
    return $secret;
}
/**
 * 解密uid
 * @param  string $secrect 加密后的uid
 * @return string          解密后的uid
 */
function uiddecode($secret){
    $key = substr($secret,2,1);
    $uid = substr($secret,3,$key);
    return $uid;
}

/**
 * 按照关键字降序排列数组
 */
function desc_sort($x,$y){
    if($x['time'] > $y['time']){
        return false;
    }elseif ($x['time'] < $y['time']) {
        return true;
    }else{
        return 0;
    }
}


//---------------加密函数块-开始----------------
function keyED($txt,$encrypt_key){       
    $encrypt_key =    md5($encrypt_key);
    $ctr=0;       
    $tmp = "";       
    for($i=0;$i<strlen($txt);$i++)       
    {           
        if ($ctr==strlen($encrypt_key))
        $ctr=0;           
        $tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1);
        $ctr++;       
    }       
    return $tmp;   
}    
function encrypt($txt,$key)   {
    $encrypt_key = md5(mt_rand(0,100));
    $ctr=0;       
    $tmp = "";      
     for ($i=0;$i<strlen($txt);$i++)       
     {
        if ($ctr==strlen($encrypt_key))
            $ctr=0;           
        $tmp.=substr($encrypt_key,$ctr,1) . (substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
        $ctr++;       
     }       
     return keyED($tmp,$key);
} 
    
function decrypt($txt,$key){       
    $txt = keyED($txt,$key);       
    $tmp = "";       
    for($i=0;$i<strlen($txt);$i++)       
    {           
        $md5 = substr($txt,$i,1);
        $i++;           
        $tmp.= (substr($txt,$i,1) ^ $md5);       
    }       
    return $tmp;
}

/**
 * @param $url URL
 * @param $key 加盐秘钥
 * @return 加密后的url字符串
 */
function encrypt_url($url,$key){
    if(empty($key)){
        exit('加密算法错误!');
    }
    return rawurlencode(base64_encode(encrypt($url,$key)));
}
/**
 * 解密url参数
 * @param $url URL
 * @param key 加盐秘钥
 * @return 解密后的url字符串
 */
function decrypt_url($url,$key){
    return decrypt(base64_decode(rawurldecode($url)),$key);
}

//---------------加密函数块-结束-----------------