<?php
/*
 *      _______ _     _       _     _____ __  __ ______
 *     |__   __| |   (_)     | |   / ____|  \/  |  ____|
 *        | |  | |__  _ _ __ | | _| |    | \  / | |__
 *        | |  | '_ \| | '_ \| |/ / |    | |\/| |  __|
 *        | |  | | | | | | | |   <| |____| |  | | |
 *        |_|  |_| |_|_|_| |_|_|\_\\_____|_|  |_|_|
 */
/*
 *     _________  ___  ___  ___  ________   ___  __    ________  _____ ______   ________
 *    |\___   ___\\  \|\  \|\  \|\   ___  \|\  \|\  \ |\   ____\|\   _ \  _   \|\  _____\
 *    \|___ \  \_\ \  \\\  \ \  \ \  \\ \  \ \  \/  /|\ \  \___|\ \  \\\__\ \  \ \  \__/
 *         \ \  \ \ \   __  \ \  \ \  \\ \  \ \   ___  \ \  \    \ \  \\|__| \  \ \   __\
 *          \ \  \ \ \  \ \  \ \  \ \  \\ \  \ \  \\ \  \ \  \____\ \  \    \ \  \ \  \_|
 *           \ \__\ \ \__\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \__\    \ \__\ \__\
 *            \|__|  \|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|__|     \|__|\|__|
 */
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class IndexController extends HomebaseController {

	public function index() {
		$index_json = file_get_contents(SITE_PATH.'/index_json/index.json');
		$index_array = json_decode($index_json, true);
        // 幻灯片
        $where['slide_status']	=	1;

        $slide = D("Common/Slide")->field('slide_pic,slide_url,slide_cid')->where($where)->select();

        foreach($slide as $k=>$slide_one) {
            $swap[$slide_one['slide_cid']][$k]['slide_pic']	=	'/data/upload/'.$slide[$k]['slide_pic'];
        }


        $index_array['slide']	=	$swap;
        // 幻灯片
		$this->assign('index_array', $index_array);
//		dump($index_array);die;
    	$this->display(":index");
    }

	public function index_json() {
		$where['status']    =   2;
		$teacher_data = M("teacher")->where($where)->order('last_time desc')->limit('0,12')->select();
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


		// 最新文章

		$term_id    =   array('3','4','5','6','7','8');
		$term_name	=	array(
			3	=>	'最新公告',
			4	=>	'家长必读',
			5	=>	'老师必读',
			6	=>	'家教经验',
			7	=>	'教育资讯',
			8	=>	'学习经验'
		);

		$obj_id = array();
		$obj_data	=	array();
		$k 	=	0;
		foreach($term_id as $term_id_one) {
			$data = D("Portal/TermRelationships")->where(array('term_id'=>$term_id_one))->select();

			$obj_id =   array();
			foreach($data as $data_one) {

				$obj_id[]	=	$data_one['object_id'];
			}
			$obj_id	=	implode(',', $obj_id);


			if (!$obj_id) {
				continue;
			}

			$obj_array  =   array();
			$obj_data_array			=	D("Portal/Posts")->where(array('id'=>array('in', $obj_id),'post_status'=>array('NEQ',3)))->order('post_modified desc')->limit('0,10')->select();

			foreach($obj_data_array as $key=>$obj_data_array_one) {
				$obj_array[$key]['title']	=	$obj_data_array_one['post_title'];
				$obj_array[$key]['url']	=	leuu('article/index',array('id'=>7,'article_id'=>$obj_data_array_one['id'],'cid'=>$term_id_one));
			}
			$obj_data[$k]['content']	=	$obj_array;
			$obj_data[$k]['title']	=	$term_name[$term_id_one];
			$obj_data[$k]['url']	=	U('list/index', array('id'=>7,'term_id'=>$term_id_one));
			$k++;
		}
		$json_array['notices']	=	$obj_data;

		// 最新文章end


        // 最新需求
        $where = array();
        $where['status']	=	array('gt',1);
        $demand_data = D("demand")->where($where)->order('add_time desc')->limit('0,8')->select();

        // 年级
        $grade = sp_get_grade_name();

        // 性别
        $sex = array(
            1   =>'女',
            2   =>'男'
        );

        // 得到辅导课程
        $counseling = sp_get_counseling();
		$status = array(
			1=>'未审核',
			2=>'预约中',
			3=>'预约中',
			4=>'成功',
		);

        foreach($demand_data as $k=>$demand_one) {
            $counseling_data = explode(',', $demand_one['counseling_ids']);
            $swap = array();
            foreach($counseling_data as $counseling_one) {
                $swap[] = $counseling[$counseling_one];
            }
            $counseling_data = implode(',', $swap);
            $demand_data[$k]['counseling']  =   $counseling_data;
            $demand_data[$k]['grade_name']  =   $grade[$demand_one['grade_id']];
            $demand_data[$k]['sex']         =   $sex[$demand_one['sex']];
            $demand_data[$k]['status']      =   $status[$demand_one['status']];
            $demand_data[$k]['url']         =   U('Demand/demand_show', array('id'=>6, 'demand_id'=>$demand_one['id']));
        }

        $json_array['demand']	=	$demand_data;

        // 最新需求end
		$json_array 	=	json_encode($json_array);
//		$index_json = file_get_contents(SITE_PATH.'/index_json/index.json');
		echo $json_array;die;
	}

}


