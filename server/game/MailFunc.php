<?php                      
require_once("./interface.php");
require_once("./utils.php");
require_once("utils/StringUtils.php");

define("Mail_PAGE_CPP",10);
define("MAX_MAIL_COUNT",100);

//得到收件箱消息列表
function getMail($uid)
{
	$param2=array();
	$param2[]=0;
	$unreadCount=sql_fetch_one_cell("select count(*) from sys_mail_box where `uid`='$uid' and `recvstate`=0 and `read`=0");
	if($unreadCount!=0)
	{
		$ret=getInboxMail($uid,$param2);
		$ret[]=0;
	}
	else
	{
    	$unreadCount=sql_fetch_one_cell("select count(*) from sys_mail_sys_box where `uid`='$uid' and `read`=0");
    	if($unreadCount!=0)
    	{
    		$ret=getSysMail($uid,$param2);
    		$ret[]=1;
    	}
    }
    if($unreadCount==0)
    {
    	sql_query("update `sys_alarm` set `mail`='0' where `uid`='$uid'");
    	$ret=getInboxMail($uid,$param2);
		$ret[]=0;
    }
    return $ret;
}
function getInboxMail($uid,$param)
{
    $page = array_shift($param);
    $mailCount=sql_fetch_one_cell("select count(*) from sys_mail_box where `uid`='$uid' and `recvstate`=0");
    $pageCount=ceil($mailCount/Mail_PAGE_CPP);
    if($page>=$pageCount)
    {
    	$page=$pageCount-1;
    }
    if($mailCount<=0)
    {
    	$page=0;
    	$pageCount=0;
    }
    $ret = array();
    $ret[]=$pageCount;
    $ret[]=$page;
    if ($mailCount>0)
    {
    	$pagestart = $page * Mail_PAGE_CPP;
    	$ret[]=sql_fetch_rows("select `mid`,`fromname`,`contentid`,`title`,`read`,`posttime` from sys_mail_box where `uid`='$uid' and `recvstate`=0 order by `posttime` desc limit $pagestart,".Mail_PAGE_CPP);
    }
    else
    {
    	checkUnread($uid);
    	$ret[]=array();
    }
	return $ret;
}
//得到发件箱消息列表
function getOutboxMail($uid,$param)
{
    $page = array_shift($param);
    $mailCount=sql_fetch_one_cell("select count(*) from sys_mail_box where `fromuid`='$uid' and `sendstate`=0");
    $pageCount=ceil($mailCount/Mail_PAGE_CPP);
    if($page>=$pageCount)
    {
    	$page=$pageCount-1;
    }
    if($mailCount<=0)
    {
    	$page=0;
    	$pageCount=0;
    }
    $ret = array();
    $ret[]=$pageCount;
    $ret[]=$page;
    if($mailCount>0)
    {
    	$pagestart = $page *  Mail_PAGE_CPP;
    	$ret[]=sql_fetch_rows("select `mid`,`name`,`contentid`,`title`,`read`,`posttime` from sys_mail_box where `fromuid`='$uid' and `sendstate`=0 order by `posttime` desc limit $pagestart,".Mail_PAGE_CPP);
    }
    else 
    {
    	$ret[]=array();
    }
    return $ret;
}
//获取系统信箱列表
function getSysMail($uid,$param)
{
    $page = array_shift($param);
    $mailCount=sql_fetch_one_cell("select count(*) from sys_mail_sys_box where `uid`='$uid'");
    $pageCount=ceil($mailCount/Mail_PAGE_CPP);
    if($page>=$pageCount)
    {
    	$page=$pageCount-1;
    }
    if($mailCount<=0)
    {
    	$page=0;
    	$pageCount=0;
    }
    $ret = array();
    $ret[]=$pageCount;
    $ret[]=$page;
    if ($mailCount>0)
    {
    	$pagestart = $page * Mail_PAGE_CPP;
    	$ret[]=sql_fetch_rows("select `mid`,`contentid`,`title`,'".$GLOBALS['getSysMail']['fromname']."' as fromname,`read`,`posttime` from sys_mail_sys_box where `uid`='$uid' order by `posttime` desc limit $pagestart,".Mail_PAGE_CPP);
    }
    else
    {
    	checkUnread($uid);
    	$ret[]=array();
    }
	return $ret;
}
//删除收件箱
function deleteInboxMail($uid,$param)
{
    $mids = array_shift($param);
    $page= array_shift($param);
    $mids = implode(",",$mids);
    
    if(!empty($mids))
    {
    	sql_query("update sys_mail_box set `recvstate`=1 where `uid`='$uid' and `mid` in ($mids)");
    	checkUnread($uid);
    }
    $param2=array();
    $param2[]=$page;
    return getInboxMail($uid,$param2);
}
//删除发件箱
function deleteOutboxMail($uid,$param)
{         
    $mids = array_shift($param);
    $page= array_shift($param);
    $mids = implode(",",$mids);
    $mids = addslashes($mids);
    if (!empty($mids)) {
    	sql_query("update sys_mail_box set `sendstate`=1 where `fromuid`='$uid' and `mid` in ($mids)");
    }
    $param2=array();
    $param2[]=$page;
    return getOutboxMail($uid,$param2);
}

