<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 查询联盟
 * @功能 通过联盟id查询联盟信息
 * @参数 $id 联盟id
 * @返回 
 * array(
 *      'id'=>'联盟id'，
 *      'union_name'=>'联盟名称',
 *      'leader_name'=>'盟主',
 *      'member'=>'成员数量',
 *      'rank'=>'排名',
 *      'fprestige'=>'联盟声望',
 * )
 * 如果为空 返回 'no data'
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($id))exit("param_not_exist");
	$ret = sql_fetch_one("select s.id as id,s.name as union_name,u.name as leader_name,s.member,s.rank,s.prestige,from_unixtime(s.createtime) as createtime from sys_union s left join sys_user u on (s.leader=u.uid) where s.id='$id'");
	if(empty($ret))$ret = 'no data';

?>