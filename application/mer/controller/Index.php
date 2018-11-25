<?php
namespace app\mer\controller;

/**
 * @todo 首页操作
 */
class Index extends Common {
	// ------------------------------------------------------------------------
	public function index() {
		return $this->fetch();
	}

	// ------------------------------------------------------------------------
	public function welcome() {
		// 获取平台账户详情
		
		$pd_count = model("StoProd")->where('mer_id',session('mer_id'))->count();
		$order_today = model("StoOrderDetail")->where('mer_id',session('mer_id'))->whereTime('detail_add_time', 'today')->count();
		$order_jine = model("StoOrderDetail")->where('mer_id',session('mer_id'))->whereTime('detail_add_time', 'today')->sum('order_money');

		$order_count = model("StoOrderDetail")->where('mer_id',session('mer_id'))->count();
		$order_total = model("StoOrderDetail")->where('mer_id',session('mer_id'))->sum('order_money');

		$this->assign(array(
			
			'pd_count' => $pd_count,
			'order_today' => $order_today,
			'order_jine' => $order_jine,
			'order_count' => $order_count,
			'order_total' => $order_total,
		));
		return $this->fetch();
	}
	// ------------------------------------------------------------------------

}
