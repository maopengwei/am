<?php
namespace app\common\validate;

use think\Validate;

/**
 * 添加管理员验证器
 */
class User extends Validate {
	protected $rule = [
		'p_acc' => 'require',
		'p_tel' => 'require|regex:/^[1][34578][0-9]{9}$/',
		'a_acc' => 'require',
		'a_tel' => 'require|regex:/^[1][34578][0-9]{9}$/',
		'us_aid' => 'require',
		'us_pid' => 'require',
		'us_account'   => 'require|alphaNum',
		'us_real_name' => 'require',
		'us_tel'	   => 'require|regex:/^[1][34578][0-9]{9}$/',
		'old_pwd' 	   => 'require',
		'us_pwd' => 'require|alphaNum|max:20|min:6',
		'us_safe_pwd' => 'require|alphaNum|max:20|min:6',
		'pwd'          => 'require|confirm:us_pwd',
		'sode' 		   => 'require',
		'us_addr_addr'    => 'require',
		'us_addr_tel'     => 'require',
		'us_addr_person'  => 'require',
	];
	protected $field = [
		'p_acc' => '推荐人',
		'p_tel' => '推荐人手机号',
		'a_acc' => '安置人账号',
		'a_tel' => '安置人手机号',
		'us_aid'       => '节点人',
		'us_pid'       => '推荐人',
		'us_account'   => '帐户名',
		'us_real_name' => '用户真实姓名',
		'us_tel'       => '手机号',

		'old_pwd'      => '原密码',
		'us_pwd'       => '新密码',
		'pwd'		   => '确认密码',

		'us_safe_pwd'  => '安全密码',
	
		'sode'		   => '短信验证码',
		'us_addr_addr'    => '收货地址',
		'us_addr_tel'     => '收货电话',
		'us_addr_person'  => '收货人',
	];
	protected $message = [
		'us_tel.regex' => '请填写正确的手机号',
		'is_coin.require' => '请选择是否使用购物币',
		'is_reservation.require' => '请选择是否使用预定',
		'is_courier.require' => '请选择是否需要配送',
	];
	protected $scene = [

		'pass' => ['old_pwd', 'us_pwd','pwd'], //登录密码
		'safe' => ['us_tel','sode', 'us_pwd','pwd'], //交易密码
		

		'addUser' => ['p_acc','a_acc','us_account','us_tel', 'us_pwd'], //添加用户
		'addr' => ['us_addr_addr','us_addr_tel','us_addr_person'],
		'editUser' => ['us_real_name', 'us_tel'], //修改用户
		
	];

}
