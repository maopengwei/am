<?php
namespace app\common\model;

use think\Model;

/**
 *产品
 */
class StoKu extends Model {

	//关联用户表
	public function user() {
		return $this->hasOne('User', 'id', 'us_id');
	}
	public function prod() {
		return $this->hasOne('StoProd', 'id', 'prod_id');
	}
	//详情
	public function detail($where, $field = "*") {
		return $this->with('user,prod')->where($where)->field($field)->find();
	}
	//查询
	public function chaxun($map, $order, $size, $field = "*") {
		$list = $this->with('user,prod')->where($map)->order($order)->field($field)->paginate($size, false, [
			'query' => request()->param()]);
		return $list;
	}

	/**
	 * 添加
	 * @param  [array] $data [description]
	 * @return [bool]       [description]
	 */
	public function tianjia($data) {
		$rel = $this->insertGetid($data);
		return ['code' => 1,'msg' => '添加成功'];
	}


	//状态
	// public function getStatusTextAttr($value, $data) {
	// 	$array = [
	// 		0 => '未上线',
	// 		1 => '使用中',
	// 	];
	// 	return $array[$data['cate_status']];
	// }

}
