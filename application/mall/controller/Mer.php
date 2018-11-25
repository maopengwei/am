<?php
namespace app\mall\controller;

use app\common\controller\IndexBase;
use think\Controller;

/**
 * 商家页面
 */
class Mer extends Basic
{

    public function __construct()
    {
        parent::__construct();
        session('shop', null);
        session('arrid', null);
    }

    public function list()
    {
       
        if(is_post()){
            if (input('mer_name')) {
                $this->map[] = ['mer_name','like',"%" . input('mer_name') . "%"];
            }
            $this->size = 10;
            $this->map[] = ['mer_status','=','1'];
            $list = model('StoMer')->chaxun($this->map,$this->order,$this->size);
            foreach ($list as $k => $v) {
                $list[$k]['count'] = model('StoOrderDetail')->where('mer_id',$v['id'])->sum('prod_num');
            }

            return ['code' => 1,'data' => $list];
        }
        return $this->fetch();
    }

    public function mer_list()
    {
       
        if (input('get.mer_name')) {
            $this->map['mer_name'] = array('like', "%" . input('get.mer_name') . "%");
        }
        $this->map[] = ['mer_status','=','1'];
        $list = model('StoMer')->chaxun($this->map,$this->order,$this->size);
        foreach ($list as $k => $v) {
            $list[$k]['count'] = model('StoOrderDetail')->where('mer_id',$v['id'])->sum('prod_num');
        }
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function index()
    {
        if (input('get.id')) {
            $id = input('get.id');
        } else {
            $this->error('非法操作');
        }
        $info = model('StoMer')->get($id);
        $info['count'] = model('StoOrderDetail')->where('mer_id',$info['id'])->sum('prod_num');
        $map = array(
            'prod_status' => 1,
            'mer_id' => $info['id'],
        );
        $this->map[] = ['prod_status','=',1];
        $this->map[] = ['mer_id','=',$info['id']];
        // $us_id = $info['us_id'];
        // dump($this->map);
        $list = model('StoProd')->chaxun($this->map,$this->order,$this->size);
        // halt($list);
        $this->assign(array(
            'list' => $list,
            'info' => $info,
        ));
        return $this->fetch();
    }
}
