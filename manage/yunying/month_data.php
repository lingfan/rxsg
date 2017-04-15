<?php
/**
 * @inform 运营接口 -- 每月数据
 * @author 许孝敦
 * @param null
 * @return 
 * @example  
 */

if (!defined("MANAGE_INTERFACE"))
exit;
function twotoone($two)
//二维数组转成一维数组
{
	for ($i=0;$i<count($two);$i++)
	{
		for ($j=0;$j<count($two[$i]);$j++)
		{$one[]=$two[$i][$j];}
	}
	return $one;
}
try {
	//参数判断
	if (!isset($day) || empty($day)) {
		$time = date("Y-m-d");
		$day = sql_fetch_one_cell("select unix_timestamp('$time')");
	}else{
		if($day > time()){
			throw new Exception('date error');
		}
	}
	if(!isset($type) || empty($type)){
		throw new Exception('type error');
	}
	$regtime = sql_fetch_one_cell("select regtime from sys_user where uid=1001");
	if($day < $regtime){
		throw new Exception('date error');
	}
	switch ($type){
		case 'month_active_user':{
			/************活跃用户(月)************/
			$a_log = date('Y-m-d',$day);
			$tables = sql_fetch_rows("show tables like 'log_user_%'",'bloodwarlog');
			foreach ($tables as $value){
				$table[] = $value['Tables_in_bloodwarlog (log_user_%)'];
			}
			if(!in_array("log_user_$a_log",$table)){
				throw new Exception("table_error");
			}
			$n = count($table);
			for($i=0;$i<30;$i++){
				$b_log = date('Y-m-d',$day-(30-$i)*86400);
				$a = "log_user_".$b_log;
				if(in_array($a,$table)){
					break;
				}
			}
			$result = sql_fetch_one_cell("select count(f.uid) from `log_user_$a_log` as f,`log_user_$b_log` as e where f.uid = e.uid and f.onlinetime-e.onlinetime > 8 * 3600",'bloodwarlog');
			if(!empty($result)){
				$ret['content']['month_active_user'] = $result;
			}else {
				$ret['content']['month_active_user'] = 0;
			}
			break;
		}
	case 'month_login_num':{
			/*************登陆用户*************/
			$sql = "select count(distinct uid) from log_login where time < $day and time >=$day-30*86400";
			break;
		}
		case 'month_pay_num':{
			/************充值人数(月)************/
			$sql = "select count(distinct(passport)) from pay_log where time < '$day' and time >='$day'-30*86400";
			break;
		}
		case 'month_pay_add_num':{
			/************新增充值人数(月)************/
			$sql = "select count(distinct(passport)) from pay_log where time < '$day' and time >='$day'-30*86400 and passport not in (select passport from pay_log where time < '$day'-30*86400)";
			break;
		}
		case 'month_pay_account':{
			/***********最高充值账号(月)****************/
			$sql = "select passport from pay_log where time < $day and time >=$day-30*86400 and money = (select max(money) from pay_log)";
			break;
		}
		case 'month_pay_money':{
			/*************月充值总金额*************/
			$sql = "select sum(money) from pay_log where time < $day and time >=$day-30*86400";
			break;
		}
		case 'pay_num':{
			/*************充值次数(月)*************/
			$sql = "select count(*) from pay_log where time < $day and time >=$day-30*86400";
			break;
		}
		case 'month_build_user_loss':{
			//建号用户流失总数
			$result = sql_fetch_one_cell("select count(uid) from sys_online where ('$day'-lastupdate)/86400 >= 30");
			if(!empty($result)){
				$ret['content']['month_build_user_loss'] = $result;
			}else {
				$ret['content']['month_build_user_loss'] = 0;
			}
			break;
		}
		case 'month_old_user_lost':{
			/***********日老用户流失数*************/
			$result = sql_fetch_one_cell("select count(distinct(uid)) from sys_online where uid in (select distinct(uid) from sys_user where prestige >= 10000 and uid>1000 and regtime < $day) and ('$day'-lastupdate)/86400 >= 30");
			if(!empty($result)){
				$ret['content']['month_old_user_lost'] = $result;
			}else {
				$ret['content']['month_old_user_lost'] = 0;
			}
			break;
		}
		case 'month_pay_new_user':{
			/***********付费新建号用户*************/
			$act_uid = sql_fetch_one_cell("select group_concat(a.uid) as uid from (select uid from log_login where `time` < $day and `time` >= $day- 7 * 86400 and uid in (select uid from sys_user where `regtime` <  $day - 7 * 86400 and `regtime` >= $day- 8 * 86400) group by uid having count(uid)>2) as a,sys_online s where a.uid=s.uid and s.onlinetime>7200");
			$pay_uid = sql_fetch_rows("select distinct(su.uid) from sys_user as su, pay_log as pl where su.passport = pl.passport and pl.`time` < $day and pl.`time` >=$day- 7 * 86400 and su.uid in ($act_uid)");
			//			$act = twotoone($act_uid);
			//			$pay = twotoone($pay_uid);
			//$row = sql_fetch_rows("select pay.uid from (select distinct(su.uid) from sys_user as su, pay_log as pl where su.passport = pl.passport and pl.`time` < $day and pl.`time` >=$day- 8 * 86400) as pay  join (select distinct(lg.uid) from log_login as lg left join sys_online as so on so.uid = lg.uid and so.onlinetime > 7200 where lg.`time` < $day and lg.`time` >=$day- 8 * 86400 and lg.uid in (select uid from sys_user where `regtime` < $day - 7 * 86400 and `regtime` >=$day- 8 * 86400) having count(lg.uid) > 2) as act on pay.uid = act.uid");
			//$row = sql_fetch_rows("select pay.uid from (select distinct(su.uid) from sys_user as su, pay_log as pl where su.passport = pl.passport and pl.`time` < $day and pl.`time` >=$day- 8 * 86400) as pay where pay.uid in (select distinct(lg.uid) from log_login as lg left join sys_online as so on so.uid = lg.uid and so.onlinetime > 7200 where lg.`time` < $day and lg.`time` >=$day- 8 * 86400 and lg.uid in (select uid from sys_user where `regtime` < $day - 7 * 86400 and `regtime` >=$day- 8 * 86400) having count(lg.uid) > 2)");
			//$result = count(array_intersect($act,$pay));
			$result = count($pay_uid);
			if(!empty($result)){
				$ret['content']['month_pay_new_user'] = $result;
			}else {
				$ret['content']['month_pay_new_user'] = 0;
			}
			break;
		}
		case 'month_active_pay_user':{
			//活跃用户中的充值用户
			$a_log = date('Y-m-d',$day);
			$tables = sql_fetch_rows("show tables like 'log_user_%'",'bloodwarlog');
			foreach ($tables as $value){
				$table[] = $value['Tables_in_bloodwarlog (log_user_%)'];
			}
			if(!in_array("log_user_$a_log",$table)){
				throw new Exception("table_error");
			}
			$n = count($table);
			for($i=0;$i<30;$i++){
				$b_log = date('Y-m-d',$day-(30-$i)*86400);
				$a = "log_user_".$b_log;
				if(in_array($a,$table)){
					break;
				}
			}
			$act_uid = sql_fetch_rows("select f.uid from `log_user_$a_log` as f,`log_user_$b_log` as e where f.uid = e.uid and f.onlinetime-e.onlinetime > 8 * 3600",'bloodwarlog');
			$pay_uid = sql_fetch_rows("select distinct(passport) from pay_log where time < $day and time >=$day-30*86400");
			$actuid = twotoone($act_uid);
			$payuid = twotoone($pay_uid);
			$result = count(array_intersect($payuid,$actuid));
			if(!empty($result)){
				$ret['content']['month_active_pay_user'] = $result;
			}else {
				$ret['content']['month_active_pay_user'] = 0;
			}
			break;
		}
		case 'month_new_user_lost':{
			/***********新建号流失数*************/
			$sum = sql_fetch_one_cell("select count(uid) from sys_user where  uid > 1000 and regtime < $day - 7 * 86400 and regtime >=$day- 8 * 86400");
			$res= sql_fetch_one_cell("select count(distinct(uid)) from log_login where uid in (select distinct(uid) from sys_user where  uid > 1000 and regtime < $day - 7 * 86400 and regtime >=$day- 8 * 86400) and `time` between $day- 7 * 86400 and $day+86400");
			$result = $sum - $res;
			if(!empty($result)){
				$ret['content']['month_new_user_lost'] = $result;
			}else {
				$ret['content']['month_new_user_lost'] = 0;
			}
			break;
		}
	}
	if(!empty($sql)){
		$result = sql_fetch_one_cell($sql);
		if (mysql_error()) {
			throw new Exception(mysql_error());
		}else{
			if(!empty($result)){
				$ret['content'][$type] = $result;
			}else {
				$ret['content'][$type] = 0;
			}
		}
	}
}
catch (exception $e) {
	$ret['error'] = $e->getMessage();
}
?>