<?php
namespace app\common\validate;

use think\Validate;

/**
 * 商城验证器
 */
class Addr extends Validate {
    
	protected $rule = [
		'addr_name' 	=> 'require',
		'addr_tel' 		=> 'require|regex:/^[1][34578][0-9]{9}$/',
		'addr_province' => 'require',
		'addr_city' 	=> 'require',
		'addr_area' 	=> 'require',
		'addr_stree' 	=> 'require',
		
	];
	protected $field = [
		'addr_name' => '收货人',
		'addr_tel' => '收货人电话',
		'addr_province' => '省份',
		'addr_city' => '城市',
		'addr_area' => '县区',
		'addr_stree' => '街道信息',
	];
	protected $message = [
		
	];
	protected $scene = [
		'addr' => ['addr_name','addr_tel','addr_province','addr_city','addr_area','addr_stree'], //添加地址
	];

}
