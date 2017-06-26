<?php
/**
 *  前台家长需求相关页面
 *
 *
 */
namespace Portal\Controller;
use Common\Controller\HomebaseController;
class DemandController extends HomebaseController {

	// 验证码方式：1是图形验证   2：短信验证
	public $is_code;
	protected $d_code;

	protected $demand_model;
    protected $status;

	function _initialize() {
		parent::_initialize();
		$this->demand_model =D("demand");
        $this->status = array(
            1=>'未审核',
            2=>'预约中',
            3=>'预约中',
            4=>'成功',
        );

		// 得到当前是否开启短信验证
		$where = array('option_name'=>'code_manner');
		$option = M('Options')->field('option_value')->where($where)->find();
		$option = json_decode($option['option_value'], true);

		// 得到家长用什么验证码：1图形验证码  2短信验证码
		$this->is_code = $option['parents']['value'];

		// 得到老师用什么验证码：1图形验证码  2短信验证码
		$this->d_code	=	$option['doctor']['value'];

		$this->assign('is_code', $this->is_code);
		$this->assign('d_code', $option['doctor']['value']);
	}

	// 家长发布需求的页面
	public function index()
	{
//		echo '<pre>';
//		print_r($_SESSION);
////		echo time() - $_SESSION['yzm']['time'];die;
//		print_r(sp_send_sms('yzm', '15501246050', '【叮咚云】您的验证码是：2222', 2222));
//		print_r($_SESSION);die;
//		die;
		$this->display();
	}



	public function add_post()
	{
		if (IS_POST) {
			// 暂时注销掉，等页面都有了，在测试
            if ($this->is_code == 1) {
                // 检测图形验证码
                if(!sp_check_verify_code()){
                    $array  =   array('info'=>'验证码出错，请重新输入', 'status'=>0);
                    echo json_encode($array);die;
                }
            } else {
                // 检测短信验证码
                if (!sp_check_sms_code()) {
                    $array  =   array('info'=>'短信验证码过期或者出错，请重新输入', 'status'=>0);
                    echo json_encode($array);die;
                }
            }

			$post = I("post.");


			$data = $this->get_data($post);


			$data['ip']         =   $_SERVER['REMOTE_ADDR'];
			$data['add_time']   =   date('Y-m-d H:i:s', time());
			$data['status']		=	1;

			$res = $this->demand_model -> create($data);
			if (!$res) {
				$this->error($this->demand_model->getError());
			} else{
				$result = $this->demand_model->add($data);
			}

			if ($result) {
//                $this->add_json();
				// 判断是否
				$where = array('option_name'=>'email_warn');
				$option = M('Options')->where($where)->find();
				if($option){
					$options = json_decode($option['option_value'], true);
					// 如果是1则想指定邮箱发送提示邮件
					if ($options['value'] == 1) {
                        sp_send_email($options['options']['to'], $options['options']['title'], $options['options']['template']);
					}
				}

                $array  =   array('info'=>'添加成功', 'status'=>1);
                echo json_encode($array);die;
			} else {
                $array  =   array('info'=>'添加失败', 'status'=>0);
                echo json_encode($array);die;
			}
		}
	}

