<?php
	require_once("./interface.php");
	require_once("./utils.php");
	
	
	//1 建筑升级 2拆除建筑  3研究完成 4 招募完成 5城防完成',
	
	function GetAlarmBuildingUpgrading($uid)
	{
		sql_query("update sys_alarm set upgrading=0 where uid='$uid'");
		return GetAlarmBuildingTechnic($uid,1);
	}
	
	function GetAlarmBuildingDestroy($uid)
	{		
		sql_query("update sys_alarm set destroy=0 where uid='$uid'");
		return GetAlarmBuildingTechnic($uid,2);
	}
	
	function GetAlarmTechnicResearch($uid)
	{
		sql_query("update sys_alarm set research=0 where uid='$uid'");	
		return GetAlarmBuildingTechnic($uid,3);
	}
	
	function GetAlarmRecruitment($uid)
	{
		sql_query("update sys_alarm set recruitment=0 where uid='$uid'");
		return GetAlarmBuildingTechnic($uid,4);
	}
	
	function GetAlarmWallDefence($uid)
	{
		sql_query("update sys_alarm set defence=0 where uid='$uid'");
		return GetAlarmBuildingTechnic($uid,5);
	}
	
	function GetAlarmUnionArmy($uid)
	{
		sql_query("update sys_alarm set unionarmy=0 where uid='$uid'");
		return GetAlarmBuildingTechnic($uid,6);
	}

	function GetAlarmCityMorale($uid)
	{
		sql_query("update sys_alarm set morale=0 where uid='$uid'");
		return GetAlarmBuildingTechnic($uid,7);
	}
		
	function GetAlarmCityComplaint($uid)
	{
		sql_query("update sys_alarm set complaint=0 where uid='$uid'");
		return GetAlarmBuildingTechnic($uid,8);
	}
	
	function GetAlarmHeroLoyalty($uid)
	{
		sql_query("update sys_alarm set loyalty=0 where uid='$uid'");
		return GetAlarmBuildingTechnic($uid,9);
	}
	
	
	function GetAlarmBuildingTechnic($uid,$type)
	{
		$upgradings=sql_fetch_rows("select content from log_building_technic_state where uid='$uid' and type='$type' limit 10");
		sql_query("delete from log_building_technic_state where uid='$uid' and type='$type'");
		return $upgradings;   
	}
	
	

	
	
?>