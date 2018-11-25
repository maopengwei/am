<?php
namespace app\mer\controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @todo 会员管理 查看，状态变更，密码重置
 */
class User extends Common {

	// ------------------------------------------------------------------------
	//用户列表
	public function index() {
		if (is_post()) {
			$rst = model('User')->xiugai([input('post.key') => input('post.value')], ['id' => input('post.id')]);
			return $rst;
		}

		if (is_numeric(input('get.status'))) {
			$this->map[] = ['us_status', '=', input('get.status')];
		}
		if (is_numeric(input('get.mer'))) {
			$this->map[] = ['us_is_mer', '=', input('get.mer')];
		}
		if (is_numeric(input('get.level'))) {
			$this->map[] = ['us_level', '=', input('get.level')];
		}
		if (is_numeric(input('get.type'))) {
			$this->map[] = ['us_type', '=', input('get.type')];
		}
		if (input('get.keywords')) {
			$this->map[] = ['us_tel|us_account|us_real_name', '=', input('get.keywords')];
		}
		if (input('get.start')) {
			$this->map[] = ['us_add_time', '>=', input('get.start')];
		}
		if (input('get.end')) {
			$this->map[] = ['us_add_time', '<=', input('get.end')];
		}

		if (input('get.p_acc')) {
			$pinfo = model("User")->where('us_account',input("get.p_acc"))->field('id')->find();
			if(count($pinfo)){
				$this->map[] = ['us_pid','=',$pinfo['id']];
			}else{
				$this->map[] = ['us_pid','=',99999];
			}
		}
		if (input('get.a_acc')) {
			$ainfo = model("User")->where('us_account',input("get.a_acc"))->field('id')->find();
			if(count($ainfo)){
				$this->map[] = ['us_aid','=',$ainfo['id']];
			}else{
				$this->map[] = ['us_aid','=',99999];
			}
		}

		
		if (input('get.a') == 1) {
			$list = model("User")->where($this->map)->select();
			// $url = action('Excel/user', ['list' => $list]);
			$bb = env('ROOT_PATH') . "public\user.xlsx";
			if (file_exists($bb)) {
				$aa = unlink($bb);
			}
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setCellValue('A1', '账户名')
				->setCellValue('B1', '真实姓名')
				->setCellValue('C1', '电话号码')
				->setCellValue('D1', '购物币')
				->setCellValue('E1', '佣金')
				->setCellValue('F1', '添加时间');
			$i = 2;
			foreach ($list as $k => $v) {
				$sheet->setCellValue('A' . $i, $v['us_account'])
					->setCellValue('B' . $i, $v['us_real_name'])
					->setCellValue('C' . $i, $v['us_tel'])
					->setCellValue('D' . $i, $v['us_wallet'])
					->setCellValue('E' . $i, $v['us_msc'])
					->setCellValue('F' . $i, $v['us_add_time']);
				$i++;
			}
			$writer = new Xlsx($spreadsheet);
			$writer->save('user.xlsx');
			return "http://" . $_SERVER['HTTP_HOST'] . "/user.xlsx";
		}

		$list = model('User')->chaxun($this->map, $this->order, $this->size);
		$this->assign(array(
			'yuming' => $_SERVER['HTTP_HOST'],
			'list' => $list,
		));
		return $this->fetch();
	}

