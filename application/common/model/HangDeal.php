<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 奖励Doken
 */
class HangDeal extends Model {
	use SoftDelete;
	protected $deleteTime = 'delete_time';



	public function seller() {
		return $this->hasOne('User', 'id', 'us_id');
	}
	public function buyer() {
		return $this->hasOne('User', 'id', 'us_to_id');
	}
	//详情
	public function detail($where, $field = "*") {
		return $this->with('seller,buyer')->where($where)->field($field)->find();
	}
	//查询
	public function chaxun($map, $order, $size, $field = "*") {
		$list = $this->with('seller')->where($map)->order($order)->field($field)->paginate($size, false, [
			'query' => request()->param()]);
		return $list;
	}
	/**
	 * 添加
	 * @param  [array] $data [description]
	 * @return [bool]       [description]
	 */
	public function tianjia($data) {
		$data['deal_add_time'] = date('Y-m-d H:i:s');
		$rel = $this->insertGetId($data);
		return $rel;
	}
	/**
	 * 修改
	 * @param  [array] $data  [数据]
	 * @param  [array] $where [条件]
	 * @return [bool]
	 */
	public function xiugai($data, $where) {
		return $this->save($data, $where);
	}
	public function getStatusTextAttr($value,$data){
		$arr = [
			0 => '未付款',
			1 => '未收款',
			2 => '已完成',
		];
		return $arr[$data['deal_status']];
	}

	// //用户账号
	// public function getUsTextAttr($value, $data) {
	// 	if ($data['us_id'] == "") {
	// 		return '';
	// 	}
	// 	$name = model('User')->where('id', $data['us_id'])->value('us_account');
	// 	return $name;
	// }
	// //真实姓名
	// public function getUsNameAttr($value, $data) {
	// 	if ($data['us_id'] == "") {
	// 		return '';
	// 	}
	// 	$name = model('User')->where('id', $data['us_id'])->value('us_real_name');
	// 	return $name;
	// }
}
