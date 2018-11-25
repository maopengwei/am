<?php
namespace app\admin\controller;

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
		$this->assign('request',$this->request);
		return $this->fetch();
	}
	// ------------------------------------------------------------------------

}
