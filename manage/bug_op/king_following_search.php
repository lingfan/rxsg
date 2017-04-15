<?php
if (!defined("MANAGE_INTERFACE")) exit();
if (!isset($name)) {
	exit("param_not_exist");
}
$result = sql_fetch_one("select name,uid from sys_user where name='$name'");
$ret[] = $result;
if (!empty($result)) {
	$rows_result = sql_fetch_rows("select * from sys_city_hero where uid='$result[uid]' and npcid=0 and state=0");
	if (!empty($rows_result)) {
		$ret[] = 1;
	}
	else {
		$ret[] = 0;
	}
}
else {
	$ret[] = 0;
}
?>