<?php
namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 *
 */
class User extends Model {
	use SoftDelete;
	protected $deleteTime = 'delete_time';

	public function parent() {
		return $this->hasOne('User', 'id', 'us_pid');
	}
	// 状态
	public function getStatusTextAttr($value, $data) {
		$array = [
			0 => '被禁用',
			1 => '正常',
			2 => '被禁用',
		]; 
		return $array[$data['us_status']];
	}
	
	// public function getLevelTextAttr($value,$data){
	// 	return cache('level')['us_level']['cal_name'];
	// }
	
	//父账号
	public function getPtelAttr($value, $data) {
		if ($data['us_pid']) {
			return $this->where('id', $data['us_pid'])->value('us_account');
		} else {
			return '空';
		}
	}
	//节点人
	public function getAtelAttr($value, $data) {
		if ($data['us_aid']) {
			return $this->where('id', $data['us_aid'])->value('us_account');
		} else {
			return '空';
		}
	}
	//详情
	public function detail($where, $field = "*") {
		
		return $this->with('parent')->where($where)->field($field)->find();
	}
	//查询
	public function chaxun($map, $order, $size, $field = "*") {
		return $this->where($map)->order($order)->field($field)->paginate($size, false, [
			'query' => request()->param()]);
	}
	
	/**
	 * 添加
	 * @param  [array] $data [description]
	 * @return [bool]       [description]
	 */
	public function tianjia($da) {

		$validate = validate('User');
		$res = $validate->scene('addUser')->check($da);
		if (!$res) {
			return [
				'code'  =>  0,
				'msg'	=>  $validate->getError(),
			];
		}

		/*$tel_count = $this->where('us_tel', $da['us_tel'])->count();
		if ($tel_count) {
			return [
				'code' => 0,
				'msg' => '该手机号已存在',
			];
		}*/
		$acc_count = $this->where('us_account', $da['us_account'])->count();
		if ($acc_count) {
			return [
				'code' => 0,
				'msg' => '该账号已存在',
			];
		}
		
		$pinf = model("User")->where('us_account', $da['p_acc'])->find();
		if (count($pinf)) {
			$da['us_pid'] = $pinf['id'];
			$da['us_path'] = $pinf['us_path'] . ',' . $pinf['id'];
			$da['us_path_long'] = $pinf['us_path_long'] + 1;
		} else {
			return [
				'code' => 0,
				'msg' => '推荐人不存在',
			];
		}
		$ainf = model("User")->where('us_account', $da['a_acc'])->find();
		if (count($ainf)) {
			$da['us_aid'] = $ainf['id'];
			$da['us_tree'] = $ainf['us_tree'] . ',' . $ainf['id'];
			$da['us_tree_long'] = $ainf['us_tree_long'] + 1;
		} else {
			return [
				'code' => 0,
				'msg' => '推荐人不存在',
			];
		}



		$da['us_add_time'] = date('Y-m-d H:i:s');
		$da['us_status'] = 1;
		// $da['us_head_pic'] = '/static/mobile/img/logo.png';
		$da['us_pwd'] = mine_encrypt($da['us_pwd']);
		$da['us_safe_pwd'] = mine_encrypt($da['us_safe_pwd']);
		$rel = $this->insertGetId($da);
		if($rel){
			return [
				'code' => 1,
				'msg' => '注册成功',
			];
		}else{
			return [
				'code' => 0,
				'msg' => '注册失败',
			];
		}
		
	}

