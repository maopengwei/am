<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 购物车
 */
class StoCart extends Model {
	use SoftDelete;
	protected $deleteTime = 'delete_time';


	// //关联产品
	public function prod() {
		return $this->hasOne('StoProd', 'id', 'prod_id');
	}

	//详情
	public function detail($where, $field = "*") {
		return $this->with('prod')->where($where)->field($field)->find();
	}
	//查询
	public function chaxun($map, $order, $size, $field = "*") {
		$list = $this->with('prod')->where($map)->order($order)->field($field)->paginate($size, false, [
			'query' => request()->param()]);
		return $list;
	}
	
	/**
	 * 添加
	 * @param  [array] $data [description]
	 * @return [bool]       [description]
	 */
	public function tianjia($d) {
		$d['cart_add_time'] = date('Y-m-d H:i:s');
		$rel = $this->insertGetid($d);
		return ['code' => 1,'msg' => '添加成功'];
	}
}
