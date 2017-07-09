<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Tuolaji <479923197@qq.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;

use Common\Controller\AdminbaseController;

class TeacherAdminController extends AdminbaseController {

    protected $teacher_model;
    protected $status;
    function _initialize() {
        parent::_initialize();
        $this->teacher_model =M("teacher");

        $this->status = array(
            1=>'未审核',
            2=>'预约中',
            3=>'预约中',
            4=>'成功',
        );
    }

    // 后台页面管理列表
    public function index()
    {
        // where条件拼接
        $where  =   '';
        if ($id = $_REQUEST['id']) {
            $where['id']    =   get_teacher_id($id);
        }

        if ($phone = $_REQUEST['phone']) {
            $where['phone'] =   array('like','%'.$phone.'%');
        }

        if ($name = $_REQUEST['name']) {
            $where['name']  =   array('like', '%'.$name.'%');
        }

        if ($status =   $_REQUEST['status']) {
            $where['status']  =   $status;
        }

        if ($identity = $_REQUEST['identity']) {
            $where['identity']  =   $identity;
        }

        if ($tag_id = $_REQUEST['tag_id']) {
            $tag_where              =   array();
            $tag_where['tag_id']    =   $tag_id;
            $tag_data = M('subscription_teacher')->field('teacher_id')->where($tag_where)->select();
            $ids    =   array();
            if ($tag_data) {
                foreach($tag_data as $tag_one) {
                    $ids[]    =   $tag_one['teacher_id'];
                }
                $where['id']    =   array('in', $ids);
            }else {
                $where['id']    =   100000;
            }
        }

        $count=$this->teacher_model->where($where)->count();

        $page = $this->page($count, 20);
        $teacher_data = $this->teacher_model
            ->where($where)
            ->limit($page->firstRow , $page->listRows)
            ->order("id DESC")
            ->select();

//        echo '<pre>';
//        print_r($teacher_data);die;
        // 老师身份
        $identity = array(
            1   =>  '大学生',
            2   =>  '研究生',
            3   =>  '在职教师',
            4   =>  '专职老师'
        );

        $stage = array(
            1   =>  '高中老师',
            2   =>  '初中老师',
            3   =>  '小学老师'
        );

        // 得到订阅标签
        $this->assign('tag', M('subscription_tag')->select());

        // 得到辅导课程
        $counseling = sp_get_counseling();
        $this->assign('stage', $stage);
        $this->assign('identity', $identity);
        $this->assign('status', $this->status);
        $this->assign('counseling', $counseling);
        $this->assign("page", $page->show('Admin'));
        $this->assign('teacher_data', $teacher_data);

        $this->assign("formget",array_merge($_GET,$_POST));
        $this->display();
    }

    private function get_data($post)
    {
        $data['name']   =   htmlspecialchars($post['name']);
        $data['sex']    =   (int)$post['sex'];

        // 验证手机号
        $phone_auth       =   '/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/';
        $phone      =   $post['phone'];
        if ($phone) {
            if (!preg_match($phone_auth, $phone)) {
                $array = array('info'=>'手机格式有误','status'=>0);
                echo json_encode($array);die;
            } else {
                $data['phone']  =   $phone;
            }
        }

		$small_cost	=	(int)$post['small_cost'];
		if (!$small_cost) {
			$array = array('info'=>'小学课时费不能为空','status'=>0);
			echo json_encode($array);die;
		}
		$data['small_cost']		=	$small_cost;

		$junior_cost	=	(int)$post['junior_cost'];
		if (!$junior_cost) {
			$array = array('info'=>'初中课时费不能为空','status'=>0);
			echo json_encode($array);die;
		}
		$data['junior_cost']		=	$junior_cost;

		$high_cost	=	(int)$post['high_cost'];
		if (!$high_cost) {
			$array = array('info'=>'高中课时费不能为空','status'=>0);
			echo json_encode($array);die;
		}
		$data['high_cost']		=	$high_cost;


        $email_auth =   '/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])*(\.([a-z0-9])([-a-z0-9_-])([a-z0-9])+)*$/i';
        $email      =   $post['email'];
        if ($email) {
            if (!preg_match($email_auth, $email)) {
                $array = array('info'=>'邮箱格式有误','status'=>0);
                echo json_encode($array);die;
            } else {
                $data['email']  =   $email;
            }
        }

        $data['university'] =   $post['university'] ? htmlspecialchars($post['university']) : '';
        $data['profession'] =   $post['profession'] ? htmlspecialchars($post['profession']) : '';
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

        $post['smeta']['thumb'] = sp_asset_relative_url($post['smeta']['thumb']);

        $data['smeta']          =   json_encode($post['smeta']);
        $identity       =   (int)$post['identity'];
        if ($identity) {
            $data['identity']   =   $identity;
        } else {
            $array = array('info'=>'请输入老师身份','status'=>0);
            echo json_encode($array);die;
        }

        $data['remarks']        =   htmlspecialchars($post['remarks']);
        $data['status']         =   (int)$post['status'];
        $data['is_black']       =   (int)$post['is_black'];

//        $data['ip']         =   $_SERVER['REMOTE_ADDR'];
//        $data['add_time']   =   date('Y-m-d H:i:s', time());
        return $data;
    }

    // 页面编辑
    public function edit(){
        $id= I("get.id",0,'intval');
        $teacher     =   $this->teacher_model->where(array('id'=>$id))->find();
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
        $this->display();
    }

