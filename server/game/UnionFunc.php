<?php                      
require_once("./interface.php");
require_once("./utils.php");
require_once("./MailFunc.php");
require_once("./WorldFunc.php");

define("APPLY_PAGE_CPP",10);
define("EVENT_PAGE_CPP",10);
define("UNION_REPORT_PAGE_CPP",10);
define("EVT_ADD_UNION",0);
define("EVT_QUIT_UNION",1);
define("EVT_KICK_MENBER",2);
define("EVT_CHANGE_LEADER",3);
define("EVT_CHANGE_NAME",4);
define("EVT_RELATION_FRIEND",5);
define("EVT_RELATION_NEUTRAL",6);
define("EVT_RELATION_ENEMY",7);
define("EVT_WAR",8);
define("EVT_PROVICY",9);
define("EVT_DEMISSION",10);
define("EVT_ACHIVEMENT",11);

function getHongLuInfo($uid,$cid)
{
    $honglu = sql_fetch_one("select * from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_HONGLU." order by level desc limit 1");
    if (empty($honglu))
    {   
        throw new Exception($GLOBALS['getHongLuInfo']['no_HongLu_built']); 
    }
    return doGetBuildingInfo($uid,$cid,$honglu['xy'],ID_BUILDING_HONGLU,$office['level']);
}

function getAllowUnionTroop($uid,$cid)
{
	$allow=sql_fetch_one_cell("select `allow` from sys_allow_union_troop where uid='$uid'");
	if($allow===false) $allow=0;
	
	return $allow;
}
function getAllowAntiPlunder($uid,$cid)
{
	$allow=sql_fetch_one_cell("select `anti_plunder` from sys_allow_union_troop where uid='$uid'");
	if($allow===false) $allow=1;
	
	return $allow;
}
function getAllowAntiInvade($uid,$cid)
{
	$allow=sql_fetch_one_cell("select `anti_invade` from sys_allow_union_troop where uid='$uid'");
	if($allow===false) $allow=1;
	
	return $allow;
}
function createUnion($uid,$param)
{
    $user = sql_fetch_one("select `name`,`union_id` from sys_user where uid='$uid'");
    $userunion=$user['union_id'];
    if ($userunion > 0) throw new Exception($GLOBALS['createUnion']['already_joined_other_union']);
    $unionname = trim(array_shift($param));
    if(empty($unionname))
    {
        throw new Exception($GLOBALS['createUnion']['union_name_notNull']);
    }
    else if (mb_strlen($unionname,"utf-8") > 8)
    {
        throw new Exception($GLOBALS['createUnion']['union_name_tooLong']);
    }
    else if ((!(strpos($unionname,'\'')==false))||(!(strpos($unionname,'\\')==false)) || (!(strpos($unionname,'13')==false)))
    {
    	throw new Exception($GLOBALS['createUnion']['has_ivalid_char']);
    }
    else if (sql_check("select * from cfg_baned_name where instr('$unionname',`name`)>0"))
    {
    	throw new Exception($GLOBALS['createUnion']['has_ivalid_char']);
    }
    $createunionlog=sql_fetch_one_cell("select unix_timestamp()-time from `sys_user_action_log` where uid='$uid' and action='createunion' limit 1");
    if(!empty($createunionlog)){
    	if($createunionlog<86400){
    		throw new Exception($GLOBALS['createUnion']['add_union_event_time_near']);
    	}
    }
    $unionname=addslashes($unionname);
    //需要联盟2级
    $cid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
    $honglulevel = sql_fetch_one_cell("select max(b.level) from sys_building b,sys_city c where b.bid='".ID_BUILDING_HONGLU."' and b.cid=c.cid and c.uid='$uid'");   
    if ($honglulevel < 2)
    {
        throw new Exception($GLOBALS['createUnion']['level_lessThen_2']);
    }
    
    $citygold = sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
    if ($citygold < 10000)
    {
        throw new Exception($GLOBALS['createUnion']['gold_not_enough']);
    }
    
    if (sql_check("select * from sys_union where name='$unionname'"))
    {
        throw new Exception($GLOBALS['createUnion']['use_another_name']);
    }
    sql_query("update mem_city_resource set gold=gold-10000 where cid='$cid'");
    
    $unionid = sql_insert("insert into sys_union (`name`,`leader`,`creator`,`createtime`,`intro`,`announcement`) values ('$unionname','$uid','$uid',unix_timestamp(),'','')");
    
    sql_query("update sys_user set union_id='$unionid',union_pos=5 where uid='$uid' ");
    sql_query("delete from sys_user_action_log where uid='$uid' and action='createunion'");
    $mark=$GLOBALS['actionlog']['createunion'];
    sql_query("insert into sys_user_action_log(uid,time,action,mark)values('$uid',unix_timestamp(),'createunion','$mark')");
    sql_query("insert into sys_user_donate(uid,unionid,donate) values('$uid','$unionid',10000)");
    sql_query("insert into sys_union_building(unionid,bid,level) select $unionid,bid,1 from cfg_union_building_upgrade group by bid");    
    notifyUnionChange($uid,$unionid,1);
                            
    completeTask($uid,67);                                                              
    updateUnionRank($unionid);
    
    //addUnionEvent($unionid,EVT_ADD_UNION,"$user[name] 创建联盟 $unionname ！");
    $evtMsg = sprintf($GLOBALS['createUnion']['add_union_event'],$user[name],$unionname);
    addUnionEvent($unionid,EVT_ADD_UNION,$evtMsg);
    finishAchivement($uid,13);
    $ret = array();
    $ret[] = $unionid;
    $ret[] = 0;
    $ret[] = $unionname;
    return $ret;
}
//返回自己发出的申请和向自己的邀请
function getUnionApplyInvite($uid,$param)
{
	$user=sql_fetch_one("select `union_id`,`union_pos` from `sys_user` where `uid`='$uid'");
    $ret=array();
    $unionid=$user['union_id'];
    $unionpos=$user['union_pos'];
    $ret[]=$unionid;
    $ret[]=$unionpos;
	if ($unionid>0)
	{
		$unionname=sql_fetch_one_cell("select `name` from `sys_union` where `id`='$unionid'");
		$ret[]=$unionname;
		$ret[]="［".$unionname.$GLOBALS['getUnionApplyInvite']['succ'];
	}
	else
	{
    	sql_query("update sys_alarm set `union` = 0 where uid=$uid");
		$name=sql_fetch_one_cell("select `name` from `sys_union_apply` where `uid`='$uid'");
		if(empty($name))
		{
			$ret[]="";
		}
		else
		{
			$ret[]=$name;
		}
	    $ret[]=sql_fetch_rows("select u.* from sys_union_invite i left join sys_union u on u.id=i.unionid where i.uid='$uid'");
    }
    return $ret;
}                

//申请加入联盟
function applyJoin($uid,$param)
{
	$honglulevel = sql_fetch_one_cell("select max(b.level) from sys_building b,sys_city c where b.bid='".ID_BUILDING_HONGLU."' and b.cid=c.cid and c.uid='$uid'");
    if (empty($honglulevel))
    {   
        throw new Exception($GLOBALS['applyJoin']['no_HongLu_built']); 
    }
	$userunion = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
    if ($userunion > 0)
    {
    	throw new Exception($GLOBALS['applyJoin']['already_joined_other_union']);
    }
	$unionid=intval(array_shift($param));
	$unionname=sql_fetch_one_cell("select `name` from sys_union_apply where `uid`='$uid'");
	if(!empty($unionname))
	{
		$msg = sprintf($GLOBALS['applyJoin']['reset_application'],$unionname);
		//throw new Exception("你已经申请加入［".$unionname."］,去鸿胪寺撤消原申请之后才能重新申请。");
		throw new Exception($msg);
	}
	$unionname=sql_fetch_one_cell("select `name` from `sys_union` where `id`='$unionid'");
	if(empty($unionname))
	{
		throw new Exception($GLOBALS['applyJoin']['union_not_exist']);
	}
	sql_insert("insert into `sys_union_apply` values ('$uid','$unionid','$unionname',unix_timestamp())");
	throw new Exception($GLOBALS['applyJoin']['send_application_succ']);
}
//取得本联盟的申请列表
function getApplyList($uid,$param)
{
	$user = sql_fetch_one("select union_id,union_pos from sys_user where uid='$uid'");
    $unionid=$user['union_id'];
    if ($unionid<=0||$user['union_pos']==0)
    {
        throw new Exception($GLOBALS['getApplyList']['not_official']);
    }
    $page=array_shift($param);
    $rowCount=sql_fetch_one_cell("select count(*) from `sys_union_apply` where `unionid`='$unionid'");
    $pageCount=ceil($rowCount/APPLY_PAGE_CPP);
    if($page>=$pageCount)
    {
    	$page=$pageCount-1;
    }
    if($page<0)
    {
    	$page=0;
    	$pageCount=0;
    }
    $ret=array();
    $ret[]=$pageCount;
    $ret[]=$page;
    if($rowCount>0)
    {
    	$start=$page*APPLY_PAGE_CPP;
    	$ret[]=sql_fetch_rows("select u.`uid` as userid,u.`name`,a.`time`,r.`prestige`,r.`rank`,r.`city` from `sys_union_apply` a left join `sys_user` u on a.`uid`=u.`uid` left join `rank_user` r on u.`name`=r.`name` where a.`unionid`='$unionid' order by a.`time` limit $start,".APPLY_PAGE_CPP);
    }
    else
    {
    	$ret[]=array();
    }
    return $ret;
}
function acceptApply($uid,$param)
{
	$page=array_shift($param);
	$auid=intval(array_shift($param));
    $user=sql_fetch_one("select `name`,`union_id` from `sys_user` where `uid`='$auid'");
    $userunion=$user['union_id'];
    if ($userunion > 0)
    {
    	throw new Exception($GLOBALS['acceptApply']['taget_joined_other_union']);
    }
    $honglulevel = sql_fetch_one_cell("select max(b.level) from sys_building b,sys_city c where b.bid='".ID_BUILDING_HONGLU."' and b.cid=c.cid and c.uid='$auid'");
    if (empty($honglulevel))
    {   
        throw new Exception($GLOBALS['acceptApply']['target_has_no_HongLu']); 
    }
	$myinfo = sql_fetch_one("select name,union_id,union_pos from sys_user where uid='$uid'");
	$unionid=$myinfo['union_id'];
	if ($myinfo['union_id']<=0||$myinfo['union_pos']<2)
    {
        throw new Exception($GLOBALS['acceptApply']['not_official']);
    }
    $union = sql_fetch_one("select `leader`,`member`,`chieforder` from sys_union where id='$unionid'");
    if (empty($union))
    {
    	throw new Exception($GLOBALS['acceptApply']['union_not_exist']);
    }
    if(!sql_check("select `uid` from `sys_union_apply` where `uid`='$auid' and `unionid`='$unionid'"))
    {
    	throw new Exception($GLOBALS['acceptApply']['data_record_not_exist']);
    }
	if(empty($union['chieforder']))
	{
    	$honglulevel = sql_fetch_one_cell("select max(b.level) from sys_building b,sys_city c where b.bid='".ID_BUILDING_HONGLU."' and b.cid=c.cid and c.uid='$union[leader]'");
	    if(empty($honglulevel))
	    {
	    	throw new Exception($GLOBALS['acceptApply']['no_HongLu_built']);
	    }
    	//$maxmember = $honglulevel * HONGLU_LEVEL_RATE;
	}
	//else $maxmember=150;
	$maxmember = getUnionMemberMaxCount($unionid);
   // $inviteCount = sql_fetch_one_cell("select count(*) from sys_union_invite where unionid='$unionid'");
    if ($union['member'] + 1 > $maxmember)
    {
        throw new Exception($GLOBALS['acceptApply']['union_is_full']);
    }
    
    //加入联盟
    
    sql_query("update `sys_user` set `union_id`='$unionid',`union_pos`=0 where `uid`='$auid'");
    sql_query("update `sys_union` set `member`=`member`+1 where `id`='$unionid'");
    $famouscity=sql_fetch_one_cell("select count(*) from sys_city where uid='$auid' and type>0 and type<5");
    if($famouscity>0)
    {
    	sql_query("update sys_union_city set `count`=GREATEST(0,`count`+$famouscity) where unionid='$unionid'");
    }
    completeTask($auid,67);
    notifyUnionChange($auid,$unionid,1);
    updateUnionRank($unionid);
    sql_query("delete from sys_union_apply where uid='$auid'");

//	addUnionEvent($unionid,EVT_ADD_UNION,"$myinfo[name] 通过了 $user[name] 入盟申请！");
    $msg = sprintf($GLOBALS['acceptApply']['addUnionEvent'],$myinfo[name],$user[name]);
	addUnionEvent($unionid,EVT_ADD_UNION,$msg );
	/*
	 * 平台接口
	 */
	PUSH_SNS_UNION_MSG("AddUnionMemberEvent",$unionid,2);
	
	$param2=array();
	$param[]=$page;
    return getApplyList($uid,$param2);
}

function rejectApply($uid,$param)
{
	$page=array_shift($param);
	$auid=intval(array_shift($param));
	$union = sql_fetch_one("select union_id,union_pos from sys_user where uid='$uid'");
	$unionid=$union['union_id'];
	$unionpos=$union['union_pos'];
    if ($unionid<=0||$unionpos<2)
    {
        throw new Exception($GLOBALS['rejectApply']['not_official']);
    }
    sql_query("delete from sys_union_apply where uid='$auid'");

	$param2=array();
	$param[]=$page;
    return getApplyList($uid,$param2);
}

function cancelApply($uid,$param)
{
	sql_query("delete from `sys_union_apply` where `uid`='$uid'");
	$ret=array();
	return $ret;
}

function acceptInvite($uid,$param)
{
    $unionid = intval(array_shift($param));
    
    //查看请求是否还有效
    $invite=sql_fetch_one("select * from sys_union_invite where unionid='$unionid' and uid='$uid'");

    if(empty($invite))
    {
         throw new Exception($GLOBALS['acceptInvite']['invalid_invitation']);  
    }
    if (!sql_check("select * from sys_union where id='$unionid'"))
    {
         throw new Exception($GLOBALS['acceptInvite']['union_not_exist']);  
    }
	$honglulevel = sql_fetch_one_cell("select max(b.level) from sys_building b,sys_city c where b.bid='".ID_BUILDING_HONGLU."' and b.cid=c.cid and c.uid='$uid'");
    if(empty($honglulevel))
    {
    	throw new Exception($GLOBALS['acceptInvite']['no_HongLu_built']);
    }
    $user = sql_fetch_one("select `name`,`union_id` from sys_user where uid='$uid'");
    $userunion=$user['union_id'];
    if ($userunion > 0) throw new Exception($GLOBALS['acceptInvite']['already_joined_other_union']);

    //加入联盟
    
    sql_query("update sys_user set union_id='$unionid',union_pos=0 where uid='$uid'");
    sql_query("update sys_union set member=member+1 where id='$unionid'");    
    $famouscity=sql_fetch_one_cell("select count(*) from sys_city where uid='$uid' and type>0 and type<5");
    if($famouscity>0)
    {
    	sql_query("update sys_union_city set `count`=GREATEST(0,`count`+$famouscity) where unionid='$unionid'");
    }
    completeTask($uid,67);
    notifyUnionChange($uid,$unionid,1);
    updateUnionRank($unionid);
    sql_query("delete from sys_union_invite where uid='$uid'");
    
    $msg = sprintf($GLOBALS['acceptInvite']['addUnionEvent'],$invite[inviter],$user[name]);
    addUnionEvent($unionid,EVT_ADD_UNION,$msg);
    /*
	 * 平台接口
	 */
	PUSH_SNS_UNION_MSG("AddUnionMemberEvent",$unionid,2);
    
    $ret = array();
    $ret[] = $unionid;
    $ret[] = 1;
    return $ret;
}
function rejectInvite($uid,$param)
{
    $unionid = intval(array_shift($param));
    if (!sql_check("select * from sys_union_invite where unionid='$unionid' and uid='$uid'"))
    {
         throw new Exception($GLOBALS['rejectInvite']['invalid_invitation']);  
    }
    if (!sql_check("select * from sys_union where id='$unionid'"))
    {
         throw new Exception($GLOBALS['rejectInvite']['union_not_exist']);  
    }
    sql_query("delete from sys_union_invite where unionid='$unionid' and uid='$uid'");                                                                   
    return array();
}

function loadUnionDetail($uid,$param)
{
	$unionid=intval(array_shift($param));
	if (!sql_check("select id from sys_union where id='$unionid'"))
    {
        $ret[] = 0;
        $ret[] = $GLOBALS['loadUnionDetail']['union_dissmissed'];
        return $ret;
    }
	return getUnionDetail($unionid,0);
}

function loadUnionInfo($uid,$param)
{
    $union = sql_fetch_one("select union_id,union_pos from sys_user where uid='$uid'");
    $unionid=$union['union_id'];
    if($union['union_id'] == 0 || $union['union_pos'] <=0 || $union['union_pos'] >= 3)
    {
    	sql_query("update sys_alarm set `union` = 0 where uid=$uid");
    }
    $ret = array();
    if (empty($union)||$unionid<=0)
    {
        $ret[] = 0;
        $ret[] = $GLOBALS['loadUnionInfo']['not_belongTo_union'];
        return $ret;
    }
    else if (!sql_check("select id from sys_union where id='$unionid'"))
    {
        $ret[] = 0;
        $ret[] = $GLOBALS['loadUnionInfo']['your_union_is_out'];
        return $ret;
    }
    $ret=getUnionDetail($unionid,1);
    $ret[]=$union['union_pos'];
    return $ret;
}

function getUnionDetail($unionid,$inner)
{
	$ret=array();
	$ret[] = 1;
	if($inner==1)
	{
    	$union = sql_fetch_one("select n.*,u.name as leadername,u2.name as creator from sys_union n left join sys_user u on n.leader=u.uid left join sys_user u2 on n.creator=u2.uid where n.id='$unionid'");
	}
	else
	{
    	$union = sql_fetch_one("select n.`id`,n.`chieforder`,r.`leader`,r.`name`,r.`member`,r.`famouscity`,r.`rank`,r.`prestige`,u.name as creator,n.`intro` from sys_user u, sys_union n left join rank_union r on r.uid=n.id where n.id='$unionid' and u.uid=n.creator");
	}
	if($union['famouscity'] == "") {
		$union['famouscity'] = 0;
	}
    $ret[] = $union;
   
    $ret[]=getUnionMemberMaxCount($unionid);
//    if(empty($union['chieforder']))
//    {
//	    $leader = sql_fetch_one_cell("select leader from sys_union where id='$unionid'");
//	    $ret[] = sql_fetch_one_cell("select max(b.level)*10 from sys_building b,sys_city c where b.bid='".ID_BUILDING_HONGLU."' and b.cid=c.cid and c.uid='$leader'");
//    }
//    else $ret[]=150;
    return $ret;
}

function loadUnionMemberList($uid,$param)
{
	$union=sql_fetch_one("select `union_id`,`union_pos` from sys_user where `uid`='$uid'");
	$unionid = intval($union['union_id']);
    if ($unionid <= 0)
    {
        throw new Exception($GLOBALS['loadUnionMemberList']['you_belongTo_none_union']);
    }
    if (intval($union['union_pos'])>0) {//成员以上才能看盟友是否在线
    	return sql_fetch_rows("select u.*,r.rank as srank,count(c.cid) as cityCount,s.lastupdate,d.donate from sys_user u left join sys_city c on c.uid=u.uid left join sys_online s on s.uid=u.uid left join rank_user r on r.uid=u.uid left join sys_user_donate d on d.uid=u.uid and d.unionid=u.union_id where u.union_id='$unionid' group by u.uid");
    }else{   
    	return sql_fetch_rows("select u.*,r.rank as srank,count(c.cid) as cityCount,0 as lastupdate,d.donate from sys_user u left join sys_city c on c.uid=u.uid left join sys_online s on s.uid=u.uid left join rank_user r on r.uid=u.uid left join sys_user_donate d on d.uid=u.uid and d.unionid=u.union_id where u.union_id='$unionid' group by u.uid");
    }
    
}
function leaveUnion($uid,$param)
{
    $user = sql_fetch_one("select `name`, `union_id`,`union_pos` from sys_user where uid='$uid'");
    $unionid=$user['union_id'];
    $unionpos=$user['union_pos'];
    if ($unionid == 0) throw new Exception($GLOBALS['leaveUnion']['you_belongTo_none_union']);
    $union = sql_fetch_one("select * from sys_union where id='$unionid'");
    if (empty($union))
    {
        throw new Exception($GLOBALS['leaveUnion']['your_union_is_out']);
    }
    $luoyangUid = sql_fetch_one_cell("select uid from sys_luoyang_info limit 1");
    if ($luoyangUid == $uid) {
    	throw new Exception($GLOBALS['luoyang']['cannot_leave_union']);
    }
    //如果玩家挂了本盟内的资源交易，提醒玩家取消掉 
    $tradeCids = sql_fetch_rows("select distinct cid from sys_city_trade where unionid='$unionid' and cid in(select cid from sys_city where uid='$uid')");
    $cidStr="";
    if(!empty($tradeCids)){
    	foreach($tradeCids as $tradeCid){
    		$existCid = intval($tradeCid['cid']);
    		$x = $existCid%1000;
    		$y = floor($existCid/1000);
    		$cidStr .= '['.strval($x).','.strval($y).']  ';  		
    	}
    }
    if(strlen($cidStr)>0){
    	$msg = sprintf($GLOBALS['union']['market_has_resource'],$cidStr);
    	throw new Exception($msg);
    }
    
    //如果是盟主的话，如果还有会员就不能离开联盟
    if ($union['leader'] == $uid)
    {
        $unionUserCount = sql_fetch_one_cell("select `member` from sys_union where id='$unionid'");
        if ($unionUserCount > 1)
        {
            throw new Exception($GLOBALS['leaveUnion']['chief_cant_leave']);
        }
        canUserLeaveUnion($uid);
        sql_query("update sys_user set union_id=0,union_pos=0 where uid='$uid'"); 
        sql_query("delete from sys_union where id='$unionid'");
        sql_query("delete from sys_union_relation where unionid='$unionid' or `target`='$unionid'");
        sql_query("delete from sys_union_event where unionid='$unionid'");
        sql_query("delete from sys_union_invite where unionid='$unionid'");
        sql_query("delete from sys_union_apply where unionid='$unionid'");
        sql_query("delete from huangjin_task_log_union where unionid='$unionid'");
        sql_query("delete from sys_union_city where unionid='$unionid'");
        notifyUnionChange($uid,$unionid,0);
    }
    else if($unionpos==5)
    {
        throw new Exception($GLOBALS['leaveUnion']['official_cant_leave']);
    }
    else
    {
    	notifyUnionChange($uid,$unionid,0);
        sql_query("update sys_user set union_id=0,union_pos=0 where uid='$uid'");
        sql_query("update sys_union set member=member-1 where id='$unionid'");
        $famouscity=sql_fetch_one_cell("select count(*) from sys_city where uid='$uid' and type>0 and type<5");
        if($famouscity>0)
        {
        	sql_query("update sys_union_city set `count`=GREATEST(0,`count`-$famouscity) where unionid='$unionid'");
        }
        $cities=sql_fetch_rows("select cid from sys_city where uid='$uid'");
        $fieldcids="";
		$comma="";
        if(!empty($cities))
        {
        	foreach($cities as $city)
        	{
        		$cid=$city['cid'];
		        $ownerfields=sql_fetch_rows("select wid from mem_world where ownercid='$cid'");
			    if(!empty($ownerfields))
			    {
					foreach($ownerfields as $mywid)
					{
						$fieldcids.=$comma;
						$fieldcids.=wid2cid($mywid['wid']);
						$comma=",";
					}
				}
			}
			if(!empty($fieldcids))
			{
				sql_query("update sys_troops set `state`=1,endtime=pathtime+unix_timestamp(),starttime=unix_timestamp() where targetcid in ($fieldcids) and state=4 and task<6 and uid<>'$uid' and uid > 0");
			}
			foreach($cities as $city)
			{
				updateCityResourceAdd($city['cid']);
			}
		}
		$troops=sql_fetch_rows("select id,targetcid from sys_troops where uid='$uid' and state=4 and task<=6");
		foreach($troops as $troop)
		{
			$wid=cid2wid($troop['targetcid']);
			$owneruid=sql_fetch_one_cell("select c.uid from sys_city c, mem_world m where m.wid='$wid' and m.ownercid=c.cid");
			if($owneruid!=$uid)
			{
				sql_query("update sys_troops set `state`=1,endtime=pathtime+unix_timestamp(),starttime=unix_timestamp() where id='$troop[id]'");
				updateCityResourceAdd($troop['cid']);
			}
			
		}
        updateUnionRank($unionid);
    } 
    $msg = sprintf($GLOBALS['leaveUnion']['addUnionEvent'],$user[name]);
    addUnionEvent($unionid,EVT_QUIT_UNION,$msg);
    //如果玩家主动退盟,检测下玩家是否有被开除的记录,有就给清除掉
    if(sql_check("select 1 from mem_union_remove where uid='$uid' and unionid='$unionid'"))
    {
    	sql_query("delete from mem_union_remove where uid='$uid' and unionid='$unionid'");
    }
    /*
	 * 平台接口
	 */
	PUSH_SNS_UNION_MSG("AddUnionMemberEvent",$unionid,1);
    
    return array();                         
}
function getInviteList($uid,$param)
{
    $union = sql_fetch_one("select union_id,union_pos from sys_user where uid='$uid'");
    $unionid=$union['union_id'];
    $unionpos=$union['union_pos'];
    if($unionid<=0||$unionpos==0)
    {
        throw new Exception($GLOBALS['getInviteList']['you_are_not_official']);
    }
     return sql_fetch_rows("select u.uid as userid,u.name,u.rank,u.prestige,i.inviter,i.`time` from sys_union_invite i left join sys_user u on u.uid=i.uid where i.unionid='$unionid';");
}
function cancelInvite($uid,$param)
{
    $targetuid = intval(array_shift($param));
    $union = sql_fetch_one("select union_id,union_pos from sys_user where uid='$uid'");
    $unionid=$union['union_id'];
    $unionpos=$union['union_pos'];
    if($unionid<=0||$unionpos<2)
    {
        throw new Exception($GLOBALS['cancelInvite']['you_are_not_official']);
    }
    sql_query("delete from sys_union_invite where unionid='$unionid' and uid='$targetuid'");
    return getInviteList($uid,$param);
}
function inviteUser($uid,$param)
{
    $username = (trim(array_shift($param)));
    if (empty($username))
    {
        throw new Exception($GLOBALS['inviteUser']['enter_target_name']);
    }
    else if(mb_strlen($username,"utf-8")>MAX_USER_NAME)
    {
    	throw new Exception($GLOBALS['inviteUser']['name_length_most']);
    }
    $username=addslashes($username);
    $myinfo = sql_fetch_one("select name,union_id,union_pos from sys_user where uid='$uid'");
    $unionid=$myinfo['union_id'];
    $unionpos=$myinfo['union_pos'];
    $union = sql_fetch_one("select * from sys_union where id='$unionid'");
    if($unionid<=0||$unionpos<2)
    {
        throw new Exception($GLOBALS['inviteUser']['you_are_not_official']);
    }
    $user = sql_fetch_one("select * from sys_user where name='$username'");
    if (empty($user))
    {
        throw new Exception($GLOBALS['inviteUser']['named_user_not_exist']);
    }
    if ($user['uid'] == $uid)
    {
        throw new Exception($GLOBALS['inviteUser']['cant_invite_yourself']);
    }
    else if ($user['union_id'] > 0)
    {
        throw new Exception($GLOBALS['inviteUser']['taget_joined_other_union']);
    }
    
//    $honglulevel = sql_fetch_one_cell("select max(b.level) from sys_building b,sys_city c where b.bid='".ID_BUILDING_HONGLU."' and b.cid=c.cid and c.uid='$union[leader]'");   
//    if(empty($union['chieforder']))
//    {
//    	$maxmember = $honglulevel * HONGLU_LEVEL_RATE;
//    }
//    else $maxmember=150;
	$maxmember = getUnionMemberMaxCount($unionid);
    $inviteCount = sql_fetch_one_cell("select count(*) from sys_union_invite where unionid='$unionid'");
    if ($union['member'] + $inviteCount > $maxmember)
    {
        throw new Exception($GLOBALS['inviteUser']['your_union_is_full']);
    }
    
    sql_query("replace into sys_union_invite (unionid,inviter,uid,`time`) values ('$unionid','$myinfo[name]','$user[uid]',unix_timestamp())");
                                          
    return getInviteList($uid,$param);
                                                           
}
function kickMember($uid,$param)
{                       
    $username = (trim(array_shift($param)));
	if(empty($username))
	{
		throw new Exception($GLOBALS['kickMember']['enter_target_name']);
	}
    if(mb_strlen($username,"utf-8")>MAX_USER_NAME)
    {
    	throw new Exception($GLOBALS['kickMember']['name_length_most']);
    }
    $username=addslashes($username);
    $user = sql_fetch_one("select `name`,`union_id`,union_pos from sys_user where uid='$uid'");
    $unionid=$user['union_id'];
    $unionpos=$user['union_pos'];
    if($unionid<=0||($unionpos==0||$unionpos<4))
    {
        throw new Exception($GLOBALS['kickMember']['not_elder']);
    }
    $userinfo = sql_fetch_one("select * from sys_user where name='$username' and union_id='$unionid'");
    if (empty($userinfo))
    {
        throw new Exception($GLOBALS['kickMember']['target_not_in_your_union']);
    }
    else if ($userinfo['union_id'] != $unionid)
    {
        throw new Exception($GLOBALS['kickMember']['target_not_in_your_union']);
    }
    else if ($userinfo['union_pos']>3)
    {
    	throw new Exception($GLOBALS['kickMember']['descend_target_level']);
    }
    else if ($userinfo['uid']==$uid)
    {
    	throw new Exception($GLOBALS['kickMember']['cant_kick_oneself']);
    } 
    $info = sql_fetch_one("select * from mem_union_remove where uid='$userinfo[uid]'");
    if (!empty($info)) {
    	throw new Exception($GLOBALS['kickMember']['union_member_removed']);
    }
    $needTime=0;
    switch ($userinfo['union_pos']) {
    	case 0: 
    		$needTime = 10800;
    		break;
    	case 1:
    		$needTime =	21600;
    		break;
    	case 2:
    		$needTime = 43200;
    		break;
    	case 3:
    		$needTime = 86400;
    		break;
    	default:
    		$needTime = 86400;
    		break;
    }
    sql_query("insert into mem_union_remove(douid,uid,unionid,time) values('$uid','$userinfo[uid]','$unionid',unix_timestamp()+$needTime)");
    /*
    sql_query("update sys_user set union_id=0 where uid='$userinfo[uid]'");
    sql_query("update sys_union set member=member-1 where id='$unionid'");
    $famouscity=sql_fetch_one_cell("select count(*) from sys_city where uid='$uid' and type>0 and type<5");
    if($famouscity>0)
    {
    	sql_query("update sys_union_city set `count`=GREATEST(0,`count`-$famouscity) where unionid='$unionid'");
    }
    $cities=sql_fetch_rows("select cid from sys_city where uid='$userinfo[uid]'");
    $fieldcids="";
	$comma="";
    if(!empty($cities))
    {
    	foreach($cities as $city)
    	{
    		$cid=$city['cid'];
	        $ownerfields=sql_fetch_rows("select wid from mem_world where ownercid='$cid'");
		    if(!empty($ownerfields))
		    {
				foreach($ownerfields as $mywid)
				{
					$fieldcids.=$comma;
					$fieldcids.=wid2cid($mywid['wid']);
					$comma=",";
				}
			}
		}
		if(!empty($fieldcids))
		{
			sql_query("update sys_troops set `state`=1,endtime=pathtime+unix_timestamp(),starttime=unix_timestamp() where targetcid in ($fieldcids) and state=4 and uid<>'$userinfo[uid]' and uid > 0");
		}
		foreach($cities as $city)
		{
			updateCityResourceAdd($city['cid']);
		}
	}
	$troops=sql_fetch_rows("select id,targetcid from sys_troops where uid='{$userinfo['uid']}' and state=4");
	foreach($troops as $troop)
	{
		$wid=cid2wid($troop['targetcid']);
		$owneruid=sql_fetch_one_cell("select c.uid from sys_city c, mem_world m where m.wid='$wid' and m.ownercid=c.cid");
		if($owneruid!=$uid)
		{
			sql_query("update sys_troops set `state`=1,endtime=pathtime+unix_timestamp(),starttime=unix_timestamp() where id='$troo[id]'");
			updateCityResourceAdd($troop['cid']);
		}
	}
    notifyUnionChange($userinfo['uid'],$unionid,0);
    updateUnionRank($unionid);
    
    $msg = sprintf($GLOBALS['kickMember']['addUnionEvent'],$user[name],$userinfo[name]);
    addUnionEvent($unionid,EVT_KICK_MENBER,$msg);
    */
    $param2=array();
    $param2[]=$unionid;
    return loadUnionMemberList($uid,$param2);
	
}

function changeLeader($uid,$param)
{
	//canUserLeaveUnion($uid);
	$username = (trim(array_shift($param)));
	if(mb_strlen($username,"utf-8")==0)
	{
		throw new Exception($GLOBALS['changeLeader']['enter_target_name']);
	}
	else if(mb_strlen($username,"utf-8")>MAX_USER_NAME)
    {
    	throw new Exception($GLOBALS['changeLeader']['name_length_most']);
    }
    $username=addslashes($username);
    $olduser = sql_fetch_one("select `name`,`union_id` from sys_user where uid='$uid'");
    $unionid=$olduser['union_id'];
    $union = sql_fetch_one("select * from sys_union where id='$unionid'");
    if ($union['leader'] != $uid)
    {
        throw new Exception($GLOBALS['changeLeader']['you_are_not_chief']);
    }
    $user = sql_fetch_one("select * from sys_user where name='$username'");
    if (empty($user))
    {
        throw new Exception($GLOBALS['changeLeader']['target_name_not_exist']);
    }
    else if ($user['union_id'] != $unionid)
    {
        throw new Exception($GLOBALS['changeLeader']['target_not_in_your_union']);
    }
	if(sql_check("select 1 from mem_union_remove where uid={$user['uid']} and unionid='$unionid'"))
    {
    	throw new Exception($GLOBALS['union']['user_can_not_transfer']);
    }
//    else if ($user['union_pos']!=5)
//    {
//    	throw new Exception($GLOBALS['changeLeader']['upgrade_vice_chief']);
//    }
    $leader=$user['uid'];
    if($leader!=$uid)
    {
	    sql_query("update sys_user set union_pos='0' where uid='$uid'");
	    sql_query("update sys_user set union_pos='5' where uid='$leader'");
	    sql_query("update sys_union set `leader`='$leader' where id='$unionid'");
	    exchangeUnionState($uid,$leader);
	    $msg = sprintf($GLOBALS['changeLeader']['addUnionEvent'],$olduser[name],$username);
        addUnionEvent($unionid,EVT_CHANGE_LEADER,$msg);
        /*
		 * 平台接口
		 */
		PUSH_SNS_UNION_MSG("AddTransferUnionChiefEvent",$unionid,$olduser[name],$username);
        finishAchivement($uid,13);
    }                                                                                          
    return loadUnionInfo($uid,null);
}

function getUnionIntro($uid,$param)
{
    $user = sql_fetch_one("select union_id,union_pos from sys_user where uid='$uid'");
    $unionid=$user['union_id'];
    $unionpos=$user['union_pos'];
    if(($unionid<=0)||($unionpos<4))
    {
        throw new Exception($GLOBALS['getUnionIntro']['you_are_not_chief']);
    }
    return sql_fetch_rows("select * from sys_union where id='$unionid'");
}
function modifyIntro($uid,$param)
{
    $name = trim(array_shift($param));
    $name = addslashes($name);
    $intro = array_shift($param);
    $intro = addslashes($intro);
    $announce = array_shift($param);
    $announce = addslashes($announce);
    $flag = intval(array_shift($param));      
   
   	if(mb_strlen($name,"utf-8")==0)
   	{
   		throw new Exception($GLOBALS['modifyIntro']['union_name_notNull']);
   	}
    else if (mb_strlen($name,"utf-8") > MAX_UNION_NAME)
    {
        throw new Exception($GLOBALS['modifyIntro']['union_name_tooLong']);
    }
    else if ((!(strpos($name,'\'')==false))||(!(strpos($name,'\\')==false)) || (!(strpos($name,13)==false)))
    {
    	throw new Exception($GLOBALS['modifyIntro']['invalid_char']);
    }
    else if (sql_check("select * from cfg_baned_name where instr('$name',`name`)>0"))
    {
    	throw new Exception($GLOBALS['modifyIntro']['invalid_char']);
    }
    else if(mb_strlen($intro,"utf-8")>200)
    {
    	throw new Exception($GLOBALS['modifyIntro']['union_description_tooLong']);
    }
    else if (mb_strlen($announce,"utf-8")>500)
    {
    	throw new Exception($GLOBALS['modifyIntro']['union_announce_tooLong']);
    }
    
	if (sql_check("select * from cfg_baned_mail_content where instr('$announce',`content`)>0"))
	{
		throw new Exception($GLOBALS['banned']['word_illegal']);
	}
	if (sql_check("select * from cfg_baned_mail_content where instr('$intro',`content`)>0"))
	{
		throw new Exception($GLOBALS['banned']['word_illegal']);
	}
	
    //$name=addslashes($name);
    $user= sql_fetch_one("select `name`,`union_id`,`union_pos` from sys_user where uid='$uid'");
    $unionid=$user['union_id'];
    $unionpos=$user['union_pos'];
    $union = sql_fetch_one("select * from sys_union where id='$unionid'");
    if(($unionid<=0)||($unionpos<4))
    {
        throw new Exception($GLOBALS['modifyIntro']['you_are_not_chief']);
    }
    if ($unionpos<5 && $flag==1) {
    	throw new Exception($GLOBALS['modifyIntro']['you_are_not_chief']);
    }
    if($union['name']!=$name)
    {
		if(sql_check("select `id` from `sys_union` where `name`='$name'"))
		{
			throw new Exception($GLOBALS['modifyIntro']['union_name_in_use']);
		}
	}
    sql_query("update sys_union set name='$name',intro='$intro',announcement='$announce' where id='$unionid'");
    if($union['name']!=$name)
    {
    	$msg = sprintf($GLOBALS['modifyIntro']['addUnionEvent'],$user[name],$name);
    	addUnionEvent($unionid,EVT_CHANGE_NAME,$msg);
    }
    return array();
}                                                             

function getUnionRelation($uid,$param)
{
	$type=array_shift($param);
	$type = addslashes($type);
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
    $ret = array();
    if (empty($unionid))
    {
        $ret[] = 0;
        $ret[] = $GLOBALS['getUnionRelation']['not_belongTo_union'];
    }
    else
    {
    	$ret[]=1;
    	$ret[]=sql_fetch_rows("select $type as relationtype, ur.`target` as unionid,u.`name`,r.`leader`,u.`prestige`,u.`member`,r.`rank` from `sys_union_relation` ur left join `sys_union` u on u.`id`=ur.`target` left join `rank_union` r on r.`uid`=ur.`target` where ur.`unionid`=$unionid and ur.`type`=$type");
    }
    return $ret;
}

function getRequestList($uid,$param)
{
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
	$unionpos = sql_fetch_one_cell("select union_pos from sys_user where uid='$uid'");
    $ret = array();
    if (empty($unionid))
    {
        $ret[] = 0;
        $ret[] = $GLOBALS['getUnionRelation']['not_belongTo_union'];
    }
    else
    {
    	$requests = sql_fetch_rows("select * from mem_union_relation where target=$unionid and type=0");
    	if(!empty($requests))
    	{
    		foreach($requests as $request){
    			$fromunionid = $request['unionid'];
    			$fromunion = sql_fetch_one("select * from sys_union where id=$fromunionid");
    			if(empty($fromunion)) {
    				sql_query("delete from mem_union_relation where target=$unionid and unionid=$fromunionid and type = 0");
    			}
    		}
    	}    	
    	$ret[]=1;    	
    	$ret[]=sql_fetch_rows("select -1 as flag, ur.`unionid` as unionid,from_unixtime(ur.`time`) as time,u.`name`,r.`leader`,u.`prestige`,u.`member`,r.`rank` from `mem_union_relation` ur left join `sys_union` u on u.`id`=ur.`unionid` left join `rank_union` r on r.`uid`=ur.`unionid` where ur.`target`=$unionid and ur.`type`=0");
    }
    $count = sql_fetch_one_cell("select count(*) from mem_union_relation where target = $unionid");
    if($count == 0 || $unionpos >= 3 || $unionpos <= 0 || $unionid == 0) {
    	sql_query("update sys_alarm set `union` = 0 where uid=$uid");
    }    
    return $ret;
}

function addUnionRelationByName($uid, $param) {
	$type=array_shift($param);
	$name = array_shift($param);
	$name = addslashes($name);
	if(mb_strlen($name,"utf-8")==0)
	{
		throw new Exception($GLOBALS['addUnionRelation']['enter_target_name']);
	}
	else if(mb_strlen($name,"utf-8")>8)
	{
		throw new Exception($GLOBALS['addUnionRelation']['union_name_tooLong']);
	}
	//$name=addslashes($name);
	$union = sql_fetch_one("select * from sys_union where name='$name' limit 1");
	
	if(empty($union)) {
		throw new Exception($GLOBALS['addUnionRelation']['target_union_not_exist']);
	}
	
	$param2 = array();
	$param2[] = $type;
	$param2[] = $union['id'];
	return addUnionRelation($uid, $param2);
}

function addUnionRelation($uid,$param)
{
	$type=array_shift($param);
	if($type < 0 || $type > 2) {
		throw new Exception($GLOBALS['addUnionRelation']['no_this_diplomacy']);
	}
	
	$target = array_shift($param);

	$myinfo = sql_fetch_one("select name,union_id,union_pos from sys_user where uid='$uid'");
	$unionid=$myinfo['union_id'];
	$unionpos=$myinfo['union_pos'];
    $union = sql_fetch_one("select `name`,`leader` from sys_union where id='$unionid'");
    // 盟主 副盟主 有权限，长老也不行
    if(($unionid<=0)||($unionpos==0)||($unionpos<3))
    {
        throw new Exception($GLOBALS['addUnionRelation']['you_are_not_chief']);
    }
    else if ($unionid == $target)
    {
    	throw new Exception($GLOBALS['addUnionRelation']['cant_contact_with_oneself']);
    }
	$targetunion=sql_fetch_one("select * from `sys_union` where id='$target' limit 1");
	if(empty($targetunion))
	{
		throw new Exception($GLOBALS['addUnionRelation']['target_union_not_exist']);
	}
	
	//上次成功更改外交状态的时间
	$oldRelation=sql_fetch_one("select *,unix_timestamp() as nowtime from sys_union_relation where unionid='$unionid' and target='$target'");

	if(!empty($oldRelation) && $oldRelation['time']>$oldRelation['nowtime']-900)
	{
		$remainTime=MakeTimeLeft(900+$oldRelation['time']-$oldRelation['nowtime']);
		throw new Exception(sprintf($GLOBALS['addUnionRelation']['too_frequency'],$remainTime));
	}
	
	if(!empty($oldRelation) && $oldRelation['type'] == $type)
	{
		if($type == 0) {
			$relation = $GLOBALS['addUnionRelation']['friendly'];
		} else if($type == 1) {
			$relation = $GLOBALS['addUnionRelation']['neutral'];
		} else if($type == 2) {
			$relation = $GLOBALS['addUnionRelation']['hostile'];
		}
		$msg = sprintf($GLOBALS['addUnionRelation']['have_done'],$relation);
		throw new Exception($msg);
	}
	
	if($type==0)// 设置友好
	{
		$oldAttempt=sql_fetch_one("select *,unix_timestamp() as nowtime from mem_union_relation where unionid='$unionid' and target='$target' and type=0");
		
		if(!empty($oldAttempt)) {
			throw new Exception($GLOBALS['addUnionRelation']['friendly_have_sent']);
		}
		
		sql_query("insert into `mem_union_relation` (`type`,`unionid`,`target`,time) values ('$type','$unionid','$target',unix_timestamp()) on duplicate key update `type`='$type',time=unix_timestamp()");
		
		// 信件通知对方盟内高官，包括 盟主 副盟主  长老
		$receivers=sql_fetch_rows("select uid from sys_user where union_id='$target' and union_pos>=3 and union_pos<=5");
		foreach($receivers as $receiver)
		{
			$msg = sprintf($GLOBALS['addUnionRelation']['friendly_attempt_mail_content_AtoB'],$union['name']);
			sendSysMail($receiver['uid'],$GLOBALS['addUnionRelation']['friendly_attempt_mail_title'],$msg);
			// 将toppanel的联盟按钮闪烁开启,只有盟主和副盟主
			if($receiver['union_pos'] >=4) {
				sql_query("insert into sys_alarm (`uid`,`union`) values ('$receiver[uid]',1) on duplicate key update `union`=1");
			}
			
			// 插入nb的信息提示
			$nowtime = sql_fetch_one_cell("select unix_timestamp()");
			$alarminfo = sprintf($GLOBALS['addUnionRelation']['friendly_attempt_alarm_info'],$union['name']);
			sql_query("insert into sys_info_alarm(`uid`,`type`,`info`,`time`) values('$receiver[uid]',0,'$alarminfo','$nowtime')");
		}
	}
	else if($type==1)// 设置中立
	{
		// 啥都不干
		 /*
		 * 平台接口
		 */
		PUSH_SNS_UNION_MSG("AddUnionDiplomacyEvent",$unionid,$targetunion['name'],$GLOBALS['addUnionRelation']['neutral']);
	}
	else if($type==2)// 设置敌对
	{
		if(!empty($oldRelation) && $oldRelation['type'] == 2)
		{
			throw new Exception($GLOBALS['addUnionRelation']['hostile_have_done']);
		}
		
		$otherside = sql_fetch_one_cell("select time from sys_union_relation where type=2 and unionid=$target and target=$unionid");
		// 建立双边关系
		if(empty($otherside)) {//对方之前没有宣战过
			sql_query("insert into `sys_union_relation` (`type`,`unionid`,`target`,time) values ('2','$unionid','$target',unix_timestamp()) on duplicate key update `type`='2',time=unix_timestamp()");
			sql_query("insert into `sys_union_relation` (`type`,`unionid`,`target`,time) values ('2','$target','$unionid',unix_timestamp()) on duplicate key update `type`='2',time=unix_timestamp()");
		} else {//之前打过，按照之前的时间，避免重新计算8小时
			sql_query("insert into `sys_union_relation` (`type`,`unionid`,`target`,time) values ('2','$unionid','$target',$otherside) on duplicate key update `type`='2',time=$otherside");
		}
		
		// 把申请删掉
		//sql_query("delete from mem_union_relation where type=2 and unionid=$target and target=$unionid");
		
		// 信件通知对方盟内所有成员
		$receivers=sql_fetch_rows("select uid from sys_user where union_id='$target'");
		foreach($receivers as $receiver)
		{
			$msg = sprintf($GLOBALS['addUnionRelation']['hostile_mail_content_BtoA'],$union['name'],$union['name']);
			sendSysMail($receiver['uid'],$GLOBALS['addUnionRelation']['hostile_mail_title'],$msg);
			
			// 插入nb的信息提示
			$nowtime = sql_fetch_one_cell("select unix_timestamp()");
			$alarminfo = sprintf($GLOBALS['addUnionRelation']['hostile_attempt_alarm_info_BtoA'],$union['name']);
			sql_query("insert into sys_info_alarm(`uid`,`type`,`info`,`time`) values('$receiver[uid]',0,'$alarminfo','$nowtime')");
		}
		
		// 信件通知本方盟内所有成员
		$receivers=sql_fetch_rows("select uid from sys_user where union_id='$unionid'");
		foreach($receivers as $receiver)
		{
			$msg = sprintf($GLOBALS['addUnionRelation']['hostile_mail_content_AtoB'],$targetunion['name'],$targetunion['name']);
			sendSysMail($receiver['uid'],$GLOBALS['addUnionRelation']['hostile_mail_title'],$msg);
			
			// 插入nb的信息提示
			$nowtime = sql_fetch_one_cell("select unix_timestamp()");
			$alarminfo = sprintf($GLOBALS['addUnionRelation']['hostile_attempt_alarm_info_AtoB'],$targetunion['name']);
			sql_query("insert into sys_info_alarm(`uid`,`type`,`info`,`time`) values('$receiver[uid]',0,'$alarminfo','$nowtime')");
		}
		
		// 频道里面公布一下
		$msg = sprintf($GLOBALS['addUnionRelation']['hostile_unionWar'],$union['name'],$targetunion['name']);
		if(!sql_check("select id from sys_inform where scrollcount=100000+'$unionid'"))
		{
			sendSysInform(0,1,0,600,50000,100000+'$unionid',16727871,$msg);
			//sql_query("insert into sys_inform (`type`,`inuse`,`starttime`,`endtime`,`interval`,`scrollcount`,`color`,`msg`) values (0,1,unix_timestamp(),unix_timestamp()+600,50000,100000+'$unionid',16727871,'$msg')");
		}
		
		// 联盟事件
		$msgAToB = sprintf($GLOBALS['addUnionRelation']['hostile_union_message_B_to_A'],$union['name']);
		addUnionEvent($target,EVT_RELATION_FRIEND,$msgAToB);
		$msgBToA = sprintf($GLOBALS['addUnionRelation']['hostile_union_message_A_to_B'],$targetunion['name']);	
		addUnionEvent($unionid,EVT_RELATION_FRIEND,$msgBToA);
		 /*
		 * 平台接口
		 */
		PUSH_SNS_UNION_MSG("AddUnionDiplomacyEvent",$unionid,$targetunion['name'],$GLOBALS['addUnionRelation']['hostile']);
		 /*
		 * 平台接口
		 */
		PUSH_SNS_UNION_MSG("AddUnionDeclareWarEvent",$unionid,$union['name'],$targetunion['name']);
	}

//	$ret = array();
//	$ret[] = 255;
//	$ret[] = $GLOBALS['addUnionRelation']['friendly_attempt_send_succ'];
//	$param2=array();
//	$param2[]=$type;
//	$ret[] = getUnionRelation($uid,$param2);
//	return $ret;
	throw new Exception($GLOBALS['addUnionRelation']['diplomacy_send_succ']);
}

function pendingFriendly($uid, $param)
{
	$accept=intval(array_shift($param));
	$target = intval(array_shift($param));

	$myinfo = sql_fetch_one("select name,union_id,union_pos from sys_user where uid='$uid'");
	$unionid=$myinfo['union_id'];
	$unionpos=$myinfo['union_pos'];
    $union = sql_fetch_one("select `name`,`leader` from sys_union where id='$unionid'");
    if(($unionid<=0)||($unionpos==0)||($unionpos<3))
    {
        throw new Exception($GLOBALS['addUnionRelation']['you_are_not_chief']);
    }
    else if ($unionid == $target)
    {
    	throw new Exception($GLOBALS['addUnionRelation']['cant_contact_with_oneself']);
    }
	$targetunion=sql_fetch_one("select * from `sys_union` where id='$target' limit 1");
	if(empty($targetunion))
	{
		throw new Exception($GLOBALS['addUnionRelation']['target_union_not_exist']);
	}
	
	$oldRelation=sql_fetch_one("select *,unix_timestamp() as nowtime from mem_union_relation where type = 0 and unionid='$target' and target='$unionid'");
	
	// 对方根本没申请，就不要自作多情了
	if(empty($oldRelation)) {
		throw new Exception($GLOBALS['addUnionRelation']['target_union_dont_request']);
	}
	
	if($accept == 1) //接受
	{
		// 建立双边关系
		sql_query("insert into `sys_union_relation` (`type`,`unionid`,`target`,time) values ('0','$unionid','$target',unix_timestamp()) on duplicate key update `type`='0',time=unix_timestamp()");
		sql_query("insert into `sys_union_relation` (`type`,`unionid`,`target`,time) values ('0','$target','$unionid',unix_timestamp()) on duplicate key update `type`='0',time=unix_timestamp()");
		
		// 把申请删掉
		sql_query("delete from mem_union_relation where type=0 and unionid=$target and target=$unionid");
		
		// 信件通知对方盟内高官，包括 盟主 副盟主  长老
		$receivers=sql_fetch_rows("select uid from sys_user where union_id='$target' and union_pos>=1 and union_pos<=3");
		foreach($receivers as $receiver)
		{
			$msg = sprintf($GLOBALS['addUnionRelation']['friendly_mail_content'],$union['name']);
			sendSysMail($receiver['uid'],$GLOBALS['addUnionRelation']['friendly_mail_title'],$msg);
			
			// 插入nb的信息提示
			$nowtime = sql_fetch_one_cell("select unix_timestamp()");
			$alarminfo = sprintf($GLOBALS['addUnionRelation']['friendly_accept_alarm_info_BtoA'],$union['name']);
			sql_query("insert into sys_info_alarm(`uid`,`type`,`info`,`time`) values('$receiver[uid]',0,'$alarminfo','$nowtime')");
		}
		
		// 信件通知本方盟内高官，包括 盟主 副盟主  长老
		$receivers=sql_fetch_rows("select uid from sys_user where union_id='$unionid' and union_pos>=1 and union_pos<=3");
		foreach($receivers as $receiver)
		{
			$msg = sprintf($GLOBALS['addUnionRelation']['friendly_mail_content'],$targetunion['name']);
			sendSysMail($receiver['uid'],$GLOBALS['addUnionRelation']['friendly_mail_title'],$msg);
			
			// 插入nb的信息提示
			$nowtime = sql_fetch_one_cell("select unix_timestamp()");
			$alarminfo = sprintf($GLOBALS['addUnionRelation']['friendly_accept_alarm_info_AtoB'],$targetunion['name']);
			sql_query("insert into sys_info_alarm(`uid`,`type`,`info`,`time`) values('$receiver[uid]',0,'$alarminfo','$nowtime')");
		}
		
		// 联盟事件
		$msgAToB = sprintf($GLOBALS['addUnionRelation']['friendly_union_message_A_to_B'],$targetunion['name']);
		addUnionEvent($unionid,EVT_RELATION_FRIEND,$msgAToB);
		$msgBToA = sprintf($GLOBALS['addUnionRelation']['friendly_union_message_B_to_A'],$union['name']);	
		addUnionEvent($target,EVT_RELATION_FRIEND,$msgBToA);
		 /*
		 * 平台接口
		 */
		PUSH_SNS_UNION_MSG("AddUnionDiplomacyEvent",$unionid,$targetunion['name'],$GLOBALS['addUnionRelation']['friendly']);
		
	} else {//拒绝
		// 把申请删掉
		sql_query("delete from mem_union_relation where type=0 and unionid=$target and target=$unionid");
		
		// 信件通知对方盟内高官，包括 盟主 副盟主  长老
		$receivers=sql_fetch_rows("select uid from sys_user where union_id='$target' and union_pos>=1 and union_pos<=3");
		foreach($receivers as $receiver)
		{
			$msg = sprintf($GLOBALS['addUnionRelation']['friendly_reject_mail_content_BtoA'],$union['name']);
			sendSysMail($receiver['uid'],$GLOBALS['addUnionRelation']['friendly_mail_title'],$msg);
			
			// 插入nb的信息提示
			$nowtime = sql_fetch_one_cell("select unix_timestamp()");
			$alarminfo = sprintf($GLOBALS['addUnionRelation']['friendly_reject_alarm_info_BtoA'],$union['name']);
			sql_query("insert into sys_info_alarm(`uid`,`type`,`info`,`time`) values('$receiver[uid]',0,'$alarminfo','$nowtime')");
		}
		
		// 信件通知本方盟内高官，包括 盟主 副盟主  长老
		$receivers=sql_fetch_rows("select uid from sys_user where union_id='$unionid' and union_pos>=1 and union_pos<=3");
		foreach($receivers as $receiver)
		{
			$msg = sprintf($GLOBALS['addUnionRelation']['friendly_reject_mail_content_AtoB'],$targetunion['name']);
			sendSysMail($receiver['uid'],$GLOBALS['addUnionRelation']['friendly_mail_title'],$msg);
			
			// 插入nb的信息提示
			$nowtime = sql_fetch_one_cell("select unix_timestamp()");
			$alarminfo = sprintf($GLOBALS['addUnionRelation']['friendly_reject_alarm_info_AtoB'],$targetunion['name']);
			sql_query("insert into sys_info_alarm(`uid`,`type`,`info`,`time`) values('$receiver[uid]',0,'$alarminfo','$nowtime')");
		}
		
		// 联盟事件
		$msgAToB = sprintf($GLOBALS['addUnionRelation']['friendly_reject_union_message_A_to_B'],$targetunion['name']);
		addUnionEvent($unionid,EVT_RELATION_FRIEND,$msgAToB);
		$msgBToA = sprintf($GLOBALS['addUnionRelation']['friendly_reject_union_message_B_to_A'],$union['name']);	
		addUnionEvent($target,EVT_RELATION_FRIEND,$msgBToA);
	}	
	$param = array();
	return getRequestList($uid, $param);
}

function removeUnionRelation($uid,$param)
{
	$type=array_shift($param);
	if($type < 0 || $type > 2) {
		throw new Exception($GLOBALS['removeUnionRelation']['no_this_diplomacy']);
	}
	$target=array_shift($param);
	
	$myinfo = sql_fetch_one("select name,union_id,union_pos from sys_user where uid='$uid'");
	$unionid=$myinfo['union_id'];
	$unionpos=$myinfo['union_pos'];
    $union = sql_fetch_one("select `name`,`leader` from sys_union where id='$unionid'");
    // 盟主 副盟主 有权限，长老也不行
    if(($unionid<=0)||($unionpos==0)||($unionpos<3))
    {
        throw new Exception($GLOBALS['removeUnionRelation']['you_are_not_chief']);
    }
    else if ($unionid == $target)
    {
    	throw new Exception($GLOBALS['removeUnionRelation']['cant_contact_with_oneself']);
    }
	$targetunion=sql_fetch_one("select * from `sys_union` where id='$target' limit 1");
	if(empty($targetunion))
	{
		throw new Exception($GLOBALS['removeUnionRelation']['target_union_not_exist']);
	}
	
	//上次成功更改外交状态的时间
	$oldRelation=sql_fetch_one("select *,unix_timestamp() as nowtime from sys_union_relation where unionid='$unionid' and target='$target'");

	if(!empty($oldRelation) && $oldRelation['time']>$oldRelation['nowtime']-900)
	{
		$remainTime=MakeTimeLeft(900+$oldRelation['time']-$oldRelation['nowtime']);
		throw new Exception(sprintf($GLOBALS['removeUnionRelation']['too_frequency'],$remainTime));
	}
	
	if(!empty($oldRelation) && $oldRelation['type'] != $type)
	{
		if($type == 0) {
			$relation = $GLOBALS['removeUnionRelation']['friendly'];
		} else if($type == 1) {
			$relation = $GLOBALS['removeUnionRelation']['neutral'];
		} else if($type == 2) {
			$relation = $GLOBALS['removeUnionRelation']['hostile'];
		}
		$msg = sprintf($GLOBALS['removeUnionRelation']['have_done'],$relation);
		throw new Exception($msg);
	}
	
	if($type==0) //删除友好关系
	{
		// 变成中立的
		sql_query("update `sys_union_relation` set type=1, time=unix_timestamp() where `unionid`='$unionid' and `target`=$target and `type`='0'");
		// 对方也自动删掉了
		sql_query("update `sys_union_relation` set type=1, time=unix_timestamp() where `unionid`='$target' and `target`=$unionid and `type`='0'");
		
		// 信件通知对方盟内所有成员
		$receivers=sql_fetch_rows("select uid from sys_user where union_id='$target'");
		foreach($receivers as $receiver)
		{
			$msg = sprintf($GLOBALS['removeUnionRelation']['cancel_friendly_mail_content_BtoA'],$union['name'],$union['name']);
			sendSysMail($receiver['uid'],$GLOBALS['removeUnionRelation']['cancel_friendly_mail_title'],$msg);
			
			// 插入nb的信息提示
			$nowtime = sql_fetch_one_cell("select unix_timestamp()");
			$alarminfo = sprintf($GLOBALS['removeUnionRelation']['cancel_friendly_alarm_info_BtoA'],$union['name']);
			sql_query("insert into sys_info_alarm(`uid`,`type`,`info`,`time`) values('$receiver[uid]',0,'$alarminfo','$nowtime')");
		}
		
		// 信件通知本方盟内所有成员
		$receivers=sql_fetch_rows("select uid from sys_user where union_id='$unionid'");
		foreach($receivers as $receiver)
		{
			$msg = sprintf($GLOBALS['removeUnionRelation']['cancel_friendly_mail_content_AtoB'],$targetunion['name'],$targetunion['name']);
			sendSysMail($receiver['uid'],$GLOBALS['removeUnionRelation']['cancel_friendly_mail_title'],$msg);
			
			// 插入nb的信息提示
			$nowtime = sql_fetch_one_cell("select unix_timestamp()");
			$alarminfo = sprintf($GLOBALS['removeUnionRelation']['cancel_friendly_alarm_info_AtoB'],$targetunion['name']."");
			sql_query("insert into sys_info_alarm(`uid`,`type`,`info`,`time`) values('$receiver[uid]',0,'$alarminfo','$nowtime')");
		}
		
		// 联盟事件
		$msgAToB = sprintf($GLOBALS['removeUnionRelation']['cancel_friendly_union_message_B_to_A'],$union['name']);
		addUnionEvent($target,EVT_RELATION_FRIEND,$msgAToB);
		$msgBToA = sprintf($GLOBALS['removeUnionRelation']['cancel_friendly_union_message_A_to_B'],$targetunion['name']."");	
		addUnionEvent($unionid,EVT_RELATION_FRIEND,$msgBToA);
	}
	else if($type==1) //删除中立关系
	{
		//不给删
	}
	else if($type==2) //删除敌对关系
	{
		// 变成中立的,对方不自动删
		sql_query("update `sys_union_relation` set type=1, time=unix_timestamp() where `unionid`='$unionid' and `target`=$target and `type`='2'");
		
		$oneside = sql_fetch_one_cell("select count(*) from sys_union_relation where unionid=$target and target=$unionid and type=2");
		
		// 信件通知对方盟内所有成员
		$receivers=sql_fetch_rows("select uid from sys_user where union_id='$target'");
		foreach($receivers as $receiver)
		{
			if($oneside == 1) {
				$msg = sprintf($GLOBALS['removeUnionRelation']['cancel_hostile_mail_content_BtoA'],$union['name'],$union['name']);
			} else {
				$msg = sprintf($GLOBALS['removeUnionRelation']['cancel_hostile_mail_content_BtoA_allclear'],$union['name'],$union['name']);
			}
			sendSysMail($receiver['uid'],$GLOBALS['removeUnionRelation']['cancel_hostile_mail_title'],$msg);
			
			// 插入nb的信息提示
			$nowtime = sql_fetch_one_cell("select unix_timestamp()");
			$alarminfo = sprintf($GLOBALS['removeUnionRelation']['cancel_hostile_alarm_info_BtoA'],$union['name']."");
			sql_query("insert into sys_info_alarm(`uid`,`type`,`info`,`time`) values('$receiver[uid]',0,'$alarminfo','$nowtime')");
		}
		
		// 信件通知本方盟内所有成员
		$receivers=sql_fetch_rows("select uid from sys_user where union_id='$unionid'");
		foreach($receivers as $receiver)
		{
			if($oneside == 1) {
				$msg = sprintf($GLOBALS['removeUnionRelation']['cancel_hostile_mail_content_AtoB'],$targetunion['name'],$targetunion['name']);
			} else {
				$msg = sprintf($GLOBALS['removeUnionRelation']['cancel_hostile_mail_content_AtoB_allclear'],$targetunion['name'],$targetunion['name']);
			}
			sendSysMail($receiver['uid'],$GLOBALS['removeUnionRelation']['cancel_hostile_mail_title'],$msg);
			
			// 插入nb的信息提示
			$nowtime = sql_fetch_one_cell("select unix_timestamp()");
			$alarminfo = sprintf($GLOBALS['removeUnionRelation']['cancel_hostile_alarm_info_AtoB'],$targetunion['name']."");
			sql_query("insert into sys_info_alarm(`uid`,`type`,`info`,`time`) values('$receiver[uid]',0,'$alarminfo','$nowtime')");
		}
		
		// 联盟事件
		$msgAToB = sprintf($GLOBALS['removeUnionRelation']['cancel_hostile_union_message_B_to_A'],$union['name']);
		addUnionEvent($target,EVT_RELATION_FRIEND,$msgAToB);
		$msgBToA = sprintf($GLOBALS['removeUnionRelation']['cancel_hostile_union_message_A_to_B'],$targetunion['name']);	
		addUnionEvent($unionid,EVT_RELATION_FRIEND,$msgBToA);
	}
	
	$param2 = array();
	$param2[] = $type;
	return getUnionRelation($uid, $param2);
}

//得到联盟势力范围
function getUnionArea($unionid) {
	$areas = sql_fetch_rows("select cid from sys_city c join sys_user u on c.uid=u.uid where c.type=2 and u.union_id=$unionid");
	$ret = array();
	foreach($areas as $area) {
		$cid = $area['cid'];
		$wid = cid2wid($cid);
		$provincejun = sql_fetch_one("select concat(province, '_', jun) as provincejun from mem_world where wid=$wid limit 1");
		$ret[] = $provincejun; 
	}
	return $ret;	
}

//得到本盟势力范围
function getSelfUnionArea($uid, $param) // yellow
{
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	$union = sql_fetch_one("select * from sys_union where id=$unionid");
	if(empty($union)) {
		throw new Exception($GLOBALS['getUnionArea']['no_this_union']);
	}
	$ret = array();
	$ret[] = getProvinceCityOwner();
	$ret[] = getUnionArea($unionid);
	return $ret;
}

//得到友盟势力范围
function getFriendlyUnionArea($uid, $param)//green
{
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	$union = sql_fetch_one("select * from sys_union where id=$unionid");
	if(empty($union)) {
		throw new Exception($GLOBALS['getUnionArea']['no_this_union']);
	}
	$friendlyUnions = sql_fetch_rows("select target from sys_union_relation where unionid=$unionid and type=0");
	$ret = array();
	if(empty($friendlyUnions)) {
		return $ret;
	}
	foreach($friendlyUnions as $friendlyUnion) {
		$targetid = $friendlyUnion['target'];
		$count = sql_fetch_one_cell("select count(*) from sys_union_relation where unionid=$targetid and target=$unionid and type=0");
		if($count < 1) continue;
		$area = getUnionArea($targetid);
		$ret = array_merge($ret, $area);
	}
	$result = array();
	$result[] = getProvinceCityOwner();
	$result[] = $ret;
	return $result;
}

//得到敌盟势力范围
function getHostileUnionArea($uid, $param)// red
{
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	$union = sql_fetch_one("select * from sys_union where id=$unionid");
	if(empty($union)) {
		throw new Exception($GLOBALS['getUnionArea']['no_this_union']);
	}
	$hostileUnions = sql_fetch_rows("select target from sys_union_relation where unionid=$unionid and type=2");
	$ret = array();
	if(empty($hostileUnions)) {
		return $ret;
	}
	foreach($hostileUnions as $hostileUnion) {
		$targetid = $hostileUnion['target'];
//		$count = sql_fetch_one_cell("select * from sys_union_relation where unionid=$targetid and target=$unionid and type=0");
//		if($count < 1) continue;
		$area = getUnionArea($targetid);
		$ret = array_merge($ret, $area);
	}
	$result = array();
	$result[] = getProvinceCityOwner();
	$result[] = $ret;
	return $result;
}

//得到其他联盟势力范围
function getOtherUnionArea($uid, $param)// blue
{
	$unionid = intval(array_shift($param));
	$union = sql_fetch_one("select * from sys_union where id=$unionid");
	if(empty($union)) {
		throw new Exception($GLOBALS['getUnionArea']['no_this_union']);
	}
	$ret = array();
	$ret[] = $union['name'];
	$ret[] = getProvinceCityOwner();
	$ret[] = getUnionMarkCity($uid);
	$ret[] = "blue";
	$ret[] = $unionid;
	$ret[] = getUnionArea($unionid);
	return $ret;
}

//得到其他玩家城池
function getOtherCityByName($uid, $param) //blue
{
	$name = array_shift($param);
	if(mb_strlen($name,"utf-8")==0)
	{
		throw new Exception($GLOBALS['searchcityname']['enter_target_name']);
	}
	else if(mb_strlen($name,"utf-8")>8)
	{
		throw new Exception($GLOBALS['searchcityname']['union_name_tooLong']);
	}
	$name=addslashes($name);
	$citys = sql_fetch_rows("select aa.*,bb.name as username ,cc.name as unionname from sys_city aa left join sys_user bb on aa.uid =bb.uid left join sys_union cc on bb.union_id=cc.id where aa.name like '%$name%'  limit 6");
	
	if(empty($citys)) {
		throw new Exception($GLOBALS['searchcityname']['target_union_not_exist']);
	}
	
	$param[] = $citys;
	return $param;
}
function getOtherUnionAreaByName($uid, $param) //blue
{
	$name = array_shift($param);
	if(mb_strlen($name,"utf-8")==0)
	{
		throw new Exception($GLOBALS['addUnionRelation']['enter_target_name']);
	}
	else if(mb_strlen($name,"utf-8")>8)
	{
		throw new Exception($GLOBALS['addUnionRelation']['union_name_tooLong']);
	}
	$name=addslashes($name);
	$union = sql_fetch_one("select * from sys_union where name='$name' limit 1");
	
	if(empty($union)) {
		throw new Exception($GLOBALS['addUnionRelation']['target_union_not_exist']);
	}
	
	$unionid = $union['id'];
	$param2 = array();
	$param2[] = $unionid;
	return getOtherUnionArea($uid, $param2);
}

//得到州城所有者列表
function getProvinceCityOwner()
{
	$ret = array();
	$cities = sql_fetch_rows("select c.province, c.type, c.cid, c.name as cityname, u.uid, u.union_id, u.name as username from sys_city c left join sys_user u on c.uid=u.uid where type>=3 and type<5 order by c.province");
	foreach($cities as $city) {
		$unionid = $city['union_id'];
		$unionname = sql_fetch_one_cell("select name from sys_union where id=$unionid");
		$city['unionname'] = "";
		if(empty($unionname) || $unionname == "") {
			if($unionid == 0 && $city['uid'] <= 1000) {
				$city['unionname'] = $city['username'];
			}
		} else {
			$city['unionname'] = $unionname;
		}
		$ret[] = $city;
	}
	return $ret;
}

function getUnionMarkCity($uid)
{
	//删一下过期的标记
	clearMark($uid);
	
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	$marks = sql_fetch_rows("select * from sys_union_mark where unionid=$unionid");
	if(empty($marks)) $marks = array();
	return $marks;
}

//得到友盟 敌盟信息
function getSelfUnionInfo($uid, $param) 
{
	$ret = array();
	$ret[] = getProvinceCityOwner();
	$ret[] = getUnionMarkCity($uid);
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	//友盟信息
	$friendlyUnions = sql_fetch_rows("select target from sys_union_relation where unionid=$unionid and type=0");
	$result = array();
	if(!empty($friendlyUnions)) {
		foreach($friendlyUnions as $friendlyUnion) {
			$targetid = $friendlyUnion['target'];
			$count = sql_fetch_one_cell("select count(*) from sys_union_relation where unionid=$targetid and target=$unionid and type=0");
			if($count < 1) continue;
			$friendlyU = sql_fetch_one("select * from sys_union where id=$targetid");
			$cids = sql_fetch_rows("select c.cid as cid from sys_city c join sys_user u on u.uid=c.uid join sys_union un on u.union_id=un.id where c.type=2 and un.id=$targetid");
			if(empty($cids)) {
				$friendlyU['areas'] = "";
			} else {
				$str = "";
				foreach($cids as $cid) {
					$wid = cid2wid($cid['cid']);
					$provincejun = sql_fetch_one_cell("select concat(province, '_', jun) from mem_world where wid=$wid");
					if($str == "") {
						$str .= $provincejun;
					} else {
						$str .=",".$provincejun;
					}
				}
				$friendlyU['areas'] = $str;
			}
			$result[] = $friendlyU;
		}
	}
	$ret[] = $result;
	//敌盟信息
	$hostileUnions = sql_fetch_rows("select target from sys_union_relation where unionid=$unionid and type=2");
	$result = array();
	if(!empty($hostileUnions)) {
		foreach($hostileUnions as $hostileUnion) {
			$targetid = $hostileUnion['target'];
			$hostileU = sql_fetch_one("select * from sys_union where id=$targetid");
			$cids = sql_fetch_rows("select c.cid as cid from sys_city c join sys_user u on u.uid=c.uid join sys_union un on u.union_id=un.id where c.type=2 and un.id=$targetid");
			if(empty($cids)) {
				$hostieU['areas'] = "";
			} else {
				$str = "";
				foreach($cids as $cid) {
					$wid = cid2wid($cid['cid']);
					$provincejun = sql_fetch_one_cell("select concat(province, '_', jun) from mem_world where wid=$wid");
					if($str == "") {
						$str .= $provincejun;
					} else {
						$str .=",".$provincejun;
					}
				}
				$hostileU['areas'] = $str;
			}
			$result[] = $hostileU;
		}
	}
	$ret[] = $result;

	//本盟信息
	$union = sql_fetch_one("select * from sys_union where id=$unionid limit 1");
	if(!empty($union)) {
		$cids = sql_fetch_rows("select c.cid as cid from sys_city c join sys_user u on u.uid=c.uid join sys_union un on u.union_id=un.id where c.type in (2,3) and un.id=$unionid");
		if(empty($cids)) {
			$union['areas'] = "";
		} else {
			$str = "";
			foreach($cids as $cid) {
				$wid = cid2wid($cid['cid']);
				$provincejun = sql_fetch_one_cell("select concat(province, '_', jun) from mem_world where wid=$wid");
				if($str == "") {
					$str .= $provincejun;
				} else {
					$str .=",".$provincejun;
				}
			}
			$union['areas'] = $str;
		}
	}
	$ret[] = $union;
	//本人城池信息	
	$usercitys = sql_fetch_rows("select * from sys_city where uid=$uid  order by type desc");
	$ret[] = $usercitys;
	return $ret;	
}

function getSelfUnionInfo2($uid, $param)
{
	return getSelfUnionInfo($uid, $param);	
}

//获得联盟事件
function getUnionEvent($uid,$param)
{
	$page=array_shift($param);
	$unionid=sql_fetch_one_cell("select `union_id` from `sys_user` where `uid`='$uid'");
	if(empty($unionid))
	{
		throw new Exception($GLOBALS['getUnionEvent']['not_in_union']);
	}
	$evtCount=sql_fetch_one_cell("select count(*) from `sys_union_event` where `unionid`='$unionid'");
	$pageCount=ceil($evtCount/EVENT_PAGE_CPP);
	if($page>=$pageCount)
    {
    	$page=$pageCount-1;
    }
    if($page<0)
    {
    	$page=0;
    	$pageCount=0;
    }
    $ret=array();
    $ret[]=$pageCount;
    $ret[]=$page;
    if($evtCount>0)
    {
    	$start=$page*EVENT_PAGE_CPP;
    	$ret[]=sql_fetch_rows("select * from `sys_union_event` where `unionid`='$unionid' order by `evttime` desc limit $start,".EVENT_PAGE_CPP);
    }
    else
    {
    	$ret[]=array();
    }
    return $ret;
}
//添加联盟事件
function addUnionEvent($unionid,$type,$content)
{
	$content=addslashes($content);
	sql_insert("insert into `sys_union_event` (`unionid`,`type`,`content`,`evttime`) values ('$unionid','$type','$content',unix_timestamp())");
	sql_insert("insert into `mem_union_event` (`unionid`,`type`,`content`,`evttime`) values ('$unionid','$type','$content',unix_timestamp())");
}

//设置某个成员权限

function setUnionProvicy($uid,$param)
{
	$target=intval(array_shift($param));
	$position=array_shift($param);
	$position=addslashes($position);
	
	$myinfo=sql_fetch_one("select name,union_id,union_pos from sys_user where uid='$uid'");
	$unionid=$myinfo['union_id'];
	$unionpos=$myinfo['union_pos'];
	$targetinfo=sql_fetch_one("select name,union_id,union_pos from sys_user where uid='$target'");
	$targetunionpos=$targetinfo['union_pos'];
	if(($unionid<0)||($unionid!=$targetinfo['union_id'])||($unionpos==0)||(($targetunionpos!=0)&&($unionpos>=$targetunionpos)))
	{
//		throw new Exception("a");
		throw new Exception($GLOBALS['setUnionProvicy']['not_authorizied']);
	}
	else if(($position!=0)&&($position<=$unionpos))
	{
//		throw new Exception("b");
		throw new Exception($GLOBALS['setUnionProvicy']['not_authorizied']);
	}
	sql_query("update sys_user set union_pos='$position' where uid='$target'");
	
	if($position==0) $targetname=$GLOBALS['setUnionProvicy']['union_memeber'];
	else if ($position==2) $targetname=$GLOBALS['setUnionProvicy']['union_vice_chief'];
	else if ($position==3) $targetname=$GLOBALS['setUnionProvicy']['union_elder'];
	else if ($position==4) $targetname=$GLOBALS['setUnionProvicy']['union_official'];
    if($position!=$targetinfo['union_pos'])
    {
	    if(($position==0)||($targetinfo['union_pos']>0&&$position>$targetinfo['union_pos']))
	    {
	    	$msg = sprintf($GLOBALS['setUnionProvicy']['descend_level'],$myinfo[name],$targetinfo[name],$targetname);
		    addUnionEvent($unionid,EVT_PROVICY,$msg);
	    }
	    else
	    {
	    	$msg = sprintf($GLOBALS['setUnionProvicy']['upgrade_level'],$myinfo[name],$targetinfo[name],$targetname);	    	
		    addUnionEvent($unionid,EVT_PROVICY,$msg);
	    }
    }
	$ret=array();
	$ret[]=$target;
	$ret[]=$position;
	return $ret;
}
//联盟官员辞职
function demissionUnion($uid,$param)
{
	$myinfo=sql_fetch_one("select name,`union_id`,union_pos from `sys_user` where `uid`='$uid'");
	$unionid=$myinfo['union_id'];
	$unionpos=$myinfo['union_pos'];
	if(empty($unionid))
	{
		throw new Exception($GLOBALS['demissionUnion']['not_in_union']);
	}
	else if($unionpos==0)
	{
		throw new Exception($GLOBALS['demissionUnion']['no_any_position']);
	}
	$union=sql_fetch_one("select leader from sys_union where `id`='$unionid'");
	if(empty($union))
	{
		throw new Exception($GLOBALS['demissionUnion']['union_dissmissed']);
	}
	if($uid==$union['leader'])
	{
		throw new Exception($GLOBALS['demissionUnion']['chief_cant_resign']);
	}
	sql_query("update sys_user set union_pos=0 where uid='$uid'");
	if($unionpos==0) $targetname = $GLOBALS['demissionUnion']['union_memeber'];
	else if ($unionpos==2) $targetname = $GLOBALS['demissionUnion']['union_vice_chief'];
	else if ($unionpos==3) $targetname = $GLOBALS['demissionUnion']['union_elder'];
	else if ($unionpos==4) $targetname = $GLOBALS['demissionUnion']['union_official'];
	
	$msg = sprintf($GLOBALS['demissionUnion']['add_union_event'],$myinfo[name],$targetname);
	addUnionEvent($unionid,EVT_DEMISSION,$msg);
	 /*
	 * 平台接口
	 */
	PUSH_SNS_UNION_MSG("AddUnionOfficialResignEvent",$unionid,$myinfo[name],$targetname);
	return array();
}

//获取联盟军情列表

function getUnionReport($uid,$param)
{
	$page=array_shift($param);
	$unionid=sql_fetch_one_cell("select `union_id` from `sys_user` where `uid`='$uid'");
	if(empty($unionid))
	{
		throw new Exception($GLOBALS['getUnionReport']['not_in_union']);
	}
	$reportCount=sql_fetch_one_cell("select count(*) from `sys_union_report` where `unionid`='$unionid'");
	$pageCount=ceil($reportCount/UNION_REPORT_PAGE_CPP);
	if($page>=$pageCount)
    {
    	$page=$pageCount-1;
    }
    if($page<0)
    {
    	$page=0;
    	$pageCount=0;
    }
    $ret=array();
    $ret[]=$pageCount;
    $ret[]=$page;
    if($reportCount>0)
    {
    	$start=$page*UNION_REPORT_PAGE_CPP;
    	$ret[]=sql_fetch_rows("select `id`,`type`,`enemy`,`time`,`description` from `sys_union_report` where `unionid`='$unionid' order by `id` desc limit $start,".UNION_REPORT_PAGE_CPP);
    }
    else
    {
    	$ret[]=array();
    }
    return $ret;
}

function getUnionReportDetail($uid,$param)
{
	$id=intval(array_shift($param));
	$unionid=sql_fetch_one_cell("select `union_id` from `sys_user` where `uid`='$uid'");
	if(empty($unionid))
	{
		throw new Exception($GLOBALS['getUnionReportDetail']['not_in_union']);
	}
    $ret=array();
   	$caution=sql_fetch_one("select type, origincid,origincity,happencid,happencity,time,description from sys_union_report where id='$id' and `unionid`='$unionid'");
   	if(empty($caution))
   	{
   		throw new Exception($GLOBALS['getUnionReportDetail']['report_not_found']);
   	}
   	else
   	{
   		$ret[]=$caution;
   	}
    return $ret;
}

/*
 * 
 * SNS平台接口：：联盟
 * 
 */
function PUSH_SNS_UNION_MSG($method)
{
	if (defined("PASSTYPE")){
		try{
			$groupID=sql_fetch_one_cell("select sns_flag from sys_union where id=".func_get_arg(1));
			if($groupID==0) return;
		    require_once 'game/agents/AgentServiceFactory.php';
			$service=AgentServiceFactory::getInstance($uid);
			switch($method){
				case "AddUnionMemberEvent":
					$unionid=func_get_arg(1);
					$flag=func_get_arg(2);
					$service->addUnionMemberEvent($unionid,$flag,$groupID);
					break;
				case "AddUnionDiplomacyEvent":
					$unionid=func_get_arg(1);
					$targetid=func_get_arg(2);
					$relation=func_get_arg(3);
					$service->addUnionDiplomacyEvent($unionid,$targetid,$relation);
					break;
				case "AddTransferUnionChiefEvent":
					$unionid=func_get_arg(1);
					$name1=func_get_arg(2);
					$name2=func_get_arg(3);
					$service->addTransferUnionChiefEvent($unionid,$name1,$name2,$groupID);
					break;
				case "AddUnionOfficialResignEvent":
					$unionid=func_get_arg(1);
					$name=func_get_arg(2);
					$jobname=func_get_arg(3);
					$service->addUnionOfficialResignEvent($unionid,$name,$jobname);
					break;
				case "AddUnionDeclareWarEvent":
					$unionid=func_get_arg(1);
					$uname1=func_get_arg(2);
					$uname2=func_get_arg(3);
					$service->addUnionDeclareWarEvent($uname1,$uname2);
					break;
			}
		}catch(Exception $e){
			try{
				file_put_contents("./agents/log/interface-error.log",date("Y-m-d H:i:s",time())." || ".$e->getMessage()."\n",FILE_APPEND);
			}catch(Exception $err){
				
			}
		}
    }
}


//发联盟奖励
function giveRewardsByUnion($union_id,$rewards){
	foreach($rewards as $reward){
		$count=$reward["count"];
		$sort=$reward["sort"];
		$type=$reward["type"];
		$comma=",";
		$name="";
		if($type=="0") continue;
		if($sort=="1"){
			if($type=="9"){
				sql_query("update sys_user set prestige=prestige+'$count',warprestige=warprestige+'$count' where union_id='$union_id'");
				$name=$GLOBALS['act']['prestige'];
			}
		}else if($sort=="2"){
			sql_query("insert into sys_goods (uid,gid,count) select uid,'$type','$count' from sys_user where union_id='$union_id' on duplicate key update count=count+'$count'");
			sql_query("insert into log_goods (uid,gid,count,time,type) select uid,'$type','$count',unix_timestamp(),6 from sys_user where union_id='$union_id'");
			$name=sql_fetch_one_cell("select name from cfg_goods where gid='$type'");
		}else if($sort=="5"){
			sql_query("insert into sys_things (uid,tid,count) select uid,'$type','$count' from sys_user where union_id='$union_id' on duplicate key update count=count+'$count'");
			sql_query("insert into log_things (uid,tid,count,time,type) select uid,'$type','$count',unix_timestamp(),6 from sys_user where union_id='$union_id'");
			$name=sql_fetch_one_cell("select name from cfg_things where tid='$type'");
		}else if($sort=="6"){
			for($j=0;$j<$count;$j++){
				sql_query("insert into sys_user_armor (uid,armorid,hp,hp_max,hid) select uid,'$type',1000,100,0 from sys_user where union_id='$union_id'");
			}
			sql_query("insert into log_armor (uid,tid,count,time,type) select uid,'$type','$count',unix_timestamp(),6 from sys_user where union_id='$union_id'");
			$name=sql_fetch_one_cell("select name from cfg_armor where id='$type'");
		}
		$get_rewards=$get_rewards.$name.$count.$comma;
	}
	return $get_rewards;
}
//联盟累计获得
function addUnionGains($actid,$uid,$boxdetail,$count)
{	
	if($count<=0) return "";
	$union_id=sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
	
	if ($union_id=="0") return "";
	
	$get_rewards="";
	$boxdetail_id=$boxdetail["id"];
	sql_query("insert into cfg_act_union_gains (detail_id,union_id,count) values ('$boxdetail_id','$union_id','$count') on duplicate key update count=count+'$count'");
	$curCount=sql_fetch_one_cell("select `count` from cfg_act_union_gains where detail_id='$boxdetail_id' and union_id='$union_id'");
	$tasks=sql_fetch_rows("select * from cfg_act_union_task where detail_id='$boxdetail_id' order by id desc");
	foreach($tasks as $task){
		$tempCount=$task["count"];
		if($curCount>=$task["count"] && ($curCount-$count)<$task["count"]){
			//达到联盟领奖条件，自动发奖
			$rewards=sql_fetch_rows("select * from cfg_act_union_reward where tid=".$task["id"]);
			$get_rewards=giveRewardsByUnion($union_id,$rewards);
			break;
		}
	}
	$type=$boxdetail["type"];
	$name=sql_fetch_one_cell("select name from cfg_things where tid='$type'");
	$content=$GLOBALS['act']['mail_head'].sprintf($GLOBALS['act']['mail_union_gains_content'],$name,$curCount);
	
	if($get_rewards==""){
		sendSysMailToUser($uid,$GLOBALS['act']['mail_union_gains_title'],$content);
	}else{
		$addMsg=sprintf($GLOBALS['act']['mail_union_reward_content'],$get_rewards);
		$content=$content.$addMsg;
		sendSysMailByUnion($union_id,$GLOBALS['act']['mail_union_reward_title'],$content);
		//联盟奖励公告
		$union_name=sql_fetch_one_cell("select name from sys_union where id='$union_id'");
		$msg=sprintf($GLOBALS['act']['inform_union_gains'],$union_name,$name,$curCount);
		sql_query("insert into sys_inform (type,inuse,starttime,endtime,`interval`,scrollcount,color,msg) values (0,1,unix_timestamp(),unix_timestamp()+300,1800,1,49151,'$msg')");
	}
	return $name.$count;
}

function canUserLeaveUnion($uid){//看看用户是不是献帝密诏时期的盟主，如果是就不能修改盟主的位置
	
	//$unionid=sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	//$leader=sql_fetch_one_cell("select `leader` from sys_union where id=$unionid");//盟主uid
	
//	if(empty($unionid)){//如果没有联盟就不刷给他任务
//		return false;
//	}
	
	$leadergood=sql_fetch_one_cell("select count from sys_goods where uid=$uid and gid=161501");
	if(!empty($leadergood)){	//看盟主是不是有献帝诏书,有就不刷
		throw new Exception($GLOBALS['xiandimizhao']['can_not_leave_union']);
	}
	
	$nowtime=sql_fetch_one_cell("select unix_timestamp()");
	$bufs=sql_fetch_rows("select * from mem_user_buffer where uid='$uid' and endtime>'$nowtime' and buftype=161501");
	if(!empty($bufs)){//看联盟是不是在使用buffer，使用中也不刷
		throw new Exception($GLOBALS['xiandimizhao']['can_not_leave_union']);
	}
	
	$leadertask=sql_fetch_one_cell("select 1 from sys_user_task where uid=$uid and tid=112005 and state=0");//如果盟主有献帝密诏任务就不刷了
	if(!empty($leadertask)){	
		throw new Exception($GLOBALS['xiandimizhao']['can_not_leave_union']);
	}
}
/*
 * 升级联盟职位
 */
function upgradePosition($uid,$param) {
	$unionId = array_shift($param);
	//检验用户和联盟对应关系
	$union = sql_fetch_one("select union_id,union_pos,name from sys_user where uid=$uid");
	if (empty($union) || $union['union_id'] != $unionId)
		throw new Exception($GLOBALS['acceptApply']['union_not_exist']);
	if (sql_check("select 1 from sys_user where union_pos>=4 and uid=$uid")) {
		throw new Exception($GLOBALS['upgradePosition']['max_level']);
	}
	
	if(sql_check("select 1 from mem_union_remove where uid='$uid' and unionid='$unionId'"))
    {
    	throw new Exception($GLOBALS['union']['user_can_not_upgrade']);
    }
	
	//检验联盟相应等级最大人数和捐献限制
	$userDonate = sql_fetch_one("select * from sys_user_donate where unionid=$unionId and uid=$uid");
	$maxHouseLevel = sql_fetch_one_cell("select level from sys_union_building where bid=2 and unionid=$unionId");
	
	$maxMemberNum=0;
	$flag = true;
	$nextPosition = $union['union_pos']+1;
	switch ($nextPosition) {
		case 1:
			if ($userDonate['donate'] < 100) $flag = false;
			$maxMemberNum = 3+floor((10+$maxHouseLevel)/10); 
			break;
		case 2:
			if ($userDonate['donate'] < 500) $flag = false;
			$maxMemberNum = 2+floor((10+$maxHouseLevel)/20); 
			break;
		case 3:
			if ($userDonate['donate'] < 1000) $flag = false;
			$maxMemberNum = 1+floor((10+$maxHouseLevel)/30); 
			break;
		case 4:
			if ($userDonate['donate'] < 5000) $flag = false;
			$maxMemberNum = 1+floor((10+$maxHouseLevel)/30); 
			break;
		case 5:
			if ($userDonate['donate'] < 10000) $flag = false;
			$maxMemberNum = 1; 
			break;
		default:
			$flag = false;
			break;
	}
	if (!$flag)
		throw new Exception($GLOBALS['upgradePosition']['not_enogh']);
		
	//检验是否升级，升级要超过下一级的玩家最小捐献量
	$minDonate = sql_fetch_one_cell("select min(a.donate) from sys_user_donate a,sys_user b where a.uid=b.uid and a.unionid=b.union_id and b.union_id={$union['union_id']} and b.union_pos='$nextPosition'");
	if(!empty($minDonate))
	{
		if ($userDonate['donate']<= $minDonate)
			throw new Exception($GLOBALS['upgradePosition']['not_enogh']); 
		//检验是否需要挤下一个玩家
		$minUid = sql_fetch_one_cell("select a.uid from sys_user_donate a,sys_user b  where a.uid=b.uid and a.unionid = b.union_id and a.donate=$minDonate and a.unionid=$unionId and b.union_pos=$nextPosition limit 1");
		$curMemberNum = sql_fetch_one_cell("select count(*) from sys_user where union_id=$unionId and union_pos='$nextPosition'");
		if ($curMemberNum >= $maxMemberNum) {
			sql_query("update sys_user set union_pos=GREATEST(0,union_pos-1) where uid=$minUid");
		}
	}	
	sql_query("update sys_user set union_pos=LEAST(5,union_pos+1) where uid=$uid");
	//发送联盟通知，返回升级结果
	$msg = sprintf($GLOBALS['upgradePosition'][$union['union_pos']+1],$union['name']);
	addUnionEvent($unionId,EVT_CHANGE_NAME,$msg);

	return array($msg);
}
/**
 * 取消联盟开除事件
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 */
function cancelKickMember($uid,$param) {
	$targetUid = intval(array_shift($param));
	//检验下是否有权限
	$user = sql_fetch_one("select union_id,union_pos from sys_user where uid=$uid");
	if (empty($user) || $user['union_pos'] < 4) {
		throw new Exception($GLOBALS['kickMember']['not_elder']);
	}
	$targetUser = sql_fetch_one("select union_id,union_pos from sys_user where uid=$targetUid");
	if ($user['union_id'] != $targetUser['union_id']) {
		throw new Exception($GLOBALS['kickMember']['not_cancel']);
	}
	sql_query("delete from mem_union_remove where uid=$targetUid and unionid={$targetUser['union_id']}");
}

function getMaxDonate($uid,$unionid) {
	$totalNum=0;
	//计算玩家可捐赠数量
	$heroLevel = sql_fetch_one_cell("select level from sys_city_hero where uid='$uid' and herotype='1000'");
	$buildingLevel = sql_fetch_one_cell("select level from sys_union_building where unionid='$unionid' and bid='1'");
	$totalNum = intval($heroLevel/5)*3 + intval($buildingLevel/5);
	return $totalNum;
}
function donateGoods($uid,$param) {
	$bid = intval(array_shift($param));
	$count = intval(array_shift($param));
	$user = sql_fetch_one("select name,union_id,union_pos from sys_user where uid=$uid");
	if (empty($user['union_id']))
		throw new Exception($GLOBALS['leaveUnion']['you_belongTo_none_union']); 
	if (!sql_check("select 1 from sys_union where id={$user['union_id']}")) 
		throw new Exception($GLOBALS['leaveUnion']['you_belongTo_none_union']);
	if ($bid>16 || $bid <0) {
		throw new Exception($GLOBALS['upgradeBuilding']['not_exist']);
	}
	if ($count <1) {
		throw new Exception($GLOBALS['sendCommand']['command_exception']);
	}
	$openBidArr = array(1,2,9);
	if(!in_array($bid,$openBidArr))throw new Exception($GLOBALS['goods']['not_available']);
	
	$myGoodsNum = sql_fetch_one_cell("select count from sys_goods where uid=$uid and gid=19210");
	if ($myGoodsNum < $count) {
		throw new Exception($GLOBALS['duihuan']['not_enogh']);
	}
	$building = sql_fetch_one("select * from sys_union_building where bid=$bid and unionid={$user['union_id']}");
	$curLevel=$building['level'];
	if(intval($curLevel)>=100)throw new Exception($GLOBALS['donate']['max_level']);
	
	$totalNum = getMaxDonate($uid,$user['union_id']);
	$donatedNum = sql_fetch_one_cell("select curnum from sys_user_donate where uid=$uid and unionid={$user['union_id']}");
	if ($count+$donatedNum > $totalNum) throw new Exception($GLOBALS['donate']['too_much']);  
		
	$num = $count*100;
	sql_query("insert into sys_user_donate(uid,unionid,donate) values('$uid','{$user['union_id']}','$num') on duplicate key update donate=donate+$num");
	sql_query("update sys_user_donate set curnum=curnum+$count,time=unix_timestamp() where uid=$uid and unionid={$user['union_id']}");
	sql_query("update sys_union set donate=donate+$num where id={$user['union_id']}");
	reduceGoods($uid,19210,$count);
	
	$upgradeLevel=0;
	//$preNum=0;
	$needNum = sql_fetch_one_cell("select num from cfg_union_building_upgrade where level={$curLevel}+1 and bid='$bid'");
	$curNum = sql_fetch_one_cell("select curnum from sys_union_building where unionid={$user['union_id']} and bid='$bid'");
	$totalDonateNum = intval($curNum)+$count;
	while ($totalDonateNum>=$needNum) {
		$upgradeLevel++;
		//$preNum = $needNum;
		$curLevel = $building['level'] +$upgradeLevel;		
		if(intval($curLevel)<100){
			$needNum = sql_fetch_one_cell("select num from cfg_union_building_upgrade where level={$curLevel}+1 and bid='$bid'");
		}else{
			$needNum = $totalDonateNum+1;  //到100级强制退出
		}
	}
	sql_query("update sys_union_building set curnum=curnum+$count where bid=$bid and unionid={$user['union_id']}");
	
	
	//发送联盟通知，返回升级结果
	$msg = sprintf($GLOBALS['donate']['donate'],$user['name'],$count);
	addUnionEvent($user['union_id'],EVT_CHANGE_NAME,$msg);
	if ($upgradeLevel > 0) {
		sql_query("update sys_union_building set level=level+$upgradeLevel where bid=$bid and unionid={$user['union_id']}");
		//发送联盟通知，返回升级结果
		$name = sql_fetch_one_cell("select name from cfg_union_building_upgrade where bid='$bid' limit 1");
		$msg = sprintf($GLOBALS['upgradeBuilding']['upgrade'],$name,$building['level']+$upgradeLevel);
		addUnionEvent($user['union_id'],EVT_CHANGE_NAME,$msg);
	}
	$msg = sprintf($GLOBALS['donate']['can_donate'],$totalNum - $count-$donatedNum);
	$ret = array();	
	$ret[] = $msg;
	return $ret;
}

function getMaxDuihuan($uid,$unionid) {
	if (!sql_check("select 1 from sys_user where uid=$uid and union_id=$unionid")) return 0;
	//计算玩家可兑换次数
	$heroLevel = sql_fetch_one_cell("select level from sys_city_hero where uid='$uid' and herotype='1000'");
	$totalNum = 3+intval($heroLevel/5);
	return $totalNum;
}

function exchangeUnionGoods($uid,$param) {
	$fromids = array_shift($param);
	$count = intval(array_shift($param));
	$flag = intval(array_shift($param));//fromid;0:gid;1:sid
	if ($count < 0) throw new Exception($GLOBALS['sendCommand']['command_exception']);
	
	if ($count != count($fromids) && $flag == 1) throw new Exception($GLOBALS['sendCommand']['command_exception']);
	
	$user = sql_fetch_one("select name,union_id,union_pos from sys_user where uid=$uid");
	//验证对换次数是否已经超出
	if($flag==1 || intval($fromids[0]) != 19302)   //商票不进行最大次数检测
	{	
		$info = sql_fetch_one("select * from sys_user_donate where uid=$uid and unionid={$user['union_id']}");
		//如果$info为空。添加一个初始化数据
		if(empty($info))
		{
			sql_query("insert into sys_user_donate(`uid`,`unionid`,duihuan) values('$uid','{$user['union_id']}',0)");
		}
		$hasDuihuan = sql_fetch_one_cell("select duihuan from sys_user_donate where uid=$uid and unionid={$user['union_id']}");
		$maxDuihuan = getMaxDuihuan($uid,$user["union_id"]);
		if ($hasDuihuan+$count > $maxDuihuan) {
			throw new Exception($GLOBALS['donate']['too_much']);
		}
	}	
	if(checkMarketLevel($user['union_id'],$flag,$fromids))throw new Exception($GLOBALS['donate']['level_low']);

	//验证是否拥有足够的物品
	if ($flag == 1 && $count < 1)
		throw new Exception($GLOBALS['duihuan']['not_enogh']);
	if ($flag == 1 ) {
		foreach ($fromids as $fromid) {
			$fromid = intval($fromid);
			if (!sql_check("select 1 from sys_user_armor where uid=$uid and sid=$fromid")){
				$armorName = sql_fetch_one_cell("select a.name from cfg_armor a,sys_user_armor b where a.id=b.armorid and b.sid=$fromid");
				$msg = sprintf($GLOBALS['duihuan']['not_your_armor'],$armorName);
				throw new Exception($msg);
			}
		}
	}
	
	$need=1;
	$get=1;
	if ($flag == 0) {
		$fromid = intval(array_shift($fromids));
		switch ($fromid) {
			case 35 :
				$need = $count;
				$get = $count;
				break;
			case 36 :
				$need = $count*5;
				$get = $count*2;
				break;
			case 37 :
				$need = $count*10;
				$get = $count*3;
				break;
			case 38 :
				$need = $count*10;
				$get = $count*5;
				break;
			case 152 :
				$need = $count*100;
				$get = $count*5;
				break;
			case 10960 :
				$need = $count*100;
				$get = $count*5;
				break;
			case 19302 :
				$need = $count;
				$get = $count*10;
				break;
			default:
				$need=0;
				$get=0;
				break;
		}
		$tmpCount = sql_fetch_one_cell("select count from sys_goods where uid=$uid and gid=$fromid");
		if (empty($tmpCount) || $tmpCount < $need) {
			throw new Exception($GLOBALS['duihuan']['not_enogh']);
		}
		reduceGoods($uid,$fromid,$need);
		addGoods($uid,19210,$get,1);
	}
	if ($flag == 1) {
		//验证下是否是蓝色物品
		$need=1;
		$get=5;
		foreach ($fromids as $fromid) {
			$fromid = intval($fromid);
			$armorType = sql_fetch_one_cell("select a.type from cfg_armor a,sys_user_armor b where a.id=b.armorid and b.sid=$fromid");
			if ($armorType != 4)
				throw new Exception($GLOBALS['duihuan']['not_blue_armor']);
			if (sql_check("select 1 from sys_user_armor where hid>0 and sid=$fromid")) {
				throw new Exception($GLOBALS['duihuan']['not_blue_armor']);
			}
			sql_query("delete from sys_user_armor where sid=$fromid");
			addGoods($uid,19210,$get,1011);
		}
	}
	if(!($flag==0&&$fromid==19302))   //商票兑换不影响每日兑换上线
	{
		sql_query("update sys_user_donate set duihuan=duihuan+$count,time=unix_timestamp() where uid=$uid and unionid={$user['union_id']}");
	}	
	$ret = array();
	$ret[] = getUnionStoreInfo($uid);
	$ret[] = $GLOBALS['unionreward']['recieve_reward'];	
	return $ret;
}

function getUnionReward($uid,$param) {
	if (empty($uid)) return array();
	$unionid = intval(array_shift($param));
	
	if (!sql_check("select 1 from sys_user where uid=$uid and union_id=$unionid")) {
		throw new Exception($GLOBALS['unionreward']['not_in_union']);
	}
	if (!sql_check("select 1 from sys_union_reward where uid=$uid and unionid=$unionid")) {
		throw new Exception($GLOBALS['unionreward']['not_in_rank']);
	}
	if (sql_check("select 1 from sys_union_reward where uid=$uid and unionid=$unionid and time>1")) {
		throw new Exception($GLOBALS['unionreward']['has_got_reward']);
	}
	$rewards = sql_fetch_rows("select a.gid,a.count from cfg_union_reward a,sys_union_reward b where a.rank=b.rank and a.pos=b.pos and b.uid=$uid and b.unionid=$unionid");
	foreach ($rewards as $reward) {
		addGoods($uid,$reward['gid'],$reward['count'],1);
	}
	sql_query("update sys_union_reward set time=unix_timestamp() where uid=$uid and unionid=$unionid");
	
	$ret = array();
	$ret[] = loadUnionRewardData($uid);
	$ret[] = $GLOBALS['unionreward']['recieve_reward'];
	return $ret;
}
function loadUnionRewardData($uid)
{
	$rankInfo = sql_fetch_rows("select rank,name from rank_union order by rank asc limit 10");
	$userInfo = sql_fetch_one("select r.rank,u.union_pos from rank_union r,sys_user u where r.`uid`=u.`union_id` and u.uid='$uid' limit 1");
	
	$rewardInfo = sql_fetch_one("select * from sys_union_reward where uid='$uid' limit 1");
	if(!empty($rewardInfo))
	{
		$userInfo['getrewardtime'] = $rewardInfo['time'];
	}else 
	{
		$userInfo['getrewardtime'] = 0;
	}
	
	$ret = array();
	$ret[] = $rankInfo;
	$ret[] = $userInfo;
	return $ret;
}
function loadUnionChiefCityInfo($uid) {
	if (empty($uid)) return array();
	$ret=array();
	$user = sql_fetch_one("select union_id,union_pos from sys_user where uid=$uid");
	$buildInfo=sql_fetch_rows("select * from cfg_union_building_upgrade a,sys_union_building b where a.bid=b.bid and a.level=b.level and b.unionid={$user['union_id']} order by a.open desc");
	$ret[0]=$user;
	$ret[1]=$buildInfo;
	
	return $ret;
}

function donateGoodsInitData($uid) {
	if (empty($uid)) return array();
	$ret = array();
	$info = array();
	//每天0点后第一次刷进行清理
	$user = sql_fetch_one("select union_id,union_pos from sys_user where uid=$uid");
	//$curHour = sql_fetch_one_cell("select substr(now(),12,2)");
	//$curHour = intval($curHour);
	$day= sql_fetch_one_cell("select unix_timestamp(substr(now(),1,10))");
	if (sql_check("select 1 from sys_user_donate where uid=$uid and unionid={$user['union_id']} and time<$day")) {
		sql_query("update sys_user_donate set duihuan=0,curnum=0 where uid=$uid and unionid={$user['union_id']}");
	}
	
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	$maxNum = getMaxDonate($uid,$unionid);
	$heroLevel = sql_fetch_one_cell("select level from sys_city_hero where herotype=1000 and uid=$uid");
	$goodsNum = sql_fetch_one_cell("select count from sys_goods where gid=19210 and uid=$uid");
	$info = sql_fetch_one("select * from sys_user_donate where uid=$uid and unionid=$unionid");
	//如果$info为空。添加一个初始化数据
	if(empty($info))
	{
		sql_query("insert into sys_user_donate(`uid`,`unionid`) values('$uid','$unionid')");
		$info = sql_fetch_one("select * from sys_user_donate where uid=$uid and unionid=$unionid");
	}
	$info['heroLevel']=$heroLevel;
	$info['goodsNum']=intval($goodsNum)<=0?0:intval($goodsNum);
	$info['maxNum']=$maxNum;
	
	$ret[]=$info;
	return $ret;
}


function updatePositionInitData($uid) {
	if (empty($uid)) return array();
	$donateInfo = array();
	$unionInfo = array();
	$ret = array();
	$unionInfo = sql_fetch_one("select union_id,union_pos from sys_user where uid=$uid");
	$donateInfo = sql_fetch_one("select * from sys_user_donate where uid=$uid and unionid={$unionInfo['union_id']}");
	$donateInfo = array_merge($unionInfo,$donateInfo);
	$nextPosition = $unionInfo['union_pos']+1;
	$minDonate = sql_fetch_one_cell("select min(a.donate) from sys_user_donate a,sys_user b where a.uid=b.uid and a.unionid=b.union_id and b.union_id={$unionInfo['union_id']} and b.union_pos='$nextPosition'");
	$needDonate = 0;
	switch($nextPosition)
	{
		case 1:
			$needDonate=100;
			break;
		case 2:
			$needDonate=500;
			break;
		case 3:
			$needDonate=1000;
			break;
		case 4:
			$needDonate=5000;
			break;
		default:break;
	}
	$donateInfo['nextLevel'] = $minDonate>=$needDonate?$minDonate+1:$needDonate;
	$ret[] = $donateInfo;
	return $ret;
}

function getUnionStoreInfo($uid) {
	if (empty($uid)) return array();
	$ret=array();
	//$info=array();
	//每天0点后第一次刷进行清理
	//$curHour = sql_fetch_one_cell("select substr(now(),12,2)");
	//$curHour = intval($curHour);
	$user = sql_fetch_one("select union_id,union_pos from sys_user where uid=$uid");
	$day= sql_fetch_one_cell("select unix_timestamp(substr(now(),1,10))");
	if (sql_check("select 1 from sys_user_donate where uid=$uid and unionid={$user['union_id']} and time<$day")) {
		sql_query("update sys_user_donate set duihuan=0,curnum=0 where uid=$uid and unionid={$user['union_id']}");
	}
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	$allGoods = sql_fetch_rows("select * from cfg_union_store order by level asc");
	//获取下当前联盟建筑中市场的等级
	$markLevel = sql_fetch_one_cell("select level from sys_union_building where unionid='$unionid' and bid='9' limit 1");
	if(empty($markLevel))$markLevel=1;
	$ret[]=$markLevel;
	$hasDuihuan = sql_fetch_one_cell("select duihuan from sys_user_donate where uid='$uid' and unionid='$unionid'");
	$leavingDuihuan = (getMaxDuihuan($uid,$unionid)-$hasDuihuan)>0?(getMaxDuihuan($uid,$unionid)-$hasDuihuan):0;
	$ret[] = $leavingDuihuan;
	foreach ($allGoods as $goods) {
		//取下物品的配置信息		
		$goodInfo = sql_fetch_one("select * from cfg_goods where gid={$goods['gid']} limit 1");
		$count=sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid={$goods['gid']}");
		$goodInfo['count'] = intval($count)<=0?0:$count;
		$goods['goodInfo']=$goodInfo;
		$ret[]=$goods;
	}
	return $ret;
}


function getKickLeavingTime($uid)
{
	$unionid = sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");
	$removeInfos = sql_fetch_rows("select * from mem_union_remove where unionid='$unionid'");	
	if(empty($removeInfos)) return array();
	
	$ret = array();
	foreach ($removeInfos as $removeInfo)
	{
		$oneInfo = array();
		$oneInfo['doName'] = sql_fetch_one_cell("select name from sys_user where uid={$removeInfo['douid']}");
		$oneInfo['name'] = sql_fetch_one_cell("select name from sys_user where uid={$removeInfo['uid']}");
		$now = sql_fetch_one_cell("select unix_timestamp()");
		$oneInfo['lefttime'] = ($removeInfo['time']-$now)>0?($removeInfo['time']-$now):0;
		$oneInfo['kickuid'] = $removeInfo['uid'];
		$ret[] = $oneInfo;
	}
	return $ret;
}
function getUnionMemberMaxCount($unionid)
{
	 //成员上限和联盟的官府建筑等级有关
    $govermentLevel = sql_fetch_one_cell("select level from sys_union_building where unionid='$unionid' and bid='2' limit 1");
    if(empty($govermentLevel))$govermentLevel=1;
    $maxMemberCount = $govermentLevel>=50?60:$govermentLevel+10;
    
    return $maxMemberCount;
}
function checkMarketLevel($unionid,$flag,$gids)   //true为市场等级没达到条件
{
	//联盟市场等级
	$level = sql_fetch_one_cell("select level from sys_union_building where unionid='$unionid' and bid='9' limit 1");
	if($flag==1)  //装备
	{
		if(intval($level)<70)return true;
	}else    //物品
	{
		$gid = intval(array_shift($gids));
		if(($gid==35||$gid==19302)&&intval($level)<1)
		{
			return true;
		}else if($gid==36&&intval($level)<10)
		{
			return true;
		}else if($gid==37&&intval($level)<20)
		{
			return true;
		}else if($gid==38&&intval($level)<30)
		{
			return true;
		}else if($gid==152&&intval($level)<40)
		{
			return true;
		}else if($gid==10960&&intval($level)<50)
		{
			return true;
		}
	}
	return false;
}

