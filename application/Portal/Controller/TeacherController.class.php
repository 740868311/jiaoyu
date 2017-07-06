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

        $d_code = $option['parents']['value'];

        $this->assign('d_code', $d_code);
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

            $data['ip']             =   $_SERVER['REMOTE_ADDR'];
            $data['add_time']       =   date('Y-m-d H:i:s', time());
            $data['last_time']      =   date('Y-m-d H:i:s', time());
            $data['status']         =   1;
            $data['is_black']       =   1;

            if (empty($post['password']) || empty($post['repassword'])) {
                $array = array('info'=>'密码或者重复密码不能为空','status'=>0);
                echo json_encode($array);die;
            } else if ($post['password']!= $post['repassword']){
                $array = array('info'=>'两次输入密码不一致','status'=>0);
                echo json_encode($array);die;
            } else if (!preg_match('/^[a-zA-Z\d_]{6,}$/', $post['password'])) {
                $array = array('info'=>'密码包含英文数字下划线，并且长度6位以上','status'=>0);
                echo json_encode($array);die;
            }
            $data['password']       =   md5('ak47'.$post['password']);

            $res = $this->teacher_model->add($data);
            if ($res) {
                $where_id['id'] =   $res;
                $user = $this->teacher_model->where($where_id)->find();
                session('user', $user);
                $this->add_json();
                $array  =   array('info'=>'添加成功', 'status'=>1);
                echo json_encode($array);die;
            } else {
                $array  =   array('info'=>'添加失败', 'status'=>0);
                echo json_encode($array);die;
            }
        } else {
            $array  =   array('info'=>'未知错误', 'status'=>0);
            echo json_encode($array);die;
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
        $data['sex']    =   (int)$post['sex'] ? (int)$post['sex'] : 1;

        // 验证手机号
        $phone_auth       =   '/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/';
        $phone      =   $post['phone'];
        if ($phone) {
            if (!preg_match($phone_auth, $phone)) {
                $array = array('info'=>'手机格式有误','status'=>0);
                echo json_encode($array);die;
            } else {
                $sign_where = array('phone' =>  $phone);
                $res = $this->teacher_model->where($sign_where)->find();
                if ($res) {
                    $array = array('info'=>'手机号已经注册','status'=>0);
                    echo json_encode($array);die;
                }
                $data['phone']  =   $phone;
            }
        } else {
            $array = array('info'=>'手机号不能为空','status'=>0);
            echo json_encode($array);die;
        }


        $email_auth =   '/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])*(\.([a-z0-9])([-a-z0-9_-])([a-z0-9])+)*$/i';
        $email      =   $post['email'];
        if ($email) {
            if (!preg_match($email_auth, $email)) {
                $array = array('info'=>'邮箱格式有误','status'=>0);
                echo json_encode($array);die;
            } else {
                $data['email']  =   $email;
            }
        }

        $data['university'] =   $post['university'] ? htmlspecialchars($post['university']) : '';
        $data['profession'] =   $post['profession'] ? htmlspecialchars($post['profession']) : '';
        $data['grade']      =   $post['grade'] ? htmlspecialchars($post['grade']) : '';

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

        if (!$post['thumb']) {
            $post['thumb']  =   'portal/default_tupian4.png';
        }
        $post['smeta']['thumb'] = sp_asset_relative_url($post['thumb']);

        $data['smeta']          =   json_encode($post['smeta']);
        $identity       =   (int)$post['identity'];
        if ($identity) {
            $data['identity']   =   $identity;
        } else {
            $array = array('info'=>'请输入老师身份','status'=>0);
            echo json_encode($array);die;
        }

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
        // 检测图形验证码
        if(!sp_check_verify_code()){
            $array  =   array('info'=>'验证码出错，请重新输入', 'status'=>0);
            echo json_encode($array);die;
        }

        if (IS_POST) {
            $post = I('post.');

            // 验证手机号
            $phone_auth       =   '/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/';
            $phone      =   $post['phone'];
            if ($phone) {
                if (!preg_match($phone_auth, $phone)) {
                    $array = array('info'=>'手机格式有误','status'=>0);
                    echo json_encode($array);die;
                }
            } else {
                $array = array('info'=>'手机号不能为空','status'=>0);
                echo json_encode($array);die;
            }

            $password   =   $post['password'];

            if (!$password) {
                $array = array('info'=>'密码不能为空','status'=>0);
                echo json_encode($array);die;
            }

            $sign_where = array('phone' =>  $phone, 'password'  =>  md5('ak47'.$password));
            $res = $this->teacher_model->where($sign_where)->find();


            if ($res) {
                if ($res['is_black']    ==  2) {
                    $array = array('info'=>'手机号已经拉入黑名单，请联系网站管理员','status'=>0);
                    echo json_encode($array);die;
                }
                // 修改老师的最后一次登录时间和当前ip
                $data['id'] =   $res['id'];
                $data['last_time']  =   date('Y-m-d H:i:s', time());
                $data['ip']         =   $_SERVER['REMOTE_ADDR'];
                $this->teacher_model->save($data);
                session('user',$res);
                $array = array('info'=>'登录成功','status'=>1);
                echo json_encode($array);die;
            } else {
                $array = array('info'=>'账号或者密码错误','status'=>0);
                echo json_encode($array);die;
            }
        } else {
            $array  =   array('info'=>'未知错误', 'status'=>0);
            echo json_encode($array);die;
        }

        // 条件
        $where  =   array();
        // is_black:1正常用户  2拉黑
        $where['is_black']  =   1;

    }

    public function teacher_index()
    {
        if (sp_is_user_login()) {

        }
    }

    // 前台ajax 判断用户登录状态接口
    function is_login(){

        if(sp_is_user_login()){
            $array  =   array('status'=>1);
            echo json_encode($array);die;
        }else{
            $array  =   array('status'=>0);
            echo json_encode($array);die;
        }
    }

    // 老师简历
    public function resume()
    {
        $id = $_GET['teacher_id'];

        if (!$id) {
            $this->error('缺少教师id');
        }
        $this->teacher_model->where(array('id'=>$id))->setInc('hits',1); // 老师的点击加1

        $data = $this->teacher_model->where(array('id'=>$id))->find();

        $where = array('option_name'=>'customer_phone');
        $option = M('Options')->where($where)->find();

        if($option){
            $this->assign('cphone', $option['option_value']);
        }

        $ttop_where['teacher_id']   =  $id;
        $ttop_where['status']       =  3;
        $ttop = M('ttoporder')->field('demand_id,add_time')->where($ttop_where)->select();

        if ($ttop) {
            foreach($ttop as $ttop_one) {
                $demand_ids[]               =   $ttop_one['demand_id'];
                $swap_ttop[$ttop_one['demand_id']] =   $ttop_one['add_time'];
            }

            $demand_ids =   implode(',', $demand_ids);
            $demand_data = M('demand')->field('id,counseling_ids,name')->where(array('id'=>array('in',$demand_ids)))->select();

            foreach($demand_data as $demand_one) {
                $swap_demand[$demand_one['id']]         =   $demand_one;
                $swap_demand[$demand_one['id']]['time'] =   $swap_ttop[$demand_one['id']];
            }
        }

        $this->assign('demand_data', $swap_demand);
        $this->assign("smeta",json_decode($data['smeta'],true));
        $this->assign('data', $data);
        $this->display();
    }

    // 教师库
    public function teacher_list()
    {
        $data  =   I('get.');
        $where  =   array();

        // ----条件开始----
        if (isset($data['counseling_id'])) {
            $counseling_id  =   (int)$data['counseling_id'];

            if ($counseling_id) {
                cookie('counseling_id', $counseling_id);
                $where['counseling_ids'] =   array('like','%,'.$counseling_id.',%');
            } else {
                cookie('counseling_id', null);
            }
        } else {
            if ((int)cookie('counseling_id')) {
                $where['counseling_ids']    =   array('like','%,'.(int)cookie('counseling_id').',%');
            }
        }


        if (isset($data['sex'])) {
            $sex           =    (int)$data['sex'];
            if ($sex) {
                cookie('sex', $sex);
                $where['sex'] =   $sex;
            } else {
                cookie('sex', null);
            }
        } else {
            if ((int)cookie('sex')) {
                $where['sex'] =   (int)cookie('sex');
            }
        }

        $where['is_black']  =   1;

        // ----条件结束----

        $count=$this->teacher_model->where($where)->count();

        $page = $this->page($count, 10);
        $teacher_data = $this->teacher_model
            ->where($where)
            ->limit($page->firstRow , $page->listRows)
            ->order("last_time DESC")
            ->select();

        foreach($teacher_data as $k=>$teacher_data_one) {
            $teacher_data[$k]['smeta']   =  json_decode($teacher_data_one['smeta'], true);
        }

        $this->assign('counseling_id', (int)cookie('counseling_id'));
        $this->assign('sex', (int)cookie('sex'));
        $this->assign("page", $page->show('Admin'));
        $this->assign("teacher_list", $teacher_data);
        $this->display();
    }

    // 明星教员
    public function star_teacher()
    {
        $data  =   I('get.');
        $where  =   array();

        // ----条件开始----
        if (isset($data['counseling_id'])) {
            $counseling_id  =   (int)$data['counseling_id'];

            if ($counseling_id) {
                cookie('counseling_id', $counseling_id);
                $where['counseling_ids'] =   array('like','%,'.$counseling_id.',%');
            } else {
                cookie('counseling_id', null);
            }
        } else {
            if ((int)cookie('counseling_id')) {
                $where['counseling_ids']    =   array('like','%,'.(int)cookie('counseling_id').',%');
            }
        }


        if (isset($data['sex'])) {
            $sex           =    (int)$data['sex'];
            if ($sex) {
                cookie('sex', $sex);
                $where['sex'] =   $sex;
            } else {
                cookie('sex', null);
            }
        } else {
            if ((int)cookie('sex')) {
                $where['sex'] =   (int)cookie('sex');
            }
        }

        $where['is_black']  =   1;
        $where['status']    =   2;


        // ----条件结束----

        $count=$this->teacher_model->where($where)->count();

        $page = $this->page($count, 10);
        $teacher_data = $this->teacher_model
            ->where($where)
            ->limit($page->firstRow , $page->listRows)
            ->order("last_time DESC")
            ->select();

        foreach($teacher_data as $k=>$teacher_data_one) {
            $teacher_data[$k]['smeta']   =  json_decode($teacher_data_one['smeta'], true);
        }



        $this->assign('counseling_id', (int)cookie('counseling_id'));
        $this->assign('status', (int)cookie('status'));
        $this->assign('sex', (int)cookie('sex'));
        $this->assign("page", $page->show('Admin'));
        $this->assign("teacher_list", $teacher_data);
        $this->display();
    }

    public function getpwd()
    {
        $this->display();
    }

    public function save_getpwd()
    {
        $data = I('post.');

        $phone              =   $data['phone'];
        $phone_auth         =   '/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/';
        if ($phone) {
            if (!preg_match($phone_auth, $phone)) {
                $array = array('info' => '手机格式有误', 'status' => 0);
                echo json_encode($array);
                die;
            }
        } else {
            $array = array('info' => '手机号不能为空', 'status' => 0);
            echo json_encode($array);
            die;
        }

        if ($phone != $_SESSION['email']['phone']) {
            $array = array('info' => '和刚才发送验证码的手机号不一致，请重新输入', 'status' => 0);
            echo json_encode($array);
            die;
        }

        $password   =   $data['password'];
        if (!$password) {
            $array = array('info' => '密码不能为空', 'status' => 0);
            echo json_encode($array);
            die;
        }

        $code   =   (int)$data['verify'];
        if (!$code) {
            $array = array('info' => '验证码不能为空', 'status' => 0);
            echo json_encode($array);
            die;
        }
        if ($code != $_SESSION['email']['code']) {
            $array = array('info' => '验证码输入错误，请重试输入', 'status' => 0);
            echo json_encode($array);
            die;
        }

        $where['phone']     =   $phone;
        $pas_data['password']  =   md5('ak47'.$password);

        $res = $this->teacher_model->where($where)->save($pas_data);

        if ($res) {
            $array = array('info' => '设置成功，请重新登录', 'status' => 1);
            echo json_encode($array);
            die;
        } else {
            $array = array('info' => '保存失败，请重试', 'status' => 0);
            echo json_encode($array);
            die;
        }
    }

    public function get_email()
    {
        $data = I('post.');

        $phone_auth       =   '/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/';
        $phone      =   $data['phone'];
        if ($phone) {
            if (!preg_match($phone_auth, $phone)) {
                $array = array('info' => '手机格式有误', 'status' => 0);
                echo json_encode($array);
                die;
            }
        } else {
            $array = array('info' => '手机号不能为空', 'status' => 0);
            echo json_encode($array);
        }

        $where['phone'] =   $phone;

        $teacher_data = $this->teacher_model->where($where)->find();
        if (!$teacher_data) {
            $array = array('info' => '未发现该手机号', 'status' => 0);
            echo json_encode($array);die;
        }
        $email = $teacher_data['email'];

        $code       =   rand(1000,9999);
        $message    =   '验证是：'.$code;

        $_SESSION['email']['code']	=	$code;
        $_SESSION['email']['phone']	=	$phone;

        sp_send_email($email, '忘记密码验证码', $message);
        $array = array('info' => '发送成功,请登录邮箱查看', 'status' => 1);
        echo json_encode($array);die;
    }

    public function add_json()
    {
        $where['status']    =   2;
        $teacher_data = $this->teacher_model->where($where)->order('last_time desc')->limit('0,12')->select();
        foreach($teacher_data as $k=>$teacher_one) {
            $thumb  =   $teacher_one['smeta'];
            $thumb  =   json_decode($thumb, true);
            $thumb  =   $thumb['thumb'];
            $teacher_data[$k]['thumb']      =   sp_get_image_preview_url($thumb);
            $teacher_data[$k]['url']        =   U('Teacher/resume', array('id'=>4, 'teacher_id'=>$teacher_one['id']));
        }

        $json_array	=	file_get_contents(SITE_PATH.'/index_json/index.json');

        if ($json_array) {
            $json_array	=	json_decode($json_array, true);
        }

        $json_array['teacher']	=	$teacher_data;
        $json_array 	=	json_encode($json_array);

        file_put_contents(SITE_PATH.'/index_json/index.json', $json_array);
    }
}