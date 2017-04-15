<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 查询联盟
 * @功能 通过联盟名精确查询联盟信息
 * @参数 $name 联盟名
 * @返回 
 * array(
 * '0'=>array(
 *      'id'=>'联盟id'，
 *      'union_name'=>'联盟名称',
 *      'leader_name'=>'盟主',
 *      'member'=>'成员数量',
 *      'rank'=>'排名',
 *      'fprestige'=>'联盟声望',
 * 		)
 * )
 * 如果为空 返回 'no data'
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($name))exit("param_not_exist");
	//$ret = sql_fetch_rows("select s.id as id,s.name as union_name,u.name as leader_name,s.member,s.rank,s.prestige,from_unixtime(s.createtime) as createtime from sys_union s left join sys_user u on (s.leader=u.uid) where s.name='$name'");
	//$ret = sql_fetch_rows("select s.id as id,s.name as union_name,u.name as leader_name,s.member,s.rank,s.prestige,from_unixtime(s.createtime) as createtime,count(so.uid) as `count` from sys_union s left join sys_user u on (s.leader=u.uid) left join sys_online as so on s.leader=so.uid  and unix_timestamp() - so.lastupdate < 30 where s.name='$name' group by s.id");
	$ret = sql_fetch_rows("select sum(lm.count) as scount,s.id as id,s.name as union_name,(select su.name from sys_user su,sys_union son where son.leader=su.uid and son.name='$name') as leader_name,s.member,s.rank,s.prestige,from_unixtime(s.createtime) as createtime,count(distinct(so.uid)) as `count` from sys_union s 
	left join sys_user u on (s.id=u.union_id) 
	left join sys_online as so on u.uid=so.uid  and unix_timestamp() - so.lastupdate < 30
	left join log_money as lm on u.uid=lm.uid and type=0
	where s.name='$name' group by s.id");
	if(empty($ret))$ret = 'no data';
?>