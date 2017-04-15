<?php
require_once("./interface.php");
require_once("./utils.php");

define("RANK_PAGE_CPP",10);


function getRankTableName($type)
{
	if($type=="user")
	{
		$tablename="rank_user";
	}
	else if($type=="union")
	{
		$tablename="rank_union";
	}
	else if($type=="union_city")
	{
		$tablename="rank_union_city";
	}
	else if($type=="hero_level")
	{
		$tablename="rank_hero";
	}
	else if($type=="hero_command")
	{
		$tablename="rank_hero_command";
	}
	else if($type=="hero_affairs")
	{
		$tablename="rank_hero_affairs";
	}
	else if($type=="hero_bravery")
	{
		$tablename="rank_hero_bravery";
	}
	else if($type=="hero_wisdom")
	{
		$tablename="rank_hero_wisdom";
	}
	else if ($type=="city_people")
	{
		$tablename="rank_city";
	}
	else if($type=="city_type")
	{
		$tablename="rank_city_type";
	}
	else if ($type=="jungong")
	{
		$tablename="rank_jungong";
	}
	else if ($type=="juanxian")
	{
		$tablename="rank_juanxian";
	}
	else if ($type=="qinwang")
	{
		$tablename="rank_qinwang";
	}
	else if ($type=="gongpin")
	{
		$tablename="rank_gongpin";
	}
	else if ($type=="jungong_union")
	{
		$tablename="rank_jungong_union";
	}
	else if ($type=="juanxian_union")
	{
		$tablename="rank_juanxian_union";
	}
	else if ($type=="qinwang_union")
	{
		$tablename="rank_qinwang_union";
	}
	else if ($type=="gongpin_union")
	{
		$tablename="rank_gongpin_union";
	}
	else if ($type=="military")
	{
		$tablename="rank_military";
	}
	else if ($type=="military_attack")
	{
		$tablename="rank_military_attack";
	}
	else if ($type=="military_defence")
	{
		$tablename="rank_military_defence";
	}
	else if ($type=="rich")
	{
		$tablename="rank_rich";
	}
	else if ($type=="rich_day")
	{
		$tablename="rank_rich_day";
	}
	else if ($type=="rich_month")
	{
		$tablename="rank_rich_month";
	}
	else if ($type=="battle_total"){
		$tablename = "rank_battle_total";
	}
	else if ( $type=="battle_week"){
		$tablename = "rank_battle_week";
	}
	else if ( $type=="battle_day"){
		$tablename = "rank_battle_day";
	}
	else if ( $type=="achivement")
	{		
		$tablename = "rank_achivement";
	}
	else if ( $type=="achivement_point")
	{		
		$tablename = "rank_achivement_point";
	}
	else if ( $type=="achivement")
	{		
		$tablename = "rank_achivement";
	}
	else if ( $type=="achivement_point")
	{		
		$tablename = "rank_achivement_point";
	}
	else if ($type=="activity")
	{
		$tablename="rank_activity";
	}
	else if($type=="activity_today")
	{
		$tablename="rank_activity_today";
	}
	else if($type=="activity_yesterday")
	{
		$tablename="rank_activity_yesterday";
	}
	else if($type=="KingRankTotal")
	{
		$tablename="rank_user_level";
	}
	return $tablename;
}

function getRank($start,$type)
{
	$mem_state=10;
	$tablename=getRankTableName($type);

	$updateTime=sql_fetch_one_cell("select `value` from `mem_state` where `state`='$mem_state'");
	$rowCount=sql_fetch_one_cell("select count(*) from `$tablename`");
	$pageCount=ceil($rowCount/RANK_PAGE_CPP);
	$page=floor(($start+RANK_PAGE_CPP-1)/RANK_PAGE_CPP);
	if($page>=$pageCount)
	{
		$page=$pageCount-1;
	}
	if($rowCount<=0)
	{
		$page=0;
		$pageCount=0;
	}
	$ret = array();
	$ret[]=$updateTime;
	if($rowCount>0&&$start<$rowCount)
	{
		$ret[]=$pageCount;
		$ret[]=$page;
		$ret[]=$type;
		$ret[]=sql_fetch_rows("select * from `$tablename` where `rank`>'$start' limit ".RANK_PAGE_CPP);		
	}
	else
	{
		$ret[]=0;
		$ret[]=0;
		$ret[]=$type;
		$ret[]=array();
	}
	return $ret;
}

function getPageRank($uid,$param)
{
	$page=array_shift($param);
	$type=array_shift($param);
	return getRank($page*RANK_PAGE_CPP,$type);
}

