<?php
namespace app\common\validate;

use think\Validate;

/**
 * 金额验证器
 */
class Profit extends Validate {
	protected $rule = [
        'us_tel'    => 'require|mobile',
        'tr_num'    => 'require|number|gt:0',
        'convert_num'    => 'require|number|gt:0',
		'sode'    => 'require',

		'suo_num' => 'require|integer|egt:100',
		'tx_type' => 'require',
		'tx_num' => 'require|integer|egt:10',
		'us_safe_pwd' => 'require',
	];
	protected $field = [
        'tx_type' => '提现到',
        'tx_num' => '提现金额',
        'suo_num' => '金额',
        'us_safe_pwd' => '交易密码',

	];
	protected $message = [
		
	];
	protected $scene = [
		// 'trans' => ['tr_account','us_tel','tr_num', 'sode'],       //转账
		// 'convert' => ['us_tel','convert_num', 'sode'], //转换
		'tx' => ['tx_type','tx_num','us_safe_pwd'], //提现
		'suo' => ['suo_num','us_safe_pwd'], //提现
	];

}
