<?php
namespace app\mall\controller;
use think\Db;

/**
 * 财务管理
 */
class Profit extends Common {

	public function __construct() {
		parent::__construct();
	}
	public function index() {
		return $this->fetch();
	}
	//锁仓
	public function tran_suo() {
		if (is_post()) {
			$d = input('post.');

			$validate = validate('Profit');
			$res = $validate->scene('suo')->check($d);
			if (!$res) {
				$this->error($validate->getError());
			}

			if (mine_encrypt($d['us_safe_pwd']) != $this->mine['us_safe_pwd']) {
				$this->error('交易密码不正确');
			} else {
				unset($d['us_safe_pwd']);
			}

			if ($d['suo_num'] % 10 != 0) {
				$this->error('锁仓金额必须为10的倍数');
			}
			if ($d['suo_num'] > $this->mine['us_wal']) {
				$this->error('可用资产不足');
			}
			$rel = model('User')->UsWalChange(session('us_id'), $d['suo_num'], 5);
			if ($rel['code']) {
				$arr = [
					'us_id' => session('us_id'),
					'suo_num' => $d['suo_num'],
					'rea_date' => date('Y-m-d H:i:s'),
					'add_time' => date('Y-m-d H:i:s'),
					'yshi_time' => date('Y-m-d H:i:s', strtotime('+30day')),
					'yshi_bie' => cache('setting')['cal_rea_yi'],
					'yshi_num' => $d['suo_num'] * cache('setting')['cal_rea_yi'] / 100,
					'zshi_num' => 0,
					'sshi_num' => $d['suo_num'],
				];
				Db::name('user_suo')->insert($arr);
				$this->success('锁仓成功');
			} else {
				$this->error('锁仓失败');
			}
			return $rel;
		} else {
			return $this->fetch();
		}
	}
	public function tran_jie() {
		if (is_post()) {
			$d = input('post.');

			$validate = validate('Profit');
			$res = $validate->scene('suo')->check($d);
			if (!$res) {
				$this->error($validate->getError());
			}

			if (mine_encrypt($d['us_safe_pwd']) != $this->mine['us_safe_pwd']) {
				$this->error('交易密码不正确');
			} else {
				unset($d['us_safe_pwd']);
			}

			if ($d['suo_num'] % 10 != 0) {
				$this->error('解仓金额必须为10的倍数');
			}
			if ($d['suo_num'] > $this->mine['us_dong']) {
				$this->error('冻结资产不足');
			}
			$rel = model('User')->UsWalChange(session('us_id'), $d['suo_num'], 6);
			if ($rel['code']) {
				$this->success('解仓成功');
			} else {
				$this->error('解仓失败');
			}
			return $rel;
		} else {
			return $this->fetch();
		}
	}

	public function tran_amfc() {

		$time = date('Y-m-d');
		$price = Db::name('sys_price')->where('time', '=', $time)->value('price');

		if (is_post()) {
			$d = input('post.');

			$validate = validate('Profit');
			$res = $validate->scene('suo')->check($d);
			if (!$res) {
				$this->error($validate->getError());
			}

			if (mine_encrypt($d['us_safe_pwd']) != $this->mine['us_safe_pwd']) {
				$this->error('交易密码不正确');
			} else {
				unset($d['us_safe_pwd']);
			}

			if ($d['suo_num'] % 10 != 0) {
				$this->error('置换金额必须为10的倍数');
			}
			if ($d['suo_num'] > $this->mine['us_msc']) {
				$this->error('Doken资产不足');
			}

			$nn = $d['suo_num'] / $price;
			$rel = model('User')::UsMscChange(session('us_id'), $d['suo_num'], 5);
			model('User')::UsWalChange(session('us_id'), $nn, 8);

			if ($rel['code']) {

				model('User')->UsWalChange(session('us_id'), $nn, 5);
				$arr = [
					'us_id' => session('us_id'),
					'suo_num' => $nn,
					'rea_date' => date('Y-m-d H:i:s'),
					'add_time' => date('Y-m-d H:i:s'),
				];
				Db::name('user_suo')->insert($arr);

				$this->success('置换成功');
			} else {
				$this->error('置换失败');
			}
			return $rel;
		} else {
			$this->assign('price', $price);
			return $this->fetch();
		}
	}

