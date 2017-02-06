<?php 
namespace Debug\Controller;
use \Think\Controller;

class IndexController extends Controller{
	public function index(){
		class WebRequest extends Thread {
			public $url;
			public $data;
			public function run(){
				$wechat = new \Org\Util\Wechat();
				echo $wechat->get_user_openidlist('gh_19fb1bed539e');
			}
		}
		$t = microtime(true);
		$g = new WebRequest();
		/* starting synchronized */
		if($g->start()){
			printf("Request took %f seconds to start ", microtime(true)-$t);
			while($g->isRunning()){
				echo ".";
				$g->synchronized(function() use($g) {
					$g->wait(100);
				});
			}
			if ($g->join()){
				printf(" and %f seconds to finish receiving %d bytes\n", microtime(true)-$t, strlen($g->data));
			} else printf(" and %f seconds to finish, request failed\n", microtime(true)-$t);
			
		}
	}

}