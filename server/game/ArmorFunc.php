<?php

require_once("./interface.php");
require_once("./utils.php");
require_once("./HeroFunc.php");
require_once ("./ActFunc.php");
require_once ("./TaskFunc.php");
function getArmorEmbedGoods($armors)
{
	$gid_str = "";
   for($i=0; $i<count($armors);$i++)
    {
    	$armor = $armors[$i];
    	$pearls = $armor['embed_pearls'];
    	//$gids = explode(",", $pearls);
    	if(!empty($pearls)){
    		if($gid_str == "")
    			$gid_str = $pearls;
    		else
    			$gid_str = $gid_str.",$pearls";
    	}
    }
    if($gid_str != "")
    	return sql_fetch_rows("select * from cfg_goods where gid in ($gid_str)");
    else
    	return array();
}

function getTieInfo($armors, $hid) 
{
	$ret = array();
	if($hid == -1) {
		$armorid_str = "";
		for($i=0; $i < count($armors); $i++)
		{
			$armor = $armors[$i];
			if($armorid_str == "")
				$armorid_str = $armor['id'];
			elseif (!empty($armor['id'])) 
				$armorid_str = $armorid_str.",".$armor['id'];
		}
		$armorid_str=preg_replace("/,$/u","",$armorid_str);
		$armorid_str=preg_replace("/,\\s*,/u","",$armorid_str);
		if($armorid_str == "") return $ret;
		$ret = sql_fetch_rows("select distinct t.*, 0 as armed from cfg_tie t left join cfg_armor a on t.tieid = a.tieid where a.tieid >0 and a.id in ($armorid_str)");
	} else {
		$ret = sql_fetch_rows("select t.*, count(t.tieid) as armed from sys_hero_armor ha left join sys_user_armor ua on ua.sid=ha.sid left join cfg_armor a on a.id=ua.armorid left join cfg_tie t on t.tieid=a.tieid where ha.hid=$hid and ua.hp > 0 group by t.tieid");
	}
	return $ret;
}

function getTieArmorAttribute($armors) 
{
	$ret = array();
	$armorid_str = "";
	for($i=0; $i < count($armors); $i++)
	{
		$armor = $armors[$i];
		if($armorid_str == "")
			$armorid_str = $armor['id'];
		elseif (!empty($armor['id'])) {
			$armorid_str = $armorid_str.",".$armor['id'];
		}
	}
	if($armorid_str == "") return $ret;
	$ret = sql_fetch_rows("select distinct ta.tieid, ta.value, ta.precond, ca.* from cfg_tie_attribute ta left join cfg_tie t on t.tieid = ta.tieid left join cfg_armor a on ta.tieid = a.tieid left join cfg_attribute ca on ca.attid=ta.attid where a.tieid > 0 and a.id in ($armorid_str)");
	return $ret;
}

function getDeifyAttribute($armors)
{
	$ret = array();	
	for($i=0; $i < count($armors); $i++)
	{
		$armor = $armors[$i];
		$attrs = sql_fetch_rows("select tda.*, ca.name from sys_user_tie_deify_attribute tda left join cfg_attribute ca on tda.attid=ca.attid where tda.sid=".$armor['sid']);
		if(empty($attrs)) $attrs = array();
		$ret[] = $armor['sid'];
		$ret[] = $attrs;
	}
	return $ret;
}

function getFusionAttribute($armors)
{
	$ret = array();	
	for($i=0; $i < count($armors); $i++)
	{
		$armor = $armors[$i];
		$combineAttrs = sql_fetch_rows("select concat(c.level,',',c.attr) as combineAttr from cfg_armor_level_attr c left join sys_user_armor s on s.combine_level=c.level where s.sid=".$armor['sid']);
		if(empty($combineAttrs)) 
		{
			$ret[] = 0;
			$combineAttrs = array(0,1,0,0);
		}else 
		{
			$ret[] = 1;	
		}		
		$ret[] = $armor['sid'];
		$ret[] = $combineAttrs;
	}
	return $ret;
}
function getArmorNewAttribute($armors)
{
	$ret = array();	
	for($i=0; $i < count($armors); $i++)
	{
		$armor = $armors[$i];
		$attrs = sql_fetch_rows("select aa.*, ca.name from cfg_armor_attribute aa left join cfg_attribute ca on ca.attid=aa.attid where armorid=".$armor['armorid']);
		if(empty($attrs)) $attrs = array();
		$ret[] = $armor['armorid'];
		$ret[] = $attrs;
	}
	return $ret;	
}

function loadUserArmor($uid,$param)
{
	$ret = array();
	$armor_column = sql_fetch_one_cell("select armor_column from sys_user where uid=$uid");
	$ret[]= $armor_column; //50;
    $armors = sql_fetch_rows("select * from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$uid' and a.`hid`=0 order by a.strong_level desc,a.combine_level desc");
    $ret[] = addSpecialArr($armors);
    $ret[] = getArmorNewAttribute($armors);
    $ret[] = getArmorEmbedGoods($armors);
    $ret[] = getTieInfo($armors, -1);
	$ret[] = getTieArmorAttribute($armors);
	$ret[] = getDeifyAttribute($armors);
	$ret[] = getFusionAttribute($armors);
    return $ret;
}
function loadUserActivedArmor($uid,$param)
{
	$type = array_shift($param);   //1：装备升级时加载的装备类型
	$ret = array();
	$armor_column = sql_fetch_one_cell("select armor_column from sys_user where uid=$uid");
	$ret[]= $armor_column; 
	if($type==0||$type==2){
		$armors = sql_fetch_rows("select * from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$uid' and a.`hid`=0 and embed_holes!='' order by a.strong_level desc,a.combine_level desc");
	}else if($type==1){
		$armors = sql_fetch_rows("select * from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$uid' and c.tieid in('12003','10008','11002','12006','12007') and a.`hid`=0 and embed_holes!='' order by a.strong_level desc,a.combine_level desc");
	}	
    $ret[] = addSpecialArr($armors);
    $ret[] = getArmorNewAttribute($armors);
    $ret[] = getArmorEmbedGoods($armors);
    $ret[] = getTieInfo($armors, -1);
	$ret[] = getTieArmorAttribute($armors);
	$ret[] = getDeifyAttribute($armors);
	$ret[] = getFusionAttribute($armors);
    return $ret;
}
function loadUserPartArmor($uid,$param)
{
	$part=intval(array_shift($param));
	
	$armors = sql_fetch_rows("select * from sys_user_armor a , cfg_armor c where a.uid='$uid' and a.`hid`=0 and c.id=a.armorid and c.part=$part order by a.strong_level desc,a.combine_level desc");
	$ret=array();
	$ret[] = addSpecialArr($armors);
    $ret[] = getArmorNewAttribute($armors);
	$ret[] = getArmorEmbedGoods($armors);
    $ret[] = getTieInfo($armors, -1);
	$ret[] = getTieArmorAttribute($armors);
	$ret[] = getDeifyAttribute($armors);
	$ret[] = getFusionAttribute($armors);
	return $ret;
}