	public function add_kspost()
	{
		if (IS_POST) {
			// 暂时注销掉，等页面都有了，在测试
			if ($this->is_code == 1) {
				// 检测图形验证码
				if(!sp_check_verify_code()){
					$array  =   array('info'=>'验证码出错，请重新输入', 'status'=>0);
					echo json_encode($array);die;
				}
			} else {
				// 检测短信验证码
				if (!sp_check_sms_code()) {
					$array  =   array('info'=>'短信验证码过期或者出错，请重新输入', 'status'=>0);
					echo json_encode($array);die;
				}
			}

			$post = I("post.");

			$phone                  =   $post['phone'];

			// 判断手机号是否是黑名单
			$phone_balck	=	sp_get_phone_black();
			if (in_array($phone, $phone_balck)) {
				$array  =   array('info'=>'手机号已被拉黑，请联系管理员', 'status'=>1);
				echo json_encode($array);die;
			}

			if (!$post['name']) {
				$array  =   array('info'=>'姓名不能为空', 'status'=>1);
				echo json_encode($array);die;
			}
			$data['name']   =   htmlspecialchars($post['name']);

			$counseling_ids = ',';

			foreach($post['counseling_id'] as $counseling_one) {
				if ((int)$counseling_one && strpos($counseling_ids, ','.(int)$counseling_one.',') === false) {
					$counseling_ids .= (int)$counseling_one.',';
				}
			}

			if (!empty($counseling_ids)) {
				$data['counseling_ids']     =   trim($counseling_ids, ',');
			} else {
				$array = array('info'=>'请输入辅导课程','status'=>0);
				echo json_encode($array);die;
			}

			if (!empty($post['counseling_other'])) {
				$data['counseling_other']   =   htmlspecialchars($post['counseling_other']);
			}

			$phone_auth       =   '/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/';
			if (!preg_match($phone_auth, $phone)) {
				$array = array('info'=>'手机格式有误','status'=>0);
				echo json_encode($array);die;
			}
			$data['phone']          =   $phone;

			$data['address']        =   htmlspecialchars($post['address']);

			$data['ip']         =   $_SERVER['REMOTE_ADDR'];
			$data['add_time']   =   date('Y-m-d H:i:s', time());
			$data['status']		=	1;

			$res = $this->demand_model -> create($data);
			if (!$res) {
				$this->error($this->demand_model->getError());
			} else{
				$result = $this->demand_model->add($data);
			}

			if ($result) {
//				$this->add_json();
				// 判断是否
				$where = array('option_name'=>'email_warn');
				$option = M('Options')->where($where)->find();
				if($option){
					$options = json_decode($option['option_value'], true);
					// 如果是1则想指定邮箱发送提示邮件
					if ($options['value'] == 1) {
						sp_send_email($options['options']['to'], $options['options']['title'], $options['options']['template']);
					}
				}
				$array  =   array('info'=>'添加成功', 'status'=>1);
				echo json_encode($array);die;
			} else {
				$array  =   array('info'=>'添加失败', 'status'=>0);
				echo json_encode($array);die;
			}
		}
	}

	// 前台ajax 判断用户登录状态接口
	public function is_login(){
		if(sp_is_user_login()){
			$data	=	array('status'=>1);
			echo json_encode($data);die;
		}else{
			$data	=	array('status'=>0);
			echo  json_encode($data);die;
		}
	}

    public function get_sms()
    {
		$phone	=	I('post.phone');

        if (I('post.is_login')) {
            if (!sp_is_user_login()) {
                $array = array('info'=>'请先登陆','status'=>0);
                echo json_encode($array);die;
            }
            $user = session("user");
            $phone = $user['phone'];
            if (!$phone) {
                $array = array('info'=>'手机号为空','status'=>0);
                echo json_encode($array);die;
            }
        }
		$phone_auth       =   '/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/';
		if (!preg_match($phone_auth, $phone)) {
			$array = array('info'=>'手机格式有误','status'=>0);
            echo json_encode($array);die;
		}
		// 判断手机号是否是黑名单
		$phone_balck	=	sp_get_phone_black();
		if (in_array($phone, $phone_balck)) {
			$array  =   array('info'=>'手机号已被拉黑，请联系管理员', 'status'=>1);
			echo json_encode($array);die;
		}

		$code = rand(1000,9999);
		$message = '【叮咚云】您的验证码是：'.$code;
		return sp_send_sms('yzm', $phone, $message, $code);
    }

