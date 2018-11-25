<?php
namespace app\mall\controller;

use think\Controller;

// require_once ROOT_PATH . DIRECTORY_SEPARATOR . 'service/AlipayTradeService.php';
// require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'buildermodel/AlipayTradeWapPayContentBuilder.php';
// require dirname(__FILE__) . DIRECTORY_SEPARATOR . './../config.php';
//调用模型
// error_reporting(0);
class Alipay extends Controller
{
    protected $config;
    public function _initialize()
    {
        // $path = ROOT_PATH . 'public' . DIRECTORY_SEPARATOR . 'alipay/wappay/';
        require_once 'alipay/wappay/service/AlipayTradeService.php';
        require_once 'alipay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
        require_once 'alipay/config.php';
        $this->config = $config;
    }
    public function index()
    {
        halt(input('get.'));
        if ($this->is_weixin()) {
            return $this->fetch();
        }
        $number = '';
        $table = '';
        $type = input('type');
        $num = input('money');
        $uid = input('uid');
        $number = input('number');
        $table = input('table');
        $orderid = "kyb" . date("YmdHis") . rand(100, 999);
        model('AlipayPay')->tianjia($uid, $orderid, $num, $type, $number, $table);
        $subject = pay_type($type);
        $body = '支付宝在线' . $subject;
        $out_trade_no = $orderid;
        //订单名称，必填
        // $subject = $su;

        //付款金额，必填
        $total_amount = $num;

        //商品描述，可空
        // $body = $_POST['WIDbody'];

        //超时时间
        $timeout_express = "1m";

        $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);
        $payResponse = new \AlipayTradeService($this->config);
        $result = $payResponse->wapPay($payRequestBuilder, $this->config['return_url'], $this->config['notify_url']);
    }
    //回调函数
    public function alipay_notify()
    {
        $arr = $_POST;
        $alipaySevice = new \AlipayTradeService($this->config);
        $alipaySevice->writeLog(var_export($_POST, true));
        $result = $alipaySevice->check($arr);
        if ($result) {
            model('AlipayPay')->back_success($arr['out_trade_no']);
            echo "success";
        } else {
            echo "fail";
        }
    }
    // 判断是否微信
    protected function is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }
    // public function index()
    // {
    //     $num = input('money');
    //     //发起支付宝支付
    //     if (request()->isPost()) {
    //         //商户订单号，商户网站订单系统中唯一订单号，必填
    //         $out_trade_no = $_POST['WIDout_trade_no'];
    //         //订单名称，必填
    //         $subject = $_POST['WIDsubject'];
    //         //付款金额，必填
    //         $total_amount = $_POST['WIDtotal_amount'];

    //         //商品描述，可空
    //         $body = $_POST['WIDbody'];

    //         //超时时间
    //         $timeout_express = "1m";

    //         $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
    //         $payRequestBuilder->setBody($body);
    //         $payRequestBuilder->setSubject($subject);
    //         $payRequestBuilder->setOutTradeNo($out_trade_no);
    //         $payRequestBuilder->setTotalAmount($total_amount);
    //         $payRequestBuilder->setTimeExpress($timeout_express);
    //         $payResponse = new \AlipayTradeService($this->config);
    //         $result = $payResponse->wapPay($payRequestBuilder, $this->config['return_url'], $this->config['notify_url']);

    //     }
    // }
}
