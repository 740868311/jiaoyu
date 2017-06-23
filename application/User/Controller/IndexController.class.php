<?php
namespace User\Controller;

use Common\Controller\HomebaseController;

class IndexController extends HomebaseController {

	function _initialize() {
		parent::_initialize();

		if (!sp_is_user_login()) {
			redirect(__ROOT__."/");
		}
	}
    
    // 前台用户首页 (公开)
	public function index() {
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


		// 订阅标签部分

		// 得到该老师订阅的标签
		$tag_where	=	array(
			'teacher_id'	=>	$user['id'],
		);
		$tag_teacher = M('subscription_teacher')->field('tag_id')->where($tag_where)->select();
		$tag_teacher_swap	=	array();
		foreach($tag_teacher as $tag_teacher_one) {
			$tag_teacher_swap[]	=	$tag_teacher_one['tag_id'];
		}

		$tag_data	=	M('subscription_tag')->select();
		$tag_swap 	=	array();

		foreach($tag_data as $tag_one) {
			$tag_swap[$tag_one['id']]	=	$tag_one['tag'];
		}

		$this->assign('tag_in', $tag_teacher_swap);
		$this->assign('tag', $tag_swap);
		// 订阅标签部分结束


		// 预约家长
		$ttop_where		=	array(
			'teacher_id'	=>		$user['id'],
		);
		$ttop_data = M('ttoporder')->where($ttop_where)->select();

		$demand_where_array	=	array();
		$ttop_status		=	array();
		foreach($ttop_data as $ttop_one) {
			$demand_where_array[]	=	$ttop_one['demand_id'];
			$ttop_status[$ttop_one['demand_id']]			=	$ttop_one['status'];
		}

		$demand_ids	=	implode(',', $demand_where_array);
		$demand_where['id'] 	=	array('in', $demand_ids);

		$demand_data = M('demand')->where($demand_where)->select();

		$this->assign('demand', $demand_data);

		// 周几
		$week	=	array(
			1	=>	'周一',
			2	=>	'周二',
			3	=>	'周三',
			4	=>	'周四',
			5	=>	'周五',
			6	=>	'周六',
			7	=>	'周日',
		);

		//
		$z	=	array(
			1	=>	'寒假',
			2	=>	'暑假',
			3	=>	'长期',
			4	=>	'面议'
		);

		$status	=	array(
			1	=>	'报名中',
			2	=>	'试讲中',
			3	=>	'成功'
		);
		$this->assign('week', $week);
		$this->assign('z',	$z);
		$this->assign('status', $status);
		$this->assign('ttop_status', $ttop_status);
		// 预约家长end

		$this->display(":index");
    }

	// 修改密码
	public function repass()
	{
		if (IS_POST) {
			$post = I('post.');

			$user = session("user");

			$old 	=	$post['old'];
			if (!$old) {
				$array = array('info'=>'旧密码不能为空','status'=>0);
				echo json_encode($array);die;
			}
			$where['password']	=	md5('ak47'.$old);
			$where['id']		=	$user['id'];

			$res = M('teacher')->where($where)->find();

			if (!$res) {
				$array = array('info'=>'旧密码不正确，重新输入','status'=>0);
				echo json_encode($array);die;
			}

			$new		=	$post['password'];
			$repassword	=	$post['repassword'];

			if (!$new) {
				$array = array('info'=>'新密码不能为空','status'=>0);
				echo json_encode($array);die;
			} else if($new != $repassword) {
				$array = array('info'=>'两次输入不一直','status'=>0);
				echo json_encode($array);die;
			} else if (!preg_match('/^[a-zA-Z\d_]{6,}$/', $new)) {
				$array = array('info'=>'密码包含英文数字下划线，并且长度6位以上','status'=>0);
				echo json_encode($array);die;
			}

			$data['password']	=	md5('ak47'.$new);
			$where_new = array(
				'id'	=>	$user['id']
			);
			$res 	=	 M('teacher')->where($where_new)->save($data);

			if ($res) {
				$array = array('info'=>'修改成功','status'=>1);
				echo json_encode($array);die;
			} else {
				$array = array('info'=>'修改失败','status'=>0);
				echo json_encode($array);die;
			}
		} else {
			$array = array('info'=>'缺少参数','status'=>0);
			echo json_encode($array);die;
		}
	}

