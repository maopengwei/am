<?php
namespace app\mall\controller;

use think\Db;

/**
 *
 */
class User extends Common
{

    public function _initialize()
    {
        parent::_initialize();
    }

     //推荐图
    public function team() {
        if (is_post()) {
            $us_account = input('post.us_account');
            if ($us_account) {
                $info = model('User')->where('us_account|us_tel', input('post.us_account'))->find();
                if (!$info) {
                    return [
                        'code' => 0,
                        'msg' => '该用户不存在',
                    ];
                }
                $arr = explode(',',$info['us_path']);
                if($info['id']!=session('us_id') && !in_array(session('us_id'),$arr)){
                    return [
                        'code' => 0,
                        'msg' => '该用户不在我的团队中',
                    ];
                }
            }else{
                return [
                    'code' => 0,
                    'msg' => '该用户不存在',
                ];
            }
            $this->map[] = ['us_path', 'like', $info['us_path'] . "," . $info['id'] . "%"];
            $this->map[] = ['us_path_long', '<=', $info['us_path_long'] + 2];
            $list = model('User')->where($this->map)->select()->toArray();
            array_push($list, $info);

            foreach ($list as $k => $v) {
                if($v['us_pid']){
                    $p = Db::name('user')->where('id',$v['us_pid'])->value('us_account');
                }else{
                    $p = '空';
                }
                $nn = $v['us_wal']+$v['us_dong'];
                $yeji = yeye($v['id'],$v['us_tree']);
                $znote[$k]['name'] = $v['us_account'];
                $znote[$k]['tel'] = $v['us_tel'] . "(" . $v['us_real_name'] . ")";
                $znote[$k]['zuo'] = "可用资产:".$nn;
                $znote[$k]['you'] = "团队资产:".$yeji;
                /*$znote[$k]['level'] ='推荐人:'.$p;*/
                $znote[$k]['key'] = $v['id'];
                $znote[$k]['parent'] = $v['us_pid'];
                $znote[$k]['source'] = $v['us_head_pic'];
            }

            return [
                'code' => 1,
                'data' => $znote,
                'ptel' =>$info['ptel'],
            ];

        } else {
            //进入节点图的id
            $id = 0;
            if(input('get.id')){
                $id = input('get.id');
            }
            $this->assign(array(
                'us_account' =>$this->mine['us_account'],
                'id'=>$id,
            ));
            return $this->fetch();
        }
    }

    //节点图
    public function node() {
        if (is_post()) {
            $us_account = input('post.us_account');
            if ($us_account) {
                $info = model('User')->where('us_account|us_tel', input('post.us_account'))->find();
                if (!$info) {
                    return [
                        'code' => 0,
                        'msg' => '该用户不存在',
                    ];
                }
                $arr = explode(',',$info['us_tree']);
                if($info['id']!=session('us_id') && !in_array(session('us_id'),$arr)){
                    return [
                        'code' => 0,
                        'msg' => '该用户不在我的团队中',
                    ];
                }
            }else{
                return [
                    'code' => 0,
                    'msg' => '该用户不存在',
                ];
            }
            $this->map[] = ['us_tree', 'like', $info['us_tree'] . "," . $info['id'] . "%"];
            $this->map[] = ['us_tree_long', '<=', $info['us_tree_long'] + 2];
            $list = model('User')->where($this->map)->select()->toArray();
            array_push($list, $info);

            foreach ($list as $k => $v) {
                if($v['us_pid']){
                    $p = Db::name('user')->where('id',$v['us_pid'])->value('us_account');
                }else{
                    $p = '空';
                }
                $nn = $v['us_wal']+$v['us_dong'];
                $yeji = yeji($v['id'],$v['us_tree']);
                $znote[$k]['name'] = $v['us_account'];
                $znote[$k]['tel'] = $v['us_tel'] . "(" . $v['us_real_name'] . ")";
                $znote[$k]['zuo'] = "可用资产:".$nn;
                $znote[$k]['you'] = "团队资产:".$yeji;
                /*$znote[$k]['level'] ='推荐人:'.$p;*/
                $znote[$k]['key'] = $v['id'];
                $znote[$k]['parent'] = $v['us_pid'];
                $znote[$k]['source'] = $v['us_head_pic'];
            }

            return [
                'code' => 1,
                'data' => $znote,
                'ptel' =>$info['ptel'],
            ];

        } else {
            //进入节点图的id
            $id = 0;
            if(input('get.id')){
                $id = input('get.id');
            }
            $this->assign(array(
                'us_account' =>$this->mine['us_account'],
                'id'=>$id,
            ));
            return $this->fetch();
        }
    }

