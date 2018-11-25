<?php
namespace app\admin\controller;

/**
 * 属性列表
 */
class Baod extends Common
{

    public function __construct()
    {
        parent::__construct();
    }
    //
    public function index()
    {
        /** 
         * prod_zone 0普通产品  1报单产品
         * 报单列表 
         */
        $this->map[] = ['prod_zone','=',1];
		if (input('get.keywords')) {
			$us_id = model("User")->where('us_account|us_tel', input('get.keywords'))->value('id');
			if ($us_id) {
                $this->map[] = ['us_id', '=', $us_id];
			}else{
                $this->map[] = ['us_id', '=', 0];
            }
		}

		if (input('get.status') != "") {
			$this->map[] = ['detail_status', '=', input('get.status')];
		}

		if (input('get.prod_name') != "") {
			$this->map[] = ['prod_name', 'like', "%".input('get.prod_name')."%"];
		}
		
		if (input('get.order_number') != "") {
			$this->map[] = ['order_number', '=', input('get.order_number')];
		}
		if (input('get.start')) {
			$this->map[] = ['detail_add_time', '>=', input('get.start')];
		}
		if (input('get.end')) {
			$this->map[] = ['detail_add_time', '<=', input('get.end')];
		}

        $list = model('StoOrderDetail')->chaxun($this->map, $this->order, $this->size);
		$this->assign(array(
			'list' => $list,
		));
		return $this->fetch();
        
    }
    //添加属性
    public function add()
    {
        if (is_post()) {
            $data = input('post.');

            //属性分类
            if ($data['cate_id'] == 0) {
                $this->error('请选择商品分类');
            }
            //属性名
            if ($data['attr_name'] == '') {
                $this->error('属性名不能为空');
            }

            if (model('StoAttr')->where($data)->count() > 0) {
                $this->error('此分类下已有该属性名');
            }
            $rst = model('StoAttr')->tianjia($data);
                
            $this->success('添加成功');

        } else {
            // $map = array(
            //     'pid' => 0,
            // );
            $cate = model('StoCate')->where('cate_pid',0)->select();
            foreach ($cate as $k => $v) {
                $cate[$k]['son'] = model('StoCate')->where('cate_pid', $v['id'])->select();
            }
            if (input('cate_id')) {
                $this->map[] = ['cate_id','=',input('cate_id')];
            }
            $this->map[] = ['attr_pid','=', 0];
            $attr = model('StoAttr')->where($this->map)->select();
            $this->assign(array(
                'cate' => $cate,
                'attr' => $attr,
            ));
            return $this->fetch();
        }
    }
    
    //删除分类
    public function del()
    {
        if (input('post.id')) {
            $id = input('post.id');
        } else {
            $this->error('非法操作');
        }
        $info = model('StoAttr')->where('id', $id)->find();
        
        // $product = model('product')->where('cate_id', $info['id'])->find();
        // if ($product) {
        //     $this->error('该分类下面有产品,请修改产品分类后再删除');
        // }
        if ($info) {
            if (model('StoAttr')->where('attr_pid', $info['id'])->find()) {
                $this->error('该属性名下面有属性值所以不能删除');
            }
            $rel = db('StoAttr')->where('id', $id)->delete();
            if ($rel) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('该数据不存在');
        }
    }
}