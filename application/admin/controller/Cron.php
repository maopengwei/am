<?php
namespace app\admin\controller;
use app\common\controller\Base;
use think\Db;

/**
 * 乱七八糟控制器
 */
class Cron extends Base {

	public function arar() {
		$list = Db::name('user')->where('us_level', '<>', 0)->select();
		if ($list) {
			foreach ($list as $k => $v) {
				Db::name('user')->where('id', $v['id'])->setfield('us_level', 0);
			}
		}
	}

	//每日价格更新
	public function day_price() {
		$time = date('m-d');
		$info = Db::name('sys_price')->where('price_time', $time)->find();

		$arr = [
			'price' => cache('setting')['cal_day'],
			'price_time' => $time,
		];
		if ($info) {
			$arr['id'] = $info['id'];
			Db::name('sys_price')->update($arr);
		} else {

			Db::name('sys_price')->insert($arr);
		}
		return ['code' => 1, 'msg' => '更新成功'];
	}

	//锁仓释放
	public function release() {

		$d = date('d');

		// $m = (int)$d;
		$c = cache('setting')['cal_rel_day'];

		/*if($d!=$c){
			return ['code'=>1,'msg'=>'今天不能释放'];
		}*/

		$arr = [
			'0' => cache('setting')['cal_rea_yi'],
			'1' => cache('setting')['cal_rea_er'],
			'2' => cache('setting')['cal_rea_san'],
		];
		$tt = unixtime('day', -30);
		$t = date('Y-m-d', $tt);

		// $list = Db::name('user_suo')->where('rea_time','<',3)->whereTime('rea_date','<','today')->whereTime('add_time','<=',$t)->select();
		$list = Db::name('user_suo')->where('rea_time', '<', 3)->select();
		if ($list) {
			foreach ($list as $k => $v) {
				$cal = $arr[$v['rea_time']];
				$num = $v['suo_num'] * $cal / 100;
				$time = $v['rea_time'] + 1;
				if ($time < 3) {
					$brr = [
						'id' => $v['id'],
						'rea_time' => $time,
						'rea_date' => date('Y-m-d H:i:s'),
						'yshi_time' => date('Y-m-d H:i:s', strtotime('+30day')),
						'yshi_bie' => $arr[$v['rea_time'] + 1],
						'yshi_num' => $v['suo_num'] * $arr[$v['rea_time'] + 1] / 100,
						'zshi_num' => $v['zshi_num'] + $v['suo_num'] * $arr[$v['rea_time']] / 100,
					];
					Db::name('user_suo')->update($brr);
					Db::name('user_suo')->where('id', $v['id'])->setDec('sshi_num', $v['suo_num'] * $arr[$v['rea_time']] / 100);
					model('User')::usWalChange($v['us_id'], $num, 9);
				}

				if ($time == 3) {
					$brr = [
						'id' => $v['id'],
						'rea_time' => $time,
						'rea_date' => date('Y-m-d H:i:s'),
						'yshi_time' => '',
						'yshi_bie' => '',
						'yshi_num' => '',
						'zshi_num' => $v['suo_num'],
						'sshi_num' => 0,
					];
					Db::name('user_suo')->update($brr);
					model('User')::usWalChange($v['us_id'], $num, 9);
					//model('User')::usWalChange($v['us_id'], $v['suo_num'], 6);
				}
			}
		}
		return ['code' => 1, 'msg' => '释放成功'];
	}

	//额外奖励
	public function live() {
		$list = model("User")->select();
		$b = 0;
		if ($list) {
			foreach ($list as $k => $v) {
				$direct = model('User')->where('us_aid', $v['id'])->select();
				$arr = [];
				$num = 0;
				foreach ($direct as $key => $value) {
					$yeji = yeji($value['id'], $value['us_tree']);
					$cal = Db::name('sys_level')->where('cal_condition', '<=', $yeji)->order('id desc')->value('cal_red');
					if ($cal > 0) {
						$mon = $yeji * $cal / 1000;
						$num += $mon;
						array_push($arr, $mon);
					}
				}
				// dump($arr);
				if ($arr) {
					$max = max($arr);
					$nn = $num - $max;
					if ($nn > 0) {
						$b = 1;
						model('User')->usWalChange($v['id'], $nn, 11);
					}
				}

				$money = $v['us_wal'] + $v['us_dong'];
				$calcu = Db::name('sys_sx')->where('bi', '<', $money)->order('id desc')->value('cal');
				if ($calcu == 0) {
					continue;
				}
				$nnn = $money * $calcu / 1000;
				$mm = floor($nnn, 2);
				if ($mm) {
					$b = 1;
					model('User')->usWalChange($v['id'], $mm, 10);
				}

			}
		}
		if ($b) {
			return ['code' => 1, 'msg' => '执行成功'];
		} else {
			return ['code' => 1, 'msg' => '执行失败'];
		}
	}

	//持币生息
	// public function sx() {
	// 	$list = model("User")->where('us_wal', '>', 0)->select();
	// 	$b = 0;
	// 	if ($list) {
	// 		foreach ($list as $k => $v) {
	// 			$money = $v['us_wal'] + $v['us_dong'];
	// 			$cal = Db::name('sys_sx')->where('bi', '<', $money)->order('id desc')->value('cal');
	// 			if ($cal == 0) {
	// 				continue;
	// 			}
	// 			$nn = $money * $cal / 1000;
	// 			$mm = round($nn, 2);
	// 			if ($mm) {
	// 				$b = 1;
	// 				model('User')->usWalChange($v['id'], $mm, 10);
	// 			}
	// 		}
	// 	}

	// 	if ($b) {
	// 		return ['code' => 1, 'msg' => '生息成功'];
	// 	} else {
	// 		return ['code' => 1, 'msg' => '没有生息'];
	// 	}

	// }

}
