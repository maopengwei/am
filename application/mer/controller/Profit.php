<?php
namespace app\mer\controller;

/**
 * 利润表
 */
class Profit extends Common {

	public function __construct() {
		parent::__construct();
	}

	/*--------------------支付------------------------*/
	public function payRecord() {
		if (is_post()) {

			$rst = model('Order')->xiugai([input('post.key') => input('post.value')], ['id' => input('post.id')]);
			return $rst;
		}
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_real_name|us_tel', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			$this->map[] = ['us_id', '=', $us_id];
		}
		if (input('get.pay_type') != "") {
			$this->map[] = ['pay_type', '=', input('get.pay_type')];
		}
		if (input('get.pay_lei') != "") {
			$this->map[] = ['pay_lei', '=', input('get.pay_lei')];
		}
		$list = model('PayRecord')->chaxun($this->map, $this->order, $this->size);
		$num = model("PayRecord")->where($this->map)->sum('pay_num');
		$this->assign(array(
			'list' => $list,
			'num'=>$num,
		));
		return $this->fetch();
	}

	// public function commission() {
	// 	if (is_post()) {

	// 		$rst = model('Tixian')->xiugai([input('post.key') => input('post.value')], ['id' => input('post.id')]);
	// 		return $rst;
	// 	}
	// 	if (input('get.keywords')) {
	// 		$us_id = model("User")->where('us_account|us_real_name|us_tel', input('get.keywords'))->value('id');
	// 		if (!$us_id) {
	// 			$us_id = 0;
	// 		}
	// 		$this->map[] = ['us_id', '=', $us_id];
	// 	}
	// 	if (input('get.tx_status') != "") {
	// 		$this->map[] = ['tx_status', '=', input('get.tx_status')];
	// 	}
	// 	$list = model('Tixian')->chaxun($this->map, $this->order, $this->size);
	// 	$num = model("Tixian")->where($this->map)->sum('tx_num');
	// 	$this->assign(array(
	// 		'list' => $list,
	// 		'num'=>$num,
	// 	));
	// 	return $this->fetch();
	// }
	// public function txCheck() {
	// 	$id = input('post.id');
	// 	$info = model('Tixian')->get($id);
	// 	$rst = model('Tixian')->xiugai(['tx_status' => input('post.status')], ['id' => input('post.id')]);
	// 	if ($rst) {
	// 		if (input('post.status') == 2) {
	// 			model("Wallet")->tianjia($info['us_id'], $info['tx_num'], 8);
	// 			$this->success('已驳回');
	// 		}else{
	// 			$this->success('审核通过');
	// 		}
	// 	} else {
	// 		$this->error('操作失败');
	// 	}
	// }

	/*---------------------奖励Doken----------------------*/
	public function wal() {
		
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_real_name|us_tel', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			$this->map[] = ['us_id', '=', $us_id];
		}
		if (input('get.type') != "") {
			$this->map[] = ['wal_type', '=', input('get.type')];
		}
		$list = model('ProWal')->chaxun($this->map, $this->order, $this->size);
		foreach ($list as $k => $v) {
			if(in_array($v['wal_type'],[5,6,10,11,12])){
				$list[$k]['wal_num'] = "-".$v['wal_num'];
			}
		}
		$num = model("ProWal")->where($this->map)->sum('wal_num');
		$this->assign(array(
			'list' => $list,
			'num'=>$num,
		));
		return $this->fetch();
	}


	/*---------------------奖金----------------------*/
	public function msc() {
		if (is_post()) {
			$rst = model('Order')->xiugai([input('post.key') => input('post.value')], ['id' => input('post.id')]);
			return $rst;
		}
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_real_name|us_tel', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			$this->map[] = ['us_id', '=', $us_id];
		}
		if (input('get.type') != "") {
			$this->map[] = ['msc_type', '=', input('get.type')];
		}
		$list = model('ProMsc')->chaxun($this->map, $this->order, $this->size);
		foreach ($list as $k => $v) {
			if(in_array($v['msc_type'],[2,3,4])){
				$list[$k]['msc_num'] = "-".$v['msc_num'];
			}
		}
		$num = model("ProMsc")->where($this->map)->sum('msc_num');
		$this->assign(array(
			'list' => $list,
			'num'=>$num,
		));
		return $this->fetch();
	}

	/*--------------Doken-----------------*/
	public function integral() {
		if (is_post()) {
			$rst = model('Order')->xiugai([input('post.key') => input('post.value')], ['id' => input('post.id')]);
			return $rst;
		}
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_real_name|us_tel', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			$this->map[] = ['us_id', '=', $us_id];
		}
		if (input('get.in_type') != "") {
			$this->map[] = ['in_type', '=', input('get.in_type')];
		}
		$list = model('Integral')->chaxun($this->map, $this->order, $this->size);
		$num = model("Integral")->where($this->map)->sum('in_num');
		$this->assign(array(
			'list' => $list,
			'num'=>$num,
		));
		return $this->fetch();
	}
	


	//转账记录
	public function transfer() {
		if (is_post()) {

			$rst = model('Tixian')->xiugai([input('post.key') => input('post.value')], ['id' => input('post.id')]);
			return $rst;
		}
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_real_name|us_tel', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			if(input("get.type")==1){
				$this->map[] = ['us_id', '=', $us_id];
			}elseif(input("get.type")==0){
				$this->map[] = ['us_to_id', '=', $us_id];
			}else{
				$this->map[] = ['us_id|us_to_id', '=', $us_id];
			}
			
		}
		if (input('get.wa_type') != "") {
			$this->map[] = ['wa_type', '=', input('get.wa_type')];
		}
		$list = model('Transfer')->chaxun($this->map, $this->order, $this->size);
		$num = model('Transfer')->where($this->map)->sum('tr_num');
		$this->assign(array(
			'list' => $list,
			'num' => $num,
		));
		return $this->fetch();
	}



	/*--------------------提现-------------------------*/
	public function tx() {
		if (is_post()) {
			$rst = model('Tixian')->xiugai([input('post.key') => input('post.value')], ['id' => input('post.id')]);
			return $rst;
		}
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_real_name|us_tel', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			$this->map[] = ['us_id', '=', $us_id];
		}
		if (input('get.status') != "") {
			$this->map[] = ['tx_status', '=', input('get.status')];
		}
		$list = model('Tixian')->chaxun($this->map, $this->order, $this->size);
		$num = model('Tixian')->where($this->map)->sum('tx_num');
		$this->assign(array(
			'list' => $list,
			'num' => $num,
		));
		return $this->fetch();
	}

	public function txCheck() {
		if(is_post()){
			$da = input('post.');
			$id = input('post.id');
			$info = model('Tixian')->get($id);
			$rst = model('Tixian')->xiugai(['tx_status' => input('post.status')], ['id' => input('post.id')]);
			if ($rst) {
				if (input('post.status') == 2) {
					model("ProWal")->tianjia($info['us_id'], $info['tx_num'], 11);
				}
				$this->success('已审核');
			} else {
				$this->error('操作失败');
			}
		}
		
	}


}
