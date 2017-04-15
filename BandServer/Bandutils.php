<?php
function sendSysMail($touid,$title,$content){
	$title = addslashes($title);
	$content = addslashes($content);
	$mid = sql_insert("insert into sys_mail_sys_content (`content`,`posttime`) values ('$content',unix_timestamp())");
	$sql="insert into sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$touid','$mid','$title','0',unix_timestamp())";
	sql_insert($sql);
	sql_query("insert into sys_alarm (`uid`,`mail`) values ('$touid',1) on duplicate key update `mail`=1");
}
function wid2cid($wid){
	$y = floor($wid / 10000) * 10 + floor((($wid % 100) / 10));
	$x = floor(($wid % 10000) / 100) * 10 + floor($wid % 10);
	return $y * 1000 + $x;
}
function cid2wid($cid){
	$y = floor($cid / 1000);
	$x = ($cid % 1000);
	return (floor($y / 10)) * 10000 + (floor($x / 10)) * 100 + ($y % 10) * 10 + ($x % 10);
}
function updateCityHeroChange($uid,$cid){
	$hero_fee=sql_fetch_one_cell("select sum(level*20+(GREATEST(affairs_base+affairs_add-90,0)+GREATEST(bravery_base+bravery_add-90,0)+GREATEST(wisdom_base+wisdom_add-90,0))*50) from sys_city_hero where herotype!=1000 and cid='$cid' and uid='$uid' and state!=5 and state!=6 and state!=9");
	$hero_fee = $hero_fee+1-1;
	sql_query("update mem_city_resource set hero_fee='$hero_fee'  where cid='$cid'");
}
function completeTaskWithTaskid($uid,$taskid){
	$goals = sql_fetch_rows("select * from cfg_task_goal where tid=$taskid");
	foreach($goals as $goal) {
		$goalid = $goal["id"];
		sql_query("replace into sys_user_goal (`uid`,`gid`) values ('$uid','$goalid')");		
	}
}
function updateCityPeopleStable($cid){
	//人口稳定值=人口上限*民心
	$people_max = sql_fetch_one_cell("select `people_max` from mem_city_resource where `cid`='$cid'");
	$city_morale = sql_fetch_one_cell("select `morale` from mem_city_resource where `cid`='$cid'");
	$people_stable = $people_max * $city_morale * 0.01;
	sql_query("update mem_city_resource set `people_stable`='$people_stable' where `cid`='$cid'");
}
function updateCityResourceAdd($cid){
	$ownercid = sql_fetch_one_cell("select ownercid from mem_world where wid=".cid2wid($cid));
	if (empty($ownercid)) $ownercid = $cid;
	if (empty($ownercid)) return;
	sql_query("update sys_city_res_add set resource_changing=1 where cid=".$ownercid);
}
function updateCityPeopleMax($cid){
	//民房 N级增长人口上限100*N
	$people_max = sql_fetch_one_cell("select sum(level*(level+1)*50) from sys_building where `cid`='$cid' and bid='5'");
	sql_query("update mem_city_resource set `people_max`='$people_max' where `cid`='$cid'");
	updateCityPeopleStable($cid);
}
function updateCityGoldMax($cid){
	$level=sql_fetch_one_cell("select level from sys_building where `cid`='$cid' and bid='6' ");
	$vw=10000;//万
	$gold_max = sql_fetch_one_cell("select level*(level+1)*500000 from sys_building where `cid`='$cid' and bid='6' ");
	if($level==11){
		$gold_max=6600*$vw;
	}else if($level==12){
		$gold_max=7800*$vw;		
	}
	else if($level==13){
		$gold_max=9100*$vw;		
	}
	else if($level==14){
		$gold_max=10500*$vw;		
	}
	else if($level==15){
		$gold_max=12000*$vw;		
	}
	sql_query("update mem_city_resource set `gold_max`='$gold_max' where `cid`='$cid'");
}
function addGoods($uid,$gid,$cnt,$type){
	if ($cnt==0) {
		return;
	}	
	if ($gid == 160052) {
		if (!sql_check("select 1 from log_qiyue where uid=$uid and gid=160052 and count<0")) {//如果没有使用过，可以掉落，但是只能还是1.
			sql_query("insert into sys_goods (uid,gid,count) values ('$uid','$gid','$cnt') on duplicate key update `count`=1");
			sql_query("insert into log_qiyue (uid,gid,count,time) values ('$uid','$gid','$cnt',unix_timestamp())");
		}
	} else {
		if($gid==0){			
			sql_query("update sys_user set gift=gift+'".$cnt."' where uid='".$uid."'");
			sql_query("insert into log_gift (uid,gid,count,time,type) values ('$uid','$gid','$cnt',unix_timestamp(),$type)");
		}else{
		    if($gid==152) $gid=888888;
			sql_query("insert into sys_goods (uid,gid,count) values ('$uid','$gid','$cnt') on duplicate key update `count`=`count`+'$cnt'");
			sql_query("insert into log_goods (uid,gid,count,time,type) values ('$uid','$gid','$cnt',unix_timestamp(),$type)");
		}
	}
}
function throwHeroToField($hero){
	$hid = $hero['hid'];
	sql_query("delete from mem_hero_blood where hid='$hid'");
	sql_query("delete from sys_hero_armor where hid='$hid'");
	sql_query("update sys_user_armor set hid=0 where hid='$hid'");
	sql_query("update sys_user_book set hid=0 where hid=$hid");//去掉将领技能书
	//把人往野地里面丢，如果没有人的话，就放在上面，如果有人的话，如果这个人也是NPC，则另外找地方，如果这个人不是NPC的话，就替代他的位置。
	$findtimes = 10;  //找十次，如果找不到的话就丢掉了
	if(isActHero($hero["herotype"]) || isCardHero($hero["herotype"])){
		$findtimes=0;
	}else if ($hero['npcid']>0)	{
		$findtimes=40;//名将多找几次
	}
	while($findtimes > 0){
		$findtimes--;
		if ($hero['npcid'] > 0) {
			$wid = sql_fetch_one_cell("select wid from mem_world where ownercid=0 and province<=13 and type > 1 and state=0 order by rand() limit 1");
		} else {
			$wid = sql_fetch_one_cell("select wid from mem_world where ownercid=0 and type > 1 and state=0 order by rand() limit 1");
		}
		$newcid = wid2cid($wid);
		$oldhero = sql_fetch_one("select * from sys_city_hero where uid=0 and cid='$newcid'");
		if (empty($oldhero)){ //该地点无人
			sql_query("update sys_city_hero set cid='$newcid',state=4,uid=0,loyalty=70 where hid=$hid");
			break;
		}
		else{    //有人
	  	  if ($oldhero['npcid'] > 0){    //也是一个NPC
			 //重新找过
				continue;
			}
			else {   //不是NPC，算他倒霉，要被砍掉
			  sql_query("update sys_city_hero set cid=$newcid,state=4,uid=0,loyalty=70 where hid=$hid");
			  sql_query("delete from sys_city_hero where hid=$oldhero[hid]");
			  sql_query("delete from mem_hero_blood where hid='$oldhero[hid]'");
  			  if(!isActHero($hero["herotype"]))
					sql_query("insert into sys_recruit_hero (`name`,`npcid`,`sex`,`face`,`cid`,`level`,`exp`,`affairs_add`,`bravery_add`,`wisdom_add`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`gold_need`,`gen_time`) values ('$oldhero[name]','$oldhero[npcid]','$oldhero[sex]','$oldhero[face]','0','$oldhero[level]','$oldhero[exp]','$oldhero[affairs_add]','$oldhero[bravery_add]','$oldhero[wisdom_add]','$oldhero[affairs_base]','$oldhero[bravery_base]','$oldhero[wisdom_base]','66',0,unix_timestamp())");
			  $troop = sql_fetch_one("select * from sys_troops where uid=0 and cid='$newcid' and hid=$oldhero[hid]");
			  if (!empty($troop)){
					sql_query("update sys_troops set hid=$hid where id=$troop[id]");
				}
				break;
			}
		}
	}
	if ($findtimes == 0){    // 十次都没有找到，砍了
	  if ($hero['npcid']>0){
			return;//名将不删
		}
	  sql_query("update sys_troops set hid=0 where hid='$hid'");
	  sql_query("delete from sys_city_hero where hid='$hid'");
  	  if((!isActHero($hero["herotype"])) || (!isCardHero($hero["herotype"]))){
			sql_query("insert into sys_recruit_hero (`name`,`npcid`,`sex`,`face`,`cid`,`level`,`exp`,`affairs_add`,`bravery_add`,`wisdom_add`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`gold_need`,`gen_time`) values ('$hero[name]','$hero[npcid]','$hero[sex]','$hero[face]','0','$hero[level]','$hero[exp]','$hero[affairs_add]','$hero[bravery_add]','$hero[wisdom_add]','$hero[affairs_base]','$hero[bravery_base]','$hero[wisdom_base]','66',0,unix_timestamp())");
		}
	}
}
function isActHero($heroType) {
	return ($heroType > 10 && $heroType != 100 && $heroType < 20000);
}
function isCardHero($heroType){
	return ($heroType>=21250 && $heroType<=24250);
}
?>