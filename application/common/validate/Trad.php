<?php
namespace app\common\validate;

use think\Validate;

/**
 * 交易
 */
class Trad extends Validate {
	protected $rule = [
        'us_tel'    => 'require|mobile',
        'tr_num'    => 'require|number|gt:0',
        'convert_num'    => 'require|number|gt:0',
		'sode'    => 'require',

		'tx_type' => 'require',
		'issue_num' => 'require|integer|egt:10',
		'us_safe_pwd' => 'require',
	];
	protected $field = [
        'issue_num' => '金额',
        'us_safe_pwd' => '交易密码',

	];
	protected $message = [
		
	];
	protected $scene = [
		'buy' => ['issue_num','us_safe_pwd'],       //发布买入

		'convert' => ['us_tel','convert_num', 'sode'], //转换
		'bu' => ['tx_type','tx_num','us_safe_pwd'], //提现
	];

}