function getRankRank($uid,$param)
{
	$rank=array_shift($param)-1;
	$rank=floor(($rank/RANK_PAGE_CPP))*RANK_PAGE_CPP;
	$type=array_shift($param);
	return getRank($rank,$type);
}

function getNameRank($uid,$param)
{
	$name=array_shift($param);
	$name=addslashes($name);
	$type=array_shift($param);
	$mem_state=10;
	$tablename=getRankTableName($type);
	$rankArray=sql_fetch_rows("select * from `$tablename` where `name` like '%$name%' order by rand() limit ".RANK_PAGE_CPP);
	$updateTime=sql_fetch_one_cell("select `value` from `mem_state` where `state`='$mem_state'");
	$ret=array();
	$ret[]=$updateTime;
	$ret[]=1;
	$ret[]=0;
	$ret[]=$type;
	$ret[]=$rankArray;
	return $ret;
}

function getMyRank($uid,$param)
{
	$name=array_shift($param);
	$name=addslashes($name);
	$type=array_shift($param);
	$tablename=getRankTableName($type);
	$rank=sql_fetch_one_cell("select `rank` from `$tablename` where `name`='$name'");
	if($rank >=1 && $rank <=1000) {
		if ($type=="military")
		{
			completeTaskWithTaskid($uid, 316);
		}
		else if ($type=="military_attack")
		{
			completeTaskWithTaskid($uid, 317);
		}
		else if ($type=="military_defence")
		{
			completeTaskWithTaskid($uid, 318);
		}
		else if ($type == "user") 
		{
			completeTaskWithTaskid($uid, 315);
		}
		
	}
	if($rank<=0)
	{
		$rank=1;
	}
	$rank=floor((($rank-1)/RANK_PAGE_CPP))*RANK_PAGE_CPP;
	return getRank($rank,$type);
}

function getRankConfig($uid,$param)
{
	$ret=array();
	$rankType=array_shift($param);
	if($rankType=="KingRankTotal")  //君主将排行榜
	{
		$ret = getKingRankConfig($uid);
		return $ret;
	}
	$act=sql_fetch_one("select *,unix_timestamp() as now from cfg_activity_rank order by endtime desc limit 1");
	if(!empty($act)){
		$ret[]=$act;
		if($rankType=="activity"){
			$ended=($act['endtime']<$act['now']&&$act['reward_endtime']>$act['now']);
			$getable=false;
			if($ended){
				$info=sql_fetch_one("select l.rank,l.reward from cfg_activity_rank_reward r,log_activity_rank l where l.uid='$uid' and r.actid='$act[actid]' and r.actid=l.actid and l.rank >=r.minrank and l.rank<=r.maxrank");
				if(!empty($info)&&$info['reward']==0){
					$getable=true;
				}
			}
			$ret[]=$getable?1:0;
		}else if($rankType=="activity_today"){
			$ret[]=0;
		}else if($rankType=="activity_yesterday"){
			$day=floor(($act['now']-$act['starttime'])/86400);
			$ended=($day>0&&($act['now']-$act['endtime']<86400));
			$getable=false;
			if($ended){
				$info=sql_fetch_one("select l.rank,l.reward from cfg_activity_rank_reward_day r,log_activity_rank_day l where l.uid='$uid' and r.actid='$act[actid]' and l.day='$day' and r.day='$day' and r.actid=l.actid and l.rank >=r.minrank and l.rank<=r.maxrank");
				if(!empty($info)&&$info['reward']==0){
					$getable=true;
				}
			}
			$ret[]=$getable?1:0;
		}
	}
	return $ret;
}

function getKingRankConfig($uid)
{
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$intervalTime=14*24*3600;
	$openServerTime = sql_fetch_one_cell("select value from mem_state where state='6'");
	$actStartTime = 0;
	//全服更新结束时间点为新老服判断时间点
	$oldServerSignTime = sql_fetch_one_cell("select unix_timestamp('2014-4-3 15:00:00')");
	if($openServerTime<$oldServerSignTime)  //老服
	{
		$actStartTime = $oldServerSignTime;
	}else 
	{
		$actStartTime = $openServerTime;
	}
	$rewardStarttime = $actStartTime+$intervalTime;     //可以领取的开始时间为开服后的14天
	$rewardEndtime = $actStartTime+$intervalTime+7*24*3600;     //领取的截至时间为开服后的21天
	
	$ret = array();
	$getEnabled = 0;
	if($now>=$rewardStarttime&&$now<=$rewardEndtime)
	{
		$rankInfo = sql_fetch_one("select * from log_user_level where uid='$uid'");
		if(!empty($rankInfo)&&intval($rankInfo['reward'])!=1)
		{
			$getEnabled=1;
		}
	}
	$ret[] = $getEnabled;
	return $ret;
}

