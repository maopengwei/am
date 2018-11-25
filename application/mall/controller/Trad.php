<?php
namespace app\mall\controller;

use think\Db;

/**
 *
 */
class Trad extends Common
{

    public function _initialize()
    {
        parent::_initialize();
    }

    // public function qr_code(){
    //     $url = "http://".$_SERVER['HTTP_HOST'].'/mall/login/reg?p_acc='.$this->mine['us_account'];
    //     $this->assign(array(
    //         'url' => $url,
    //     ));
    //     return $this->fetch();
    // }

    public function index()
    {
        /*
        卖出单
        我的交易中的单子
         */
        // $this->map[] = ['us_id','=',$this->mine['id']];
        $sell = model('HangIssue')->chaxun($this->map,$this->order,$this->size);
        $aa[] = ['us_id|us_to_id','=',$this->mine['id']];
        $list = model('HangDeal')->with('buyer,seller')->where($aa)->order('id desc')->select();
        foreach ($list as $k => $v) {
            if($v['us_id']==$this->mine['id']){
                $list[$k]['ru'] = 0;
            }else{
                $list[$k]['ru'] = 1;
            }
        }
        $dd = date('Y-m-d');
        $price = Db::name('sys_price')->where('time',$dd)->value('price');
        // $ = model('HangDeal')
        $this->assign([
            'price' => $price,
            'sell' => $sell,
            'list' => $list,
        ]);

        return $this->fetch();
    }

    // //发布买入 
    // public function buy(){
        
    //     if(is_post()){
    //         $d = input('post.');
    //         $validate = validate('Trad');
    //         $res = $validate->scene('buy')->check($d);
    //         if (!$res) {
    //             $this->e_msg($validate->getError());
    //         }
    //         if($this->mine['us_safe_pwd'] != mine_encrypt($d['us_safe_pwd'])){
    //              $this->error('交易密码错误');
    //         }

    //     }

    // }
    //发布卖出  
    public function sell(){
        if(is_post()){
            
            $d = input('post.');
            $validate = validate('Trad');
            $res = $validate->scene('buy')->check($d);
            if (!$res) {
                $this->e_msg($validate->getError());
            }
            
            if($this->mine['us_safe_pwd'] != mine_encrypt($d['us_safe_pwd'])){
                 $this->error('交易密码错误');
            }

            if($d['issue_num']>$this->mine['us_wal']){
                return['code'=>0,'msg'=>'可用资产不足'];
            }
            
            $d['us_id'] = $this->mine['id'];
            $rel = model('HangIssue')->tianjia($d);
            return['code'=>1,'msg'=>'挂卖成功'];
        
        }

    }
    // //买入单详情 + 卖出   
    // public function chu(){

    // }
    //卖出单详情  ＋  买入
    public function ru(){
        if(is_post()){
            $d = input('post.');
            $info = Db::name('hang_issue')->where('id',$d['id'])->find();
            
            if(!$info){
                return['code'=>0,'msg'=>'该单子不存在'];
            }

            if($info['us_id']==$this->mine['id']){
                // return['code'=>0,'msg'=>'您不能购买自己的单子'];
            }
            
            if(mine_encrypt($d['us_safe_pwd']) != $this->mine['us_safe_pwd']){
                $this->error('安全密码不正确');
            }
            
            $arr = [
                'us_id' => $info['us_id'],
                'us_to_id'=>$this->mine['id'],
                'deal_num' => $info['issue_num'],
                'deal_price'=>$info['issue_price'],
                'deal_yuan'=>$info['issue_yuan'],
                'deal_number' => 'AM'.getNumber(13), 
            ];
            $rel = model("HangDeal")->tianjia($arr);
            if($rel){
                Db::name('hang_issue')->where('id',$info['id'])->delete();
                return['code'=>1,'msg'=>'购买成功'];
            }else{
                return['code'=>0,'msg'=>'购买失败'];
            }
        }else{
            $info = model('HangIssue')->detail(['id'=>input('id')]);
            $this->assign([
                'info' => $info,
            ]);
            return $this->fetch();
        }
    }
    //详情
    public function detail(){
        $id = input('id');
        if($id){
            $info = model("HangDeal")->detail(['id'=>$id]);
        }else{
            $this->error('请传入id');
        }

        if($info['us_id'] == $this->mine['id']){
            $info['user'] = $info->buyer;
            $info['zt'] = 0;
        }else{
            $info['user'] = $info->seller;
             $info['zt'] = 1;
        }
        $this->assign([
            'info'=>$info,
        ]);
        return $this->fetch();
    }
    //提交打款凭证
    public function dak(){
        if(is_post()){
            $d = input('post.');
            $info = model('HangDeal')->detail(['id'=>$d['id']]);
            if(!$info){
                return['code'=>0,'msg'=>'该单子不存在'];
            }
            if(mine_encrypt($d['us_safe_pwd']) != $this->mine['us_safe_pwd']){
                $this->error('交易密码不正确');
            }
            if($info['deal_status']!=0){
                return['code'=>0,'msg'=>'该单子不是待付款状态'];
            }
            if(!$d['deal_pic']){
                return['code'=>0,'msg'=>'请上传打款凭证'];
            }
            $arr = [
                'deal_pic'=>$d['deal_pic'],
                'deal_status'=>1,
                'deal_pay_time'=>date('Y-m-d H:i:s'),
                'id'=>$d['id'],
            ];
            model('HangDeal')->update($arr);
            return['code'=>1,'msg'=>'确定成功，等待对面确认'];
        }else{
            $this->error('get');
        }
    }
    //确认收款
    public function receive(){
        if(is_post()){

            $d = input('post.');
            $info = model('HangDeal')->detail(['id'=>$d['id']]);
            if(!$info){
                return['code'=>0,'msg'=>'该单子不存在'];
            }
            if($info['deal_status']!=1){
                return['code'=>0,'msg'=>'该单子不是待收款状态'];
            }
            $arr = [
                'deal_status'=>2,
                'deal_finish_time'=>date('Y-m-d H:i:s'),
                'id'=>$d['id'],
            ];
            model('HangDeal')->update($arr);
            model('User')->usWalChange($info['us_to_id'],$info['deal_num'],4);
            return['code'=>1,'msg'=>'交易完成'];
        }
    }

