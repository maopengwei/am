<?php
namespace app\common\validate;

use think\Validate;

/**
 * 商城验证器
 */
class Baod extends Validate {
    
	protected $rule = [
		'us_safe_pwd'    => 'require',
		'prod_id' => 'require',
		'addr_id'  => 'require',
	];
	protected $field = [
		'us_safe_pwd' => '支付密码',
		'prod_id' => '报单产品',
		'addr_id' => '地址',
	];
	protected $message = [
		
	];
	protected $scene = [
		'baod' => ['us_safe_pwd','prod_id','addr_id'], 
	];

}
