<?php
/**
 * @模块：查询查看 -- 当前武将的装备信息 
 * @功能：查询当前武将拥有的装备情况信息
 * @参数：int hid 武将id
 * @返回：
 *    array(
 *      array(
 *      spart:装备部件位置
 *      name:装备的名称
 *      type:获得的方式
 *      *：装备属性
 *      )
 *    )
 */
	if (!defined("MANAGE_INTERFACE")) exit;	
	if (!isset($hid))exit("param_not_exist");
	$ret = sql_fetch_rows("select ca.*,ch.uid,ha.spart,ha.armorid,ua.hp as hp from sys_hero_armor as ha,cfg_armor as ca,sys_city_hero as ch,sys_user_armor as ua where ca.id = ha.armorid and ch.hid = ha.hid and ua.hid = ha.hid and ha.sid=ua.sid and ua.armorid = ha.armorid and ha.hid = $hid");
	foreach($ret as &$r)
	{
        $uid = $r['uid'];
        $armorid = $r['armorid'];
        $log = sql_fetch_one("select t.name from log_armor as l,log_armor_type as t where l.uid = $uid and l.armorid=$armorid and l.type = t.id");
        $r['get_type'] = $log['name'];
	}
	

?>