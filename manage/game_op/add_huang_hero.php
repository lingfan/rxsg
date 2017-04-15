<?php
/**
 * @作者：张昌彪
 * @模块：游戏操作 -- 增加黄巾将领
 * @功能：给多个用户添加战场黄巾将领
 * @参数：passports：回车间隔的用户账号（通行证）列表
 * 		  names：回车间隔的玩家名称列表
 *		  huang_hero：增加的黄巾将领
 * @返回：
 */
if (! defined ( "MANAGE_INTERFACE" ))
	exit ();

if (! isset ( $passports )) {
	exit ( "param_not_exist" );
}
if (! isset ( $names )) {
	exit ( "param_not_exist" );
}
if (! isset ( $huang_hero )) {
	exit ( "param_not_exist" );
}

if ((empty ( $passports )) && (empty ( $names ))) {
	$ret [] = "没有君主名或通行证";
	$ret [] = 'false';
}
else {
	if (! empty ( $passports )) {
		$passports = explode ( "\n", $passports );
		foreach ( $passports as $passport ) {
			$passport = addslashes ( trim ( $passport ) );
			$user = sql_fetch_one ( "select * from sys_user where uid > 1000 and passport='$passport' limit 1" );			
			if (empty ( $user )) {
				$ret [] = "不存在帐号：" . $passport . "。";
				$ret [] = 'false';
			}
			else {
				$cityid = sql_fetch_rows ( "select cid from sys_city where uid = '$user[uid]' order by cid" );				
				$left_space = 0;
				foreach ( $cityid as $cid ) {
					$left = cityHasHeroPosition ( $user ['uid'], $cid ['cid'] );
					if ($left > 0)	$left_space += $left;
				}				
				if ($left_space < count ( $huang_hero )) {
					$ret [] = $passport . "城内招贤馆空闲位子不够！";
					$ret [] = 'false';
				}
				else {
					foreach ( $huang_hero as $huang_hero_id ) {
						foreach ( $cityid as $cid2 ) {
							if (cityHasHeroPosition ( $user ['uid'], $cid2 ['cid'] ) > 0) {
								addBattleHero ( $user ['uid'], $huang_hero_id, 1, $cid2 ['cid'] );

								break;
							}
						}
					}
					$ret [] = '成功为' . $user ['passport'] . "[" . $user ['name'] . "]添加黄巾将领";
					$ret [] ='success';
				}
			}
		}
	}
	else {
		$names = explode ( "\n", $names );
		foreach ( $names as $name ) {
			$name = addslashes ( trim ( $name ) );
			$user = sql_fetch_one ( "select * from sys_user where uid > 1000 and name='$name' limit 1" );
			if (empty ( $user )) {
				$ret [] = "不存在君主名：" . $name;
				$ret [] = 'false';
			}
			else {
				$cityid = sql_fetch_rows ( "select cid from sys_city where uid = '$user[uid]' order by cid" );
				$left_space = 0;
				foreach ( $cityid as $cid ) {
					$left = cityHasHeroPosition ( $user ['uid'], $cid ['cid'] );
					if ($left > 0)
						$left_space += $left;
				}
				if ($left_space < count ( $huang_hero )) {
					$ret [] = $passport . "城内招贤馆空闲位子不够！";
					$ret [] = 'false';
				}
				else {
					foreach ( $huang_hero as $huang_hero_id ) {
						foreach ( $cityid as $cid2 ) {
							if (cityHasHeroPosition ( $user ['uid'], $cid2 ['cid'] ) > 0) {
								addBattleHero ( $user ['uid'], $huang_hero_id, 1, $cid2 ['cid'] );
								break;
							}
						}
					}
					$ret [] = '成功为' . $user ['passport'] . "[" . $user ['name'] . "]添加黄巾将领";
					$ret [] ='success';
				}
			}
		}
	}
}

function addBattleHero($uid, $hid, $cnt, $cityId) {
	$chid = $hid;
	$hero = sql_fetch_one ( "select * from cfg_hero where id='$hid'" );
	if (empty ( $hero ))
		throw new Exception ( $GLOBALS ['buyGoods'] ['can_not_exchange'] );
	
	if (cityHasHeroPosition ( $uid, $cityId ) == false) { //招贤馆等级是否够
		throw new Exception ( $GLOBALS ['recruitHero'] ['hotel_level_low'] );
	}
	$_add = intval ( intval ( $hero ['level'] ) / 3 );
	$affairs = intval ( $hero ['affairs_base'] ); //+ $_add; //"内政 "
	$bravery = intval ( $hero ['bravery_base'] ); //+ $_add; //"勇武 "
	$levelLeft = intval ( $hero ['level'] ) - $_add - $_add;
	$wisdom = intval ( $hero ['wisdom_base'] ); //+ $levelLeft; //"智谋 "
	$affairs_add = $_add;
	$bravery_add = $_add;
	$wisdom_add = $levelLeft;
	
	$command = intval ( $hero ['command_base'] ); //+ intval($hero['level']); // "统帅"
	

	$total_exp = sql_fetch_one_cell ( "select total_exp from cfg_hero_level where level='$hero[level]'" );
	
	$val = sprintf ( "'%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','20000'", $uid, $hero ['name'], $hero ['npcid'], $hero ['sex'], $hero ['face'], $cityId, 0, $hero ['level'], $total_exp, $affairs, $bravery, $wisdom, $affairs_add, $bravery_add, $wisdom_add, $hero ['loyalty'], $command );
	
	//$val = sprintf("'%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'",
	//		$uid, $hero['name'], $hero['npcid'], $hero['sex'], $hero['face'], $cityId, 0, $hero['level'],
	//		$hero['exp'], $hero['affairs_base'], $hero['bravery_base'], $hero['wisdom_base'],
	//		$hero['affairs_add'], $hero['bravery_add'], $hero['wisdom_add'], $hero['loyalty']);
	

	$sql = "insert into sys_city_hero (`uid`,`name`,`npcid`,`sex`,
	`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`, `command_base`, `herotype`) values ($val)";
	
	$hid = sql_insert ( $sql );
	$forcemax = intval ( 100 + $hero ['level'] / 5 + ($hero ['bravery_base'] + $hero ['bravery_add']) / 3 );
	$energymax = intval ( 100 + $hero ['level'] / 5 + ($hero ['wisdom_base'] + $hero ['wisdom_add']) / 3 );
	sql_query ( "insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`)
	values ('$hid', '$forcemax','$forcemax','$energymax','$energymax') on duplicate key update `force`='$forcemax',`energy`='$energymax',`force_max`='$forcemax', `energy_max`='$energymax' " );
	sql_query ( "insert into log_goods (uid,gid,count,time,type) values ('$uid','$chid','$cnt',unix_timestamp(), 5)" ); //属于商场购买,但是属于勋章换来的Hero
}
function cityHasHeroPosition($uid, $cid) {
	$officeLevel = sql_fetch_one_cell ( "select level from sys_building where cid='$cid' and bid='11'" );
	if (empty ( $officeLevel ))
		return 0;
	$heroCount = sql_fetch_one_cell ( "select count(*) from sys_city_hero where cid='$cid' and uid='$uid'" );
	return $officeLevel - $heroCount;
}

?>