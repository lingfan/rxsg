<?php
/**
 * @author 方鸿鹏
 * @method 查询查看-名将查询
 * @param $search_name 名将名称
 * @return 
 * array{
 * 	0=>{
 * 		type 经历类型
 *      count 好感度
 *      time  时间
 *      passport 用户账号
 *      uname  君主名
 *      hname 名将名称
 * 		}
 * }
 */

if (!defined("MANAGE_INTERFACE")) exit;	
if (!isset($search_name))exit("param_not_exist");

/*if(!empty($search_name)){*/
$sql = "select l.type,l.count,l.npcid,from_unixtime(l.time) as time,u.passport,u.name as uname,nh.name as hname 
		    from log_lionize l left join sys_user u on l.uid=u.uid left join cfg_npc_hero nh on l.npcid = nh.npcid 
		    where nh.name = '$search_name' order by l.npcid asc,time desc";
/*}
else{
	$sql = "select l.type,l.count,l.npcid,from_unixtime(l.time) as time,u.passport,u.name as uname,nh.name as hname 
		    from log_lionize l left join sys_user u on l.uid=u.uid left join cfg_npc_hero nh on l.npcid = nh.npcid 
		    where nh.npcid = '$search_npcid' order by time desc";
}*/
$ret = sql_fetch_rows($sql);
$sql_error = mysql_error();
if(empty($ret)||!empty($sql_error)) $ret='no data';
?>