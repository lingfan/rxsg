<?php
	//获得玩家技能书合成日志
	//参数列表：
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$ret = sql_fetch_rows("select c.name,l.* from log_book_strong l left join cfg_book c on c.id=l.bid and c.level=l.startlevel where l.uid='$uid' order by l.time desc");
	if(empty($ret))$ret = 'no data';
?>