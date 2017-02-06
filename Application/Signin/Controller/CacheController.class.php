<?php
namespace Signin\Controller;
use Think\Controller;
class CacheController extends Controller{
    

    Public function index(){

        ini_set("max_execution_time", 0);
        M()->startTrans();
           $sql = "SELECT i.`new_school` FROM `kdgx_signin` AS s INNER JOIN `kddx_user_info` AS i ON s.`uid`=i.`uid` WHERE i.`new_school`!='' && i.`new_school` NOT LIKE '社会人士' GROUP BY i.`new_school` ";
        $arr = M()->query($sql);
        $sql ='delete from `kdgx_signin_school`';
        M()->execute($sql);
        
        $times = date('Y-m-d H:i:s');
        $i=0;

        foreach($arr as $k => $v){
            $sql = "SELECT u.`dayscore`,u.`uid`,i.`new_school` FROM `kdgx_signin` AS u INNER JOIN `kddx_user_info` AS i ON i.`uid` = u.`uid` 
                    WHERE u.`timestamp`>='1484409600' && u.`timestamp`<='1487088000' && i.`new_school`='{$v['new_school']}'";
            $rs = M()->query($sql);
            $score = 0;
            $count = 0;
            foreach($rs as $vt){
                $score += $vt['dayscore'];
                $count ++;
            }
            $re = M('kdgx_signin_school')->add(array('score'=>$score,'count'=>$count,'school_name'=>$v['new_school'],'times'=>$times));
            if(!$re){
                $i++;
            }
        }
        if($i<=0){
            M()->commit();
            echo 'OK';
        }else{
            M()->rollback();
            echo '有'.$i.'所学校未入库,请重试';
        }
        
    }

















}
