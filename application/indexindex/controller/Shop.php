<?php

namespace app\index\controller;
use think\Db;
use think\Request;

/**
 * 商城
 */
class Shop extends Base
{
    //分类列表
    public function cate(){
        if(is_post()){
            $list = model('StoCate')->where('cate_pid', 0)->order('cate_sort desc')->select();
            $this->msg($list);
        }else{
            $this->s_msg('get');
        }
    }
    //产品列表
    public function prod(){
        if(is_post()){
            $this->order = 'prod_sort desc,id desc';
            $this->map[] = ['prod_status', '=', 1];
            $this->map[] = ['prod_zone', '=', 0];
            $da = input('post.');
            if(isset($da['is_like'])){
                $this->map[] = ['prod_is_like', '=', 1];
            }
            if(isset($da['is_hot'])){
                $this->map[] = ['prod_is_hot', '=', 1];
            }
            
            if(isset($da['cate_id'])){
                $arr = [];
                array_push($arr, $da['cate_id']);
                $cate = model('StoCate')->where('cate_pid',$da['cate_id'])->select();
                if($cate){
                    foreach ($cate as $k => $v) {
                        array_push($arr, $v["id"]);
                    }
                }
                $this->map[] = ['cate_id','in',$arr]; 
            }

            $list = model('StoProd')->chaxun($this->map, $this->order, $this->size);
            foreach ($list as $k => $v) {
                $list[$k]['prod_pic'] = explode(',',$v['prod_pic'])[0];
            }  
            $this->msg($list);
        }else{
            $this->s_msg('get');
        }
    }
    //商家列表
    public function mer(){
        if(is_post()){
            $this->map[] = ['mer_status','=',1];
            $list = model('StoMer')->chaxun($this->map, $this->order, $this->size);
            $this->msg($list);
        }else{
            $this->s_msg('get');
        }
    }
    //商家详情
    public function merDetail(){
        if(is_post()){
            $info = model('StoMer')->detail(['id'=>input('id')]);
            $this->msg($info);
        }else{
            $this->s_msg('get');
        }
    }

    //商品详情
    public function prodDetail(){
        if(is_post()){
            $info = model('StoProd')->detail(['id'=>input('id')]);
            $info['pic'] = explode(',',$info['prod_pic']);
            $info['pic_one'] = $info['pic'][0];
            $info['mer_name'] = Db::name('sto_mer')->where('id',$info['mer_id'])->value('mer_name');
            $this->msg($info);
        }else{
            $this->s_msg('get');
        }
    }
}
