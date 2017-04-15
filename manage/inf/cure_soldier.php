<?php
if (!defined("MANAGE_INTERFACE"))
	exit();
if (!isset($type))
	exit("param_not_exist");
if (!isset($cid))
	exit("param_not_exist");
if ($type == 'lamster') {
	if(isset($lamtype) && $lamtype == "sent"){
		sql_query("delete from mem_city_lamster where cid = '$cid'");
		$ret=1;
	}
	else{
		$city_lamster = sql_fetch_rows("select count, sid, cid from mem_city_lamster where cid=$cid");
		if(!empty($city_lamster)){
			foreach ($city_lamster as $lamster)
				sql_query("insert into sys_city_soldier (cid,sid,count) values ('$cid','$lamster[sid]','$lamster[count]') on duplicate key update count=count+'$lamster[count]'");
		}
		//sql_query("update sys_city_soldier s,(select l.`count`,l.sid,l.cid from `mem_city_lamster` l  where l.cid=$cid) as a set s.`count`=s.`count`+a.`count` where a.cid=s.cid and a.sid=s.sid");
		sql_query("delete from mem_city_lamster where cid=$cid");
		$ret = 1;
	}
}
elseif ($type == 'wounded') {
	$city_wounded = sql_fetch_rows("select count, sid, cid from mem_city_wounded where cid=$cid");
	if(!empty($city_wounded)){
			foreach ($city_wounded as $wounded)
				sql_query("insert into sys_city_soldier (cid,sid,count) values ('$cid','$wounded[sid]','$wounded[count]') on duplicate key update count=count+'$wounded[count]'");
	}
	//sql_query("update sys_city_soldier s,(select l.`count`,l.sid,l.cid from `mem_city_wounded` l  where l.cid=$cid) as a set s.`count`=s.`count`+a.`count` where a.cid=s.cid and a.sid=s.sid");
	sql_query("delete from mem_city_wounded where cid=$cid");
	$ret = 1;
}
else {
	$ret = 2;
}
?>