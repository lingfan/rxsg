<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 查询用户
 * @功能 通过玩家id查询装备信息
 * @参数 $uid 玩家id
 * @返回 
 * array(
 * '0'=>array(
 *      'uid'=>'玩家id'，
 *      'uname'=>'君主名',
 *      'passport'=>'账号',
 *      'gname'=>'装备名',
 *      'count'=>'装备数量',
 *      'formattime'=>'记录时间',
 *      'type'=>'操作类型数'
 *      ),
 * '1'=>array(
 *      'uid'=>'玩家id'，
 *      'uname'=>'君主名',
 *      'passport'=>'账号',
 *      'gname'=>'装备名',
 *      'count'=>'装备数量',
 *      'formattime'=>'记录时间',
 *      'type'=>'操作类型数'
 *      ),
 * .......
 * )
 * 如果为空 返回 'no data'
 */
if (!defined("MANAGE_INTERFACE"))
	exit();
if (!isset($uid))
	exit("param_not_exist");

if (empty($result)){
	
}
$ret = sql_fetch_rows("select * from ((select a.`count`,from_unixtime(time) AS formattime,a.`type` AS `type`,c.name AS gname,c.attribute,
	c.`type` as color_type,c.part,c.value, 1 as table_type from log_armor a,sys_user u,cfg_armor c where u.uid=$uid and a.armorid=c.id and u.uid=a.uid) 
	union (select l.`count`,from_unixtime(l.time) AS formattime,l.`type` AS `type`,c.name AS gname,c.attribute,c.`type` as color_type,
	c.part,c.value, 0 as table_type from log_goods l,cfg_armor c  where l.uid=$uid and l.gid=c.id and ((l.type=9 and l.gid>=20000 and l.gid<=20041)or(l.type=11 
	and ((l.gid between 31060 and 31073)or(l.gid between 31030 and 31043)or(l.gid between 31001 and 31014)or(l.gid between 31044 and 31059)
	or(l.gid between 51001 and 51032)))) order by l.time )
	) as a order by formattime desc");
if (empty($ret))
	$ret = 'no data';

?>