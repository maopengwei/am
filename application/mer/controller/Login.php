<?php
namespace app\mer\controller;

use app\common\controller\Base;
class Login extends Base {
	// ------------------------------------------------------------------------
	public function __construct() {
		parent::__construct();
		$this->system();
	}
	/*-----------------------登陆*/
	public function index() {
		if (is_Post()) {
           
            $data = input('post.');
            $us = model('User');

            $flag = 0;
            // $count1 = $us->where('us_tel',$data['us_tel'])->count();
            $count2 = $us->where('us_account',$data['us_tel'])->count();
            // if($count1){
            //     $info = $us->where('us_tel',$data['us_tel'])->where('us_pwd',mine_encrypt($data['us_pwd']))->find();
            //     if(!$info){
            //         $this->error('密码错误');
            //     }else{
            //         $flag = 1;
            //     }
            // }
            if($count2){
                $info = $us->where('us_account',$data['us_tel'])->where('us_pwd',mine_encrypt($data['us_pwd']))->find();
                if(!$info){
                    $this->error('密码错误');
                }else{
                    $flag = 1;
                }
            }

            if($flag){
                if ($info['us_status'] == 0) {
                    $this->error('账号被禁用!');
                }
                if($info['us_is_mer']!=1){
                	$this->error('只有商家能登陆商家端');
                }
                $mer = model('StoMer')->detail(['us_id'=>$info['id']]);	
                if(!$mer){
                	model("StoMer")->tianjia(['us_id'=>$info['id']]);
                	$mer = model('StoMer')->detail(['us_id'=>$info['id']]);	
                	halt($mer);
                }
                session('mer_us_id',$info['id']);
                session('mer_id',$mer['id']);
                $this->success('登录成功');
            }else{
                $this->error('不存在此用户');
            }
        }
        return $this->fetch();
	}

	/*-----------------------是否手机*/
	protected function is_mobile() {
		if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
			return true;
		}
		if (isset($_SERVER['HTTP_VIA'])) {
			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		}
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile', 'MicroMessenger');
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
				return true;
			}
		}
		return false;
	}
	// ------------------------------------------------------------------------
	public function logout() {
		session('admin', null);
		session('rules', null);
		session(null);
		$this->redirect('login/index');
	}

}
