<?php 
namespace Debug\Controller;
use \Think\Controller;

class UploadBase64{
    
     // 默认上传配置
    private $config = array(
        'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'autoSub'       =>  true, //自动子目录保存文件
        'rootPath'      =>  'D:\VirtualHost\Uploads\Uploads/', //保存根路径
        'savePath'      =>  '', //保存路径
    );
    /**
     * 构造方法，用于构造上传实例
     * @param array  $config 配置
     */
    public function __construct($config = array()){
        /* 获取配置 */
        $this->config   =   array_merge($this->config, $config);
    }
    /**
     * 使用 $this->name 获取配置
     * @param  string $name 配置名称
     * @return multitype    配置值
     */
    public function __get($name) {
        return $this->config[$name];
    }

    public function __set($name,$value){
        if(isset($this->config[$name])) {
            $this->config[$name] = $value;
            if($name == 'driverConfig'){
                //改变驱动配置后重置上传驱动
                //注意：必须选改变驱动然后再改变驱动配置
                $this->setDriver(); 
            }
        }
    }
    public function __isset($name){
    	echo "*";
        return isset($this->config[$name]);
    }
    /**
     * 上传base文件
     * @param  string $strbase base字符串
     * @return array       文件上传后的返回信息
     */
    public function upload($strbase){
        $arrbase = explode(',',$strbase);
        $file = base64_decode($arrbase[1]);
        $upload = array();
        /* 生成保存文件名 */
        $savename = $this->getSaveName(microtime());
        $upload['savename'] = $savename;
        /* 检测并创建子目录 */
        $subpath = $this->getSubPath();
        $upload['savepath'] = $this->savePath . $subpath;
        $uploadname = $this->rootPath.$upload['savepath'].$upload['savename'];
        $result = file_put_contents($uploadname, $file);
    	if($result){
    		return $uploadname;
    	}else{
    		return false;
    	}
    }
    /**
     * 获取子目录的名称
     * @param array $file  上传的文件信息
     */
    private function getSubPath() {
        $subpath = '';
        $rule    = $this->subName;
        if ($this->autoSub && !empty($rule)) {
            $subpath = $this->getName($rule, $file) . '/';
            // echo $this->rootPath.$this->savePath . $subpath;
            $flag = mkdir($this->rootPath.$this->savePath . $subpath,0777);
            if(empty($subpath) && !$flag){
                return false;
            }
        }
        return $rootPath.$subpath;
    }
    /**
     * 根据上传文件命名规则取得保存文件名
     * @param string $file 文件信息
     */
    private function getSaveName($file) {
    	$file = $file.rand();
    	$file = md5($file);
    	$savename = $file;
        return $savename.'.png';
    }
    /**
     * 根据指定的规则获取文件或目录名称
     * @param  array  $rule     规则
     * @param  string $filename 原文件名
     * @return string           文件或目录名称
     */
    private function getName($rule){
        $name = '';
        if(is_array($rule)){ //数组规则
            $func     = $rule[0];
            $param    = (array)$rule[1];
            $name = call_user_func_array($func, $param);
        } elseif (is_string($rule)){ //字符串规则
            if(function_exists($rule)){
                $name = call_user_func($rule);
            } else {
                $name = $rule;
            }
        }
        return $name;
    }

    /**
     * 旋转图片为正常状态
     * @param  [type] $file [description]
     * @return [type]       [description]
     */
    private function rotalePhoto($file){
        $data = imagecreatefromstring(file_get_contents($file));
        $exif = exif_read_data($file);
        if(!empty($exif['Orientation'])){
            switch($exif['Orientation']){
                case '8':
                    $data = imagerotate($data, 90, 0);
                    break;
                case '3':
                    $data = imageretate($data,180,0);
                    break;
                case '6':
                    $data = imageretate($data,-90,0);
                    break;
                default :
                    break;
            }
        }
        imagejpeg($data,$file);
    }

}
class CurlController extends Controller{

