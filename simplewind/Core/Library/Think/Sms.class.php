<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Think;

class Sms {

	protected $ch;
	protected $apikey;

	public function __construct(){
		$this->apikey	=	'c88fb52f9fdd048e1df85a6ecda31845';


		$this->ch = curl_init();

		/* 设置验证方式 */
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));

		/* 设置返回结果为流 */
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);

		/* 设置超时时间*/
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);

		/* 设置通信方式 */
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
	}

	public function send_sms($type = 'sms', $phone, $message){
		$data=array('content'=>urlencode($message),'apikey'=>$this->apikey,'mobile'=>$phone);
		if ($type 	==	'yzm') {
			$json_data = $this->send_yzm($this->ch, $data);
		} else if($type ==	'voice') {
			return false;
		} else if($type	==	'notice') {
			$json_data = $this->send_tz($this->ch,$data);
		} else if($type	==	'yx') {
			return false;
		}

		echo $json_data;
		return true;
	}
	//获得账户
	public function get_user($ch,$apikey){
		curl_setopt ($ch, CURLOPT_URL, 'https://api.dingdongcloud.com/v1/sms/userinfo');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('apikey' => $apikey)));
		return curl_exec($ch);
	}

	//验证码
	public function send_yzm($ch,$data){
		curl_setopt ($ch, CURLOPT_URL, 'https://api.dingdongcloud.com/v1/sms/sendyzm');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return curl_exec($ch);
	}

	//语音验证码
	public function send_yyyzm($ch,$data){
		curl_setopt ($ch, CURLOPT_URL, 'https://api.dingdongcloud.com/v1/sms/sendyyyzm');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return curl_exec($ch);
	}

	//通知
	public function send_tz($ch,$data){
		curl_setopt ($ch, CURLOPT_URL, 'https://api.dingdongcloud.com/v1/sms/sendtz');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return curl_exec($ch);
	}

	//营销
	public function send_yx($ch,$data){
		curl_setopt ($ch, CURLOPT_URL, 'https://api.dingdongcloud.com/v1/sms/sendyx');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return curl_exec($ch);
	}
}