	public function tran_token() {

		$time = date('Y-m-d');
		$price = Db::name('sys_price')->where('time', '=', $time)->value('price');

		if (is_post()) {
			$d = input('post.');

			$validate = validate('Profit');
			$res = $validate->scene('suo')->check($d);
			if (!$res) {
				$this->error($validate->getError());
			}
			if (mine_encrypt($d['us_safe_pwd']) != $this->mine['us_safe_pwd']) {
				$this->error('交易密码不正确');
			} else {
				unset($d['us_safe_pwd']);
			}

			if ($d['suo_num'] % 10 != 0) {
				$this->error('置换金额必须为10的倍数');
			}
			if ($d['suo_num'] > $this->mine['us_wal']) {
				$this->error('AMFC资产不足');
			}
			$mmm = $d['suo_num'] * 10 / 100;
			$mmn = $d['suo_num'] - $mmm;

			$nn = $mmn * $price;
			$rel = model('User')::UsWalChange(session('us_id'), $d['suo_num'], 7);
			model('User')::UsMscChange(session('us_id'), $nn, 4);
			Db::name("user")->where('id', session('us_id'))->setInc('us_jijin', $mmm);
			model('ProJiji')->tianjia(session('us_id'), $mmm, 1, 'AMFC转入');
			if ($rel['code']) {
				$this->success('置换成功');
			} else {
				$this->error('置换失败');
			}
			return $rel;
		} else {
			$this->assign('price', $price);
			return $this->fetch();
		}
	}

	public function tx() {
		if (is_post()) {
			$d = input('post.');

			$validate = validate('Profit');
			$res = $validate->scene('tx')->check($d);
			if (!$res) {
				$this->error($validate->getError());
			}
			if ($this->mine['us_is_mer'] == 0) {
				$this->error('必须是商家才能提现');
			}
			if ($d['tx_num'] % 10 != 0) {
				$this->error('提现金额必须为10的倍数');
			}
			if (mine_encrypt($d['us_safe_pwd']) != $this->mine['us_safe_pwd']) {
				$this->error('交易密码不正确');
			}
			if ($d['tx_type'] == 1) {
				if (!$this->mine['us_bank_name'] || !$this->mine['us_bank_person'] || !$this->mine['us_bank_number']) {
					$this->error('请到个人中心完善银行信息');
				}
				$d['tx_account'] = $this->mine['us_bank_number'];
				$d['tx_addr'] = $this->mine['us_bank_name'];
				$d['tx_name'] = $this->mine['us_bank_person'];

			} elseif ($d['tx_type'] == 2) {
				if (!$this->mine['us_alipay']) {
					$this->error('请到个人中心完善支付宝信息');
				}
				$d['tx_account'] = $this->mine['us_alipay'];
			} elseif ($d['tx_type'] == 3) {

				if (!$this->mine['us_wechat']) {
					$this->error('请到个人中心完善微信信息');
				}
				$d['tx_account'] = $this->mine['us_wechat'];
			}
			$d['us_id'] = $this->mine['id'];
			$rel = model("ProTixian")->tianjia($d);
			return $rel;
		} else {
			return $this->fetch();
		}
	}
	public function tx_rec() {
		if (is_post()) {

			$this->map[] = ['us_id', '=', $this->mine['id']];
			$this->size = 10;
			$list = model("ProTixian")->chaxun($this->map, $this->order, $this->size);
			return ['code' => 1, 'data' => $list];

		}
		return $this->fetch();
	}