function getKingRankReward($uid)
{
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$intervalTime=14*24*3600;
	$openServerTime = sql_fetch_one_cell("select value from mem_state where state='6'");
		
	$actStartTime = 0;
	//全服更新结束时间点为新老服判断时间点
	$oldServerSignTime = sql_fetch_one_cell("select unix_timestamp('2014-4-3 15:00:00')");
	if($openServerTime<$oldServerSignTime)  //老服
	{
		$actStartTime = $oldServerSignTime;
	}else 
	{
		$actStartTime = $openServerTime;
	}
	$rewardStarttime = $actStartTime+$intervalTime;     //可以领取的开始时间为开服后的14天
	$rewardEndtime = $actStartTime+$intervalTime+7*24*3600;     //领取的截至时间为开服后的21天
	
	$ret = array();
	if($now>=$rewardStarttime&&$now<=$rewardEndtime)
	{
		$rankInfo = sql_fetch_one("select * from log_user_level where uid='$uid'");
		if(empty($rankInfo))
		{
			throw new Exception($GLOBALS['activity']['not_in_valid_rank']);
		}
		if(intval($rankInfo['reward'])==1)
		{
			throw new Exception($GLOBALS['activity']['reward_already_get']);
		}
		if(intval($rankInfo['rank'])>100)
		{
			throw new Exception($GLOBALS['king']['out_rank_range']);
		}
		$reward = sql_fetch_one_cell("select reward from cfg_activity_rank_reward where `actid`='1000000' and `minrank`<='$rankInfo[rank]' and `maxrank`>='$rankInfo[rank]'");
		if(empty($reward))
		{
			throw new Exception($GLOBALS['kingReward']['not_find_data']);
		}
		sql_query("update log_user_level set reward='1',time='$now' where uid='$uid'");
		$ret[]=1;
		$ret[]=parseAndAddReward($uid,$reward,5,5,5,65);
	}else 
	{
		throw new Exception($GLOBALS['kingReward']['can_not_get']);	
	}
	return $ret;
}

function getRankReward($uid,$param)
{
	$type=array_shift($param);
	$ret=array();
	if($type=="KingRankTotal")
	{
		$ret = getKingRankReward($uid);
		return $ret;
	}
	$act=sql_fetch_one("select *,unix_timestamp() as now from cfg_activity_rank where starttime<unix_timestamp() and reward_endtime>unix_timestamp()");
	
	if(empty($act)){
		$ret[]=0;
		$ret[]=$GLOBALS['activity']['no_rank_activity'];
		return $ret;
	}
	$actid=$act['actid'];
	if($type=="activity"){
		if($act['endtime']>$act['now']){ //活动还没有结束
			$ret[]=0;
			$ret[]=$GLOBALS['activity']['not_in_valid_rank'];
			return $ret;
		}
		$reward=sql_fetch_one("select r.*,rw.reward from rank_activity r,cfg_activity_rank_reward rw where r.uid='$uid' and r.rank>=rw.minrank and r.rank<=rw.maxrank and rw.actid='$actid'");
		if(empty($reward)){
			$ret[]=0;
			$ret[]=$GLOBALS['activity']['not_in_valid_rank'];
			return $ret;
		}
		if(sql_check("select 1 from log_activity_rank where actid='$actid' and uid='$uid' and reward=1"))
		{
			$ret[]=0;
			$ret[]=$GLOBALS['activity']['reward_already_get'];
			return $ret;
		}
		sql_query("insert into log_activity_rank (actid,rank,uid,reward,time) values ('$actid','$reward[rank]','$uid','1',unix_timestamp()) on duplicate key update reward='1',time=unix_timestamp()");
		
	}else if($type=="activity_yesterday"){
		$day=floor(($act['now']-$act['starttime'])/86400);
		$reward=sql_fetch_one("select r.*,rw.reward from rank_activity_yesterday r,cfg_activity_rank_reward_day rw where r.uid='$uid' and r.rank>=rw.minrank and r.rank<=rw.maxrank and rw.actid='$actid' and rw.day='$day'");
		if(empty($reward)){
			$ret[]=0;
			$ret[]=$GLOBALS['activity']['not_in_valid_rank'];
			return $ret;
		}
		if(sql_check("select 1 from log_activity_rank_day where actid='$actid' and uid='$uid' and day='$day' and reward=1"))
		{
			$ret[]=0;
			$ret[]=$GLOBALS['activity']['reward_already_get'];
			return $ret;
		}
		sql_query("insert into log_activity_rank_day (actid,day,rank,uid,reward,time) values ('$actid','$day','$reward[rank]','$uid','1',unix_timestamp()) on duplicate key update reward='1',time=unix_timestamp()");
		
	}else{
		$ret[]=0;
		$ret[]=$GLOBALS['activity']['invalid_param'];
		return $ret;
	}
	$ret2[]=1;
	$ret2[]=parseAndAddReward($uid,$reward['reward'],4,4,4,60);
	return $ret2;	
}

