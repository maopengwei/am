<?php
namespace app\admin\controller;

/**
 * 消息控制器
 */
class News extends Common {
	protected $order;
	public function __construct() {
		parent::__construct();
		$this->order = 'id desc';
	}
	//新闻列表
	public function index() {
		if(input('get.keywords')){
			$this->map[] = ['me_title','like','%'.input('get.keywords').'%'];
		}
		$this->map[] = ['me_type','=',1];
		$list = model('Message')->where($this->map)->order($this->order)->paginate($this->size);
		$this->assign(array(
			'list' => $list,
		));
		return $this->fetch();
	}
	//消息列表
	public function message() {
		if(input('get.keywords')){
			$this->map[] = ['me_title','like','%'.input('get.keywords').'%'];
		}
		$this->map[] = ['me_type','=',2];
		$list = model('Message')->where($this->map)->order($this->order)->paginate($this->size);
		$this->assign(array(
			'list' => $list,
		));
		return $this->fetch();
	}
	//添加
	public function add() {
		if (is_Post()) {
			$request = input('post.');
			if ($request['me_title'] == "" || $request['me_introduction'] == "") {
				$this->error('标题和内容不能为空');
			}
			$data = array(
				'me_add_time' => date('Y-m-d H:i:s'),
				'me_title' => $request['me_title'],
				'me_introduction' => $request['me_introduction'],
				'me_content' => $request['me_content'],
				'me_type' => 1,
			);
			$rel = model('Message')->insertGetid($data);
			if ($rel) {
				$this->success('添加成功');
			}
		}
		return $this->fetch();
	}
	// 修改
	public function edit() {
		$id = input('id');
		if (is_post()) {
			$data = input('post.');
			model('Message')->update($data);
			$this->success('修改成功');
			
		}
		$this->assign('info',  model("Message")->get($id));
		return $this->fetch();
	}
}
