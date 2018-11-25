<?php
namespace app\mall\controller;

use app\common\controller\Base as ba;

/**
 * 网站基类
 */
class Base extends ba {
	public function initialize() {
		parent::initialize();
		
		// if (!request()->isAjax()) {
		// 	session('url', $_SERVER['REQUEST_URI']);
		// }
		// if ($this->is_weixin() && session('openid') == null) {
  //           $this->openid(session('us_id'));
  //       }

		$this->system();
	}
	//网站维护
	public function system() {
		if (cache('setting')['web_status'] == 0) {
			$this->error('网站维护中');
		}
	}
	public function is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {

            return true;
        }
        return false;
    }
    public function openid()
    {
        
        $wxConfig = config('wechat_numb');
        $options = [
            'token' => $wxConfig['token'],
            'encodingaeskey' => $wxConfig['encodingaeskey'],
            'appid' => $wxConfig['appid'],
            'appsecret' => $wxConfig['appsecret'],
        ];
        $weObj = new TPWechat($options);

        $token = $weObj->getOauthAccessToken();
        if (!$token) {
            $url = $weObj->getOauthRedirect(request()->domain() . url('base/openid'), $id, 'snsapi_base');
            // $url = $weObj->getOauthRedirect(request()->domain() . url('login/getOpenid'));
            header("location: $url");
            return;
        }
        $wx = $weObj->getOauthUserinfo($token["access_token"], $token["openid"]);
        session("openid", $wx["openid"]);
        session("us_id", $wx["state"]);
    }
}
