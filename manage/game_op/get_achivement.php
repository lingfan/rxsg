<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($startorder)) exit('param_not_exist');
	if (!isset($endorder)) exit('param_not_exist');
	$ret = sql_fetch_rows("select ra.*,su.passport,su.prestige,ru.city,ru.people,cn.name as nobname from rank_achivement ra left join sys_user su on ra.uid=su.uid left join rank_user ru on ra.uid=ru.uid left join cfg_nobility cn on su.nobility=cn.id where ra.rank>=$startorder and ra.rank<=$endorder");
?>