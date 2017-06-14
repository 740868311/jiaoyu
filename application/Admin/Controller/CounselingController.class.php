<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class CounselingController extends AdminbaseController
{
    public function index()
    {
        $counseling = M('counseling')->select();
        $this->assign('counseling', $counseling);
        $this->display();
    }

    public function add()
    {
        $this->display();
    }

    public function add_post()
    {
        if (IS_POST) {
            if (M('counseling')->create()!==false) {
                if (M('counseling')->add()!==false) {
                    F('all_terms',null);
                    $this->success("添加成功！",U("counseling/index"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error(M('counseling')->getError());
            }
        }
    }

    public function del()
    {
        $id = I("get.id",0,'intval');

        if (M('counseling')->delete($id)!==false) {
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
        $counseling = M('counseling')->where(array('id'=>$id))->find();
        $this->assign('counseling', $counseling);
        $this->display();
    }

    public function edit_post()
    {
        if (IS_POST) {
            $id = I("post.id",0,'intval');
            $counseling = I("post.counseling");

            if (!$id) {
                $this->error('缺少id！');
            }

            $data['id']         =   $id;
            $data['counseling']  =   $counseling;

            if (M('counseling')->save($data)) {
                $this->success("修改成功！",U("counseling/index"));
            } else {
                $this->error("修改失败！");
            }
        }
    }
}