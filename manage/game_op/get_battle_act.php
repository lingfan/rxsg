<?php
	//返回开启的活动战场列表
	//参数列表：无
	//返回开启的战场
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;
		$ret=array();
		$rows = sql_fetch_rows("select * from cfg_act_battle order by date");
		foreach($rows as &$row){
			$weektoday=intval(sql_fetch_one_cell("select weekday(from_unixtime(unix_timestamp()))+1"));
			if($row["date"]==0&&$row["actid"]<=7){//每周定期
				$date=sql_fetch_one_cell("select DATE_FORMAT(from_unixtime(unix_timestamp()+($row[actid]-$weektoday)*86400),'%m.%d')");
				$weekday=$row["actid"];
			}else{
				$date=sql_fetch_one_cell("select DATE_FORMAT(from_unixtime($row[date]),'%m.%d')");
				$weekday=sql_fetch_one_cell("select weekday(from_unixtime($row[date]))+1");
			}
			$row["date"]=$date;
			$row["weekday"]=$weekday;
		}
		$row['battle'] = sql_fetch_rows("select name from cfg_battle_field where type=1");
		$ret = $rows;
	

	
?>