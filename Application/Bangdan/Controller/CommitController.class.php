<?php 
namespace Bangdan\Controller;
use \Think\Controller;

class CommitController extends Controller{

	// 提交内容处理
	public function editData(){
		$data = I('post.');
		if($data = M('kdgx_bangdan_commit')->create($data)){
			if(M('kdgx_bangdan_commit')->data($data)->add()){
				$this->redirect('Commit/success');
			}else{
				exit('提交错误!');
			}

		}
	}

	// 展示提交内容
	public function Index(){
		$data = M('kdgx_bangdan_commit')->select();
		$this->assign('data',$data);
		$this->display('m/Reg/showcommit');
	}

	// 成功页面
	public function success(){
		$this->display('m/Reg/success');
	}
}