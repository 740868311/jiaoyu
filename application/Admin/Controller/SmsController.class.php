<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class SmsController extends AdminbaseController
{
    public function index()
    {

        $where = array('option_name'=>'sms');
        $option = M('Options')->where($where)->find();

        if($option){
            $options = json_decode($option['option_value'], true);
            $this->assign('options', $options['option']);
            $this->assign('value', $options['value']);
            $this->assign('message', $options['message']);
        }
        $this->display();
    }

    public function index_post()
    {
        if(IS_POST){
            $data=I('post.');

            if ($data['option'] == 1 || $data['option'] == 2) {
                $where = array('option_name'=>'sms');
                $option = M('Options')->where($where)->find();
                $options = json_decode($option['option_value'], true);
                $options['value']    =   $data['option'];
                $options['message']  =   $data['message'];

                $option_data['option_value'] = json_encode($options);
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