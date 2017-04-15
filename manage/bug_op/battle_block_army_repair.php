<?php
if (!defined("MANAGE_INTERFACE"))
	exit();
sql_query("update sys_troops set state=1,starttime=unix_timestamp(),pathtime=30,cid=startcid,endtime=unix_timestamp()+30 where battlefieldid=0  and task=7 and state=4");
$ret = "战场卡兵问题处理成功";
?>