<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	$ret =  sql_fetch_rows("select adm_name,opration,opration_content,FROM_UNIXTIME(oprate_time) as time,id  from adm_log where opration='apply_yuanbao' order by oprate_time desc");
?>