	//添加
	public function add() {
		if (is_post()) {
			$da = input('post.');
			$validate = validate('Admin');
			$res = $validate->scene('addUser')->check($da);
			if (!$res) {
				$this->error($validate->getError());
			}

			/*$calcu = cache('calcu')[$da['us_type']];
				if($calcu['cal_status']==0){
				$this->error('该投资等级处于关闭状态');
			}*/
			
			//类型 1金卡0代言
			if($da['us_type'] ==1 ){
				$money = cache('setting')['pv']; 
			}else{
				$money = cache('setting')['pb'];
			}

			if ($da['p_tel']) {
				$pinfo = model("User")->where('us_tel', $da['p_tel'])->find();
				if (count($pinfo)) {
					$da['us_pid'] = $pinfo['id'];
					$da['us_path'] = $pinfo['us_path'] . ',' . $pinfo['id'];
					$da['us_path_long'] = $pinfo['us_path_long'] + 1;
				} else {
					$this->error('推荐人不存在');
				}
			} else {
				$da['us_pid'] = 0;
				$da['us_path'] = 0;
				$da['us_path_long'] = 0;
			}
			// halt($da);
			//节点人
			$ainfo = model('User')->get($da['us_aid']);
			if (count($ainfo)) {

				$da['us_aid'] = $ainfo['id'];
				$da['us_tree'] = $ainfo['us_tree'] . ',' . $ainfo['id'];
				$da['us_tree_long'] = $ainfo['us_tree_long'] + 1;
				$da['us_tree_qu'] = $ainfo['us_tree_qu'].$da['us_qu'];

			}else{
				$this->error('节点人不存在');
			}
			if($da['us_qu'] ==1 ){
				$zuo = model("User")->where('us_aid',$ainfo['id'])->where('us_qu',0)->find();
				if(!$zuo){
					$this->error('必须先注册左区');
				}
			}
			$wei = model("User")->where('us_aid',$da['us_aid'])->where('us_qu',$da['us_qu'])->find();
			if($wei){
				$this->error('该位置已被注册');
			}
			//报单中心
			if($da['us_center']){
				$center = model('User')->where('us_tel',$da['us_center'])->find();
				if(!count($center)){
					$this->error('该报单中心不存在');
				}
				if($center['us_is_center']==0){
					$this->error('该报单中心不是报单中心');
				}
				$da['us_center'] = $center['id'];
			}
				
			$rel = model('User')->tianjia($da);
			// $rel = [
			// 	'code'=>1,
			// 	'id' => 19,
			// ];
			if($rel['code']){
				
				if($pinfo['us_is_tui']==0){
					model('User')->where('id',$pinfo['id'])->setfield('us_is_tui',1);
				}

				//业绩  层碰 管理 
				yeji($rel['id'],$money);


				model('StaFa')->tianjia($rel['id']);
				
				//报单中心
				if($da['us_center']){
					$num = $money *  cache('setting')['center'] / 100;
					if($center['us_is_center']==1){
						one_to_two($da['us_center'],$num,1);
					}
				}
			}
			return $rel;
		}

		$us_aid = input('get.us_aid');
		$us_qu = input('get.qu');

		$ainfo = model('User')->get($us_aid);
		$this->assign(array(
			'ainfo' => $ainfo,
			'us_qu' => $us_qu,
		));
		return $this->fetch();
	}

