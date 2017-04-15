<?php
/**
 * @method 获取玩家城池信息
 * @author 方鸿鹏
 * @param $passport 账号 $name 君主名 $uid 用户id
 * @return 
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($name)&&!isset($passport)&&!isset($uid)){exit("param_not_exist");}
	
	if(isset($uid)){
		$sql = "select c.uid,c.cid as cid,c.name as name,c.type as type,c.state as state,people,gold,morale,complaint,food,wood,rock,iron,
				CONCAT(c.cid%1000,',',floor(c.cid/1000)) as position 
				from sys_city c,mem_city_resource r where c.uid='$uid' and c.cid=r.cid";
	}else{
		if(!empty($passport)){
			$sql = "select c.uid,c.cid as cid,c.name as name,c.type as type,c.state as state,people,gold,morale,complaint,food,wood,rock,iron,
					CONCAT(c.cid%1000,',',floor(c.cid/1000)) as position 
					from sys_city c,mem_city_resource r,sys_user u where u.passport='$passport' and c.cid=r.cid and u.uid=c.uid";
		}
		else {
			if(!empty($name)){
				$sql = "select c.uid,c.cid as cid,c.name as name,c.type as type,c.state as state,people,gold,morale,complaint,food,wood,rock,iron,
						CONCAT(c.cid%1000,',',floor(c.cid/1000)) as position 
						from sys_city c,mem_city_resource r,sys_user u where u.name='$name' and c.cid=r.cid and u.uid=c.uid";
			}
		}
	}
	$ret = sql_fetch_rows($sql);
	$sql_error = mysql_error();
	if(empty($ret)||!empty($sql_error))
		$ret = 'no data';
?>