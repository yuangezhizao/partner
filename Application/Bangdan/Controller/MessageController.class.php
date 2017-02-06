<?php
/***
 * 
 * 管理员推送消息
 * 
 */
namespace Bangdan\Controller;
use Bangdan\Controller\AbaseController;
class MessageController extends AbaseController
{

    /* -----------------------------------【管理员权限进入】--------------------------------------------- */
    Public function index()
    {
		$uid=$_SESSION['uid'];
		$uid=	decrypt_url ( $uid, 'pocketuniversity' );
		$re=M('kdgx_operator')->where("uid='$uid' && state>=2")->find();
		if($re){
			$this->display();
		}else{
			$this->redirect('Index/index');
		}
	}

        /* -------------------------------------【所有消息管理】-------------------------------------------- */
    Public function confirm()
    {    
		$sql="SELECT n.* FROM `kdgx_public_news`  AS  n  INNER JOIN  `kdgx_public_inbox`  AS  i ON n.id=i.nid GROUP BY  i.nid HAVING MIN(state)>0  ORDER BY timestamp DESC";
		$arr =M()->query($sql);
		$array['confirm']=$arr;//全部确认
		foreach ($arr as $k =>$v){
			$array['confirm'][$k]['timestamp']=date("m-d H:i",$v['timestamp']);
		}
		$sql="SELECT  n.* FROM  `kdgx_public_news`  AS  n  INNER JOIN  `kdgx_public_inbox`  AS  i  ON  n.id=i.nid GROUP BY  i.nid HAVING MIN(state)=0  ORDER BY  timestamp DESC";
		$arr =M()->query($sql);
		$array['unconfirmed']=$arr;//未全部确认
		foreach ($arr as $k =>$v){
			$array['unconfirmed'][$k]['timestamp']=date("m-d H:i",$v['timestamp']);
		}
		foreach ($arr as $k=>$v){
			$id=$v['id'];
			$sql="SELECT   p.school,o.name,i.state FROM `kdgx_public_inbox`  AS  i
			INNER JOIN  `kdgx_operator`  AS  o  ON  o.uid=i.uid
			INNER JOIN  `kddx_public`  AS  p  ON  p.public_id=o.public_id
			WHERE  i.nid=$id && i.state=0";
			$arr2=M()->query($sql);
			$num=0;
			foreach ($arr2 as $v){
				$num++;
			}
			$array['unconfirmed'][$k]['num']=$num;
			$array['unconfirmed'][$k]['info']=$arr2;
	}
	$this->ajaxReturn($array);
	}

        /* -----------------------------------------【管理员推消息及发送模板消息】------------------------------------------------ */
    public function add()
    {
		if ($_POST ['type'] == 1) {
			$files = $_POST ['images'];
		$upload = new \Org\Util\UploadBase64(array('rootPath'=>'D:\VirtualHost\Uploads/Bangdan/'));
		$info = $upload->upload($files);
		$info = substr($info, strlen('D:\VirtualHost\Uploads'));
			if ($info) {
				$_POST ['images'] = $info;
			}else{
			    $_POST ['images'] = '';//图片上传失败 exit();
			}
		}
		$_POST ['timestamp'] = time ();
		$uid=$_SESSION['uid'];
		$uid=	decrypt_url ( $uid, 'pocketuniversity' );
		$_POST['addresser']=$uid;
		$re = M ( 'kdgx_public_news' )->data ( $_POST )->add ();
		
		if ($re) {
			$r1 = M ( 'kdgx_public_news' )->where ( "id=$re" )->find ();
			if ($r1 ['type'] == 1) {
				$msgType = "广告单子";
			} elseif ($r1 ['type'] == 2) {
				$msgType = "实时热点";
			} elseif ($r1 ['type']) {
				$msgType = "通知公告";
			}
			$content = $r1 ['title'];
			$content = mb_substr ( $content, 0, 14, "utf-8" );
			foreach ( $_POST ['contacts'] as $v ) {
	//			$v = json_decode ( $v, true );				
				$array ['nid'] = $re;
				$array ['name'] = $v ['name'];
				$array ['uid'] = $v ['uid'];
				$array ['public_num'] = $v ['public_num'];
				$uid = $v ['uid'];
				$id = M ( 'kdgx_public_inbox' )->data ( $array )->add ();
				if ($id) {
					if ($v ['valuetype'] == '1') {
						$r2 = M ( 'kdgx_public_inbox' )->where ( "id=$id" )->find ();
						$name = $r2 ['name'];
						$public_num = $r2 ['public_num'];
						$pub = M ( 'kddx_public' )->where ( "public_num='$public_num'" )->find ();
						$public_name = $pub ['public_name'];
						$time = date ( "Y-m-d H:i", time () );
						$url = "http://www.pocketuniversity.cn/index.php/home/Wechat/sendTemplate";
						$data = array (
								'uid' => $uid,
								'first' => "亲爱的运营者,您有新的消息!请注意查收",
								'msgType' => $msgType,
								'name' => "《" . $public_name . "》" . "运营者",
								'content' => $content,
								'time' => $time,
								'remark' => '如有疑问，欢迎咨询小口袋！',
								'url' => "http://www.pocketuniversity.cn/index.php/bangdan?get=news",
								'type' => '0',
						);
						$a = requestPost ( $url, $data );
						if ($a->errcode == 0) {
							echo "0"; // 操作完全成功
						} else {
							echo "4"; // 发送模板消息失败
						}
					} else {
						echo "0"; // 不发模板消息 操作成功
					}
				} else {
					echo "3"; // 入index表失败
					exit ();
				}
			}
		} else {
			echo "2"; // 入news表失败
			exit ();
		}
	}

        /* ----------------------------------------【用户头像】------------------------------------- */
    Public function user()
    {
		$uid=$_SESSION['uid'];
		$uid=	decrypt_url ( $uid, 'pocketuniversity' );
		$array['uid'] = $uid;
		$re=M('kddx_user_openid')->field("nickname,headimgurl")->where("uid='$uid' && public_id='gh_3347961cee42' ")->find();
		$array['name']=$re['nickname'];
		$array['header_img']=$re['headimgurl'];
		$this->ajaxReturn($array);
	}

        /* --------------------------------------【公共号对应的所有运营者】---------------------------------------- */
    /**
     *
     * @return 公众号ID
     * @return ID对应的学校
     * @return 公共号对应的所有运营者
     */
    PUBLIC function contacts()
    {
	    $sql = "SELECT  id , public_id , public_name , public_num FROM `kddx_public` WHERE  `is_show`='1' ";
	    $arr = M()->query($sql);
	    $num=0;
	    foreach ($arr as $k=>$v){
	        $array ['contacts'] [$k] ['public_id'] = $v ['public_id'];
	        $array ['contacts'] [$k] ['public_num'] = $v ['public_num'];
	        $array ['contacts'] [$k] ['public_name'] = $v ['public_name'];
	        $arr2 = M('kdgx_operator')->where("state>='1' && public_id = '{$v['public_id']}' ")->select();
			$array ['contacts'] [$k] ['count'] = 0;
	        foreach ($arr2 as $k2=>$v2){
				$array ['contacts'] [$k] ['count'] ++;
				$array ['contacts'] [$k] ['contacts'] [$k2] ['name'] = $v2 ['name'];
				$array ['contacts'] [$k] ['contacts'] [$k2] ['uid'] = $v2 ['uid'];
	            $num++;
	        }
	    }
	    $array ['num'] = $num;
	    $this->ajaxReturn ( $array );
	}
		
	
}







