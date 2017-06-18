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

	protected $demand_model;

	function _initialize() {
		parent::_initialize();
		$this->demand_model =D("demand");

		// 得到当前是否开启短信验证
		$where = array('option_name'=>'code_manner');
		$option = M('Options')->field('option_value')->where($where)->find();
		$option = json_decode($option['option_value'], true);

		// 得到老师用什么验证码：1图形验证码  2短信验证码
		$this->is_code = $option['parents']['value'];

		$this->assign('is_code', $this->is_code);
	}

	// 家长发布需求的页面
	public function index()
	{
		echo '<pre>';
		print_r($_SESSION);
//		echo time() - $_SESSION['yzm']['time'];die;
		print_r(sp_send_sms('yzm', '15501246050', '【叮咚云】您的验证码是：2222', 2222));
		print_r($_SESSION);die;
		die;
		$this->display();
	}



	public function add_post()
	{
		if (IS_POST) {

			// 暂时注销掉，等页面都有了，在测试

//            if ($this->is_code == 1) {
//                // 检测图形验证码
//                if(!sp_check_verify_code()){
//                    $array  =   array('info'=>'验证码出错，请重新输入', 'status'=>0);
//                    echo json_encode($array);die;
//                }
//            } else {
//                // 检测短信验证码
//                if (!sp_check_sms_code()) {
//                    $array  =   array('info'=>'短信验证码过期或者出错，请重新输入', 'status'=>0);
//                    echo json_encode($array);die;
//                }
//            }

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
                $this->add_json();
				$this->success("添加成功！");
			} else {
				$this->error("添加失败！");
			}
		}
	}

	private function get_data($post)
	{
		$data['name']   =   htmlspecialchars($post['name']);
		$grade_id       =   (int)$post['grade_id'];
		if ($grade_id) {
			$data['grade_id']   =   $grade_id;
		} else {
			$array = array('info'=>'请选择年级','status'=>0);
			dump($array);die;
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
			dump($array);die;
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
			dump($array);die;
			echo json_encode($array);die;
		}

		$data['teacher_sex']    =   (int)$post['teacher_sex'];

		$teacher_identity       =   (int)$post['teacher_identity'];
		if ($teacher_identity) {
			$data['teacher_identity']   =   $teacher_identity;
		} else {
			$array = array('info'=>'请输入老师身份','status'=>0);
			dump($array);die;
			echo json_encode($array);die;
		}

		$data['remarks']        =   htmlspecialchars($post['remarks']);
		$data['address']        =   htmlspecialchars($post['address']);
		$data['note']           =   htmlspecialchars($post['note']);
		$data['phone']          =   $post['phone'];


		if (!empty($post['S'])) {
			$data['tutor_time_s']   =   implode(',', $post['S']);
		}
		if (!empty($post['W'])) {
			$data['tutor_time_w']   =   implode(',', $post['W']);
		}
		if (!empty($post['X'])) {
			$data['tutor_time_x']   =   implode(',', $post['X']);
		}
		if (!empty($post['Z'])) {
			$data['tutor_time_z']   =   implode(',', $post['Z']);
		}
		if ($post['Z'] || $post['S'] || $post['W'] || $post['X']) {

		} else {
			$array = array('info'=>'家教时间请至少选择一天','status'=>0);
			dump($array);die;
			echo json_encode($array);die;
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

		$where['status']	=	$status;

		$count=$this->demand_model->count();

		$page = $this->page($count, 5);
		$demand_data = $this->demand_model
			->where($where)
			->limit($page->firstRow , $page->listRows)
			->order("add_time DESC")
			->select();

	}

	// 学员详情
	public function demand_show()
	{
		$id = (int)I('get.id');

		if ($id) {
			$this->error('缺少ID');
		}
		$where['id']	=	$id;
		$this->demand_model->where($where)->select();
	}

	// 更新首页家长需求的json      首页家长需求显示
	public function add_json()
	{
		$demand_data = $this->demand_model->order('add_time desc')->limit('0,10')->select();

		$json_array	=	file_get_contents(SITE_PATH.'/index_json/index.json');

		if ($json_array) {
			$json_array	=	json_decode($json_array, true);
		}

		$json_array['demand']	=	$demand_data;
		$json_array 	=	json_encode($json_array);

		file_put_contents(SITE_PATH.'/index_json/index.json', $json_array);
	}
}