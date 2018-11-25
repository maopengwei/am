<?php
namespace app\mall\controller;

/**
 * 新闻消息
 */
class News extends Common
{

    public function __construct()
    {
        parent::__construct();
    }
    //奖励Doken
    public function index()
    {
        return $this->fetch();
    }

    //新闻公告
    public function news()
    {

        if(is_post()){

            $this->map[] = ['me_type','=',1];
            $this->size = 10;
            $list = model("Message")->chaxun($this->map, $this->order, $this->size);
            foreach ($list as $k => $v) {
                $list[$k]['con'] = mb_substr(html_entity_decode($v['me_content']),0,10);
            }
            return ['code' => 1,'data' => $list];

        }
        return $this->fetch();
    
    }
    //新闻公告
    public function mess()
    {
        if(is_post()){

            $this->map[] = ['me_type','=',3];
            $this->size = 10;
            $list = model("Message")->chaxun($this->map, $this->order, $this->size);
            foreach ($list as $k => $v) {
                $list[$k]['con'] = mb_substr(html_entity_decode($v['me_content']),0,10);
            }
            return ['code' => 1,'data' => $list];

        }
        return $this->fetch();
    
    }
    public function xq()
    {
        $d = input('id');
        if($d){
           $info = model("Message")->get($d);
           $info['me_content'] = html_entity_decode($info['me_content']);
           $this->assign('info', $info);
            return $this->fetch(); 
        }else{
            $this->redirect('/mall/index/index');
        }
        
    }


    //交易消息
    public function deal()
    {
        return $this->fetch();
    }
}
