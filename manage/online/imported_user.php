<?php
/**
 * @author 
 * @模块 运营数据 -- 导入用户的数据
 */ 
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($site_id)){exit("param_not_exist");}
	if (!isset($page_id)){exit("param_not_exist");}
	$day=date('Y-m-d');
	$users=sql_fetch_rows("select u.passport,u.name,n.name as nobility,u.prestige,
		(select sum(money) from pay_log p where p.passport=u.passport) as pay_coin, 
		from_unixtime(u.regtime) as reg_time,
		from_unixtime(o.lastupdate) as last_update,
		o.onlinetime
		from sys_user_comming c 
			left join sys_online o on o.uid=c.uid
			left join sys_user u on u.uid=o.uid
			left join cfg_nobility n on n.id=u.nobility
			 where c.site_id='$site_id' and c.page_id in ($page_id) order by u.nobility desc");
	$ret['users'] = $users;