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

		$page = $this->page($count, 20);
		$ptotorder_data = $this->ptotorder_model
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
}