function getHeroArmor($uid,$param)
{
	$hid=intval(array_shift($param));
	//$ret[]= sql_fetch_one_cell("select hero_health from sys_city_hero where hid=$hid");
	return doGetHeroArmor($hid);
}
function doGetHeroArmor($hid)
{
	$ret=array();
	$ret[]=$hid;
	$armors = sql_fetch_rows("select * from sys_hero_armor h left join sys_user_armor u on u.sid=h.sid and u.hid=h.hid left join cfg_armor c on c.id=u.armorid where h.hid='$hid'");

	$ret[] = addSpecialArr($armors);
	$result = deifyArmorCheck($uid, $hid);
	$ret[] = array_shift($result);
	$ret[] = array_shift($result);
	$ret[] = getArmorNewAttribute($armors);
	$ret[] = getArmorEmbedGoods($armors);
    $ret[] = getTieInfo($armors, $hid);
	$ret[] = getTieArmorAttribute($armors);
	$ret[] = getDeifyAttribute($armors);
	$ret[] = getFusionAttribute($armors);
	return $ret;
}
function addSpecialArr($armors)  //带末日之刃的特效说明
{
	$armor = array();
	foreach($armors as $armorTmp)
	{
		$sid = $armorTmp['sid'];		
		$specialArr = sql_fetch_one("select * from sys_armor_special where sid='$sid'");
		if(!empty($specialArr)){
			$type = $specialArr['type'];
			$specialAttrValue = sql_fetch_one("select * from sys_armor_addon where sid='$sid'");
			if(!empty($specialAttrValue))
			{		
				$armorTmp['specialAttid']=$specialAttrValue['attid'];
				$armorTmp['specialValue']=$specialAttrValue['value']; 
			}elseif ($type==2){
				$armorTmp['specialAttid']="-1";
				$armorTmp['specialValue']=$GLOBALS['armor']['morizhiren_special_1'];					
			}elseif($type==3){
				$armorTmp['specialAttid']="-2";
				$armorTmp['specialValue']=$GLOBALS['armor']['morizhiren_special_2'];
			}						
		}
		$armor[] = $armorTmp;
	}
	return $armor;
}
function checkLoyaltyAdd($hid)
{
	$count = sql_fetch_one_cell("select count(*) from sys_user_armor a, sys_hero_armor b where a.sid=b.sid and b.hid=$hid and b.armorid=12010 and a.active_special=1");
	
	$herotype = sql_fetch_one_cell("select herotype from sys_city_hero where hid='$hid'");
	if(intval($herotype)==10001){
		$baseLoyalty=300;
	}else{
		$baseLoyalty=100;
	}	
	$maxLoyalty=$baseLoyalty;
	if($count==1){
		$maxLoyalty = $baseLoyalty+20;
	}else if($count==2){
		$maxLoyalty = $baseLoyalty+50;
	}
	sql_query("update sys_city_hero set loyalty=LEAST(loyalty,$maxLoyalty) where hid='$hid'");
}

function deifyArmorCheck($uid, $hid)
{
	$atleastone = false;
	$allclear = true;
	$tiename = "";
	$finished = sql_fetch_rows("select t.*, count(t.tieid) as armed from sys_hero_armor ha left join sys_user_armor ua on ua.sid=ha.sid left join cfg_armor a on a.id=ua.armorid left join cfg_tie t on t.tieid=a.tieid where ha.hid=$hid and ua.hp > 0 group by t.tieid");
    foreach ($finished as $finishTie)
	{
		if($finishTie['armed'] < $finishTie['count']) continue;
		$finishTieid = $finishTie['tieid'];
		if(empty($finishTieid) || $finishTieid == 0) continue;
		$armors = sql_fetch_rows("select * from sys_user_armor ua left join cfg_armor a on a.id=ua.armorid where ua.hp>0 and ua.hid=$hid and a.tieid=$finishTieid");
		$deifyAttribute = sql_fetch_rows("select * from cfg_tie_deify_attribute where tieid=$finishTieid");
		$length = count($deifyAttribute);
		foreach ($armors as $armor)
		{
			$armorid = $armor['sid'];
			$count = sql_fetch_one_cell("select count(*) from sys_user_tie_deify_attribute where sid=$armorid");
			if($count > 0) {
				$allclear = false;
				break;
			}
		}
		$atleastone = true;
		//目前保证身上只能穿起来一套套装（比如，所有套装都有个头）
		break;		
	}
	$ret=array();
	$ret[] = $atleastone;
	$ret[] = !$allclear;	
	return $ret;
}

function getHeroDetail($hid)
{
	$hero=sql_fetch_one("select * from sys_city_hero h left join mem_hero_blood m on m.hid=h.hid where h.`hid`='$hid'");
	$buffers=sql_fetch_rows("select * from mem_hero_buffer where hid='$hero[hid]' and endtime>unix_timestamp()");
	foreach($buffers as $buf)
	{
		$typeidx="buf".$buf['buftype'];
		$hero[$typeidx]=$buf['endtime'];
	}
	//君主将修为等级
	if(intval($hero['herotype'])==1000)
	{
		$level = sql_fetch_one_cell("select level from sys_user_level where uid='$hero[uid]'");
		if(empty($level)){$level=0;}
		$hero['kingLevel']=$level;
	}
	$hero['curCid'] = doGetHeroState($hero['hid']);
	return $hero;
}

function deifyArmor($uid, $param)
{
	$atleastone = false;
	$tiename = "";
	$hid = intval(array_shift($param));
	$state = sql_fetch_one_cell("select state from sys_city_hero where hid=$hid");
	if(!empty($state) && $state != 0 && $state != 1 && $state != 7 && $state != 8) {
		throw new Exception($GLOBALS['deifyArmor']['not_in_city']);
	}
	$finished = sql_fetch_rows("select t.*, count(t.tieid) as armed from sys_hero_armor ha left join sys_user_armor ua on ua.sid=ha.sid left join cfg_armor a on a.id=ua.armorid left join cfg_tie t on t.tieid=a.tieid where ha.hid=$hid and ua.hp > 0 group by t.tieid");
    foreach ($finished as $finishTie)
	{
		if($finishTie['armed'] < $finishTie['count']) continue;
		$finishTieid = $finishTie['tieid'];
		if(empty($finishTieid) || $finishTieid == 0) continue;
		$armors = sql_fetch_rows("select * from sys_user_armor ua left join cfg_armor a on a.id=ua.armorid where ua.hp>0 and ua.hid=$hid and a.tieid=$finishTieid");
		$itemneed = 1;//count($armors);只消耗一个
		if(!checkGoodsCount($uid, 155, $itemneed))
		{
			throw new Exception("not_enough_goods155#$itemneed");
		}
		$deifyAttribute = sql_fetch_rows("select * from cfg_tie_deify_attribute where tieid=$finishTieid");
		$length = count($deifyAttribute);
		$allclear = true;
		foreach ($armors as $armor)
		{
			$armorid = $armor['sid'];
			$count = sql_fetch_one_cell("select count(*) from sys_user_tie_deify_attribute where sid=$armorid");
			if($count > 0) {
				sql_query("delete from sys_user_tie_deify_attribute where sid=$armorid");
			}
		}
		if(!$allclear) continue;
		$atleastone = true;
		if($tiename != "") $tiename .= ",";
		$tiename = $tiename.$finishTie['name'];
		foreach ($armors as $armor)
		{
			$armorid = $armor['sid'];
			$choose = $deifyAttribute[rand(0, $length - 1)];
			$value = rand($choose['low'], $choose['high']);
			$attid = $choose['attid'];						
			sql_query("insert into sys_user_tie_deify_attribute(`attid`, `sid`, `value`) values('$attid', '$armorid', '$value') on duplicate key update `value`='$value'");
		}
		
		reduceGoods($uid, 155, $itemneed);
		//目前保证身上只能穿起来一套套装（比如，所有套装都有个头）
		break;
	}
	if(!$atleastone) {
		throw new Exception($GLOBALS['deifyArmor']['failure']);
	}
	regenerateHeroAttri($uid,$hid);
	$ret=array();
	$ret[] = sprintf($GLOBALS['deifyArmor']['success'], $tiename);
	$ret[]=getHeroDetail($hid);
	$ret[]=doGetHeroArmor($hid);
	logUserAction($uid,13);
	return $ret;	
}

