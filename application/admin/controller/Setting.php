<?php
namespace app\admin\controller;

use Cache;
use think\Db;
/**
 * @todo 配置信息管理
 */
class Setting extends Common {
	public function _initialize() {
		parent::_initialize();
	}
	public function drop(){
		Db::query("truncate table new_pro_msc");
		Db::query("truncate table new_pro_wal");
		Db::query("truncate table new_pro_tixian");
		Db::query("truncate table new_pro_transfer");
		Db::query("truncate table new_sto_order_detail");
		Db::query("truncate table new_sto_order");
		Db::query("truncate table new_hang_deal");
		Db::query("truncate table new_hang_issue");
		Db::query("truncate table new_user");
		$arr = [
			'us_account' => 'am1001',
			'us_real_name' => 'mao',
			'us_tel' => '13000000000',
			'us_pwd' => 'X89GtgVjrpo/XfbVw6YFz4B/ZJsafRyx4TK35XeP6tA=',
			'us_safe_pwd' => 'SfHJrseEQSLdM3w89nx1sBSBz1Hn2ipI1f1y+88JBrc=',
			'us_add_time' => '2018-10-18 09:38:00',
			'us_tree' => '0',
		];
		$rel = Db::name('user')->insert($arr);
		if($rel){
			return ['code'=>1,'msg' => '成功'];
		}else{
			return ['code'=>0,'msg'=>'失败'];
		}
		
	}


	// --- ---------------------------------------------------------------------
	//
	public function index() {
		if (is_post()) {
			$data = input('post.');
			model('SysConfig')->xiugai($data);
			$this->success('修改成功');
		}
		return $this->fetch();
	}
	//系统参数
	public function system() {

		if(is_post()){
			$data = input('post.');
			$rel = 0;
			if($data['type']==1){
				$rel = db('sys_level')->where('id',$data['i'])->setfield($data['key'],$data['val']);
			}elseif($data['type']==2){
				$rel = db('sys_sx')->where('id',$data['i'])->setfield($data['key'],$data['val']);
				
			}elseif($data['type']==3){
				$rel = db('sys_price')->where('id',$data['i'])->setfield($data['key'],$data['val']);
			}
			if($rel){
				Cache::clear();
			}
		}

		$this->assign(array(
			'list'=> Db::name('sys_level')->order('id asc')->select(),
			'll'=> Db::name('sys_sx')->order('id asc')->select(),
		));
		return $this->fetch();
	}
	//
	public function edit() {
		if (is_post()) {
			$data = input('post.');
			$rel = model('Calcu')->xiugai($data);
			return $rel;
		}

		$k = input('id') - 1;
		//dump($k);
		$this->assign(array(
			'k' => $k,
		));
		return $this->fetch();
	}

	/*-------------------轮播图*/
	public function shuff(){

		if (is_numeric(input('get.status'))) {
			$this->map[] = ['shuff_status', '=', input('get.status')];
		}
		if (input('get.keywords')) {
			$this->map[] = ['shuff_name', 'like', "%".input('get.keywords')."%"];
		}
		
		$list = model('Shuff')->chaxun($this->map,$this->order,$this->size);
		$this->assign(array(
			'list'=>$list,
		));
		return $this->fetch();
	}
	public function shuff_add(){
		if (is_post()) {
			$data = input('post.');
			// halt($data);
			$file = request()->file('file');

			if($file){
				$base = uploads($file);
				if($base['code']){
					$data['shuff_pic'] = $base['path'];
				}else{
					return $base;
				}
			}
			//验证器
			$validate = validate('Other');
			$res = $validate->scene('addshuff')->check($data);
			if (!$res) {
				$this->error($validate->getError());
			}

			$rel = model('Shuff')->tianjia($data);
			return $rel;
		}
		return $this->fetch();
	}
	public function shuff_edit(){
		
		if(is_post()){
			$data = input('post.');
			$file = request()->file('file');
			if($file){
				$base = uploads($file);
				if($base['code']){
					$data['shuff_pic'] = $base['path'];
				}else{
					return $base;
				}
			}
			$rel = model('Shuff')->update($data);
			return ['code'=>1,'msg'=>'修改成功'];
		}else{
			$this->assign('info',model("Shuff")->where('id',input('id'))->find());
			return $this->fetch();
		}
		
	}


	public function shuff_del(){
		if (input('post.id')) {
			$id = input('post.id');
		} else {
			$this->error('id不存在');
		}
		$info = db('shuff')->where('id',$id)->find();
		if ($info) {
			$rel = model('Shuff')->destroy($id);
			if ($rel) {
				$this->success('删除成功');
			} else {

				$this->error('请联系网站管理员');
			}
		} else {
			$this->error('数据不存在');
		}
	}


	//每日价格走势
	public function day(){
		if(is_post()){
			$d = input('post.');
			$info = Db::name('sys_price')->order('id desc')->find();
			$time = strtotime($info['time']);
			$date = $time+86400;
			$dt = date('m-d',$date);
			$dd = date('Y-m-d',$date);
			$arr = [
				'price' => input('price'),
				'price_time' => $dt,
				'time' => $dd,
			];
			$rel = Db::name('sys_price')->insert($arr);
			if($rel){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}

		$list = Db::name('sys_price')->order('id desc')->select();
		$this->assign(array(
			'list'=>$list,
		));
		return $this->fetch();
	}



	//项目文档
	public function api() {

		return $this->fetch();
	}
	public function document() {
		$path = env('ROUTE_PATH');
		$swagger = \Swagger\scan($path);
		header('Content-Type: application/json');
		echo $swagger;
	}
}
