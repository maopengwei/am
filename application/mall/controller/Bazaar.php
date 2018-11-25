<?php
namespace app\mall\controller;

/**
 * 市场控制器
 */
class Bazaar extends Common {
	//市场首页列表
	public function index() {
		// $arr = explode(',',$this->mine['us_level_text']);
		// $this->assign('arr',$arr);
		return $this->fetch();
	}
	//团队
	public function team(){
		if (is_post()) {
			$info = model('User')->where('us_account|us_tel|us_real_name', input('post.us_account'))->field('id,us_path,us_pid,us_account,us_tel')->find();
			if (!$info) {
				return ['code' => 0,'msg' => "查无此人",];
			}
			$base = array(
				'id' => $info['id'],
				'pId' => $info['us_pid'],
				'name' => $info['us_account'] . "," . $info['us_tel'],
			);
			$znote[] = $base;
			$where[] = array('us_path', 'like', $info['us_path'] . "," . $info['id'] . "%");
			$list = Model('User')->where($where)->field('id,us_pid,us_account,us_tel')->select();
			foreach ($list as $k => $v) {
				$base = array(
					'id' => $v['id'],
					'pId' => $v['us_pid'],
					'name' => $v['us_account'] . "," . $v['us_tel'],
				);
				$znote[] = $base;
			}
			return [
				'code' => 1,
				'data' => $znote,
			];
		}
	}
	//节点图
	public function node() {
		if (is_post()) {
			$us_account = input('post.us_account');
			$level = input('post.level');
			if ($us_account) {
				$info = model('User')->where('us_account|us_tel', input('post.us_account'))->find();
				if (!$info) {
					return [
						'code' => 0,
						'msg' => '该用户不存在',
					];
				}
				$arr = explode(',',$info['us_tree']);
				if($info['id']!=session('us_id') && !in_array(session('us_id'),$arr)){
					return [
						'code' => 0,
						'msg' => '该用户不在我的团队中',
					];
				}
			}else{
				return [
					'code' => 0,
					'msg' => '该用户不存在',
				];
			}

			$znote = jiedian();
			$this->map[] = ['us_tree', 'like', $info['us_tree'] . "," . $info['id'] . "%"];
			$this->map[] = ['us_tree_long', '<=', $info['us_tree_long'] + 2];
			$list = db('user')->where($this->map)->select();
			array_push($list, $info);
			for ($i = 0; $i < 8; $i++) {
				if (isset($list[$i])) {
					$arr = $list[$i];
					$key = is_level($level);

					if($arr[$key]){
						$status = '已激活';
					}else{
						$status = '未激活';
					}
					$qu_ye = qu_yeji($arr['id'],$level);
					
					// $level_text = explode(',',$arr['us_level_text']);
					// if(in_array($level,$level_text)){
					// 	$status = '已激活';
					// }else{
					// 	$status = '未激活';
					// }
					
					$us_tree_qu = str_split($arr['us_tree_qu']);
					$qu = array_reverse($us_tree_qu);
					$length = $arr['us_tree_long'] - $info['us_tree_long'];
					if ($length == 0) {
						$key = 0;
					} elseif ($length == 1) {
						$key = 2 * $length + $arr['us_qu'] - 1;
					} else {
						$key = 2 * $length + $arr['us_qu'] + $qu[1] * 2 - 1;
					}


					$znote[$key]['name'] = $arr['us_account'];
					$znote[$key]['tel'] = $arr['us_tel'] . "(" . $arr['us_real_name'] . ")";
					$znote[$key]['zuo'] = "左:" . $qu_ye['l']['money'] .  "," . $qu_ye['l']['num'];
					$znote[$key]['you'] = "右:" . $qu_ye['r']['money'] .  "," . $qu_ye['r']['num'];
					$znote[$key]['level'] = cache('level')[$level]['cal_name'].":".$status;
					$znote[$key]['k'] = $arr['id'];
					$znote[$key]['p'] = $arr['us_aid'];
					if ($list[$i]['us_head_pic']) {
						$znote[$key]['source'] = $arr['us_head_pic'];
					}
					
				}
			}
			return [
				'code' => 1,
				'data' => $znote,
				'ptel' =>$info['atel'],
			];
		}
	}

