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

class DemandAdminController extends AdminbaseController {

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


    }

    // 后台页面管理列表
    public function index(){
        // where条件拼接
        $where  =   '';
        if ($id = $_REQUEST['id']) {
            $where['id']    =   (int)$id;
        }

        if ($phone = $_REQUEST['phone']) {
            $where['phone'] =   array('like','%'.$phone.'%');
        }

        if ($name = $_REQUEST['name']) {
            $where['name']  =   array('like', '%'.$name.'%');
        }



        $count=$this->demand_model->count();

        $page = $this->page($count, 20);
        $demand_data = $this->demand_model
            ->where($where)
            ->limit($page->firstRow , $page->listRows)
            ->order("status,add_time DESC")
            ->select();

        // 得到有未审核老师订单的需求信息
        $ttop_where             =   array();
        $ttop_where['status']   =   1;

        $ttop_data = M('ttoporder')
            ->field('demand_id,count("X") as count')
            ->where($ttop_where)
            ->group('demand_id')
            ->select();

        // 有未处理的需求id和预约数
        $ttop_swap  =   array();
        foreach($ttop_data as $ttop_one) {
            $ttop_swap[$ttop_one['demand_id']]  =   $ttop_one['count'];
        }

        $demand_swap    =   array();
        foreach($demand_data as $demand_one) {
            $demand_swap[$demand_one['id']] =   $demand_one;
            $demand_swap[$demand_one['id']]['teacher_none'] =   $ttop_swap[$demand_one['id']];
        }

        // 年级
        $grade = sp_get_grade_name();

        // 得到辅导课程
        $counseling = sp_get_counseling();

        $this->assign('ttop_swap', $ttop_swap);
        $this->assign('grade', $grade);
        $this->assign('status', $this->status);
        $this->assign('counseling', $counseling);
        $this->assign("page", $page->show('Admin'));
        $this->assign('demand_data', $demand_swap);
        $this->assign("formget",array_merge($_GET,$_POST));
        $this->display();
    }

    // 页面添加
    public function add(){
        $this->display();
    }

    // 页面添加提交
    public function add_post(){
        if (IS_POST) {
            $post = I("post.");

            $data = $this->get_data($post);
            $data['ip']         =   $_SERVER['REMOTE_ADDR'];
            $data['add_time']   =   date('Y-m-d H:i:s', time());
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

        $teacher_identity       =   (int)$post['teacher_identity'];
        if ($teacher_identity) {
            $data['teacher_identity']   =   $teacher_identity;
        } else {
            $array = array('info'=>'请输入老师身份','status'=>0);
            echo json_encode($array);die;
        }

        $data['remarks']        =   htmlspecialchars($post['remarks']);
        $data['address']        =   htmlspecialchars($post['address']);
        $data['note']           =   htmlspecialchars($post['note']);
        $data['phone']          =   $post['phone'];
        $status                 =   (int)$post['status'];
        if ($status) {
            $data['status']         =   (int)$post['status'];
        } else {
            $array = array('info'=>'请选择需求状态', 'status'=>0);
            echo json_encode($array);die;
        }


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
            echo json_encode($array);die;
        }
        return $data;
    }

    // 页面编辑
    public function edit(){
        $id= I("get.id",0,'intval');
        $demand     =   $this->demand_model->where(array('id'=>$id))->find();
        $demand['counseling_ids'] = explode(',', $demand['counseling_ids']);
        while(true) {
            if (count($demand['counseling_ids']) < 3) {
                $demand['counseling_ids'][] = null;
            } else {
                break;
            }
        }

        $this->assign("demand",$demand);
        $this->display();
    }

    // 页面编辑提交
    public function edit_post(){
        if (IS_POST) {
            $post = I("post.");

            $data = $this->get_data($post);
//            print_r($data);die;
            $data['id'] = (int)$post['id'];

            $result=$this->demand_model->save($data);
            if ($result !== false) {
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
            if ($this->demand_model->where(array("id"=>array("in", $ids)))->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }else{
            if(isset($_GET['id'])){
                $id = I("get.id",0,'intval');
                if ($this->demand_model->delete($id)) {
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
            $where['demand_id'] =   $id;
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

    // 更新首页家长需求的json      首页家长需求显示
    public function add_json()
    {
        $where['status']	=	array('gt',1);
        $demand_data = $this->demand_model->where($where)->order('add_time desc')->limit('0,8')->select();

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
            $demand_data[$k]['url']         =   U('Demand/demand_show', array('id'=>6, 'demand_id'=>$demand_one['id']));
        }

        $json_array['demand']	=	$demand_data;
        $json_array 	=	json_encode($json_array);

        file_put_contents(SITE_PATH.'/index_json/index.json', $json_array);
    }
}