    //反馈
    public function back(){
        if (is_post()) {
            $d = input('post.');
            if ($d['me_title'] == "") {
                $this->error('请输入标题');
            }
            if ($d['me_content'] == "") {
                $this->error('内容不能为空');
            }
            $data = array(
                'me_title' => $d['me_title'],
                'me_content' => $d['me_content'],
                'us_id' => $this->mine['id'],
                'me_type' => 2,
            );
            $rel = model('Message')->tianjia($data);
            if ($rel) {
                $this->success('反馈成功');
            } else {
                $this->error('反馈失败');
            }
        }else{
            return $this->fetch();
        }
    }
    public function qr_code(){
        $url = "http://".$_SERVER['HTTP_HOST'].'/mall/login/reg?p_acc='.$this->mine['us_account'];
        $this->assign(array(
            'url' => $url,
        ));
        return $this->fetch();
    }

    public function index()
    {
        $this->map[] = ['us_id','=',$this->mine['id']];
        $this->map[] = ['wal_type','=',10];
        $kuang = Db::name('ProWal')->where($this->map)->whereTime('wal_add_time','yesterday')->sum('wal_num');
        $this->assign('kuang',$kuang);


        $where[] = ['wal_type','in',[7,9]];
        $where[] = ['us_id','=',$this->mine['id']];
        $tui = Db::name('ProWal')->where($where)->sum('wal_num');
         $this->assign('tui',$tui);
        return $this->fetch();
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

            if($this->mine['us_tel'] !=$d['us_tel']){
                 $this->error('手机号不正确');
            }


            //验证码
            $code_info = cache($d['us_tel'] . 'code') ?: "";
            if (!$code_info) {
                $this->e_msg('请重新发送验证码');
            } elseif ($d['sode'] != $code_info) {
                $this->e_msg('验证码不正确');
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
    


    public function login_pass()
    {
        if (is_post()) {
            $data = input('post.');
            if ($data['us_pwd'] == '') {
                $this->error('新密码不能为空');
            }
            if ($data['us_pwd'] != $data['pass1']) {
                $this->error('两次输入新密码不同');
            }
            if($this->mine['us_pwd'] != mine_encrypt($data['old_pass'])){
                 $this->error('原密码错误');
            }

            $pass = mine_encrypt($data['us_pwd']);
            model('User')->where('id', session('us_id'))->setfield('us_pwd', $pass);
            $this->success('设置成功', 'user/account');
        }
        return $this->fetch();
    }
    //设置支付密码
    public function pay_pass()
    {
        if (is_post()) {
            $data = input('post.');
            // if ($data['us_tel'] != $info['us_tel']) {
            //     $this->error('非法操作');
            // }
            //验证码
            $code_info = cache($data['us_tel'] . 'code') ?: "";
            if (!$code_info) {
                $this->e_msg('请重新发送验证码');
            } elseif ($data['note_code'] != $code_info) {
                $this->e_msg('验证码不正确');
            }

            if ($data['pay_pwd'] == '') {
                $this->error('新支付密码不能为空');
            }
            if ($data['pay_pwd'] != $data['pass1']) {
                $this->error('两次输入支付密码不同');
            }
       
            $pay_pwd = mine_encrypt($data['pay_pwd']);
          
            model('User')->where('id', session('us_id'))->setfield('us_safe_pwd', $pay_pwd);
            $this->success('设置成功', 'user/account');
            
        }
        $this->assign('tel', session('tel'));
        return $this->fetch();
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