	/*-------------报单列表*/
	public function center(){
		if(is_post()){
			$this->map[] = ['us_center', '=', session('user')['id']];
			$this->size = 20;
			$list = model("User")->chaxun($this->map, $this->order, $this->size);
			foreach ($list as $k => $v) {
				$list[$k]['time'] = substr($v['us_add_time'],0,10);
				$list[$k]['day'] = substr($v['us_add_time'],10,20);
			}
			return [
				'code' => 1,
				'data' => $list,
			];
		}
		return $this->fetch();
	}

	/*-------------人员详情*/
	public function center_xq(){
		$info = model('User')->detail(input('get.id'));
		$this->assign('info',$info);
		return $this->fetch();
	}
	public function add(){
		if (is_post()) {
			$d = input('post.');

			$validate = validate('Front');
			$res = $validate->scene('addUser')->check($d);
			if (!$res) {
				$this->error($validate->getError());
			}

			$key = is_level($d['us_level']);
			if(!$this->mine[$key]){
				$this->error('您没有激活该等级');
			}

			//短信验证码
			// $code_info = cache($data['us_tel'] . 'code') ?: "";
			// if (!$code_info) {
			// 	$this->error('请重新发送验证码');
			// } elseif ($data['sode'] != $code_info) {
			// 	$this->error('验证码不正确');
			// }
			//消耗
			$calcu = cache('level')[$d['us_level']];


			if($calcu['cal_status']==0){
				$this->error('该等级被暂时关闭');
			}


			if($this->mine['us_reg']<$calcu['cal_money']){
				$this->error('注册Doken不足');
			}

			//父账号
			// $pinf = model("User")->where('us_account', $data['p_acc'])->find();
			// if (count($pinf)) {
			// 	$data['us_pid'] = $pinf['id'];
			// 	$data['us_path'] = $pinf['us_path'] . ',' . $pinf['id'];
			// 	$data['us_path_long'] = $pinf['us_path_long'] + 1;
			// } else {
			// 	$this->error(__('推荐人不存在'));
			// }
			
			$d['us_pid'] = $this->mine['id'];
			$d['us_path'] = $this->mine['us_path'] . ',' . $this->mine['id'];
			$d['us_path_long'] = $this->mine['us_path_long'] + 1;
			
			//节点人
			$ainfo = model('User')->get($d['us_aid']);
			if (count($ainfo)) {
				$d['us_aid'] = $ainfo['id'];
				$d['us_tree'] = $ainfo['us_tree'] . ',' . $ainfo['id'];
				$d['us_tree_long'] = $ainfo['us_tree_long'] + 1;
				$d['us_tree_qu'] = $ainfo['us_tree_qu'].$d['us_qu'];
			}else{
				$this->error('节点人不存在');
			}


			// if($da['us_center']){
			// 	$center = model('User')->where('us_tel',$da['us_center'])->find();
			// 	if(!count($center)){
			// 		$this->error('该报单中心不存在');
			// 	}
			// 	if($center['us_is_center']==0){
			// 		$this->error('该报单中心不是报单中心');
			// 	}
			// 	$da['us_center'] = $center['id'];
			// }

			//左右区位置限制
			if($d['us_qu'] ==1 ){

				$zuo = model("User")->where('us_aid',$ainfo['id'])->where('us_qu',0)->find();
				if(!$zuo){
					$this->error('必须先注册左区');
				}
			}
			$wei = model("User")->where('us_aid',$d['us_aid'])->where('us_qu',$d['us_qu'])->find();
			if($wei){
				$this->error('该位置已被注册');
			}
			$d[$key] = 1;
			$rel = model('User')->tianjia($d);
			if($rel['code']){

				//减去我的相应Doken
				model('ProReg')->tianjia(session('us_id'),$calcu['cal_money'],1);
				//业绩  对碰 管理 
				fan($rel['id'],$d['us_level'],$calcu['cal_money']);
				//节点奖励
				node($d['us_aid'],$calcu['cal_money'],$d['us_qu']);
				//静态添加
				model('StaFa')->tianjia($rel['id'],$d['us_level']);

				//互助奖励
				if($d['us_qu']==0){
					model('User')->where('id',$ainfo['id'])->setfield('us_is_hu',1);
				}else{
					if($ainfo['us_is_hu']==3){
						zhu($rel['id'],$calcu['cal_money']);
					}
					model('User')->where('id',$ainfo['id'])->setfield('us_is_hu',2);
				}
			}
			return $rel;
		}
		$us_aid = input('get.us_aid');
		$us_qu = input('get.qu');

		$ainfo = model('User')->get($us_aid);

		// $level = explode(',',$this->mine['us_level_text']);
		$list = [];
		// foreach ($level as $k => $v) {
		// 	$list[$k]['name'] = cache('level')[$v]['cal_name'];
		// 	$list[$k]['level'] = $v;
		// }
		$this->assign(array(
			'ainfo' => $ainfo,
			'us_qu' => $us_qu,
			'list' => $list,
		));
		return $this->fetch();
	}


