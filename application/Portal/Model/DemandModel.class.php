<?php
namespace Portal\Model;

use Common\Model\CommonModel;

class DemandModel extends CommonModel {

    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('name', 'require', '姓名不能为空！', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('phone', 'require', '手机号不能为空'),
        array('grade_id', 'require', '年级不能为空'),
        array('sex', 'require', '性别不能为空'),
        array('counseling_ids', 'require', '辅导课程不能为空'),
        array('prepayments', 'require', '拟付课酬不能为空'),
        array('teacher_identity', 'require', '老师身份不能为空'),
        array('address', 'require', '家庭住址不能为空'),
        array('tatus', 'require', '需求状态不能为空'),
//        array('teacher_sex', 'require', '老师性别不能为空'),
        array('phone','/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/','请输入正确的手机号',self::EXISTS_VALIDATE),
    );

    protected $_auto = array (
        array('post_date', 'mGetDate', self::MODEL_INSERT, 'callback' ),
        array('post_modified', 'mGetDate',self::MODEL_BOTH, 'callback' )
    );

    // 获取当前时间
    public function mGetDate() {
        return date( 'Y-m-d H:i:s' );
    }

    protected function _before_write(&$data) {
        parent::_before_write($data);
    }
}