<?php
	/**
	*参数：
	*排名的开始序号$startorder
	*排名的结束序号$endorder
	*
	*返回：$ret二维数组
	*
	*
	**/
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($startorder)) exit('param_not_exist');
	if (!isset($endorder)) exit('param_not_exist');
	
	$ret=sql_fetch_rows("select rank_user.rank,rank_user.uid,sys_user.passport,rank_user.name,rank_user.union,rank_user.prestige,rank_user.city,rank_user.people,cfg_nobility.name as nobname 
						from rank_user,sys_user,cfg_nobility where rank_user.rank>='$startorder' and rank_user.rank<='$endorder' and sys_user.uid = rank_user.uid and cfg_nobility.id = sys_user.nobility order by rank_user.rank");
?>