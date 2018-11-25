<?php
namespace app\admin\controller;

use think\Container;
use think\Db;
/**
 * 商家
 */
class Mer extends Common {





	/*-----------------申请*/

	public function apply(){

		if(is_post()){
			$data = input('post.');
			$info = model('StoApply')->detail(['id'=>$data['id']]);
			$uu = Db::name("user")->where("id",$info['us_id'])->field('id,us_msc,us_is_mer')->find();
			if($uu['us_is_mer']==1){
				$this->error('该用户已经是商家了');
			}
			model("StoApply")->update($data);
			if($data['apply_status']==1){

				Db::name("user")->where('id',$info['us_id'])->setfield('us_is_mer',1);
				//直推商家
				model('User')->direct_mer($info['us_id'],$info['apply_jine']);
				$data  = [
					'us_id'=>$info['us_id'],
					'mer_jine' => $info['apply_jine'],
				];
				model('StoMer')->tianjia($data);
				$this->success('审核通过');
			}else{
				model("User")::usMscChange($info['us_id'],$info['apply_jine'],11);
				$this->success('已被驳回');
			}
		}
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_tel|us_real_name', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			$this->map[] = ['us_id', '=', $us_id];
		}
		if (is_numeric(input('get.status'))) {
			$this->map[] = ['apply_status', '=', input('get.status')];
		}
		$list = model('StoApply')->chaxun($this->map, $this->order, $this->size);

