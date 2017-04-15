<?php
/**
 * @inform 运营接口 -- 及时数据恢复 --注册人数
 * @author 张昌彪
 * @param null
 * @return array(today_active,last_active_all)
 * @example  
 */
if (!defined("MANAGE_INTERFACE"))
exit;
try {
	//参数判断

	if(isset($day)  && !empty($day)) //$day的格式是当天凌晨的unixstamp
	{
		if($day > time()){
			throw new Exception('date error');
		}
		if (!isset($distance) || empty($distance)) {
			$distance = 24;
		}
		$spacetime = 86400 / $distance;
		for ($i = 0; $i < $distance; $i++) {
			$result = sql_fetch_one_cell("select count(*) from sys_user where uid > 1000 and regtime<" .
			(int)($day + $spacetime * $i + $spacetime));
			if (empty($result)){$result=0;}
			$reg_num[$i] = $result;
		}
	}
	if (mysql_error()) {
		throw new Exception(mysql_error());
	}
	if (empty($reg_num)){$reg_num=0;}
	$ret['content']['reg_num'] = $reg_num;
}
catch (exception $e) {
	$ret['error'] = $e->getMessage();
}






?>