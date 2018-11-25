<?php
namespace app\mall\controller;

/**
 * 红包账户
 */
class Red extends Common
{

    public function __construct()
    {
        parent::__construct();
    }
    //奖励Doken
    public function index()
    {
        $info = model('user')->get(session('us_id'));
        $num1 = model('ProWal')->where(['us_id' => $info['id'], 'wal_type' => 5])->sum('wal_num');
        $num2 = model('ProWal')->where(['us_id' => $info['id'], 'wal_type' => 15])->sum('wal_num');
        $ling = $num1-$num2;
        $this->assign(array(
            'info' => $info,
            'ling' => $ling,
        ));
        return $this->fetch();
    }
    public function get_info(){
        if(is_post()){
            $info = model('User')->where('us_account',input('us_account'))->find();
            if($info){
                return ['code'=>1,'data'=>$info];
            }else{
                return ['code'=>0];
            }
        }
    }
    
    /*--提现*/
    public function get_red()
    {
       
        if (is_post()) {
            $da = input('post.');
            if ($da['jine'] == "" || !is_numeric($da['jine']) || $da['jine'] <= 0) {
                $this->error('请输入合法金额');
            }
            if (!$this->mine['us_bank'] || !$this->mine['us_bank_number']) {
                $this->error('请去账户中心完善自己银行信息');
            }
            if ($da['jine'] < 100 || $da['jine'] % 100 != 0) {
                $this->error('金额需为100的整数倍');
            }
            if ($this->mine['us_wal'] < $da['jine']) {
                $this->error('Doken不足');
            }

            $data = array(
                'tx_num' => $da['jine'],
                'us_id' => session('us_id'),
                'tx_status' => 0,
                'tx_type' => 3,
                'tx_account' => $this->mine['us_bank_number'],
                'tx_name' => $this->mine['us_bank_person'],
                'tx_addr' => $this->mine['us_bank_addr'],
                'tx_recharge' => cache('setting')['recharge']."/100" ,
                'tx_shidao' => $da['jine'] - $da['jine'] * cache('setting')['recharge']/100,
            );
            $rel = model('Tixian')->tianjia($data);
            if ($rel['code']) {
                model('ProWal')->tianjia(session("us_id"),$da['jine'],5);
            }
            return $rel;
        }
        return $this->fetch();
    }
    public function tixian()
    {
        if (is_post()) {
            $this->map[] = ['us_id', '=', session('us_id')];
            $this->size = 10;
            $list = model("Tixian")->chaxun($this->map, $this->order, $this->size);
            return ['code' => 1,'data' => $list];
        }
        return $this->fetch();
    }
    public function tx_detail()
    {
        $id = input('get.id');
        $info = model('Tixian')->get($id);
        if (!$info) {
            $this->error('非法操作');
        } else {
            $this->assign('info', $info);
        }
        return $this->fetch();
    }

    /*----转账*/
    public function transfer(){
        if(is_post()){
            $da = input('post.');
            if($da['us_to_id']==session('us_id')){
                $this->error('您不能转给自己');
            }
            if ($da['tr_num'] == "" || !is_numeric($da['tr_num']) || $da['tr_num'] <= 0) {
                $this->error('请输入合法金额');
            }
            if($da['tr_num']>$this->mine['us_wal']){
                $this->error('奖励Doken不足');
            }
            if(mine_encrypt($da['us_safe_pwd']) != $this->mine['us_safe_pwd']){
                $this->error('安全密码不正确');
            }else{
                unset($da['us_safe_pwd']);
            }
            $da['us_id'] = session('us_id');
            
            $rel = model("Transfer")->tianjia($da);
            return $rel;
        }
        return $this->fetch();
    }

    public function tr_list(){
        if(is_post()){

            $this->map[] = ['us_id|us_to_id', '=', session('us_id')];
            $this->size = 4;
            $list = model("Transfer")->chaxun($this->map, $this->order, $this->size);
            foreach ($list as $k => $v) {
                if($v['us_id'] == session('us_id')){
                    $t1 = '出';
                    $list[$k]['tr_name'] = $v['us_to_text'];
                    $list[$k]['tr_num'] = $v['tr_num']."(".$t1.")";
                }else{
                    $t1 = '入';
                    $list[$k]['tr_name'] = $v['us_text'];
                    $list[$k]['tr_num'] = $v['tr_num']."(".$t1.")";
                }
            }
            return ['code' => 1,'data' => $list];
        }
        return $this->fetch();
    }

    /*-------------------------Doken*/
    public function msc(){
        return $this->fetch();
    }

    public function dui(){
        if(is_post()){
            
            $da = input('post.');
            if ($da['dui_num'] == "" || !is_numeric($da['dui_num']) || $da['dui_num'] <= 0) {
                $this->error('请输入合法金额');
            }

            if($da['dui_num']>$this->mine['us_msc']){
                $this->error('Doken不足');
            }



            if(mine_encrypt($da['us_safe_pwd']) != $this->mine['us_safe_pwd']){
                $this->error('安全密码不正确');
            }else{
                unset($da['us_safe_pwd']);
            }
            model('ProMsc')->tianjia(session('us_id'),$da['dui_num'],2);
            model('ProWal')->tianjia(session('us_id'),$da['dui_num'],2);
            return ['code'=>1,'msg'=>'兑换成功'];
        }
        return $this->fetch();
    }

    











