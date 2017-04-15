<?php                      
require_once("./interface.php");
require_once("./utils.php");
require_once("./GoodsFunc.php");

function doGetCityHero($uid,$cid)
{	
	
	return getCityInfoHero($uid,$cid);
}

function doGetOfficeValidPosition($uid,$cid)
{
    $office_level = sql_fetch_one_cell("select level from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_OFFICE);
    $hero_count = sql_fetch_one_cell("select count(*) from sys_city_hero where uid='$uid' and cid='$cid'");
    return $office_level - $hero_count;
}
function getOfficeInfo($uid,$cid)
{
	$office = sql_fetch_one("select * from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_OFFICE." order by level desc limit 1");
	if (empty($office))
	{   
		throw new Exception($GLOBALS['getOfficeInfo']['no_office_built']); 
	}
	return doGetBuildingInfo($uid,$cid,$office['xy'],ID_BUILDING_OFFICE,$office['level']);
}
function updateCityChiefResAdd($cid,$hid){
	updateCityResourceAdd($cid);
}
function setCityChief($uid,$cid,$param){  
	$uid=intval($uid);
	$cid=intval($cid);
	//检验当前城池是否属于该用户。
	checkCityOwner($cid,$uid);
	//在变更前，对将领的信息进行验证。
	foreach ($param as $heroState) {
		if(!is_array($heroState)){
			throw new Exception($GLOBALS['setCityChief']['set_chief_fail_2']);
		}
	    $hid = array_shift($heroState);
	    $cheiftype = array_shift($heroState);
		if ($hid > 0) {
	        if (!sql_check("select * from sys_city_hero where cid='$cid' and hid='$hid' and state in (0,1,7,8)")) {
	            throw new Exception($GLOBALS['setCityChief']['set_chief_fail']);
	        }
	    }
	}
	//开始变更将领的状态和相关资源的变化。
	//0：无职位，1：城守，7：主将，8：军师
	/*
	foreach ($param as $heroState) {
		$hid = array_shift($heroState);
	    $cheiftype = array_shift($heroState);
	    $flag = false;
	    if (sql_check("select 1 from sys_city where chiefhid=$hid and cid=$cid")) $flag = true;
	    
	    if($cheiftype==0 && $flag)
	    	doSetCityChief($uid,$cid,0);
	    else if($cheiftype==1)
	    	doSetCityChief($uid,$cid,$hid);
	    else if($cheiftype==7)
	    	doSetCityGeneral($uid,$cid,$hid);
	    else if($cheiftype==8)
	    	doSetCityCounsellor($uid,$cid,$hid);
	}   
    */    
	$sets=array();    
	foreach ($param as $heroState){
		if(!is_array($heroState)){
			throw new Exception($GLOBALS['setCityChief']['set_chief_fail_2']);
		}
		$hid = array_shift($heroState);
	    $cheiftype = array_shift($heroState); 
	    $oldtype=sql_fetch_one_cell("select state from sys_city_hero where hid=$hid");
	    //if($cheiftype==0){
    	 if($oldtype==1){
    	 	 doSetCityChief($uid,$cid,0);          
    	 }else if($oldtype==7){
    	 	doSetCityGeneral($uid,$cid,0);
    	 }else if($oldtype==8){
    	 	doSetCityCounsellor($uid,$cid,0);
    	 }
	    if($cheiftype>0){
	    	array_push($sets,array("hid"=>$hid,"cheiftype"=>$cheiftype,"oldtype"=>$oldtype));
	    }
	}
	foreach ($sets as $set) {
		$newtype=$set['cheiftype'];
		$hid=$set['hid'];
		if($newtype==1){
			doSetCityChief($uid,$cid,$hid);
		}else if($newtype==7){
			doSetCityGeneral($uid,$cid,$hid);
		}else if($newtype==8){
			doSetCityCounsellor($uid,$cid,$hid);
		}
	}
	
    return getOfficeInfo($uid,$cid);    
}

function heroIsInTroop($hid)
{
	if($hid==0)
		return 0;
	$hero = sql_fetch_one("select * from sys_troops where hid=$hid and uid!=0 limit 1");
	if(empty($hero))
		return 0;
	else
		return 1;
}
function checkHeroSelf($cid,$hid) {
	if (empty($hid)) return;
	if (sql_check("select 1 from sys_city where chiefhid=$hid and cid=$cid")) {
		sql_query("update sys_city set chiefhid=0 where cid=$cid");
	} else if (sql_check("select 1 from sys_city where counsellorid=$hid and cid=$cid")) {
		sql_query("update sys_city set counsellorid=0 where cid=$cid");
	} else if (sql_check("select 1 from sys_city where generalid=$hid and cid=$cid")) {
		sql_query("update sys_city set generalid=0 where cid=$cid");
	}
}
//设置城守
function doSetCityChief($uid,$cid,$hid){
	checkHeroSelf($cid,$hid);
	$oldChief = sql_fetch_one_cell("select chiefhid from sys_city where cid='$cid'");
	
	if(1 == heroIsInTroop($oldChief))
    {
    	throw new Exception($GLOBALS['setCityChief']['set_chief_hero_busy']);
    }
	
    if ($oldChief > 0)
    {
        sql_query("update sys_city_hero set state=0 where hid='$oldChief'");
    }
    if ($hid > 0)
    {
        sql_query("update sys_city_hero set state=1 where hid='$hid'");
        sql_query("update mem_city_resource m,sys_city_hero h set m.`chief_loyalty`=h.`loyalty` where m.cid='$cid' and h.hid='$hid'");
        updateCityChiefResAdd($cid,$hid);  
        completeTask($uid,85);  
        
    }
    else
    {
        sql_query("update mem_city_resource set `chief_loyalty`=0 where cid='$cid'");
        sql_query("update sys_city_res_add set `resource_changing`=1 where cid='$cid'");
    }
    //将领技能
    sql_query("update sys_city_res_add set skill_gold_add=0,skill_food_add=0,skill_wood_add=0,skill_rock_add=0,skill_iron_add=0 where cid=$cid");
    if (!empty($hid)) {
	    $attrValue=0;
	    $attrValue = sql_fetch_one_cell("select b.attr from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=8 and a.hid=$hid");
	    if (!empty($attrValue)) {//苛捐杂税
	    	sql_query("update sys_city_res_add set skill_gold_add=$attrValue where cid=$cid");
	    }
	    $attrValue=0;
		$attrValue = sql_fetch_one_cell("select b.attr from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=9 and a.hid=$hid");
	    if (!empty($attrValue)) {//安居乐业
	    	sql_query("update sys_city_res_add set skill_gold_add=$attrValue,skill_food_add=$attrValue,skill_wood_add=$attrValue,skill_rock_add=$attrValue,skill_iron_add=$attrValue where cid=$cid");
	    }
	    $attrValue=0;
		$attrValue = sql_fetch_one_cell("select b.attr from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=10 and a.hid=$hid");
	    if (!empty($attrValue)) {//五谷丰登
	    	sql_query("update sys_city_res_add set skill_food_add=$attrValue where cid=$cid");
	    }
	    $attrValue=0;
		$attrValue = sql_fetch_one_cell("select b.attr from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=11 and a.hid=$hid");
	    if (!empty($attrValue)) {//茂林密谷
	    	sql_query("update sys_city_res_add set skill_wood_add=$attrValue where cid=$cid");
	    }
	    $attrValue=0;
		$attrValue = sql_fetch_one_cell("select b.attr from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=12 and a.hid=$hid");
	    if (!empty($attrValue)) {//裂石穿云
	    	sql_query("update sys_city_res_add set skill_rock_add=$attrValue where cid=$cid");
	    }
	    $attrValue=0;
		$attrValue = sql_fetch_one_cell("select b.attr from sys_user_book a,cfg_book b where a.bid=b.id and a.level=b.level and a.bid=13 and a.hid=$hid");
	    if (!empty($attrValue)) {//铸炼冶金
	    	sql_query("update sys_city_res_add set skill_iron_add=$attrValue where cid=$cid");
	    }
	    sql_query("update sys_city_res_add set `resource_changing`=1 where cid='$cid'");
    }
    
    sql_query("update sys_city set chiefhid='$hid' where cid='$cid'");   
}
//设置主将
function doSetCityGeneral($uid,$cid,$hid){
	checkHeroSelf($cid,$hid);
	$oldChief = sql_fetch_one_cell("select generalid from sys_city where cid='$cid'");
	if(1 == heroIsInTroop($oldChief))
    {
    	throw new Exception($GLOBALS['setCityChief']['set_chief_hero_busy']);
    }
	
    if ($oldChief > 0)
    {
        sql_query("update sys_city_hero set state=0 where hid='$oldChief'");
    }
    if ($hid > 0)
    {
        sql_query("update sys_city_hero set state=7 where hid='$hid'");
        sql_query("update mem_city_resource m,sys_city_hero h set m.`chief_loyalty`=h.`loyalty` where m.cid='$cid' and h.hid='$hid'");         
        
    }
    sql_query("update sys_city set generalid='$hid' where cid='$cid'");   
	completeTaskWithTaskid($uid, 328);
}
//设置军师
function doSetCityCounsellor($uid,$cid,$hid){
	checkHeroSelf($cid,$hid);
	$oldChief = sql_fetch_one_cell("select counsellorid from sys_city where cid='$cid'");
	
	if(1 == heroIsInTroop($oldChief))
    {
    	throw new Exception($GLOBALS['setCityChief']['set_chief_hero_busy']);
    }
	
    if ($oldChief > 0)
    {
        sql_query("update sys_city_hero set state=0 where hid='$oldChief'");
    }
    if ($hid > 0)
    {
        sql_query("update sys_city_hero set state=8 where hid='$hid'");
        sql_query("update mem_city_resource m,sys_city_hero h set m.`chief_loyalty`=h.`loyalty` where m.cid='$cid' and h.hid='$hid'");         
        
    }
    sql_query("update sys_city set counsellorid='$hid' where cid='$cid'");    

    completeTaskWithTaskid($uid, 330);
}
?>