//删除系统信
function deleteSysMail($uid,$param)
{
    $mids = array_shift($param);
    $page= array_shift($param);
    $mids = implode(",",$mids);
    $mids = addslashes($mids);
    if(!empty($mids))
    {
    	sql_query("delete from sys_mail_sys_box where `uid`='$uid' and `mid` in ($mids)");
    	checkUnread($uid);
    }
    $param2=array();
    $param2[]=$page;
    return getSysMail($uid,$param2);
}
//得到收件箱消息内容
function readInboxMail($uid,$param)
{
    $mid = intval(array_shift($param));
    $mail = sql_fetch_one("select i.`mid`,i.`name`,i.`fromname`, i.`title`,i.`posttime`,c.`content` from sys_mail_box i left join sys_mail_content c on c.mid = i.contentid where i.mid='$mid' and i.`uid`='$uid'");
    if (!empty($mail))
    {
        sql_query("update sys_mail_box set `read`=1 where `mid`='$mid'");
        $unreadCount=sql_fetch_one_cell("select count(*) from sys_mail_box where `uid`='$uid' and `recvstate`=0 and `read`=0");
        checkUnread($uid);
    }
    else
    {
    	throw new Exception($GLOBALS['readInboxMail']['mail_lost']);
    }
    $ret=array();
	$ret[]=$mail;
	return $ret;
}
//得到发件箱消息内容
function readOutboxMail($uid,$param)
{
    $mid = intval(array_shift($param));
	$mail=sql_fetch_one("select i.`mid`,i.`name`,i.`fromname`, i.`title`,i.`posttime`,c.`content` from sys_mail_box i left join sys_mail_content c on c.mid = i.contentid where i.mid='$mid' and i.`fromuid`='$uid'");
	if (empty($mail))
    {
    	throw new Exception($GLOBALS['readOutboxMail']['mail_lost']);
    }
    $ret=array();
	$ret[]=$mail;
	return $ret;
}

//得到系统箱消息内容
function readSysMail($uid,$param)
{
    $mid = intval(array_shift($param));
    $mail = sql_fetch_one("select u.`name`,'".$GLOBALS['getSysMail']['fromname']."' as fromname,i.`uid`,i.`title`,i.`read`,i.`posttime`,c.`content` from sys_mail_sys_box i left join sys_mail_sys_content c on c.mid = i.contentid left join sys_user u on i.`uid`=u.`uid` where i.mid='$mid' and i.`uid`='$uid'");
    if (!empty($mail))
    {
    	//看过激活邮箱邮件的用户数统计
    	$mailstate = sql_fetch_one("select `title`, `read` from sys_mail_sys_box where `mid`='$mid'");
    	if($mailstate['title']===$GLOBALS['activate_mail_box']['title'] && $mailstate['read']==0){
    		sql_query("insert into log_daily_action_count (aid, `date`, `count`) values (10002, unix_timestamp(curdate()), 1) on duplicate key update count=count+1");
    	}
    	
        sql_query("update sys_mail_sys_box set `read`=1 where `mid`='$mid'");
        $unreadCount=sql_fetch_one_cell("select count(*) from sys_mail_box where `uid`='$uid' and `recvstate`=0 and `read`=0");
        checkUnread($uid);
    }
    else
    {
    	throw new Exception($GLOBALS['readSysMail']['mail_lost']);
    }
    completeTaskWithTaskid($uid,290);
    $ret=array();
	$ret[]=$mail;
	return $ret;
}

function checkUnread($uid)
{
	$unreadCount=sql_fetch_one_cell("select count(*) from sys_mail_box where `uid`='$uid' and `recvstate`=0 and `read`=0");
    if($unreadCount==0)
    {
    	$unreadCount=sql_fetch_one_cell("select count(*) from sys_mail_sys_box where `uid`='$uid' and `read`=0");
    	if($unreadCount==0)
    	{
    		sql_query("update `sys_alarm` set `mail`='0' where `uid`='$uid'");
    	}
    }
}

