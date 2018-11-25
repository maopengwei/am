<?php
namespace app\mer\controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @todo
 */
class Order extends Common {

	// ------------------------------------------------------------------------
	// 订单列表
	public function index() {
		if (is_post()) {

			$rst = model('Order')->xiugai([input('post.key') => input('post.value')], ['id' => input('post.id')]);
			return $rst;
		}
		$this->map[] = ['prod_zone','=',0];
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_tel', input('get.keywords'))->value('id');
			if ($us_id) {
				$this->map[] = ['us_id', '=', $us_id];
			}else{
				$this->map[] = ['us_id', '=', 0];
			}
		}
		
		$this->map[] = ['mer_id','=',session('mer_id')];
		if (input('get.status') != "") {
			$this->map[] = ['detail_status', '=', input('get.status')];
		}

		if (input('get.prod_name') != "") {
			$this->map[] = ['prod_name', 'like', "%".input('get.prod_name')."%"];
		}

		if (input('get.order_number') != "") {
			$this->map[] = ['order_number', '=', input('get.order_number')];
		}
		if (input('get.start')) {
			$this->map[] = ['detail_add_time', '>=', input('get.start')];
		}
		if (input('get.end')) {
			$this->map[] = ['detail_add_time', '<=', input('get.end')];
		}
		if (input('get.a') == 1) {
			$list = model("StoOrderDetail")->with('order')->where($this->map)->select();
			// $url = action('Excel/user', ['list' => $list]);
			$bb = env('ROOT_PATH') . "public\order.xlsx";
			if (file_exists($bb)) {
				$aa = unlink($bb);
			}
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			
			$sheet->setCellValue('A1', '订单编号')
				->setCellValue('B1', '客户姓名')
				->setCellValue('C1', '店铺')
				->setCellValue('D1', '产品')
				->setCellValue('E1', '产品类型')
				->setCellValue('F1', '总价')
				->setCellValue('G1', '单价')
				->setCellValue('H1', '数量')
				->setCellValue('I1', '状态')
				->setCellValue('J1', '添加时间');
			$i = 2;
			foreach ($list as $k => $v) {
				$sheet->setCellValue('A' . $i, $v['order_number'])
					->setCellValue('B' . $i, $v->order->user['us_account'])
					->setCellValue('C' . $i, $v['mer_text'])
					->setCellValue('D' . $i, $v['prod_name'])
					->setCellValue('E' . $i, $v['zone_text'])
					->setCellValue('F' . $i, $v['order_money'])
					->setCellValue('G' . $i, $v['prod_price'])
					->setCellValue('H' . $i, $v['prod_num'])
					->setCellValue('I' . $i, $v['status_text'])
					->setCellValue('J' . $i, $v['detail_add_time']);
				$i++;
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save('order.xlsx');
			return "http://" . $_SERVER['HTTP_HOST'] . "/order.xlsx";
		}

		$list = model('StoOrderDetail')->chaxun($this->map, $this->order, $this->size);
		$this->assign(array(
			'list' => $list,
		));
		return $this->fetch();
	}

	public function detail() {

		
		$id = input('id');
		$info = model('StoOrderDetail')->detail(['id'=>$id]);
		if (is_post()) {
			$da  = input('post.');
			if($info['detail_status']<1 || $info['detail_status']>3){
				return ['code'=>0,'msg'=>'该订单状态不支持发货'];
			}
			$da['detail_status'] = 2;
			$da['detail_delive_time'] = date('Y-m-d H:i:s');
			$res = model("StoOrderDetail")->update($da);
			return ['code'=>1,'msg'=>'成功'];
		}
		$id = input('get.id');
		$info = model('StoOrderDetail')->detail(['id'=>$id]);
		$this->assign(array(
			'info' => $info,
		));
		return $this->fetch();
	}

	public function finish(){
		if(is_post()){
			$id = input('post.id');
			$info = model('StoOrderDetail')->detail(['id'=>$id]);
			$time = unixtime('day',-10);
			$ten = date('Y-m-d H:i:s',$time);
			
			if($info['detail_status']!=2 || $info['detail_delive_time']>$ten ){
				return ['code'=>0,'msg'=>'该订单不是待收货状态或发货时间小于10天'];
			}
			$data = array(
	            'detail_finish_time' => date('Y-m-d H:i:s'),
	            'detail_status' => 3,
	        );
	       
	        $rel = model('StoOrderDetail')->where('id',$id)->update($data);
	        if ($rel) {
	            $this->success('确定收货成功');
	        } else {
	            $this->error('确定收货失败');
	        }
	    }
		
	}	

	public function del(){
		if (input('post.id')) {
            $id = input('post.id');
        } else {
            $this->error('id不存在');
        }
        $info = model('StoOrderDetail')->get($id);
        if ($info) {
            $rel = model('StoOrderDetail')->destroy($id);
            if ($rel) {
                $this->success('删除成功');
            } else {
                $this->error('请联系网站管理员');
            }
        } else {
            $this->error('数据不存在');
        }
	}
}
