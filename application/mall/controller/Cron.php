<?php
namespace app\mall\controller;

use think\Controller;
use wechat\TPWechat;

/**
 *  @todo  计划任务
 */
class Cron extends Controller
{

    protected function _initialize()
    {
        header("Content-Type:text/html; charset=utf-8");
        if (!cache('config')) {
            $setting = model('Config')->getConfig();
            cache('config', $setting);
        }
        config(cache('config'));
    }
    public function getOpenid()
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
            $url = $weObj->getOauthRedirect(request()->domain() . url('cron/getOpenid'), '123', 'snsapi_base');
            // $url = $weObj->getOauthRedirect(request()->domain() . url('cron/getOpenid')
            header("location: $url");
            return;
        }
        dump($token);
        $wxuser = $weObj->getOauthUserinfo($token["access_token"], $token["openid"]);
        halt($wxuser);
        session("openid", $wxuser["openid"]);
        $this->redirect('index');
    }
    public function index()
    {
        halt(session('openid'));
    }
    /**
     * 加权分红 当天销售额
     * 创智 3%
     * 创客 3%
     * 代理 3%
     * 每天晚上11:50点
     */
    // public function participation()
    // {
    //     //今天凌晨
    //     $todaystart = strtotime(date('Y-m-d' . '00:00:00', time()));
    //     //获取今天24:00
    //     $todayend = strtotime(date('Y-m-d' . '00:00:00', time() + 3600 * 24));
    //     $where = array(
    //         'status' => 3,
    //         'receive_time' => array('between', array($todaystart, $todayend)),
    //     );
    //     $list = model('OrderDetail')->where($where)->select();
    //     $money = 0;

    //     foreach ($list as $k => $v) {
    //         $money += $v['pd_cash'] * $v['pd_num'];
    //     }
    //     $money = $money * 3 / 100;
    //     if ($money) {
    //         $chuangke = model('User')->where('partner', 1)->select();
    //         if ($chuangke) {
    //             $chuangke_count = model('User')->where('partner', 1)->count();
    //             $money_chuangke = round($money / $chuangke_count);
    //             foreach ($chuangke as $k => $v) {
    //                 model('User')->where('id', $v['id'])->setInc('wallet_cash', $money_chuangke);
    //                 $data = array(
    //                     'us_id' => $v['id'],
    //                     'type' => 8,
    //                     'num' => $money_chuangke,
    //                     'add_time' => time(),
    //                     'note' => '创客分红',
    //                 );
    //                 db('profit_cash')->insert($data);
    //             }
    //         }
    //         $chuangzhi = model('User')->where('partner', 2)->select();
    //         if ($chuangzhi) {
    //             $chuangzhi_count = model('User')->where('partner', 2)->count();
    //             $money_chuangzhi = round($money / $chuangzhi_count);
    //             foreach ($chuangzhi as $k => $v) {
    //                 model('User')->where('id', $v['id'])->setInc('wallet_cash', $money_chuangzhi);
    //                 $data = array(
    //                     'us_id' => $v['id'],
    //                     'type' => 9,
    //                     'num' => $money_chuangzhi,
    //                     'add_time' => time(),
    //                     'note' => '创智分红',
    //                 );
    //                 db('profit_cash')->insert($data);
    //             }
    //         }
    //         $map['agency'] = array('neq', 0);
    //         $agency = model('User')->where($map)->select();
    //         if ($agency) {
    //             $agency_count = model('User')->where($map)->count();
    //             $money_agency = round($money / $agency_count);
    //             foreach ($chuangzhi as $k => $v) {
    //                 model('User')->where('id', $v['id'])->setInc('wallet_cash', $money_agency);
    //                 $data = array(
    //                     'us_id' => $v['id'],
    //                     'type' => 10,
    //                     'num' => $money_agency,
    //                     'add_time' => time(),
    //                     'note' => '代理分红',
    //                 );
    //                 db('profit_cash')->insert($data);
    //             }
    //         }

    //     }
    // }
    /**
     *产品绑定人收益
     * 每月一次 奖励上个月的销售额的百分比
     *   奖励规则：
     *   50万以下给  0.2%
     *   51万——100万 0.5%
     *   101万——200万 0.8%
     *   200万以上  1%
     *   总体规则是这样的，
     *  【健康云客】不管厂家销售额有多少，最高只享受0.2%
     *  【健康公使】不管厂家销售额有多少，最高只享受0.2%——0.5%
     *  【健康大使】不管厂家销售额有多少，最高只享受0.2%——1%
     *  最高也就1%了  合伙人 代理 都是最高方式
     */
    public function product_user_profit()
    {
        $arr = last_month();
        $where = array(
            'status' => 3,
            'receive_time' => array('between', $arr),
        );
        $list = db('OrderDetail')->group('pd_id')->field('referrer_id,sum(order_sum)')->select();
        foreach ($list as $k => $v) {
            $money = $v['sum(order_sum)'];
            $user = model('User')->get($v['referrer_id']);
            if ($user == "" || $money == "") {
                continue;
            }
            $level = $user['level'];
            if ($level > 4 || $user['partner'] != 0 || $user['agency'] != 0) {
                $level = 4;
            }
            if ($level > 1 && $money > 0) {
                $calculate = db('config_product_link')->where('level', $level)->value('calculate');
                $true_money = $money * $calculate / 100;
                model('User')->where('id', $v['referrer_id'])->setInc('wallet_cash', $true_money);
                $data = array(
                    'us_id' => $v['referrer_id'],
                    'type' => 12,
                    'num' => $true_money,
                    'create_time' => time(),
                    'add_time' => time(),
                    'note' => '对接产品',
                );
                db('profit_cash')->insert($data);
            }
        }
    }
    //极差
    // public function jicha()
    // {
    //     $where = array(
    //         'level' => array('in', '4,5'),
    //     );
    //     $list = model('user')->where($where)->select();
    //     if ($list) {
    //         foreach ($list as $k => $v) {
    //             $yeji = last_month_yeji($v['id']);
    //             $calculate = calculate_jicha($yeji);
    //             $map = array(
    //                 'pid' => $v['id'],
    //             );
    //             $int = 0;
    //             $direct = model('user')->where($map)->select();
    //             foreach ($direct as $key => $value) {
    //                 $direct_yeji = last_month_yeji($value['id']);
    //                 $direct_calculate = calculate_jicha($direct_yeji);
    //                 $int += $direct_yeji * ($calculate - $direct_calculate) / 100;
    //             }
    //             if ($int) {
    //                 $data = array(
    //                     'us_id' => $v['id'],
    //                     'num' => $int,
    //                     'add_time' => time(),
    //                     'type' => 5,
    //                     'note' => '益联Doken',
    //                 );
    //                 db('profit_integrity')->insert($data);
    //             }
    //         }
    //     }
    // }
    /**
     * 每日分红
     * @return [type] [description]
     */
    public function fenhong()
    {
        $where['wallet_integral'] = array('gt', 0);

        $list = model('User')->where($where)->select();
        foreach ($list as $k => $v) {
            if ($v['wallet_integral'] > config('fenhong')) {
                $money = config('fenhong');
            } else {
                $money = $v['wallet_integral'];
            }
            model('ProfitCash')->tianjia($v['id'], 11, $money, '每日分红');
            model('ProfitIntegrity')->tianjia($v['id'], 5, $money, '每日分红', 'wallet_integral');
        }
    }

}
