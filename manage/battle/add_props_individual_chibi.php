<?php
/**
 * @author 方鸿鹏
 * @method 为玩家添加赤壁道具
 * @param $uid $prop_list
 * @return 
 */

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($uid)){exit("param_not_exist");}
	if (!isset($prop_list)){exit("param_not_exist");}
	
	$ret = sql_fetch_one("select name,passport from sys_user where uid='$uid' limit 1","chibinet");
	foreach ($prop_list as $prop) {
		$gid = $prop[0];
		$add_count = $prop[2];
		$cur_count = sql_fetch_one_cell ( "select count from sys_goods where gid='$gid' and uid='$uid'","chibinet" );
		if (abs ( $add_count ) > $cur_count && $add_count < 0) {
			$add_count = '-' . $cur_count;
		}
		sql_query ( "insert into sys_goods (`gid`,`uid`,`count`) values ('$gid','$uid','$add_count') ON DUPLICATE KEY UPDATE `count`=`count`+'$add_count'","chibinet" );
		sql_query ( "insert into log_goods (uid,gid,count,time,type) values ('$uid','$gid','$add_count',unix_timestamp(),5)","chibinet" );
	}
?>