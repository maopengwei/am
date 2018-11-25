<?php
namespace app\index\controller;
use think\facade\Config;
use app\common\controller\Api;

class Total extends Api 
{
    //一条新闻
    public function news(){
        $this->map[] = ['me_type','=',1];
        $list = model('Message')->where($this->map)->order($this->order)->find();
        $this->msg($list);
    }
    /**
	 * 上传图片
	 * @return [type] [description]
	 */
	public function uploads() {
		try {
			$rel = base64_upload(input('post.img'));
		} catch (\Exception $e) {
			$this->e_msg($e->getMessage());
		}
		if ($rel) {
            $arr = [
                'code'=>1,
                'msg' => "成功",
                'data' => $rel,
            ];
            $this->msg($arr);
		} else {
			$this->e_msg('失败');
        }
       
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
            $this->e_msg('请填入短信类型');
        }
        if($mobile){
            if(db('user')->where('us_tel', $mobile)->count()){
                if ('reg' === $type) {
                    $this->e_msg('该手机号已注册');
                }
            }else{
                // 忘记密码/登陆  获取验证码
                if ('fg' == $type) {
                    $this->e_msg('该手机号未注册账户');
                }
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

    public function fg(){
        $post = input('post.');
        if ($post) {

            $validate = validate('User');
            $res = $validate->scene('pass')->check($post);
            if (!$res) {
                $this->e_msg($validate->getError());
            }
           
            $info = db('user')->where('us_tel',$post['us_tel'])->find();
            if(!$info){
                $this->e_msg('该用户不存在');
            }
            $code_info = cache($post['us_tel'] . 'code') ?: "";
            if (!$code_info) {
                $this->e_msg('请重新发送验证码');
            } elseif ($post['sode'] != $code_info) {
                $this->e_msg('验证码不正确');
            }

            $arr = array_merge($post,['id'=>$info['id']]);
            $rst  = model('User')->editInfo($arr);
            $this->s_msg($rst);

        }else{
            $this->e_msg();
        }
    }
   
}
