<?php

/*
 * 
 * 家园平台接口数据:::信件相关操作
 * 
 */

require_once 'Verify.php';
require_once 'Data.php';
define("MAX_MAIL_COUNT",100);

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
    	throw new Exception($GLOBALS['_checkMailFull']['inbox_full']);
    }
    $sendcount=sql_fetch_one_cell("select count(*) from `sys_mail_box` where `fromuid`='$uid' and `sendstate`=0");
    if($sendcount>MAX_MAIL_COUNT)
    {
    	throw new Exception($GLOBALS['_checkMailFull']['outbox_full']);
    }
}

//回复用户游戏信件
function ReplyGameMail()
{
	global $uid;
	/*$uid=100;
	$_POST["Receiver"]="d";
	$_POST["Sender"]="c";
	$_POST["Content"]="test";
	$_POST["MailType"]=1;
	*/
	$GLOBALS['_checkMailFull']['inbox_full'] = 101;
	$GLOBALS['_checkMailFull']['outbox_full'] = 102;
	$GLOBALS['_sendPersonMail']['no_name'] = 103;
	$GLOBALS['_sendPersonMail']['untitled'] = "无标题";
	$GLOBALS['_sendPersonMail']['enemy'] = 104;
	$GLOBALS['_sendPersonMail']['content_illegal'] = 105;
	$GLOBALS['_sendPersonMail']['cant_find_addressee'] = 106;
	if($uid==-1||!isset($_POST["Receiver"])||!isset($_POST["Sender"])||!isset($_POST["Content"])||!isset($_POST["MailType"])){
		echo 0;
		return;
	}
    if ($_POST["MailType"] == 0) {
        $title = ($_POST["Subject"] !== "") ? addslashes($_POST["Subject"]) : $GLOBALS['sendPersonMail']['untitled'];
        $content = addslashes($_POST["Content"]);
        try {
            sql_query("insert into sys_mail_sys_content (content,posttime) values ('$content',unix_timestamp())");
            $mid = sql_fetch_one_cell("select last_insert_id()");
            sql_query("insert into sys_mail_sys_box (uid,contentid,title,`read`,posttime) values ('$uid','$mid','$title','0',unix_timestamp())");
            sql_query("insert into sys_alarm (uid,mail) values ('$uid',1) on duplicate key update mail=1");
            echo 1;
        } catch (Exception $e) {
            echo 0;
        }
    }else{
	//$fromname=$_POST["Sender"];
	$sender=addslashes($_POST["Sender"]);
	$receiver=addslashes($_POST["Receiver"]);
	
	$info=sql_fetch_one("select uid,name from sys_user where passport='$sender'");
	if(empty($info)){
		echo 0;
		return;
	}
	
	$senderuid=$info["uid"];
	$fromname=$info["name"];
	
	$info = sql_fetch_one("select uid,name from sys_user where passport='$receiver'");
	if(empty($info)){
		echo 0;
		return;
	}
	$touid=$info["uid"];
	$toname=$info["name"];
	
	define("MAX_MAIL_COUNT",100);
	$Fault_Value=0;
	/*$GLOBALS['_checkMailFull']['inbox_full'] = "收件箱已满，请删除多余信件后再发送。";
	$GLOBALS['_checkMailFull']['outbox_full'] = "发件箱已满，请删除多余信件后再发送。";
	$GLOBALS['_sendPersonMail']['no_name'] = "你要先填写君主名并建立城市才能发信。";
	$GLOBALS['_sendPersonMail']['untitled'] = "无标题";
	$GLOBALS['_sendPersonMail']['enemy'] = "你在对方仇人名单中，无法发信";
	$GLOBALS['_sendPersonMail']['content_illegal'] = "信件内容包含非法字符，不能发送";
	$GLOBALS['_sendPersonMail']['cant_find_addressee'] = "找不到收信人［%s］！";
	*/

	
	$title=($_POST["Subject"]!=="")?addslashes($_POST["Subject"]):$GLOBALS['_sendPersonMail']['untitled'];
	//$toname = addslashes($_POST["Receiver"]);
	$content = addslashes($_POST["Content"]);
	
	try{
		$userstate = sql_fetch_one_cell("select state from sys_user where uid=$senderuid");
		checkMailFull($senderuid);
		if(isset($userstate) && $userstate == 3) {
			throw new Exception($GLOBALS['_sendPersonMail']['no_name']);
		}	
		
		//$fromname = sql_fetch_one_cell("select name from sys_user where `uid`='$senderuid'");
	   // $fromname=addslashes($fromname);
	    //$touid = sql_fetch_one_cell("select uid from sys_user where `name`='$toname'");
	    
		if (!empty($touid))
	    {
	    	$isEnemy= sql_fetch_one_cell("select id from sys_user_relation where `uid`='$touid' and tuid='$senderuid' and type=1" );
	    	if($isEnemy){
	    		throw new Exception($GLOBALS['_sendPersonMail']['enemy']);
	    	}
	  
	        $banedcontent=sql_fetch_rows("select * from cfg_baned_mail_content");
	    	foreach($banedcontent as &$banedstr)
	    	{
	    		$bcontent=$banedstr['content'];
				if(!(strpos($content,$bcontent)===false))
	    		{
	    			sql_query("insert into log_illegal_user (uid,name,count) values ($senderuid,'$fromname',1) on duplicate key update count=count+1");
	    			throw new Exception($GLOBALS['_sendPersonMail']['content_illegal']);
	    		}
	    	}
	        $title = addslashes($title);
		    $content = addslashes($content);
		    $mid = sql_insert("insert into sys_mail_content (`content`,`posttime`) values ('$content',unix_timestamp())");
		    
		    if($_POST["MailType"]==2)
		    	$isSendState=1;
		    else
		    	$isSendState=0;
		    sql_insert("insert into sys_mail_box (`uid`,`name`,`fromuid`,`fromname`,`contentid`,`title`,`read`,`recvstate`,`sendstate`,`posttime`) values ('$touid','$toname','$senderuid','$fromname','$mid','$title','0','0','$isSendState',unix_timestamp())");
		    sql_query("insert into sys_alarm (`uid`,`mail`) values ('$touid',1) on duplicate key update `mail`=1");
	    }
	    else
	    {
	    	//$msg = sprintf($GLOBALS['_sendPersonMail']['cant_find_addressee'],$toname);
	    	throw new Exception($GLOBALS['_sendPersonMail']['cant_find_addressee']);
	    }
	    echo 1;    
	}catch(Exception $e){
		//echo 0;
		echo $e->getMessage();
	}
    }
}

