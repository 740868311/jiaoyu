<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class PhoneblackController extends AdminbaseController
{
    public function index()
    {
        $Phoneblack = M('PhoneBlack')->select();
        $this->assign('Phoneblack', $Phoneblack);
        $this->display();
    }

    public function add()
    {
        $this->display();
    }

    public function add_post()
    {
        if (IS_POST) {
            if (M('PhoneBlack')->create()!==false) {
                if (M('PhoneBlack')->add()!==false) {
                    F('all_terms',null);
                    $this->success("添加成功！",U("Phoneblack/index"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error(M('PhoneBlack')->getError());
            }
        }
    }

    public function del()
    {
        $id = I("get.id",0,'intval');

        if (M('PhoneBlack')->delete($id)!==false) {
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
        $Phoneblack = M('PhoneBlack')->where(array('id'=>$id))->find();
        $this->assign('Phoneblack', $Phoneblack);
        $this->display();
    }

    public function edit_post()
    {
        if (IS_POST) {
            $id = I("post.id",0,'intval');
            $Phoneblack_name = I("post.phone");

            if (!$id) {
                $this->error('缺少id！');
            }

            $data['id']         =   $id;
            $data['phone']  =   $Phoneblack_name;

            if (M('PhoneBlack')->save($data)) {
                $this->success("修改成功！",U("Phoneblack/index"));
            } else {
                $this->error("修改失败！");
            }
        }
    }
}