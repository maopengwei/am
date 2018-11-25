<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 *
 */
class ProJiji extends Model {
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
	static public function tianjia($uid, $jine, $type, $note = "") {
		
		$array = array(
			'us_id' => $uid,
			'ji_num' => $jine,
			'ji_type' => $type,
			'ji_note' => $note,
			'ji_add_time' => date('Y-m-d H:i:s'),
		);
		return static::insertGetId($array);
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

	//用户账号
	public function getUsTextAttr($value, $data) {
		if ($data['us_id'] == "") {
			return '空';
		}
		$name = User::where('id', $data['us_id'])->value('us_account');
		return $name;
	}
	//真实姓名
	public function getUsNameAttr($value, $data) {
		if ($data['us_id'] == "") {
			return '';
		}
		$name = User::where('id', $data['us_id'])->value('us_real_name');
		return $name;
	}
}
