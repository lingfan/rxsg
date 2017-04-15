<?php

	if (!defined("MANAGE_INTERFACE")) exit;

	$ret = sql_fetch_rows("select c.name as cityname,f.name,b.level from sys_building b left join sys_city c on c.cid=b.cid left join cfg_building f on f.bid=b.bid where b.level=0 and b.state=0");

?>