//更新用户游戏信件阅读状态
function ReadGameMail()
{
	global $uid;
	if($uid==-1||!isset($_GET["mailID"])||!isset($_GET["mailType"])){
		echo 0;
		return;
	}
	
	$mailID=intval($_GET["mailID"]);
	$mailType=intval($_GET["mailType"]);
	
	$isSuccess=0;
	if($mailType==1){
		$isSuccess=sql_query("update sys_mail_box set `read`=1 where uid='$uid' and mid='$mailID' and `read`=0");
	}else{
		$isSuccess=sql_query("update sys_mail_sys_box set `read`=1 where uid='$uid' and mid='$mailID' and `read`=0");
	}
	checkUnread($uid);
	
	echo $isSuccess;
}

//获取用户游戏信件列表
function GetMailByPageIndex()
{
	global $uid;	
	if($uid==-1||!isset($_GET["pageIndex"])||!isset($_GET["mailType"])){
		echo 0;
		return;
	}
	global $MailPageCount;
	global $SysMail_FromName;
	
	$page=intval($_GET["pageIndex"]);
	$mailType=intval($_GET["mailType"]);
	
	$ret=array();
	if($mailType==2){
		$mailCount=sql_fetch_one_cell("select count(*) from sys_mail_box where `fromuid`='$uid' and `sendstate`=0");
		$pageCount=ceil($mailCount/$MailPageCount);
		if($page>=$pageCount)
		{
			$page=$pageCount-1;
		}
		if($mailCount<=0)
		{
			$page=0;
			$pageCount=0;
		}
		$pagestart = $page * $MailPageCount;
		$outBoxMails=sql_fetch_rows("select a.`mid` as MailID,`fromname` as Sender,`name` as Receiver,(select passport from sys_user where uid=a.fromuid) as SenderPassport,(select passport from sys_user where uid=a.uid) as ReceiverPassport,from_unixtime(a.posttime) as Time,`read` as IsRead,`title` as Subject,`content` as Content,'$mailType' as MailType,'$pageCount' as PageCount from sys_mail_box a,sys_mail_content b where a.contentid=b.mid and `fromuid`='$uid' and `sendstate`=0 order by a.`posttime` desc limit $pagestart,".$MailPageCount);
		$ret = $outBoxMails;
	}else if($mailType==1){
		$mailCount=sql_fetch_one_cell("select count(*) from sys_mail_box where `uid`='$uid' and `recvstate`=0");
		$pageCount=ceil($mailCount/$MailPageCount);
		if($page>=$pageCount)
		{
			$page=$pageCount-1;
		}
		if($mailCount<=0)
		{
			$page=0;
			$pageCount=0;
		}
		$pagestart = $page * $MailPageCount;
		$inBoxMails=sql_fetch_rows("select a.`mid` as MailID,`fromname` as Sender,`name` as Receiver,(select passport from sys_user where uid=a.fromuid) as SenderPassport,(select passport from sys_user where uid=a.uid) as ReceiverPassport,from_unixtime(a.posttime) as Time,`read` as IsRead,`title` as Subject,`content` as Content,'$mailType' as MailType,'$pageCount' as PageCount from sys_mail_box a,sys_mail_content b where a.contentid=b.mid and `uid`='$uid' and `recvstate`=0 order by a.`posttime` desc limit $pagestart,".$MailPageCount);
		$ret = $inBoxMails;
	}else{
		$mailCount=sql_fetch_one_cell("select count(*) from sys_mail_sys_box where `uid`='$uid'");
		$pageCount=ceil($mailCount/$MailPageCount);
		if($page>=$pageCount)
		{
			$page=$pageCount-1;
		}
		if($mailCount<=0)
		{
			$page=0;
			$pageCount=0;
		}
		$pagestart = $page * $MailPageCount;
		$sysBoxMails=sql_fetch_rows("select a.`mid` as MailID,'$SysMail_FromName' as Sender,from_unixtime(a.posttime) as Time,`read` as IsRead,`title` as Subject,`content` as Content,'$mailType' as MailType,'$pageCount' as PageCount from sys_mail_sys_box a,sys_mail_sys_content b where a.contentid=b.mid and `uid`='$uid' order by a.`posttime` desc limit $pagestart,".$MailPageCount);
		$ret = $sysBoxMails;
	}
	
	if(count($ret)==0){
		echo "";
		return;
	}
	echo json_encode($ret);
}

//判断用户是否有新的游戏信件
function GetMailAlarm()
{
	global $uid;
	if($uid==-1){
		echo 0;
		return;
	}
	echo sql_fetch_one_cell("select mail from sys_alarm where uid=$uid")?1:0;
}

//删除用户游戏信件
function DeleteGameMail()
{
	global $uid;
	if($uid==-1||!isset($_GET["mailID"])||!isset($_GET["mailType"])){
		echo 0;
		return;
	}
	
	$mailID=intval($_GET["mailID"]);
	$mailType=intval($_GET["mailType"]);
	
	$isSuccess=0;
	if($mailType==2){
		$isSuccess=sql_query("update sys_mail_box set `sendstate`=1 where `fromuid`='$uid' and `mid` = '$mailID'");
	}else if($mailType==1){
		$isSuccess=sql_query("update sys_mail_box set `recvstate`=1 where `uid`='$uid' and `mid` = '$mailID'");
	}else{
		$isSuccess=sql_query("delete from sys_mail_sys_box where `uid`='$uid' and `mid` = '$mailID'");
	}
	checkUnread($uid);
	
	echo $isSuccess;
}

function error(){echo "error";}
$func=$_GET["func"]?$_GET["func"]:"error";
$func();


?>