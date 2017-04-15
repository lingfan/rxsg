<?php
	//获得玩家装备使用信息
	//参数列表：
	//cid:城市id
	//许孝敦
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$ret = sql_fetch_rows("select c.name,u.level,h.name as hero from cfg_book c ,sys_user_book u left join sys_city_hero h on h.hid=u.hid where u.uid='$uid' and c.id=u.bid and c.level=u.level order by u.bid,u.level desc;");
	if(empty($ret))$ret = 'no data';
?>