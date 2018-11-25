<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 会员卡
 */
class PayRecord extends Model {
	use SoftDelete;
	protected $deleteTime = 'delete_time';
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
	public function tianjia($pay_type, $us_id, $pay_num, $pay_lei, $note,$ll = 0) {

		if($pay_lei!=0){
			$money = buy_type($pay_lei);
			if($money != $pay_num){
				return;
			}
		}


		$info = model('User')->get($us_id);
		$array = array(
			'pay_type' => $pay_type,
			'us_id' => $us_id,
			'pay_num' => $pay_num,
			'pay_lei' => $pay_lei,
			'pay_note' => $note,
			'pay_add_time' => date('Y-m-d H:i:s'),
		);
		$rel = $this->insertGetId($array);
		if(in_array($pay_lei, [0, 1, 2, 3, 4, 15])){
			$arr = [
				0 => 8, //充值
				1 => 9, //开通vip
				2 => 10, //续费vip
				3 => 11, //开通plus
				4 => 12, //续费plus
				15 => 13, // vip升plus
			];
			if($pay_lei == 0 && $pay_num >=3000){
				$pay_num = $pay_num + cache('setting')['song_num'];
			}
			model('Wallet')->tianjia($us_id, $pay_num, $arr[$pay_lei]);
		}else{
			$arr = [
				'id' => $us_id,
			];
			if(in_array($pay_lei,[5,6,7,8])){

				$pay_num = $pay_num+cache('setting')['vip'];
				
				$arr['us_jibie_day'] = date('Y-m-d H:i:s');
				$arr['us_is_jing'] = 1;
				$arr['us_jibie'] = $pay_lei - 4;
				$arr['us_wei_jibie'] = $pay_lei - 4;
				$pay_nn = $pay_num/3;
				if($pay_nn && $ll == 0){
					model('Wallet')->tianjia($us_id,$pay_nn,14);
				}
				direct_profit($us_id, $pay_num,$arr['us_jibie']);

				if($info['us_wei_pid']){
					$parent = model("User")->get($info['us_wei_pid']);
					$arr['us_pid'] = $parent['id'];
					$arr['us_path'] = $parent['us_path'] . "," . $parent['id'];
					$arr['us_path_long'] = $parent['us_path_long'] + 1;
				}
			}else{
				if($info['us_is_jing']==2){
					yeji($us_id,$pay_num,0);
				}
				$pay_nn = $pay_num/3;
				if($pay_nn && $ll == 0){
					model('Wallet')->tianjia($us_id,$pay_nn,15);
				}
				switch (true) {
				case $pay_lei == 9:
					$arr['us_jibie'] = 2;
					break;
				case $pay_lei == 10 || $pay_lei == 12:
					$arr['us_jibie'] = 3;
					break;
				case $pay_lei == 11 || $pay_lei == 13 || $pay_lei == 14:
					$arr['us_jibie'] = 4;
					break;
				default:
					break;
				}
			}
			model('User')->update($arr);
		}
		
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
	//真实姓名
	public function getTypeTextAttr($value, $data) {
		$arr = [
			1 => '微信',
			2 => '支付宝',
			3 => '银行卡',
			4 => '会员卡',
			5 => '线下',
		];

		return $arr[$data['pay_type']];
	}
}