function equipArmor($uid,$param)
{
	$hid=array_shift($param);
	$sid=array_shift($param);
	$spart=array_shift($param);
 
	
	$armorInfo=sql_fetch_one("select * from sys_user_armor u left join cfg_armor c on c.id=u.armorid where u.sid='$sid' and u.uid='$uid'");
	
	if($armorInfo["part"]!=floor($spart/10)){
		throw new Exception($GLOBALS['equipArmor']['not_right_part']);
	}
	
	if(empty($armorInfo)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	else if ($armorInfo['hid']!=0) throw new Exception($GLOBALS['equipArmor']['arm_in_use']);
	$hp=ceil($armorInfo['hp']/10);
	if($hp<=0)
	{
		throw new Exception($GLOBALS['equipArmor']['no_hp_max']);
	}
	$heroInfo=sql_fetch_one("select * from sys_city_hero where hid='$hid'");
	if(empty($heroInfo)|| isHeroInCity($heroInfo['state'])==0)//$heroInfo['state']>1)
	{
		throw new Exception($GLOBALS['equipArmor']['hero_state_wrong']);
	}
	
	if(intval($heroInfo['herotype'])!=1000)
	{
		if(intval($armorInfo['armorid'])>=15000&&intval($armorInfo['armorid'])<16000)
		{
			throw new Exception($GLOBALS['equipArmor']['hero_type_wrong']);
		}
	}
	
	$heroLevel=$heroInfo['level'];
	if($armorInfo['hero_level']>$heroLevel)
	{
		throw new Exception(sprintf($GLOBALS['equipArmor']['level'],$armorInfo['hero_level']));
	}
	$armorid=$armorInfo['armorid'];
	if (($armorid >=12001 && $armorid <= 12008) && $hid >=1030 && !checkHeroLevel($uid,6,50)) {
		throw new Exception($GLOBALS['equipment']['must_be_npchero']);
	}

	$oldarmor=sql_fetch_one("select * from sys_hero_armor h left join cfg_armor c on c.id=h.armorid where h.hid='$hid' and h.spart='$spart'");
	if(!empty($oldarmor))	//把旧的装备换下来
	{
		$oldid=$oldarmor['sid'];
		sql_query("update sys_user_armor set hid=0 where sid='$oldid'");
	}
	sql_query("update sys_user_armor set hid='$hid' where sid='$sid'");
	sql_query("insert into sys_hero_armor (hid,spart,sid,armorid) values ($hid,$spart,$sid,$armorid) on duplicate key update sid=$sid,armorid=$armorid");
	if($heroInfo['state']==1)
	{
		updateCityResourceAdd($heroInfo['cid']);
	}
	//洛神玉佩 检查加成
	checkLoyaltyAdd($hid);
	regenerateHeroAttri($uid,$hid);
	$ret=array();
	$ret[]=getHeroDetail($hid);
	$ret[]=doGetHeroArmor($hid);
	return $ret;
}

function offloadArmor($uid,$param)
{
	$hid=intval(array_shift($param));
	$spart=intval(array_shift($param));
	
	$armorInfo=sql_fetch_one("select * from sys_hero_armor h left join sys_user_armor u on u.sid=h.sid left join cfg_armor c on c.id=u.armorid where h.hid='$hid' and h.spart='$spart'");
	if(empty($armorInfo)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	$heroInfo=sql_fetch_one("select * from sys_city_hero where hid='$hid'");
	if(empty($heroInfo)||isHeroInCity($heroInfo['state'])==0)//$heroInfo['state']>1)
	{
		throw new Exception($GLOBALS['equipArmor']['hero_state_wrong']);
	}
	
	$sid=$armorInfo['sid'];
	sql_query("update sys_user_armor set hid=0 where sid='$sid'");
	sql_query("delete from sys_hero_armor where hid='$hid' and spart='$spart'");
	
	//洛神玉佩 检查加成
	checkLoyaltyAdd($hid);
	regenerateHeroAttri($uid,$hid);
	if($heroInfo['state']==1)
	{
		updateCityResourceAdd($heroInfo['cid']);
	}
	
	$ret=array();
	$ret[]=getHeroDetail($hid);;
	$ret[]=doGetHeroArmor($hid);
	return $ret;
}

function repairArmor($uid,$param)
{
	$cid=intval(array_shift($param));
	$sid=intval(array_shift($param));
	
	$armorInfo=sql_fetch_one("select * from sys_user_armor where sid='$sid' and uid='$uid'");
	if(empty($armorInfo)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	$hp=ceil($armorInfo['hp']/10);
	$hpmax=$armorInfo['hp_max'];
	if($hp<0)
	{
		throw new Exception($GLOBALS['repairArmor']['no_hp_max']);
	}
	$goldNeed=($hpmax-$hp)*100;
	if($goldNeed<=0) throw new Exception($GLOBALS['repairArmor']['no_need']);
	$cityGold=sql_fetch_one_cell("select gold from mem_city_resource where cid=$cid");
	if($goldNeed>$cityGold) throw new Exception($GLOBALS['repairArmor']['no_gold']);
	$reduce=max(1,ceil(($hpmax-$hp)/10));
	$hpmax=max(0,$hpmax-$reduce);
	sql_query("update sys_user_armor set  hp=$hpmax*10,hp_max=$hpmax where sid='$sid'");
	sql_query("update mem_city_resource set `gold`=GREATEST(0,`gold`-'$goldNeed') where `cid`='$cid'");
	if($armorInfo['hid']!=0)
	{
		regenerateHeroAttri($uid,$armorInfo['hid']);
	}
	$ret=array();
	$ret[]=$sid;
	$ret[]=$hpmax;
	$ret[]=$cid;
	$ret[]=intval(floor(sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'")));
	return $ret;
}

function renovateArmor($uid,$param)
{
	$sid=intval(array_shift($param));
	$armorInfo=sql_fetch_one("select * from sys_user_armor u left join cfg_armor c on c.id=u.armorid where u.sid='$sid' and u.uid='$uid'");
	if(empty($armorInfo)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	$hp=ceil($armorInfo['hp']/10);
	$hpmax=$armorInfo['hp_max'];
	$orihpmax=$armorInfo['ori_hp_max'];
	$moneyNeed=($orihpmax-$hpmax)+ceil(($hpmax-$hp)/10);
	if($moneyNeed<=0) throw new Exception($GLOBALS['renovateArmor']['no_need']);
	if(!checkMoney($uid,$moneyNeed))
	{
		throw new Exception($GLOBALS['renovateArmor']['no_money']);
	}
	addMoney($uid,-$moneyNeed,100);
	sql_query("update sys_user_armor set hp=$orihpmax*10,hp_max=$orihpmax where sid='$sid'");
	if($armorInfo['hid']!=0)
	{
		regenerateHeroAttri($uid,$armorInfo['hid']);
	}
	$ret=array();
	$ret[]=$sid;
	return $ret;
}

function renovateArmorWithGift($uid,$param)
{
	$sid=intval(array_shift($param));
	$armorInfo=sql_fetch_one("select * from sys_user_armor u left join cfg_armor c on c.id=u.armorid where u.sid='$sid' and u.uid='$uid'");
	if(empty($armorInfo)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	$hp=ceil($armorInfo['hp']/10);
	$hpmax=$armorInfo['hp_max'];
	$orihpmax=$armorInfo['ori_hp_max'];
	$moneyNeed=($orihpmax-$hpmax)+ceil(($hpmax-$hp)/10);
	if($moneyNeed<=0) throw new Exception($GLOBALS['renovateArmor']['no_need']);
	if(!checkGift($uid,$moneyNeed))
	{
		throw new Exception($GLOBALS['renovateArmor']['no_gift']);
	}
	sql_query("update sys_user_armor set hp=$orihpmax*10,hp_max=$orihpmax where sid='$sid'");
	if($armorInfo['hid']!=0)
	{
		regenerateHeroAttri($uid,$armorInfo['hid']);
	}
	addGift($uid,-$moneyNeed,100);
	$ret=array();
	$ret[]=$sid;
	return $ret;
}

function sellArmor($uid,$param)
{
	$cid=intval(array_shift($param));
	$sid=intval(array_shift($param));
	$marketLevel=sql_fetch_one_cell("select level from sys_building where cid='$cid' and bid=".ID_BUILDING_MARKET);
	if($marketLevel<5)
	{
		throw new Exception($GLOBALS['sellArmor']['market_level_low']);
	}
	
	$nobility=sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
	//推恩
	$nobility=getBufferNobility($uid,$nobility);
	
	if($nobility<1)
	{
		throw new Exception($GLOBALS['sellArmor']['nobility_low']);
	}
	$armorInfo=sql_fetch_one("select * from sys_user_armor u, cfg_armor c where u.sid='$sid' and u.uid='$uid' and c.id=u.armorid");
	if(empty($armorInfo)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	$armorid=$armorInfo['armorid'];
	$is_zuoji=sql_check("select 1 from cfg_armor where id='$armorid' and part=12");
	if($is_zuoji){
		$embed_pearls = explode(",", $armorInfo['embed_pearls']);
		foreach($embed_pearls as $embed_pearl){
			if($embed_pearl>0) throw new Exception($GLOBALS['sellArmor']['exist_zuoji_embed']);
		}
	}
	$hp=ceil($armorInfo['hp']/10);
	$hpmax=$armorInfo['hp_max'];
	$orihpmax=$armorInfo['ori_hp_max'];
	$armorType = $armorInfo['type'];
	
	$goldAdd=intval(max(1,floor($hp/$orihpmax))*$armorInfo['value'])*500;
	
	sql_query("update mem_city_resource set `gold`=`gold`+$goldAdd where cid='$cid'");
		sql_query("insert into log_selled_armor(sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,time)(select sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,unix_timestamp() from sys_user_armor where sid='$sid')");
	sql_query("delete from sys_user_armor where sid='$sid'");
	sql_query("insert into log_armor (uid,armorid,count,time,type) values ($uid,$armorInfo[armorid],-1,unix_timestamp(),9)");
	$ret=array();
	$ret[]=$sid;
	$ret[]=$cid;
	$ret[]=intval(floor(sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'")));
	
	$msg = checkAndDoArmorRecycleAct($uid, $armorType);//回收装备活动
	if($msg){
		$ret[]= $msg;
	}
	
	return $ret;
}

function repairAllArmor($uid,$param)
{
	$cid=intval(array_shift($param));
	$type=intval(array_shift($param));
	$ids=array_shift($param);
	$ids = addslashes($ids);
	if(empty($ids)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	$armors=sql_fetch_rows("select * from sys_user_armor u left join cfg_armor c on c.id=u.armorid where u.uid='$uid' and u.sid in ($ids)");
	$goldNeed=0;
	foreach($armors as $armorInfo)
	{
		$hp=ceil($armorInfo['hp']/10);
		$hpmax=$armorInfo['hp_max'];
		if($hp<0)
		{
			throw new Exception($GLOBALS['repairArmor']['no_hp_max']);
		}
		$hpmax=max(0,$hpmax-$reduce);
		$goldNeed=$goldNeed+($hpmax-$hp)*100;
	}
	if($goldNeed<=0) throw new Exception($GLOBALS['repairArmor']['no_need']);
	$cityGold=sql_fetch_one_cell("select gold from mem_city_resource where cid=$cid");
	if($goldNeed>$cityGold) throw new Exception($GLOBALS['repairArmor']['no_gold']);
	foreach($armors as $armorInfo)
	{
		$hp=ceil($armorInfo['hp']/10);
		$hpmax=$armorInfo['hp_max'];
		$reduce=max(1,ceil(($hpmax-$hp)/10));		
		$hpmax=max(0,$hpmax-$reduce);
		sql_query("update sys_user_armor set  hp=$hpmax*10,hp_max=$hpmax where sid='$armorInfo[sid]'");
	}
	sql_query("update mem_city_resource set `gold`=GREATEST(0,`gold`-'$goldNeed') where `cid`='$cid'");
	if((!empty($armors))&&$armors[0]['hid']!=0)
	{
		regenerateHeroAttri($uid,$armors[0]['hid']);
	}
	$ret=array();
	$ret[]=$type;
	$ret[]=$cid;
	$ret[]=intval(floor(sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'")));
	return $ret;
}

function renovateAllArmor($uid,$param)
{
	$type=intval(array_shift($param));
	$ids=array_shift($param);
	$ids=addslashes($ids);
	if(empty($ids)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	$armors=sql_fetch_rows("select * from sys_user_armor u left join cfg_armor c on c.id=u.armorid where u.uid='$uid' and u.sid in ($ids)");
	$moneyNeed=0;
	foreach($armors as $armorInfo)
	{
		$hp=ceil($armorInfo['hp']/10);
		$hpmax=$armorInfo['hp_max'];
		$orihpmax=$armorInfo['ori_hp_max'];
		$moneyNeed=$moneyNeed+($orihpmax-$hpmax)+ceil(($hpmax-$hp)/10);
	}
	if($moneyNeed<=0) throw new Exception($GLOBALS['renovateArmor']['no_need']);
	if(!checkMoney($uid,$moneyNeed))
	{
		throw new Exception($GLOBALS['renovateArmor']['no_money']);
	}
	addMoney($uid,-$moneyNeed,100);
	foreach($armors as $armorInfo)
	{
		$orihpmax=$armorInfo['ori_hp_max'];
		$sid=$armorInfo['sid'];
		sql_query("update sys_user_armor set hp=$orihpmax*10,hp_max=$orihpmax where sid='$sid'");
	}
	if((!empty($armors))&&$armors[0]['hid']!=0)
	{
		regenerateHeroAttri($uid,$armors[0]['hid']);
	}
	$ret=array();
	$ret[]=$type;
	return $ret;
}

function renovateAllArmorWithGift($uid,$param)
{
	$type=array_shift($param);
	$ids=array_shift($param);
	$ids = addslashes($ids);
	if(empty($ids)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	$armors=sql_fetch_rows("select * from sys_user_armor u left join cfg_armor c on c.id=u.armorid where u.uid='$uid' and u.sid in ($ids)");
	$moneyNeed=0;
	foreach($armors as $armorInfo)
	{
		$hp=ceil($armorInfo['hp']/10);
		$hpmax=$armorInfo['hp_max'];
		$orihpmax=$armorInfo['ori_hp_max'];
		$moneyNeed=$moneyNeed+($orihpmax-$hpmax)+ceil(($hpmax-$hp)/10);
	}
	if($moneyNeed<=0) throw new Exception($GLOBALS['renovateArmor']['no_need']);
	if(!checkGift($uid,$moneyNeed))
	{
		throw new Exception($GLOBALS['renovateArmor']['no_gift']);
	}
	foreach($armors as $armorInfo)
	{
		$orihpmax=$armorInfo['ori_hp_max'];
		$sid=$armorInfo['sid'];
		sql_query("update sys_user_armor set hp=$orihpmax*10,hp_max=$orihpmax where sid='$sid'");
	}
	if((!empty($armors))&&$armors[0]['hid']!=0)
	{
		regenerateHeroAttri($uid,$armors[0]['hid']);
	}
	addGift($uid,-$moneyNeed,100);
	$ret=array();
	$ret[]=$type;
	return $ret;
}
/*
function showArmor($uid,$sid)
{
	
}
*/
function zhuangbeichaijie($uid, $param) 
{
	$sid = intval(array_shift($param));
	$armorInfo=sql_fetch_one("select * from sys_user_armor a left join cfg_armor ca on ca.id=a.armorid where a.sid='$sid' and a.uid='$uid' limit 1");
	if(empty($armorInfo)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	else if ($armorInfo['hid']!=0) throw new Exception($GLOBALS['equipArmor']['arm_in_use']);
	
	//if($armorInfo['strong_level'] == 0 && ($armorInfo['embed_pearls'] == "" || $armorInfo['embed_pearls'] == "0,0,0,0,0")) {
	if(empty($armorInfo['embed_holes'])) {
		throw new Exception($GLOBALS['equipArmor']['can_not_chaijie']);
	}
	
	if($armorInfo['part'] == 12) {
		throw new Exception($GLOBALS['equipArmor']['horse']);
	}
	//只要激活，就给碎片。
	$cfgArmorInfo = sql_fetch_one("select hero_level,attribute from cfg_armor where id=".$armorInfo['armorid']);
	$suipianCount = getSuipian($cfgArmorInfo);
	$itemstr = $GLOBALS['equipArmor']['armor_chip'].$suipianCount;
	//$itemstr = "装备碎片$suipianCount";
	addThings($uid,10400,$suipianCount,9);//9，拆解获得，tid=10400，装备碎片。
	
	$cnt = 0;
	$highCnt=0;//新增返回高级强化宝珠
	if($armorInfo['strong_level'] > 0){
		$probability = sql_fetch_rows("select * from cfg_strong_probability order by level");
		$level = $armorInfo['strong_level'];
		if($level<=10)	//10级及10级以下返回强化宝珠
		{
			for($i = 1; $i <= $level; $i++)
			{
				$cnt += floor(100.0/$probability[$i - 1]['suc_value']);
			}
		}
		else if($level>10)
		{
			for($i = 1; $i <= 10; $i++)		//前10级的强化宝珠数
			{
				$cnt += floor(100.0/$probability[$i - 1]['suc_value']);
			}
			for($i=11;$i<=$level;$i++)		//后5级返回高级强化宝珠
			{
				$highCnt +=floor( (100.0/$probability[$i - 1]['suc_value'])*0.2 );
			}
		}
				
	}
	
	$items = array();
	$tempitems=array();
	if($cnt > 0) {
		$tempitems[205]=$cnt;  //强化宝珠
	}
	if($highCnt>0)
	{
		$tempitems[11170]=$highCnt;//高级强化宝珠
	}
	//11级宝珠和10级宝珠的gid不是减一的关系
	$elevenArr=array(17500=>309,17505=>319,17510=>329,17515=>339,17520=>349,17525=>359,17530=>369,17535=>379);
	if($armorInfo['embed_pearls'] != "") {
		$ary = explode(",", $armorInfo['embed_pearls']);
		for($i = 0; $i < count($ary); $i++)
		{
			$gid = $ary[$i];
			/*if($gid < 300 || $gid >= 400) {
				continue;
			}    是谁写的400！*/
			//300-379是1到10级镶嵌宝珠，17500-17539是11-15级镶嵌宝珠
			
			if($gid>=300 && $gid<=379)
			{
				$level=$gid % 10 +1;
			}	
			else if($gid>=17500 && $gid<=17539)
			{
				$level=$gid % 5 +11;
			}		
			else 
				continue;
			//$level = $gid % 10;
			if(mt_rand(0, 99) < 50) {
				if(empty($tempitems[$gid])){
					$tempitems[$gid] =1;
				}else{
					$tempitems[$gid] =$tempitems[$gid]+1;					
				}
			} 
			else 
			{
				if(array_key_exists($gid,$elevenArr))	//如果该镶嵌宝珠为11级镶嵌宝珠
				{
					if(empty($tempitems[$elevenArr[$gid]]))
					{
						$tempitems[$elevenArr[$gid]] =1;
					}else
					{
						$tempitems[$elevenArr[$gid]] =$tempitems[$elevenArr[$gid]]+mt_rand(1, 2);					
					}
				}
				else if($level > 1)	//二级以上且非11级宝珠
				{
					if(empty($tempitems[$gid - 1])){
						$tempitems[$gid - 1] =1;
					}else{
						$tempitems[$gid - 1] =$tempitems[$gid - 1]+mt_rand(1, 2);					
					}
				}
			}
		}
	}
	foreach ($tempitems as $gidkey=>$gidcount){
		$items[]=$gidkey;
		$items[]=$gidcount;
	}
	/*
	if($cnt > 0) {
		$items[] = 205;
		$items[] = $cnt;
	}
	if($armorInfo['embed_pearls'] != "") {
		$ary = explode(",", $armorInfo['embed_pearls']);
		for($i = 0; $i < count($ary); $i++)
		{
			$gid = $ary[$i];
			if($gid < 300 || $gid >= 400) {
				continue;
			}
			$level = $gid % 10;
			if(mt_rand(0, 99) < 50) {
				$items[] = $gid;
				$items[] = 1;
			} else {
				if($level > 0) {
					$items[] = $gid - 1;
					$items[] = mt_rand(1, 2);
				}
			}
		}
	}
*/
	
	//sql_query("update sys_user_armor set embed_pearls='', strong_level = 0, strong_value = 0 where uid=$uid and sid=$sid");
	//sql_query("delete from sys_user_armor where uid=$uid and sid=$sid");
	cutArmorBySid($uid,$armorInfo['armorid'],$sid,11); //11：装备拆解
	
	for($i = 0; $i < count($items); $i+=2)
	{
		$gid = $items[$i];
		$cnt = $items[$i+1];
		if($cnt == 0) continue;
		$good = sql_fetch_one("select * from cfg_goods where gid=$gid");
		if(empty($good)) continue;
		$desc = $good['name'].' '.$cnt;
		if($itemstr == "")
		{
			$itemstr = $itemstr.$desc;
		} else {
			$itemstr = $itemstr.", ".$desc;
		}
		addGoods($uid,$gid,$cnt,23);
	}
	if($itemstr != "") {
		$msg = $GLOBALS['equipArmor']['get_sth_from_chaijie'].$itemstr.$GLOBALS['equipArmor']['get_sth_from_chaijie_end'];
	} else {
		$msg = $GLOBALS['equipArmor']['nothing_get_from_chaijie'];
	}
	
	$armorInfo=sql_fetch_one("select * from sys_user_armor a left join cfg_armor ca on ca.id=a.armorid where a.sid='$sid' and a.uid='$uid' limit 1");
	
	$ret = array();
	//$ret[] = $sid;
	$ret[] = $msg;
	logUserAction($uid,24);
	return $ret;
}

function getSuipian($param) {
	$level = array_shift($param);
	$attribute = array_shift($param);
	//折算公式为：加权属性值=4*等级要求+2*统率+4*内政+8*勇武+4*智谋+3*速度，每10个加权属性值可获得1枚装备碎片，加权属性值的个位数全部按0处理。
	//1：统帅2：内政3：勇武4：智谋5：体力6：精力7：生命8：攻击9：防御10：射程11：速度12：负重',
	$value = 0;
	$count = 0;
	
	$value = $level*4;
	$arr = explode(',',$attribute);
	$lengh = array_shift($arr);
	
	for($i=0;$i<$lengh;$i++) {
		$item = array_shift($arr);
		$num = array_shift($arr);
		if ($item == 1) $value += $num*2;
		if ($item == 2) $value += $num*4;
		if ($item == 3) $value += $num*8;
		if ($item == 4) $value += $num*4;
		if ($item == 11) $value += $num*3;
	}
	
	$count = intval(ceil($value/10));
	if ($value == 0) $count =1;
	
	return $count;
}

function loadActiveArmorSpecialGoods($uid,$param){
	$sid=intval(array_shift($param));
	$gid=10522;//神兵鉴符
	$goods=sql_fetch_one("select c.*,ifnull(g.count,0) as count from cfg_goods c left join sys_goods g on c.gid=g.gid and g.uid='$uid' where c.gid='$gid'");
	$ret[]=$goods;
	return $ret;
}

function activeArmorSpecial($uid,$param){
	$sid=intval(array_shift($param));
	$armor=sql_fetch_one("select a.*,c.* from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$uid' and a.sid='$sid'");
	if(empty($armor)){
		throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	}
	if($armor['active_special']!=0 && $armor['armorid'] != 12016){
		throw new Exception($GLOBALS['active_special']['already_active']);
	}
	$gid=10522;//神兵鉴符
	if(!checkGoods($uid, $gid)){
		throw new Exception($GLOBALS['active_special']['not_enough_goods']);
	}
	//扣东西
	reduceGoods($uid, $gid, 1);
	//激活特殊效果
	sql_query("update sys_user_armor set active_special=1 where uid='$uid' and sid='$sid'");
	$armor['active_special']=1;
	//末日之刃处理
	if ($armor['armorid'] == 12016) {
		$str = activeMorizhiren($sid);
	}
	$ret[]=addSpecialArr(array($armor));
	$ret[]=$GLOBALS['active_special']['success'].$str;
	return $ret;
}

function activeMorizhiren($sid) {
	$msg="";
	$type=1;
	$rand = rand(1,100);
	if ($rand>=81 && $rand<=85) {
		$type = 2;
	} elseif ($rand>=86 && $rand<=95) {
		$type = 3;
	} elseif ($rand>=96 && $rand<=100) {
		$type = 4;
	}
	//先清理
	sql_query("delete from sys_armor_addon where sid=$sid");
	sql_query("delete from sys_armor_special where sid=$sid");
	
	if ($type == 1) {
		$msg = morizhirenAddAttr($sid);
	}elseif($type == 4) {
		sql_query ( "insert into sys_armor_addon(`sid`, `attid`, `value`) values($sid, 11, 1) on duplicate key update attid=11,value=1" );
		$msg = $GLOBALS['morizhiren']['add_speed'];
	}elseif ($type == 5) {
		sql_query ( "insert into sys_armor_addon(`sid`, `attid`, `value`) values($sid, 10, 500) on duplicate key update attid=10,value=500" );
		$msg = $GLOBALS['morizhiren']['add_range'];
	}elseif ($type==2){
		$msg = $GLOBALS['morizhiren']['add_health'];
	}elseif ($type==3){
		$msg = $GLOBALS['morizhiren']['add_degrade_building_level'];
	}
	sql_query("insert into sys_armor_special(sid,type) values('$sid','$type')");
	return $msg;
}

function morizhirenAddAttr($sid) {
	$attFlag = rand(0,1);
	$value = rand(1,10);
	$str = "";
	if ($attFlag == 1) {
		$attid = 10008;
		$str = sprintf($GLOBALS['active_special']['attack_success'],$value,'%');
	} else {
		$attid = 10009;
		$str = sprintf($GLOBALS['active_special']['defence_success'],$value,'%');
	}
	sql_query ( "insert into sys_armor_addon(`sid`, `attid`, `value`) values($sid, $attid, $value) on duplicate key update attid=$attid,value=$value" );
	return $str;
}

function offloadAllArmor($uid,$param)
{
	$hid = intval(array_shift($param));
		
	$heroInfo=sql_fetch_one("select * from sys_city_hero where hid='$hid'");
	if(empty($heroInfo)||isHeroInCity($heroInfo['state'])==0)//$heroInfo['state']>1)
	{
		throw new Exception($GLOBALS['equipArmor']['hero_state_wrong']);
	}
	
	sql_query("update sys_user_armor set hid=0 where hid='$hid'");
	sql_query("delete from sys_hero_armor where hid='$hid'");
	
	//洛神玉佩 检查加成
	checkLoyaltyAdd($hid);
	regenerateHeroAttri($uid,$hid);
	if($heroInfo['state']==1)
	{
		updateCityResourceAdd($heroInfo['cid']);
	}
	
	$ret=array();
	$ret[]=getHeroDetail($hid);;
	$ret[]=doGetHeroArmor($hid);
	return $ret;
	
}
//function analyzeArmor($uid,$param)
//{
//	$sid = intval(array_shift($param));
//	
//	$armorInfo = sql_fetch_one("select a.*,c.* from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$uid' and a.sid='$sid' and a.hid='0'");
//	if(empty($armorInfo))
//	{
//		throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
//	}
//	$type = $armorInfo['type'];
//	$rate = mt_rand(0, 100);
//	
//	$rewardGood = sql_fetch_one("select * from cfg_goods where gid='205'");
//	$count = 0;
//	switch(strval($type))
//	{
//		case "1" :
//		if($rate<=30)
//		{
//			addGoods($uid, $rewardGood['gid'], 1, 0704);
//			$count=1;
//		}
//		break;
//		case "2" :
//		if ($rate<=50)
//		{
//			addGoods($uid, $rewardGood['gid'], 1, 0704);
//			$count=1;
//		}
//		break;
//		case "3" :
//		if ($rate<=70)
//		{
//			addGoods($uid, $rewardGood['gid'], 1, 0704);
//			$count=1;
//		}
//		break;
//		case "4" :
//		if ($rate<=80)
//		{
//			addGoods($uid, $rewardGood['gid'], 2, 0704);
//			$count=2;
//		}
//		break;
//		case "5" :
//		if ($rate<=100)
//		{
//			addGoods($uid, $rewardGood['gid'], 5, 0704);
//			$count=5;
//		}
//		break;
//		default: throw new Exception($GLOBALS['analyzeArmor']['type_not_exist']);
//		break;
//	}
//	sql_query("delete from sys_user_armor where sid='$sid'");
//	sql_query("delete from sys_user_tie_deify_attribute where sid='$sid'");
//	sql_query("insert into log_armor (uid,armorid,count,time,type) values ($uid,$armorInfo[armorid],-1,unix_timestamp(),0704)");
//	
//	if($count==0)
//	{
//		$msg = $GLOBALS['analyzeArmor']['analyze_succ'];
//	}else
//	{
//		$msg = sprintf($GLOBALS['analyzeArmor']['analyze_succ_reward'],$rewardGood['name'],$count);
//	}
//	
//	$ret = array();
//	$ret[] = $msg;
//	return $ret;
//	
//}
//装备分解
function multipAnalyzeArmor($uid,$param)
{
	$armorSidArr = $param;
	
	foreach($armorSidArr as $sid)
	{
		$armorInfo = sql_fetch_one("select a.*,c.* from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$uid' and a.sid='$sid'");
		if(empty($armorInfo))
		{
			throw new Exception($GLOBALS['equipArmor']['Multip_arm_not_exist']);
		}
		if(intval($armorInfo['hid'])>0)
		{
			throw new Exception($GLOBALS['equipArmor']['can_not_on_hero']);
		}
	}
	
	$count = 0;
	foreach($armorSidArr as $sid)
	{
		$armorInfo = sql_fetch_one("select a.*,c.* from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$uid' and a.sid='$sid'");		
		$type = $armorInfo['type'];
		$armorId = intval($armorInfo['armorid']);   //部分君主将套装特殊处理(装备id范围：15000---15032)蓝色品质
		$rate = mt_rand(0, 100);
		
		$rewardGood = sql_fetch_one("select * from cfg_goods where gid='205'");

		switch(strval($type))
		{
			case "1" :
			if($rate<=30)
			{
				addGoods($uid, $rewardGood['gid'], 1, 0704);
				$count=$count+1;
			}
			break;
			case "2" :
			if ($rate<=50)
			{
				addGoods($uid, $rewardGood['gid'], 1, 0704);
				$count=$count+1;
			}
			break;
			case "3" :
			if ($rate<=70)
			{
				addGoods($uid, $rewardGood['gid'], 1, 0704);
				$count=$count+1;
			}
			break;
			case "4" :
			if($armorId>=15000&&$armorId<=15032)  //部分君主将套装
			{
				if($rate<=30)
				{
					addGoods($uid, $rewardGood['gid'], 2, 0704);
					$count=$count+2;
				}
			}else 
			{
				if ($rate<=65)
				{
					addGoods($uid, $rewardGood['gid'], 2, 0704);
					$count=$count+2;
				}
			}			
			break;
			case "5" :
			if ($rate<=100)
			{
				addGoods($uid, $rewardGood['gid'], 5, 0704);
				$count=$count+5;
			}
			break;
			case "6":
			if ($rate<=100)
			{
				addGoods($uid, $rewardGood['gid'], 10, 0704);
				$count=$count+10;
			}
			break;
			default: throw new Exception($GLOBALS['analyzeArmor']['type_not_exist']);
			break;
		}
		sql_query("delete from sys_user_armor where sid='$sid'");
		sql_query("delete from sys_user_tie_deify_attribute where sid='$sid'");
		sql_query("insert into log_armor (uid,armorid,count,time,type) values ($uid,$armorInfo[armorid],-1,unix_timestamp(),0704)");
	}
	
	if($count==0)
	{
		$msg = $GLOBALS['analyzeArmor']['analyze_succ'];
	}else
	{
		$msg = sprintf($GLOBALS['analyzeArmor']['analyze_succ_reward'],$rewardGood['name'],$count);
	}
	
	$ret = array();
	$ret[] = $msg;
	return $ret;
}

function doUpgradeArmor($uid,$param)
{
	$sid = array_shift($param);
	$isProtected = array_shift($param);
	
	if(intval($sid)<0) throw new Exception($GLOBALS['hero']['xidian_unvalid']);
	$armorInfo = sql_fetch_one("select c.tieid,s.* from cfg_armor c,sys_user_armor s where c.id=s.armorid and s.uid='$uid' and s.sid='$sid' and s.hid=0");
	if(empty($armorInfo)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
	//if(!empty($armorInfo['embed_pearls'])){
	//	$embed_gids = explode(',',$armorInfo['embed_pearls']);
	//	for($i=0;$i<count($embed_gids);$i++)
	//	{
		//	if(intval($embed_gids[$i])>0) throw new Exception($GLOBALS['upgradeArmor']['has_embed']);//有珠宝 要卸载掉才能升级
	//	}
	//}
	$tieid = intval($armorInfo['tieid']);
	$curGoodStr = checkAndReduceUpgradeGood($uid,$tieid,$isProtected);
	$upgradeRateArr = array(
		11002=>30,
		12003=>20,
		10008=>15,
		12006=>12,
		12007=>10
	);
	$upgradeToArmorIdAdd = array(    //转化对应装备armorid相差的值
		11002=>42810,
		12003=>-37000,
		10008=>4116,
		12006=>12
	);
	
	$succRate = mt_rand(1, 100);
	$isSucc = 0;
	$targetArmorid = 0;
	if($succRate<=$upgradeRateArr[$tieid])   //成功了
	{
		$isSucc = 1;
		$targetArmorid = intval($armorInfo['armorid'])+$upgradeToArmorIdAdd[$tieid];
	}else{     //升级失败了
		$isSucc = 0;  
		$isCut = true;		
		if($tieid==11002){    //名将套装备有20%概率是保留装备的
			$isHoldRate = mt_rand(1,100);
			if($isHoldRate<=20)$isCut = false;
		}
		if($isProtected)$isCut=false;
	}
	//加升级log
	$protectTag = $isProtected?1:0;
	//sql_query("insert into log_armor_upgrade select *,$protectTag,$isSucc,unix_timestamp() from sys_user_armor where sid='$sid'");
	if($isSucc==1){
		sql_query("update sys_user_armor set armorid='$targetArmorid' where sid='$sid' limit 1");
		
	  /*	if($tieid==10008){//增加一个孔
		sql_query("update sys_user_armor set embed_holes ='0,0,0,0,4' where sid='$sid' limit 1");
		  }*/
		  
		  switch ($tieid)//增加一个孔
	    {
		case 12006:   //龙渊装备
		
			break;
		case 12007:   //白虎装备
		
			break;
		case 11002:   //名将装备->冰封
		
			break;
		case 12003:   //冰封装备->冰魂   增加装备耐久
		sql_query("update sys_user_armor set embed_holes ='0,0,0,0,4',hp_max ='300',hp='3000' where sid='$sid' limit 1");
			break;
		case 10008:   //神武装备->神龙  增加装备耐久
		sql_query("update sys_user_armor set embed_holes ='0,0,0,0,4',hp_max ='200',hp='2000' where sid='$sid' limit 1");
			break;
		default:
		    break;
		
	    }
		  
	}else{
		if($isCut){
			sql_query("delete from sys_user_armor where sid='$sid' limit 1");
		}
	}
			
	$msg = getUpgradeResultMsg($isSucc,$isProtected,$targetArmorid);
	$ret = array();
	$ret[] = $msg;
	$ret[] = $curGoodStr;
	$ret[] = $isProtected;
	$ret[] = $isSucc;
	$ret[] = getOneArmorBySid($uid,$sid);
	
	return $ret;
}

function checkAndReduceUpgradeGood($uid,$tieid,$isProtected)
{
	if(!lockUser($uid)) throw new Exception($GLOBALS['pacifyPeople']['server_busy']);
	$gid1=-1;
	$gid2=-1;
	switch ($tieid)
	{
		case 11002:   //名将装备
			$gid1=12158;
			$gid2=12159;
			break;
		case 12003:   //冰封装备
			$gid1=12158;
			$gid2=12160;
			break;
		case 10008:   //神武装备
			$gid1=12158;
			$gid2=12161;
			break;
		case 12006:   //龙渊装备
			$gid1=12158;
			$gid2=12161;
			break;
		case 12007:   //白虎装备
			$gid1=12158;
			$gid2=12161;
			break;
        case 0:
            $gid1=12158;
			$gid2=12161;		
		default:throw new Exception($GLOBALS['upgradeArmor']['armor_cannot_upgrade']);		
	}
	
	$gid1Cnt = sql_fetch_one_cell("select `count` from sys_goods where uid='$uid' and gid='$gid1'");
	if(empty($gid1Cnt)||intval($gid1Cnt)<1)throw new Exception($GLOBALS['upgradeArmor']['material_not_enough']);
	$gid2Cnt = sql_fetch_one_cell("select `count` from sys_goods where uid='$uid' and gid='$gid2'");
	if(empty($gid2Cnt)||intval($gid2Cnt)<1)throw new Exception($GLOBALS['upgradeArmor']['material_not_enough']);

	if($isProtected){
		$protectedCnt = sql_fetch_one_cell("select `count` from sys_goods where uid='$uid' and gid='12157'");
		if(empty($protectedCnt)||intval($protectedCnt)<1)throw new Exception($GLOBALS['upgradeArmor']['protect_not_enough']);
		addGoods($uid, 12157, -1, 1115);
	}	
	addGoods($uid, $gid1, -1, 1115);
	addGoods($uid, $gid2, -1, 1115);	
	
	$gid1Count = sql_fetch_one_cell("select GREATEST(0,count) from sys_goods where uid='$uid' and gid='$gid1'");
	$gid2Count = sql_fetch_one_cell("select GREATEST(0,count) from sys_goods where uid='$uid' and gid='$gid2'");
	$protectedCount = sql_fetch_one_cell("select GREATEST(0,count) from sys_goods where uid='$uid' and gid='12157'");
	
	unlockUser($uid);
	$goodStr = $gid1.','.$gid1Count.','.$gid2.','.$gid2Count.',12157,'.$protectedCount;
	return $goodStr;
}
function getUpgradeResultMsg($isSucc,$isProtected,$targetArmorid)
{
	$msg = "";
	if($isSucc==1){
		$targetArmorName = sql_fetch_one_cell("select name from cfg_armor where id='$targetArmorid' limit 1");
		$msg = sprintf($GLOBALS['upgradeArmor']['upgrade_succ'],$targetArmorName);
	}else{
		if($isProtected){
			$msg = $GLOBALS['upgradeArmor']['upgrade_fail_1'];
		}else{
			$msg = $GLOBALS['upgradeArmor']['upgrade_fail_2'];
		}
	}
	return $msg;
}
function getOneArmorBySid($uid,$sid)
{	
	$armors = sql_fetch_rows("select * from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$uid' and a.sid='$sid' limit 1");
	$ret=array();
	$ret[] = addSpecialArr($armors);
    $ret[] = getArmorNewAttribute($armors);
	$ret[] = getArmorEmbedGoods($armors);
    $ret[] = getTieInfo($armors, -1);
	$ret[] = getTieArmorAttribute($armors);
	$ret[] = getDeifyAttribute($armors);
	$ret[] = getFusionAttribute($armors);
	return $ret;
}