	private function get_data($post)
	{
		$data['name']   =   htmlspecialchars($post['name']);
		$grade_id       =   (int)$post['grade_id'];
		if ($grade_id) {
			$data['grade_id']   =   $grade_id;
		} else {
			$array = array('info'=>'请选择年级','status'=>0);
			echo json_encode($array);die;
		}
		$data['sex']    =   (int)$post['sex'];
		$counseling_ids = ',';

		foreach($post['counseling_id'] as $counseling_one) {
			if ((int)$counseling_one && strpos($counseling_ids, ','.(int)$counseling_one.',') === false) {
				$counseling_ids .= (int)$counseling_one.',';
			}
		}

		if (!empty($counseling_ids)) {
			$data['counseling_ids']     =   trim($counseling_ids, ',');
		} else {
			$array = array('info'=>'请输入辅导课程','status'=>0);
			echo json_encode($array);die;
		}

		if (!empty($post['counseling_other'])) {
			$data['counseling_other']   =   htmlspecialchars($post['counseling_other']);
		}

		$prepayments            =   (int)$post['prepayments'];
		if ($prepayments) {
			$data['prepayments']    =   $prepayments;
		} else {
			$array = array('info'=>'请输入拟付课酬','status'=>0);
			echo json_encode($array);die;
		}

		$data['teacher_sex']    =   (int)$post['teacher_sex'];

		$teacher_identity       =   (int)$post['identity'];
		if ($teacher_identity) {
			$data['teacher_identity']   =   $teacher_identity;
		} else {
			$array = array('info'=>'请输入老师身份','status'=>0);
			echo json_encode($array);die;
		}

		$data['remarks']        =   htmlspecialchars($post['remarks']);
		$data['address']        =   htmlspecialchars($post['address']);
		$data['note']           =   htmlspecialchars($post['note']);

        $phone                  =   $post['phone'];
        $phone_auth       =   '/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/';
        if (!preg_match($phone_auth, $phone)) {
            $array = array('info'=>'手机格式有误','status'=>0);
			echo json_encode($array);die;
        }
		$data['phone']          =   $phone;



		if (!$post['is_phone']) {
			$time = $post['time'];
			foreach($time as $time_one) {
				$swap = explode('-', $time_one);
				if (!$data['tutor_time_'.strtolower($swap[0])]) {
					$data['tutor_time_'.strtolower($swap[0])]   =   $swap[1];
				} else {
					$data['tutor_time_'.strtolower($swap[0])]   .=   ','.$swap[1];
				}

			}

			if ($time) {

			} else {
				$array = array('info'=>'家教时间请至少选择一天','status'=>0);
				echo json_encode($array);die;
			}
		}

		return $data;
	}

	// 学员库
	public function demand_list()
	{
		$status = (int)I('get.status');

		if (!$status) {
			$status = 2;
		}

		if ($status == 1 || $status == 3) {
			echo '所选状态有误';die;
		}

		$where['status']	=	$status;

		$count=$this->demand_model->count();

		$page = $this->page($count, 5);
		$demand_data = $this->demand_model
			->where($where)
			->limit($page->firstRow , $page->listRows)
			->order("add_time DESC")
			->select();

		// 年级
		$grade = sp_get_grade_name();

		// 得到辅导课程
		$counseling = sp_get_counseling();

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

		$where = array();
		if ($status == 4) {
			foreach($demand_data as $k=>$demand_one) {
				$where['a.demand_id']	=	$demand_one['id'];
				$where['a.status']		=	3;
				$data = M()->table('edu_ttoporder  as  a')->join('edu_teacher  as  b  on  b.id = a.teacher_id')->where($where)->find();
				$demand_data[$k]['teacher']	=	$data;
			}
		}

		$this->assign('week', $week);
		$this->assign('status', $status);
		$this->assign('z',	$z);
		$this->assign('grade', $grade);
		$this->assign('counseling', $counseling);
		$this->assign("page", $page->show('Admin'));
		$this->assign("demand_data", $demand_data);
		$this->display();
	}

	// 学员详情
	public function demand_show()
	{
		$id = (int)I('get.demand_id');

		if (!$id) {
			$this->error('缺少ID');
		}
		$where['id']	=	$id;
		$where['status']=	array('gt', 1);
		$demand_data = $this->demand_model->where($where)->find();
		if (!$demand_data) {
//			$this->error();
		}

		// 得到预约的老师
		$ttop_where	=	array('demand_id'	=>	$id);
		$ttoporder = M('ttoporder')->where($ttop_where)->select();

		$teacher_ids = array();
		$ttop_swap	=	array();
		foreach($ttoporder as $ttoporder_one) {
			$ttop_swap[$ttoporder_one['teacher_id']]	=	$ttoporder_one['status'];
			$teacher_ids[] = $ttoporder_one['teacher_id'];
		}
		$teacher_ids = implode(',', $teacher_ids);

		$teacher_data = M('teacher')->where(array('id'=>array('in',$teacher_ids)))->select();

		$status = array(
			1   =>  '报名中',
			2   =>  '试讲中',
			3   =>  '成功',
		);



		$this->assign('status', $status);
		$this->assign('teacher', $teacher_data);
		$this->assign('ttoporder', $ttop_swap);
		// 得到预约的老师 end

		$td_status = array(
			2	=>	'投递简历',
			3	=>	'试讲中',
			4	=>	'成功'
		);
		// 得到当前需求的状态
		$this->assign('td_status', $td_status);

		// 得到辅导课程
		$counseling = sp_get_counseling();

		$this->assign('counseling', $counseling);
		$this->assign('demand', $demand_data);
		$this->display();
	}