    public function setting(){
        return $this->fetch();
    }


    //修改昵称
    public function change()
    {
        $key = input('key');
        if($key ==1){
            $kk = 'us_nick_name';
        }elseif($key ==2){
            $kk = 'us_wechat';
        }else{
            $kk = 'us_alipay';
        }
        if (is_post()) {
            $d = input('post.');
            $arr['id'] = session('us_id');
            $arr[$kk] = $d['value'];
            $rel = model('User')->update($arr);
            return 1;
        }
       
        $this->assign(array(
            'kk' => $kk,
            'key' => $key,
        ));
        return $this->fetch();
    }

    public function head(){

        $bb = env('ROOT_PATH');
        $file = request()->file('file');
        $info = $file->validate(['size' => '4096000'])
            ->move($bb . 'public/uploads/');
        if ($info) {
            $path = '/uploads/' . $info->getsavename();
            $path = str_replace('\\', '/', $path);
            Db::name('user')->where('id',$this->mine['id'])->setfield('us_head_pic',$path);
            return $data = array(
                'code' => 1,
                'msg' => '上传成功',
                'data' => $path,
            );
        } else {
            return $data = array(
                'msg' => $file->getError(),
                'code' => 0,
            );
        }
    }

    public function pass(){


        if (is_post()) {
            $d = input('post.');
             $validate = validate('User');
            $res = $validate->scene('pass')->check($d);
            if (!$res) {
                $this->e_msg($validate->getError());
            }

            if($this->mine['us_pwd'] != mine_encrypt($d['old_pwd'])){
                 $this->error('原密码错误');
            }

            $pass = mine_encrypt($d['us_pwd']);

            $rel = Db::name('user')->where('id',$this->mine['id'])->setfield('us_pwd',$pass);
            if($rel){
                $this->success('修改成功');
            }else{
                $this->error('您并没有做出修改');
            }
            
        }else{
            return $this->fetch();
        }
      
    }

    public function safe(){


        if (is_post()) {
            $d = input('post.');
             $validate = validate('User');
            $res = $validate->scene('safe')->check($d);
            if (!$res) {
                $this->e_msg($validate->getError());
            }
            if($this->mine['us_safe_pwd'] != mine_encrypt($d['old_pwd'])){
                 $this->error('原交易密码错误');
            }

            $pass = mine_encrypt($d['us_pwd']);

            $rel = Db::name('user')->where('id',$this->mine['id'])->setfield('us_safe_pwd',$pass);
            if($rel){
                $this->success('修改成功');
            }else{
                $this->error('您并没有做出修改');
            }
            
        }else{
            return $this->fetch();
        }
      
    }

