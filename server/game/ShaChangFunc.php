<?php
require_once ("../config/shachang.php");
require_once ("./HttpClient.php");
//include ("../../server_info.php");
function getCrossShaChangInfo($uid,$param){
	//include ("../../server_info.php");
	$sendParam=array();
	$sendParam['server_guid']=$server_guid;
	$sendParam['uid']=$uid;
	$time=time();
	$sendParam['time']=$time;
	$sendParam['sign']=md5($server_guid.$uid.$time.CROSS_SIGN_KEY);
	//$curl=new cURL();
	//$result=$curl->post(CROSS_ShaChang_CHECK,$sendParam);
	$result=HttpClient::quickPost(CROSS_ShaChang_CHECK,$sendParam);
	if($result==FALSE){
		$ret[]=array('status'=>-1);
	}else{
		$json=json_decode($result,true);
		$ret[]=$json;
	}
	return $ret;
}

function joinCrossShaChang($uid,$param){
	//include ("../../server_info.php");
	$hid=intval(array_shift($param));
	if(!sql_check("select 1 from sys_city_hero where hid=$hid and uid=$uid")){
		throw new Exception($GLOBALS['addHeroPoint']['cant_find_hero']);
	}
	return doJoinCrossShaChang($uid,$hid,$server_guid);
}

function doJoinCrossShaChang($uid,$hid,$server_guid)
{
	$ret=getCityInfoHero($uid,0,$hid);
	$hero=array_shift($ret);
	$newhero['hid']=$hero['hid'];
	$newhero['name']=$hero['name'];
	$newhero['level']=$hero['level'];
	$newhero['sex']=$hero['sex'];
	$newhero['face']=$hero['face'];
	$newhero['mythic']=$hero['npcid']!=0?1:0;
	$newhero['level']=$hero['level'];
	$newhero['command']=$hero['level']+$hero['command_base']+$hero['command_add_on'];
	if($hero['buf1']){
		$newhero['command']=($hero['level']+$hero['command_base'])*1.5+$hero['command_add_on'];
	}
	$newhero['affairs']=$hero['affairs_base']+$hero['affairs_add'];
	if($hero['buf2']){
		$newhero['affairs']=$newhero['affairs']*1.25;
	}
	$newhero['affairs']+=$hero['affairs_add_on'];
	
	$newhero['bravery']=$hero['bravery_base']+$hero['bravery_add'];
	if($hero['buf3']){
		$newhero['bravery']=$newhero['bravery']*1.25;
	}
	$newhero['bravery']+=$hero['bravery_add_on'];
	
	$newhero['wisdom']=$hero['wisdom_base']+$hero['wisdom_add'];
	if($hero['buf4']){
		$newhero['wisdom']=$newhero['wisdom']*1.25;
	}
	$newhero['wisdom']+=$hero['wisdom_add_on'];
	
	$newhero['speed']=$hero['speed_add_on'];
	
	$newhero['attack']=$newhero['bravery']*10+$hero['attack_add_on'];
	$newhero['defence']=$newhero['wisdom']*10+$hero['defence_add_on'];
	
	$armor_data=getHeroArmor($uid,array($hid));
	$armor_data[]=sql_fetch_one_cell("select c.name from sys_hero_skill h left join cfg_book c on c.id=h.skill and c.level=1 where h.hid=".$hero['hid']);
	$armor_data[]=sql_fetch_one("select * from sys_user_book u left join cfg_book c on c.id=u.bid and c.level=u.level where u.uid='$uid' and u.hid=".$hero['hid']);
	$newhero['hero_data']=json_encode($armor_data);
	//武将技能
	//$newhero['skill_name']=sql_fetch_one_cell("select c.name from sys_hero_skill h left join cfg_book c on c.id=h.skill and c.level=1 where h.hid=".$hero['hid']);
	//$newhero['skill_book']=sql_fetch_one("select * from sys_user_book u left join cfg_book c on c.id=u.bid and c.level=u.level where u.uid='$uid' and u.hid=".$hero['hid']);
	
	$user=sql_fetch_one("select * from sys_user where uid='$uid'");
	$sendParam=array();
	$sendParam['server_guid']=$server_guid;
	$sendParam['uid']=$uid;
	$time=time();
	$sendParam['time']=$time;
	$sendParam['user_info']=json_encode($user);
	$sendParam['hero_info']=json_encode($newhero);
	$sendParam['sign']=md5($server_guid.$uid.$time.CROSS_SIGN_KEY);
	//$curl=new cURL();
	//$result=$curl->post(CROSS_ShaChang_JOIN,$sendParam);
	$result=HttpClient::quickPost(CROSS_ShaChang_JOIN,$sendParam);
	$ret=array();
	$ret[0]=0;
	if($result==FALSE){
		$ret[]='net_error';
	}else{
		if($result=='need_user'||$result=='need_hero'||$result=='sign_error'){
			$ret[]='cross_param_error';
		}else if($result=='user_full'){
			throw new Exception($GLOBALS['juezhantianxia']['user_full']);
		}else if($result=='nobility_limit'){
			throw new Exception($GLOBALS['juezhantianxia']['nobility_limit']);
		}else if($result=='hero_level_limit'){
			throw new Exception($GLOBALS['juezhantianxia']['hero_level_limit']);
		}else if($result=='change_hero_time_over'){
			throw new Exception($GLOBALS['juezhantianxia']['change_hero_time_over']);
		}
		else{
			$ret[0]=1;
			$ret[]=$result;
		}
	}
	return $ret;
}

