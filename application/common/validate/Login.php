<?php
namespace app\common\validate;

use think\Validate;

/**
 * 添加管理员验证器
 */
class Login extends Validate {
	protected $rule = [
		'referee' => 'require',
		'username' => 'require',
		'userpassword' => 'require|alphaNum|max:20|min:6',
		'us_safe_pwd' => 'require|alphaNum|max:20|min:6',
		'mobile' => 'require|regex:/^[1][34578][0-9]{9}$/',
		'us_account' => 'require|alphaNum',
		'us_pwd' => 'require|alphaNum|max:20|min:6',
		'pwd' => 'require|alphaNum|max:20|min:6|confirm:us_pwd',
		'us_tel' => 'require|regex:/^[1][34578][0-9]{9}$/',
		'sode' => 'require|number',
		'code' => 'require|captcha',
		/*
		'p_acc' => 'require',
		'p_tel' => 'require|regex:/^[1][34578][0-9]{9}$/',
		'a_acc' => 'require',
		'a_tel' => 'require|regex:/^[1][34578][0-9]{9}$/',
		'us_aid' => 'require',
		'us_pid' => 'require',
		'us_account'   => 'require|alphaNum',
		'us_real_name' => 'require',
		'us_tel'	   => 'require|regex:/^[1][34578][0-9]{9}$/',
		'us_pwd' 	   => 'require',
		'us_safe_pwd'  => 'require',
		'us_qu' 	   => 'require',
		'us_type' 	   => 'require',
		'old_pwd' 	   => 'require',
		'sode' 		   => 'require',
		'us_addr_addr'    => 'require',
		'us_addr_tel'     => 'require',
		'us_addr_person'  => 'require',*/
	];
	protected $field = [
		'referee' => '父账号',
		'username' => '用户账号',
		'userpassword' => '用户密码',
		'mobile' => '手机号',
		'us_safe_pwd' => '交易密码',
		'us_account' => '账户名',
		'us_pwd' => '登录密码',
		'pwd' => '确认密码',
		'us_tel' => '手机号',
		'sode' => '短信验证码',
		'code' => '验证码',
	

		
	];
	protected $message = [
		'mobile.regex' => '请填写正确的手机号',
		// 'is_coin.require' => '请选择是否使用购物币',
		// 'is_reservation.require' => '请选择是否使用预定',
		// 'is_courier.require' => '请选择是否需要配送',
	];
	protected $scene = [
		'reg' => ['referee','username','userpassword', 'mobile','us_safe_pwd'], //添加用户
		'forget' => ['us_account','us_pwd','pwd', 'us_tel','sode'], //添加用户
		'login' => ['username','userpassword','code'], //添加用户
	];

}
