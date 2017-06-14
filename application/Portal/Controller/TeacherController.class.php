<?php
/**
 *  前台老师相关页面
 *
 *
 */
namespace Portal\Controller;
use Common\Controller\HomebaseController;
class TeacherController extends HomebaseController {

    private $is_code;
    function _initialize() {
        parent::_initialize();

        $this->teacher_model =M("teacher");

        // 得到当前是否开启短信验证
        $where = array('option_name'=>'code_manner');
        $option = M('Options')->field('option_value')->where($where)->find();
        $option = json_decode($option['option_value'], true);

        // 得到老师用什么验证码：1图形验证码  2短信验证码
        $this->is_code = $option['doctor']['value'];

        $this->assign('is_code', $this->is_code);
    }

	// 当老师页面
    public function index()
    {
        $this->display();
    }

    public function index_post()
    {
        if (IS_POST) {
            $post = I("post.");

            // 暂时注销掉，等页面都有了，在测试

//            if ($this->is_code == 1) {
//                // 检测图形验证码
//                if(!sp_check_verify_code()){
//                    $array  =   array('info'=>'验证码出错，请重新输入', 'status'=>0);
//                    echo json_encode($array);die;
//                }
//            } else {
//                // 检测短信验证码
//                if (!sp_check_sms_code()) {
//                    $array  =   array('info'=>'短信验证码过期或者出错，请重新输入', 'status'=>0);
//                    echo json_encode($array);die;
//                }
//            }

            $data = $this->get_data($post);

            $data['ip']             =   $_SERVER['REMOTE_ADDR'];
            $data['add_time']       =   date('Y-m-d H:i:s', time());
            $data['status']         =   1;
            $data['is_black']       =   1;

            if (empty($post['password'])) {
                $array = array('info'=>'密码不能为空','status'=>0);
//            echo json_encode($array);die;
                dump($array);die;
            } else if (!preg_match('/^[a-zA-Z\d_]{6,}$/', $post['password'])) {
                $array = array('info'=>'密码包含英文数字下划线，并且长度6位以上','status'=>0);
//            echo json_encode($array);die;
                dump($array);die;
            }
            $data['password']       =   md5('ak47'.$post['password']);

            $res = $this->teacher_model->add($data);
        }
    }

	// 处理数据
    private function get_data($post)
    {
        $data['name']   =   htmlspecialchars($post['name']);
        $data['sex']    =   (int)$post['sex'];

        // 验证手机号
        $phone_auth       =   '/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/';
        $phone      =   $post['phone'];
        if ($phone) {
            if (!preg_match($phone_auth, $phone)) {
                $array = array('info'=>'手机格式有误','status'=>0);
//                echo json_encode($array);die;
                dump($array);die;
            } else {
                $sign_where = array('phone' =>  $phone);
                $res = $this->teacher_model->where($sign_where)->find();
                if ($res) {
                    $array = array('info'=>'手机号已经注册','status'=>0);
//                    echo json_encode($array);die;
                    dump($array);die;
                }
                $data['phone']  =   $phone;
            }
        } else {
            $array = array('info'=>'手机号不能为空','status'=>0);
//            echo json_encode($array);die;
            dump($array);die;
        }


        $email_auth =   '/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])*(\.([a-z0-9])([-a-z0-9_-])([a-z0-9])+)*$/i';
        $email      =   $post['email'];
        if ($email) {
            if (!preg_match($email_auth, $email)) {
                $array = array('info'=>'邮箱格式有误','status'=>0);
//                echo json_encode($array);die;
                dump($array);die;
            } else {
                $data['email']  =   $email;
            }
        }

        $data['university'] =   $post['university'] ? htmlspecialchars($post['university']) : '';
        $data['profession'] =   $post['profession'] ? htmlspecialchars($post['profession']) : '';
        $data['grade']      =   $post['grade'] ? htmlspecialchars($post['grade']) : '';
        $data['stage']      =   (int)($post['stage']);

        $counseling_ids = ',';
        foreach($post['counseling_id'] as $counseling_one) {
            if ((int)$counseling_one && strpos($counseling_ids, ','.(int)$counseling_one.',') === false) {
                $counseling_ids .= (int)$counseling_one.',';
            }
        }

        if (!empty($counseling_ids)) {
            $data['counseling_ids']     =   trim($counseling_ids, ',');
        } else {
            $array = array('info'=>'请输入辅导课程','status'=>0);
//            echo json_encode($array);die;
            dump($array);die;
        }

        if (!empty($post['counseling_other'])) {
            $data['counseling_other']   =   htmlspecialchars($post['counseling_other']);
        }

        $data['introduction']           =   htmlspecialchars($post['introduction']);
        $data['teaching_experience']    =   htmlspecialchars($post['teaching_experience']);

        if(!empty($post['photos_alt']) && !empty($post['photos_url'])){
            foreach ($post['photos_url'] as $key=>$url){
                $photourl=sp_asset_relative_url($url);
                $post['smeta']['photo'][]=array("url"=>$photourl,"alt"=>$post['photos_alt'][$key]);
            }
        }

        $post['smeta']['thumb'] = sp_asset_relative_url($post['smeta']['thumb']);

        $data['smeta']          =   json_encode($post['smeta']);
        $identity       =   (int)$post['identity'];
        if ($identity) {
            $data['identity']   =   $identity;
        } else {
            $array = array('info'=>'请输入老师身份','status'=>0);
//            echo json_encode($array);die;
            dump($array);die;
        }



        $data['remarks']        =   htmlspecialchars($post['remarks']);


        return $data;
    }

    // 老师登录页
    public function teacher_login()
    {
        $this->display();
    }

    // 老师登录
    public function teacher_dologin()
    {
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
    }

    // 老师简历
    public function resume()
    {
        $id = get_teacher_id($_GET['id']);

        if (!$id) {
            $this->error('缺少教师id');
        }

        $data = $this->teacher_model->where(array('id'=>$id))->find();

        $this->assign('data', $data);
        $this->display();
    }
}