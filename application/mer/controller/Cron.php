<?php
namespace app\mer\controller;
use app\common\controller\Base;


/**
 * 乱七八糟控制器
 */
class Cron extends Base {

	public function ceshi() {
		
		$money  = 2000000;
		$is_jy = is_jy($money);
		halt($is_jy);
	}

	/*
		推广
		每日运行
		判断日期

	*/
	public function tuig(){
		$time = date('d');
		if($time!=cache('setting')['tui_day']){
			return;
		}
		$list = model('User')->where('us_is_tui',1)->select();
		if($list){
			foreach ($list as $k => $v) {
				$num  = cache('setting')['tui_money'];
				$top = cache('setting')['tui_top'];

				if($v['us_wal']>cache('setting')['tui_top']){
					continue;
					model("User")->where('id',$v['id'])->setfied('us_is_tui',2);
				}
				if($v['us_wal']+$num>$top){
					$num = $top - $v['us_wal'];
					model("User")->where('id',$v['id'])->setfied('us_is_tui',2);
				}
				one_to_two($v['id'],$num,4);
			}

		}
	}
	
	/*---静态*/
	public function expand() {
		$list = model("StaFa")->where('fa_status',0)->select();
		if($list){
			foreach ($list as $k => $v) {
				$num  =  cache('setting')['sta_money']/30;
				$calcu = cache('setting')['sta_calcu'];
				$da = [
					'id'=>$v['id'],
				];
				if($v['fa_res']>$num){
					$number = $num;
					$da['fa_res'] = $v['fa_res']-$number;
				}else{
					$number = $v['fa_res'];
					$da['fa_res'] = 0;
					$da['fa_status'] = 1;
				}
				model('StaFa')->update($da);
				$msc = round($number*$calcu/100);
				$rec = $number-$msc;
				model("ProMsc")->tianjia($v['us_id'],$msc,1);
				model('ProRec')->tianjia($v['us_id'],$rec,1);
			}
		}
	}


					
}


