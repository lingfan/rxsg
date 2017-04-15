<?php
	//名将占有率
	//参数列表：
	//返回
	//总数
	//占有数
	if (!defined("MANAGE_INTERFACE")) exit;
	
	$ret[0] = sql_fetch_rows("select count(c.province)as count,p.name from sys_city_hero h,sys_city c,cfg_province p where h.npcid>0 and h.hid<897 and c.cid = h.cid and c.province = p.id group by c.province");
	$ret[1] = sql_fetch_rows("select count(c.province)as count,p.name from sys_city_hero h,sys_city c,cfg_province p where h.npcid>0 and h.hid<897 and h.uid>1000 and c.cid = h.cid and c.province = p.id group by c.province");
?>