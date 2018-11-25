<?php
namespace app\index\controller;

use think\Facade\Request;
use think\Response;
use think\exception\HttpResponseException;
use app\common\controller\Api;

/**
 * 需要登录基类
 */
class Base extends Api {
	public $user;
	// public function initialize() {
	// 	parent::initialize();
	// 	/*获取头部信息*/
	// 	$header = $this->request->header();
	// 	$authToken = null;
	// 	if (key_exists('authtoken', $header)) {
	// 		$authToken = $header['authtoken'];
	// 	}
	// 	if ($authToken) {
    //         $authToken = explode(':', $authToken);
    //         $this->user = model('User')->where("us_tel", $authToken[0])->find();
	// 	} else {
	// 		$this->e_msg("token不存在");
    //     }

    //     if (empty($this->user)) {
	// 		$this->e_msg("账号不存在");
	// 	}

    //     if (!cache('setting')['web_status']) {
	// 		$this->e_msg("网站维护");
    //     }

    //     $password = $this->user['us_pwd'];

    //     $dataStr = $this->jsDecrypt($authToken[1], $password);

    //     $dataStr = explode(':', $dataStr);

    //     if (empty($dataStr)) {
    //         $this->e_msg('no access');
    //     }
    //     if ($dataStr[0] != $_SERVER['REQUEST_URI']) {
    //         $this->e_msg('账户信息不正确');
    //     }
	// }
    public function initialize(){
        parent::initialize();
        $this->user = model('User')->where("us_tel",13000000000)->find();
    }
    /**
     * 解密token数据
     * @param [type] $encryptedData
     * @param [type] $privateKey
     * @param string $iv
     * @return void
     */
    private function jsDecrypt($encryptedData, $privateKey, $iv = "O2%=!ExPCuY6SKX(")
    {
        $encryptedData = base64_decode($encryptedData);
        // mcrypt_decrypt php7.1以后，不建议用
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $privateKey, $encryptedData, MCRYPT_MODE_CBC, $iv);

        $decrypted = rtrim($decrypted, "\0");

        return $decrypted;
    }
    /**
     * Md5合成
     */
    function HmacMd5($data, $key) {
        //需要配置环境支持iconv,否则中文参数不能正常处理
        $b = 64;
        if (strlen($key) > $b) {
            $key = pack("H*", md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;
        return md5($k_opad . pack("H*", md5($k_ipad . $data)));
    }

    

    //解密
    // function jsDecrypt($encryptedData, $privateKey, $iv = "O2%=!ExPCuY6SKX(") {
    //  $encryptedData = base64_decode($encryptedData);
    //  $decrypted = openssl_decrypt($encryptedData, 'AES128', $privateKey, OPENSSL_RAW_DATA, $iv);
    //  $decrypted = rtrim($decrypted, "\0");
    //  return $decrypted;
    // }

    //加密
    // function jsEncode($encodeData, $privateKey, $iv = "O2%=!ExPCuY6SKX(") {
    //  $encode = base64_encode(openssl_encrypt($encodeData, 'AES128', $privateKey, OPENSSL_RAW_DATA, $iv));
    //  $encode = rtrim($encode, "\0");
    //  return $encode;
    // }

}
