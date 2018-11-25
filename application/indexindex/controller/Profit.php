<?php
namespace app\index\controller;

class Profit extends Base 
{
    public function wal(){
        $this->map[] = ['us_id', '=', $this->user['id']];
        $list = model('ProWal')->chaxun($this->map, $this->order, $this->size);
        $this->msg($list);
    }
    // 奖励明细
    public function msc(){
        $this->map[] = ['us_id', '=', $this->user['id']];
        $list = model('ProMsc')->chaxun($this->map, $this->order, $this->size);
        $this->msg($list);
    }
   
    // 茶币转账
    public function trans(){
        if(is_post()){
            $d = input('post.');

            $validate = validate('Profit');
            $res = $validate->scene('trans')->check($d);
            if (!$res) {
                $this->e_msg($validate->getError());
            }
            if($this->user['us_account']==$d['tr_account']){
                $this->e_msg('您不能转给自己');
            }

            $code_info = cache($d['us_tel'] . 'code') ?: "";
            if (!$code_info) {
                $this->e_msg('请重新发送验证码');
            } elseif ($d['sode'] != $code_info) {
                $this->e_msg('验证码不正确');
            }
           
            if($this->user['us_wal']<$d['tr_num']){
                $this->e_msg('茶币不足');
            }
            $rel = model('ProTransfer')->tianjia($d,$this->user['id']);
            $this->msg($rel);

        }else{
            
            $this->map[] = ['us_id|us_to_id', '=', $this->user['id']];
            $list = model('ProTransfer')->chaxun($this->map, $this->order, $this->size);
            foreach ($list as $k => $v) {
                if($v['us_id']==$this->user['id']){
                    $list[$k]['tr_num'] = '-'.$v['tr_num'];
                }
                $list[$k]['us_account'] = $this->user['us_account'];
            }
            $this->msg($list);
        
        }
    }
    //转换
    public function convert(){
        if(is_post()){
            $d = input('post.');
            $validate = validate('Profit');
            $res = $validate->scene('convert')->check($d);
            if (!$res) {
                $this->e_msg($validate->getError());
            }
            $code_info = cache($d['us_tel'] . 'code') ?: "";
            if (!$code_info) {
                $this->e_msg('请重新发送验证码');
            } elseif ($d['sode'] != $code_info) {
                $this->e_msg('验证码不正确');
            }
            if($this->user['us_msc']<$d['convert_num']){
                $this->e_msg('您的奖励不足');
            }
            if($this->user['us_tel']!=$d['us_tel']){
                $this->e_msg('您的手机号和自身的手机号不一致');
            }
            $rel = model("ProConvert")->tianjia($this->user['id'],$d['convert_num']);
            $this->s_msg('转换成功');
        }else{
            $this->map[] = ['us_id','=',$this->user['id']];
            $list = model('ProConvert')->chaxun($this->map, $this->order, $this->size);
            $this->msg($list);
        }
    }
    public function tx(){
        $d = input('post.');
        $validate = validate('Profit');
        $res = $validate->scene('tx')->check($d);
        if (!$res) {
            $this->e_msg($validate->getError());
        }
        // $code_info = cache($d['us_tel'] . 'code') ?: "";
        // if (!$code_info) {
        //     $this->e_msg('请重新发送验证码');
        // } elseif ($d['sode'] != $code_info) {
        //     $this->e_msg('验证码不正确');
        // }
        if($this->user['us_msc']<$d['tx_num']){
            $this->e_msg('您的奖励积分不足');
        }
        if($this->user['us_tel']!=$d['us_tel']){
            $this->e_msg('您的手机号和自身的手机号不一致');
        }
        $d['us_id'] = $this->user['id'];
        // halt($d); 
        $rel = model("ProTixian")->tianjia($d);
        $this->s_msg('提现成功,等待后台审核');

    }
    public function tx_list(){
        
    }
}
