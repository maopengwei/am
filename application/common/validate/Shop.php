<?php
namespace app\common\validate;

use think\Validate;

/**
 * 商城验证器
 */
class Shop extends Validate {
	protected $rule = [
		'us_id'    => 'require',
		'mer_name' => 'require',
		'mer_pic'  => 'require',

		'mer_id'     => 'require',
		'prod_id'    => 'require',
		'prod_num'    => 'require',
		'us_safe_pwd'    => 'require',
		'prod_id'    => 'require',




		'prod_name'  => 'require',
		'prod_pic'   => 'require',
		'prod_price' => 'require',
		'cate_id' 	 => 'require',
		

		'pic0' 	 => 'require',
		'pic1' 	 => 'require',
		'pic2' 	 => 'require',
		'pic3' 	 => 'require',
		'jine' 	 => 'require|number|gt:0',



	];
	protected $field = [
		'us_id' => '用户',
		'mer_name' => '商铺名称',
		'mer_pic' => '商铺主图',

		'prod_id'    => '商品',
		'prod_num'    => '商品数量',
		'us_safe_pwd'    => '支付密码',
		'addr_id'    => '地址',

		'mer_id' => '商铺',
		'prod_name' => '产品名称',
		'prod_pic' => '主图',
		'prod_price' => '价格',
		'cate_id' => '分类',
		'arrid'  => '产品',

		'pic0' 	 => '身份证正面',
		'pic1' 	 => '身份证反面',
		'pic2' 	 => '营业执照',
		'pic3' 	 => '打款凭证',
		'jine' 	 => '金额',


	];
	protected $message = [
		
	];
	protected $scene = [

		'apply'  => ['pic0','pic1','pic2','pic3','jine'],
		'addmer' => ['us_id','mer_name', 'mer_pic'], //添加商店
		'addprod' => ['mer_id','prod_name', 'prod_pic', 'prod_price', 'cate_id'], //添加产品
		'editprod' => ['prod_name', 'prod_pic', 'prod_price'], //编辑产品
		'order' => ['us_safe_pwd','prod_id','prod_num','addr_id'],
		'cartorder' => ['us_safe_pwd','arrid','addr_id'], 
	];

}
