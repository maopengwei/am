<?php
namespace app\common\controller;

use think\Controller;
use think\Request;

class Base extends Controller {
	protected $order;
	protected $size;
	protected $map;
	public function initialize() {
		
		parent::initialize();
		// !cache('setting') && cache('setting',model('SysConfig')->getInfo());
		// !cache('calcu') && cache('calcu',db('calcu')->select());
		// !cache('jing') && cache('jing',db('jing')->select());
		cache('setting',model('SysConfig')->getInfo());
		cache('level',db('sys_level')->select());
		$this->order = 'id desc';
		$this->size = '20';
		$this->map = [];
	}
	protected function s_msg($msg = "成功", $data = "") {
		if ($msg == null) {
			$msg = "成功";
		}
		$this->result($data, 1, $msg, 'json');
	}
	protected function e_msg($msg = "失败", $code = 0, $data = "") {
		if ($msg == null) {
			$msg = "失败";
		}
		$this->result($data, $code, $msg, 'json');
	}
	protected function msg($data) {
		$msg = '';
		$code = 1;
		$dada = $data;
		if (is_array($data)) {
			$msg = $data['msg'];
			$code = $data['code'];
			$dada = key_exists('data', $data) ?: "";
		}
		$this->result($dada, $code, $msg, 'json');
	}
	
	public function _empty($name) {
		$request = request();
		$file = env('app_path') . $request->module() . '/view/' . lcfirst($request->controller()) . "/" . $name . '.' . ltrim(config('template.view_suffix'), '.');
		if (file_exists($file)) {
			return $this->fetch($name);
		} else {
			$this->redirect('Index/index');
		}
	}
	//网站维护
    public function system() {
        // if (cache('setting')['sys_status'] == 0) {
        //     $this->error('网站维护中');
        // }
    }
	public function is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {

            return true;
        }
        return false;
    }
}
