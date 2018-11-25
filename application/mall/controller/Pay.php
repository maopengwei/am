<?php
namespace app\mall\controller;

use thinnk\Db;

/**
 * 支付
 */
class Pay extends Common
{

    public function _initialize()
    {
        parent::_initialize();
    }
    public function cash()
    {
        return $this->fetch();
    }
    
    //充值
    public function recharge()
    {
        $this->assign('uid', session('mid'));
        return $this->fetch();
    }
    
    //充值列表
    public function pay(){
        if(is_post()){

            $this->map[] = ['us_id', '=', session('us_id')];
            $this->size = 10;
            $list = model("PayRecord")->chaxun($this->map, $this->order, $this->size);
            return ['code' => 1,'data' => $list];
           
        }
        return $this->fetch();
    }




    /*--提交支付前的界面*/
    public function sub()
    {

        if (!input('prod_id')) {
            if (session('shop')) {
                $data = session('shop');
            } else {
                $this->error('非法操作');
            }
        } else {
            $data = array(
                'prod_id' => input('prod_id'),
                'num' => input('num'),
                'attr' => input('attr'),
            );
            session('shop', $data);
        }
        // dump(session('shop'));
        $info = model('StoProd')->detail(['id'=>$data['prod_id']]);
        $info['prod_pic'] = explode(',',$info['prod_pic']);
        //地址
        if (input('addr_id')) {
            $where['id'] = input('addr_id');
        } else {
            $where = array(
                'us_id' => session('us_id'),
                'addr_default' => 1,
            );
        }
        $addr = model('UserAddr')->where($where)->find();
        if (!$addr) {
            $addr = model('UserAddr')->where('us_id', session('us_id'))->find();
        }

        $this->assign(array(
            'info' => $info,
            'addr' => $addr,
            'data' => $data,
        ));
        return $this->fetch();
    }

    /**
     *
     * @param  [int] type [1 申请apply  2订单order]
     * @param  [int] $id [表id ]
     */
    public function index()
    {
        // halt(input('get.'));
        $da = input("get.");
        if($da['type']==1){
            $info = model('StoOrder')->get($da['id']);
            $money = $info['order_money'];
            $note = '直接购物';
        }elseif($da['type']==2){
            $info = model('StoOrderDetail')->get($da['id']);
            $money = $info['order_money'];
             $note = '未付款购买';
        }else{
            $this->error('非法操作');
        }
        
        $this->assign(array(
            'number' => $info['pay_number'],
            'uid' => session('us_id'),
            'type' => $da['type'],
            'money' => $money,
            'note' => $note,
        ));
        return $this->fetch();
    }
    
    public function cash_pay()
    {
        if (request()->isPost()) {
            $request = input('post.');
            $info = model('User')->getInfo(session('mid'));
            if (!check_pwd($request['pay_pwd'], $info['pay_pwd'])) {
                $this->error('交易密码不正确');
            }
            if ($info['wallet_cash'] < $request['money']) {
                $this->error('红包账号不足，请充值，或选择其它支付方式');
            }
            $rel1 = model('ProfitCash')->tianjia(session('mid'), $request['type'], $request['money'], $request['note']);
            $rel2 = model($request['table'])->zhifu($request['number'], 1);
            if ($rel1 && $rel2) {
                $this->success('支付成功', 'order/index.html?ji=1');
            } else {
                $this->error('支付失败');
            }
        }
    }
    public function alipay_pay()
    {
        if (is_Post()) {
            $request = input('post.');
            if ($request['type'] == 1) {
                $apply = db('apply')->where('id', $request['id'])->find();
                if ($apply['whether_pay'] != 0 || !$apply) {
                    $this->error('非法操作');
                }
            }
        }
    }
    
    
    public function pingzheng()
    {
        if (is_Post()) {
            $request = request()->post();
            if ($request['jine'] == '' || $request['jine'] <= 0 || !is_numeric($request['jine'])) {
                $this->error('请输入正确的充值金额');
            }
            if ($request['pic'] == "") {
                $this->error('转账凭证不能为空');
            }
            $request['us_id'] = session('mid');
            $rel = model('Recharge')->save($request);
            if ($rel) {
                $this->success('提交成功');
            } else {
                $this->error('提交失败');
            }
        }
        $info = model('User')->where('id', session('mid'))->field('us_name,wallet_cash')->find();
        $this->assign(array(
            'us_name' => $info['us_name'],
            'wallet_cash' => $info['wallet_cash'],
        ));
        return $this->fetch();
    }
    public function upload_pingzheng()
    {
        $file = request()->file('merchant0');
        $info = $file->validate(['size' => '2048000', 'ext' => 'jpg,png,gif'])
            ->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'apply_merchant' . DS);
        if ($info) {
            $path = '/uploads/apply_merchant/' . $info->getsavename();
            return $data = array(
                'code' => 1,
                'msg' => '上传成功',
                'data' => $path,
            );
        } else {
            return $data = array(
                'msg' => $file->getError(),
            );
        }
    }
    // public function recharge_list()
    // {
    //     return $this->fetch();
    // }
    // 大额充值记录
    public function recharge_list()
    {
        if (is_post()) {
            $p = input('p') ? input('p') : 1;
            $page = $p . ',15';
            $map = array(
                'us_id' => session('mid'),
                'type' => 1,
            );
            $list = model('Recharge')->where($map)->page($page)->order('id desc')->select();
            $html = "";
            foreach ($list as $k => $v) {
                $url = "/mall/pay/recharge_detail?id=" . $v['id'];
                $html .= '<li><div class="jyjl_left"><p>' . $v['create_time'] . '</p></div><div class="jyjl_center"><p><span>' . $v['jine'] . '</span></p></div>';
                $html .= '<div class="jyjl_right"><p>' . $v['status_text'] . '</p></div><a href="' . $url . '"></a></li>';
            }
            echo json_encode($html);
            return;
        }
        return $this->fetch();
    }
    public function tixian_list()
    {
        if (is_post()) {
            $p = input('p') ? input('p') : 1;
            $page = $p . ',15';
            $map = array(
                'us_id' => session('mid'),
                'type' => 2,
            );
            $list = model('Recharge')->where($map)->page($page)->order('id desc')->select();
            $html = "";
            foreach ($list as $k => $v) {
                $url = "/mall/pay/recharge_detail?id=" . $v['id'];
                $html .= '<li><div class="jyjl_left"><p>' . $v['create_time'] . '</p></div><div class="jyjl_center"><p><span>' . $v['jine'] . '</span></p></div>';
                $html .= '<div class="jyjl_right"><p>' . $v['status_text'] . '</p></div><a href="' . $url . '"></a></li>';
            }
            echo json_encode($html);
            return;
        }
        return $this->fetch();
    }
    //大额充值详情
    public function recharge_detail()
    {
        $id = input('get.id');
        $info = model('Recharge')->get($id);
        if (!$info) {
            $this->error('非法操作');
        } else {
            $this->assign('info', $info);
        }
        return $this->fetch();
    }
    private function info_info($db, $number)
    {
        $info = model($db)->where('number', $number)->find();
        return $info;
    }
}
