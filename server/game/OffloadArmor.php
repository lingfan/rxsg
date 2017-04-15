<?php
	
	require_once("./interface.php");
	require_once("./utils.php");
	require_once("./HeroFunc.php");

	function offloadArmor($uid,$param)
	{
		$hid=array_shift($param);
		$spart=array_shift($param);
		
		$armorInfo=sql_fetch_one("select * from sys_hero_armor h left join sys_user_armor u on u.sid=h.sid left join cfg_armor c on c.id=u.armorid where h.hid='$hid' and h.spart='$spart'");
		if(empty($armorInfo)) throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
		$heroInfo=sql_fetch_one("select * from sys_city_hero where hid='$hid'");
		if(empty($heroInfo))//$heroInfo['state']>1)
		{
			throw new Exception($GLOBALS['equipArmor']['hero_state_wrong']);
		}
		
		$sid=$armorInfo['sid'];
		sql_query("update sys_user_armor set hid=0 where sid='$sid'");
		sql_query("delete from sys_hero_armor where hid='$hid' and spart='$spart'");
		
		regenerateHeroAttri($uid,$hid);
		if($heroInfo['state']==1)
		{
			updateCityResourceAdd($heroInfo['cid']);
		}
	}
	//清理过期装备，如果穿着，先卸下
	function clearArmor_act($armorid)
	{
		$armors=sql_fetch_rows("select * from sys_hero_armor h left join sys_user_armor u on h.sid=u.sid where h.armorid='$armorid'");
		if(!empty($armors)){
			foreach ($armors as $armorinfo){
				$tArr=array();
				$tArr[]=$armorinfo['hid'];
				$tArr[]=$armorinfo['spart'];
				offloadArmor($armorinfo['uid'],$tArr);
			}
		}
		sql_fetch_one("delete from sys_user_armor where armorid='$armorid'");
	}
	clearArmor_act(10028);
	
	
?>