  <?php

//安置
function yeji($id, $path) {
	$yeji = 0;
	// $info = Db::name('user')->where('id',$id)->find();
	$where[] = ['us_tree', 'like', $path . "," . $id . "%"];

	$yeji += model('User')->where('id',$id)->sum('us_wal');
	$yeji += model('User')->where('id',$id)->sum('us_dong');
	$yeji += model('User')->where($where)->sum('us_wal');
	$yeji += model('User')->where($where)->sum('us_dong');
	return $yeji;
}
// 推荐
function yeye($id, $path) {
	$yeji = 0;
	// $info = Db::name('user')->where('id',$id)->find();
	$where[] = ['us_path', 'like', $path . "," . $id . "%"];
	$yeji += model('User')->where('id',$id)->sum('us_wal');
	$yeji += model('User')->where('id',$id)->sum('us_dong');
	$yeji += model('User')->where($where)->sum('us_wal');
	$yeji += model('User')->where($where)->sum('us_dong');
	return $yeji;
}

/**
 * 获取一个基于时间偏移的Unix时间戳
 *
 * @param string $type 时间类型，默认为day，可选minute,hour,day,week,month,quarter,year
 * @param int $offset 时间偏移量 默认为0，正数表示当前type之后，负数表示当前type之前
 * @param string $position 时间的开始或结束，默认为begin，可选前(begin,start,first,front)，end
 * @param int $year 基准年，默认为null，即以当前年为基准
 * @param int $month 基准月，默认为null，即以当前月为基准
 * @param int $day 基准天，默认为null，即以当前天为基准
 * @param int $hour 基准小时，默认为null，即以当前年小时基准
 * @param int $minute 基准分钟，默认为null，即以当前分钟为基准
 * @return int 处理后的Unix时间戳
 */
function unixtime($type = 'day', $offset = 0, $position = 'begin', $year = null, $month = null, $day = null, $hour = null, $minute = null) {
	$year = is_null($year) ? date('Y') : $year;
	$month = is_null($month) ? date('m') : $month;
	$day = is_null($day) ? date('d') : $day;
	$hour = is_null($hour) ? date('H') : $hour;
	$minute = is_null($minute) ? date('i') : $minute;
	$position = in_array($position, array('begin', 'start', 'first', 'front'));

	switch ($type) {
	case 'minute':
		$time = $position ? mktime($hour, $minute + $offset, 0, $month, $day, $year) : mktime($hour, $minute + $offset, 59, $month, $day, $year);
		break;
	case 'hour':
		$time = $position ? mktime($hour + $offset, 0, 0, $month, $day, $year) : mktime($hour + $offset, 59, 59, $month, $day, $year);
		break;
	case 'day':
		$time = $position ? mktime(0, 0, 0, $month, $day + $offset, $year) : mktime(23, 59, 59, $month, $day + $offset, $year);
		break;
	case 'week':
		$time = $position ?
		mktime(0, 0, 0, $month, $day - date("w", mktime(0, 0, 0, $month, $day, $year)) + 1 - 7 * (-$offset), $year) :
		mktime(23, 59, 59, $month, $day - date("w", mktime(0, 0, 0, $month, $day, $year)) + 7 - 7 * (-$offset), $year);
		break;
	case 'month':
		$time = $position ? mktime(0, 0, 0, $month + $offset, 1, $year) : mktime(23, 59, 59, $month + $offset, cal_days_in_month(CAL_GREGORIAN, $month + $offset, $year), $year);
		break;
	case 'quarter':
		$time = $position ?
		mktime(0, 0, 0, 1 + ((ceil(date('n', mktime(0, 0, 0, $month, $day, $year)) / 3) + $offset) - 1) * 3, 1, $year) :
		mktime(23, 59, 59, (ceil(date('n', mktime(0, 0, 0, $month, $day, $year)) / 3) + $offset) * 3, cal_days_in_month(CAL_GREGORIAN, (ceil(date('n', mktime(0, 0, 0, $month, $day, $year)) / 3) + $offset) * 3, $year), $year);
		break;
	case 'year':
		$time = $position ? mktime(0, 0, 0, 1, 1, $year + $offset) : mktime(23, 59, 59, 12, 31, $year + $offset);
		break;
	default:
		$time = mktime($hour, $minute, 0, $month, $day, $year);
		break;
	}
	return $time;
}

