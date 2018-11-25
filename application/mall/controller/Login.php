<?php
namespace app\mall\controller;

use think\Config;
use wechat\TPWechat;
use app\common\controller\Base;
use think\Db;
/***
 *
 */
class Login extends Base
{
   /* protected function initialize()
    {
        header("Content-Type:text/html; charset=utf-8");
        //读取配置,判断配置
        if (!cache('config')) {
            $setting = model('Config')->getConfig('web');
            cache('config', $setting);
        }
        config(cache('config'));
        if ($this->is_weixin() && session('openid') == null) {
            if (input('id')) {
                $id = input('id');
            } else {
                $id = 0;
            } 
            $this->getOpenid($id);
        }
    }*/
    // ------------------------------------------------------------------------
    public function login()
    {
        if (is_post()) {
            // halt(cache(''))
            if (cache('setting')['web_status'] == '0') {
                $this->error('系统升级中，暂停登录!');
            }
            $data = input('post.');
            $us = model('User');

            $validate = validate('Login');
            if (!$validate->scene('login')->check($data)) {
               $this->error($validate->getError());
            }

            $flag = 0;
            //$count1 = $us->where('us_tel',$data['username'])->count();
            $count2 = $us->where('us_account',$data['username'])->count();
           /* if($count1){
                $info = $us->where('us_tel',$data['username'])->where('us_pwd',mine_encrypt($data['userpassword']))->find();
                if(!$info){
                    $this->error('密码错误');
                }else{
                    $flag = 1;
                }
            }*/
            if($count2){
                $info = $us->where('us_account',$data['username'])->where('us_pwd',mine_encrypt($data['userpassword']))->find();
                if(!$info){
                    $this->error('密码错误');
                }else{
                    $flag = 1;
                }
            }

            if($flag){
               
                session('us_id',$info['id']);
                session('us_account',$info['us_account']);
                
                $this->success('登录成功');
            }else{
                $this->error('不存在此用户');
            }
        }else{
           return $this->fetch(); 
        }

    }
    // ------------------------------------------------------------------------
    public function reg()
    {
        if (is_post()) {
            $d = request()->post();
            
            
            //验证码
            $code_info = cache($d['us_tel'] . 'code') ?: "";
            if (!$code_info) {
                $this->e_msg('请重新发送验证码');
            } elseif ($d['sode'] != $code_info) {
                $this->e_msg('验证码不正确');
            }

            
            $rel = model('User')->tianjia($d);
            return $rel;
            
            if ($rel) {
                $this->success('注册成功', 'login/index');
            } else {
                $this->error('注册失败');
            }
        } else {
            $id = input('id');
            $us_tel = db('user')->where('id', $id)->value('us_tel');
            if ($us_tel == "") {
                $us_tel = "空";
            }
            $url = "http://yilian360.com/mall/login/perfect";
            $url = 'https://app.i1170.com/oauth/authorize.php?response_type=code&client_id=yilian_health_360&state=' . $us_tel . "&scope=basic integral";
            $this->assign(array(
                'us_name' => $us_tel,
                'url' => $url,
            ));
            return $this->fetch();
        }

    }
    

    public function fg(){
        if(is_post()){
            $da = input('post.');

            $validate = validate('Login');
            if (!$validate->scene('forget')->check($da)) {
               $this->e_msg($validate->getError());
            }

            //验证码
            $code_info = cache($da['us_tel'] . 'code') ?: "";
            if (!$code_info) {
                $this->e_msg('请重新发送验证码');
            } elseif ($da['sode'] != $code_info) {
                $this->e_msg('验证码不正确');
            }

            $info = model('User')->where('us_tel', $da['us_tel'])->where('us_account',$da['us_account'])->field('id')->find();
            if (!$info) {
                $this->error('账号手机号不匹配');
            }
            Db::name('user')->where('id',$info['id'])->setfield('us_pwd',mine_encrypt($da['us_pwd']));

            $this->success('设置成功');


        }else{
            return $this->fetch();
        }
        
    }

