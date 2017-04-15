<?php
/**
 * @作者：张昌彪
 * @模块: 产品数据 -- 每日战场数据
 * @功能: 获得每日的战场数据
 * @返回：
 * ret[0]:每日勋章获得总数量
 * ret[1]:每日荣誉获得总数量
 * ret[2]:每日战场参与人数
 * ret[3]:每日战场开启总数量
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	
//**************************勋章
		$ret = sql_fetch_rows("select a.day,a.huang,a.yuan,a.cao,a.scs,a.ctd,a.ytd,a.uid,b.huangcount,b.guancount,b.shicshi,b.taodong from
													(select from_unixtime(starttime,'%Y-%m-%d') AS `day`,
													sum(case when battleid='1001' then metal else 0 end) as huang,
													sum(case when battleid='2001' and unionid=3 then metal else 0 end) as yuan,
													sum(case when battleid='2001' and unionid=4 then metal else 0 end) as cao,
													sum(case when battleid='3001' then metal else 0 end) as scs,
													sum(case when battleid='4001' and unionid=7 then metal else 0 end) as ctd,
													sum(case when battleid='4001' and unionid=9 then metal else 0 end) as ytd,
													count(DISTINCT uid) as uid
													from log_battle_honour WHERE starttime BETWEEN  unix_timestamp('$startday') AND unix_timestamp('$endday')+86400 group by from_unixtime(starttime,'%Y-%m-%d')) a
												left join
													(select from_unixtime(starttime,'%Y-%m-%d') AS `day`,
													sum(case when battleid='1001' then 1 else 0 end) as huangcount,
													sum(case when battleid='2001' then 1 else 0 end) as guancount,
													sum(case when battleid='3001' then 1 else 0 end) as shicshi,
													sum(case when battleid='4001' then 1 else 0 end) as taodong
													from (select battleid,starttime from log_battle_honour WHERE starttime BETWEEN  unix_timestamp('$startday') AND unix_timestamp('$endday')+86400 group by battlefieldid,starttime) p
													group by from_unixtime(starttime,'%Y-%m-%d')) b
												on a.day = b.day");
//***************************荣誉
//		$day_uids = sql_fetch_rows("select uid from log_battle_honour where starttime>='$today' and starttime<'$today'+86400 group by uid");	
//		$sum = 0;
//		foreach($day_uids as $row)
//		{
//			$uid = $row['uid'];
//			$last_honour = 0;
//			$today_honour = 0;
//			$last_honour = sql_fetch_one_cell("select honour from log_battle_honour where uid = $uid and starttime<'$today' order by starttime desc limit 1");
//			$today_honour = sql_fetch_one_cell("select honour from log_battle_honour where uid = $uid and starttime<'$today'+86400 order by starttime desc limit 1");
//			$m = (int)$today_honour-(int)$last_honour;
//			$sum =$sum + $m;
//		}
//		$ret[$today][] = $sum;	
?>