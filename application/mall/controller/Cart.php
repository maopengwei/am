<?php
namespace app\mall\controller;

/**
 * 购物车
 */
class Cart extends Common
{

    public function _initialize()
    {
        parent::_initialize();
    }
    //添加购物车
    public function cart_add()
    {
        if (is_post()) {
            $da = input('post.');
            if(!session('us_id')){
                return ['code'=>0,'msg'=>'请先登陆'];
            }
            $where = array(
                'us_id' => session('us_id'),
                'prod_id' => $da['prod_id'],
            );
            $cart = model('StoCart')->where($where)->find();
            if ($cart) {
                $this->error('您已经把该商品加入购物车了');
            }
            $prod = model('StoProd')->get($da['prod_id']);
            if($prod['prod_res']<=0){
                $this->error('库存不足');
            }
            $data = array(
                'us_id' => session('us_id'),
                'prod_id' => $da['prod_id'],
                'cart_attr' => $da['attr'],
                'cart_num' => $da['num'],
            );
            $rel = model('StoCart')->tianjia($data);
            return $rel;
        }
    }
    public function index()
    {
        session('arrid', null);
        $where = array(
            'user_id' => session('mid'),
        );
        $this->map[] = ['us_id','=',session('us_id')];
        $this->size = 100;
        $list = model('StoCart')->chaxun($this->map,$this->order,$this->size);
        $this->assign(array(
            'list' => $list,
        ));
        return $this->fetch();
    }

    //增加减少

    public function add()
    {
        if (is_post()) {
            $da = input('post.');
            $num = $da['num'] + 1;

            $info = model('StoCart')->detail(['id'=> $da['id']]);
            if ($info->prod['prod_res'] < $num) {
                return ['code'=>2,'msg'=>'库存不足','num'=>$info->prod['prod_res']];
            }
            $rel = model('StoCart')->where('id', $da['id'])->setfield('cart_num', $num);
            if ($rel) {
                return $data = array(
                    'code' => 1,
                    'msg' => '添加成功',
                    'num' => $num,
                );
            } else {
                $this->error('添加失败');
            }
        }
    }
    public function reduce()
    {
        if (is_post()) {
            $da = input('post.');
            $num = $da['num'] - 1;
            $info = model('StoCart')->detail(['id'=> $da['id']]);
            if ($num <= 0) {
                $this->error('不能为0');
            }
            if ($info->prod['prod_res'] < $num) {
                return ['code'=>2,'msg'=>'库存不足','num'=>$info->prod['prod_res']];
            }
            $rel = model('StoCart')->where('id', input('id'))->setfield('cart_num', $num);
            if ($rel) {
                return $data = array(
                    'code' => 1,
                    'msg' => '减少成功',
                    'num' => $num,
                );
            } else {
                $this->error('减少失败');
            }
        }
    }
    public function val()
    {
        if (request()->isPost()) {
            $da = input('post.');
            $num = $da['num'];
            $info = model('StoCart')->detail(['id'=> $da['id']]);
            if ($num <= 0) {
                return ['code'=>0,'msg'=>'不能为0','num'=>$info->prod['prod_res']];
            }
            if ($info->prod['prod_res'] < $num) {
                return ['code'=>0,'msg'=>'库存不足','num'=>$info->prod['prod_res']];
            }
            dump($da);
            dump($info->prod['prod_res']);
            model('StoCart')->where('id', $da['id'])->setfield('attr_num', $num);
            return $data = array(
                'code' => 1,
                'msg' => '修改成功',
            );
        }
    }
    public function del()
    {
        $id = input('post.id');
        if(!$id){
            $this->error('非法操作');
        }
        $info = model('StoCart')->get($id);
        if ($info) {
            $rel = db('sto_cart')->where('id',$id)->delete();
            if ($rel) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('非法操作');
        }
    }

    public function submit()
    {

        if (input('arrid') == "" && session('arrid') == "") {
            $this->error('您未选取任何产品');
        } elseif (input('arrid')) {
            $arrid = rtrim(input('arrid'), ',');
            $arr = explode(',', $arrid);
            session('arrid', $arrid);
        } else {
            $arrid = session('arrid');
            $arr = explode(',', session('arrid'));
        }
        $list = [];
        foreach ($arr as $k => $v) {
            $list[$k] = model('StoCart')->detail(['id'=>$v]);
        }

        if (input('addr_id')) {
            $where['id'] = input('addr_id');
        } else {
            $where = array(
                'us_id' => session('us_id'),
                'addr_default' => 1,
            );
        }
        $addr = model('UserAddr')->where($where)->find();
        // halt($addr);
        if (!$addr) {
            $addr = model('UserAddr')->where('us_id', session('us_id'))->find();
        }
        $this->assign(array(
            'list' => $list,
            'addr' => $addr,
        ));
        return $this->fetch();
    }

