<?php
namespace app\index\controller;
use think\Db;
/**
 * 地址
 */
class Addr extends Base
{

    public function __construct()
    {
        parent::__construct();
    }
    //地址列表
    public function index()
    {
        if(is_post()){
            $where['us_id'] = $this->user['id'];
            $list = model('UserAddr')->where($where)->select();
            $this->msg($list);
        }else{
            $this->e_msg('get');
        }
    }
    //增加地址
    public function add()
    {
        if (is_post()) {
            $da = input('post.');
            /*
                 收货人  addr_name
                 收货电话  addr_tel
                 收货地址  addr_stree
            */
            $validate = validate('Addr');
            $res = $validate->scene('addr')->check($da);
            if (!$res) {
                $this->error($validate->getError());
            }
            $da['us_id'] = $this->user['id'];
            $rel = model('UserAddr')->tianjia($da);
            if ($rel) {
                $this->s_msg('添加成功');
            } else {
                $this->e_msg('添加失败');
            }
        }
       
        return $this->fetch();

    }

    public function xq(){
        if(is_post()){
            $id = input('post.id');
            if(!$id){
                $info = Db::name('user_addr')->where('addr_default',1)->find();
                if(!$info){
                    $info = Db::name('user_addr')->order('id desc')->find();
                }
            }else{
                $info = model('UserAddr')->get($id);
            }
            $this->msg($info);
        }else{
            $this->e_msg('get');
        }
    }
    //编辑地址
    public function edit()
    {
        if (is_post()) {
            $d = input('post.');
            
            $validate = validate('Addr');
            $res = $validate->scene('addr')->check($d);
            if (!$res) {
                $this->e_msg($validate->getError());
            }
            $rel = model('UserAddr')->update($d);
            $this->s_msg('修改成功');
        }else{
            $this->e_msg('删除成功');
        }
        
    }
    //修改默认地址
    public function def()
    {
        if(is_post()){
            $id = input('post.id');
            $info = model('UserAddr')->get($id);
            if ($info) {
                Db::name('user_addr')->where('addr_default', 1)->where('us_id',$this->user['id'])->setfield('addr_default', 0);
                $rel = model('UserAddr')->where('id', $id)->setfield('addr_default', 1);
                if ($rel) {
                    $this->s_msg('设为默认成功');
                } else {
                    $this->e_msg('设为默认失败');
                }
            } else {
                $this->e_msg('信息不存在');
            }
        }else{
            $this->e_msg('get');
        }
       
    }
    //删除
    public function del()
    {
        if(is_post()){
            $id = input('post.id');
            $info = model('UserAddr')->get($id);
            if ($info) {
                $rel = model('UserAddr')->destroy($id);
                if ($rel) {
                    $this->s_msg('删除成功');
                } else {
                    $this->e_msg('删除失败');
                }
            } else {
                $this->e_msg('信息不存在');
            }
        }else{
            $this->e_msg('get');
        }
        
    }
}
