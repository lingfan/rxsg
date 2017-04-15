<?php
require_once("./interface.php");
require_once("./utils.php");

function getGuidesByGroup($uid,$param){
	$group=intval(array_shift($param));
	$ret=array();
	$ret[]=sql_fetch_rows("select b.* from  cfg_guide b where `group`='$group'  order by b.gid");
	return $ret;
}
function completeGuide($uid,$param){
	$gid=intval(array_shift($param));
	sql_query("insert into sys_user_guide(uid,gid,state,time) values($uid,$gid,1,unix_timestamp()) on duplicate key update state = 1");
}

?>