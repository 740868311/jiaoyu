<?php
namespace User\Controller;

use Common\Controller\HomebaseController;

class IndexController extends HomebaseController {
    
    // 前台用户首页 (公开)
	public function index() {

		if (!sp_is_user_login()) {
			redirect(__ROOT__."/");
		}
		$user = session("user");

		$where	=	array(
			'id'	=>	$user['id'],
		);
		$teacher     =   M("teacher")->where($where)->find();
		$teacher['counseling_ids'] = explode(',', trim($teacher['counseling_ids'], ','));
		while(true) {
			if (count($teacher['counseling_ids']) < 3) {
				$teacher['counseling_ids'][] = null;
			} else {
				break;
			}
		}
		$this->assign("smeta",json_decode($teacher['smeta'],true));
		$this->assign("teacher",$teacher);
		$this->display(":index");

    }
    
    // 前台ajax 判断用户登录状态接口
    function is_login(){
    	if(sp_is_user_login()){
    		$this->ajaxReturn(array("status"=>1));
    	}else{
    		$this->ajaxReturn(array("status"=>0));
    	}
    }

    //退出
    public function logout(){
    	$ucenter_syn=C("UCENTER_ENABLED");
    	$login_success=false;
    	if($ucenter_syn){
    		include UC_CLIENT_ROOT."client.php";
    		echo uc_user_synlogout();
    	}
    	session("user",null);//只有前台用户退出
    	redirect(__ROOT__."/");
    }

}
