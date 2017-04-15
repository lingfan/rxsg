<?php
/**
 * @author 方鸿鹏
 * @method 获得用户战场勋绩消费
 * @param $uid 用户id
 * @return 
 * 		array{
 * 			0=>用户id
 * 			1=>君主名
 *  		2=>账号
 * 			3=>商品名
 * 			4=>数量
 * 			5=>价格
 * 			6=>类型
 * 			6=>记录时间
 * 		}
 */

if (!defined("MANAGE_INTERFACE")) exit;
if (!isset($uid))exit("param_not_exist");
$ret = sql_fetch_rows("select u.uid,u.name as uname,u.passport,s.name as gname,g.count,s.price,g.type,from_unixtime(time) as formattime
					   from sys_user u, log_goods g,cfg_shop s 
					   where g.gid=s.gid and u.uid=g.uid and g.uid='$uid' and g.gid between 31044 and 31059");
$sql_error = mysql_error();
if(empty($ret)||!empty($sql_error))$ret = 'no data';
?>