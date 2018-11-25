<?php

namespace app\index\controller;

use think\Request;

/**
 * 玩家个人
 */
class User extends Base
{
    // 返回 玩家列表
    public function index()
    {
        $map = [];
        $map['status'] = 1;
        if (is_numeric(input('param.status'))) {
            $map['status'] = input('param.status');
        }
        if (input('param.pid')) {
            $map['pid'] = input('param.pid');
        }
        if (input('param.keywords')) {
            $map['name|tel'] = input('param.keywords');
        }
        if (is_numeric(input('param.is_agent'))) {
            $map['is_agent'] = input('param.is_agent');
        }
        if (is_numeric(input('param.is_shop'))) {
            $map['is_shop'] = input('param.is_shop');
        }
        if (input('param.p_code')) {
            $map['province'] = input('param.p_code');
        }
        if (input('param.c_code')) {
            $map['city'] = input('param.c_code');
        }

        $list = db('user')->where($map)->paginate(input('param.size'));
        $this->result($list);
    }

    // 获取玩家信息
    public function info()
    {
        if (input('get.us_tel')) {
            $this->map = ['us_tel'=>input('get.us_tel')]; 
        }
        if (input('get.id')) {
            $this->map = ['id'=>input('get.id')]; 
        }
        if($this->map){
            $info = model('User')->detail($this->map);
            if($info){
                $this->msg($info);
            }else{
                $this->e_msg('用户不存在');
            }
        }else{
            $this->msg($this->user);
        }
    }
    
    // 会员信息修改
    public function edit()
    {
        $post = input('post.');
        if ($post) {
            $arr = array_merge($post,['id'=>$this->user['id']]);
            $rst  = model('User')->editInfo($arr);
            $this->s_msg($rst);
        }else{
            $this->e_msg();
        }
        
    }
    
    public function pass(){
        $post = input('post.');
        if ($post) {

            $validate = validate('User');
            $res = $validate->scene('pass')->check($post);
            if (!$res) {
                $this->e_msg($validate->getError());
            }
            if($post['us_tel'] != $this->user['us_tel']){
                $this->e_msg('手机号不一致');
            }
            $code_info = cache($post['us_tel'] . 'code') ?: "";
            // if (!$code_info) {
            //     $this->e_msg('请重新发送验证码');
            // } elseif ($post['sode'] != $code_info) {
            //     $this->e_msg('验证码不正确');
            // }

            $arr = array_merge($post,['id'=>$this->user['id']]);
            $rst  = model('User')->editInfo($arr);
            $this->s_msg($rst);

        }else{
            $this->e_msg();
        }
    }
    
    public function safe(){
        $post = input('post.');
        if ($post) {
            $validate = validate('User');
            $res = $validate->scene('safe')->check($post);
            if (!$res) {
                $this->e_msg($validate->getError());
            }
            if($post['us_tel'] != $this->user['us_tel']){
                $this->e_msg('手机号不一致');
            }

            // $code_info = cache($post['us_tel'] . 'code') ?: "";
            // if (!$code_info) {
            //     $this->e_msg('请重新发送验证码');
            // } elseif ($post['sode'] != $code_info) {
            //     $this->e_msg('验证码不正确');
            // }
            $arr = array_merge($post,['id'=>$this->user['id']]);
            $rst  = model('User')->editInfo($arr);
            $this->s_msg($rst);

        }else{
            $this->e_msg();
        }
    }

