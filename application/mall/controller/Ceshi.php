<?php
namespace app\mall\controller;

use think\Controller;
use think\captcha\Captcha;
/**
 * 测试
 */
class Ceshi extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }
    
    public function cece(){

            $captcha = new Captcha();
            
            return $captcha->entry();       
    }


    //让利
    public function rangli()
    {
        $phone = "18739912538";
        $amount = 10;
        $rel = Yilian::deal($phone, $amount);
        halt($rel);
    }
    //
    //获取益联Doken
    public function getLianji()
    {
        $token = "de2c090b65baef1b698fdb73cd7dc654c6db2b12";
        $rel = Yilian::getLianji($token);
        halt($rel);
    }
    //获取个人信息
    public function getInfo()
    {
        $token = "de2c090b65baef1b698fdb73cd7dc654c6db2b12";
        $rel = Yilian::getInfo($token);
        halt($rel);
    }

    public function ceshi()
    {
        model('ProfitCash')->tianjia(44, 1, 0.1, '测试');
    }
}
