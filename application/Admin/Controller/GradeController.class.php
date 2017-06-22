<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class GradeController extends AdminbaseController
{
	public function index()
	{
		$grade = M('grade')->select();
		$this->assign('grade', $grade);
		$this->display();
	}

	public function add()
	{
		$this->display();
	}

	public function add_post()
	{
		if (IS_POST) {
			if (M('grade')->create()!==false) {
				if (M('grade')->add()!==false) {
					F('all_terms',null);
					$this->success("添加成功！",U("grade/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error(M('grade')->getError());
			}
		}
	}

	public function del()
	{
		$id = I("get.id",0,'intval');

		if (M('grade')->delete($id)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}

	public function edit()
	{
		$id = I("get.id",0,'intval');
		if (!$id) {
			$this->error("缺少id！");
		}
		$grade = M('grade')->where(array('id'=>$id))->find();
		$this->assign('grade', $grade);
		$this->display();
	}

	public function edit_post()
	{
		if (IS_POST) {
			$id = I("post.id",0,'intval');
			$grade_name = I("post.grade_name");

			if (!$id) {
				$this->error('缺少id！');
			}

			$data['id']         =   $id;
			$data['grade_name']  =   $grade_name;

			if (M('grade')->save($data)) {
				$this->success("修改成功！",U("grade/index"));
			} else {
				$this->error("修改失败！");
			}
		}
	}
}