	public function up(){
		
		if(is_post()){
			$id = session('us_id');
			$d = input('post.');
			
			if(mine_encrypt($d['us_safe_pwd']) != $this->mine['us_safe_pwd']){
                $this->error('安全密码不正确');
            }

            if($d['level']==''){
            	$this->error('请选择激活等级');
            }
            $key = is_level($d['level']);

            if($this->mine[$key]){
            	$this->error('您已经激活该等级');
            }
            $calcu = cache('level')[$d['level']];

            if($calcu['cal_status']==0){
				$this->error('该等级被暂时关闭');
			}
			
            if($this->mine['us_reg']<$calcu['cal_money']){
            	$this->error('您的注册Doken不足');
            }

           	$rel = model('User')->where('id',$id)->setfield($key,1);
           	if($rel){
           		//扣Doken
           		// model('ProReg')->tianjia($id,$calcu['cal_money'],4);

           		// 节点人消费返利   
           		fan($id,$d['level'],$calcu['cal_money']);

           		// 自己销售返利
				fan_self($id,$d['level']);

				//自己获得推荐奖励
				$node = model("User")->where('us_aid',$id)->where($key,1)->select();
				if(count($node)==1){
					node($id,$calcu['cal_money']);
				}elseif(count($node)==2){
					node($id,$calcu['cal_money']);
					node($id,$calcu['cal_money'],1);
				}

				//推荐人获得推荐奖励
				$ainfo = model('User')->detail($this->mine['us_aid']);
				if($ainfo[$key]==1){
					$anode = model("User")->where('us_aid',$ainfo['id'])->where($key,1)->select();
					if(count($anode)==1){
						node($ainfo['id'],$calcu['cal_money']);
					}elseif(count($anode)==2){
						node($ainfo['id'],$calcu['cal_money'],1);
					}
				}

				//静态发放
				$sta = model('StaFa')->tianjia($id,$d['level']); //静态发放
				// $sta = model('StaFa')->where('us_id',$id)->find(); 
				// if(count($sta)){
				// 	$arr = [
				// 		'fa_all' => $sta['fa_all']+$calcu['cal_top'],
				// 		'fa_res' => $sta['fa_res']+$calcu['cal_top'],
				// 		'fa_day' => $sta['fa_day']+$calcu['cal_day_money'],
				// 		'fa_status' => 0,
				// 		'id'=>$sta['id'],
				// 	];
				// 	model("StaFa")->update($arr);
				// }else{
				// 	$sta = model('StaFa')->tianjia($id,$d['level']); //静态发放
				// }

           		$this->success('激活成功');
           	}else{
           		$this->error('激活失败');
           	}
		}

		// $arr = explode(',',$this->mine['us_level_text']);
		return $this->fetch();
	}
}