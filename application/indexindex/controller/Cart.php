<?php

namespace app\index\controller;

use think\Request;
use think\Db;
/**
 * 商城
 */
class Cart extends Base
{
    //购物车列表
    public function cart(){
        if(is_post()){
            $this->map[] = ['us_id','=',$this->user['id']];
            $list = model('StoCart')->with('prod')->where($this->map)->order('id desc')->select();
           
            $this->msg($list);
        }else{
            $this->s_msg('get');
        }
    }
   
    //添加
    public function add(){
        if(is_post()){
            $d = input('post.');
            $info = db('sto_cart')->where('prod_id',input('id'))->where('us_id',$this->user['id'])->find();
            if($info){
                $this->e_msg('该商品已被添加到购物车');
            }else{
                $arr = [
                    'us_id' => $this->user['id'],
                    'prod_id' => input('id'),
                ];
                $rel = model('StoCart')->tianjia($arr);
                $this->msg($rel);
            }
        }else{
            $this->s_msg('get');
        }
    }
    
    /*
        量量
        购物车 id
        数量   num    
    */
    
    public function num(){
        if(is_post()){
            $d = input('post.');
            $info = Db::name('sto_cart')->where('id',input('id'))->where('us_id',$this->user['id'])->select();
            if($info){
                $prod = Db::name('sto_prod')->where('id',$info['prod_id'])->find();
                if($prod['prod_res']<input('num')){
                    $this->e_msg('该商品库存不足');
                }
                Db::name('sto_cart')->where('id',input('id'))->setfield('cart_num',input('num'));
                $this->s_msg('修改成功');
            }else{
                $this->e_msg('信息不存在');
            }
          
        }else{
            $this->s_msg('get');
        }
    }
    public function del(){
        if(is_post()){
            $d = input('post.');
            $info = Db::name('sto_cart')->where('id',input('id'))->where('us_id',$this->user['id'])->select();
            if($info){
                Db::name('sto_cart')->where('id',input('id'))->delete();
                $this->s_msg('删除成功');
            }else{
                $this->e_msg('信息不存在');
            }
          
        }else{
            $this->s_msg('get');
        }
    }



}
