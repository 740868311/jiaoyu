<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class CustomerPhoneController extends AdminbaseController
{
    public function index()
    {
        $where = array('option_name'=>'customer_phone');
        $option = M('Options')->where($where)->find();

        if($option){
            $this->assign('value', $option['option_value']);
        }
        $this->display();
    }

    public function index_post()
    {
        if(IS_POST){
            $data=I('post.');
            if ($data['value']) {
                $where = array('option_name'=>'customer_phone');
                $option_data['option_value'] = $data['value'];
                $result =  M('Options')->where($where)->save($option_data);

                if ($result!==false) {
                    $this->success("保存成功！");
                } else {
                    $this->error("保存失败！");
                }
            }else {
                return false;
            }
        } else {
            return false;
        }
    }
}