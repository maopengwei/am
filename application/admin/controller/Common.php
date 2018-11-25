<?php
namespace app\admin\controller;

use app\common\controller\Base;

/**
 * 基类
 */
class Common extends Base {

	public function __construct() {
		parent::__construct();

		if ($this->is_login()) {
			$this->redirect('login/logout');
		}
		if(session('ad_id')!=1 && $this->auth()){
			
			$this->error('您没有权限访问','/admin/index/index');
		}
	}
	public function is_login() {
		if (!session('ad_id')) {
			return true;
		}
		if (session('ad_id') <= 0) {
			return true;
		}
		return false;
	}
	
	public function auth() {

		$meth_name = strtolower(explode(".", $this->request->pathinfo())[0]);
		$meth_type = strtolower($this->request->method());
		$info = db('admin_rule')
			->where('name', $meth_name)
			->where('meth', $meth_type)
			->find();

		if (!$info) {
			return false;
		}
		if (in_array($info['id'], session('rules'))) {
			return false;
		}
		
		return true;
	}
	
}
