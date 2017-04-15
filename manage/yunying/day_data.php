<?php
/**
 * @inform 运营接口 -- 日数据恢复
 * @author 许孝敦
 * @param null
 * @return array(today_avg,)
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
	if (!isset($day) || empty($day)){
		$time = date("Ymd");
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
		case 'last_active_all':{
			/*************活跃用户*************/
			$taday= date("Y-m-d",$day-86400);
			$yesterday = date("Y-m-d",$day);
			$tab_yesterday = "log_user_$yesterday";
			$tab_taday = "log_user_$taday";
			$tables = sql_fetch_rows("show tables like 'log_user_%'",'bloodwarlog');
			foreach ($tables as $value){
				$table[] = $value['Tables_in_bloodwarlog (log_user_%)'];
			}
			if(in_array($tab_yesterday,$table) && in_array($tab_taday,$table)){
				$result = sql_fetch_one_cell("select count(a.uid) from `$tab_taday` a  left join `$tab_yesterday` b on a.uid = b.uid where b.onlinetime-a.onlinetime>1800",'bloodwarlog');
			}else{
				$result=0;
			}
			if(!empty($result)){
				$ret['content']['last_active_all'] = $result;
			}else {
				$ret['content']['last_active_all'] = 0;
			}
			break;
		}
		case 'login_num':{
			/*************活跃用户*************/
			$sql = "select count(distinct uid) from log_login where time < $day and time >=$day-86400";
			break;
		}
		case 'last_bullion_all':{
			/*************元宝消耗*************/
			$sql = "select abs(sum(count)) from log_money where count<0 and time < $day and time >=$day-86400";
			break;
		}
		case 'last_bullion_use_all':{
			/*************元宝消耗人数*************/
			$sql = "select count(distinct(uid)) from log_money where count<0 and time < $day and time >=$day-86400";
			break;
		}
		case 'last_new_reg_all':{
			/*************新建号数*************/
			$sql = "select count(*) from sys_user where uid > 1000 and regtime<$day and regtime>=$day-86400";
			break;
		}
		case 'last_pay_money':{
			/*************日充值金额*************/
			$sql = "select sum(money) from pay_log where time < $day and time >=$day-86400 and money>0";
			break;
		}
		case 'pay_money_all':{
			/*************充值总金额*************/
			$sql = "select sum(money) from pay_log where time < $day and money>0";
			break;
		}
		case 'last_pay_all':{
			/*************日充值人数*************/
			$sql = "select count(distinct(passport)) from pay_log where time < $day and time >=$day-86400";
			break;
		}
		case 'pay_num_all':{
			/*************充值总人数*************/
			$sql = "select count(distinct(passport)) from pay_log where `time` < $day";
			break;
		}
		case 'city_number':{
			/*************城池数量*************/
			$sql = "select count(*) from sys_city";
			break;
		}
		case 'reg_num':{
			/***********注册人数****************/
			$sql = "select count(*) from sys_user where uid > 1000 and regtime<$day";
			break;
		}
		case 'reg_max':{
			/***********注册上限****************/
			$sql = "select value from mem_state where state=100";
			break;
		}
		case 'pay_money_highest':{
			/***********单笔充值最高金额****************/
			$sql = "select max(money) from pay_log where time < $day and time >=$day-86400";
			break;
		}
		case 'pay_account_highest':{
			/***********充值最高账号****************/
			$sql = "select passport from pay_log where time < $day and time >=$day-86400 and money = (select max(money) from pay_log)";
			break;
		}
		case 'pay_num':{
			/***********日的充值次数****************/
			$sql = "select count(*) from pay_log where time < $day and time >=$day-86400";
			break;
		}
		case 'pay_add_num':{
			/***********新增充值人数****************/
			$sql = "select count(distinct(passport)) from pay_log where time < '$day' and time >='$day'-86400 and passport not in (select passport from pay_log where time <'$day'-86400)";
			break;
		}
		case 'last_max_online':{
			/*************在线人数*************/
			$result = sql_fetch_one_cell("select max(online) from log_online where time < $day and time >=$day-86400",'bloodwarlog');
			if(!empty($result)){
				$ret['content']['last_max_online'] = $result;
			}else {
				$ret['content']['last_max_online'] = 0;
			}
			break;
		}
		case 'avg_num':{
			/*************平均在线人数*************/
			$result = sql_fetch_one_cell("select avg(online) as online from log_online where time < $day and time >=$day-86400","bloodwarlog");
			if(!empty($result)){
				$ret['content']['avg_num'] = round($result);
			}else {
				$ret['content']['avg_num'] = 0;
			}
			break;
		}
		case 'new_user_lost':{
			/***********新建号流失数*************/
			$sum = sql_fetch_one_cell("select count(uid) from sys_user where  uid > 1000 and regtime < $day - 7 * 86400 and regtime >=$day- 8 * 86400");
			$res= sql_fetch_one_cell("select count(distinct(uid)) from log_login where uid in (select distinct(uid) from sys_user where  uid > 1000 and regtime < $day - 7 * 86400 and regtime >=$day- 8 * 86400) and `time` between $day- 7 * 86400 and $day+86400");
			$result = $sum - $res;
			if(!empty($result)){
				$ret['content']['new_user_lost'] = $result;
			}else {
				$ret['content']['new_user_lost'] = 0;
			}
			break;
		}
		case 'old_user_lost':{
			/***********老用户流失总数*************/
			//$sum = sql_fetch_one_cell("select count(uid) from sys_user where prestige >= 10000 and uid>1000");
			$result = sql_fetch_one_cell("select count(distinct(uid)) from sys_online where uid in (select distinct(uid) from sys_user where prestige >= 10000 and uid>1000) and (unix_timestamp()-lastupdate)/86400 >= 30");
			//$result = $sum - $res;
			if(!empty($result)){
				$ret['content']['old_user_lost'] = $result;
			}else {
				$ret['content']['old_user_lost'] = 0;
			}
			break;
		}
		case 'day_old_user_lost':{
			/***********日老用户流失数*************/
			//$sum = sql_fetch_one_cell("select count(uid) from sys_user where prestige >= 10000 and uid>1000 and regtime < $day");
			$result = sql_fetch_one_cell("select count(distinct(uid)) from sys_online where uid in (select distinct(uid) from sys_user where prestige >= 10000 and uid>1000 and regtime < $day) and ('$day'-lastupdate)/86400 >= 30");
			if(!empty($result)){
				$ret['content']['day_old_user_lost'] = $result;
			}else {
				$ret['content']['day_old_user_lost'] = 0;
			}
			break;
		}
		case 'yesterday_old_user_lost':{
			/***********前日老用户流失数*************/
			$result = sql_fetch_one_cell("select count(distinct(uid)) from sys_online where uid in (select distinct(uid) from sys_user where prestige >= 10000 and uid>1000 and regtime < $day-86400) and ($day-86400-lastupdate)/86400 >= 30");
			if(!empty($result)){
				$ret['content']['yesterday_old_user_lost'] = $result;
			}else {
				$ret['content']['yesterday_old_user_lost'] = 0;
			}
			break;
		}
		case 'city_percent':{
			/***********名城占有率*************/
			$ctotal = sql_fetch_one_cell("select count(*) as count from sys_city c where c.type>0");
			$cnum = sql_fetch_one_cell("select count(*) as count from sys_city c where c.type>0 and c.uid>1000");
			if(empty($cnum)){$cnum=0;}else{
				$result = round($cnum/$ctotal,4) * 100;
			}
			if(!empty($result)){
				$ret['content']['city_percent'] = $result;
			}else {
				$ret['content']['city_percent'] = 0;
			}
			break;
		}
		case 'hero_percent':{
			/***********名将占有率*************/
			$htotal = sql_fetch_one_cell("select count(c.province)as count from sys_city_hero h,sys_city c where h.npcid>0 and h.hid<897 and c.cid = h.cid");
			$hnum = sql_fetch_one_cell("select count(c.province)as count from sys_city_hero h,sys_city c where h.npcid>0 and h.hid<897 and h.uid>1000 and c.cid = h.cid");
			if(empty($hnum)){$result=0;}else{
				$result = round($hnum/$htotal,4) * 100;
			}
			if(!empty($result)){
				$ret['content']['hero_percent'] = $result;
			}else {
				$ret['content']['hero_percent'] = 0;
			}
			break;
		}
		case 'new_day_bullion':{
			/***********新增元宝消耗人数(第一次) *************/
			$result = sql_fetch_one_cell("select count(distinct(uid)) from log_money where `time` < $day and `time` >=$day-86400 and uid not in (select distinct(uid) from log_money where `time` < $day - 86400)");
			if(!empty($result)){
				$ret['content']['new_day_bullion'] = $result;
			}else {
				$ret['content']['new_day_bullion'] = 0;
			}
			break;
		}
		case 'eff_new_user':{
			/***********有效新建用户数 *************/
			$err_uid = sql_fetch_rows("select distinct(uid) from log_login where `time` < $day and `time` >=$day- 7 * 86400 and uid in (select uid from sys_user where `regtime` < $day - 7 * 86400 and `regtime` >=$day- 8 * 86400) group by uid having count(uid)>2");
			$result = count($err_uid);
			if(!empty($result)){
				$ret['content']['eff_new_user'] = $result;
			}else {
				$ret['content']['eff_new_user'] = 0;
			}
			break;
		}
		case 'act_new_user':{
			/***********活跃新建号用户 *************/
			$act_uid = sql_fetch_rows("select a.uid from (select uid from log_login where `time` < '$day' and `time` >= $day - 7 * 86400 and uid in (select uid from sys_user where `regtime` <  $day - 7 * 86400 and `regtime` >= $day - 8 * 86400) group by uid having count(uid)>2) as a,sys_online s where a.uid=s.uid and s.onlinetime>7200");
			$result = count($act_uid);
			if(!empty($result)){
				$ret['content']['act_new_user'] = $result;
			}else {
				$ret['content']['act_new_user'] = 0;
			}
			break;
		}
		case 'pay_new_user':{
			/***********付费新建号用户*************/
			$act_uid = sql_fetch_one_cell("select group_concat(a.uid) as uid from (select uid from log_login where `time` < $day and `time` >= $day- 7 * 86400 and uid in (select uid from sys_user where `regtime` <  $day - 7 * 86400 and `regtime` >= $day- 8 * 86400) group by uid having count(uid)>2) as a,sys_online s where a.uid=s.uid and s.onlinetime>7200");
			if(!empty($act_uid)){
				$pay_uid = sql_fetch_rows("select distinct(su.uid) from sys_user as su, pay_log as pl where su.passport = pl.passport and pl.`time` < $day and pl.`time` >=$day- 7 * 86400 and su.uid in ($act_uid)");
				$result = count($pay_uid);
			}else {
				$result = 0;
			}
			if(!empty($result)){
				$ret['content']['pay_new_user'] = $result;
			}else {
				$ret['content']['pay_new_user'] = 0;
			}
			break;
		}
		case 'hoard_num':{
			/***********元宝囤积人数*************/
			$result = sql_fetch_one_cell("select count(uid) from sys_user where uid>1000 and money > 0 and regtime < $day");
			if(!empty($result)){
				$ret['content']['hoard_num'] = $result;
			}else {
				$ret['content']['hoard_num'] = 0;
			}
			break;
		}
		case 'hoard_money':{
			/***********元宝囤积数*************/
			$result = sql_fetch_one_cell("select sum(money) from sys_user where uid > 1000 and regtime < $day and money>0");
			if(!empty($result)){
				$ret['content']['hoard_money'] = $result;
			}else {
				$ret['content']['hoard_money'] = 0;
			}
			break;
		}
		case 'build_user_loss':{
			/***********建号用户流失总数*************/
			$result = sql_fetch_one_cell("select count(uid) from sys_online where ('$day'-lastupdate)/86400 >= 30");
			if(!empty($result)){
				$ret['content']['build_user_loss'] = $result;
			}else {
				$ret['content']['build_user_loss'] = 0;
			}
			break;
		}
		case 'pay_user_loss':{
			/***********充值用户流失总数*************/
			sql_query("set group_concat_max_len=9999999");
			$pay_user = sql_fetch_one_cell("select group_concat('\'',pa.passport,'\'') from( select distinct(passport) as passport from pay_log where `time` < '$day'-86400) as pa");
			if(!empty($pay_user)){
				$loss_user = sql_fetch_rows("select su.passport as pa from sys_online as so,sys_user as su where ('$day'-so.lastupdate)/86400 >= 30 and su.uid = so.uid and su.passport in ($pay_user)");
				$result = count($loss_user);
			}else {
				$result = 0;
			}
			if (mysql_error()) {
				throw new Exception(mysql_error());
			}
			if(!empty($result)){
				$ret['content']['pay_user_loss'] = $result;
			}else {
				$ret['content']['pay_user_loss'] = 0;
			}
			break;
		}
		case 'day_pay_user_loss':{
			/***********前日充值用户流失数*************/
			sql_query("set group_concat_max_len=9999999");
			$pay_user = sql_fetch_one_cell("select group_concat('\'',pa.passport,'\'') from( select distinct(passport) as passport from pay_log where `time` < '$day'-86400) as pa");
			if(!empty($pay_user)){
				$loss_user = sql_fetch_rows("select su.passport from sys_online as so,sys_user as su where ('$day'-86400-so.lastupdate)/86400 >= 30 and su.uid = so.uid and su.passport in ($pay_user)");
				$result = count($loss_user);
			}else{
				$result = 0;
			}
			if (mysql_error()) {
				throw new Exception(mysql_error());
			}
			if(!empty($result)){
				$ret['content']['day_pay_user_loss'] = $result;
			}else {
				$ret['content']['day_pay_user_loss'] = 0;
			}
			break;
		}
		case 'money_consumer':{
			/***********元宝消耗总数*************/
			$result = sql_fetch_one_cell("select abs(sum(count)) from log_money where count < 0 and `time` < '$day'");
			if (mysql_error()) {
				throw new Exception(mysql_error());
			}
			if(!empty($result)){
				$ret['content']['money_consumer'] = $result;
			}else {
				$ret['content']['money_consumer'] = 0;
			}
			break;
		}
		case 'money_num':{
			/***********元宝消耗人总数*************/
			$result = sql_fetch_one_cell("select count(distinct(uid)) from log_money where count < 0 and `time` < '$day'");
			if(!empty($result)){
				$ret['content']['money_num'] = $result;
			}else {
				$ret['content']['money_num'] = 0;
			}
			break;
		}
		case 'pay_account_money':{
			/***********最高账号充值金额****************/
			$result = sql_fetch_one_cell("select max(m) as ma from (select sum(money) as m from pay_log where time < $day and time >=$day-86400 group by passport) as ta");
			if(!empty($result)){
				$ret['content']['pay_account_money'] = $result;
			}else {
				$ret['content']['pay_account_money'] = 0;
			}
			break;
		}
		
		case 'active_pay_user':{
			//活跃用户中的充值用户
			$taday= date("Y-m-d",$day-86400);
			$yesterday = date("Y-m-d",$day);
			$tab_yesterday = "log_user_$yesterday";
			$tab_taday = "log_user_$taday";
			$tables = sql_fetch_rows("show tables like 'log_user_%'",'bloodwarlog');
			foreach ($tables as $value){
				$table[] = $value['Tables_in_bloodwarlog (log_user_%)'];
			}
			if(in_array($tab_yesterday,$table) && in_array($tab_taday,$table)){
				sql_query("set group_concat_max_len=9999999");
				$rows = sql_fetch_one_cell("select group_concat('\'',a.passport,'\'')  from `$tab_taday` a  left join `$tab_yesterday` b on a.uid = b.uid where b.onlinetime-a.onlinetime>1800",'bloodwarlog');
				if(!empty($rows)){
					$result = sql_fetch_one_cell("select count(distinct(passport)) from pay_log where time < $day and time >=$day-86400 and passport in ($rows)");
				}else{
					$result = 0;
				}
			}else{
				$result=0;
			}
			//$passports = sql_fetch_one_cell("select group_concat('\'',su.passport,'\'') from log_login as ll,sys_user as su where ll.time < $day and ll.time >=$day-86400 and ll.uid = su.uid");
			if(!empty($result)){
				$ret['content']['active_pay_user'] = $result;
			}else {
				$ret['content']['active_pay_user'] = 0;
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
	$ret=array();
	$ret['error'] = $e->getMessage();
}