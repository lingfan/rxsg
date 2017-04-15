<?php
//有效活跃用户数：当日上线时间超过10分钟的用户总数
if (!defined("MANAGE_INTERFACE"))
exit;
set_time_limit(30000);
switch ($type){
	case 'act_user':{
		/************活跃用户************/
		$tables = sql_fetch_rows("show tables like 'log_user_%'",'bloodwarlog');
		foreach ($tables as $value){
			$table[] = $value['Tables_in_bloodwarlog (log_user_%)'];
		}
		$n = count($table);
		for($i=0;$i<$n;$i++){
			$j=$i+1;
			$result[] = sql_fetch_one_cell("select count(a.uid) as `count` from `$table[$i]` a,`$table[$j]` b where a.uid = b.uid and (b.onlinetime-a.onlinetime>600)",'bloodwarlog');
			if(!empty($result)){
				$ret['content']['act_user'] = $result;
			}else {
				$ret['content']['act_user'] = 0;
			}
		}
		break;
	}
	case 'act_user_propor':{
		/************活跃用户比************/
		$tables = sql_fetch_rows("show tables like 'log_user_%'",'bloodwarlog');
		foreach ($tables as $value){
			$table[] = $value['Tables_in_bloodwarlog (log_user_%)'];
		}
		$n = count($table);
		for($i=0;$i<$n;$i++){
			$j=$i+1;
			$row1= sql_fetch_one_cell("select count(a.uid) from `$table[$i]` a,`$table[$j]` b where a.uid = b.uid and (b.onlinetime-a.onlinetime>600)",'bloodwarlog');
			$row2 = sql_fetch_one_cell("select count(a.uid) from `$table[$i]` a,`$table[$j]` b where a.uid = b.uid and (b.onlinetime-a.onlinetime> 0)",'bloodwarlog');
			$result[] = $row1/$row2;
			if(!empty($result)){
				$ret['content']['act_user_propor'] = $result;
			}else {
				$ret['content']['act_user_propor'] = 0;
			}
		}
		break;
	}
	case 'act_avg_user':{
		/************活跃用户平均************/
		$tables = sql_fetch_rows("show tables like 'log_user_%'",'bloodwarlog');
		foreach ($tables as $value){
			$table[] = $value['Tables_in_bloodwarlog (log_user_%)'];
		}
		$n = count($table);
		for($i=0;$i<$n;$i++){
			$j=$i+1;
			$row1= sql_fetch_one_cell("select count(a.uid) from `$table[$i]` a,`$table[$j]` b where a.uid = b.uid and (b.onlinetime-a.onlinetime>600)",'bloodwarlog');
			$row2 = sql_fetch_one_cell("select sum(b.onlinetime - a.onlinetime) from `$table[$i]` a,`$table[$j]` b where a.uid = b.uid and (b.onlinetime-a.onlinetime>600)",'bloodwarlog');
			$result[] = $row2/$row1;
			if(!empty($result)){
				$ret['content']['act_avg_user'] = $result;
			}else {
				$ret['content']['act_avg_user'] = 0;
			}
		}
		break;
	}
	case 'online_peak':{
		/************峰值在线用户数************/
		$result[] = sql_fetch_rows("select max(online) as `count`,from_unixtime(`time`,'%Y-%m-%d') as `day` from log_online group by from_unixtime(`time`,'%Y-%m-%d')",'bloodwarlog');
		if(!empty($result)){
			$ret['content']['online_peak'] = $result;
		}else {
			$ret['content']['online_peak'] = 0;
		}
		break;
	}
	case 'day_pay_suer':{
		/************当日消费人数************/
		$result[] = sql_fetch_rows("select count(distinct(uid)) as `count`,from_unixtime(`time`,'%Y-%m-%d') as `day` from log_money where `count` < 0 group by from_unixtime(`time`,'%Y-%m-%d')");
		if(!empty($result)){
			$ret['content']['day_pay_suer'] = $result;
		}else {
			$ret['content']['day_pay_suer'] = 0;
		}
		break;
	}
	case 'pay_money':{
		/************元宝消耗数************/
		$result[] = sql_fetch_rows("select abs(sum(`count`)) as `count`,from_unixtime(`time`,'%Y-%m-%d') as `day` from log_money where `count` < 0 group by from_unixtime(`time`,'%Y-%m-%d')");
		if(!empty($result)){
			$ret['content']['pay_money'] = $result;
		}else {
			$ret['content']['pay_money'] = 0;
		}
		break;
	}
	case 'avg_money':{
		/************人均消费额************/
		$result[] = sql_fetch_rows("select abs(sum(`count`))/count(distinct(uid)) as `count`,from_unixtime(`time`,'%Y-%m-%d') as `day` from log_money where `count` < 0 group by from_unixtime(`time`,'%Y-%m-%d')");
		if(!empty($result)){
			$ret['content']['avg_money'] = $result;
		}else {
			$ret['content']['avg_money'] = 0;
		}
		break;
	}
	case 'pay_avg_user':{
		/************消费用户平均在线时间************/
		$uids = sql_fetch_one_cell("select group_concat(distinct(uid)) from log_money where `count` < 0 group by from_unixtime(`time`,'%Y-%m-%d')");

		$tables = sql_fetch_rows("show tables like 'log_user_%'",'bloodwarlog');
		foreach ($tables as $value){
			$table[] = $value['Tables_in_bloodwarlog (log_user_%)'];
		}
		$n = count($table);
		for($i=0;$i<$n;$i++){
			$j=$i+1;
			$result[] = sql_fetch_one_cell("select sum(b.onlinetime-a.onlinetime)/count(a.uid) as `count`  from `$table[$i]` a,`$table[$j]` b where a.uid = b.uid and (b.onlinetime-a.onlinetime>0) and a.uid in ($uids)",'bloodwarlog');
			if(!empty($result)){
				$ret['content']['act_user'] = $result;
			}else {
				$ret['content']['act_user'] = 0;
			}
		}
		break;
	}
	case 'eff_act_user':{
		//有效活跃用户
		$result = sql_fetch_rows("select date(from_unixtime(time)) as `time`,uid,count(1) as `count` from log_login  group by uid,date(from_unixtime(time)) order by date(from_unixtime(time))");
		$n = count($result);
		for($i=0;$i<$n;$i++){
			$count = 0;
			for($j=0;$j<$n;$j++){
				$ftime = date("Y-m-d",strtotime($result[$i]['time'].'+1day'));
				$etime = date("Y-m-d",strtotime($result[$i]['time'].'+8day'));
				if(strtotime($ftime) <= strtotime($result[$j]['time']) && strtotime($result[$j]['time']) <= strtotime($etime)){
					if($result[$i]['uid'] == $result[$j]['uid']){
						$count = $count + $result[$j]['count'];
					}
				}
			}
			if($count > 2){
				$uid[$result[$i]['time']][] = $result[$i]['uid'];
			}else{
				$uid[$result[$i]['time']]=array();
			}
		}
		//print_r($uid);exit;
		foreach($uid as $key => $v){
			$ret[] = count($uid[$key]);
		}
		$result = $ret;
		if(!empty($result)){
			$ret['content']['eff_act_user'] = $result;
		}else {
			$ret['content']['eff_act_user'] = 0;
		}
		break;
	}
}
?>