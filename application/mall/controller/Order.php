<?php
namespace app\mall\controller;

/**
 * 订单控制器
 */
class Order extends Common
{

    public function __construct()
    {
        parent::__construct();
    }
    //增加
    public function order_add()
    {
        if (is_Post()) {
            $da = input('post.');

            if(mine_encrypt($da['us_safe_pwd']) != $this->mine['us_safe_pwd']){
                $this->error('安全密码不正确');
            }else{
                unset($da['us_safe_pwd']);
            }

            if (session('shop')) {
                $datd = session('shop');
            } else {
                $this->error('非法操作');
            }
            $prod = model('StoProd')->get($datd['prod_id']);

            //判断库存
            if($prod['prod_res']<$datd['num'] || $prod['prod_res']<0){
                $this->error('商品库存不足');
            }

            $order_number = "AM" . time() . GetRandStr(3);
            $pay_number1 = time() . GetRandStr(5);
            $pay_number2 = time() . GetRandStr(5);

            $sum_money = $prod['prod_price'] * $datd['num'];
           
            //扣Doken
            if ($sum_money != 0) {
                if ($sum_money > $this->mine['us_msc']) {
                    $this->error('您的DokenDoken不足');
                } else {
                    model('User')->usMscChange(session('us_id'),$sum_money, 8);
                }
            }
            
            //整理数据
            $data = array( //订单详情表
                'order_number' => $order_number,
                'prod_id' => $prod['id'],
                'prod_name' => $prod['prod_name'],
                'prod_zone' => $prod['prod_zone'],
                'prod_pic' => $prod['pic_text'][0],
                'prod_num' => $datd['num'],
                'prod_attr' => $datd['attr'],
                'prod_price' => $prod['prod_price'],
                'prod_price_yuan' => $prod['prod_price_yuan'],
                'mer_id' => $da['mer_id']?:$prod['mer_id'],
                'us_id' => session('us_id'),
                'order_money' => $sum_money,
                'detail_status' => 1,
                'detail_pay_time' => date('Y-m-d H:i:s'),
            );
            

            $datb = array( //订单表
                'order_number' => $order_number,
                'pay_number' => $pay_number1,
                'addr_id' => $da['addr_id'],
                'order_money' => $sum_money,
                'order_note' => $da['message'],
                'us_id' => session('us_id'),
            );
            model('StoOrderDetail')->tianjia($data);
            $rel = model('StoOrder')->tianjia($datb);

            if($rel['code']){
                session('shop', null);
                 model('StoProd')
                    ->where(['id' => $prod['id']])
                    ->dec('prod_res', $datd['num'])
                    ->inc('prod_sales', $datd['num'])
                    ->inc('prod_sales_true', $datd['num'])
                    ->update();
                
                return $datt = array(
                    'code' => 1,
                    'msg' => '支付成功',
                );
              
            }else{
                $this->error('支付失败');
            }
        }
    }
    //查询 列表
    public function index()
    {
        // halt(session('mid'));
        $ji = input('get.ji');
        if (is_post()) {
            $da = input('post.');

            $this->map[] = ['us_id', '=', session('us_id')];
            if ($da['status'] != "" && $da['status'] != 4) {
                $this->map[] = ['detail_status', '=',$da['status']];
            }
            $this->size = 10;
            $list = model('StoOrderDetail')->chaxun($this->map,$this->order,$this->size);
            foreach ($list as $k => $v) {
                $list[$k]['pic'] = $v['pic_text'][0];
            }
            return ['code' => 1,'data' => $list];
        }
        $this->assign(array(
            'ji' => $ji,
        ));
        return $this->fetch();
    }
    
    //详情 展示
    public function detail()
    {
        if (input('get.id') != "") {
            $id = input('get.id');
        } else {
            $this->error('非法操作');
        }
        $info = model('StoOrderDetail')->detail(['id'=>$id]);
        $addr = model('UserAddr')->where('id', $info->order['addr_id'])->find();
    
        $this->assign(array(
            'info' => $info,
            'addr' => $addr,
        ));
        return $this->fetch();
    }


    //取消
    public function quxiao(){
        if(is_post()){
            $da = input('post.');

            $info = model("StoOrderDetail")->detail(['id'=>$da['id']]);
            if($info['detail_status']!=1){
                $this->error('该订单不是待发货状态');
            }
            $rel = model('StoOrderDetail')->where('id',$da['id'])->setfield('detail_status',0);

            if($rel){
                /*
                    1.退钱
                    2.退库存
                 */
                model("User")::usMscChange($info['us_id'],$info['order_money'],12);
                model('StoProd')
                    ->where(['id' => $info['prod_id']])
                    ->inc('prod_res', $info['prod_num'])
                    ->dec('prod_sales', $info['prod_num'])
                    ->dec('prod_sales_true', $info['prod_num'])
                    ->update();
            }


            return ['code'=>'1','msg'=>'取消成功'];
        }
    }