    public function account(){


        if (is_post()) {
            $d = input('post.');
            if(!$d['us_real_name']){
                $this->error('真实姓名不能为空'); 
            }

            $rel = Db::name('user')->where('id',$this->mine['id'])->update($d);
            if($rel){
                $this->success('修改成功');
            }else{
                $this->error('您并没有做出修改');
            }
            
        }else{
            return $this->fetch();
        }
      
    }
    //账户信息
    // public function account()
    // {
    //     if (request()->isPost()) {
    //         $request = request()->post();
    //         $data = array(
    //             'realname' => $request['realname'],
    //             'alipay' => $request['alipay'],
    //             'wechat' => $request['wechat'],
    //             'card_number' => $request['card_number'],
    //             'bank_name' => $request['bank_name'],
    //             'bank_address' => $request['bank_address'],
    //         );
    //         $info = db('user_info')->where('uid', session('mid'))->find();
    //         if ($info) {
    //             $rel = db('user_info')->where('uid', session('mid'))->update($data);
    //         } else {
    //             $data['uid'] = session('mid');
    //             $rel = db('user_info')->insert($data);
    //         }
    //         $datb['nick'] = $request['nick'];
    //         $rel1 = db('user')->where('id', session('mid'))->update($datb);
    //         if ($rel || $rel1) {
    //             $this->success('保存成功');
    //         } else {
    //             $this->error('保存失败');
    //         }
    //     }
       
    //     return $this->fetch();
    // }
    //反馈
    public function back(){
        if (is_post()) {
            $d = input('post.');
            if ($d['me_content'] == "") {
                $this->error('内容不能为空');
            }
            $data = array(
                'me_title' => '提出问题',
                'me_content' => $d['me_content'],
                'us_id' => session('us_id'),
                'me_type' => 2,
            );
            $rel = model('Message')->tianjia($data);
            if ($rel) {
                $this->success('反馈成功');
            } else {
                $this->error('反馈失败');
            }
        }
    }


  
    //注册
    public function register()
    {
        if (request()->isPost()) {
            $request = request()->post();
            if ($request['note_code'] == '') {
                $this->error('验证码为空');
            }
            if ($request['note_code'] != session('note_code')) {
                $this->error('验证码错误');
            }
            $info = Db::name('user')->where('us_tel', $request['us_tel'])->field('id,us_pwd')->find();
            if ($info) {
                $this->error('该手机号已注册');
            }

            if ($request['us_pwd'] == "") {
                $this->error('新登录密码不能是空');
            }
            if ($request['pass1'] != $request['us_pwd']) {
                $this->error('两次输入密码不相同');
            }


            $validate = validate('Verify');
            if (!$validate->scene('addUser')->check($data)) {
             $this->e_msg($validate->getError());
            }

            
            $parent = model('user')->where('id', session('mid'))->find();
            if ($parent) {
                $us_referrer = $parent['id'];
                $path = $parent['path'] . ',' . $parent['id'];
            } else {
                $this->error('推荐人不存在');
            }
            //账户名
            $yonghu = db('user')->order('id desc')->find();
            $old_name = $yonghu['us_name'];
            if ($old_name) {
                $bb = substr($old_name, -9);
                $cc = substr($old_name, 0, 4);
                $dd = $bb + 1;
                $us_name = $cc . $dd;
            } else {
                $aa = 'yljk360000001';
            }
            $data = array(
                'us_name' => $us_name,
                'us_tel' => $request['us_tel'],
                'us_pwd' => $request['us_pwd'],
                'us_referrer' => $us_referrer,
                'path' => $path,
            );
            $rel = model('User')->save($data);
            if ($rel) {
                $this->success('注册成功');
            } else {
                $this->error('注册失败');
            }
        }
        $this->assign('tname', db('user')->where('id', session('mid'))->value('us_tel'));
        return $this->fetch();
    }
    
    //账户信息
    public function info()
    {
        if (is_post()) {
            $data = input('post.');
            $data['id'] = session('us_id');
            model('User')->update($data);
            return ['code'=>1,'msg'=>'保存成功'];
        }
        return $this->fetch();
    }
    //分享
    public function share()
    {

        $url = "http://" . $_SERVER['HTTP_HOST'] . "/mall/login/register?id=" . session('us_id');

        $this->assign(array(
            'url' => $url,
        ));
        return $this->fetch();
    }
    //人物头像
    public function upload_avatar()
    {
        $file = request()->file('upload_file');
        $info = $file->validate(['size' => '4096000'])
            ->move(env('ROOT_PATH') . 'public/uploads/avatar/');
        if ($info) {
            $path = '/uploads/avatar/' . $info->getsavename();
            $avatar = model('user')->get(session('us_id'))['us_head_pic'];
            $avatar = env('ROOT_PATH') . 'public' . $avatar;
            $rel = db('user')->where('id', session('us_id'))->setfield('us_head_pic', $path);
            if ($rel) {
                unlink($avatar);
                return $data = array(
                    'code' => 1,
                    'msg' => '上传成功',
                    'data' => $path,
                );
            } else {
                $this->error('上传失败');
            }
        } else {
            return $data = array(
                'msg' => $file->getError(),
            );
        }
    }

