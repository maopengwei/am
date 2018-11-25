<?php
namespace app\mall\controller;

use think\facade\Config;
use sms\Sms;
/**
 * 图片验证码
 */
class Every extends Base {

	public function initialize() {
		parent::initialize();
	}
	/**
	 * 通过订单调用回调地址
	 * @return [type] [回调结果]
	 */
	public function notify() {
		$number = 'als20180702165101999';
		$rel = model('AlipayPay')->back_success($number);
	}
	//上传图片 对象
	public function upload() {
		$bb = env('ROOT_PATH');
		$file = request()->file('file');
		$info = $file->validate(['size' => '4096000'])
			->move($bb . 'public/uploads/');
		if ($info) {
			$path = '/uploads/' . $info->getsavename();
			$path = str_replace('\\', '/', $path);
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

	/**
	 * 上传图片 字符串
	 */
	public function sctp() {
		try {
			$rel = base64_upload(input('post.img'));
		} catch (\Exception $e) {
			$this->e_msg($e->getMessage());
		}
		if ($rel) {
			$this->s_msg('上传成功', $rel);
		} else {
			$this->e_msg();
		}
	}
	protected function object_to_array($obj) {
		$obj = (array) $obj;
		foreach ($obj as $k => $v) {
			if (gettype($v) == 'resource') {
				return;
			}
		}

		return $obj;
	}
	/**
	 * 86400 / 24 3600/60    120 两分钟
	 * 验证码
	 * @return [type] [description]
	 */
	public function send() {
        $mobile = input('post.us_tel');
        $type   = input('post.type');
        if(!$type){
            $this->error('请填入短信类型');
        }
        if($mobile){
            if(db('user')->where('us_tel', $mobile)->count()){
                if ('reg' === $type) {
                    $this->error('该手机号已注册');
                }
            }else{
                // 忘记密码/登陆  获取验证码
                if ('fg' == $type) {
                    $this->error('该手机号未注册账户');
                }
                
            }
            cache($mobile . 'code', 123456,120);
            $this->success('发送成功,现在的验证码是123456');
            if (cache($mobile . 'code')) {
                $this->error('每次发送间隔120秒');
            }
            // else{
            //     cache($mobile . 'code', 123456,120);
            //     $this->success('发送成功,现在的验证码是123456');
            // }

            $random = mt_rand(100000, 999999);
            $xxx = $this->notecode($mobile, $random);
           
            // $rel = $this->object_array($xxx);
            if ($xxx['code']) {
                $this->e_msg($xxx['errorMsg']);
            } else {
                cache($mobile . 'code', $random,120);
                $this->s_msg('发送成功');
            }
        }else{
            $this->e_msg("手机号为空");
        }
    }
    public function notecode($tel,$code){
    	$clapi  = new Sms();
		
    	$content = '【AMFC】您好，您的验证码'.$code;
		//设置您要发送的内容：其中“【】”中括号为运营商签名符号，多签名内容前置添加提交
		$result = $clapi->sendSMS($tel,$content);

		if(!is_null(json_decode($result))){
			
			$output=json_decode($result,true);
			return $output;
			if(isset($output['code'])  && $output['code']=='0'){
				echo '发送成功';
			}else{
				echo $output['errorMsg'];
			}
		}else{
				echo $result; 
		}
    }




	//所有删除
	public function alldel() {
		if (input('post.id')) {
			$id = input('post.id');
		} else {
			$this->e_msg('id不存在');
		}
		if (input('post.key')) {
			$key = input('post.key');
		} else {
			$this->e_msg('数据表不存在');
		}
		$array = array(
			'Admin', 'Carouse', 'Center', 'Code', 'Message', 'Msc', 'Order', 'Product', 'Wallet', 'Tixian', 'Transfer', 'Cate', 'User', 'UserAddr', 'Courier',
		);
		if (!in_array($key, $array)) {
			$this->e_msg('非法操作');
		}
		$info = model($key)->get($id);
		if ($info) {
			$rel = model($key)->destroy($id);
			if ($rel) {
				$this->s_msg('删除成功');
			} else {
				$this->e_msg('请联系网站管理员');
			}
		} else {
			$this->error('数据不存在');
		}
	}
	
	public function config() {
		$this->s_msg(null, cache('setting'));
	}

	// /**
	//  * hmacMd5
	//  */
	// function aabbc($bearer, $pass, $url) {
	// 	$pass1 = HmacMd5($pass, $pass);
	// 	$str = jsEncode($url, $pass1);
	// 	$sstr = $bearer . ':' . $str;
	// 	return $sstr;
	// }

	// public function ceshii() {
	// 	$aa = $this->aabbc("lzq1", "13800000000", "/user/login:" . $this->getMillisecond());
	// 	halt($aa);
	// }

	function getMillisecond() {
		list($s1, $s2) = explode(' ', microtime());
		return (float) sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
	}

	private function note_code22($mobile, $content) {
		header('Content-Type:text/html;charset=utf8');
		$sms = Config::get('sms');
		$sms['password'] = ucfirst(md5($sms['password']));
		$sms['content'] = $sms['content'] . $content;
		// $sms['content'] = urlencode($sms['content']);
		$sms['mobile'] = $mobile;
		$query_str = http_build_query($sms);
		$gateway = "http://114.113.154.5/sms.aspx?action=send&" . $query_str;
		// dump($gateway);
		// echo "<br />";
		// $gateway = "http://114.113.154.5/sms.aspx?action=send&userid={$sms['userid']}&account={$sms['account']}&password={$sms['password']}&mobile={$mobile}&content={$sms['content']}&sendTime=";
		// dump($gateway);
		$url = preg_replace("/ /", "%20", $gateway);
		$result = file_get_contents($url);
		$xml = simplexml_load_string($result);
		return $this->object_array($xml);
	}

/*----------------------------*/
	// public function tokenTest() {

	// 	$encryptedData = 'RFmgbCytuuQKIkar9fGKqAl3KIvH72OQX2LjQ8bCHe4=';

	// 	$privateKey = 'b2a1ec0f3e0607099d7f39791c04e9a4';

	// 	$iv = "O2%=!ExPCuY6SKX(";

	// 	$encryptedData = base64_decode($encryptedData);
	// 	// mcrypt_decrypt php7.1以后，不建议用

	// 	// $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $privateKey, $encryptedData, MCRYPT_MODE_CBC, $iv);

	// 	$decrypted = openssl_decrypt($encryptedData, 'AES192', $privateKey, 1, $iv);

	// 	$decrypted = rtrim($decrypted, "\0");

	// 	halt($decrypted);

	// }

}