function getHuangJinProgress($uid,$param)
{
	$type=intval(array_shift($param));

	if($type==0) //���
	{
		return sql_fetch_rows("select tid,name,maxvalue,curvalue,maxvalue-curvalue as remainvalue from huangjin_progress where `group`=11000 order by tid");
	}
	else if($type==1) //���ױ�
	{
		return sql_fetch_rows("select tid,name,maxvalue,curvalue,maxvalue-curvalue as remainvalue from huangjin_progress where `group`=12000 order by tid");
	}
	else if($type==2) //����گ
	{
		return sql_fetch_rows("select tid,name,maxvalue,curvalue,maxvalue-curvalue as remainvalue from huangjin_progress where `group`=13000 order by tid");
	}
	else if($type==3) //��Ʒ¼
	{
		return sql_fetch_rows("select tid,name,maxvalue,curvalue,maxvalue-curvalue as remainvalue from huangjin_progress where `group`=14000 order by tid");
	}
}
function getDongZhuoProgress($uid,$param)
{
	$type=intval(array_shift($param));
	if($type==0) 
	{
		return sql_fetch_rows("select tid,name,maxvalue,curvalue,maxvalue-curvalue as remainvalue from dongzhuo_progress where `group`=15000 order by tid");
	}
	else if($type==1) 
	{
		return sql_fetch_rows("select tid,name,maxvalue,curvalue,maxvalue-curvalue as remainvalue from dongzhuo_progress where `group`=15100 order by tid");
	}
	else if($type==2) 
	{
		return sql_fetch_rows("select tid,name,maxvalue,curvalue,maxvalue-curvalue as remainvalue from dongzhuo_progress where `group`=15200 order by tid");
	}
	else if($type==3) 
	{
		return sql_fetch_rows("select tid,name,maxvalue,curvalue,maxvalue-curvalue as remainvalue from dongzhuo_progress where `group`=15300 order by tid");
	}
	else if($type==4) 
	{
		return sql_fetch_rows("select tid,name,maxvalue,curvalue,maxvalue-curvalue as remainvalue from dongzhuo_progress where `group`=15400 order by tid");
	}
	else if($type==5) 
	{
		return sql_fetch_rows("select tid,name,maxvalue,curvalue,maxvalue-curvalue as remainvalue from dongzhuo_progress where `group`=15500 order by tid");
	}
}
function getBeiZhanLuoYangProgress($uid,$param)
{
	$type=intval(array_shift($param));
	$unionid=sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	if($type==0&&$unionid>0) 
	{
		initCurUnionBeiZhanLuoYangProgress($uid);
		return sql_fetch_rows("select tid,name,maxvalue,curvalue,maxvalue-curvalue as remainvalue from luoyang_progress where union_id=$unionid order by tid");
	}else{
		throw new Exception ($GLOBALS['rankfunc']['uniontask']);
	}
}

function initCurUnionBeiZhanLuoYangProgress($uid){
	$unionid=sql_fetch_one_cell("select union_id from sys_user where uid=$uid");
	for($tid=112001;$tid<=112004;$tid++){
		$task=sql_fetch_one("select * from cfg_task where id=$tid limit 1");
		$sql="select count from cfg_task_goal where sort=203 and type=$tid";
		$taskMax=sql_fetch_one_cell($sql);
		sql_query("insert into luoyang_progress(union_id,tid,maxvalue,curvalue,`group`,`name`) values('$unionid','$tid','$taskMax',0,'$task[group]','$task[name]') on duplicate key update  curvalue=curvalue");
	}
		
}
function testAddMili(){
	for($i=0;$i<10100;$i++){
		$name="user".$i;
		$union="union".$i;
		$army=$i*100;
		$attack=$i*101;
		$defence=$i*102;
		sql_query("insert into rank_military (name,unionname,army,attack,defence) values ('$name','$union','$army','$attack','$defence') ");
	}
}

function testAddRich(){
	for($i=0;$i<10100;$i++){
		$name="user".$i;
		$union="union".$i;
		$army=$i*100;
		$attack=$i*101;
		$defence=$i*102;
		sql_query("insert into rank_rich (name,unionname,total,day,month) values ('$name','$union','$army','$attack','$defence') ");
	}
}

?>