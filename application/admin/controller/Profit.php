<?php
namespace app\admin\controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
			'num' => $num,
		));
		return $this->fetch();
	}

	/*---------------------奖励奖励----------------------*/
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
		if (input('get.a') == 1) {
			$list = model("ProWal")->where($this->map)->select();
			// $url = action('Excel/user', ['list' => $list]);
			$bb = env('ROOT_PATH') . "public\wal.xlsx";
			if (file_exists($bb)) {
				$aa = unlink($bb);
			}
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			$sheet->setCellValue('A1', '账户名')
				->setCellValue('B1', '真实姓名')
				->setCellValue('C1', '金额')
				->setCellValue('D1', '类型')
				->setCellValue('E1', '时间');
			$i = 2;
			foreach ($list as $k => $v) {
				if (in_array($v['wal_type'], [2, 3, 5, 7])) {
					$list[$k]['wal_num'] = '-' . $v['wal_num'];
				}

				$sheet->setCellValue('A' . $i, $v['us_text'])
					->setCellValue('B' . $i, $v['us_name'])
					->setCellValue('C' . $i, $v['wal_num'])
					->setCellValue('D' . $i, $v['wal_note'])
					->setCellValue('E' . $i, $v['wal_add_time']);
				$i++;
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save('wal.xlsx');
			return "http://" . $_SERVER['HTTP_HOST'] . "/wal.xlsx";
		}
		$list = model('ProWal')->chaxun($this->map, $this->order, $this->size);
		$num = 0;
		foreach ($list as $k => $v) {
			if (in_array($v['wal_type'], [2, 3, 5, 7])) {
				$list[$k]['wal_num'] = '-' . $v['wal_num'];
			}
			$num += $v['wal_num'];
		}
		$this->assign(array(
			'list' => $list,
			'num' => $num,
		));
		return $this->fetch();
	}

	/*---------------------锁仓----------------------*/
	public function sc() {
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_real_name', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			$this->map[] = ['us_id', '=', $us_id];
		}
		/*if (input('get.type') != "") {
			$this->map[] = ['wal_type', '=', input('get.type')];
		}*/

		/*if (input('get.a') == 1) {
			$list = model("ProWal")->where($this->map)->select();
			// $url = action('Excel/user', ['list' => $list]);
			$bb = env('ROOT_PATH') . "public\wal.xlsx";
			if (file_exists($bb)) {
				$aa = unlink($bb);
			}
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			$sheet->setCellValue('A1', '账户名')
				->setCellValue('B1', '真实姓名')
				->setCellValue('C1', '金额')
				->setCellValue('D1', '类型')
				->setCellValue('E1', '时间');
			$i = 2;
			foreach ($list as $k => $v) {
				$sheet->setCellValue('A' . $i, $v['us_text'])
					->setCellValue('B' . $i, $v['us_name'])
					->setCellValue('C' . $i, $v['wal_num'])
					->setCellValue('D' . $i, $v['wal_note'])
					->setCellValue('E' . $i, $v['wal_add_time']);
				$i++;
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save('wal.xlsx');
			return "http://" . $_SERVER['HTTP_HOST'] . "/wal.xlsx";
		}*/

		$list = model('UserSuo')->chaxun($this->map, $this->order, $this->size);
		//$num = model("ProWal")->where($this->map)->sum('wal_num');
		$this->assign(array(
			'list' => $list,
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
		if (input('get.a') == 1) {
			$list = model("ProMsc")->where($this->map)->select();
			// $url = action('Excel/user', ['list' => $list]);
			$bb = env('ROOT_PATH') . "public\msc.xlsx";
			if (file_exists($bb)) {
				$aa = unlink($bb);
			}
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			$sheet->setCellValue('A1', '账户名')
				->setCellValue('B1', '真实姓名')
				->setCellValue('C1', '金额')
				->setCellValue('D1', '类型')
				->setCellValue('E1', '时间');
			$i = 2;
			foreach ($list as $k => $v) {
				if (in_array($v['msc_type'], [2, 3, 5, 8, 10])) {
					$list[$k]['msc_num'] = '-' . $v['msc_num'];
				}
				$sheet->setCellValue('A' . $i, $v['us_text'])
					->setCellValue('B' . $i, $v['us_name'])
					->setCellValue('C' . $i, $v['msc_num'])
					->setCellValue('D' . $i, $v['msc_note'])
					->setCellValue('E' . $i, $v['msc_add_time']);
				$i++;
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save('msc.xlsx');
			return "http://" . $_SERVER['HTTP_HOST'] . "/msc.xlsx";
		}
		$list = model('ProMsc')->chaxun($this->map, $this->order, $this->size);
		$num = 0;
		foreach ($list as $k => $v) {
			if (in_array($v['msc_type'], [2, 3, 5, 8, 10])) {
				$list[$k]['msc_num'] = '-' . $v['msc_num'];
			}
			$num += $v['msc_num'];
		}
		$this->assign(array(
			'list' => $list,
			'num' => $num,
		));
		return $this->fetch();
	}
	/*---------------------奖金----------------------*/
	public function jiji() {
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
			$this->map[] = ['ji_type', '=', input('get.type')];
		}
		if (input('get.a') == 1) {
			$list = model("ProJiji")->where($this->map)->select();
			// $url = action('Excel/user', ['list' => $list]);
			$bb = env('ROOT_PATH') . "public\jijin.xlsx";
			if (file_exists($bb)) {
				$aa = unlink($bb);
			}
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			$sheet->setCellValue('A1', '账户名')
				->setCellValue('B1', '真实姓名')
				->setCellValue('C1', '金额')
				->setCellValue('D1', '类型')
				->setCellValue('E1', '时间');
			$i = 2;
			foreach ($list as $k => $v) {
				$sheet->setCellValue('A' . $i, $v['us_text'])
					->setCellValue('B' . $i, $v['us_name'])
					->setCellValue('C' . $i, $v['ji_num'])
					->setCellValue('D' . $i, $v['ji_note'])
					->setCellValue('E' . $i, $v['ji_add_time']);
				$i++;
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save('jijin.xlsx');
			return "http://" . $_SERVER['HTTP_HOST'] . "/jijin.xlsx";
		}
		$list = model('ProJiji')->chaxun($this->map, $this->order, $this->size);
		// foreach ($list as $k => $v) {
		// 	if(in_array($v['msc_type'],[2,3,5])){
		// 		$list[$k]['msc_num'] = "-".$v['msc_num'];
		// 	}
		// }
		$num = model("ProJiji")->where($this->map)->sum('ji_num');
		$this->assign(array(
			'list' => $list,
			'num' => $num,
		));
		return $this->fetch();
	}
	/*--------------奖励-----------------*/
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
			'num' => $num,
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
			if (input("get.type") == 1) {
				$this->map[] = ['us_id', '=', $us_id];
			} elseif (input("get.type") == 0) {
				$this->map[] = ['us_to_id', '=', $us_id];
			} else {
				$this->map[] = ['us_id|us_to_id', '=', $us_id];
			}

		}
		if (input('get.wa_type') != "") {
			$this->map[] = ['wa_type', '=', input('get.wa_type')];
		}

		if (input('get.a') == 1) {
			$list = model("ProTransfer")->where($this->map)->select();
			// $url = action('Excel/user', ['list' => $list]);
			$bb = env('ROOT_PATH') . "public\zhuan.xlsx";
			if (file_exists($bb)) {
				$aa = unlink($bb);
			}
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			$sheet->setCellValue('A1', '转出账户')
				->setCellValue('B1', '姓名')
				->setCellValue('C1', '转入账号')
				->setCellValue('D1', '姓名')
				->setCellValue('E1', '数目')
				->setCellValue('F1', '时间');
			$i = 2;
			foreach ($list as $k => $v) {
				$sheet->setCellValue('A' . $i, $v['us_text'])
					->setCellValue('B' . $i, $v['us_name'])
					->setCellValue('C' . $i, $v['us_to_text'])
					->setCellValue('D' . $i, $v['us_to_name'])
					->setCellValue('E' . $i, $v['tr_num'])
					->setCellValue('F' . $i, $v['tr_add_time']);
				$i++;
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save('zhuan.xlsx');
			return "http://" . $_SERVER['HTTP_HOST'] . "/zhuan.xlsx";
		}

		$list = model('ProTransfer')->chaxun($this->map, $this->order, $this->size);
		$num = model('ProTransfer')->where($this->map)->sum('tr_num');
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

		if (input('get.a') == 1) {
			$list = model("ProTixian")->where($this->map)->select();
			// $url = action('Excel/user', ['list' => $list]);
			$bb = env('ROOT_PATH') . "public\xian.xlsx";
			if (file_exists($bb)) {
				$aa = unlink($bb);
			}
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			$sheet->setCellValue('A1', '用户')
				->setCellValue('B1', '真实姓名')
				->setCellValue('C1', '金额')
				->setCellValue('D1', '手续费')
				->setCellValue('E1', '实到')
				->setCellValue('F1', '类型')
				->setCellValue('G1', '账户')
				->setCellValue('H1', '收款人')
				->setCellValue('I1', '银行名称')
				->setCellValue('J1', '时间')
				->setCellValue('K1', '状态');
			$i = 2;
			foreach ($list as $k => $v) {
				$sheet->setCellValue('A' . $i, $v['us_text'])
					->setCellValue('B' . $i, $v['us_name'])
					->setCellValue('C' . $i, $v['tx_num'])
					->setCellValue('D' . $i, $v['tx_recharge'])
					->setCellValue('E' . $i, $v['tx_shidao'])
					->setCellValue('F' . $i, $v['type_text'])
					->setCellValue('G' . $i, $v['tx_account'])
					->setCellValue('H' . $i, $v['tx_name'])
					->setCellValue('I' . $i, $v['tx_addr'])
					->setCellValue('J' . $i, $v['tx_add_time'])
					->setCellValue('K' . $i, $v['status_text']);
				$i++;
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save('xian.xlsx');
			return "http://" . $_SERVER['HTTP_HOST'] . "/xian.xlsx";
		}

		$list = model('ProTixian')->chaxun($this->map, $this->order, $this->size);
		$num = model('ProTixian')->where($this->map)->sum('tx_num');
		$this->assign(array(
			'list' => $list,
			'num' => $num,
		));
		return $this->fetch();
	}

	public function txCheck() {
		if (is_post()) {
			$da = input('post.');
			$id = input('post.id');
			$info = model('ProTixian')->get($id);
			$rst = model('ProTixian')->xiugai(['tx_status' => input('post.status')], ['id' => input('post.id')]);
			if ($rst) {
				if (input('post.status') == 2) {
					model("User")::usMscChange($info['us_id'], $info['tx_num'], 6);
				}
				$this->success('已审核');
			} else {
				$this->error('操作失败');
			}
		}

	}

}
