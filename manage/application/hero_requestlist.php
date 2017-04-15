<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($id)){exit("param_not_exist");}

	$ret = sql_fetch_rows("select r.hero_name,r.id,c.name as city_name,u.passport,u.name,r.hero_level,r.hero_name,r.hero_sex,r.hero_face,r.affairs_base,r.bravery_base,r.wisdom_base,r.loyalty,r.affairs_add,r.bravery_add,r.wisdom_add,from_unixtime(time) as requesttime,m.username,r.reason from adm_hero_request r left join sys_city c on c.cid=r.cid left join sys_user u on u.uid=r.uid left join adm_user m on m.userid=r.adminid where server_id='$id' and r.state=0");
?>