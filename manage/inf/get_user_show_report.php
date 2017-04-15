<?php
/**
 * @author 阮钰标
 * @模块 查询查看 -- 查询用户
 * @功能 查询用户战报列表
 * @参数 $uid int 用户的uid
 * @返回 array 战报列表
 *       如果为空就返回no data
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($id))exit("param_not_exist");
	$report = array();
	$battle = array();
	$report = sql_fetch_one("select *,from_unixtime(time,'%Y%m%d') as datetime from sys_report where id='$id'");
	if (!empty($report) && !empty($report[battleid]))
		$battle = sql_fetch_rows("select * from sys_battle_report where battleid=$report[battleid] order by round");
	if(empty($report['content'])){
    	$report['content']=file_get_contents("/bloodwar/server/game/report_data/".$report['datetime']."/".$id.".html");
    }
	$ret['report'] = $report;
	$ret['battle'] = $battle;
?>