    // 团队
    public function team(){
        $info = model('User')->where('us_account|us_tel|us_real_name', input('post.us_account'))->field('id,us_path,us_pid,us_account,us_tel')->find();
        if (!$info) {
            $this->e_msg('查无此人');
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
        $this->msg($znote);
    }
        //图谱

//     public function node(){
//         $list = [];
//         if(input('post.id')){
//             $id = input('post.id');
//             $info =db('user')->where(array('id' =>$id)) ->find();
//         }else{
//             $info = $this->user;
//         }

//         $user1 = db('user')->where(array('us_aid'=>$info['id'],'us_qu'=>0)) ->find();

// //      $this->result($user1);
//         $user2 = db('user')->where(array('us_aid'=>$info['id'],'us_qu'=>1)) ->find();
// //      $this->result($user2);
//         $user3 = db('user')->where(array('us_aid'=>$user1['id'],'us_qu'=>0)) ->find();
// //      $this->result($user3);
//         $user4 = db('user')->where(array('us_aid'=>$user1['id'],'us_qu'=>1)) ->find();
// //      $this->result($user4);
//         $user5 = db('user')->where(array('us_aid'=>$user2['id'],'us_qu'=>0)) ->find();
// //      $this->result($user5);
//         $user6 = db('user')->where(array('us_aid'=>$user2['id'],'us_qu'=>1)) ->find();

//         $info['k'] = 0;
//         $info['p'] = 1000;
//         $user1['k'] = 1;
//         $user1['p'] = 0;
//         $user2['k'] = 2;
//         $user2['p'] = 0;
//         $user3['k'] = 3;
//         $user3['p'] = 1;
//         $user4['k'] = 4;
//         $user4['p'] = 1;
//         $user5['k'] = 5;
//         $user5['p'] = 2;
//         $user6['k'] = 6;
//         $user6['p'] = 2;


//         array_push($list,$info,$user1,$user2,$user3,$user4,$user5,$user6);
//         return $this->msg($list);
//     }

    public function node(){
        if (input('post.us_account')) {
            $info = model('User')->where('us_account|us_tel|us_real_name', input('post.us_account'))->find();
            if (!$info) {
                $this->e_msg('用户不存在');
            }
        }else{
            $info = $this->user;
        }
        $znote = jiedian();

        $this->map[] = ['us_tree', 'like', $info['us_tree'] . "," . $info['id'] . "%"];
        $this->map[] = ['us_tree_long', '<=', $info['us_tree_long'] + 2];
        $list = db('user')->where($this->map)->select();
        array_push($list, $info);
        $rrrrrarr = [
            0 => '[未激活]',
            1 => '[正常]',
            2 => '[被禁用]',
        ];
        for ($i = 0; $i < 8; $i++) {
            if (isset($list[$i])) {
                $arr = $list[$i];
                $status = $rrrrrarr[$arr['us_status']];
                $qu = str_split($arr['us_tree_qu']);
                // $qu = array_reverse($us_tree_qu);
                $length = $list[$i]['us_tree_long'] - $info['us_tree_long'];
                $calcu = cache('level')[$arr['us_level']];
                $qu_ye = qu_yeji($arr['id']);

                if ($length == 0) {
                    $key = 0;
                } elseif ($length == 1) {
                    $key = 2 * $length + $arr['us_qu'] - 1;
                } else {
                    $key = 2 * $length + $arr['us_qu'] + $qu[1] * 2 - 1;
                }
                $znote[$key]['name'] = $arr['us_account'];
                $znote[$key]['tel'] = $arr['us_tel'] . "(" . $arr['us_real_name'] . ")";
                $znote[$key]['zuo'] = "左:" . $qu_ye['l_num']*cache('setting')['cal_bd'].  "," . $qu_ye['l_num'];
                $znote[$key]['you'] = "右:" . $qu_ye['r_num']*cache('setting')['cal_bd'].  "," . $qu_ye['r_num'];
                $znote[$key]['level'] = $calcu['cal_name'].$status;
                $znote[$key]['k'] = $arr['id'];
                $znote[$key]['p'] = $arr['us_aid'];
                if ($arr['us_head_pic']) {
                   $znote[$key]['source'] = $this->request->domain().$arr['us_head_pic'];;
                }
            }
        }
        $this->msg($znote);
    }

    //添加
    public function add() {
        $data = input('post.');
        
        $rel = model('User')->tianjia($data);

        $this->msg($rel);
        if($rel['code']){
            $this->s_msg($rel);
        }else{
            $this->e_msg($rel);
        }
        // $this->msg($rel);
    }
    //激活
    public function active(){
		if(is_post()){
            $data = input('post.');
            if(!$data){
                $this->e_msg('请填写信息');
            }
            $validate = validate('User');
            $res = $validate->scene('addr')->check($data);
            if (!$res) {
                return [
                    'code'  =>  0,
                    'msg'	=>  $validate->getError(),
                ];
            }

            if($this->user['us_status']!=0){
                $this->e_msg('该用户状态不是未激活状态');
            }
            if($this->user['us_wal']<cache('setting')['cal_bd']){
                $this->e_msg('茶币不足');
            }

            $data['us_status'] = 1;
            $data['us_active_time'] = date('Y-m-d H:i:s');
            $arr = array_merge($data,['id'=>$this->user['id']]);
            $rel  = model('User')->editInfo($arr);

			if($rel){
            // if(true){
                model("User")->usWalChange($this->user['id'],cache('setting')['cal_bd'],3);
				// 直推奖
				model('User')->direct_pro($this->user['id']);	
				// 层碰奖励 对碰奖励
				model('User')->ceng_peng_pro($this->user['id']);
                $this->s_msg('报单成功');
			}else{
                $this->s_msg('报单失败');
            }
            
		}
	}

    public function relation()
    {
        if (is_post()) {
            $request = input('post.');
            if ($request['me_content'] == "") {
                $this->e_msg('内容不能为空');
            }
            $data = array(
                'me_title' => '反馈问题',
                'me_content' => $request['me_content'],
                'us_id' => $this->user['id'],
                'me_type' => 2,
            );
            $rel = model('Message')->tianjia($data);
            if ($rel) {
                $this->s_msg('反馈成功');
            } else {
                $this->e_msg('反馈失败');
            }
        }
    }

    /**
	 * 86400 / 24 3600/60    120 两分钟
	 * 修改
	 * @return [type] [description]
	 */
	public function send() {
        $mobile = input('post.us_tel');
        if($mobile){
            if($mobile != $this->user['us_tel']){
                $this->e_msg('手机号不一致');
            }
            if (cache($mobile . 'code')) {
                $this->e_msg('每次发送间隔120秒');
            }else{
                cache($mobile . 'code', 123456,120);
                $this->s_msg('发送成功,现在的验证码是123456');
            }
            $random = mt_rand(100000, 999999);
            $xxx = note_code($mobile, $random);
            $rel = $this->object_array($xxx);
            if ($rel['returnstatus'] == "Faild") {
                $this->e_msg($rel['message']);
            } else {
                cache($mobile . 'code', $random,120);
                $this->s_msg('发送成功');
            }
        }else{
            $this->e_msg("手机号为空");
        }
    }

    // 获取团队
    public function getTeam()
    {
        $map['status'] = 1;
        if (input('param.pid')) {
            $map['pid'] = input('param.pid');
        }

        $list = model('User')->getTeam($map, input('param.size'));
        $this->result($list);
    }

    // 获取账户明细
    public function getAcclog()
    {
        $map['uid'] = $this->user['uid'];

        if (input('param.acc_type')) {
            $map['acc_type'] = input('param.acc_type');
        }

        $list = model('UserAccountRecords')->getList($map, input('param.size'));

        $this->result($list);
    }
    
    // 货币转换rules
    public function coinRules()
    {
        $list = db('user_coin_trans')->where('status', 1)->select();
        $this->result($list);
    }

    // 玩家账户货币转换
    public function coinTrans()
    {
        $post = input('post.');

        $validate = validate('Vuser');
        $rst = $validate->scene('cointrans')->check($post);
        if (! $rst) {
            $this->error($validate->getError());
        } else {
            $rst = model('User')->coinTrans($post, $this->user['uid']);
            $this->success($rst);
        }
        
        $this->error('刷新重试！');
    }

    // 申请成为商家
    public function toshop()
    {
        $post = input('post.');
        $rst = model('User')->beShop($post, $this->user['uid']);
        $this->success($rst);
    }

    // ------------------------------------------------------------------------
    //  转账、买入、卖出
    // ------------------------------------------------------------------------
    // 转账给代理（充值）
    public function userAgent()
    {   
        $post = input('post.');
        $post['type'] = 1;
        $post['uid'] = $this->user['uid'];
        $rst = model('UserAgent')->addInfo($post);
        $this->success($rst);
    }

    // 转账记录列表
    public function transList()
    {
        $map = [
            'type' => 1 //转账记录
        ];
        if (input('param.status')) {
            $map['status'] = input('param.status');
        }
        if (input('param.uid')) {
            $map['uid'] = input('param.uid');
        }
        if (input('param.agent_uid')) {
            $map['agent_uid'] = input('param.agent_uid');
        } else {
            $map['uid'] = input('param.uid');
        }

        $list = model('UserAgent')->getList($map, input('param.size'));
        $this->result($list);
    }
   
    // 卖出
    public function sell()
    {
        $post = input('post.');
        if ($post){
            $rst = model('UserTrade')->addInfo($post, $this->user['uid']);
            $this->success($rst);
        }
    }
    
    // 卖出列表
    public function sellList()
    {   
        $map = [];
        if (input('param.seller_uid')) {
            $map['seller_uid'] = input('param.seller_uid');
        }
        if (input('param.buyer_uid')) {
            $map['buyer_uid'] = input('param.buyer_uid');
        }
        if (is_numeric(input('param.status'))) {
            $map['status'] = input('param.status');
        }
        if (input('param.trade_no')) {
            $map['trade_no'] = ['like', input('param.trade_no').'%'];
        }

        $list = model('UserTrade')->getList($map, input('param.size'));
        $this->result($list);
    }

    // 取消卖出
    public function cancleSell()
    {
        // 交易单号ID
        $id = input('param.id');

        if (encrypt(input('param.s_pwd')) != $this->user['s_pwd']) {
            $this->error('密码有错');
        }

        $rst = model('UserTrade')->reject($id, '会员：'.$this->user['name'].'主动', $this->user['uid']);
        $this->success($rst);
    }
    
    // 买入
    public function buy()
    {
        $post = input('post.');
        $rst = model('UserTrade')->addBuyer($post);

        $this->success($rst);
    }

    // 买入记录 1 待卖家确认，2 交易完成
    public function buyList()
    {
        $map = [];
        $map['buyer_uid'] = $this->user['uid'];
        if (input('param.buyer_uid')) {
            $map['buyer_uid'] = input('param.buyer_uid');
        }
        if (is_numeric(input('param.status'))) {
            $map['status'] = input('param.status');
        }

        $list = model('UserTrade')->getList($map, input('param.size'));
        $this->result($list);
    }

    // 买入确认
    public function sellConfirm()
    {
        $post = input('post.');

        $post['pwd'] = $this->user['s_pwd'];

        $rst = model('UserTrade')->confirm($post);
        $this->success($rst);
    }

    // ------------------------------------------------------------------------
    // 获取短信验证码
    public function smsCode()
    {
        $tel = input('post.mobile');
        if (! $tel) {
            $this->error('手机号码为空');
        }
        if (cache($tel.'code')) {
            $this->error('请勿重复发送');
        }

        $sms = config('sms');

        $sms['password'] = ucfirst(md5($sms['password']));
        $sms['mobile'] = $tel;

        $code = mt_rand(1234, 9876);
        $sms['content']  .= $code;
        $sms['sendTime'] = '';

        $query_str = http_build_query($sms);

        $gateway = "http://114.113.154.5/sms.aspx?action=send&".$query_str;
        $result = file_get_contents($gateway);
        $xml = simplexml_load_string($result);

        if ($xml->returnstatus == 'Faild') {
            $this->error($xml->message);
        }
        cache($tel.'code', $code, 120);
        $this->success('验证码'.$code.'已发送,两分钟内有效');
    }

    //-------------------------------------------------------------------------
    // 留言列表
    public function noteList()
    {   
        $map['uid'] = $this->user['uid'];
        $list = model('Message')->getList($map, input('param.size'));
        $this->result($list);
    }

    // 留言
    public function leaveNote()
    {
        $post = input('post.');
        $post['uid'] = $this->user['uid'];
        $rst = model('Message')->addInfo($post);
        $this->success($rst);
    }

    //-------------------------------------------------------------------------
    // 会员注册
    public function reg()
    {
        $post = input('post.');
        if (config('sms.status')) {
            $smsCode = cache($post['tel'].'code') ?: '';
            if ($smsCode != $post['code'] || $post['code'] == '') {
                $this->error('短信验证码无效！');
            }
        }
        $validate = validate('Vuser');
        $rst = $validate->scene('addUser')->check($post);

        if (! $rst) {
            $this->error($validate->getError());
        } else {
            if (isset($post['p_tel'])) {
                $pid = db('user')->where('tel', $post['p_tel'])->value('uid');
                if ($pid) {
                    $post['pid'] = $pid;
                } else {
                    $this->error('推荐人不存在！');
                }
            }
            $ret = model('User')->addInfo($post);
            $this->success($ret);
        }
        $this->error('稍后重试！');
    }

    // 登录
    public function login()
    {   
        $this->msg($this->user);
    }

}
