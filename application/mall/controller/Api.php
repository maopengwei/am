<?php
namespace app\mall\controller;

use wechat\TPWechat;

/**
 * 微信公众号
 */
class Api
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

    public function index()
    {
        $weObj = $this->init();
        $weObj->valid();
        $type = $weObj->getRev()->getRevType();
        $appUrl = "http://" . input("server.HTTP_HOST");
        switch ($type) {
            case 'text':
                $weObj->text('欢迎关注！')->reply();
                break;
            case 'event':
                $eventype = $weObj->getRev()->getRevEvent();
                if ($eventype['event'] == "subscribe") {
                    //用户关注动作
                    $weObj->text('欢迎关注！')->reply();
                }
                break;
        }
    }
    // 创建菜单
    public function createMenu()
    {
        $m = db("menu");
        $menu = $m->where(array("pid" => 0))->limit(3)->select();

        $newmenu["button"] = array();
        for ($i = 0; $i < count($menu); $i++) {
            if ($menu[$i]["type"] == "view") {
                $sub = $m->where(array("pid" => $menu[$i]["id"]))->limit(5)->select();
                if ($sub) {
                    $sub_button = array();

                    for ($j = 0; $j < count($sub); $j++) {
                        if ($sub[$j]["type"] == "view") {
                            array_push($sub_button, array('type' => 'view', 'name' => $sub[$j]["name"], 'url' => $sub[$j]["url"]));
                        } else {
                            array_push($sub_button, array('type' => 'click', 'name' => $sub[$j]["name"], 'key' => $sub[$j]["key"]));
                        }
                    }
                    array_push($newmenu["button"], array('name' => $menu[$i]["name"], 'sub_button' => $sub_button));
                } else {
                    array_push($newmenu["button"], array('type' => 'view', 'name' => $menu[$i]["name"], 'url' => $menu[$i]["url"]));
                }
            } else {
                $sub = $m->where(array("pid" => $menu[$i]["id"]))->limit(5)->select();
                if ($sub) {
                    $sub_button = array();

                    for ($j = 0; $j < count($sub); $j++) {
                        if ($sub[$j]["type"] == "view") {
                            array_push($sub_button, array('type' => 'view', 'name' => $sub[$j]["name"], 'url' => $sub[$j]["url"]));
                        } else {
                            array_push($sub_button, array('type' => 'click', 'name' => $sub[$j]["name"], 'key' => $sub[$j]["key"]));
                        }
                    }
                    array_push($newmenu["button"], array('name' => $menu[$i]["name"], 'sub_button' => $sub_button));
                } else {
                    array_push($newmenu["button"], array('type' => 'click', 'name' => $menu[$i]["name"], 'key' => $menu[$i]["key"]));
                }
            }
        }
        $weObj = $this->init();

        if ($weObj->createMenu($newmenu)) {
            echo "success";
        } else {
            echo "error";
        }
    }

    // 获取token值
    public function getAccessToken()
    {
        $weObj = $this->init();
        $accessToken = $weObj->checkAuth();
        return $accessToken;
    }
    //获取个人信息
    public function getInfo()
    {
        $access_token = $this->getAccessToken();
        $openid = session('openid');
        // https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN
        // access_token    是   调用接口凭证
        // openid  是   普通用户的标识，对当前公众号唯一
        // lang    否   返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
        $test = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $test);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $result = json_decode($response);
        curl_close($ch);
        return $result;
    }
    // 订单 线上支付
    public function orderPay()
    {

    }

    // 根据坐标获取城市
    public function getCityLocation()
    {
        $data = input('post.');
        $latitude = $data['latitude']; //纬度
        $longitude = $data['post.longitude']; //经度

        $url = "http://api.map.baidu.com/geocoder/v2/?ak=IHDKmkKAofhZDlUG8Vq2iqFKgQzvDt5c&location=" . $latitude . "," . $longitude . "&output=json&coordtype=gcj02ll";

        $output = file_get_contents($url);
        $address = json_decode($output, true);
        $city_name = $address['result']['addressComponent']['city']; //获取城市名称
        $city_code = $address['result']['cityCode']; //获取城市代码id
        if (!empty($city_name)) {
            return json(['city' => $city_name]);
        }
    }

    // 下载到服务器
    public function getTmp()
    {
        $accessToken = $this->getAccessToken();
        $serverId = input('post.serverId');
        // 服务器存放地址
        $filename = date('YmdHis') . '.jpg';
        $targetName = ROOT_PATH . 'public/upload/shoptitle/' . $filename;

        $ch = curl_init("http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$accessToken}&media_id={$serverId}");
        $fp = fopen($targetName, 'w+');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        return json(['pic' => $filename]);
    }

    // 获取城市
    public function getCity()
    {
        $pro = input('get.code');
        $list = db('cn_city')->where('provincecode', $pro)->select();
        return json(['city' => $list]);
    }

}
