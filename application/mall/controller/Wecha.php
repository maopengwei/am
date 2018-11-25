<?php
namespace app\mall\controller;

// use app\common\model\Order;
use think\Controller;
use think\Log;
use wechat\Tpwechat;
use wxpay\database\WxPayUnifiedOrder;
use wxpay\JsApiPay;
use wxpay\PayNotifyCallBack;
use wxpay\WxPayApi;
use wxpay\WxPayConfig;

class Wecha extends Controller
{
    public function init()
    {
        /* 读取微信设置数据 */
        if (!cache('wechat')) {
            cache('wechat', db('config_wechat')->find());
        }
        $wxConfig = cache('wechat');
        // $wxConfig = db('config_wechat')->find();
        $options = [
            'token' => $wxConfig['TOKEN'],
            'encodingaeskey' => $wxConfig['ENCODINGAESKEY'],
            'appid' => $wxConfig['APPID'],
            'appsecret' => $wxConfig['APPSECRET'],
        ];
        $weObj = new TPWechat($options);
        return $weObj;
    }
    /**
     * 微信支付使用 JSAPI 的样例
     * @return mixed
     */
    public function index()
    {
        $da = input('get.');
        halt($da);
        $number = '';
        $table = '';
        $type = input('type');
        $num = input('money');
        $uid = input('uid');
        $number = input('number');
        $table = input('table');
        $orderid = "kyb" . date("YmdHis") . rand(100, 999);
        model('PayRecord')->tianjia($uid, $orderid, $num, $type, $number, $table);
        $body = pay_type($type);
        //获取用户openid
        $tools = new JsApiPay();
        if (session('openid')) {
            $openId = session('openid');
        } else {
            $openId = $tools->getOpenid();
        }
        $money = $num * 100;
        //统一下单
        $input = new WxPayUnifiedOrder();
        $input->setBody($body);
        // $input->setAttach("test");
        $input->setOutTradeNo(WxPayConfig::$MCHID . date("YmdHis"));
        $input->setTotalFee($money);
        $input->setTimeStart(date("YmdHis"));
        $input->setTimeExpire(date("YmdHis", time() + 600));
        // $input->setGoodsTag("Reward");
        $input->setNotifyUrl("http://www.yilian360.com/index.php/mall/wecha/notify/number/" . $orderid);
        $input->setTradeType("JSAPI");
        $input->setOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->getJsApiParameters($order);
        $this->assign('order', $order);
        $this->assign('jsApiParameters', $jsApiParameters);
        return $this->fetch();
    }
    /**
     * 异步接收订单返回信息，订单成功付款
     * @param int $id 订单编号
     */
    public function notify()
    {
        $notify = new PayNotifyCallBack();
        $notify->handle(true);
        $number = input('number');

        $succeed = ($notify->getReturnCode() == 'SUCCESS') ? true : false;
        if ($succeed) {
            model('PayRecord')->back_success($number);
        } else {
            Log::write('订单' . $number . '支付失败');
        }
    }

}
