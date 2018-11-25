<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;
use think\Db;
/**
 * 奖励Doken
 */
class HangIssue extends Model {
	use SoftDelete;
	protected $deleteTime = 'delete_time';



	public function user() {
		return $this->hasOne('User', 'id', 'us_id');
	}
	//详情
	public function detail($where, $field = "*") {
		return $this->with('user')->where($where)->field($field)->find();
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
	public function tianjia($data) {
		$dd = date('Y-m-d');
		$data['issue_price'] = Db::name('sys_price')->where('time',$dd)->value('price');
		$data['issue_yuan'] = $data['issue_num']*$data['issue_price'];
		$data['issue_add_time'] = date('Y-m-d H:i:s');
		$rel = $this->insertGetId($data);
		if ($rel) {
			User::usWalChange($data['us_id'],$data['issue_num'],3);
		}
		return $rel;
	}
	/**
	 * 修改
	 * @param  [array] $data  [数据]
	 * @param  [array] $where [条件]
	 * @return [bool]
	 */
	// public function xiugai($data, $where) {
	// 	return $this->save($data, $where);
	// }

	//用户账号
	public function getUsTextAttr($value, $data) {
		if ($data['us_id'] == "") {
			return '';
		}
		$name = model('User')->where('id', $data['us_id'])->value('us_account');
		return $name;
	}
	//真实姓名
	public function getUsNameAttr($value, $data) {
		if ($data['us_id'] == "") {
			return '';
		}
		$name = model('User')->where('id', $data['us_id'])->value('us_real_name');
		return $name;
	}
}