function note_code($mobile, $content) {
	header('Content-Type:text/html;charset=utf8');
	$sms = config('sms');
	$sms['password'] = ucfirst(md5($sms['password']));
	$sms['content'] = $sms['content'] . $content;
	// $sms['content'] = urlencode($sms['content']);
	$sms['mobile'] = $mobile;
	$query_str = http_build_query($sms);
	$gateway = "http://114.113.154.5/sms.aspx?action=send&" . $query_str;
	// dump($gateway);
	// echo "<br />";
	// $gateway = "http://114.113.154.5/sms.aspx?action=send&userid={$sms['userid']}&account={$sms['account']}&password={$sms['password']}&mobile={$mobile}&content={$sms['content']}&sendTime=";
	// dump($gateway);
	// $gateway = "= "http://114.113.154.5/sms.aspx?action=send&".$q".$query_str;
	// $result = file_get_contents($gateway);
	$url = preg_replace("/ /", "%20", $gateway);
	$result = file_get_contents($url);
	return $xml = simplexml_load_string($result);
	//  $this->object_array($xml);
}
//文件对象 上传图片
function uploads($file) {

	$info = $file->validate(['size' => '4096000'])
		->move(env('ROOT_PATH') . 'public/uploads/');
	if ($info) {
		$path = '/uploads/' . $info->getsavename();
		$path = str_replace('\\', '/', $path);
		return [
			'code' => 1,
			'path' => $path,
		];
	} else {
		return [
			'msg' => $file->getError(),
			'code' => 0,
		];
	}
}
//base64 上传图片
function base64_upload($base64) {
	$base64_image = str_replace(' ', '+', $base64);
	//post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
	if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)) {
		$image_name = rand(100, 999) . time() . '.png';
		$path = "/uploads/" . date("Ymd") . '/' . $image_name;

		$image_file = env('ROOT_PATH') . 'public/' . $path;

		if (!is_dir(dirname($image_file))) {
			mkdir(dirname($image_file), 0755, true);
		}
		//服务器文件存储路径
		if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))) {
			return $path;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
function check_path($path) {
	if (is_dir($path)) {
		return true;
	}
	if (mkdir($path, 0755, true)) {
		return true;
	}
	return false;
}

/*
区业绩
 */
function qu_yeji($id) {
	$left = model('User')->where("us_aid", $id)->where('us_qu', 0)->find();

	$right = model('User')->where("us_aid", $id)->where('us_qu', 1)->find();
	$l = 0;
	$r = 0;
	if (count($left)) {
		if ($left['us_status']) {
			$l = 1;
		}
		$where1[] = ['us_tree', 'like', $left['us_tree'] . "," . $left['id'] . '%'];
		$where1[] = ['us_status', '>', 0];
		$l += model('User')->where($where1)->count();
	}
	if (count($right)) {
		if ($right['us_status']) {
			$r = 1;
		}
		$where2[] = ['us_tree', 'like', $right['us_tree'] . "," . $right['id'] . '%'];
		$where2[] = ['us_status', '>', 0];
		$r += model('User')->where($where2)->count();
	}
	$arr = [];
	$arr['l_num'] = $l;
	$arr['r_num'] = $r;
	return $arr;
}

// 应用公共文件
function is_options() {
	return app('request')->isOptions();
}
function is_post() {
	return app('request')->isPost();
}
function is_get() {
	return app('request')->isGet();
}

// 对象转数组
function object_array($array) {
	if (is_object($array)) {
		$array = (array) $array;
	}
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			$array[$key] = object_array($value);
		}
	}
	return $array;
}

// /**
//  * 加密函数
//  * @param string 加密后字符串
//  */
// function mine_encrypt($data, $key = 'fes45dskfes45dsk') {
// 	$prep_code = serialize($data); //序列化

// 	$block = 8; // 获得加密算法的分组大小 8

// 	$pad = $block - (strlen($prep_code) % $block);
// 	if (($pad = $block - (strlen($prep_code) % $block)) < $block) {
// 		$prep_code .= str_repeat(chr($pad), $pad);
// 	}
// 	$encrypt = openssl_encrypt($prep_code, 'AES128', '55555555555', OPENSSL_RAW_DATA, $key);

// 	return base64_encode($encrypt);
// }
// 加密
function mine_encrypt1($str = '123456') {
	return hash_hmac('md5', $str, $str);
}