function checkMailFull($uid)
{
	$recvcount=sql_fetch_one_cell("select count(*) from `sys_mail_box` where `uid`='$uid' and `recvstate`=0");
    if($recvcount>MAX_MAIL_COUNT)
    {
    	throw new Exception($GLOBALS['checkMailFull']['inbox_full']);
    }
    $sendcount=sql_fetch_one_cell("select count(*) from `sys_mail_box` where `fromuid`='$uid' and `sendstate`=0");
    if($sendcount>MAX_MAIL_COUNT)
    {
    	throw new Exception($GLOBALS['checkMailFull']['outbox_full']);
    }
}

//发送个人信件
function sendPersonMail($uid,$param)
{
	// check if the quantity of mails one day
//	$row = sql_fetch_rows("select * from cfg_mail_limit");
//	//$rangeTime = $row['range'] * 24 ;
//	$mail_num = sql_fetch_one_cell("select count(*) from sys_mail_box where fromuid='$uid' and posttime >=unix_timestamp(curdate()) and type=1");
//	$time = sql_fetch_one("select unix_timestamp() as nowtime,max(posttime) as lasttime  from sys_mail_box where fromuid='$uid' and type=1");
//	$nowtime = $time['nowtime'];
//	$time = $nowtime - $time['lasttime'];
//	for ($i = 0; $i < count($row); $i++) {
//		if ($row[$i][mail_lower] == null && $mail_num > $row[$i][mail_cap]) {
//			$limitid = $row[$i][id];
//			if(!sql_check("select * from log_mail_limit where uid=$uid and limitid=$limitid and logtime>=unix_timestamp(curdate())")) {
//				$prestige = sql_fetch_one_cell("select prestige from sys_user where uid=$uid");
//				sql_insert("insert log_mail_limit(uid, limitid, prestige, logtime) values($uid, $limitid, $prestige, $nowtime)");
//				//throw new Exception(sprintf($GLOBALS['banned']['forbid_mail'] . "[[]]"));
//				// TODO send sys mail
//			}
//       		throw new Exception(sprintf($GLOBALS['banned']['forbid_mail']));
//		}
//    	if ($mail_num > $row[$i][mail_cap] && $mail_num <= $row[$i][mail_lower] && $time < $row[$i][time]) {
//    		$limitid = $row[$i][id];
//			if(!sql_check("select * from log_mail_limit where uid='$uid' and limitid='$limitid' and logtime>=unix_timestamp(curdate())")) {
//				$prestige = sql_fetch_one_cell("select prestige from sys_user where uid=$uid");
//				sql_insert("insert log_mail_limit(uid, limitid, prestige, logtime) values($uid, $limitid, $prestige, $nowtime)");
//			}
//
//        	throw new Exception(sprintf($GLOBALS['banned']['too_frequent_mail'], $row[$i][time]));
//    	}
//	}
	
	$userstate = sql_fetch_one_cell("select state from sys_user where uid=$uid");
	if(!empty($userstate) && $userstate == 3) {
		throw new Exception($GLOBALS['sendPersonMail']['no_name']);
	}
	$fromname=array_shift($param);
    $toname = array_shift($param);
    $title = array_shift($param);
    $content = array_shift($param);
    $title = addslashes($title); 
    
    // joyport will check the mail contents the bad players sent
	$row = sql_fetch_rows("select * from cfg_mail_limit");
	//$rangeTime = $row['range'] * 24 ;
	$mail_num = sql_fetch_one_cell("select count(*) from sys_mail_box where fromuid='$uid' and posttime >=unix_timestamp(curdate()) and type=1");
	$time = sql_fetch_one("select unix_timestamp() as nowtime,max(posttime) as lasttime  from sys_mail_box where fromuid='$uid' and type=1");
	$nowtime = $time['nowtime'];
	$time = $nowtime - $time['lasttime'];
	for ($i = 0; $i < count($row); $i++) {
		if ($row[$i][mail_lower] == null && $mail_num > $row[$i][mail_cap]) {
			$limitid = $row[$i][id];
			//if(!sql_check("select * from log_mail_limit where uid=$uid and limitid=$limitid and logtime>=unix_timestamp(curdate())")) {
				$prestige = sql_fetch_one_cell("select prestige from sys_user where uid=$uid");
				$paid = sql_fetch_one_cell("select 1 from pay_log as log join sys_user as user on log.passport=user.passport where uid='$uid' and type=0 limit 1");
				if(!$paid)
					$paid = 0;
				sql_insert("insert log_mail_limit(uid, limitid, prestige, logtime, paid, content) values($uid, $limitid, $prestige, $nowtime, $paid, '$content')");
				//throw new Exception(sprintf($GLOBALS['banned']['forbid_mail'] . "[[]]"));
				// TODO send sys mail
			//}
       		throw new Exception(sprintf($GLOBALS['banned']['forbid_mail']));
		}
    	if ($mail_num > $row[$i][mail_cap] && $mail_num <= $row[$i][mail_lower] && $time < $row[$i][time]) {
    		$limitid = $row[$i][id];
			//if(!sql_check("select * from log_mail_limit where uid='$uid' and limitid='$limitid' and logtime>=unix_timestamp(curdate())")) {
				$prestige = sql_fetch_one_cell("select prestige from sys_user where uid=$uid");
				$paid = sql_fetch_one_cell("select 1 from pay_log as log join sys_user as user on log.passport=user.passport where uid='$uid' and type=0 limit 1");
				if(!$paid)
					$paid = 0;
				sql_insert("insert log_mail_limit(uid, limitid, prestige, logtime, paid, content) values($uid, $limitid, $prestige, $nowtime, $paid, '$content')");
			//}

        	throw new Exception(sprintf($GLOBALS['banned']['too_frequent_mail'], $row[$i][time]));
    	}
	}
    
    //$titleToCheck = StringUtils::removeSpace($title);
    $titleToCheck = StringUtils::removeChars($title, $GLOBALS['word']['special']);
	$content = addslashes($content);	 
	//$contentToCheck = StringUtils::removeSpace($content);
	$contentToCheck = StringUtils::removeChars($content, $GLOBALS['word']['special']);
    if(empty($title))
    {
    	$title = $GLOBALS['sendPersonMail']['untitled'];
    }
    checkMailFull($uid);

	if (sql_check("select * from cfg_baned_mail_content where instr('$titleToCheck',`content`)>0"))
	{
		throw new Exception($GLOBALS['banned']['word_illegal']);
	}
	
	$fromname = sql_fetch_one_cell("select name from sys_user where `uid`='$uid'");
    $fromname=addslashes($fromname);
    //$toname=addslashes($toname);
    $toname=trim($toname);
    $touid = sql_fetch_one_cell("select uid from sys_user where `name`='$toname' limit 1");
    if (!empty($toname)&& !empty($touid))
    {
    	$isEnemy= sql_fetch_one_cell("select id from sys_user_relation where `uid`='$touid' and tuid='$uid' and type=1" );
    	if($isEnemy){
    		throw new Exception($GLOBALS['sendPersonMail']['enemy']);
    	}
  
        $banedcontent=sql_fetch_rows("select * from cfg_baned_mail_content");
    	foreach($banedcontent as &$banedstr)
    	{
    		$bcontent=$banedstr['content'];
			if(!(strpos($contentToCheck,$bcontent)===false))
    		{
    			sql_query("insert into log_illegal_user (uid,name,count) values ($uid,'$fromname',1) on duplicate key update count=count+1");
    			throw new Exception($GLOBALS['sendPersonMail']['content_illegal']);
    		}
    	}
        //$title = addslashes($title);
	    //$content = addslashes($content);
	    $mid = sql_insert("insert into sys_mail_content (`content`,`posttime`) values ('$content',unix_timestamp())");
	    sql_insert("insert into sys_mail_box (`uid`,`name`,`fromuid`,`fromname`,`contentid`,`title`,`read`,`recvstate`,`sendstate`,`posttime`, `type`) values ('$touid','$toname','$uid','$fromname','$mid','$title','0','0','0',unix_timestamp(),'1')");
	    sql_query("insert into sys_alarm (`uid`,`mail`) values ('$touid',1) on duplicate key update `mail`=1");
	    if ($touid>1000 && $uid!=$touid) {//给自己发信与NPC发信不能完成任务
	    	completeTask($uid,80);
	    }
	    if($touid==710){
	    	completeTask($uid,3101107);
	    }
    }
    else
    {
    	$msg = sprintf($GLOBALS['sendPersonMail']['cant_find_addressee'],$toname);
    	throw new Exception($msg);
    }
    $ret=array();
    return $ret;
}
//联盟群发
function sendUnionMail($uid,$param)
{
	$userstate = sql_fetch_one_cell("select state from sys_user where uid=$uid");
	if(!empty($userstate) && $userstate == 3) {
		throw new Exception($GLOBALS['sendUnionMail']['no_name']);
	}
	$fromname=array_shift($param);
	$fromname = addslashes($fromname);
	$fromname = sql_fetch_one_cell("select name from sys_user where `uid`='$uid'");

    $unionid = intval(array_shift($param));
    $title = array_shift($param);
    $content = array_shift($param);
    $title = addslashes($title);
	$titleToCheck = StringUtils::removeChars($title, $GLOBALS['word']['special']);
	$content = addslashes($content);	 
	$contentToCheck = StringUtils::removeChars($content, $GLOBALS['word']['special']);	
    if(empty($title))
    {
    	$title=$GLOBALS['sendUnionMail']['untitled'];
    }
    $title = $GLOBALS['sendUnionMail']['union'].$title;
    
	if (sql_check("select * from cfg_baned_mail_content where instr('$titleToCheck',`content`)>0"))
	{
		throw new Exception($GLOBALS['banned']['word_illegal']);
	}	
	
	
    checkMailFull($uid);
    $union=sql_fetch_one("select union_id,union_pos from sys_user where uid='$uid'");
    $unionid=$union['union_id'];
    $unionpos=$union['union_pos'];
    $leader=sql_fetch_one_cell("select `leader` from `sys_union` where `id`='$unionid'");
    if($unionpos!=4&&$unionpos!=5&&(empty($leader)||$leader!=$uid))
    {
    	throw new Exception($GLOBALS['sendUnionMail']['not_champion']);
    }
    
    $horn=sql_fetch_one_cell("select `count` from `sys_goods` where `uid`='$uid' and `gid`='1'");
    if($horn<2)
    {
    	$goodsNeed=2-$horn;
    	throw new Exception ( "not_enough_goods1#$goodsNeed" );
    	throw new Exception($GLOBALS['sendUnionMail']['no_enough_acoustic']);
    }
    $banedcontent=sql_fetch_rows("select * from cfg_baned_mail_content");
	foreach($banedcontent as &$banedstr)
	{
		$bcontent=$banedstr['content'];
		if(!(strpos($contentToCheck,$bcontent)===false))
		{
			sql_query("insert into log_illegal_user (uid,name,count) values ($uid,'$fromname',1) on duplicate key update count=count+1");
			throw new Exception($GLOBALS['sendPersonMail']['content_illegal']);
		}
	}
    //$title = addslashes($title);
	//$content = addslashes($content);
    $fromname=addslashes($fromname);

	$mid = sql_insert("insert into sys_mail_content (`content`,`posttime`) values ('$content',unix_timestamp())");
	sql_insert("insert into sys_mail_box (`uid`,`name`,`fromuid`,`fromname`,`contentid`,`title`,`read`,`recvstate`,`sendstate`,`posttime`, `type`) (select `uid`,`name`,'$uid','$fromname','$mid','$title','0','0','0',unix_timestamp(),'2' from `sys_user` where `union_id`='$unionid')");
	sql_query("insert into sys_alarm (`uid`,`mail`) (select `uid`,1 from `sys_user` where `union_id`='$unionid') on duplicate key update `mail`=1");

	addGoods($uid, 1, -2, 0);
    completeTask($uid,80);
    
    $ret=array();
    return $ret;
}


