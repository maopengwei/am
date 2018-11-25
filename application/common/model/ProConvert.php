<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 *
 */
class ProConvert extends Model {
	use SoftDelete;
	protected $deleteTime = 'delete_time';

	public function user() {
		return $this->hasOne('User', 'id', 'us_id');
	}

	//查询
	public function chaxun($map, $order, $size, $field = "*") {
		$list = $this->with('user')->where($map)->order($order)->field($field)->paginate($size, false, [
			'query' => request()->param()]);
		return $list;
	}
	/**
	 * 添加
	 * @param  [array] $data [description]
	 * @return [bool]       [description]
	 */
	public function tianjia($id,$num) {
		
		$arr = [
			'us_id'       =>  $id,
			'convert_num' 	  =>  $num,
			'convert_add_time' =>  date('Y-m-d H:i:s'),
		];

		$rel = $this->insertGetId($arr);

		if ($rel) {
			User::usWalChange($id,$num,6);
			User::usMscChange($id,$num,6);
			return [
				'code' => 1,
				'msg' => '转换成功',
			];
		} else {
			return [
				'code' => 0,
				'msg' => '转换失败',
			];
		}
	}
	//转账账号
	public function getUsTextAttr($value, $data) {
		if ($data['us_id'] == "") {
			return '';
		}
		$name = model('User')->where('id', $data['us_id'])->value('us_account');
		return $name;
	}
	//转账姓名
	public function getUsNameAttr($value, $data) {
		if ($data['us_id'] == "") {
			return '';
		}
		$name = model('User')->where('id', $data['us_id'])->value('us_real_name');
		return $name;
	}
	//转入账号
	public function getUsToTextAttr($value, $data) {
		if ($data['us_to_id'] == "") {
			return '';
		}
		$name = model('User')->where('id', $data['us_to_id'])->value('us_account');
		return $name;
	}
	//转入姓名
	public function getUsToNameAttr($value, $data) {
		if ($data['us_to_id'] == "") {
			return '';
		}
		$name = model('User')->where('id', $data['us_to_id'])->value('us_real_name');
		return $name;
	}
}
