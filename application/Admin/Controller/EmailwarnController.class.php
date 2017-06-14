<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class EmailwarnController extends AdminbaseController
{
	public function index()
	{
		$where = array('option_name'=>'email_warn');
		$option = M('Options')->where($where)->find();
		if($option){
			$options = json_decode($option['option_value'], true);
			$this->assign('options', $options);
		}
		$this->display();
	}

	public function index_post()
	{
		$data=array();

		$data['option_name'] = "email_warn";
        $value       =  (int)I('post.value');
		$options=I('post.options/a');

        if (!$options['title']) {
            $this->error("请输入不对！");
        }

		$options['template']=htmlspecialchars_decode($options['template']);
        $pattern = '/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])*(\.([a-z0-9])([-a-z0-9_-])([a-z0-9])+)*$/i';
        if (!preg_match($pattern, $options['to'])) {
            $this->error("邮箱格式不对！");
        }
        $email_data['value']    =   $value;
        $email_data['options']   =   $options;

		$data['option_value']= json_encode($email_data);

		$options_model= M('Options');
		if($options_model->where("option_name='email_warn'")->find()){
			$result = $options_model->where("option_name='email_warn'")->save($data);
		}else{
			$result = $options_model->add($data);
		}

		if ($result!==false) {
			$this->success("保存成功！");
		} else {
			$this->error("保存失败！");
		}
	}


}