		$this->assign(array(
			'list' => $list,
		));
		return $this->fetch();


	}
	public function apply_edit() {
		
		$info = model('StoApply')->detail(['id' => input('get.id')]);
		
		$this->assign(array(
			
			'info' => $info,
		
		));
		return $this->fetch();
	
	}
	public function apply_del(){
		if (input('post.id')) {
            $id = input('post.id');
        } else {
            $this->error('id不存在');
        }
        $info = model('StoApply')->get($id);
        if ($info) {
            $rel = model('StoApply')->destroy($id);
            if ($rel) {
                $this->success('删除成功');
            } else {
                $this->error('请联系网站管理员');
            }
        } else {
            $this->error('数据不存在');
        }
	}


	/*--------------------------商家*/
	public function index() {
		if (is_post()) {

			$rst = model('Store')->xiugai([input('post.key') => input('post.value')], ['id' => input('post.id')]);
			return $rst;

		}

		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_tel|us_real_name', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			$this->map[] = ['us_id', '=', $us_id];
		}

		if (is_numeric(input('get.status'))) {
			$this->map[] = ['mer_status', '=', input('get.status')];
		}
		if (input('get.mer_name')) {
			$this->map[] = ['mer_name', 'like', '%'.input('get.mer_name')."%"];
		}


		$list = model('StoMer')->chaxun($this->map, $this->order, $this->size);
		// foreach ($list as $k => $v) {
		// 	$list[$k]['ku_num'] = model("StoKu")->where('us_id',$v['us_id'])->sum('ku_num');
		// }
		$this->assign(array(
			'list' => $list,
		));
		return $this->fetch();

	}

	public function add() {
		if (is_post()) {
			$data = input('post.');
			$validate = validate('Shop');
			$res = $validate->scene('addmer')->check($data);
			if (!$res) {
				$this->error($validate->getError());
			}
			$user = Model("User")->get($data['us_id']);
			if(!$user || $user['us_is_mer']){
				return ['code'=>0,'msg'=>'该用户不存在或已经是商家了'];
			}
			$rel = model('StoMer')->tianjia($data);
			if($rel['code']){
				model("User")->where('id',$data['us_id'])->setfield('us_is_mer',1);
			}
			return $rel;
		}
		return $this->fetch();
	}

	public function edit() {

		if (is_post()) {
			$data = input('post.');
			$validate = validate('Shop');
			$rst = $validate->scene('addmer')->check($data);
			if (!$rst) {
				$this->error($validate->getError());
			}
			
			$rel = model('StoMer')->update($data);
			return ['code'=>1,'msg'=>'修改成功'];
		}
		$info = model('StoMer')->detail(['id'=>input('get.id')]);
		
		$this->assign(array(
			'info' => $info,
		));
		return $this->fetch();
	}

	public function del(){
		if (input('post.id')) {
            $id = input('post.id');
        } else {
            $this->error('id不存在');
        }
        $info = model('StoMer')->get($id);
        if ($info) {
        	Db::name("User")->where('id',$info['us_id'])->setfield('us_is_mer',0);
           	model('User')::usMscChange($info['us_id'],$info['mer_jine'],13);

            $rel = model('StoMer')->destroy($id);
            if ($rel) {
                $this->success('删除成功');
            } else {
                $this->error('请联系网站管理员');
            }
        } else {
            $this->error('数据不存在');
        }
	}

	//门店定位
	public function positioning() {
		if (is_post()) {
			$data = input("post.");
			$validate = validate('Verify');
			$rst = $validate->scene('editTude')->check($data);
			if (!$rst) {
				$this->error($validate->getError());
			}
			$rel = model('Store')->xiugai($data, ['id' => input('post.id')]);
			if ($rel) {
				$this->success('修改成功');
			} else {
				$this->error('您未进行修改');
			}
		}
		$info = model('Store')->get(input('get.id'));
		$this->assign(array(
			'info' => $info,
		));
		return $this->fetch();
	}
	public function position() {

	}
	

	




	public function get_cate() {
		$list = model('Cate')->where('st_id', input('post.id'))->select();

		if (count($list)) {
			return $data = [
				'code' => 1,
				'data' => $list,
			];
		} else {
			return $data = [
				'code' => 0,
			];
		}
	}





	/*--查询用户*/
	public function get_us(){
		$info = model("User")->where('us_account',input('us_account'))->find();

		if($info){
			if($info['us_is_mer']){
				return ['code'=>2,'msg'=>'该用户已经是商家了'];
			}
			return ['code'=>1,'data'=>$info];
		}else{
			return ['code'=>0];
		}
	}


	//送
	public function song(){
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_tel|us_real_name', input('get.keywords'))->value('id');
			if (!$us_id) {
				$us_id = 0;
			}
			$this->map[] = ['us_id', '=', $us_id];
		}
		if (input('get.mer_name') != "") {
			if(input('get.mer_name')=="自营"){
				$mer_id = 0;
			}else{
				$mer = model("StoMer")->where('mer_name|mer_account', input('get.mer_name'))->find();
				if($mer){
					$mer_id = $mer['id'];
				}else{
					$mer_id = 999999;
				}
			}
			$this->map[] = ['mer_id', '=', $mer_id];
			
		}
		if (is_numeric(input('get.status'))) {
			$this->map[] = ['huo_status', '=', input('get.status')];
		}
		if (is_numeric(input('get.type'))) {
			$this->map[] = ['huo_type', '=', input('get.type')];
		}
		$list = model('Fahuo')->chaxun($this->map, $this->order, $this->size);
		$this->assign(array(
			'list' => $list,
		));
		return $this->fetch();
	}
	public function songcheck() {
		if(is_post()){
			$id = input('post.id');
			$info = model('Fahuo')->get($id);
			if(!$info){
				$this->error('非法操作');
			}
			$rst = model('Fahuo')->xiugai(['huo_status' => 1], ['id' => input('post.id')]);
			if ($rst) {
				$prod = model("StoProd")->get($info['prod_id']);
				$num = $prod['prod_price']*cache('setting')['huo_calcu']/100;
				model("ProWal")->tianjia($info['us_id'],$num,15); 
				$this->success('已发货');
			} else {
				$this->error('操作失败');
			}
		}
	}
	public function song_xq(){
		$id = input('get.id');
		$info = model('Fahuo')->detail(['id'=>$id]);
		$this->assign(array(
			'info'=>$info,
		));
		return $this->fetch();
	}	

	//报单产品库存
	public function ku(){
		$id = input('id');
		$mer = model("StoMer")->detail(['id'=>$id]);
		$this->map[] = ['us_id','=',$mer['us_id']];
		$ku = model('StoKu')->chaxun($this->map,$this->order,$this->size);
		$this->assign(array(
			'list' => $ku,
			'mer' => $mer,
		));
		return $this->fetch();
	}
	//报单产品库存
	public function ku_add(){

		$id = input('id');
		$mer = model("StoMer")->detail(['id'=>$id]);
		if(is_post()){
			$d = input('post.');
			$ku = model('StoKu')->where('us_id',$d['us_id'])->where('prod_id',$d['prod_id'])->find();
            if($ku){
                model('Stoku')->where('id',$ku['id'])->setInc('ku_num',$d['num']);
            }else{
                $arr = [
                    'prod_id' => $d['prod_id'],
                    'us_id' =>   $d['us_id'],
                    'ku_num' => $d['num'],
                ];
                model('StoKu')->tianjia($arr);
            }
            return ['code'=>1,'msg'=>'添加成功'];
		}


		
		$this->map[] = ['us_id','=',$mer['us_id']];
		$prod = model('StoProd')->where('prod_zone',1)->where('prod_status',1)->select();
		$this->assign(array(
			'mer' => $mer,
			'prod' => $prod,
		));
		return $this->fetch();
	}
}
