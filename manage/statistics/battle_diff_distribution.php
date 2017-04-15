<?php
/**
 * @作者：张昌彪
 * @模块：产品数据 -- 战场难度分布
 * @功能：获得两类战场的难度分布
 * @返回：战场数据
 *		
 */
if (!defined("MANAGE_INTERFACE")) exit();
if (!isset($date)) {
	exit("param_not_exist");
}

$hj_num = sql_fetch_one_cell("select count(*) from (SELECT * FROM `log_battle_honour` where battleid='1001' and starttime > unix_timestamp('$date') and starttime < unix_timestamp('$date')+86400 group by battlefieldid) as a");
$gd_num = sql_fetch_one_cell("select count(*) from (SELECT * FROM `log_battle_honour` where battleid=2001 and starttime > unix_timestamp('$date') and starttime < unix_timestamp('$date')+86400 group by starttime) as a");
$scs_num = sql_fetch_one_cell("select count(*) from (SELECT * FROM `log_battle_honour` where battleid=3001 and starttime > unix_timestamp('$date') and starttime < unix_timestamp('$date')+86400 group by starttime) as a");
$dz_num = sql_fetch_one_cell("select count(*) from (SELECT * FROM `log_battle_honour` where battleid=4001 and starttime > unix_timestamp('$date') and starttime < unix_timestamp('$date')+86400 group by starttime) as a");
//$zy_num = sql_fetch_one_cell("select count(*) from (SELECT * FROM `log_battle_honour` where battleid=6001 and starttime > unix_timestamp('$date') and starttime < unix_timestamp('$date')+86400 group by starttime) as a");
$hj_level_num = array();
$gd_level_num = array();
$scs_level_num = array();
$dz_level_num = array();
//$zy_level_num = array();

for ($i = 0; $i < 12; $i++) {
	$level = $i + 1;
	$hj_level_num[$i] = sql_fetch_one_cell("select count(*) from (SELECT * FROM  `log_battle_honour` where battleid='1001' and level='$level' and starttime > unix_timestamp('$date') and starttime < unix_timestamp('$date')+86400 group by battlefieldid) as a");
	$gd_level_num[$i] = sql_fetch_one_cell("select count(*) from (SELECT * FROM  `log_battle_honour` where battleid='2001' and level='$level' and starttime > unix_timestamp('$date') and starttime < unix_timestamp('$date')+86400 group by starttime) as a");
	$scs_level_num[$i] = sql_fetch_one_cell("select count(*) from (SELECT * FROM  `log_battle_honour` where battleid='3001' and level='$level' and starttime > unix_timestamp('$date') and starttime < unix_timestamp('$date')+86400 group by starttime) as a");
	$dz_level_num[$i] = sql_fetch_one_cell("select count(*) from (SELECT * FROM  `log_battle_honour` where battleid='4001' and level='$level' and starttime > unix_timestamp('$date') and starttime < unix_timestamp('$date')+86400 group by starttime) as a");
	//$zy_level_num[$i] = sql_fetch_one_cell("select count(*) from (SELECT * FROM  `log_battle_honour` where battleid='6001' and level='$level' and starttime > unix_timestamp('$date') and starttime < unix_timestamp('$date')+86400 group by starttime) as a");
}

$ret['hj_num'] = $hj_num;
$ret['gd_num'] = $gd_num;
$ret['scs_num'] = $scs_num;
$ret['dz_num'] = $dz_num;
//$ret['zy_num'] = $zy_num;
$ret['hj_level_num'] = $hj_level_num;
$ret['gd_level_num'] = $gd_level_num;
$ret['scs_level_num'] = $gd_level_num;
$ret['dz_level_num'] = $gd_level_num;
//$ret['zy_level_num'] = $gd_level_num;
?>