<?php 
namespace Bangdan\Controller;
use Bangdan\Controller\BaseController;
class NewsController extends BaseController{

	public function index(){
		$this->display();
	} 

/*------------------------------------------【我的消息】-----------------------------------------*/
	public function message(){
		$uid=$_SESSION['uid'];
		$uid=	decrypt_url ( $uid, 'pocketuniversity' );
		$public_num=$_SESSION['public_num'];                                                                                                                                   //&&b.public_num='$public_num'
		$sql="select n.*,b.state  from kdgx_public_news as n inner join kdgx_public_inbox as b on n.id=b.nid where b.uid='$uid'   && b.state=0 order by timestamp desc  ";
		$arr=M()->query($sql);
		$sql="select n.*,b.state  from kdgx_public_news as n inner join kdgx_public_inbox as b on n.id=b.nid where b.uid='$uid'   && b.state!=0 order by timestamp desc  ";
		$arr1=M()->query($sql);
		$arr = array_merge($arr, $arr1);
		$array['message']=$arr;
 		foreach ($arr as $k =>$v){
 		    $addresser=$v['addresser'];
 		    $re=M('kddx_user_openid')->field("nickname,headimgurl")->where("uid='$addresser' && public_id='gh_3347961cee42' ")->find();
 		    $array['message'][$k]['header_img']=$re['headimgurl'];
 		    $array['message'][$k]['add_name']=$re['nickname'];
			$array['message'][$k]['timestamp']=date("m-d H:i",$v['timestamp']);
            $praiseArr = M('kdgx_public_praise')->field('praise')->where("nid='{$v['id']}' && uid='$uid'")->find();
            $praise_state=$praiseArr['praise'];
            $array['message'][$k]['praise_state']=$praise_state;
            $num=M('kdgx_public_praise')->where("nid='{$v['id']}'")->count();
            $array['message'][$k]['parise_num']=$num;
		}
		$this->ajaxReturn($array);
	}
	
/*----------------------------------------【未读消息】-------------------------------------------*/
	public function state(){
		$uid=$_SESSION['uid'];
		$uid=	decrypt_url ( $uid, 'pocketuniversity' );
		$public_num=$_SESSION['public_num'];
		$sql="SELECT  n.*  FROM  `kdgx_public_news`  AS  n  INNER JOIN   `kdgx_public_inbox`  AS  i  ON  n.id=i.nid  WHERE   i.uid=$uid   && n.type!=3 GROUP BY  i.nid HAVING  MIN(state)=0 ORDER BY  timestamp DESC";
        $arr = M()->query($sql);
        $array['ad'] = count($arr);
		$sql="SELECT  n.* FROM `kdgx_public_news`  AS  n  INNER JOIN  `kdgx_public_inbox`  AS  i  ON  n.id=i.nid  WHERE  i.uid=$uid   && n.type=3  GROUP BY  i.nid  HAVING MIN(state)=0  ORDER BY  timestamp DESC";
        $arr = M()->query($sql);
        $array['message'] = count($arr);
        $array['num'] = $array['ad'] + $array['message'];
        $this->ajaxReturn($array);
	}

/*----------------------------------【更改状态】-----------------------------------------*/
	public function check(){
		$nid=$_POST['nid'];
		$uid = $_SESSION['uid'];
		$uid=	decrypt_url ( $uid, 'pocketuniversity' );
		$public_num=$_SESSION['public_num'];
		$re=M ( "kdgx_public_inbox" )->where ( "uid='$uid' && nid='$nid'  " )->setInc("state");
		if($re){
			echo "1";
		}else{
			echo "0";
		}
	}
	
	
	
/*------------------------------------【发表评论】------------------------------------------*/
	Public function add_comment(){
	    if(!empty($_POST)){
	        $uid=$_SESSION['uid'];
	        $uid=	decrypt_url ( $uid, 'pocketuniversity' );
	        $_POST['comment_uid']=$uid;
	        $_POST['timestamp']=time();
	     $re = M('kdgx_public_comment')->data($_POST)->add();
	     if($re){
                echo json_encode(array('errcode' => 0,'errmsg' => '评论成功')); exit();
	     }else{
	           echo json_encode(array('errcode'=>2,'errmsg'=>'提交失败'));exit();
	     }
	    }else{
	        echo json_encode(array('errcode'=>1,'errmsg'=>'非法访问'));exit();
	    }
	}

	
/*-------------------------------------【文章的评论消息】-------------------------------------*/
	public function comment(){
	    if(!empty($_GET['nid'])){
	        $nid=$_GET['nid'];
	        $sql="SELECT   c.id,c.nid, c.comment_uid, c.content, c.timestamp,c.praise_num, p.name, u.header_img    FROM  `kdgx_public_comment` as c 
	           INNER   JOIN    `kddx_user_info`as  u  ON c.comment_uid=u.uid   INNER  JOIN  `kdgx_operator` as  p ON  p.uid=u.uid  
	        WHERE nid='$nid' ";
	        $arr=M()->query($sql);
	        foreach ($arr as $k=>$v){
	            $arr[$k]['timestamp']=date("Y-m-d H:i",$v['timestamp']);
	        }
	        $array['comment']=$arr;
	        $this->ajaxReturn($array);
	    }else{
	        echo json_encode(array('errcode'=>1,'errmsg'=>'非法访问'));
	    }
	}
	

/*--------------------------【对发布者点赞】--------------------------------*/
	function praise(){
	    
        if ($_GET['nid']) {
            $uid = $_SESSION['uid'];
            $uid = decrypt_url($uid, 'pocketuniversity');
            $nid = $_GET['nid'];
            $re = M('kdgx_public_praise')->where("nid='$nid' && uid='$uid'")->find();
            if(!$re){
                $data['nid'] = $nid;
                $data['uid'] = $uid;
                $data['praise']='1';
                $data['timestamp'] = time();
                $re = M('kdgx_public_praise')->data($data)->add();
                if ($re) {
                    $num = M("kdgx_public_praise")->where("nid='$nid' && praise='1' ")->count();
                    echo json_encode(array('errcode'=>0,'errmsg'=>$num));
                } else {
                    echo json_encode(array('errcode'=>2,'errmsg'=>'操作失败'));
                }
            }else{
                echo json_encode(array('errcode'=>3,'errmsg'=>'禁止重复点赞'));
            }
        } else {
	        echo json_encode(array('errcode'=>1,'errmsg'=>'非法访问'));
        }
    }

/* --------------------------------------【对评论者点赞】---------------------------------------- */
    Public function praise_num()
    {
        if ($_GET['pid']) {
            $id=$_GET['pid'];
            $re = M('kdgx_public_comment')->where("id='$id'")->setInc('praise_num', 1);
            if ($re) {
               $arr= M('kdgx_public_comment')->field('praise_num')->where("id=$id")->find();
               $praise_num=$arr['praise_num'];
                echo json_encode(array(  'errcode' => 0, 'errmsg' => $praise_num ));
            } else {
                echo json_encode( array('errcode' => 2, 'errmsg' => '操作失败'  ));
            }
        }else{
            echo json_encode(array( 'errcode'=>1, 'errmsg'=>'非法访问'));
        }
    }
	
	
	
	
	
	
	
	
	
	
	
}
 ?>