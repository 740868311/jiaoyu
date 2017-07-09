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

class PtotorderAdminController extends AdminbaseController {

	protected $ptotorder_model;
	protected $status;
	function _initialize() {
		parent::_initialize();
		$this->ptotorder_model =M("ptotorder");

		$this->status = array(
			1=>'未审核',
			2=>'预约中',
			3=>'预约中',
			4=>'成功',
		);
	}

	// 后台页面管理列表
	public function index(){
		$count=$this->ptotorder_model->count();

        // where条件拼接
        $where  =   '';
        // 家长姓名
        if ($demand_name = $_REQUEST['demand_name']) {
            $where['name']    =   array('like','%'.$demand_name.'%');
        }

        // 手机号
        if ($phone = $_REQUEST['phone']) {
            $where['phone'] =   array('like','%'.$phone.'%');
        }

        // 老师姓名
        if ($teacher_name = $_REQUEST['teacher_name']) {
            $teacher_names = M('teacher')->field('id')->where(array('name'=>array('like','%'.$teacher_name.'%')))->select();
            foreach($teacher_names as $teacher_names_one) {
                $ids[]    =     $teacher_names_one['id'];
            }
            $ids = implode(',', $ids);
            if ($ids) {
                $where['teacher_id']    =   array('in', $ids);
            }
        }

		$page = $this->page($count, 20);
		$ptotorder_data = $this->ptotorder_model
            ->where($where)
			->limit($page->firstRow , $page->listRows)
			->order("add_time DESC")
			->select();

		$teacher_ids = array();
		foreach($ptotorder_data as $ptotorder_one) {
			$teacher_ids[] = $ptotorder_one['teacher_id'];
		}
		$teacher_ids = implode(',', $teacher_ids);

		$teacher_data = M('teacher')->field('id,name')->where(array('id'=>array('in',$teacher_ids)))->select();

		$teacher_swap = array();
		foreach($teacher_data as $teacher_one) {
			$teacher_swap[$teacher_one['id']]	=	$teacher_one['name'];
		}

		// 年级
		$grade = sp_get_grade_name();

		// 得到辅导课程
		$counseling = sp_get_counseling();
		$this->assign('teacher', $teacher_swap);
		$this->assign('grade', $grade);
		$this->assign('status', $this->status);
		$this->assign('counseling', $counseling);
		$this->assign("page", $page->show('Admin'));
		$this->assign('ptotorder_data', $ptotorder_data);

        $this->assign("formget",array_merge($_GET,$_POST));
		$this->display();
	}

	// 删除页面
	public function delete(){
		if(isset($_POST['ids'])){
			$ids = array_map("intval", $_POST['ids']);
			$ids = implode(',', $ids);
			if ($this->ptotorder_model->where(array("id"=>array("in", $ids)))->delete()) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}else{
			if(isset($_GET['id'])){
				$id = I("get.id",0,'intval');
				if ($this->ptotorder_model->delete($id)) {
					$this->success("删除成功！");
				} else {
					$this->error("删除失败！");
				}
			}
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
		$where['grade_id'] =   $is_status;

		$res = $this->ptotorder_model->save($where);

		if ($res) {
			$this->success();
		} else {
			$this->error();
		}
	}
}