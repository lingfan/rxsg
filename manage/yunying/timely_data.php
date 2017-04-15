<?php
/**
 * @inform 运营接口 -- 及时数据
 * @author 许孝敦
 * @param null
 * @return 
 * @example  
 */
if (!defined("MANAGE_INTERFACE"))
exit;
try {
	//参数判断
	$day = date('Ymd');
	/***********今日当前活跃人数*************/
	$cur_active = "select count(distinct(uid)) from log_login where time>unix_timestamp($day) and time < unix_timestamp()";
	/***********今日当前的元宝消耗*************/
	$cur_bullion = "select abs(sum(count)) from log_money where count<0 and time>unix_timestamp($day) and time < unix_timestamp()";
	/***********今日元宝消耗人数*************/
	$cur_bullion_use = "select count(distinct(uid)) from log_money where count<0 and time>unix_timestamp($day) and time < unix_timestamp()";
	/***********城池数量*************/
	$city_number = "select count(*) from sys_city";
	/***********今日新建号*************/
	$today_new_reg = "select count(*) from sys_user where uid > 1000 and regtime<unix_timestamp() and regtime>unix_timestamp($day)";
	/***********今日在线*************/
	$cur_online = "select count(0) AS `count(*)` from `sys_online` where ((unix_timestamp() - `lastupdate`) < 60) ";
	/***********今日当前充值*************/
	$cur_pay = "select sum(money) from pay_log where time>unix_timestamp($day) and time < unix_timestamp()";
	/***********今日当前充值人数*************/
	$pay_num = "select count(distinct(passport)) from pay_log where time>unix_timestamp('$day') and time < unix_timestamp() ";
	/***********注册人数*************/
	$reg_num = "select count(*) from sys_user where uid > 1000";
	/***********注册上限*************/
	$reg_max = "select value from mem_state where state=100";
	$sqls = array('cur_active'=>$cur_active,'cur_bullion'=>$cur_bullion,'cur_bullion_use'=>$cur_bullion_use,'city_number'=>$city_number,
	'today_new_reg'=>$today_new_reg,'cur_online'=>$cur_online,'cur_pay'=>$cur_pay,'reg_num'=>$reg_num,'pay_num'=>$pay_num,'reg_max'=>$reg_max);
	foreach($sqls as $key=>$sql){
		$result = sql_fetch_one_cell($sql);
		if (mysql_error()) {
			throw new Exception(mysql_error());
		}else{
			if(!empty($result)){
				$ret['content'][$key] = $result;
			}else {
				$ret['content'][$key] = 0;
			}
		}
	}
}
catch (exception $e) {
	$ret=array();
	$ret['error'] = $e->getMessage();
}






?>