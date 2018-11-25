<?php
namespace app\mall\controller;

/**
 * 申请控制器
 */
class Apply extends Common
{

    public function _initialize()
    {
        parent::_initialize();
    }
    public function index()
    {
        return $this->fetch();
    }

// ***********商家*************************************************************************
    //申请商家
    public function mer_apply()
    {
        if (is_post()) {
            $da = input("post.");
            
            if($this->mine['us_is_mer']==1){
                 $this->error('您已经是商户');
            }
            $whe_wei[] = ['us_id','=',session('us_id')];
            $whe_wei[] = ['apply_status','=',0];
            $wei = model('StoApply')->where($whe_wei)->find();
            // if ($wei) {
            //     $this->error('您已经申请过了,请耐心等待');
            // }
            if ($da['pic1'] == "") {$this->error('身份证正面不能为空');}
            if ($da['pic2'] == "") {$this->error('身份证反面不能为空');}
            if ($da['pic3'] == "") {$this->error('营业执照不能为空');}
                


            $data = array(
                'us_id' => session('us_id'),
                'apply_card_front' => $da['pic1'],
                'apply_card_back' => $da['pic2'],
                'apply_trad' => $da['pic3'],
                'apply_jine' => $da['jine'],
            );
            $rel = model('StoApply')->tianjia($data);
            return $rel;
        }
        return $this->fetch();
    }
    // 申请商家列表
    public function mer_list()
    {
        $this->map[] = ['us_id','=', session('us_id')];
        $list = model('StoApply')->where($this->map)->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    // 申请商家信息详情
    public function apply_detail()
    {
        $id = input("get.id");
        if (!$id) {
            $this->error("参数错误");
        } else {
            $info = model('StoApply')->get($id);
        }
        if (!$info) {
            $this->error("数据不存在");
        } else {
            $this->assign('info', $info);
        }
        return $this->fetch();
    }
// ***********汽车*********************************************************************
    //申请汽车
    public function car_apply()
    {
        if (request()->isPost()) {
            $request = request()->post();
            $info = model('User')->getInfo(session('mid'));
            if ($info['is_car'] == 0) {
                $this->error('对不起，您还未达到送车标准');
            }
            // if ($info['level']<)
            // $where = array(
            //     'uid' => session('mid'),
            //     'type' => 1,
            // );
            if ($request['name'] == "" || $request['whether_marry'] == "" || $request['tel'] == "" || $request['house'] == "") {
                $this->error('填写信息不能为空');
            }
            {
                $data = array(
                    'uid' => session('mid'),
                    'name' => $request['name'],
                    'tel' => $request['tel'],
                    'whether_marry' => $request['whether_marry'],
                    'house' => $request['house'],
                    'add_time' => time(),
                );
            }
            $rel = model('ApplyCar')->save($data);
            if ($rel) {
                $this->success('申请成功,等待后台审核');
            } else {
                $this->error('申请失败');
            }
        }
        $where = array(
            'user_id' => session('mid'),
            'default' => 1,
        );
        $info = model('UserAddr')->where($where)->find();
        $this->assign(array(
            'info' => $info,
        ));
        return $this->fetch();
    }
    // 申请汽车列表
    public function car_list()
    {
        $map = [];
        $map['uid'] = session('mid');
        $list = model('ApplyCar')->where($map)->paginate(10);
        // halt($list);
        $this->assign('list', $list);
        return $this->fetch();
    }
    // 申请汽车提交信息详情
    public function car_detail()
    {
        $id = input("get.id");
        if (!$id) {
            $this->error("非法操作");
        } else {
            $info = model('ApplyCar')->get($id);
        }
        if (!$info) {
            $this->error("非法操作");
        } else {
            $this->assign('info', $info);
        }
        $user_info = model('User')->get(session('mid'));
        $this->assign('user_info', $user_info);
        return $this->fetch();
    }
//------------代理---------------------------------------------------------------
    public function agency_apply()
    {
        return $this->fetch();
    }
    public function agency_apply_detail()
    {
        if (is_Post()) {
            $request = input('post.');
            $info = model('User')->get(session('mid'));
            $number = time() . GetRandStr(5);
            $where = array(
                'uid' => session('mid'),
                'status' => 0,
            );
            $cunzai = model('ApplyAgency')->where($where)->find();
            // if ($cunzai) {
            //     $this->error('您有申请正在等待审核');
            // }
            if ($request['type'] == 1) {
                if ($info['agency'] > 0) {
                    $this->error("请查看您的代理等级");
                }
                $data = array(
                    'number' => $number,
                    'type' => $request['type'],
                    'num' => 100000,
                    'uid' => session('mid'),
                );
            } elseif ($request['type'] == 2) {
                if ($info['agency'] > 1) {
                    $this->error("请查看您的代理等级");
                }
                if ($request['area2'] == "") {
                    $this->error('请选择县区');
                }
                $data = array(
                    'number' => $number,
                    'type' => $request['type'],
                    'num' => 990000,
                    'uid' => session('mid'),
                    'area_code' => $request['area2'],
                    'province_code' => $request['province2'],
                    'city_code' => $request['city2'],
                );
            } elseif ($request['type'] == 3) {
                if ($info['agency'] > 2) {
                    $this->error("请查看您的代理等级");
                }
                if ($request['city1'] == "") {
                    $this->error('请选择城市');
                }
                $data = array(
                    'number' => $number,
                    'type' => $request['type'],
                    'num' => 990000,
                    'uid' => session('mid'),
                    'province_code' => $request['province1'],
                    'city_code' => $request['city1'],
                );
            }
            if ($data) {
                $rel = model('ApplyAgency')->save($data);
                if ($rel) {
                    return $data = array(
                        'code' => 1,
                        'url' => "/mall/pay/index?table=ApplyAgency&number=" . $number,
                        'msg' => '申请成功,正在跳转支付界面..',
                    );
                } else {
                    $this->error('申请失败');
                }
            } else {
                $this->error('非法操作');
            }
        }

        $type = input('get.type');
        $list = db('addr_province')->select();
        $list1 = db('addr_city')->select();
        $list2 = db('addr_area')->select();
        $this->assign(array(
            'type' => $type,
            'list' => $list,
            'list1' => $list1,
            'list2' => $list2,
        ));
        return $this->fetch();
    }
    public function agency_list()
    {
        $map = [];
        $map['uid'] = session('mid');
        $list = model('ApplyAgency')->where($map)->order('id desc')->paginate(10);
        $this->assign('list', $list);
        return $this->fetch();
    }
    public function agency_detail()
    {
        $id = input("get.id");
        if (!$id) {
            $this->error("非法操作");
        } else {
            $info = model('ApplyAgency')->get($id);
        }
        if (!$info) {
            $this->error("非法操作");
        } else {
            $this->assign('info', $info);
        }
        $user_info = model('user')->get(session('mid'));
        $this->assign('user_info', $user_info);
        return $this->fetch();
    }
//------------合伙人---------------------------------------------------------------
    public function partner_apply()
    {
        return $this->fetch();
    }
    public function partner_apply_detail()
    {
        if (is_Post()) {
            $request = input('post.');
            $info = model('User')->get(session('mid'));
            $number = time() . GetRandStr(5);
            if ($request['type'] == 1) {
                if ($info['partner'] > 0) {
                    $this->error("请查看您的合伙人等级");
                }
                $data = array(
                    'type' => $request['type'],
                    'num' => 60000,
                    'uid' => session('mid'),
                    'number' => $number,
                );
            } elseif ($request['type'] == 2) {
                if ($info['partner'] > 1) {
                    $this->error("请查看您的合伙人等级");
                }
                // if ($info['wallet_intergrity'] < 210000) {
                //     $this->error("您的信用Doken不足");
                // }
                $data = array(
                    'type' => $request['type'],
                    'num' => 210000,
                    'number' => $number,
                    'uid' => session('mid'),
                );
            }
            if ($data) {
                $rel = model('ApplyPartner')->save($data);
                if ($rel) {
                    return $datb = array(
                        'code' => 1,
                        'url' => "/mall/pay/index?table=ApplyPartner&number=" . $number,
                        'msg' => '申请成功,正在跳转支付界面..',
                    );
                } else {
                    $this->error('申请失败');
                }
            } else {
                $this->error('非法操作');
            }
        }
        $type = input('get.type');
        $this->assign(array(
            'type' => $type,
        ));
        return $this->fetch();
    }
    public function partner_list()
    {
        $map = [];
        $map['uid'] = session('mid');
        $list = model('ApplyPartner')->where($map)->paginate(10);
        $this->assign('list', $list);
        return $this->fetch();
    }
    public function partner_detail()
    {
        $id = input("get.id");
        if (!$id) {
            $this->error("非法操作");
        } else {
            $info = model('ApplyPartner')->get($id);
        }
        if (!$info) {
            $this->error("非法操作");
        } else {
            $this->assign('info', $info);
        }
        $user_info = model('user')->get(session('mid'));
        $this->assign('user_info', $user_info);
        return $this->fetch();
    }
}
