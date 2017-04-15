<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($union_id)){exit("param_not_exist");}
	
	$ret = sql_fetch_rows("select u.*,o.name as officepos,n.name as nobility,from_unixtime(regtime) as regtime,un.name as `union` from sys_user u left join cfg_office_pos o on o.id=u.officepos left join cfg_nobility n on n.id=u.nobility left join sys_union un on un.id=u.union_id where u.uid > 1000 and u.union_id='$union_id' order by u.name");
?>