	public function sc_rec() {
		$id = $this->mine['id'];
		$this->map[] = ['us_id', '=', $this->mine['id']];
		$this->size = 10;
		$list = model("UserSuo")->chaxun($this->map, $this->order, $this->size);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function wal() {
		if (is_post()) {

			$this->map[] = ['us_id', '=', $this->mine['id']];
			$this->size = 10;
			$list = model("ProWal")->chaxun($this->map, $this->order, $this->size);

			foreach ($list as $k => $v) {
				if (in_array($v['wal_type'], [2, 3, 5, 7])) {
					$list[$k]['wal_num'] = '-' . $v['wal_num'];
				}
			}
			return ['code' => 1, 'data' => $list];

		}
		return $this->fetch();
	}
	public function msc() {
		if (is_post()) {

			$this->map[] = ['us_id', '=', session('us_id')];
			$this->size = 10;
			$list = model("ProMsc")->chaxun($this->map, $this->order, $this->size);
			foreach ($list as $k => $v) {
				if (in_array($v['msc_type'], [2, 3, 5, 8, 10])) {
					$list[$k]['msc_num'] = "-" . $v['msc_num'];
				}
			}
			return ['code' => 1, 'data' => $list];
		}
		return $this->fetch();
	}

	public function reg() {
		if (is_post()) {

			$this->map[] = ['us_id', '=', session('us_id')];
			$this->size = 10;
			$list = model("ProReg")->chaxun($this->map, $this->order, $this->size);
			return ['code' => 1, 'data' => $list];
		}
		return $this->fetch();
	}
	public function rec() {
		if (is_post()) {

			$this->map[] = ['us_id', '=', session('us_id')];
			$this->size = 10;
			$list = model("ProRec")->chaxun($this->map, $this->order, $this->size);
			return ['code' => 1, 'data' => $list];
		}
		return $this->fetch();
	}

	public function integrity_list() {
		if (is_post()) {
			$p = input('p') ? input('p') : 1;
			$page = $p . ',15';
			$map = array(
				'us_id' => session('mid'),
			);
			$list = model('ProfitIntegrity')->where($map)->page($page)->order('id desc')->select();
			$html = '';
			foreach ($list as $k => $v) {
				$html .= '<li><div class="jyjl_left"><p>' . $v['type_text'] . '</p></div><div class="jyjl_center"><p><span></span><span>' . $v['num'] . '</span></p></div>';
				$html .= '<div class="jyjl_right"><p>' . date('Y-m-d', $v['add_time']) . '</p><p>' . date('H:i', $v['add_time']) . '</p></div></li>';
			}
			echo json_encode($html);
			return;
		}
		return $this->fetch();
	}
	public function cash_list() {
		$where = array(
			'us_id' => session('mid'),
		);
		$list = model('profit_cash')->where($where)->order('id desc')->paginate(9);
		$this->assign(array(
			'list' => $list,
		));
		return $this->fetch();
	}
	public function pay_list() {
		if (is_post()) {
			$p = input('p') ? input('p') : 1;
			$map = array(
				'uid' => session('mid'),
			);
			$page = $p . ',15';
			$list = model('PayRecord')->where($map)->page($page)->order('id desc')->select();
			$html = '';
			foreach ($list as $k => $v) {
				$html .= '<li><div class="jyjl_left"><p>' . $v['type_text'] . '</p></div><div class="jyjl_center"><p><span></span><span>' . $v['money'] . '</span></p></div>';
				$html .= '<div class="jyjl_right"><p>' . date('Y-m-d', $v['add_time']) . '</p><p>' . date('H:i', $v['add_time']) . '</p></div></li>';
			}
			echo json_encode($html);
			return;
		}
		return $this->fetch();
	}
	public function ali_list() {
		if (is_post()) {
			$p = input('p') ? input('p') : 1;
			$map = array(
				'uid' => session('mid'),
			);
			$page = $p . ',15';
			$list = model('AlipayPay')->where($map)->page($page)->order('id desc')->select();
			$html = '';
			foreach ($list as $k => $v) {
				$html .= '<li><div class="jyjl_left"><p>' . $v['type_text'] . '</p></div><div class="jyjl_center"><p><span></span><span>' . $v['money'] . '</span></p></div>';
				$html .= '<div class="jyjl_right"><p>' . date('Y-m-d', $v['add_time']) . '</p><p>' . date('H:i', $v['add_time']) . '</p></div></li>';
			}
			echo json_encode($html);
			return;
		}
		return $this->fetch();
	}
}