	// 投递简历
	public function add_order()
	{
		if (IS_POST) {
			// 暂时注销掉，等页面都有了，在测试
			if ($this->d_code == 1) {
				// 检测图形验证码
				if(!sp_check_verify_code()){
					$array  =   array('info'=>'验证码出错，请重新输入', 'status'=>0);
					echo json_encode($array);die;
				}
			} else {
				// 检测短信验证码
				if (!sp_check_sms_code()) {
					$array  =   array('info'=>'短信验证码过期或者出错，请重新输入', 'status'=>0);
					echo json_encode($array);die;
				}
			}

			if (!sp_is_user_login()) {
				$this->ajaxReturn(array("status"=>0,'info'=>'请先登录'));
			}
			$user = sp_get_current_user();
			$data['teacher_id']	=	$user['id'];

			$post 	=	I('post.');
			$demand_id = (int)$post['demand_id'];

			if (!$demand_id) {
				$this->ajaxReturn(array("status"=>0, 'info'=>'请刷新后重新尝试'));
			}
			$data['demand_id']	=	$demand_id;

			$teacher_message = $post['teacher_message'];
			if (empty($teacher_message)) {
				$this->ajaxReturn(array("status"=>0, 'info'=>'留言不能为空'));
			}
			$res = M("ttoporder")->where($data)->find();
			if ($res) {
				$this->ajaxReturn(array("status"=>0, 'info'=>'请勿重复投递'));
			}

			$data['teacher_message']	=	$teacher_message;

			$data['status']	=	1;
			$data['add_time']	=	date('Y-m-d H:i:s', time());

			$res = M("ttoporder")->add($data);
			if ($res) {
				$this->ajaxReturn(array("status"=>1, 'info'=>'投递成功'));
			} else {
				$this->ajaxReturn(array("status"=>0, 'info'=>'投递失败'));
			}
		} else {
			$this->ajaxReturn(array("status"=>0, 'info'=>'未知错误'));
		}


	}

    // 更新首页家长需求的json      首页家长需求显示
    public function add_json()
    {
		$where['status']	=	array('gt',1);
        $demand_data = $this->demand_model->where($where)->order('add_time desc')->limit('0,10')->select();

        $json_array	=	file_get_contents(SITE_PATH.'/index_json/index.json');

        if ($json_array) {
            $json_array	=	json_decode($json_array, true);
        }
        // 年级
        $grade = sp_get_grade_name();

        // 性别
        $sex = array(
            1   =>'女',
            2   =>'男'
        );

        // 得到辅导课程
        $counseling = sp_get_counseling();

        foreach($demand_data as $k=>$demand_one) {
            $counseling_data = explode(',', $demand_one['counseling_ids']);
            $swap = array();
            foreach($counseling_data as $counseling_one) {
                $swap[] = $counseling[$counseling_one];
            }
            $counseling_data = implode(',', $swap);
            $demand_data[$k]['counseling']  =   $counseling_data;
            $demand_data[$k]['grade_name']  =   $grade[$demand_one['grade_id']];
            $demand_data[$k]['sex']         =   $sex[$demand_one['sex']];
            $demand_data[$k]['status']      =   $this->status[$demand_one['status']];
        }

        $json_array['demand']	=	$demand_data;
        $json_array 	=	json_encode($json_array);

        file_put_contents(SITE_PATH.'/index_json/index.json', $json_array);
    }
}