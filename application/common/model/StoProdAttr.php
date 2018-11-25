<?php
namespace app\common\model;

use think\Model;

/**
 *产品
 */
class StoProdAttr extends Model {
	

	public function attr() {
		return $this->hasOne('StoAttr', 'id', 'attr_id');
	}
	public function attrp() {
		return $this->hasOne('StoAttr', 'id', 'attr_pid');
	}

	//详情
	public function detail($where, $field = "*") {
		return $this->with('attr,attrp')->where($where)->field($field)->find();
	}
	//查询
	public function chaxun($map, $order, $size, $field = "*") {
		$list = $this->with('attr,attrp')->where($map)->order($order)->field($field)->paginate($size, false, [
			'query' => request()->param()]);
		return $list;
	}
	/**
	 * 添加
	 * @param  [array] $data [description]
	 * @return [bool]       [description]
	 */
	public function tianjia($data) {
		$info = $this->where('attr_id',$data['attr_id'])->where('prod_id',$data['prod_id'])->find();
		if(count($info)){
			return ['code' => 0,'msg' => '已添加过该属性'];
		}
		$rel = $this->insertGetid($data);
		return ['code' => 1,'msg' => '添加成功'];
	}
	
}