    public function pro(){
        if(is_post()){
            $d = input('post.');
            $mer = model("StoMer")->where('mer_account',$d['mer_account'])->where('mer_status',1)->find();

            if(count($mer)){
                $arr = explode(',', session('arrid'));
                foreach ($arr as $k => $v) {
                    $cart = model('StoCart')->detail(['id'=>$v]);
                    $where = [];
                    $where[] = ['us_id','=',$mer['us_id']];
                    $where[] = ['ku_num','>=',$cart['cart_num']];
                    $where[] = ['prod_id','=',$cart['prod_id']];
                    $prod = model('StoKu')->where($where)->find();
                    if(!$prod){
                        return ['code'=>2];
                    }
                }
                return ['code'=>1,'mer'=>$mer];
                
            }else{
                return ['code'=>0];
            }
        }
    }

    public function order_add()
    {
        $inf = model('User')->get(session('us_id'));
        if (is_Post()) {
            $da = input('post.');
         
            if(mine_encrypt($da['us_safe_pwd']) != $this->mine['us_safe_pwd']){
                $this->error('安全密码不正确');
            }else{
                unset($da['us_safe_pwd']);
            }
            $arr = explode(',', session('arrid'));

            $order_number = "AM" . time() . GetRandStr(3);
            $pay_number = time() . GetRandStr(5);

            // 扣Doken
            
            $sum_msc = 0;
            foreach ($arr as $value) {
                $info = model('StoCart')->detail(['id'=>$value]);
                $sum_msc += $info->prod['prod_price'] * $info['cart_num'];
            }

          
            if($da['mer_id']){
                $mer = model('StoMer')->get($da['mer_id']);
            }

            if ($sum_msc != 0) {

                if ($sum_msc > $this->mine['us_msc']) {
                    $this->error('您的DokenDoken不足');
                } else {
                    model('User')::usMscChange(session('us_id'),$sum_msc, 8);
                }
            }
            // 处理数据并存表  关键是价格
            foreach ($arr as $value) {
                // $detail = model('StoOrderDetail');
                $cart = model('StoCart')->detail(['id'=>$value]);
                // $order_sum = $cart->prod['prod_price'] * $cart['cart_num'];

                $order_sum = $cart->prod['prod_price'] * $cart['cart_num'];

                $data = array(
                    'order_number' => $order_number,
                    'prod_id' => $cart->prod['id'],
                    'prod_name' => $cart->prod['prod_name'],
                    'prod_zone' => $cart->prod['prod_zone'],
                    'prod_pic' => $cart->prod['pic_text'][0],
                    'prod_num' => $cart['cart_num'],
                    'prod_attr' => $cart['cart_attr'],
                    'prod_price' => $cart->prod['prod_price'],
                    'prod_price_yuan' => $cart->prod['prod_price_yuan'],
                    'mer_id' => $da['mer_id']?:$cart->prod['mer_id'],
                    'us_id' => session('us_id'),
                    'pay_number' => time() . GetRandStr(5),
                    'order_money' => $order_sum,
                    'detail_status' => 1,
                    'detail_pay_time' => date('Y-m-d H:i:s'),
                );
                model('StoOrderDetail')->tianjia($data);
            }

            $sum_money = 0;
            $sum_money += model('StoOrderDetail')->where('order_number', $order_number)->where('detail_status',0)->sum('order_money');
            $datb = array(
                'order_number' => $order_number,
                'pay_number'   => time() . GetRandStr(5),
                'addr_id'      => $da['addr_id'],
                'order_money'  => $sum_money,
                'order_note'   => $da['message'],
                'us_id'        => session('us_id'),
            );
            $rel = model('StoOrder')->tianjia($datb);
            if ($rel['code']) {
                session('arrid', null);
                foreach ($arr as $k => $v) {
                    $cart_info = model("StoCart")->detail(['id'=>$v]);
                    if($da['mer_id']){
                        model('StoKu')->where('us_id',$mer['us_id'])->where('prod_id',$cart_info['prod_id'])->setDec('ku_num',$cart_info['cart_num']);
                    }else{
                       model('Stoprod')
                        ->where(['id' => $cart_info['prod_id']])
                        ->dec('prod_res', $cart_info['cart_num'])
                        ->inc('prod_sales', $cart_info['cart_num'])
                        ->inc('prod_sales_true', $cart_info['cart_num'])
                        ->update(); 
                    }
                    
                    db('sto_cart')->where('id', $v)->delete();
                }
                
                // if ($sum_money==0) {
                return $datc = array(
                    'code' => 1,
                    'msg' => '订单支付成功',
                    'url' => "/mall/order/index?ji=1",
                );
            }else{
                return ['code'=>0,'msg'=>'下单失败'];
            }
        }
    }
}
