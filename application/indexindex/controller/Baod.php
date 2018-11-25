<?php
namespace app\index\controller;
use think\Db;

class Baod extends Base 
{
    //报单产品
    public function index(){
        if(is_post()){
            $this->order = 'prod_sort desc,id desc';
            $this->map[] = ['prod_status', '=', 1];
            $this->map[] = ['prod_zone', '=', 1];
            $list = Db::name('sto_prod')->where($this->map)->order($this->order)->select();
            foreach ($list as $k => $v) {
                $list[$k]['prod_pic'] = explode(',',$v['prod_pic'])[0];
            }  
            $this->msg($list);
        }else{
            $this->s_msg('get');
        }
    }
    //报单
    public function buy(){
        if(is_post()){
            $data = input('post.');
            if(!$data){
                $this->e_msg('请填写信息');
            }
            /** 
             *  报单 生成订单
             *  扣除商品库存 增加销量
             *  扣除茶币
             *  直推奖 层碰奖 对碰奖
             * 
             *  复投 生成订单
             *  扣除商品库存 增加销量
             *  见点奖  对碰奖
             * 
             */


            $validate = validate('Baod');
            $res = $validate->scene('baod')->check($data);
            if (!$res) {
                return [
                    'code'  =>  0,
                    'msg'	=>  $validate->getError(),
                ];
            }

            //商品信息
            $prod = model('StoProd')->get($d['prod_id']);

            $addr = Db::name('user_addr')->where('id',$d['addr_id'])->find();
            if(!$addr){
                $this->e_msg('请选择有效地址');
            }
            $state = $this->user['us_status'];//0报单  1复投
            if($state==0){
                $all_money = $prod['prod_price'];
                if($all_money!=cache('setting')['cal_bd']){
                    $this->e_msg('商品的报单金额不对');
                }
                $wal_type = 3;  //扣除茶币类型
                $peng_type = 5; //对碰奖励类型
            }else{
                $all_money =  $prod['prod_price']*cache('setting')['cal_bd']*cache('setting')['recover_zhe'];
                $wal_type = 8;
                $peng_type = 13;
            }
            
            //算茶币
            if ($all_money > $this->user['us_wal']) {
                $this->e_msg('您的茶币不足');
            }

            //生成快照
            if($prod['prod_is_gai']==1){
                $kuai_id = Db::name('kuai_prod')->where('prod_id',$prod['id'])->order('id desc')->value('id');
            }else{
                $kuai = [
                    'prod_id' => $prod['id'],
                    'prod_name' => $prod['prod_name'],
                    'prod_price' => $prod['prod_price'],
                    'prod_pic' => $prod['pic_text'][0],
                    'cate_name' => $prod['cate_text'],
                    'mer_name' => $prod['mer_text'],
                ];
                $kuai_id = model('KuaiProd')->tianjia($kuai);
                Db::name('sto_prod')->where('id',$prod['id'])->setfield('prod_is_gai',0); 
            }


            //整理数据
            $data = array( //订单表
                'order_number' => $order_number,
                'kuai_id' => $kuai_id,
                'prod_num' => 1,
                'prod_zone' => $prod['prod_zone'],
                'prod_type' => $state,
                'mer_id' => $prod['mer_id'],
                'us_id' => $this->user['id'],
                'order_money' => $all_money,
                'detail_status' => 1,
                'detail_pay_time' => date('Y-m-d H:i:s'),
            );
            

            $datb = array( //订单号表
                'order_number' => $order_number,
                'addr_id' => $d['addr_id'],
                'addr_name' => $addr['addr_name'],
                'addr_stree' => $addr['addr_stree'],
                'addr_tel' => $addr['addr_tel'],
                'order_money' => $all_money,
                'us_id' => $this->user['id'],
            );
            model('StoOrderDetail')->tianjia($data);
            $rel = model('StoOrder')->tianjia($datb);
            if($rel){
                //扣除用户茶币
                model('User')::usWalChange($this->user['id'],$all_money,$wal_type);
                //更新产品库存销量
                model('Stoprod')
                ->where(['id' => $prod['id']])
                ->dec('prod_res', 1)
                ->inc('prod_sales', 1)
                ->inc('prod_sales_true', 1)
                ->update();

                if($state==0){
                    $brr = [
                        'id' => $this->user['id'],
                        'us_active_time' => date('Y-m-d H:i:s'),
                        'us_status' => 1,
                    ];
                    $rrr = Db::name('user')->update($brr);

                    //直推
                    model('User')->direct_pro($this->user['id']);
                }else{
                    //见点
                    model("User")->point($this->user['id']);
                }
                
                model('User')->yeji($this->user['id'],$all_money,$peng_type);
                $this->s_msg('购买成功');
            }else{
                $this->e_msg('购买失败');
            }
		}
    }
    public function bd(){
          /** 
         * prod_zone 0普通产品  1报单产品
         * prod_type 0报单   1复投
         * 报单列表 
         */
        $this->map[] = ['prod_zone','=',1];
        $this->map[] = ['us_id','=',$this->user['id']];


		if (input('get.status') != "") {
			$this->map[] = ['detail_status', '=', input('get.status')];
		}

		if (input('get.prod_name') != "") {
			$this->map[] = ['prod_name', 'like', "%".input('get.prod_name')."%"];
		}
		
		if (input('get.order_number') != "") {
			$this->map[] = ['order_number', '=', input('get.order_number')];
		}
		if (input('get.start')) {
			$this->map[] = ['detail_add_time', '>=', input('get.start')];
		}
		if (input('get.end')) {
			$this->map[] = ['detail_add_time', '<=', input('get.end')];
		}

        $list = model('StoOrderDetail')->chaxun($this->map, $this->order, $this->size);
		$this->assign(array(
			'list' => $list,
		));
		return $this->fetch();
    }
}
