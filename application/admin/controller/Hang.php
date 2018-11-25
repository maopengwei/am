<?php
namespace app\admin\controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @todo
 */
class Hang extends Common {

	// ------------------------------------------------------------------------


	/*=--------------------------------------------------股份*/
	//出售
	public function sale(){
	
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_real_name|us_tel', input('get.keywords'))->value('id');
			if ($us_id) {
				$this->map[] = ['us_id','=',$us_id];
			}else{
				$this->map[] = ['us_id','=',9999];
			}
		}
		if (input('get.a') == 1) {
			$list = model("HangIssue")->where($this->map)->select();
			// $url = action('Excel/user', ['list' => $list]);
			$bb = env('ROOT_PATH') . "public\sell.xlsx";
			if (file_exists($bb)) {
				$aa = unlink($bb);
			}
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			$sheet->setCellValue('A1', '用户')
				->setCellValue('B1', '数目')
				->setCellValue('C1', '价格')
				->setCellValue('D1', '实到')
				->setCellValue('E1', '时间');
			$i = 2;
			foreach ($list as $k => $v) {
				$sheet->setCellValue('A' . $i, $v->user['us_account'])
					->setCellValue('B' . $i, $v['issue_num'])
					->setCellValue('C' . $i, $v['issue_price'])
					->setCellValue('D' . $i, $v['issue_yuan'])
					->setCellValue('E' . $i, $v['issue_add_time']);
				$i++;
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save('sell.xlsx');
			return "http://" . $_SERVER['HTTP_HOST'] . "/sell.xlsx";
		}


		$list = model("HangIssue")->chaxun($this->map, $this->order, $this->size);
		$num = model('HangIssue')->where($this->map)->sum('issue_num');
		$this->assign(array(
			'num' => $num,
			'list' => $list,
		));
		return $this->fetch();
	
	}
	//交易中
	public function order(){

		$this->map[] = ['deal_status','in',array(0,1)];
		// $this->map[] = ['deal_type', '=', 1];

		if(input('get.status')!=""){
			$this->map[] = ['deal_status','in',input('get.status')];
		}
		// if(input('get.or_number')!=""){
		// 	$this->map[] = ['or_number','in',input('get.or_number')];
		// }
		//时间
		if (input('get.start')) {
			$this->map[] = ['deal_add_time', '>=', input('get.start')];
		}
		if (input('get.end')) {
			$this->map[] = ['deal_add_time', '<=', input('get.end')];
		}
		
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_real_name|us_tel', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			if(input('get.type')!=""){
				if(input('get.type')==0){
					$this->map[] = ['us_to_id', '=', $us_id];
				}else{
					$this->map[] = ['us_id', '=', $us_id];
				}
				
			}else{
				$this->map[] = ['us_to_id|us_id', '=', $us_id];
			}
		}

		if (input('get.a') == 1) {
			$list = model("HangDeal")->where($this->map)->select();
			// $url = action('Excel/user', ['list' => $list]);
			$bb = env('ROOT_PATH') . "public\jiaoyi.xlsx";
			if (file_exists($bb)) {
				$aa = unlink($bb);
			}
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			$sheet->setCellValue('A1', '卖家账户')
				->setCellValue('B1', '手机号')
				->setCellValue('C1', '买家账户')
				->setCellValue('D1', '手机号')
				->setCellValue('E1', '数目')
				->setCellValue('F1', '价格')
				->setCellValue('G1', '实到')
				->setCellValue('H1', '状态')
				->setCellValue('I1', '添加时间');
			$i = 2;
			foreach ($list as $k => $v) {
				$sheet->setCellValue('A' . $i, $v->seller['us_account'])
					->setCellValue('B' . $i, $v->seller['us_tel'])
					->setCellValue('C' . $i, $v->buyer['us_account'])
					->setCellValue('D' . $i, $v->buyer['us_tel'])
					->setCellValue('E' . $i, $v['deal_num'])
					->setCellValue('F' . $i, $v['deal_price'])
					->setCellValue('G' . $i, $v['deal_yuan'])
					->setCellValue('H' . $i, $v['status_text'])
					->setCellValue('I' . $i, $v['deal_add_time']);
				$i++;
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save('jiaoyi.xlsx');
			return "http://" . $_SERVER['HTTP_HOST'] . "/jiaoyi.xlsx";
		}


		$list = model("HangDeal")->chaxun($this->map,$this->order,$this->size);
		$num = model('HangDeal')->where($this->map)->sum('deal_num');
		$this->assign(array(
			'list'=>$list,
			'num'=>$num,
		));
		return $this->fetch();

	}
	//交易完成
	public function order_fin(){
		$this->map[] = ['deal_status','=',2];

		if(input('get.status')!=""){
			$this->map[] = ['deal_status','in',input('get.status')];
		}
		//时间
		if (input('get.start')) {
			$this->map[] = ['deal_finish_time', '>=', input('get.start')];
		}
		if (input('get.end')) {
			$this->map[] = ['deal_finish_time', '<=', input('get.end')];
		}

		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_real_name|us_tel', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			if(input('get.type')!=""){
				if(input('get.type')==0){
					$this->map[] = ['us_to_id', '=', $us_id];
				}else{
					$this->map[] = ['us_id', '=', $us_id];
				}
				
			}{
				$this->map[] = ['us_to_id|us_id', '=', $us_id];
			}
		}
		if (input('get.a') == 1) {
			$list = model("HangDeal")->where($this->map)->select();
			// $url = action('Excel/user', ['list' => $list]);
			$bb = env('ROOT_PATH') . "public\finish.xlsx";
			if (file_exists($bb)) {
				$aa = unlink($bb);
			}
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			$sheet->setCellValue('A1', '卖家账户')
				->setCellValue('B1', '手机号')
				->setCellValue('C1', '买家账户')
				->setCellValue('D1', '手机号')
				->setCellValue('E1', '数目')
				->setCellValue('F1', '价格')
				->setCellValue('G1', '实到')
				->setCellValue('H1', '状态')
				->setCellValue('I1', '完成时间');
			$i = 2;
			foreach ($list as $k => $v) {
				$sheet->setCellValue('A' . $i, $v->seller['us_account'])
					->setCellValue('B' . $i, $v->seller['us_tel'])
					->setCellValue('C' . $i, $v->buyer['us_account'])
					->setCellValue('D' . $i, $v->buyer['us_tel'])
					->setCellValue('E' . $i, $v['deal_num'])
					->setCellValue('F' . $i, $v['deal_price'])
					->setCellValue('G' . $i, $v['deal_yuan'])
					->setCellValue('H' . $i, $v['status_text'])
					->setCellValue('I' . $i, $v['deal_finish_time']);
				$i++;
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save('finish.xlsx');
			return "http://" . $_SERVER['HTTP_HOST'] . "/finish.xlsx";
		}
		$list = model("HangDeal")->chaxun($this->map,$this->order,$this->size);
		$num = model('HangDeal')->where($this->map)->sum('deal_num');
		$this->assign(array(
			'list'=>$list,
			'num'=>$num,
		));
		return $this->fetch();

	}
	//详情
	public function xq(){
		$order = model("HangDeal")->detail(['id'=>input('get.id')]);
		// $rbm = $order['or_money'] * cache('setting')['dollar_rmb']/100;

		$this->assign(array(
			'info' => $order,
			// 'id'=>session('us_id'),
			// 'rbm' => $rbm,
		));
		return $this->fetch();
	}

}
