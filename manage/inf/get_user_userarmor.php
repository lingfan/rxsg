<?php
	//获得玩家装备使用信息
	//参数列表：
	//cid:城市id
	//许孝敦
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$ret = sql_fetch_rows("select a.sid,a.uid,a.strong_level,a.hp_max,a.embed_pearls,b.name,b.part,b.attribute from sys_user_armor a , cfg_armor b where a.uid='$uid' and a.armorid=b.id order by b.part,b.level desc");
	if(empty($ret))$ret = 'no data';
?>