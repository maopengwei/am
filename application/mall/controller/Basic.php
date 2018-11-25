<?php
namespace app\mall\controller;


/**
 * 无需登陆的网页继承的控制器
 */
class Basic extends Base {
	public function initialize() {
		parent::initialize();
		
	    if(session('us_id')){
            $this->mine = model('User')->detail(session('us_id'));
            $this->assign('mine',$this->mine);
        }	
	}
}