	//升级等级
	public function level(){
		$info = model('User')->get(input('id'));
		if(is_post()){
			$data = input('post.');

			if($info['us_level']==0){
				if($data['us_level']==1){
					$type = 1;
				}elseif($data['us_level']==2){
					$type = 3;
				}
			}else{
				$type = 15;
			}
			$money = buy_type($type);
			$note = buy_note($type);
				
			$rel = model('PayRecord')->tianjia(5,$info['id'],$money,$type,$note);
			if($rel){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
			
		}
		$this->assign('info', $info);
		return $this->fetch();
	}
	//升级经销商
	public function up(){
		$info = model('User')->get(input('id'));
		if(is_post()){
			$data = input('post.');
			$aa = get_type($info['us_jibie'],$data['us_jibie']-1);
			if($aa['code']){
				$money = buy_type($aa['type']);
				$note = buy_note($aa['type']);
				$rel = model('PayRecord')->tianjia(5,$info['id'],$money,$aa['type'],$note);
				if($rel){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
			}else{
				return $aa;
			}
			
		}
		$this->assign('info', $info);
		return $this->fetch();
	}


	/*
	
	 		$request = input('');
            $info = model('user')->get($request['id']);
            $length = strlen($info['path']);
            if ($request['refer'] == "空") {
                $pa = 0;
                $newParent['id'] = 0;
            } else {
                $newParent = model('user')->where('us_tel', $request['refer'])->find();
                if (!$newParent) {
                    $this->error('您要修改的父账号不存在');
                }
                if ($newParent['id'] == $info['us_referrer']) {
                    $this->error('您的父账号并没有修改');
                }
                if (bypassAccount($newParent['path'], $info['id'])) {
                    $this->error('您要修改的父账号是您的子账号');
                }
                $pa = $newParent['path'] . "," . $newParent['id'];
            }

            $where = array(
                'path' => array('like', $info['path'] . "," . $info['id'] . "%"),
            );
            $list = db('user')->where($where)->select();


            if ($list) {
                
                foreach ($list as $k => $v) {
                    $path = substr($v['path'], $length);
                    $newPath = $pa . $path;
                    db('user')->where('id', $v['id'])->setfield('path', $newPath);
                }
            }
            $data = array(
                'us_referrer' => $newParent['id'],
                'path' => $pa,
            );
            db('user')->where('id', $info['id'])->update($data);


	 */
	//修改
	public function edit() {
		$info = model('User')->detail(input('id'));
		if (is_post()) {
			$data = input('post.');
			if ($data['us_pwd'] != "") {
				$data['us_pwd'] = mine_encrypt($data['us_pwd']);
			} else {
				unset($data['us_pwd']);
			}
			if ($data['us_safe_pwd'] != "") {
				$data['us_safe_pwd'] = mine_encrypt($data['us_safe_pwd']);
			} else {
				unset($data['us_safe_pwd']);
			}
			//
			if($info['center_text']!=$data['us_center']){
				$center = model('User')->where('us_account', $data['us_center'])->find();
				if(!$center){
					$this->error('该报单中心不存在');
				}
				if($center['us_is_center']!=1){
					$this->error('该用户不是报单中心');
				}
				$array = explode(',',$info['us_path']);
				if(!in_array($center['id'],$array)){
					$this->error('该报单中心不是我的上级中人');
				}
				$data['us_center'] = $center['id'];
			}else{
				unset($data['us_center']);
			}


			if($data['p_acc'] && $info->parent['us_account']!=$data['p_acc']){
				
				$length = strlen($info['us_path']);
	            
                $newParent = model('User')->where('us_account', $data['p_acc'])->find();
                if (!$newParent) {
                    $this->error('您要修改的父账号不存在');
                }
               
                if (in_array($info['id'],explode(',',$newParent['us_path']))) {
                    $this->error('您要修改的父账号是您的子账号');
                }
                $pa = $newParent['us_path'] . "," . $newParent['id'];


                $where[] =['us_path','like',$info['us_path'] . "," . $info['id'] . "%"];
	               
	            $list = db('user')->where($where)->select();

	            if ($list) {
	                foreach ($list as $k => $v) {
	                    $path = substr($v['us_path'], $length);
	                    $newPath = $pa . $path;
	                    db('user')->where('id', $v['id'])->setfield('us_path', $newPath);
	                }
	            }

	            $data['us_pid'] = $newParent['id'];
	            $data['us_path'] = $pa;
			}
			unset($data['p_acc']);
			$rel = model('User')->update($data);
			if($rel){
				$this->success('修改成功');
			}else{
				$this->error('您没有修改任何东西');
			}
		}
		$this->assign('info', $info);
		return $this->fetch();
	}


	/*---------送币*/
	public function song(){
		$info = model('User')->get(input('id'));
		if(is_post()){
			$da = input('post.');

			if($da['song_type']==1){
				$key = 'ProWal';
				if($da['song_num']<0){
					$num = abs($da['song_num']);
					$type = 10;
				}else{
					$num = abs($da['song_num']);
					$type = 9;
				}
			}else{
				$key = 'ProMsc';
				if($da['song_num']<0){
					$num = abs($da['song_num']);
					$type = 5;
				}else{
					$num = abs($da['song_num']);
					$type = 4;
				}
			}
			// dump($da);
			// dump($num);
			// dump($type);
			// halt($key);
			$rel = model($key)->tianjia($da['id'],$num,$type);
			if($rel){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}
		$this->assign('info', $info);
		return $this->fetch();
	}



	//团队
	public function team() {
		if (is_post()) {
			$info = model('User')->where('us_account|us_tel|us_real_name', input('post.us_account'))->field('id,us_path,us_pid,us_account,us_tel')->find();
			if (!$info) {
				return [
					'code' => 0,
					'msg' => "查无此人",
				];
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
		if(input('get.id')){
			$this->assign('us_account',input('get.id'));
		}
		return $this->fetch();
	}

	//配送员列表
	public function courier() {
		if (is_post()) {
			$rst = model('Courier')->xiugai([input('post.key') => input('post.value')], ['id' => input('post.id')]);
			if ($rst) {
				$this->success('修改成功');
			} else {
				$this->error('修改失败');
			}
			return $rst;
		}
		if (input('get.keywords')) {
			$this->map[] = ['co_number|co_name|co_tel', '=', trim(input('get.keywords'))];
		}
		if (is_numeric(input('get.co_status'))) {
			$this->map[] = ['co_status', '=', input('get.co_status')];
		}

		$list = model('Courier')->chaxun($this->map, $this->order, $this->size);
		$this->assign(array('list' => $list));
		return $this->fetch();
	}
	

	//地址列表
	public function addr() {
		if (is_post()) {
			$rst = model('User_addr')->xiugai([input('post.key') => input('post.value')], ['id' => input('post.id')]);
			if ($rst) {
				$this->success('修改成功');
			} else {
				$this->error('修改失败');
			}
			return $rst;
		}
		if (input('get.id')) {
			$this->map[] = ['us_id', '=', input('get.id')];
		} else {
			$this->error("非法操作");
		}
		$list = model('User_addr')->chaxun($this->map, $this->order, $this->size);
		$this->assign(array(
			'list' => $list,
			'name' => model('User')->where('id', input('get.id'))->value('us_account'),
		));
		return $this->fetch();

	}
	//地址修改
	public function addr_edit() {
		if (is_post()) {
			$data = input("post.");
			$validate = validate('Verify');
			$rst = $validate->scene('editAddr')->check($data);
			if (!$rst) {
				$this->error($validate->getError());
			}
			unset($data['id']);
			$rel = model('Store')->xiugai($data, ['id' => input('post.id')]);
			if ($rel) {
				$this->success('修改成功');
			} else {
				$this->error('您未进行修改');
			}
		}
		$info = model("User_addr")->get(input('get.id'));
		$this->assign(array(
			'info' => $info,
		));
		return $this->fetch();
	}
	public function position() {
		return $list = model("User_addr")->where('us_id', input('post.us_id'))->select();
	}
	public function is_jing() {
		$id = input('get.id');
		$info = model("User")->get($id);
		if ($info['us_is_jing'] != 1) {
			return [
				'code' => 0,
				'msg' => '该用户不是待进入节点图状态',
			];
		}
		if ($info['us_jibie'] == 0) {
			return [
				'code' => 0,
				'msg' => '该用户不是经销商',
			];
		}
		return [
			'code' => 1,
		];
	}
	public function tupu() {
		if (is_post()) {
			if(input('post.us_account') ==1){
				$info = model("User")->detail(1);
			}else{
				$info = model('User')->where('us_account|us_tel|us_real_name', input('post.us_account'))->find();
				if (!$info) {
					return [
						'code' => 0,
						'msg' => '该用户不存在',
					];
				}
			}
			$znote = jiedian();

			$this->map[] = ['us_tree', 'like', $info['us_tree'] . "," . $info['id'] . "%"];
			$this->map[] = ['us_tree_long', '<=', $info['us_tree_long'] + 2];

			$list = db('user')->where($this->map)->select();
			array_push($list, $info);
			/*
				0
				00
				01
				000
				001
				010
				001
			*/

			for ($i = 0; $i < 8; $i++) {
				if (isset($list[$i])) {
					$arr = $list[$i];
					// dump($arr['us_account']);
					$us_tree_qu = str_split($arr['us_tree_qu']);
					$qu = array_reverse($us_tree_qu);
					// dump($qu);
					if($arr['us_type']){
						$type = '金卡会员';
					}else{
						$type = '代言金卡';
					}


					$length = $arr['us_tree_long'] - $info['us_tree_long'];
					// dump($length);
					if ($length == 0) {
						$key = 0;
					} elseif ($length == 1) {
						$key = 2 * $length + $arr['us_qu'] - 1;
					} else {
						$key = 2 * $length + $arr['us_qu'] + $qu[1] * 2 - 1;
					}

					$znote[$key]['name'] = $arr['us_account'];
					$znote[$key]['tel'] = $arr['us_tel'] . "(" . $arr['us_real_name'] . ")";
					$znote[$key]['zuo'] = "左:" . $arr['us_res_zuo'] .  "," . $arr['us_per_zuo'];
					$znote[$key]['you'] = "右:" . $arr['us_res_you'] . "," . $arr['us_per_you'];
					$znote[$key]['level'] = $type ."  ".cache('level')[$arr['us_level']]['cal_name'];
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
		} else {
			$id = input('get.id');
			$us_account = 1;
			if ($id) {
				$us_account = model('User')->where('id',$id)->value('us_account');
			}
			$this->assign(array(
				'us_account' => $us_account,
			));
			return $this->fetch();
		}
	}
	//所有删除
	public function del() {
		if (input('post.id')) {
			$id = input('post.id');
		} else {
			$this->error('id不存在');
		}
		$info = model('User')->get($id);
		if ($info) {

			$time = unixtime('day',-1);
			$day = date('Y-m-d H:i:s',$time);
			if($info['us_add_time']<=$day){
				$this->error('该用户注册时间已超过一天');
			}

			$child1 = model("User")->where('us_pid',$info['id'])->find();
			if(count($child1)){
				$this->error('该用户已推荐别的会员');
			}
			$child2 = model("User")->where('us_aid',$info['id'])->find();
			if(count($child2)){
				$this->error('已有会员被安置在此人下面');
			}
			$arr = explode(',',$info['us_tree']);
			$qu = str_split($info['us_tree_qu']);
			if($info['us_type']){
				$money = cache('setting')['pv'];
			}else{
				$money = cache('setting')['pb'];
			}
			
			foreach ($arr as $k => $v) {
				if($k>0){
					if($qu[$k]){
						model('User')->where('id',$v)->dec('us_res_you',$money)->dec('us_per_you',1)->update();
					}else{
						model('User')->where('id',$v)->dec('us_res_zuo',$money)->dec('us_per_zuo',1)->update();
					}
				}
				
			}
			$rel = db('user')->where('id',$id)->delete();
			if ($rel) {
				$this->success('删除成功');
			} else {

				$this->error('请联系网站管理员');
			}
		} else {
			$this->error('数据不存在');
		}
	}
	

	//经销商进节点
	public function dealer() {
		$data = input('post.');
		$id = $data['tupu_id'];
		if (!$id) {
			$this->error('您没有选择进入图谱的用户');
		} else {
			$inf = model('User')->get($id);
			if (!$inf) {
				$this->error('您选择进入图谱的用户不存在');
			}
			if($inf['us_is_jing']<>1){
				$this->error('您选择进入图谱的用户不是待进入节点状态');
			}
			if($inf['us_jibie']==0){
				$this->error('您选择进入图谱的用户不是经销商');
			}
			if($inf['us_level']==0){
				$this->error('您选择进入图谱的用户不是会员');
			}
		}

		/*节点人id  左右区*/
		$info = model("User")->get($data['us_aid']);
		
		$arr = explode(',',$info['us_tree']);  //节点人tree

		if($info['id']<>$inf['us_pid'] && !in_array($inf['us_pid'],$arr)){
			$this->error('您选择进入图谱的用户不在推荐人图谱中');
		}
		if($data['qu']){
			$zuo = model("user")->where('us_aid',$info['id'])->where('us_qu',0)->find();
			if(!$zuo){
				$this->error('必须先放到左区');
			}
			$xiaji = model('User')->where('us_pid',$info['id'])->where('us_is_jing',2)->find();
			if(!$xiaji){
				$this->error('该节点人没有推荐经销商');
			}
		}
		$array = [
			'us_aid' => $info['id'],
			'us_aid_qu' => $info['us_qu'],
			'us_tree' => $info['us_tree'] . "," . $info['id'],
			'us_tree_long' => $info['us_tree_long'] + 1,
			'us_qu' => $data['qu'],
			'us_is_jing' => 2,
		];
		$rel = model("User")->xiugai($array, ['id' => $id]);
		// $rel['code'] = 1;
		if ($rel['code']) {

			$money = cache('calcu')[$inf['us_jibie'] - 1]['cal_money'];

			//业绩 对碰 管理 精英
			yeji($id, $money);
			//见点奖励
			jiandian($id, $money);
			
			//报单中心
			if($inf['us_center']){
				$num = $money * cache('setting')['center_calcu'] / 100;
				if(!cache('setting')['switch_center']){
					$center = model('User')->get($inf['us_center']);
					if($center['us_is_center']==1){
						one_to_two($inf['us_center'],$num,6,6);
					}
				}
			}
		}
		return $rel;
	}
	protected function scerweima($url = '', $logo = '') {
		require_once __DIR__ . '\qrcode.php';
		$value = $url; //二维码内容
		$errorCorrectionLevel = 'H'; //容错级别
		$matrixPointSize = 7; //生成图片大小
		//生成二维码图片
		$path = '/uploads/erweima/' . date('YmdHis') . rand(1000, 9999) . '.png';
		$filename = $_SERVER['DOCUMENT_ROOT'] . $path;
		\QRcode::png($value, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
		// $logo = $_SERVER['DOCUMENT_ROOT'] . '/static/admin/img/tou.jpg'; //准备好的logo图片
		$QR = $filename; //已经生成的原始二维码图
		if (file_exists($logo)) {
			$QR = imagecreatefromstring(file_get_contents($QR)); //目标图象连接资源。
			$logo = imagecreatefromstring(file_get_contents($logo)); //源图象连接资源。
			$QR_width = imagesx($QR); //二维码图片宽度
			$QR_height = imagesy($QR); //二维码图片高度
			$logo_width = imagesx($logo); //logo图片宽度
			$logo_height = imagesx($logo); //logo图片高度
			$logo_qr_width = $QR_width / 4; //组合之后logo的宽度(占二维码的1/5)
			$scale = $logo_width / $logo_qr_width; //logo的宽度缩放比(本身宽度/组合后的宽度)
			$logo_qr_height = $logo_height / $scale; //组合之后logo的高度
			$from_width = ($QR_width - $logo_qr_width) / 2; //组合之后logo左上角所在坐标点
			//重新组合图片并调整大小
			/*
	         *  imagecopyresampled() 将一幅图像(源图象)中的一块正方形区域拷贝到另一个图像中
*/
			imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
		}
		// header('Content-Type: image/png');
		//输出图片
		$path1 = '/uploads/erweima/' . date('YmdHis') . rand(1000, 9999) . '.png';
		imagepng($QR, $_SERVER['DOCUMENT_ROOT'] . $path1);
		imagedestroy($QR);
		imagedestroy($logo);
		return $path1;
	}
}