    /**
     * 删除订单
     * @return [type] [description]
     */
    public function del()
    {
        if (input('post.id') != "") {
            $id = input('post.id');
        } else {
            $this->error('非法操作');
        }
        $info = model('OrderDetail')->get($id);
        if (!$info) {
            $this->error('非法操作');
        }
        $rel = model('OrderDetail')->destroy($id);
        if ($rel) {
            model('Product')->where('id', $info['pd_id'])
                ->inc('pd_store', $info['pd_num'])
                ->dec('order', $info['pd_num'])
                ->dec('order_xian', $info['pd_num'])
                ->update();
            if ($info['pd_integrity']) {
                model('ProfitIntegrity')->tianjia(session('mid'), 3, $info['pd_integrity'], '订单取消', 'wallet_integrity');
            }
            $this->success('取消成功');
        } else {
            $this->error('取消失败');
        }
    }

    /**
     * 收货
     */
    public function receive()
    {
        
        if (input('post.id') != "") {
            $id = input('post.id');
        } else {
            $this->error('非法操作');
        }
        $info = model('StoOrderDetail')->get($id);
        if ($info['detail_status'] != 2) {
            $this->error('该订单不是待收货状态');
        }
        $data = array(
            'detail_finish_time' => date('Y-m-d H:i:s'),
            'detail_status' => 3,
        );
        
        $rel = model('StoorderDetail')->where('id',$id)->update($data);
        if ($rel) {
            model("User")->direct_xiaofei(session('us_id'),$info['order_money']);
            $this->success('确定收货成功');
        } else {
            $this->error('确定收货失败');
        }
    }
    // 2级分润
    protected function jiesuan($id)
    {
        $info = model('OrderDetail')->get($id);
        $user = model('User')->get($info['user_id']);
        $array = explode(',', $user['path']);
        rsort($array);
        if ($info['frontal'] && $array[0]) {
            direct_profit($array[0], $info['frontal'], 1); //直 推分润
            if ($array[1]) {
                direct_profit($array[1], $info['frontal'], 2); //直推直推分润
            }
        }
    }
    /**
     * 市代理199万
     * 赠送价值20%产品+等值Doken
     * 享受辖区内代理N次进货额4%及区域市场销售额2%奖励
     * 县区代理99万
     * 赠送价值10%产品+等值Doken
     * 享受辖区内代理N次进货额3%及区域市场销售额2%奖励
     * 健康生活馆10万
     * 赠送价值10万产品+等值Doken
     * 享受自己
     * @param  [type] $id [订单id]
     */
    protected function agency_profit($id)
    {
        $info = model('OrderDetail')->get($id);
        $user = model('User')->get($info['user_id']);
        // dump($user['agency']);3
        if ($user['agency'] > 0) {
            $num = $info['pd_num'] * $info['pd_stock'];
            $cargo = 1;
        } else {
            $num = $info['pd_num'] * $info['pd_cash'];
            $cargo = 0;
        }

        if (!$num) {return;}
        //已经确定金额 下面是确定代理 确定给多少 记录
        $addr = model('UserAddr')->get($info->order->addr_id);
        // dump($addr);对象
        //市代
        $where1 = array(
            'type' => 3,
            'city_code' => $addr['ad_city'],
            'status' => 1,
        );
        $shidai = model('ApplyAgency')->where($where1)->find();
        // halt($shidai);
        if ($shidai) {
            $map1 = array(
                'agency' => 3,
                'cargo' => $cargo,
            );
            $n = db('config_agency')->where($map1)->value('n');
            model('ProfitCash')->tianjia($shidai['uid'], 20, $num * $n / 100, '市代理返利');
        }
        $where2 = array(
            'type' => 2,
            'area_code' => $addr['ad_area'],
            'status' => 1,
        );
        $xiandai = model('ApplyAgency')->where($where2)->find();
        // halt($xiandai);
        if ($xiandai) {
            $map2 = array(
                'agency' => 2,
                'cargo' => $cargo,
            );
            $n = db('config_agency')->where($map2)->value('n');
            model('ProfitCash')->tianjia($xiandai['uid'], 21, $num * $n / 100, '县代理返利');
        }
        //健康生活馆
        $path = $user['path'];
        $arr = explode(',', $path);
        foreach ($arr as $v) {
            if ($v > 0) {
                $tiaojian = array(
                    'id' => $v,
                    'agency' => 1,
                );
                $partner = db('user')->where($tiaojian)->find();
                if ($partner) {
                    model('ProfitCash')->tianjia($v, 22, $num * 2 / 100, '生活馆返利');
                }
                $parent = db('user')->where('id', $partner['us_referrer'])->find();
                if ($parent) {
                    model('ProfitCash')->tianjia($v, 23, $num * 2 / 100, '下级生活馆');
                }
            }
        }
    }
}