    // //红包记录
    // public function red_list()
    // {
    //     if (is_post()) {
    //         $p = input('p') ? input('p') : 1;
    //         $page = $p . ',10';
    //         $map = array(
    //             'us_id' => session('mid'),
    //         );
    //         if (input('key') != "") {
    //             $map['type'] = input('key');
    //         }
    //         if (input('start') != "" && input('end') == "") {
    //             $map['add_time'] = array('gt', strtotime(input('start')));
    //         }
    //         if (input('start') == "" && input('end') != "") {
    //             $map['add_time'] = array('lt', strtotime(input('end')));
    //         }
    //         if (input('start') != "" && input('end') != "") {
    //             $map['add_time'] = array('between', array(strtotime(input('start')), strtotime(input('end'))));
    //         }
    //         $list = model('ProfitCash')->where($map)->page($page)->order('id desc')->select();
    //         $html = '';
    //         foreach ($list as $k => $v) {
    //             $url = "/mall/red/RedDetails?id=" . $v['id'];
    //             $html .= '<li><img src="/static/mobile/img/hongbao.png"/><div><p>' . $v['note'] . '</p>' . date('Y-m-d H:i', $v['add_time']) . '</p></div><div><p>' . $v['num'];
    //             $html .= '</p></div>';
    //         }
    //         echo json_encode($html);
    //         return;
    //     }
    //     $this->assign(array(
    //         'key' => input('key'),
    //         'start' => input('start'),
    //         'end' => input('end'),
    //     ));
    //     return $this->fetch();
    // }
    // //筛选搜索
    // public function screening()
    // {
    //     return $this->fetch();
    // }
    // //奖励钱包
    // public function integral()
    // {
    //     $info = model('User')->get(session('mid'));
    //     $this->assign(array(
    //         'info' => $info,
    //     ));
    //     return $this->fetch();
    // }
    // //红包记录
    // public function integralList()
    // {
    //     if (is_post()) {
    //         $p = input('p') ? input('p') : 1;
    //         $page = $p . ',10';
    //         $map = array(
    //             'us_id' => session('mid'),
    //             'type' => array('in', '1,3,4,51'),
    //         );
    //         if (input('key') != "") {
    //             $map['type'] = input('key');
    //         }
    //         if (input('start') != "" && input('end') == "") {
    //             $map['add_time'] = array('gt', strtotime(input('start')));
    //         }
    //         if (input('start') == "" && input('end') != "") {
    //             $map['add_time'] = array('lt', strtotime(input('end')));
    //         }
    //         if (input('start') != "" && input('end') != "") {
    //             $map['add_time'] = array('between', array(strtotime(input('start')), strtotime(input('end'))));
    //         }
    //         $list = model('ProfitIntegrity')->where($map)->page($page)->order('id desc')->select();
    //         $html = '';
    //         foreach ($list as $k => $v) {
    //             // $url = "/mall/red/RedDetails?id=" . $v['id'];
    //             $html .= '<li><img src="/static/mobile/img/hongbao.png"/><div><p>' . $v['type_text'] . '</p>' . date('Y-m-d H:i', $v['add_time']) . '</p></div><div><p>' . $v['num'];
    //             $html .= '</p></div>';
    //         }
    //         echo json_encode($html);
    //         return;
    //     }
    //     $this->assign(array(
    //         'key' => input('key'),
    //         'start' => input('start'),
    //         'end' => input('end'),
    //     ));
    //     return $this->fetch();
    // }
    // //爱心钱包
    // public function love()
    // {
    //     $info = model('user')->get(session('mid'));
    //     $this->assign(array(
    //         'info' => $info,
    //     ));
    //     return $this->fetch();
    // }
    // //红包记录
    // public function loveList()
    // {
    //     if (is_post()) {
    //         $p = input('p') ? input('p') : 1;
    //         $page = $p . ',10';
    //         $map = array(
    //             'us_id' => session('mid'),
    //             'type' => array('in', '1,2,3,50'),
    //         );
    //         if (input('key') != "") {
    //             $map['type'] = input('key');
    //         }
    //         if (input('start') != "" && input('end') == "") {
    //             $map['add_time'] = array('gt', strtotime(input('start')));
    //         }
    //         if (input('start') == "" && input('end') != "") {
    //             $map['add_time'] = array('lt', strtotime(input('end')));
    //         }
    //         if (input('start') != "" && input('end') != "") {
    //             $map['add_time'] = array('between', array(strtotime(input('start')), strtotime(input('end'))));
    //         }
    //         $list = model('ProfitIntegrity')->where($map)->page($page)->order('id desc')->select();
    //         $html = '';
    //         foreach ($list as $k => $v) {
    //             $url = "/mall/red/RedDetails?id=" . $v['id'];
    //             $html .= '<li><img src="/static/mobile/img/hongbao.png"/><div><p>' . $v['type_text'] . '</p>' . date('Y-m-d H:i', $v['add_time']) . '</p></div><div><p>' . $v['num'];
    //             $html .= '</p></div>';
    //         }
    //         echo json_encode($html);
    //         return;
    //     }
    //     $this->assign(array(
    //         'key' => input('key'),
    //         'start' => input('start'),
    //         'end' => input('end'),
    //     ));
    //     return $this->fetch();
    // }
}
