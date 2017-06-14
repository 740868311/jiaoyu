<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class CodeController extends AdminbaseController
{
    public function index()
    {
        $where = array('option_name'=>'code_manner');
        $option = M('Options')->where($where)->find();

        if($option){
            $options = json_decode($option['option_value'], true);
            $status = array('parents'=>'家长','doctor'=>'教员');
            $this->assign('status', $status);
            $this->assign('options', $options);
//            $this->assign('value', $options['value']);
        }
        $this->display();
    }

    public function index_post()
    {
        if(IS_POST){
            $data=I('post.');
            if (($data['parents'] == 1 || $data['parents'] == 2) && ($data['doctor'] == 1 || $data['doctor'] == 2)) {
                $where = array('option_name'=>'code_manner');
                $option = M('Options')->where($where)->find();
                $options = json_decode($option['option_value'], true);
                $options['parents']['value']    =   $data['parents'];
                $options['doctor']['value']     =   $data['doctor'];

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