    //绩效管理 我的团队
    public function team()
    {
        if (input('get.id') != "") {
            $id = input('get.id');
        } else {
            $id = session('mid');
        }
        $info = model('user')->get($id);
        $yeji = 123;
        $path1 = count(explode(',', $this->mine['us_path']));
        $path = db('user')->where('id', session('us_id'))->value('us_path');
        $path2 = count(explode(',', $path));
        $direct_count = 2;
        $list = model('user')->where('us_pid', $this->mine['id'])->select();

        $this->assign(array(
            'info' => $info,
            'yeji' => $yeji,
            'direct_count' => $direct_count,
            'list' => $list,
            'path1' => $path1,
            'path2' => $path2,
            // 'team_yeji' => 100,
        ));
        return $this->fetch();
    }
    /**
     * 封装团队数据
     * @return [type] [description]
     */
    public function gettreeso()
    {
        $info = model('User')->where('id', session('mid'))->field('id,path,us_referrer,us_name')->find();
        $array = explode(',', $info['path']);
        $length = count($array);
        $n = 2;
        $yeji = get_team_yeji($info['id']);
        $base = array(
            'id' => $info['id'],
            'pId' => $info['us_referrer'],
            'name' => $info['us_name'] . ",业绩：" . $yeji,
        );
        $znote[] = $base;
        $path['path'] = array('like', $info['path'] . "," . $info['id'] . "%");
        $list = DB('user')->where($path)->field('id,path,us_referrer,us_name')->select();
        foreach ($list as $k => $v) {
            $arr = explode(',', $v['path']);
            $count = count($arr);
            if ($count <= $n + $length) {
                $yeye = get_team_yeji($v['id']);
                $base = array(
                    'id' => $v['id'],
                    'pId' => $v['us_referrer'],
                    'name' => $v['us_name'] . ",业绩：" . $yeye,
                );
                $znote[] = $base;
            }
        }
        echo json_encode(array("status" => 0, "data" => $znote));
    }
    //我的收藏
    public function collect()
    {

        $arr = [];
        if ($this->mine['us_collect'] != "") {
            $array = explode(',', $this->mine['us_collect']);
            foreach ($array as $k => $v) {
                if ($v) {
                    $arr[$k] = model('StoProd')->detail(['id'=>$v]);
                }
            }
        }
        $this->assign(array(
            'arr' => $arr,
        ));
        return $this->fetch();
    }
    //收藏取消
    public function coll()
    {
        if (is_Post()) {
            $da = input('post.');
            if ($da['prod_id'] == '') {
                $this->error('非法操作');
            }
            $arr = explode(',', $this->mine['us_collect']);
            if ($da['type'] == 1) {
               
                if (!in_array($da['prod_id'], $arr)) {
                    array_push($arr, $da['prod_id']);
                    $arr = implode(',', $arr);
                    $rel = model('User')->where('id', session('us_id'))->setfield('us_collect', $arr);
                    $this->error('收藏成功');
                } else {
                    $this->error('非法操作');
                }
               
            } else {
               
                $key = array_search($da['prod_id'], $arr);
                if ($key !== false) {
                    array_splice($arr, $key, 1);
                    $arr = implode(',', $arr);
                    $rel = model('User')->where('id', session('us_id'))->setfield('us_collect', $arr);
                    $this->success('取消收藏成功');
                } else {
                    $this->error('非法操作');
                }

            }
        }
    }



    public function relation()
    {
        if (is_post()) {
            $request = input('post.');
            // if ($request['content'] == "") {
            //     $this->error('内容不能为空');
            // }
            $data = array(
                'me_title' => '提出问题',
                'me_content' => $request['me_content'],
                'us_id' => session('mid'),
                'me_type' => 2,
            );
            $rel = model('Message')->tianjia($data);
            if ($rel) {
                $this->success('反馈成功');
            } else {
                $this->error('反馈失败');
            }
        }
        return $this->fetch();
    }
    
}
