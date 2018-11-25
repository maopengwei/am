<?php

namespace app\index\controller;

use think\Route;

class Index extends Base
{

    public function index()
    {
    	$this->error('no access');
    }

    
    // 公告列表
    public function noticeList()
    {
        // 获取最新公告
    	$list = db('notice')->where('status', 1)->order('create_time DESC')->find();

        $list['n_message'] = html_entity_decode($list['n_message']);

    	return $this->result($list);
    }
     // 公告列表
    public function noticeLists()
    {
        // 获取最新公告
        $list = db('notice')->where('status', 1)->order('create_time DESC')->select();
        foreach ($list as $k => $v) {
            $list[$k]['n_message'] = html_entity_decode($v['n_message']);
        }
        // $list['n_message'] = html_entity_decode($list['n_message']);
        return $this->result($list);
    }

    // 公告info 
    public function getNotice()
    {
    	$id = input('param.id');
    	$info = db('notice')->where('id', $id)->find();
        $info['n_message'] = html_entity_decode($info['n_message']);
    	return $this->result($info);
    }

    public function miss()
    {
    	$this->error('no access');
    }



}