	public function getDormentries($site_id){
		$url = "http://m.yemao.59store.com/userappapi/location/dormentry/list?device_id=RH4amkpB4HPWEi30ckS1RS5M5wGEMZnK&device_type=2&app_version=3.0&token=d919fa27916c4309a5a3d6bea092cb17&site_id={$site_id}&_=1477989335164";
		$data = requestGet($url);
		$data = json_decode($data,true);
		$data = $data['data']['groups'];
		foreach ($data as $value) {
			$group_name = $value['name'];
			foreach ($value['dormentries'] as $dormentry) {
				$insert = array(
					'dormentry_id' => $dormentry['dormentry_id'],
					'name'			=> $dormentry['name'],
					'group_name'	=>	$group_name,
					'site_id'		=>	$site_id,
				);
				echo M('kdgx_dormentries')->data($insert)->replace();
			}
		}
	}

	public function insertDormentries(){
		$sites = M('kdgx_site')->field('site_id')->where("site_id = '4137'")->order('site_id asc')->select();
		foreach ($sites as $key => $value) {
			$this->getDormentries($value['site_id']);
		}
	}

	public function base64(){
		$base = 'data:image/png;base64,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';
		$base64 = new UploadBase64();
		$info = $base64->upload($base);
		$info = substr($info, strlen("D:\VirtualHost\Uploads\ ")-1);
		$url = "http://oif5bmgmc.bkt.clouddn.com/$info";
		requestGet($url);
		print_r($info);
	}

	public function qiniu(){
		$data = M('kdgx_partner')->field('photo_src')->where('uploadtime>1481990400 and uploadtime<1482076799')->order('uploadtime desc')->select();
		// echo M('kdgx_partner')->getLastSql();exit();
		$this->assign('datas',$data);
		$this->display('Curl/qiniu');
	}
	public function noqiniu(){
		exit();
		$data = M('kdgx_partner')->field('photo_src')->where('uploadtime>1481990400 and uploadtime<1482076799')->order('uploadtime desc')->select();
		$this->assign('datas',$data);
		$this->display('Curl/noqiniu');
	}

	// 获取当前连接数接口
	public function getConnectCounts(){
		$media_id = $_POST['media_id'];
		$type = $_POST['type'];
		switch ($type) {
			case '1':	#站点连接
			case '3':	#全国连接
				echo M('kdgx_partner_connect')->where(array('medias'=>$media_id,'lastRequestTime'=>array('egt',strtotime("-2 seconds")),'type'=>$type))->count('id');
				return;
			case '2':	#校园连接
				$res = M('kdgx_wx_media_info')->field('school_name')->where(array('media_id'=>$media_id))->find();
				echo M('kdgx_partner_connect')->where(array('medias'=>$res['school_name'],'lastRequestTime'=>array('egt',strtotime("-2 seconds")),'type'=>$type))->count('id');
				return;
		}
	}

	public function sendTemplate(){
		$sendTemplate = new \Org\Util\Wechat();
		$res = M('kddx_user_cache')->field('distinct(openid) as openid')->where(array('subscribe'=>'1'))->group('openid')->select();
		print_r($res); exit();
		foreach ($res as $value) {
			$data = array(
				'type' => 5,
				'first'=> '卖室友能发弹幕了，看全国妹子/帅哥动态！',
				'keyword1' => '卖室友3.0',
				'keyword2' => '2016.12.07',
				'remark'   => 
				'新增:	1.弹幕实时互动 
						2.买卖广场（全校/国卖室友动态） 
						3.盖楼形式，可按楼号进行搜索！
	特色玩法：11:11-1:11全站/全国无限制上动态，让我们一起刷爆弹幕，将脱单进行到底！',
				'remark_color' => '#3ebee9',
				'color' => '#000000',
				'url' => 'http://www.pocketuniversity.cn/partner.php?glz=gh_aee726515b29&history=1',
				'openid' => $value['openid'],
				);
			$result = $sendTemplate->sendTemplateMsg($data);
			print_r($result);
		}
	}

}




