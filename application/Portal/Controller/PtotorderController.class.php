<?php
/**
 *  添加家长预约老师
 *
 */
namespace Portal\Controller;
use Common\Controller\HomebaseController;
class PtotorderController extends HomebaseController {

    protected $ptotorder_model;
    private $is_code;
    function _initialize() {
        parent::_initialize();
        $this->ptotorder_model =M("ptotorder");


        // 得到当前是否开启短信验证
        $where = array('option_name'=>'code_manner');
        $option = M('Options')->field('option_value')->where($where)->find();
        $option = json_decode($option['option_value'], true);

        // 得到家长用什么验证码：1图形验证码  2短信验证码
        $this->is_code = $option['parents']['value'];
    }
    // 添加
    public function add_post()
    {
        if (IS_POST) {
            $post = I("post.");

            if ($this->is_code == 1) {
                // 检测图形验证码
                if(!sp_check_verify_code()){
                    $array  =   array('info'=>'验证码出错，请重新输入', 'status'=>0);
                    echo json_encode($array);die;
                }
            } else {
                // 检测短信验证码
                if (!sp_check_sms_code()) {
                    $array  =   array('info'=>'短信验证码过期或者出错，请重新输入', 'status'=>0);
                    echo json_encode($array);die;
                }
            }


            $data = $this->get_data($post);

            $data['add_time']       =   date('Y-m-d H:i:s', time());

            $res = $this->ptotorder_model->add($data);
            if ($res) {

                $where = array('option_name'=>'email_warn');
                $option = M('Options')->where($where)->find();
                if($option){
                    $options = json_decode($option['option_value'], true);
                    // 如果是1则想指定邮箱发送提示邮件
                    if ($options['value'] == 1) {
                        $grade = sp_get_grade_name();
                        $area = sp_get_area();

                        $message = '有新的需求<br>姓名：'.$data['name']."<br>电话：".$data['phone'].'<br>地址：'.$area[$data['area_id']].' '.$data['address'];
//                        sp_send_email($options['options']['to'], $options['options']['title'], $options['options']['template']);
                        sp_send_email($options['options']['to'], $options['options']['title'], $message);
                    }
                }

                $array  =   array('info'=>'添加成功', 'status'=>1);
                echo json_encode($array);die;
            } else {
                $array  =   array('info'=>'添加失败', 'status'=>0);
                echo json_encode($array);die;
            }
        }
    }

    // 处理数据
    private function get_data($post)
    {
        if (!$post['name']) {
            $array = array('info'=>'请填写姓名','status'=>0);
            echo json_encode($array);die;
        }
        $data['name']   =   htmlspecialchars($post['name']);

        $teacher_id     =   (int)$post['teacher_id'];
        if (!$teacher_id) {
            $array = array('info'=>'请刷新后重新尝试','status'=>0);
            echo json_encode($array);die;
        }
        $data['teacher_id'] =$teacher_id;

        // 验证手机号
        $phone_auth       =   '/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/';
        $phone      =   $post['phone'];
        if ($phone) {
            if (!preg_match($phone_auth, $phone)) {
                $array = array('info'=>'手机格式有误','status'=>0);
                echo json_encode($array);die;
            } else {

                // 先注销掉，稍后加上黑名单功能，黑名单中的不允许添加
//                $sign_where = array('phone' =>  $phone);
//                $res = $this->teacher_model->where($sign_where)->find();
//                if ($res) {
//                    $array = array('info'=>'手机号已经注册','status'=>0);
//                    echo json_encode($array);die;
//                }
                $data['phone']  =   $phone;
            }
        } else {
            $array = array('info'=>'手机号不能为空','status'=>0);
            echo json_encode($array);die;
        }

        $counseling_ids = ',';
        foreach($post['counseling_id'] as $counseling_one) {
            if ((int)$counseling_one && strpos($counseling_ids, ','.(int)$counseling_one.',') === false) {
                $counseling_ids .= (int)$counseling_one.',';
            }
        }

        if (!empty($counseling_ids)) {
            $data['counseling_ids']     =   ','.trim($counseling_ids, ',').',';
        } else {
            $array = array('info'=>'请输入辅导课程','status'=>0);
            echo json_encode($array);die;
        }

        if (!$post['area_id']) {
            $array = array('info'=>'请添加地址','status'=>0);
            echo json_encode($array);die;
        }
        $data['area_id']    =   $post['area_id'];

        if (!$post['address']) {
            $array = array('info'=>'请添加地址','status'=>0);
            echo json_encode($array);die;
        }
        $data['address']    =   htmlspecialchars($post['address']);

        if (!empty($post['counseling_other'])) {
            $data['counseling_other']   =   htmlspecialchars($post['counseling_other']);
        }
        return $data;
    }
}