    /**
     * 忘记密码
     * @return [type] [description]
     */
    public function forget()
    {
        if (is_post()) {
            $da = input('post.');

            $validate = validate('Front');
            if (!$validate->scene('forget')->check($da)) {
               $this->e_msg($validate->getError());
            }

            //验证码
            $code_info = cache($da['us_tel'] . 'code') ?: "";
            if (!$code_info) {
                $this->e_msg('请重新发送验证码');
            } elseif ($da['sode'] != $code_info) {
                $this->e_msg('验证码不正确');
            }


            $info = model('User')->where('us_tel', $da['us_tel'])->field('id')->find();
            if (!$info) {
                $this->error('非法操作');
            }
            model('User')->where('id',$info['id'])->setfield('us_pwd',mine_encrypt($da['us_pwd']));

            $this->success('设置成功');
           
        }
        return $this->fetch();
    }

    public function find()
    {
        $user = input('post.user');
        $info = model('user')->where('us_name', $user)->field('id,us_tel')->find();
        if ($info) {
            $data = array(
                'code' => 1,
                'data' => $info,
            );
        } else {
            $data = array(
                'code' => 0,
            );
        }
        return $data;
    }
    // ------------------------------------------------------------------------
    public function logout()
    {
        session('mid', null);
        session(null);
        cookie('dead', 0);
        $this->redirect('login/login');
    }

    public function tg()
    {
        return $this->fetch();
    }
    /**
     * 发送短信验证码
     * @return [type] [description]
     */
    public function sendSMS()
    {
        $mobile = input('get.mobile');
        $random = mt_rand(1000, 9999);
        $content = "您的验证码为：" . $random;
        session('note_code', $random);
        session('code_time', time());
        $data = $this->note_code($mobile, $content);
        return $data;
    }
    protected function note_code($mobile, $content)
    {
        header('Content-Type:text/html;charset=utf8');
        $userid = '';
        $account = Config::get('smsaccount');
        $password = Config::get('smspassword');
        $password = md5($password);
        $password = ucfirst($password);
        $content = '【健康360生活】尊敬的会员您好,' . $content;
        $content = urlencode($content);
        $gateway = "http://114.113.154.5/sms.aspx?action=send&userid={$userid}&account={$account}&password={$password}&mobile={$mobile}&content={$content}&sendTime=";
        $result = file_get_contents($gateway);
        $xml = simplexml_load_string($result);
        if ($xml->returnstatus == 'Faild') {
            return array(
                'status' => 'failed',
                'msg' => '系统错误,发送失败',
            );
        }
        return array(
            'status' => 'success',
            'msg' => '短信已发送',
        );
    }

    public function getOpenid($id = "")
    {
        if (!cache('wechat')) {
            cache('wechat', db('config_wechat')->find());
        }
        $wxConfig = cache('wechat');
        $options = [
            'token' => $wxConfig['TOKEN'],
            'encodingaeskey' => $wxConfig['ENCODINGAESKEY'],
            'appid' => $wxConfig['APPID'],
            'appsecret' => $wxConfig['APPSECRET'],
        ];

        $weObj = new TPWechat($options);
        $token = $weObj->getOauthAccessToken();
        if (!$token) {
            $url = $weObj->getOauthRedirect(request()->domain() . url('login/getOpenid'), $id);
            // $url = $weObj->getOauthRedirect(request()->domain() . url('login/getOpenid'));
            header("location: $url");
            return;
        }
        $wxuser = $weObj->getOauthUserinfo($token["access_token"], $token["openid"]);
        if ($wxuser) {
            session("openid", $wxuser["openid"]);
            session('wexinNick', $wxuser['nickname']);
            session('wexinTou', $wxuser['headimgurl']);
        }
        if (input('state')) {
            // halt(input('state'));die;
            $this->redirect('register', ['id' => input('state')]);
        } else {
            $this->redirect('index');
        }

    }
}
