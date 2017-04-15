<?php
	//联盟充值排名
	//参数列表：
	//返回
	//array[0]:array{day,count}
	if (!defined("MANAGE_INTERFACE")) exit;
	
	$ret = sql_fetch_rows("select su.name,ifnull(a.total,0) total,ifnull(b.online,0) online,ifnull(c.money,0) count,ifnull(c.number,0) number
											from sys_union su
											left join (select su.id,count(sue.uid) total from sys_union su,sys_user sue where sue.union_id=su.id group by su.id) a on su.id=a.id
											left join (select su.id,count(sue.uid) online from sys_union su,sys_user sue,sys_online so where sue.union_id=su.id and sue.uid=so.uid and (unix_timestamp()-so.lastupdate)<60 group by su.id) b on su.id=b.id
											left join (select su.id,ifnull(sum(lm.count),0) money,count(distinct sue.uid) number from sys_user sue,log_money lm,sys_union su where lm.uid=sue.uid and lm.type=0 and sue.union_id=su.id group by su.id) c on su.id=c.id
											order by count desc"); 
?>