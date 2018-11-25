<?php
namespace app\mall\controller;
/**
 * 需要登录基类
 */
class Common extends Base {

	protected $mine;
	public function initialize() {
		parent::initialize();
		if ($this->is_login()) {
			$this->redirect('login/login');
		}
		$this->mine = model('User')->detail(['id'=>session('us_id')]);
		$this->assign('mine',$this->mine);
	}
	
	//判断登陆
	public function is_login() {
		if (!session('us_id') && session('us_id')<=0) {
			return true;
		}
		return false;
	}

}
