<?php
namespace app\mall\controller;

use think\Controller;
use wechat\TPWechat;

/**
 * 微信获取openid控制器
 *
 *
 */
class Auth extends Controller
{

    // get openid
    public function index()
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
            $url = $weObj->getOauthRedirect(request()->domain() . url('Auth/index'), '');
            header("location: $url");
            return;
        }
        $wxuser = $weObj->getOauthUserinfo($token["access_token"], $token["openid"]);
        session("openid", $wxuser["openid"]);
        session('wexinNick', $wxuser['nickname']);
        session('wexinTou', $wxuser['headimgurl']);
        $this->redirect('user/index');
    }

}