	// 取消订阅或者添加
	public function edit_subscription()
	{
		if(IS_POST) {
			$post 	=	I('post.');
			$tag_id	=	(int)$post['tag_id'];
			$user = session("user");
			$teacher_id	=	$user['id'];

			//	订阅
			if ($post['action']	==	'on') {
				$data['tag_id']		=	$tag_id;
				$data['teacher_id']	=	$teacher_id;
				$res = M('subscription_teacher')->where($data)->find();
				if ($res) {
					$array = array('info'=>'请勿重复添加','status'=>0);
					echo json_encode($array);die;
				} else {
					$res_add = M('subscription_teacher')->add($data);
					if ($res_add) {
						$array = array('info'=>'订阅成功','status'=>1);
						echo json_encode($array);die;
					} else {
						$array = array('info'=>'订阅失败','status'=>0);
						echo json_encode($array);die;
					}
				}
			}
			// 取消订阅
			else if($post['action']	==	'un') {
				$where['tag_id']		=	$tag_id;
				$where['teacher_id']	=	$teacher_id;

				$res_del = M('subscription_teacher')->where($where)->delete();
				if ($res_del) {
					$array = array('info'=>'删除成功','status'=>1);
					echo json_encode($array);die;
				} else {
					$array = array('info'=>'删除失败','status'=>0);
					echo json_encode($array);die;
				}

			} else {
				$array = array('info'=>'未知错误','status'=>0);
				echo json_encode($array);die;
			}
		} else {
			$array = array('info'=>'修改失败','status'=>0);
			echo json_encode($array);die;
		}
	}

	// 修改老师信息
	public function edit_post()
	{
		if (IS_POST) {
			$post 	=	I('post.');
			if (!$post['name']) {
				$array = array('info'=>'请填写姓名','status'=>0);
				echo json_encode($array);die;
			}
			$data['name']   =   htmlspecialchars($post['name']);
			$data['sex']    =   (int)$post['sex'] ? (int)$post['sex'] : 1;
			$data['grade']      =   $post['grade'] ? htmlspecialchars($post['grade']) : '';
			$data['stage']      =   (int)($post['stage']);
			$counseling_ids = ',';
			foreach($post['counseling_id'] as $counseling_one) {
				if ((int)$counseling_one && strpos($counseling_ids, ','.(int)$counseling_one.',') === false) {
					$counseling_ids .= (int)$counseling_one.',';
				}
			}

			if (!empty($counseling_ids)) {
				$data['counseling_ids']     =   ','.trim($counseling_ids, ',').',';
			} else {
				$array = array('info'=>'请输入辅导课程','status'=>0);
				echo json_encode($array);die;
			}
			if (!empty($post['counseling_other'])) {
				$data['counseling_other']   =   htmlspecialchars($post['counseling_other']);
			}

			$data['introduction']           =   htmlspecialchars($post['introduction']);
			$data['teaching_experience']    =   htmlspecialchars($post['teaching_experience']);

			if(!empty($post['photos_alt']) && !empty($post['photos_url'])){
				foreach ($post['photos_url'] as $key=>$url){
					$photourl=sp_asset_relative_url($url);
					$post['smeta']['photo'][]=array("url"=>$photourl,"alt"=>$post['photos_alt'][$key]);
				}
			}

			$post['smeta']['thumb'] = sp_asset_relative_url($post['thumb']);

			$data['smeta']          =   json_encode($post['smeta']);
			$identity       =   (int)$post['identity'];
			if ($identity) {
				$data['identity']   =   $identity;
			} else {
				$array = array('info'=>'请输入老师身份','status'=>0);
				echo json_encode($array);die;
			}

			$user = session("user");

			$where	=	array(
				'id'	=>	$user['id'],
			);

			$res = M("teacher")->where($where)->save($data);

			if ($res) {
				$teacher     =   M("teacher")->where($where)->find();
				session("user", $teacher);
				$array = array('info'=>'修改成功','status'=>1);
				echo json_encode($array);die;
			} else {
				$array = array('info'=>'修改失败','status'=>0);
				echo json_encode($array);die;
			}



		} else {
			$data	=	array('status'=>0,'info'=>'未知错误');
			return json_encode($data);
		}
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
