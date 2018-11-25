<?php
namespace app\common\validate;

use think\Validate;

/**
 * 添加管理员验证器
 */
class Other extends Validate {
	protected $rule = [
		'shuff_pic' => 'require',
		'shuff_type' => 'require',
		'shuff_name' => 'require',
		'us_tel' => 'require',
		'us_pwd' => 'require',
		'sode' => 'require',
	];
	protected $field = [
		'shuff_pic' => '图片',
		'shuff_type' => '展示端',
		'shuff_name' => '图片名称',
	];
	protected $message = [
		'us_tel.regex' => '请填写正确的手机号',
	];
	protected $scene = [
		'addshuff' => ['shuff_name','shuff_pic','shuff_type'], //添加轮播图
		'editUser' => ['us_real_name', 'us_tel'], //修改用户
		'forgetUser' => ['us_tel', 'us_pwd'], //忘记密码
	];

}