//发个人系统信
function sendSysMailToUser($uid,$title,$content)
{
	$title = addslashes($title);
	$content = addslashes($content);		
	sql_query("insert into sys_mail_sys_content (content,posttime) values ('$content',unix_timestamp())");			
	$mid=sql_fetch_one_cell("select last_insert_id()");
	sql_query("insert into sys_mail_sys_box (uid,contentid,title,`read`,posttime) values ('$uid','$mid','$title','0',unix_timestamp())");
	sql_query("insert into sys_alarm (uid,mail) values ('$uid',1) on duplicate key update mail=1");
}
//发联盟信
function sendSysMailByUnion($union_id,$title,$content)
{
	$title = addslashes($title);
	$content = addslashes($content);		
	
	if (sql_check("select * from cfg_baned_mail_content where instr('$title',`content`)>0"))
	{
		throw new Exception($GLOBALS['banned']['word_illegal']);
	}
	
	sql_query("insert into sys_mail_sys_content (content,posttime) values ('$content',unix_timestamp())");			
	$mid=sql_fetch_one_cell("select last_insert_id()");
	sql_query("insert into sys_mail_sys_box (uid,contentid,title,`read`,posttime) select uid,'$mid','$title','0',unix_timestamp() from sys_user where union_id='$union_id'");
	sql_query("insert into sys_alarm (uid,mail) select uid,1 from sys_user where union_id='$union_id' on duplicate key update mail=1");
}
function readAllMail($uid)
{
	sql_query("update sys_mail_sys_box set `read`='1' where uid='$uid'");
	return getMail($uid);
}
?>