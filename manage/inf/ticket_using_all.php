<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 查询礼券
 * @功能 通过礼券号码精确查询礼券使用情况
 * @参数 $ticket_no 礼券号码
 * @返回 没有礼券 'no ticket'
 * 		 没有使用 'ticket no used'
 * 		 使用了的具体礼券信息
 * 		 array(
 * 		 'uid'=>'0：没有使用；非0，使用者id',
 * 		 'time'=>'使用时间',
 * 		 'content'=>'礼券内容',
 * 		 'ticket_name'=>'礼券名称',
 * 		 'binduid'=>'绑定id',
 * 		 'username'=>'玩家名',
 * 		 'passport'=>'通行证',
 * 		 )
 * 
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($ticket_no))exit("param_not_exist");
	
//	$ticket = sql_fetch_one("select * from sys_ticket where code = '$ticket_no'");
//	if(empty($ticket))
//	{
//		$ret[] = 'no ticket';
//	}
//	else{
//		$ticket = sql_fetch_one("select t.uid as uid,from_unixtime(t.time) as time,c.content as content,c.name as ticket_name,t.binduid as binduid,u.name as username,u.passport as passport from sys_ticket t,sys_ticket_content c,sys_user u where t.contentid = c.id and t.uid = u.uid and t.code = '$ticket_no'");
//		if(empty($ticket))
//		{
//			$ret[] = 'ticket no used';
//			$ticket = sql_fetch_one("select t.uid as uid,from_unixtime(t.time) as time,c.content as content,c.name as ticket_name,t.binduid as binduid from sys_ticket t,sys_ticket_content c where t.contentid = c.id  and t.code = '$ticket_no'");
//			$ret[] = $ticket;
//		}
//		else
//		{
//			$ret[] = 'ticket used';
//			$ret[] = $ticket;
//		}
//	}
	$codes = explode("\n",$ticket_no);
	foreach ($codes as $code){
	$code = trim($code);
	$sql ="select * from sys_ticket where code='$code'";
	$row = sql_fetch_one($sql);
	if(empty($row)){
		$ticket = 'no ticket';
	}
	else {
		$sql = "select u.passport,c.name,t.code,t.uid as uid,from_unixtime(t.time) as time,c.content as content,c.name as ticket_name,t.binduid as binduid,u.name as username,u.passport as passport from sys_ticket t,sys_ticket_content c,sys_user u where t.contentid = c.id and t.uid = u.uid and t.code = '$code' order by t.time desc";
		$ticket = sql_fetch_one($sql);
		if(empty($ticket)){
			$ret[] = 'ticket no used';
			$sql = "select c.name,t.code,t.uid as uid,from_unixtime(t.time) as time,c.content as content,c.name as ticket_name,t.binduid as binduid from sys_ticket t,sys_ticket_content c where t.contentid = c.id  and t.code = '$code'";
			$ticket = sql_fetch_one($sql);
			
		}else{
			$ret[] = 'ticket used';
		}
	}
	$ret[]=$ticket;
	}
	
?>