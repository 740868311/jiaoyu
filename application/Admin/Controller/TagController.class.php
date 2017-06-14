<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class TagController extends AdminbaseController
{
	private $subscription;

	function _initialize() {
		parent::_initialize();
		$this->subscription = M('subscription_tag');
	}

	public function index()
	{
		$this->assign('tag', $this->subscription->select());
		$this->display();
	}

	public function add()
	{
		$this->display();
	}

	public function add_post()
	{
		if (IS_POST) {
			if ($this->subscription->create()!==false) {
				if ($this->subscription->add()!==false) {
					F('all_terms',null);
					$this->success("添加成功！",U("tag/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->subscription->getError());
			}
		}
	}

	public function del()
	{
		$id = I("get.id",0,'intval');

		if ($this->subscription->delete($id)!==false) {
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
		$tag = $this->subscription->where(array('id'=>$id))->find();
		$this->assign('tag', $tag);
		$this->display();
	}

	public function edit_post()
	{
		if (IS_POST) {
			$id = I("post.id",0,'intval');
			$tag = I("post.tag");

			if (!$id) {
				$this->error('缺少id！');
			}

			$data['id']         =   $id;
			$data['tag']  =   $tag;

			if ($this->subscription->save($data)) {
				$this->success("修改成功！",U("tag/index"));
			} else {
				$this->error("修改失败！");
			}
		}
	}
}