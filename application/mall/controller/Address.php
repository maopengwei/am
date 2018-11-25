<?php
namespace app\mall\controller;

/**
 * 地址
 */
class Address extends Common
{

    public function __construct()
    {
        parent::__construct();

        $province = db('addr_province')->select();
        $city = db('addr_city')->select();
        $area = db('addr_area')->select();
        $this->assign(array(
            'list' => $province,
            'list1' => $city,
            'list2' => $area,
        ));

    }
    //地址列表
    public function index()
    {
        $where['us_id'] = session('us_id');
        $list = model('UserAddr')->where($where)->select();
        $this->assign('list', $list);
        return $this->fetch();
    }
    //增加地址
    public function add()
    {
        if (is_post()) {
            
            $data = input('post.');

            $validate = validate('Addr');
            $res = $validate->scene('addr')->check($data);
            if (!$res) {
                $this->error($validate->getError());
            }
            $data['us_id'] = session('us_id');
            $rel = model('UserAddr')->tianjia($data);
            if ($rel) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
        }
       
        return $this->fetch();

    }
    //编辑地址
    public function edit()
    {
        if (is_post()) {
            $data = input('post.');


            $validate = validate('Front');
            $res = $validate->scene('addr')->check($data);
            if (!$res) {
                $this->error($validate->getError());
            }

            $rel = model('UserAddr')->update($data);
            $this->success('修改成功');
            
        }
        $id = input('get.id');
        $info = model('UserAddr')->get($id);
        $this->assign(array(
            'info' => $info,
        ));
        return $this->fetch();
    }
    //修改默认地址
    public function def()
    {
        $id = input('post.id');
        $info = model('UserAddr')->get($id);
        if ($info) {
            db('user_addr')->where('addr_default', 1)->where('us_id',session('us_id'))->setfield('addr_default', 0);
            $rel = model('UserAddr')->where('id', $id)->setfield('addr_default', 1);
            if ($rel) {
                $this->success('设为默认成功');
            } else {
                $this->error('设为默认失败');
            }
        } else {
            $this->error('非法操作');
        }
    }
    //删除邮箱
    public function del()
    {
        $id = input('post.id');
        $info = model('UserAddr')->get($id);
        if ($info) {
            $rel = model('UserAddr')->destroy($id);
            if ($rel) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('非法操作');
        }
    }
    public function getcity()
    {
        $province = input('post.code');
        $list = db('addr_city')->where('provincecode', $province)->select();
        if ($list) {
            return $list;
        }
    }
    public function getarea()
    {
        $city = input('post.code');
        $list = db('addr_area')->where('citycode', $city)->select();
        if ($list) {
            return $list;
        }
    }
}
