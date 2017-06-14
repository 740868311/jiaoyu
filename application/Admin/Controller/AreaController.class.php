<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class AreaController extends AdminbaseController
{
    public function index()
    {
        $area = M('area')->select();
        $this->assign('area', $area);
        $this->display();
    }

    public function add()
    {
        $this->display();
    }

    public function add_post()
    {
        if (IS_POST) {
            if (M('area')->create()!==false) {
                if (M('area')->add()!==false) {
                    F('all_terms',null);
                    $this->success("添加成功！",U("area/index"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error(M('area')->getError());
            }
        }
    }

    public function del()
    {
        $id = I("get.id",0,'intval');

        if (M('area')->delete($id)!==false) {
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
        $area = M('area')->where(array('id'=>$id))->find();
        $this->assign('area', $area);
        $this->display();
    }

    public function edit_post()
    {
        if (IS_POST) {
            $id = I("post.id",0,'intval');
            $area_name = I("post.area_name");

            if (!$id) {
                $this->error('缺少id！');
            }

            $data['id']         =   $id;
            $data['area_name']  =   $area_name;

            if (M('area')->save($data)) {
                $this->success("修改成功！",U("area/index"));
            } else {
                $this->error("修改失败！");
            }
        }
    }
}