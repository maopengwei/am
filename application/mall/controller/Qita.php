<?php
namespace app\mall\controller;

/**
 * 其他
 */
class Qita extends Basic
{
    protected $order;
    public function __construct()
    {
        parent::__construct();
        $this->order = "id desc";
    }
    // 列表
    public function index()
    {
        return $this->fetch();
    }
    //消息列表
    public function message()
    {
        $map = array(
            'uid' => session('mid'),
            'type' => 2,
        );
        $list = model('UserMessage')->where($map)->order($this->order)->paginate(10);
        $this->assign(array(
            'list' => $list,
        ));
        return $this->fetch();
    }
    //帮助中心
    public function help()
    {
        $map = array(
            'type' => 3,
        );
        $list = model('UserMessage')->where($map)->order($this->order)->paginate(10);
        $this->assign(array(
            'list' => $list,
        ));
        return $this->fetch();
    }
    //新闻公告
    public function news()
    {
        $map = array(
            'me_type' => 0,
        );
        $list = model('Message')->chaxun($this->map,$this->order,$this->size);
        // foreach ($list as $k => $v) {
            
        //     // $length = mb_strlen($v['title'], 'utf8');
        //     // if ($length > 35) {
        //     //     $list[$k]['tit'] = mb_strcut($v['title'], 0, 35, 'utf8') . "...";
        //     // } else {
        //     //     $list[$k]['tit'] = $v['title'];
        //     // }
            
        // }
        $this->assign(array(
            'list' => $list,
        ));
        return $this->fetch();
    }
    //消息详情
    public function message_detail()
    {
        $id = input('get.id');
        if ($id) {
            $info = model('Message')->get($id);
        } else {
            $this->error('错误');
        }
        $info['me_content'] = html_entity_decode($info['me_content']);
        $this->assign(array(
            'info' => $info,
        ));
        return $this->fetch();
    }
}