function mine_encrypt($data, $key = 'fes45dskfes45dsk') {
	$prep_code = serialize($data); //序列化

	$block = 8; // 获得加密算法的分组大小 8

	$pad = $block - (strlen($prep_code) % $block);
	if (($pad = $block - (strlen($prep_code) % $block)) < $block) {
		$prep_code .= str_repeat(chr($pad), $pad);
	}
	$encrypt = openssl_encrypt($prep_code, 'AES128', '55555555555', OPENSSL_RAW_DATA, $key);

	return base64_encode($encrypt);
}

/**
 *  解密函数
 * @param array 解密后数组
 */
function mine_decrypt($str, $key = 'fes45dskfes45dsk') {
	$str = base64_decode($str);
	$str = openssl_decrypt($str, 'AES128', '55555555555', OPENSSL_RAW_DATA, $key);
	$block = 8;
	$pad = ord($str[($len = strlen($str)) - 1]);
	if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) {
		$str = substr($str, 0, strlen($str) - $pad);
	}
	return unserialize($str);
}
/*------前后分离----------*/

// ------------------------------------------------------------------------
/**
 * 生成一段随机字符串
 * @param int $len 几位数
 */
function GetRandStr($len) {
	$chars = array(
		"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
		"l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
		"w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
		"H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
		"S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
		"3", "4", "5", "6", "7", "8", "9",
	);
	$charsLen = count($chars) - 1;
	shuffle($chars);
	$output = "";
	for ($i = 0; $i < $len; $i++) {
		$output .= $chars[mt_rand(0, $charsLen)];
	}
	return $output;
}
function getNumber($len) {
	$chars = array(
		"0", "1", "2",
		"3", "4", "5", "6", "7", "8", "9",
	);
	$charsLen = count($chars) - 1;
	shuffle($chars);
	$output = "";
	for ($i = 0; $i < $len; $i++) {
		$output .= $chars[mt_rand(0, $charsLen)];
	}
	return $output;
}
/**
 * 计算两点地理坐标之间的距离
 * @param  Decimal $longitude1 起点经度
 * @param  Decimal $latitude1  起点纬度
 * @param  Decimal $longitude2 终点经度
 * @param  Decimal $latitude2  终点纬度
 * @param  Int     $unit       单位 1:米 2:公里
 * @param  Int     $decimal    精度 保留小数位数
 * @return Decimal
 */
function getDistance($longitude1, $latitude1, $longitude2, $latitude2, $unit = 2, $decimal = 2) {

	$EARTH_RADIUS = 6370.996; // 地球半径系数
	$PI = 3.1415926;

	$radLat1 = $latitude1 * $PI / 180.0;
	$radLat2 = $latitude2 * $PI / 180.0;

	$radLng1 = $longitude1 * $PI / 180.0;
	$radLng2 = $longitude2 * $PI / 180.0;

	$a = $radLat1 - $radLat2;
	$b = $radLng1 - $radLng2;

	$distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
	$distance = $distance * $EARTH_RADIUS * 1000;

	if ($unit == 2) {
		$distance = $distance / 1000;
	}
	return round($distance, $decimal);

}

// 节点图
function jiedian() {
	return [
		0 => [
			'key' => 0,
			'parent' => 0,
			'source' => '/static/admin/img/toutou.png',
			'name' => '未注册',
		],
		1 => [
			'key' => 1,
			'parent' => 0,
			'source' => '/static/admin/img/toutou.png',
			'name' => '未注册',
		],
		2 => [
			'key' => 2,
			'parent' => 0,
			'source' => '/static/admin/img/toutou.png',
			'name' => '未注册',
		],
		3 => [
			'key' => 3,
			'parent' => 1,
			'source' => '/static/admin/img/toutou.png',
			'name' => '未注册',
		],
		4 => [
			'key' => 4,
			'parent' => 1,
			'source' => '/static/admin/img/toutou.png',
			'name' => '未注册',
		],
		5 => [
			'key' => 5,
			'parent' => 2,
			'source' => '/static/admin/img/toutou.png',
			'name' => '未注册',
		],
		6 => [
			'key' => 6,
			'parent' => 2,
			'source' => '/static/admin/img/toutou.png',
			'name' => '未注册',
		],
	];
}

//直推人数
function direct_count($id) {
	$count = model("User")->where('us_pid', $id)->count();
	return $count;
}