    // 页面编辑提交
    public function edit_post(){
        if (IS_POST) {
            $post = I("post.");

            $data = $this->get_data($post);

            $data['id'] = (int)$post['id'];

            $result=$this->teacher_model->save($data);
            if ($result !== false) {
                $this->add_json();
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
        }
    }

    // 删除页面
    public function delete(){
        if(isset($_POST['ids'])){
            $ids = array_map("intval", $_POST['ids']);
            $ids = implode(',', $ids);
            if ($this->teacher_model->where(array("id"=>array("in", $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }else{
            if(isset($_GET['id'])){
                $id = I("get.id",0,'intval');
                if ($this->teacher_model->delete($id)) {
                    $this->add_json();
                    $this->success("删除成功！");
                } else {
                    $this->error("删除失败！");
                }
            }
        }
    }

    // 查看预约老师
    public function ttoporder()
    {
        if (isset($_GET['id'])) {
            $id = I("get.id",0,'intval');
            $where['teacher_id'] =   $id;
            $ttoporder = M('ttoporder')->where($where)->select();

            $teacher_ids = array();
            foreach($ttoporder as $ttoporder_one) {
                $teacher_ids[] = $ttoporder_one['teacher_id'];
            }
            $teacher_ids = implode(',', $teacher_ids);

            $teacher_data = M('teacher')->field('id,name')->where(array('id'=>array('in',$teacher_ids)))->select();

            $teacher_swap = array();
            foreach($teacher_data as $teacher_one) {
                $teacher_swap[$teacher_one['id']]	=	$teacher_one['name'];
            }

            $status = array(
                1   =>  '报名中',
                2   =>  '试讲中',
                3   =>  '成功',
            );

            $this->assign('status', $status);
            $this->assign('teacher', $teacher_swap);
            $this->assign('ttoporder', $ttoporder);
            $this->display();
        } else {
            $this->error('缺少重要参数');
        }
    }

    // 修改老师预约的状态
    public function edit_ttoporder()
    {
        if (IS_POST) {
            $data['id']     =   (int)$_POST['id'];
            $data['status'] =   (int)$_POST['status'];
            $res = M('ttoporder')->save($data);

            if ($res) {
                echo 1;die;
            } else {
                echo 2;die;
            }
        } else {
            $this->error('缺少参数');
        }
    }

    // 发送订阅短信
    public function sendsms()
    {
        $where = array('option_name'=>'sms');
        $option = M('Options')->where($where)->find();

        $option_value   =   json_decode($option['option_value'], true);
        $value = $option_value['value'];
        $msg = $option_value['message'];

        if ($value != 1) {
            $this->error('未开启群发短信功能，请联系管理员');
        }
        if(isset($_POST['ids'])){
            $ids = array_map("intval", $_POST['ids']);
            $ids = implode(',', $ids);
            $data = $this->teacher_model->field('phone')->where(array("id"=>array("in", $ids)))->select();
            foreach($data as $data_one) {
                // 发送订阅通知短信
                $message = sp_send_sms('notice', $data_one['phone'], $msg);
                if ($message['code'] != 1) {
                    $this->error('短信接口有问题，请联系技术，错误提示：'.$message['msg']);
                }
            }
            $this->error('发送成功');
        } else {
            return $this->error("请稍后重试！");;
        }
    }

    // 更改审核状态
    public function is_status()
    {
        $id =   (int)I('get.id');
        $is_status  =   (int)I('get.is_status');
        if (!$id || !$is_status) {
            $this->error();
        }
        $where['id']        =   $id;
        $where['is_status'] =   $is_status;

        $res = $this->teacher_model->save($where);

        if ($res) {
            $this->success();
        } else {
            $this->error();
        }
    }

    public function repass()
    {
        $id     =   I('get.id');
        $this->assign('id', $id);
        $this->display();
    }

    public function password_post()
    {
        if (IS_POST) {
            if(empty($_POST['password'])){
                $this->error("新密码不能为空！");
            }
            $uid=(int)$_POST['id'];
            if (!$uid) {
                $this->error("缺少id！");
            }

            $password=I('post.password');

            if($password==I('post.repassword')){
                $data['password']=md5('ak47'.$password);
                $data['id']=$uid;
                $r=$this->teacher_model->save($data);
                if ($r!==false) {
                    $this->success("修改成功！");
                } else {
                    $this->error("修改失败！");
                }
            }else{
                $this->error("密码输入不一致！");
            }
        }
    }



    // 更新首页家长需求的json      首页家长需求显示
    public function add_json()
    {
        $where['status']    =   2;
        $teacher_data = $this->teacher_model->where($where)->order('last_time desc')->limit('0,12')->select();
        foreach($teacher_data as $k=>$teacher_one) {
            $thumb  =   $teacher_one['smeta'];
            $thumb  =   json_decode($thumb, true);
            $thumb  =   $thumb['thumb'];
            $teacher_data[$k]['thumb']      =   sp_get_image_preview_url($thumb);
            $teacher_data[$k]['url']        =   U('Teacher/resume', array('id'=>4, 'teacher_id'=>$teacher_one['id']));
        }

        $json_array	=	file_get_contents(SITE_PATH.'/index_json/index.json');

        if ($json_array) {
            $json_array	=	json_decode($json_array, true);
        }

        $json_array['teacher']	=	$teacher_data;
        $json_array 	=	json_encode($json_array);

        file_put_contents(SITE_PATH.'/index_json/index.json', $json_array);
    }
}