	/**
	 * 修改
	 * @param  [array] $data  [数据]
	 * @param  [array] $where [条件]
	 * @return [bool]
	 */
	public function editInfo($da) {
		if ($da['us_pwd'] != '') {
			$da['us_pwd'] = mine_encrypt($da['us_pwd']);
		} elseif(key_exists('us_pwd',$da)) {
			unset($da['us_pwd']);
		}
		if ($da['us_safe_pwd']!='') {
			$da['us_safe_pwd'] = mine_encrypt($da['us_safe_pwd']);
		} elseif(key_exists('us_safe_pwd',$da)) {
			unset($da['us_safe_pwd']);
		}
		// halt($da);
		model('User')->update($da);
		return [
			'code' => 1,
			'msg' => '修改成功',
		];
	}
	//送币
	public function songbi($da){
		if($da['song_type']==1){
			if($da['song_num']>0){
				$type = 1;
			}else{
				$type = 2;
			}
			return self::usWalChange($da['id'],abs($da['song_num']),$type);
			
		}elseif($da['song_type']==2){
			if($da['song_num']>0){
				$type = 1;
			}else{
				$type = 2;
			}
			return self::usMscChange($da['id'],abs($da['song_num']),$type);
		}
	}
	//可用货币变动 AMFC
	static public function usWalChange($us_id,$num,$type){
		$note = array(
			1 => '后台充值',
			2 => '后台扣除',
			3 => '卖出',
			4 => '买入',
			5 => '锁仓',
			6 => '解仓',
			7 => '置换Doken',
			8 => '置换AMFC',
			9 => '锁仓释放',
			10 => '持币生息',
			11 => '额外奖励',
		);
		if (in_array($type, array(1,4,6,8,9,10,11))) {
			$rel = self::where('id', $us_id)->setInc('us_wal', $num);
			if($type == 6){
				self::where('id',$us_id)->setDec('us_dong',$num);
			}
		} else{
			$rel = self::where('id', $us_id)->setDec('us_wal', $num);
			if($type == 5){
				self::where('id',$us_id)->setInc('us_dong',$num);
			}
		}
		if($rel){
			model('ProWal')->tianjia($us_id,$num,$type,$note[$type]);

			return [
				'code' => 1,
				'msg' => '成功',
			];
		}else{
			return [
				'code'=>0,
				'msg' => '失败',
			];
		}
	}
	//奖励变动 token
	static public function usMscChange($us_id,$num,$type,$name=''){
		// halt($us_id);
		$note = array(
			1 => '后台充值',
			2 => '后台扣除',
			3 => '提现',
			4 => '置换Doken',
			5 => '置换AMFC',
			6 => '提现驳回',
			7 => '获得直推商家'.$name.'奖励',
			8 => '购买商品',
			9 => '获得直推消费'.$name.'奖励',
			10 => '申请商家扣除',
			11 => '申请商家驳回',
			12 => '订单取消',
			13 => '申请店铺金额返回'
		);


		if (in_array($type, array(1,4,6,7,9,11,12,13))) {

			$rel = self::where('id', $us_id)->setInc('us_msc', $num);
			
		} else{
			$rel = self::where('id', $us_id)->setDec('us_msc', $num);
		}
		if($rel){
			ProMsc::tianjia($us_id,$num,$type,$note[$type]);
			return [
				'code' => 1,
				'msg' => '成功',
			];
		}else{
			return [
				'code'=>0,
				'msg' => '失败',
			];
		}
	}

	//直推奖励
	public function direct_pro($id){
		$info = $this->get($id);
		if($info['us_pid']){
			$parent = $this->get($info['us_pid']);
			if($parent && $parent['us_status']==1){
				self::usMscChange($parent['id'],cache('setting')['cal_direct_pro'],3,$info['us_account']);
			}
		}
	}
	public function direct_mer($id,$money){
		$info = $this->get($id);
		if($info['us_pid']){
			$parent = $this->get($info['us_pid']);
			if($parent && $parent['us_status']==1){
				$nn = $money * cache('setting')['cal_dir_mer']/100;
				self::usMscChange($parent['id'],$nn,7,$info['us_account']);
			}
		}
	}
	
	public function direct_xiaofei($id,$money){
		$info = $this->get($id);
		if($info['us_pid']){
			$parent = $this->get($info['us_pid']);
			if($parent && $parent['us_status']==1){
				$nn = $money * cache('setting')['cal_dir_xiao']/100;
				self::usMscChange($parent['id'],$nn,9,$info['us_account']);
			}
		}
	}


}