function getCrossShaChangReward($uid){
	include ("../../server_info.php");
	$sendParam=array();
	$sendParam['server_guid']=$server_guid;
	$sendParam['uid']=$uid;
	$time=time();
	$sendParam['time']=$time;
	$sendParam['sign']=md5($server_guid.$uid.$time.CROSS_SIGN_KEY);
	//$curl=new cURL();
	//$result=$curl->post(CROSS_ShaChang_REWARD,$sendParam);
	$result=HttpClient::quickPost(CROSS_ShaChang_REWARD,$sendParam);
	$ret=array();
	if($result==false){
		$ret[0]=0;
		$ret[]='net_error';
	}else{
		$ret[0]=1;
		$rewards=json_decode($result,true);
		if(is_string($rewards)){
			$ret[1]=0;
			if($rewards=='time_error'||$rewards=='sign_error'){
				$ret[]='cross_param_error';
			}else{
				$ret[]='no_award';
			}
		}elseif (is_array($rewards)){
			$ret[1]=1;
			$awards=array();
			foreach ($rewards as $reward){
				$ret_goods=parseAndAddReward($uid,$reward['award_str'],4,4,4,60);
				$awards=array_merge($awards,$ret_goods);
			}
			$award_result[]=$awards;
			$ret[]=$award_result;
		}
	}
	return $ret;
}

function getAllHeroList($uid){
	$heros=sql_fetch_rows("select 
	hid,name,level,
	(command_base+command_add_on) as command,
	(affairs_base+affairs_add+affairs_add_on) as affairs,
	(bravery_base+bravery_add+bravery_add_on) as bravery,
	(wisdom_base+wisdom_add+wisdom_add_on) as wisdom
	 from sys_city_hero where uid='$uid' order by level desc");
	$ret[]=$heros;
	return $ret;
}


function generateRobot()
{
	$server_guids=array('52699f24-fc55-102e-a2e4-001517894d3c'
		,'40f3d64a-042c-102f-a2e4-001517894d3c'
		,'41d5ce10-042c-102f-a2e4-001517894d3c'
		,'41846b7e-042c-102f-a2e4-001517894d3c'
		,'421aaf4e-042c-102f-a2e4-001517894d3c'
		,'425fa3ce-042c-102f-a2e4-001517894d3c'
		,'429a74b8-042c-102f-a2e4-001517894d3c'
		,'42daf452-042c-102f-a2e4-001517894d3c'
		,'43166dfc-042c-102f-a2e4-001517894d3c'
		,'436eb44e-042c-102f-a2e4-001517894d3c');
	$users=sql_fetch_rows("select uid from sys_user");
	foreach($users as $user){
		$uid=$user['uid'];
		$hid=sql_fetch_one_cell("select hid from sys_city_hero where uid=$uid limit 1");
		if(!empty($hid)){
			foreach($server_guids as $sguid){
				doJoinCrossShaChang($uid,$hid,$sguid);
			}
		}
	}
}
