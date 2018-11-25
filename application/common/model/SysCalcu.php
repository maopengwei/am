<?php
namespace app\common\model;
use Cache;
use think\Model;

/**
 * 分类表
 */
class SysCalcu extends Model {

	//详情
	public function detail($where, $field = "*") {
		return $this->where($where)->field($field)->find();
	}
	//查询
	public function chaxun($map = [], $order = '', $size, $field = "*") {
		return $this->where($map)->order($order)->field($field)->paginate($size, false, [
			'query' => request()->param()]);
	}
	/**
	 * 修改
	 * @param  [array] $data  [数据]
	 * @param  [array] $where [条件]
	 * @return [bool]
	 */
	public function xiugai($data) {
		$rel = $this->update($data);
		Cache::clear();
		return [
			'code' => 1,
			'msg' => '修改成功',
		];
	}

}
