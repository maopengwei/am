<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 *
 */
class Admin extends Model {
	use SoftDelete;
	protected $deleteTime = 'delete_time';

	public function getRoleAttr($value, $data) {
		return db('admin_role')->where('id', $data['ro_id'])->value('ro_name');
	}
	
	//详情
	public function detail($where, $field = "*") {
		return $this->where($where)->field($field)->find();
	}
	//查询
	public function chaxun($map, $order, $size, $field = "*") {

		$list = $this->where($map)->order($order)->field($field)->paginate($size, false, [
			'query' => request()->param()]);
		return $list;
	}
	/**
	 * 添加
	 * @param  [array] $data [description]
	 * @return [bool]       [description]
	 */
	public function tianjia($data) {
		$count = $this->where('ad_tel', $data['ad_tel'])->count();
		if ($count) {
			return [
				'code' => 0,
				'msg' => '手机号已存在',
			];
		}
		// 工号
		$work_number = $this->order('Id desc')->value('ad_work_number');
		if ($work_number) {
			$bb = substr($work_number, -5);
			$cc = substr($work_number, 0, 3);
			$dd = $bb + 1;
			$new_work_number = $cc . $dd;
		} else {
			$new_work_number = 'gly10001';
		}

		$data['ad_work_number'] = $new_work_number;
		$data['ad_pwd'] = mine_encrypt($data['ad_pwd']);
		$data['ad_add_time'] = date('Y-m-d H:i:s');
		$rel = $this->insertGetid($data);
		if ($rel) {
			return [
				'code' => 1,
				'msg' => '添加成功',
				'data' => $rel,
			];
		}
	}
	/**
	 * 修改
	 * @param  [array] $data  [数据]
	 * @param  [array] $where [条件]
	 * @return [bool]
	 */
	public function xiugai($data, $where) {

		if (array_key_exists('ad_pwd', $data)) {
			$data['ad_pwd'] = mine_encrypt($data['ad_pwd']);
		}
		$rel = $this->save($data, $where);
		if ($rel) {
			return [
				'code' => 1,
				'msg' => '修改成功',
				'data' => $rel,
			];
		} else {
			return [
				'code' => 0,
				'msg' => '您并没有做出修改',
			];
		}
	}


	public function delInfo($id){
		$admin = $this->detail(['id'=>session('ad_id')]);
		if($admin['ro_id']!=1){
			return [
				'code'=> 0,
				'msg' => '您不是超级管理员',
			];
		}
		if (input('post.id')) {
            $id = input('post.id');
           	if($id == 1){
           		return [
					'code'=> 0,
					'msg' => '此管理员不能删除',
				];
           	}
        } else {
        	return [
				'code'=> 0,
				'msg' => 'id不存在',
			];
        }
        
        $info = $this->get($id);
        if ($info) {
            $rel = $this->destroy($id);
            if ($rel) {
            	return [
					'code'=> 1,
					'msg' => '删除成功',
				];
            } else {
            	return [
					'code'=> 0,
					'msg' => '请联系网站管理员',
				];
            }
        } else {
        	return [
				'code'=> 0,
				'msg' => '此管理员不存在',
			];
        }
	}
}
