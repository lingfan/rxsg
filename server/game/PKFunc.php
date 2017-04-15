<?php
require_once ("./interface.php");
require_once ("./utils.php");
define ( 'RATE', 50 );

/**
 * 初始化将领状态
 */
function initHeroState($hero) {
	$heroState = array ();
	$heroState['hid'] = intval($hero['hid'] );
	$heroState['sex'] = intval($hero['sex'] );
	$heroState['blood'] = max(intval($hero['energy']) * RATE,500);
	$heroState['attackValue'] = max(intval($hero['bravery']) * 10,10);
	$heroState['defenceValue'] = max(intval($hero['wisdom']) * 10,10);
	$baoji = max(intval($hero['bravery']),10);
	if ($baoji > 3000)
		$baoji = 3000;
	$heroState['baoji'] = $baoji;
	$poji = max(intval($hero['command']),10);
	if ($poji > 3000)
		$poji = 3000;
	$heroState['poji'] = $poji;
	$gedang = max(intval($hero['affair']),10);
	if ($gedang > 3000)
		$gedang = 3000;
	$heroState['gedang'] = $gedang;
	$shanbi = max(intval($hero['wisdom']),10);
	if ($shanbi > 3000)
		$shanbi = 3000;
	$heroState['shanbi'] = $shanbi;
	$heroState['standIndex']=$hero['standIndex'];
	
	return $heroState;
}

/**
 * 伤害公式
 */
function PKDamage($attackValue, $defenceValue) {
	$damage = $attackValue * (1 - $defenceValue / ($defenceValue + 2000));
	return intval ( $damage );
}

/**
 * 计算是否能够触发
 */
function isTrigger($value) {
	$rand = mt_rand ( 1, 20000 );
	if ($value >= $rand) {
		return true;
	} else {
		return false;
	}
}

/**
 * 战斗计算
 */
function battleComute(&$attacker, &$resister, $num) {
	//攻击方数据初始化
	$attckAttack = intval ( $attacker ['attackValue'] );
	//防守方数据初始化
	$resistBlood = intval ( $resister ['blood'] );
	$resistDefence = intval ( $resister ['defenceValue'] );
	//战斗结果
	$curRet = array ();
	//攻击方攻击防守方
	$isBoji = isTrigger ( $attacker ['baoji'] );
	$isPoji = isTrigger ( $attacker ['poji'] );
	$isShanbi = isTrigger ( $resister ['shanbi'] );
	$isGedang = isTrigger ( $resister ['gedang'] );
	if ($isShanbi)
		$isGedang = false; //两者不能同时出现
	if ($isPoji)
		$isBoji = false; //两者不能同时出现
	

	if ($isShanbi) {
		$attckAttack = 0;
		if ($isPoji) {
			$curRet ['flag'] = 1;
			$curRet ['attack'] = 2;
			$curRet ['resist'] = 2;
		} elseif ($isBoji) {
			$curRet ['flag'] = 1;
			$curRet ['attack'] = 1;
			$curRet ['resist'] = 2;
		} else {
			$curRet ['flag'] = 1;
			$curRet ['attack'] = 0;
			$curRet ['resist'] = 2;
		}
	} elseif ($isGedang) {
		if ($isPoji) {
			$curRet ['flag'] = 1;
			$curRet ['attack'] = 2;
			$curRet ['resist'] = 1;
		} elseif ($isBoji) {
			$attckAttack *= 0.75;
			$curRet ['flag'] = 1;
			$curRet ['attack'] = 1;
			$curRet ['resist'] = 1;
		} else {
			$attckAttack *= 0.5;
			$curRet ['flag'] = 1;
			$curRet ['attack'] = 0;
			$curRet ['resist'] = 1;
		}
	} else {
		if ($isPoji) {
			$attckAttack *= 2;
			$curRet ['flag'] = 1;
			$curRet ['attack'] = 2;
			$curRet ['resist'] = 0;
		} elseif ($isBoji) {
			$attckAttack *= 1.5;
			$curRet ['flag'] = 1;
			$curRet ['attack'] = 1;
			$curRet ['resist'] = 0;
		} else {
			$curRet ['flag'] = 0;
			$curRet ['attack'] = 0;
			$curRet ['resist'] = 0;
		}
	}
	$damage = PKDamage ( $attckAttack, $resistDefence );
	$damage = max(1,$damage);
	if ($resistBlood > $damage)
		$resistBlood -= $damage;
	else
		$resistBlood = 0;
	$resister ['blood'] = $resistBlood;
	$curRet['damage'] = $damage;
	$curRet['blood'] = $resistBlood;
	$curRet['attackhid'] = $attacker['hid'];
	$curRet['resisthid'] = $resister['hid'];
	$curRet['attacksex'] = $attacker['sex'];
	$curRet['resistsex'] = $resister['sex'];
	$curRet['battleId'] = $num;
	$curRet['attackStandIndex']=$attacker['standIndex'];
	$curRet['resistStandIndex']=$resister['standIndex'];
	return $curRet;
}
/**
 * 战斗逻辑计算函数
 * 输入：参与双方将领信息array(array(hero1),array(hero2),array(hero3))
 * 		hero = array(hid,command,affair,bravery,wisdom,energy,speed);
 * 输出：战斗结束信息
 */
function startUserPK($attackUser, $resistUser) {
	if (empty ( $attackUser ) || empty ( $resistUser ))
		return array ();
	$ret = array (); //记录战斗结果
	$totalBattle = 0;
	
	while ( ! empty ( $attackUser ) && ! empty ( $resistUser ) ) {
		$attackHero = array_shift ( $attackUser );
		$resistHero = array_shift ( $resistUser );
		$battleRet = array ();
		if (empty ( $attackHero ) || empty ( $resistHero ))
			break;
		if ($attackHero ['speed'] >= $resistHero ['speed']) {
			$attacker = initHeroState ( $attackHero );
			$resister = initHeroState ( $resistHero );
		} else {
			$attacker = initHeroState ( $resistHero );
			$resister = initHeroState ( $attackHero );
		}
		
		while ( $attacker ['blood'] > 0 && $resister ['blood'] > 0 ) {
			$totalBattle ++;
			$battleRet[] = battleComute ( $attacker, $resister, $totalBattle );
			
			if ($resister ['blood'] == 0) {
				if ($attackHero ['hid'] == $attacker ['hid']) {
					$attackHero ['energy'] = intval ( $attacker ['blood'] / RATE );
					array_unshift ( $attackUser, $attackHero );
				} elseif ($resistHero ['hid'] == $attacker ['hid']) {
					$resistHero ['energy'] = intval ( $attacker ['blood'] / RATE );
					array_unshift ( $resistUser, $resistHero );
				}
				break;
			}
			$totalBattle ++;
			$battleRet[] = battleComute ( $resister, $attacker, $totalBattle );
			if ($attacker ['blood'] == 0) {
				if ($attackHero ['hid'] == $resister ['hid']) {
					$attackHero ['energy'] = intval ( $resister ['blood'] / RATE );
					array_unshift ( $attackUser, $attackHero );
				} elseif ($resistHero ['hid'] == $resister ['hid']) {
					$resistHero ['energy'] = intval ( $resister ['blood'] / RATE );
					array_unshift ( $resistUser, $resistHero );
				}
				break;
			}
		}
		$ret ['report'] [] = $battleRet;
	}
	$ret ['totalNum'] = $totalBattle;
	$ret ['endflag'] = 1;
	if (empty ( $resistUser )) {
		$ret ['winer'] = 1;
	} else {
		$ret ['winer'] = 0;
	}
	return $ret;
}
/**
 * 检验用户是否是首次通关，并记录首次通关以及排名
 *
 * @param unknown_type $uid
 * @param unknown_type $battleId
 */
function checkFirstPass($uid, $battleId, $flag) {
	$uid1 = sql_fetch_one_cell("select uid from cfg_pk_first where battleid=$battleId and type=$flag and rankid=1");
	$uid2 = sql_fetch_one_cell("select uid from cfg_pk_first where battleid=$battleId and type=$flag and rankid=2");
	if (sql_check ( "select 1 from cfg_pk_first where battleid=$battleId and type=$flag and rankid=1 and uid=0" )) {
		sql_query ( "update cfg_pk_first set uid=$uid,passtime=unix_timestamp() where battleid=$battleId and type=$flag and rankid=1" );
	} elseif (sql_check ( "select 1 from cfg_pk_first where battleid=$battleId and type=$flag and rankid=2 and uid=0" ) && ($uid != $uid1)) {
		sql_query ( "update cfg_pk_first set uid=$uid,passtime=unix_timestamp() where battleid=$battleId and type=$flag and rankid=2" );
	} elseif (sql_check ( "select 1 from cfg_pk_first where battleid=$battleId and type=$flag and rankid=3 and uid=0" )&& ($uid != $uid1)&& ($uid != $uid2)) {
		sql_query ( "update cfg_pk_first set uid=$uid,passtime=unix_timestamp() where battleid=$battleId and type=$flag and rankid=3" );
	}
}

function getPkGid($battleId, $flag) {
	return 18000 + $battleId * 10 + $flag;
}

/**
 * 发放奖励
 *
 * @param unknown_type $uid
 * @param unknown_type $battleId
 * @param unknown_type $battleLevel
 * @param unknown_type $battleFlag
 * @return unknown
 */
function sendUserReward($uid, $battleId, $battleLevel, $battleFlag) {
	$ret = array();
	$msg='';
	$dropArray=array();
	$gid = getPkGid ( $battleId, $battleFlag );
	$num = mt_rand ( 1, 10 );
	$flag = false;
	if ($num != 3)
		$gid = 0;
	if ($gid > 0) {
		addGoods ( $uid, $gid, 1, 1 );
		$dropArray['flag']=0;
		$dropArray['info']=sql_fetch_one("select * from cfg_goods where gid=$gid");
		$dropArray['count']=1;
		$ret[]=$dropArray;
		$msg .= sql_fetch_one_cell ( "select name from cfg_goods where gid=$gid" ) . '*1,';
	}
	if (! sql_check ( "select 1 from sys_pk_user where uid=$uid" )) {
		sql_query ( "insert into sys_pk_user(uid,normal,special) values('$uid',1,1)" );
	}
	//第一次通关奖励
	if ($battleFlag == 1) {
		$passId = sql_fetch_one_cell ( "select special from sys_pk_user where uid=$uid" );
	} else {
		$passId = sql_fetch_one_cell ( "select normal from sys_pk_user where uid=$uid" );
	}
	$passBattleId = intval ( $passId / 100 );
	$tmpLevel = intval ( $passId % 100 );
	$maxLevel = sql_fetch_one_cell ( "select max(levelid) from cfg_pk_level  where battleid=$battleId and type=$battleFlag" );
	if (($battleId == $passBattleId) && ($maxLevel == $tmpLevel+1) && ($battleLevel == $maxLevel)) {
		$armors = sql_fetch_rows ( "select rewardid,count from cfg_pk_reward where battleid=$passBattleId and type=$battleFlag and rewardtype=1" );
		foreach ( $armors as $armor ) {
			$armorInfo = sql_fetch_one("select * from cfg_armor where id={$armor['rewardid']}");
			addArmor ( $uid, $armorInfo, $armor ['count'], 1 );
			$dropArray['flag']=1;
			$dropArray['info']=sql_fetch_one("select * from cfg_armor where id={$armor['rewardid']}");
			$dropArray['count']=$armor ['count'];
			$ret[]=$dropArray;
			$msg .= sql_fetch_one_cell ( "select name from cfg_armor where id={$armor['rewardid']}" ) . "*{$armor['count']},";
		}
		$goodses = sql_fetch_rows ( "select rewardid,count from cfg_pk_reward where battleid=$passBattleId and type=$battleFlag and rewardtype=0" );
		foreach ( $goodses as $goods ) {
			addGoods ( $uid, $goods ['rewardid'], $goods ['count'], 1 );
			$dropArray['flag']=0;
			$dropArray['info']=sql_fetch_one("select * from cfg_goods where gid={$goods ['rewardid']}");
			$dropArray['count']=$goods ['count'];
			$ret[]=$dropArray;
			$msg .= sql_fetch_one_cell ( "select name from cfg_goods where gid={$goods['rewardid']}" ) . "*{$goods['count']},";
		}
		$flag = true;
		//看下是否首次通关
		checkFirstPass ( $uid, $battleId, $battleFlag );
	}
	//更新通关最大值
	$newId = $battleId * 100 + $battleLevel;
	if ($battleFlag == 0 && $newId > $passId) {
		sql_query ( "update sys_pk_user set normal=$newId where uid=$uid" );
	} elseif ($battleFlag == 1 && $newId > $passId) {
		sql_query ( "update sys_pk_user set special=$newId where uid=$uid" );
	}
	//通关提示
	if (strlen ( $msg ) > 2) {
		$msg = substr($msg,0,-1);
		$username = sql_fetch_one_cell ( "select name from sys_user where uid=$uid" );
		$battlename = sql_fetch_one_cell ( "select battlename from cfg_pk_battle where id=$battleId" );
		$str = sprintf ( $GLOBALS ['userPK'] ['pass_battle_info'], $username, $battlename, $msg );
		$color = 49151;
		if($flag) $color=15627776;
		sendSysInform ( 0, 1, 0, 600, 50000, 1, $color, $str );
	}
	//返回获得物品名称
	return $ret;
}

/**
 * 检验用户是否越级打架
 *
 * @param unknown_type $uid
 * @param unknown_type $battleId
 * @param unknown_type $battleLevel
 */
function checkUserPassLevel($uid,$battleId,$battleLevel,$battleFlag) {
	$passId = 0;
	if ($battleFlag == 1) {
		$passId = sql_fetch_one_cell ( "select special from sys_pk_user where uid=$uid" );
	} else {
		$passId = sql_fetch_one_cell ( "select normal from sys_pk_user where uid=$uid" );
	}
	$passBattleId = intval ( $passId / 100 );
	$tmpLevel = intval ( $passId % 100 );
	$maxLevel = sql_fetch_one_cell ( "select max(levelid) from cfg_pk_level  where battleid=$battleId and type=$battleFlag" );
	if ($battleId == $passBattleId) {
		if ($battleLevel > $maxLevel || $battleLevel > $tmpLevel+1) {
			throw new Exception($GLOBALS['sendCommand']['command_exception']);
		}
	} elseif ($battleId == $passBattleId+1) {
		if ($battleLevel > 1) {
			throw new Exception($GLOBALS['sendCommand']['command_exception']);
		}
	} elseif ($battleId > $passBattleId+1) {
		throw new Exception($GLOBALS['sendCommand']['command_exception']);
	}
}


/**
 * 判断将领的修为等级和将领等级
 *
 * @param unknown_type $uid
 * @param unknown_type $battleId
 */
function checkUserLevel($uid,$battleId,$flag) {
	if ($flag == 0) {//普通之判断君主将等级
		$heroLevel = sql_fetch_one_cell("select level from sys_city_hero where uid=$uid and herotype=1000");
		$needLevel = sql_fetch_one_cell("select hero_level from cfg_pk_battle where id=$battleId");
		if ($heroLevel < $needLevel) {
			throw new Exception($GLOBALS['heroPk']['hero_level_low']);
		}
	} else {//精英之判断修为
		$userLevel = sql_fetch_one_cell("select level from sys_user_level where uid=$uid");
		$needLevel = sql_fetch_one_cell("select user_level from cfg_pk_battle where id=$battleId");
		if ($userLevel < $needLevel) {
			throw new Exception($GLOBALS['heroPk']['user_level_low']);
		}
	}
	
}

/**
 * 接收客户端传来的数据，进行战斗逻辑
 */
function getOneBattleRet($uid, $param) {
	if (count ( $param ) < 4) {
		throw new Exception ( $GLOBALS ['useMojiaGoods'] ['invalid_param'] );
	}
	if (! checkGoods ( $uid, 19200 )) {
		throw new Exception ( $GLOBALS['changeCityPosition']['no_adv_lijianfu'] );
	}
	$msg = '';
	$battleId = intval ( array_shift ( $param ) );
	$battleFlag = intval ( array_shift ( $param ) );
	$battleLevel = intval ( array_shift ( $param ) );
	$battleHids = array_shift ( $param );
	checkUserLevel($uid,$battleId,$battleFlag);
	checkUserPassLevel($uid,$battleId,$battleLevel,$battleFlag);
	if (count ( $battleHids ) < 3) {
		throw new Exception ( $GLOBALS ['useMojiaGoods'] ['invalid_param'] );
	}
	$heroId1 = intval ( array_shift ( $battleHids ) ); //站位1，依次后推
	$heroId2 = intval ( array_shift ( $battleHids ) );
	$heroId3 = intval ( array_shift ( $battleHids ) );
	//检验这些将领是否是玩家的
	$checkNum = sql_fetch_one_cell ( "select count(*) from sys_city_hero where uid=$uid and hid in ('$heroId1','$heroId2','$heroId3')" );
	if ($checkNum != 3) {
		throw new Exception ( $GLOBALS['heroPk']['no_hero_info']);
	}
	$userHero1 = sql_fetch_one ( "select a.hid,a.sex,`force` as energy,11 as standIndex,command_base+command_add_on as command,affairs_base+affairs_add+affairs_add_on as affair,bravery_base+bravery_add+bravery_add_on as bravery,wisdom_base+wisdom_add+wisdom_add_on as wisdom,speed_add_on as speed from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid=$heroId1" );
	$userHero2 = sql_fetch_one ( "select a.hid,a.sex,`force` as energy,12 as standIndex,command_base+command_add_on as command,affairs_base+affairs_add+affairs_add_on as affair,bravery_base+bravery_add+bravery_add_on as bravery,wisdom_base+wisdom_add+wisdom_add_on as wisdom,speed_add_on as speed from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid=$heroId2" );
	$userHero3 = sql_fetch_one ( "select a.hid,a.sex,`force` as energy,13 as standIndex,command_base+command_add_on as command,affairs_base+affairs_add+affairs_add_on as affair,bravery_base+bravery_add+bravery_add_on as bravery,wisdom_base+wisdom_add+wisdom_add_on as wisdom,speed_add_on as speed from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid=$heroId3" );
	//获取NPC将领信息
	$npcHid1 = sql_fetch_one_cell ( "select hid from cfg_pk_level where battleid=$battleId and levelid=$battleLevel and type=$battleFlag and area=1" );
	$npcHero1 = sql_fetch_one ( "select hid,sex,energy+energy_add as energy,21 as standIndex,command_base+command_add as command,affair_base+affair_add as affair,bravery_base+bravery_add as bravery,wisdom_base+wisdom_add as wisdom,speed_base+speed_add as speed from cfg_pk_hero where hid=$npcHid1" );
	$npcHid2 = sql_fetch_one_cell ( "select hid from cfg_pk_level where battleid=$battleId and levelid=$battleLevel and type=$battleFlag and area=2" );
	$npcHero2 = sql_fetch_one ( "select hid,sex,energy+energy_add as energy,22 as standIndex,command_base+command_add as command,affair_base+affair_add as affair,bravery_base+bravery_add as bravery,wisdom_base+wisdom_add as wisdom,speed_base+speed_add as speed from cfg_pk_hero where hid=$npcHid2" );
	$npcHid3 = sql_fetch_one_cell ( "select hid from cfg_pk_level where battleid=$battleId and levelid=$battleLevel and type=$battleFlag and area=3" );
	$npcHero3 = sql_fetch_one ( "select hid,sex,energy+energy_add as energy,23 as standIndex,command_base+command_add as command,affair_base+affair_add as affair,bravery_base+bravery_add as bravery,wisdom_base+wisdom_add as wisdom,speed_base+speed_add as speed from cfg_pk_hero where hid=$npcHid3" );
	
	$ret [] = startUserPK ( array ($userHero1, $userHero2, $userHero3 ), array ($npcHero1, $npcHero2, $npcHero3 ) );
	//扣掉一个军令
	reduceGoods ( $uid, 19200, 1 );
	//发放奖励
	if ($ret [0] ['winer'] == 1) {
		$msg = sendUserReward ( $uid, $battleId, $battleLevel, $battleFlag );
	}
	$ret [0] ['reward'] = $msg;
	
	return $ret;
}

/**
 * 领取通关榜首奖励
 *
 * @param unknown_type $uid
 */
function getPkFirstReward($uid, $param) {
	
	$battleId = intval ( array_shift ( $param ) );
	$flag = intval ( array_shift ( $param ) );
	$rankId = intval ( array_shift ( $param ) );
	
	if (! sql_check ( "select 1 from cfg_pk_first where uid=$uid and battleid=$battleId and rankid='$rankId' and type=$flag" )) {
		throw new Exception ( $GLOBALS ['useMojiaGoods'] ['invalid_param'] );
	}
	if (sql_check ( "select 1 from cfg_pk_first where uid=$uid and battleid=$battleId and rankid='$rankId' and type=$flag and time>1" )) {
		throw new Exception ( $GLOBALS ['king'] ['has_get_reward'] );
	}
	$reward = sql_fetch_one_cell ( "select reward from cfg_pk_first where uid=$uid and battleid=$battleId and type=$flag and rankid='$rankId'" );
	
	$ret = array ();
	$ret [] = parseAndAddReward ( $uid, $reward, 5, 5, 5, 65 );
	
	sql_query ( "update cfg_pk_first set time=unix_timestamp() where uid='$uid' and battleid='$battleId' and rankid='$rankId' and type='$flag'" );
	
	return $ret;
}

/**
 * 初始加载数据
 *
 * @param unknown_type $uid
 * @return unknown
 */
function loadCampaignInitData($uid) {
	$ret = array ();
	$levelInfo = sql_fetch_one ( "select normal,special from sys_pk_user where uid=$uid" );
	if (empty ( $levelInfo )) {
		sql_query ( "insert into sys_pk_user(`uid`,`normal`,`special`) values('$uid','1','1')" );
		$levelInfo = sql_fetch_one ( "select normal,special from sys_pk_user where uid=$uid" );
	}
	$ret [] = $levelInfo;
	$normalHeroes = sql_fetch_rows ( "select b.battleid,b.levelid,a.hero_level,a.user_level,b.hid,c.face,c.sex,c.flag,c.name,b.area,c.energy,c.energy_add,c.command_base,c.command_add,c.affair_base,c.affair_add,c.bravery_base,c.bravery_add,c.wisdom_base,c.wisdom_add,c.speed_base,c.speed_add,FLOOR((c.bravery_base+c.bravery_add)*5+(c.energy+c.energy_add)*10+c.wisdom_base+c.wisdom_add) as battle from cfg_pk_battle a,cfg_pk_level b,cfg_pk_hero c where a.id=b.battleid and b.hid=c.hid and b.type=0" );
	$ret [] = $normalHeroes;
	$specialHeroes = sql_fetch_rows ( "select b.battleid,b.levelid,a.hero_level,a.user_level,b.hid,c.face,c.sex,c.flag,c.name,b.area,c.energy,c.energy_add,c.command_base,c.command_add,c.affair_base,c.affair_add,c.bravery_base,c.bravery_add,c.wisdom_base,c.wisdom_add,c.speed_base,c.speed_add,FLOOR((c.bravery_base+c.bravery_add)*5+(c.energy+c.energy_add)*10+c.wisdom_base+c.wisdom_add) as battle from cfg_pk_battle a,cfg_pk_level b,cfg_pk_hero c where a.id=b.battleid and b.hid=c.hid and b.type=1" );
	$ret [] = $specialHeroes;
	$rewards = array ();
	$battleids = sql_fetch_rows ( "select id from cfg_pk_battle" );
	foreach ( $battleids as $battleid ) {
		$reward = array ();
		$reward ['battleid'] = $battleid ['id'];
		$reward ['flag'] = 0;
		$gid = getPkGid ( $battleid ['id'], 0 );
		$reward ['goods'] = sql_fetch_one ( "select * from cfg_goods where gid=$gid" );
		$rewards [] = $reward;
		$reward = array ();
		$reward ['battleid'] = $battleid ['id'];
		$reward ['flag'] = 1;
		$gid = getPkGid ( $battleid ['id'], 1 );
		$reward ['goods'] = sql_fetch_one ( "select * from cfg_goods where gid=$gid" );
		$rewards [] = $reward;
	}
	$ret [] = $rewards;
	$goods = array ();
	$junling = sql_fetch_one ( "select * from sys_goods where uid=$uid and gid=19200" );
	$goods ['junling'] = $junling ['count'];
	//首次送玩家10个军令
	if (empty ( $junling )) {
		addGoods ( $uid, 19200, 10, 5 );
		$goods ['junling'] = 10;
	}
	$goods ['wuzhuqian'] = sql_fetch_one_cell ( "select count from sys_goods where uid=$uid and gid=10195" );
	$ret [] = $goods;
	$battleDesc = sql_fetch_rows ( "select id,description from cfg_pk_battle" );
	$ret [] = $battleDesc;
	
	return $ret;
}
//加载首通前三数据
function loadPKRewardRank($uid, $param) {
	//取出某个战役最先通过的前三名
	$battleId = intval ( array_shift ( $param ) );
	$battleType = intval ( array_shift ( $param ) );
	
	$rankRewardFrontThree = sql_fetch_rows ( "select * from cfg_pk_first where battleid='$battleId' and type='$battleType' order by rankid asc limit 3" );
	
	for($i = 0; $i < count ( $rankRewardFrontThree ); $i ++) {
		$uidOne = $rankRewardFrontThree [$i]['uid'];
		if(!empty($uidOne))
		{
			$rankRewardFrontThree [$i]['userName'] = sql_fetch_one_cell ( "select name from sys_user where uid='$uidOne'" );
		
			$time = $rankRewardFrontThree [$i]['passtime'];
			$rankRewardFrontThree [$i]['formatTime'] = sql_fetch_one_cell ( "select from_unixtime($time)" );
		}				
		$reward = $rankRewardFrontThree [$i]['reward'];
		$rewardArr = explode(",", $reward);
		if($rewardArr[1] == 0)   //奖励为物品
		{
			$gid = intval($rewardArr[2]);
			$rankRewardFrontThree [$i]['rewardType'] = 0;
			$rankRewardFrontThree [$i]['rewardGood'] = sql_fetch_one("select * from cfg_goods where gid='$gid'");
			$rankRewardFrontThree [$i]['rewardCount'] = $rewardArr[3];
		}else     //奖品为装备
		{
			$armorid = intval($rewardArr[2]);
			$rankRewardFrontThree [$i]['rewardType'] = 1;
			$rankRewardFrontThree [$i]['rewardGood'] = sql_fetch_one("select * from cfg_armor where id='$armorid'");
			$rankRewardFrontThree [$i]['rewardCount'] = $rewardArr[3];
		}
	}
	
	$ret = array ();
	$ret [] = $rankRewardFrontThree;
	
	return $ret;
}

//玩家通过某个楼后 取下最新的楼层信息
function regetCampaignMaxData($uid) {
	$userBattleInfo = sql_fetch_one ( "select normal,special from sys_pk_user where uid=$uid" );
	$junlin = sql_fetch_one_cell ( "select count from sys_goods where uid=$uid and gid=19200" );
	$wuzhuqian = sql_fetch_one_cell ( "select count from sys_goods where uid=$uid and gid=10195" );
	$ret = array ();
	$ret [] = $userBattleInfo;
	$ret [] = $junlin;
	$ret [] = $wuzhuqian;
	return $ret;
}

function buyJunlingFunc($uid, $param) {
	$type = intval ( array_shift ( $param ) ); //0是元宝，1是五珠钱
	$count = intval ( array_shift ( $param ) ); //购买数量
	

	//军令 gid = 19200;    
	$price = 5; //购买价格
	

	if ($type != 0) //目前只允许元宝购买
{
		throw new Exception ( $GLOBALS ['buyGoods'] ['invalid_pay_type'] );
	}
	if ($count <= 0) {
		throw new Exception ( $GLOBALS ['buyGoods'] ['invalid_amount'] );
	}
	
	$needCost = $count * $price;
	if ($type == 0 && ! checkMoney ( $uid, $needCost )) {
		throw new Exception ( $GLOBALS ['buyGoods'] ['no_enough_YuanBao'] );
	}
	
	//	if($type==1 && intval($wuzhu)<$needCost)
	//	{
	//		throw new Exception($GLOBALS['buyGoods']['no_enough_WuZhuQian']);
	//	}
	

	//验证下玩家拥有的最多军令数量为100个
	$maxBuyCount = 0;
	$currentCount = sql_fetch_one_cell ( "select count from sys_goods where uid='$uid' and gid='19200'" );
	if (empty ( $currentCount )) {
		$maxBuyCount = 100;
	} else {
		$maxBuyCount = 100 - intval ( $currentCount );
	}
	if ($count > $maxBuyCount)
		throw new Exception ( sprintf ( $GLOBALS ['buyGoods'] ['junling_max_count'], $maxBuyCount ) );
	
	$ret = array ();
	$ret [] = $type;
	$ret [] = intval ( $currentCount ) + $count;
	//扣元宝或者五铢钱
	if ($type == 0) {
		addMoney ( $uid, 0 - $needCost, $type );
		$money = sql_fetch_one_cell ( "select money from sys_user where uid=$uid" );
		$ret [] = $money;
	}
	//	else 
	//	{
	//		sql_query("update sys_goods set count=count-$needCost where uid='$uid' and gid='10960'");
	//		$ret[] = $wuzhu-$needCost;
	//	}
	//增加军令道具
	addGoods ( $uid, 19200, $count, 0 );
	
	return $ret;
}
//每次获取玩家10个将领
function getAllHeroByUid($uid,$param)
{
	$hasSelectedHidArr = array_shift($param);
	$page = intval(array_shift($param));
	
	if(!is_array($hasSelectedHidArr) || $page<=0)
	{
		throw new Exception($GLOBALS['useMojiaGoods']['invalid_param']);
	}	
	$hidStr = implode($hasSelectedHidArr, ",");
	$ret = array();
	$startIndex = ($page-1)*10;
	if($page == 1)   //君主将需要加载到第一页的第一个
	{
		if(empty($hidStr))
		{
			$kingHero = sql_fetch_one("select * from sys_city_hero h left join mem_hero_blood m on m.hid=h.hid where h.uid='$uid' and herotype='1000'");
			$tenHeros = sql_fetch_rows("select h.*,(h.bravery_base+h.bravery_add+h.bravery_add_on) as heroBravery,m.* from sys_city_hero h,mem_hero_blood m where h.hid=m.hid and h.uid='$uid' and h.herotype<>'1000' order by heroBravery desc limit 9");
			$ret[] = $tenHeros;
			$ret[] = $page;
			$ret[] = $kingHero;
			return $ret;
		}else 
		{
			//判断下君主将有没有选择
			$kingHid = sql_fetch_one_cell("select hid from sys_city_hero where uid='$uid' and herotype='1000'");
			if(in_array($kingHid, $hasSelectedHidArr))
			{
				$tenHeros = sql_fetch_rows("select h.*,(h.bravery_base+h.bravery_add+h.bravery_add_on) as heroBravery,m.* from sys_city_hero h,mem_hero_blood m where h.hid=m.hid and h.hid not in($hidStr) and h.uid='$uid' and h.herotype<>'1000' order by heroBravery desc limit 10");
				$ret[] = $tenHeros;
				$ret[] = $page;
				return $ret;
			}else 
			{
				$kingHero = sql_fetch_one("select * from sys_city_hero h left join mem_hero_blood m on m.hid=h.hid where h.uid='$uid' and herotype='1000'");
				$tenHeros = sql_fetch_rows("select h.*,(h.bravery_base+h.bravery_add+h.bravery_add_on) as heroBravery,m.* from sys_city_hero h,mem_hero_blood m where h.hid=m.hid and h.uid='$uid' and h.hid not in($hidStr) and h.herotype<>'1000' order by heroBravery desc limit 9");
				$ret[] = $tenHeros;
				$ret[] = $page;
				$ret[] = $kingHero;
				return $ret;
			}						
		}		
	}else 
	{
		if(empty($hidStr))
		{
			$tenHeros = sql_fetch_rows("select h.*,(h.bravery_base+h.bravery_add+h.bravery_add_on) as heroBravery,m.* from sys_city_hero h,mem_hero_blood m where h.hid=m.hid and h.uid='$uid' and h.herotype<>'1000' order by heroBravery desc limit $startIndex,10");
		}else 
		{
			$tenHeros = sql_fetch_rows("select h.*,(h.bravery_base+h.bravery_add+h.bravery_add_on) as heroBravery,m.* from sys_city_hero h,mem_hero_blood m where h.hid=m.hid and h.uid='$uid' and h.hid not in($hidStr) and h.herotype<>'1000' order by heroBravery desc limit $startIndex,10");
		}
		$ret[] = $tenHeros;
		$ret[] = $page;
		return $ret;
	}
}
function holdBattleHeroInfo($uid,$param)
{
	$heroCount = intval(count($param));
	if($heroCount!=3)throw new Exception($GLOBALS['assemble']['hero_count_error']);
	 	
	$hid1 = intval(array_shift($param));
	$hid2 = intval(array_shift($param));
	$hid3 = intval(array_shift($param));
	
	$userKingHid = sql_fetch_one_cell("select hid from sys_city_hero where uid='$uid' and herotype='1000'");
	if(empty($userKingHid)) throw new Exception($GLOBALS['useGoods']['king_cannot_find']);
	$userHidArr = array($hid1,$hid2,$hid3);
	if(!in_array($userKingHid, $userHidArr)){
		throw new Exception($GLOBALS['assemble']['king_not_in']);
	}
	$attackHeroInfo = sql_fetch_rows("select * from sys_city_hero where uid='$uid' and hid in($hid1,$hid2,$hid3)"); 
	if(count($attackHeroInfo)==1||count($attackHeroInfo)==2){
		$userHeroHidArr = sql_fetch_rows("select hid from sys_city_hero where uid='$uid'");
		if(count($userHeroHidArr)>=3)throw new Exception($GLOBALS['assemble']['need_change_hero']);
	}		
	sql_query("insert into mem_assemble_hero(`uid`,`hid1`,`hid2`,`hid3`) values('$uid','$hid1','$hid2','$hid3') on duplicate key update hid1='$hid1',hid2='$hid2',hid3='$hid3'");
	
	$ret = array();
	$ret[] = $GLOBALS['assemble']['update_succ'];
	return $ret;
}
function loadUserBattleHero($uid)
{
	$ret = array();
	$hids = sql_fetch_one("select hid1,hid2,hid3 from mem_assemble_hero where uid='$uid'");
	if(!empty($hids))
	{
		$hero1Info = sql_fetch_one("select h.*,b.* from sys_city_hero h,mem_hero_blood b where h.`hid`=b.`hid` and h.uid='$uid' and h.hid={$hids['hid1']}");
		$hero2Info = sql_fetch_one("select h.*,b.* from sys_city_hero h,mem_hero_blood b where h.`hid`=b.`hid` and h.uid='$uid' and h.hid={$hids['hid2']}");
		$hero3Info = sql_fetch_one("select h.*,b.* from sys_city_hero h,mem_hero_blood b where h.`hid`=b.`hid` and h.uid='$uid' and h.hid={$hids['hid3']}");
		
		if(!empty($hero1Info))$ret[] = $hero1Info;
		if(!empty($hero2Info))$ret[] = $hero2Info;
		if(!empty($hero3Info))$ret[] = $hero3Info;
	}	
	return $ret;
}
function loadArenaBattleInitData($uid)
{   
    $nobility=sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
    $nobility=getBufferNobility($uid,$nobility);
    if(intval($nobility)<2)throw new Exception($GLOBALS['assemble']['nobility_not_enough']);  //爵位没达到上造的不上竞技场
	
     //初始化数据开始	
    $userAssembleInfo = sql_fetch_one("select * from mem_assemble where uid='$uid'");
	$userArenaInfo = sql_fetch_one("select * from sys_user_assemble where uid='$uid'");
	if(empty($userAssembleInfo)){initUserAssemBleInfo($uid);}
	if(empty($userArenaInfo)){
		$userRank = sql_fetch_one_cell("select rank from mem_assemble where uid='$uid'");
		sql_query("insert into sys_user_assemble(`uid`,`rank`,`maxrank`) values('$uid','$userRank','$userRank')");
	}
	$userArneaHeroInfo = sql_fetch_one("select * from mem_assemble_hero where uid='$uid'");
	if(empty($userArneaHeroInfo)){
		$kingHid = sql_fetch_one_cell("select hid from sys_city_hero where uid='$uid' and herotype='1000'");
		if(!empty($kingHid)){
			sql_query("insert into mem_assemble_hero(`uid`,`hid1`,`hid2`,`hid3`) values('$uid','$kingHid','$kingHid','$kingHid')");
		}
	}
	//每日挑战次数清零
	$currentZeroTime = sql_fetch_one_cell("select unix_timestamp(curDate())");
	$userLastChallengTime = sql_fetch_one_cell("select next_challenge_time-600 as lasttime from sys_user_assemble where uid='$uid'");
	if(intval($currentZeroTime)>=intval($userLastChallengTime)){
		sql_query("update sys_user_assemble set count='0' where uid='$uid'");
	}   
	//初始化数据结束
	
	$userArenaInfo = sql_fetch_one("select *,GREATEST(0,next_challenge_time-unix_timestamp()) as leavingtime from sys_user_assemble where uid='$uid'");
	$userBattleInfoArr = loadUserAssembleRecord($uid);
	
	$ret = array();
	$ret[] = $userArenaInfo;
	$ret[] = $userBattleInfoArr;
	$ret[] = loadWinRecord();
	$ret[] = loadArenaUserData($uid,0);
	$ret[] = loadUserBattleHero($uid);
	
	return $ret;	
}
function initUserAssemBleInfo($uid)   //如果玩家的竞技场数据为空，初始化下
{
	$maxRank = sql_fetch_one_cell("select max(rank) from mem_assemble");
	if(empty($maxRank))$maxRank=0;
	$userRank = $maxRank+1;
	$kingLevel = sql_fetch_one_cell("select level from sys_city_hero where uid='$uid' and herotype='1000'");
	if(empty($kingLevel))$kingLevel=1;
	sql_query("insert into mem_assemble(`rank`,`uid`,`level`) values('$userRank','$uid','$kingLevel')");	
	resetResistArenaReward($uid);
}
function loadWinRecord(){
	$userSequeceWinArrTmp = sql_fetch_rows("select * from mem_assemble_win_record order by last_challenge_time desc limit 10");
	$userSequeceWinArr = array();
	foreach($userSequeceWinArrTmp as $userSequeceWin)
	{
		$fromnameWin = sql_fetch_one_cell("select name from sys_user where uid={$userSequeceWin['fromuid']}");
		$tonameWin = sql_fetch_one_cell("select name from sys_user where uid={$userSequeceWin['touid']}");
		$fromKingLevel = sql_fetch_one_cell("select level from sys_user_level where uid={$userSequeceWin['fromuid']}");
		$toKingLevel = $userSequeceWin['rKingLevel'];
		$userSequeceWin['fromnameWin'] = $fromnameWin;
		$userSequeceWin['tonameWin'] = $tonameWin;
		$userSequeceWin['fromKingLevel'] = $fromKingLevel;
		$userSequeceWin['toKingLevel'] = $toKingLevel;
		$userSequeceWinArr[] = $userSequeceWin;
	}
	return $userSequeceWinArr;
}
function loadUserAssembleRecord($uid){
	$userBattleInfoArrTmp = sql_fetch_rows("select * from mem_assemble_record where fromuid='$uid' or touid='$uid' order by time desc limit 10");
	$userBattleInfoArr = array();
	foreach($userBattleInfoArrTmp as $userBattleInfo)
	{
		$fromname = sql_fetch_one_cell("select name from sys_user where uid={$userBattleInfo['fromuid']}");
		$fromKingLevel = sql_fetch_one_cell("select level from sys_user_level where uid={$userBattleInfo['fromuid']}");
		$toKingLevel = $userBattleInfo['rKingLevel'];
		if(intval($userBattleInfo['touid'])>1000){
			$toname = sql_fetch_one_cell("select name from sys_user where uid={$userBattleInfo['touid']}");
			$rewardname = sql_fetch_one_cell("select name from cfg_assemble_reward where gid={$userBattleInfo['rewardid']}");
		}else{
			$toname = $GLOBALS['assemble']['special_hero_name'];
			$rewardname = sql_fetch_one_cell("select name from cfg_goods where gid={$userBattleInfo['rewardid']}");
		}		
		$userBattleInfo['fromname']=$fromname;	
		$userBattleInfo['toname']=$toname;
		$userBattleInfo['fromKingLevel']=$fromKingLevel;	
		$userBattleInfo['toKingLevel']=$toKingLevel;
		$userBattleInfo['rewardname']=$rewardname;
		$userBattleInfoArr[] = $userBattleInfo;		
	}
	return $userBattleInfoArr;
}
function loadArenaUserData($uid,$index)
{
	$assembleRankInfoArr = array();
	$npcPos = sql_fetch_one_cell("select rank from mem_assemble where uid<1000");
	if(empty($npcPos))$npcPos=0;
	$assembleRankInfoArr[] = $npcPos;
	if($index==0){  //加载"竞技挑战"10名玩家的数据
		$userRank = sql_fetch_one_cell("select rank from mem_assemble where uid='$uid'");
		if(intval($userRank)<=10){
			$assembleRankInfoTmp = sql_fetch_rows("select *,GREATEST(0,endtime-unix_timestamp()) as getRewardTime from mem_assemble order by rank limit 10");
		}else {
			$startIndex = $userRank-10;
			$assembleRankInfoTmp = sql_fetch_rows("select *,GREATEST(0,endtime-unix_timestamp()) as getRewardTime from mem_assemble order by rank limit $startIndex,10");
		}
	}else if($index==1){   //加载"首榜挑战"10名玩家的数据
		$assembleRankInfoTmp = sql_fetch_rows("select *,GREATEST(0,endtime-unix_timestamp()) as getRewardTime from mem_assemble order by rank limit 10");
	}	

	foreach ($assembleRankInfoTmp as $assembleRankInfo){
		if(intval($assembleRankInfo['uid'])>1000){
			$rewardInfo = sql_fetch_one("select name,base from cfg_assemble_reward where gid={$assembleRankInfo['rewardid']}");
			$kingLevel = sql_fetch_one_cell("select level from sys_user_level where uid={$assembleRankInfo['uid']}");
			$username = sql_fetch_one_cell("select name from sys_user where uid={$assembleRankInfo['uid']}");
		}else{
			$rewardInfo = sql_fetch_one("select h.rewardcount as base,g.name,type from cfg_assemble_special_hero h,cfg_goods g where h.rewardid=g.gid and h.rewardid={$assembleRankInfo['rewardid']} and h.isopen='1'");
			$kingLevel = $rewardInfo['type'];
			$username = $GLOBALS['assemble']['special_hero_name'];
		}
		
		$assembleRankInfo['username']=$username;
		$assembleRankInfo['rewardname']=$rewardInfo['name'];
		$assembleRankInfo['userid']=$assembleRankInfo['uid'];
		$assembleRankInfo['base']=$rewardInfo['base'];
		$assembleRankInfo['kingLevel']=$kingLevel;
		if(intval($assembleRankInfo['uid'])==$uid){
			$assembleRankInfo['canGetRewardCount'] = doCaulRewardCount(true,$assembleRankInfo['endtime'],$rewardInfo['base']);
		}else{
			$assembleRankInfo['canGetRewardCount'] = doCaulRewardCount(false,$assembleRankInfo['endtime'],$rewardInfo['base']);
		}
		$assembleRankInfoArr[] = $assembleRankInfo;
	}			
	return $assembleRankInfoArr;
}
function loadRankBefore10($uid)
{
	$ret = array();
	if(sql_check("select 1 from mem_assemble where uid='$uid'")){
		$ret[] = loadArenaUserData($uid,1);
	}
	return $ret;
}
function loadBeforeUser10($uid)
{
	$ret = array();
	if(sql_check("select 1 from mem_assemble where uid='$uid'")){
		$ret[] = loadArenaUserData($uid,0);
	}	
	return $ret;
}
function startArenaChallenge($uid,$param)
{
	$targetUid = intval(array_shift($param));
	$isSkip = intval(array_shift($param));
	
	if(sql_check("select 1 from sys_user_assemble where uid='$uid' and next_challenge_time>unix_timestamp()"))throw new Exception($GLOBALS['assemble']['time_is_cooling']);
	$userRank = sql_fetch_one_cell("select rank from mem_assemble where uid='$uid'");
	if(empty($userRank))throw new Exception($GLOBALS['assemble']['user_not_exist']);
	$targetUserRankInfo = sql_fetch_one("select * from mem_assemble where uid='$targetUid'");
	if(empty($targetUserRankInfo))throw new Exception($GLOBALS['assemble']['target_not_exist']);
	$nobility=sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
    $nobility=getBufferNobility($uid,$nobility);
	if(intval($nobility)<2)throw new Exception($GLOBALS['assemble']['nobility_not_enough']);
	if($uid==$targetUid)throw new Exception($GLOBALS['assemble']['target_is_wrong']);
	//检测被攻击玩家是否符合攻击条件
	$targetRank = intval($targetUserRankInfo['rank']);
	if($targetRank>10){
		$userBeforeMax = $userRank-9;
		if(intval($targetUserRankInfo['rank'])>=$userRank ||intval($targetUserRankInfo['rank'])<$userBeforeMax){
			throw new Exception($GLOBALS['assemble']['target_not_exist']);
		}
	}
	//检测攻防两方的将领信息
	$attackHeroConfig = sql_fetch_one("select * from mem_assemble_hero where uid='$uid'");
	$resistHeroConfig = sql_fetch_one("select * from mem_assemble_hero where uid='$targetUid'");
	if(empty($attackHeroConfig)||empty($resistHeroConfig))throw new Exception($GLOBALS['assemble']['hero_data_error']);
	checkHero($uid,$attackHeroConfig,$targetUid,$resistHeroConfig);
	 //将领可能被系统更新了将领配置，需要重新取下
	$attackHeroConfig = sql_fetch_one("select * from mem_assemble_hero where uid='$uid'");
	$resistHeroConfig = sql_fetch_one("select * from mem_assemble_hero where uid='$targetUid'"); 
	
	$attackHeroPartInfo1 = sql_fetch_one ( "select a.hid,a.sex,`force` as energy,11 as standIndex,command_base+command_add_on as command,affairs_base+affairs_add+affairs_add_on as affair,bravery_base+bravery_add+bravery_add_on as bravery,wisdom_base+wisdom_add+wisdom_add_on as wisdom,speed_add_on as speed from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid={$attackHeroConfig['hid1']}" );
	$attackHeroPartInfo2 = sql_fetch_one ( "select a.hid,a.sex,`force` as energy,12 as standIndex,command_base+command_add_on as command,affairs_base+affairs_add+affairs_add_on as affair,bravery_base+bravery_add+bravery_add_on as bravery,wisdom_base+wisdom_add+wisdom_add_on as wisdom,speed_add_on as speed from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid={$attackHeroConfig['hid2']}" );
	$attackHeroPartInfo3 = sql_fetch_one ( "select a.hid,a.sex,`force` as energy,13 as standIndex,command_base+command_add_on as command,affairs_base+affairs_add+affairs_add_on as affair,bravery_base+bravery_add+bravery_add_on as bravery,wisdom_base+wisdom_add+wisdom_add_on as wisdom,speed_add_on as speed from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid={$attackHeroConfig['hid3']}" );
	if($targetUid>1000)
	{
		$resistHeroPartInfo1 = sql_fetch_one ( "select a.hid,a.sex,`force` as energy,21 as standIndex,command_base+command_add_on as command,affairs_base+affairs_add+affairs_add_on as affair,bravery_base+bravery_add+bravery_add_on as bravery,wisdom_base+wisdom_add+wisdom_add_on as wisdom,speed_add_on as speed from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid={$resistHeroConfig['hid1']}" );
		$resistHeroPartInfo2 = sql_fetch_one ( "select a.hid,a.sex,`force` as energy,22 as standIndex,command_base+command_add_on as command,affairs_base+affairs_add+affairs_add_on as affair,bravery_base+bravery_add+bravery_add_on as bravery,wisdom_base+wisdom_add+wisdom_add_on as wisdom,speed_add_on as speed from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid={$resistHeroConfig['hid2']}" );
		$resistHeroPartInfo3 = sql_fetch_one ( "select a.hid,a.sex,`force` as energy,23 as standIndex,command_base+command_add_on as command,affairs_base+affairs_add+affairs_add_on as affair,bravery_base+bravery_add+bravery_add_on as bravery,wisdom_base+wisdom_add+wisdom_add_on as wisdom,speed_add_on as speed from sys_city_hero a,mem_hero_blood b where a.hid=b.hid and a.hid={$resistHeroConfig['hid3']}" );
	}else 
	{		//获取NPC将领信息
		$resistHeroPartInfo1 = sql_fetch_one ( "select hid,sex,energy+energy_add as energy,21 as standIndex,command_base+command_add as command,affair_base+affair_add as affair,bravery_base+bravery_add as bravery,wisdom_base+wisdom_add as wisdom,speed_base+speed_add as speed from cfg_pk_hero where hid={$resistHeroConfig['hid1']}" );
		$resistHeroPartInfo2 = sql_fetch_one ( "select hid,sex,energy+energy_add as energy,22 as standIndex,command_base+command_add as command,affair_base+affair_add as affair,bravery_base+bravery_add as bravery,wisdom_base+wisdom_add as wisdom,speed_base+speed_add as speed from cfg_pk_hero where hid={$resistHeroConfig['hid2']}" );
		$resistHeroPartInfo3 = sql_fetch_one ( "select hid,sex,energy+energy_add as energy,23 as standIndex,command_base+command_add as command,affair_base+affair_add as affair,bravery_base+bravery_add as bravery,wisdom_base+wisdom_add as wisdom,speed_base+speed_add as speed from cfg_pk_hero where hid={$resistHeroConfig['hid3']}" );
	}
	
	//每日挑战次数清零  处理这些人0点的时候没关过竞技场
	$currentZeroTime = sql_fetch_one_cell("select unix_timestamp(curDate())");
	$userLastChallengTime = sql_fetch_one_cell("select next_challenge_time-600 as lasttime from sys_user_assemble where uid='$uid'");
	if(intval($currentZeroTime)>=intval($userLastChallengTime)){
		sql_query("update sys_user_assemble set count='0' where uid='$uid'");
	}  
	
	//如果玩家今日超过10次，就得扣元宝
	$attackAssemInfo = sql_fetch_one("select * from sys_user_assemble where uid='$uid'");
	if(empty($attackAssemInfo))throw new Exception($GLOBALS['waigua']['invalid']);
	if(intval($attackAssemInfo['count'])>=10)
	{
		$addCount = intval($attackAssemInfo['count'])-9;
		$needMoney = $addCount*5;
		if(sql_check("select 1 from sys_user where uid='$uid' and money<'$needMoney'"))throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
		addMoney($uid,-$needMoney,620);
	}
	//如果是首榜挑战，扣元宝
	if($targetRank<=10){
		if($isSkip||($userRank>$targetRank+9)){  //如果玩家的排名在攻击者9名之外的，肯定是首榜挑战
			if(!sql_check("select 1 from sys_user where uid='$uid' and money>=50"))throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
			addMoney($uid,-50,619);	
		}		
	}
	$ret[] = holdBattleHeroBaseInfo($targetUid,$attackHeroConfig,$resistHeroConfig);
	$ret[] = startUserPK (array($attackHeroPartInfo1,$attackHeroPartInfo2,$attackHeroPartInfo3),array($resistHeroPartInfo1,$resistHeroPartInfo2,$resistHeroPartInfo3));
	
	$msg = dealWithResult($ret[1]['winer'],$uid,$targetUid,$userRank,$targetRank,$targetUserRankInfo);
	updateUserArenaChallenge($uid);
	
	$ret[1]['reward']=$msg;
	
	return $ret;	
}
function holdBattleHeroBaseInfo($targetUid,$attackHeroConfig,$resistHeroConfig)
{
	$heroArr = array();
	$heroArr[] = sql_fetch_one("select h.*,b.* from sys_city_hero h,mem_hero_blood b where h.`hid`=b.`hid` and h.`hid`={$attackHeroConfig['hid1']}");
	$heroArr[] = sql_fetch_one("select h.*,b.* from sys_city_hero h,mem_hero_blood b where h.`hid`=b.`hid` and h.`hid`={$attackHeroConfig['hid2']}");
	$heroArr[] = sql_fetch_one("select h.*,b.* from sys_city_hero h,mem_hero_blood b where h.`hid`=b.`hid` and h.`hid`={$attackHeroConfig['hid3']}");
	if(intval($targetUid)>1000){
		$heroArr[] = sql_fetch_one("select h.*,b.* from sys_city_hero h,mem_hero_blood b where h.`hid`=b.`hid` and h.`hid`={$resistHeroConfig['hid1']}");
		$heroArr[] = sql_fetch_one("select h.*,b.* from sys_city_hero h,mem_hero_blood b where h.`hid`=b.`hid` and h.`hid`={$resistHeroConfig['hid2']}");
		$heroArr[] = sql_fetch_one("select h.*,b.* from sys_city_hero h,mem_hero_blood b where h.`hid`=b.`hid` and h.`hid`={$resistHeroConfig['hid3']}");
	}else{
		$heroArr[] = sql_fetch_one("select *,FLOOR((bravery_base+bravery_add)*5+(energy+energy_add)*10+wisdom_base+wisdom_add) as battle from cfg_pk_hero where `hid`={$resistHeroConfig['hid1']}");
		$heroArr[] = sql_fetch_one("select *,FLOOR((bravery_base+bravery_add)*5+(energy+energy_add)*10+wisdom_base+wisdom_add) as battle from cfg_pk_hero where `hid`={$resistHeroConfig['hid2']}");
		$heroArr[] = sql_fetch_one("select *,FLOOR((bravery_base+bravery_add)*5+(energy+energy_add)*10+wisdom_base+wisdom_add) as battle from cfg_pk_hero where `hid`={$resistHeroConfig['hid3']}");
	}	
	return $heroArr;
}
function checkHero($attackUid,$attackHeroInfo,$resistUid,$resistHeroInfo)
{
	$aHid1 = intval($attackHeroInfo['hid1']);
	$aHid2 = intval($attackHeroInfo['hid2']);
	$aHid3 = intval($attackHeroInfo['hid3']);
	$rHid1 = intval($resistHeroInfo['hid1']);
	$rHid2 = intval($resistHeroInfo['hid2']);
	$rHid3 = intval($resistHeroInfo['hid3']);
	$rUid = intval($resistHeroInfo['uid']);
	
	//必须包含君主将
	$attackKingHid = sql_fetch_one_cell("select hid from sys_city_hero where uid='$attackUid' and herotype='1000'");
	if(empty($attackKingHid)) throw new Exception($GLOBALS['useGoods']['king_cannot_find']);
	$attackHidArr = array($aHid1,$aHid2,$aHid3);
	if(!in_array($attackKingHid, $attackHidArr)){
		throw new Exception($GLOBALS['assemble']['king_not_in']);
	}
	$attackConfigHeroCount = sql_fetch_one_cell("select count(1) from sys_city_hero where uid='$attackUid' and hid in($aHid1,$aHid2,$aHid3)"); 
	if(intval($attackConfigHeroCount)==1||intval($attackConfigHeroCount)==2){
		$attackOwnHeroCount = sql_fetch_one_cell("select count(hid) from sys_city_hero where uid='$attackUid'");
		if(intval($attackOwnHeroCount)>=3){  //大于三个将领，要求玩家重新配置，否则默认三个君主将
			throw new Exception($GLOBALS['assemble']['need_change_hero']);
		}else{
			sql_query("update mem_assemble_hero set hid1='$attackKingHid',hid2='$attackKingHid',hid3='$attackKingHid' where uid='$attackUid'");
		}
	}		
	//防守方   
	if($rUid>1000)   //系统刷出来的 就不做处理了
	{
		$resistKingHid = sql_fetch_one_cell("select hid from sys_city_hero where uid='$resistUid' and herotype='1000'");
		if(empty($resistKingHid)) throw new Exception($GLOBALS['assemble']['resist_hero_error']);	
		$resistHidArr = array($rHid1,$rHid2,$rHid3);	
		if(!in_array($resistKingHid, $resistHidArr)){
			sql_query("update mem_assemble_hero set hid1='$resistKingHid' where uid='$resistUid'");
			$rHid1 = $resistKingHid;
		}
		$resistConfigHeroCount = sql_fetch_one_cell("select count(1) from sys_city_hero where uid='$resistUid' and hid in($rHid1,$rHid2,$rHid3)");
		if(intval($resistConfigHeroCount)==1||intval($resistConfigHeroCount)==2){
			$resistHeroHidArr = sql_fetch_rows("select hid from sys_city_hero where uid='$resistUid' and herotype!=1000");
			if(count($resistHeroHidArr)>=2){
				$heroRandHids = array_rand($resistHeroHidArr,2);
				$hid2Tmp = $resistHeroHidArr[$heroRandHids[0]]['hid'];
				$hid3Tmp = $resistHeroHidArr[$heroRandHids[1]]['hid'];
				sql_query("update mem_assemble_hero set hid2='$hid2Tmp',hid3='$hid3Tmp' where uid='$resistUid'");
			}else{
				sql_query("update mem_assemble_hero set hid2='$resistKingHid',hid3='$resistKingHid' where uid='$resistUid'");
			}
		}
	}	
}
function updateUserArenaChallenge($attackUid)
{	
	sql_query("update sys_user_assemble set count=count+1 where uid='$attackUid'");
	sql_query("update sys_user_assemble set next_challenge_time=unix_timestamp()+600 where uid='$attackUid'");
}
function dealWithResult($isWin,$attackUid,$resistUid,$attackRank,$resistRank,$resistArenaInfo)  //$isWin=true 攻击方胜利,$maxRank是最高排名，值越小越高
{
	$winArray = array(3,5,10,15,30);
	if(!sql_check("select 1 from sys_user_assemble where uid='$attackUid'")){
		sql_query("insert into sys_user_assemble(`uid`,`rank`,`maxrank`) select '$attackUid',rank,rank from mem_assemble where uid='$attackUid'");
	}
	if(!sql_check("select 1 from sys_user_assemble where uid='$resistUid'")){
		sql_query("insert into sys_user_assemble(`uid`,`rank`,`maxrank`) select '$resistUid',rank,rank from mem_assemble where uid='$resistUid'");
	}
	$ret = array();
	$attackName = sql_fetch_one_cell("select name from sys_user where uid='$attackUid'");
	$rKingLevel=0;
	if(intval($resistUid)>1000){
		$rKingLevel = sql_fetch_one_cell("select level from sys_user_level where uid='$resistUid'");
		if(empty($rKingLevel))$rKingLevel=0;
	}else{
		$rKingLevel = sql_fetch_one_cell("select type from cfg_assemble_special_hero where isopen='1'");
		if(empty($rKingLevel))$rKingLevel=0;
	}
	if($isWin)
	{		
		$attackNewRank = $attackRank>$resistRank?$resistRank:$attackRank;   //新排名取小值
		$resistNewRank = $resistRank;
		if(sql_check("select 1 from sys_user_assemble where uid='$attackUid' and rank>'$attackNewRank'")){  //玩家在攻击对象之后      更新当前排名
			$resistNewRank = $resistRank+1;  //防守方排名往后退一位
			//更新攻击方和防守方之间玩家的名次
			$userCurRank = sql_fetch_one_cell("select rank from sys_user_assemble where uid='$attackUid'");
			if(intval($resistUid)>1000){
				updatePartUserRank($userCurRank,$attackNewRank,true);
				sql_query("update sys_user_assemble set `rank`='$attackNewRank' where uid='$attackUid'");
				sql_query("update mem_assemble set `rank`='$attackNewRank' where uid='$attackUid'");
			}else{
				sql_query("update mem_assemble set rank='$resistRank' where uid='$attackUid'");
				sql_query("update sys_user_assemble set rank='$resistRank' where uid='$attackUid'");
				sql_query("delete from mem_assemble where uid='$resistUid'");
				$maxRank = sql_fetch_one_cell("select max(rank) from mem_assemble");
				updatePartUserRank($maxRank,$attackRank,false);
			}						
		}else{
			if(intval($resistUid)<1000){
				sql_query("delete from mem_assemble where uid='$resistUid'");
				$maxRank = sql_fetch_one_cell("select max(rank) from mem_assemble");
				updatePartUserRank($maxRank,$resistRank,false);
			}
		}
		if(sql_check("select 1 from sys_user_assemble where uid='$attackUid' and maxrank>'$attackNewRank'")){ //更新最高排名
			sql_query("update sys_user_assemble set `maxrank`='$attackNewRank' where uid='$attackUid'");
		}				
		
		$winTimes = sql_fetch_one_cell("select times from mem_assemble_record where fromuid='$attackUid' order by time desc limit 1");
		if(empty($winTimes))$winTimes=0;
		$winTimes++;
		if(in_array($winTimes, $winArray)){  //连胜记录公告
			sql_query("insert into mem_assemble_win_record(`fromuid`,`touid`,`times`,`rKingLevel`,`last_challenge_time`) values('$attackUid','$resistUid','$winTimes','$rKingLevel',unix_timestamp())");
			$chatMsg = parseWinChatInform($winTimes,$attackName);
			if(strlen($chatMsg)>0){
				sendSysInform(0,1,0,600,50000,1,49151,$chatMsg);
			}
		}
		$currentMaxWinRecord = sql_fetch_one_cell("select maxwin from sys_user_assemble where uid='$attackUid'");
		if(intval($winTimes)>intval($currentMaxWinRecord)){ //更新玩家的连胜记录
			sql_query("update sys_user_assemble set `maxwin`='$winTimes' where uid='$attackUid'");
		}
		
		$rewardTypeBase = sql_fetch_one_cell("select base from cfg_assemble_reward where `gid`={$resistArenaInfo['rewardid']}");
		if(intval($resistUid>1000)){
			$rewardCount = doCaulRewardCount(false,intval($resistArenaInfo['endtime']),intval($rewardTypeBase));
		}else{
			$rewardCount = $resistArenaInfo['count'];
		}	
		//如果对方已经过了倒计时时间就拿不到奖励
		$isChange = $attackNewRank!=$attackRank?1:0;
		if(sql_check("select 1 from mem_assemble where uid='{$resistArenaInfo['uid']}' and endtime<unix_timestamp()")){
			sql_query("insert into mem_assemble_record(`fromuid`,`touid`,`flag`,`rewardcount`,`time`,`times`,`attackrank`,`resistrank`,`ischange`,`rKingLevel`) values('$attackUid','$resistUid','1','0',unix_timestamp(),'$winTimes','$attackNewRank','$resistNewRank','$isChange','$rKingLevel')");	
		}else{
			sql_query("insert into mem_assemble_record(`fromuid`,`touid`,`flag`,`rewardid`,`rewardcount`,`time`,`times`,`attackrank`,`resistrank`,`ischange`,`rKingLevel`) values('$attackUid','$resistUid','1','{$resistArenaInfo['rewardid']}','$rewardCount',unix_timestamp(),'$winTimes','$attackNewRank','$resistNewRank','$isChange','$rKingLevel')");
			//攻击方拿奖励
			$ret[] = addAttackArenaReward($attackUid,$resistArenaInfo);
			//重置防守方竞技场奖励
			if(intval($resistArenaInfo['uid']>1000)){
				resetResistArenaReward($resistUid);	
			}
		}	
		//打的是系统将领
		if(intval($resistUid)<1000){
			sql_query("update cfg_assemble_special_hero set isopen='0'");	
			sql_query("delete from mem_assemble_hero where uid<'1000'");
			$npcMsg = sprintf($GLOBALS['assemble']['attack_npc_succ_inform'],$attackName);
			sendSysInform(0,1,0,600,50000,1,16247152,$npcMsg);
		}							
	}else{
		sql_query("insert into mem_assemble_record(`fromuid`,`touid`,`flag`,`time`,`times`,`attackrank`,`resistrank`,`rKingLevel`) values('$attackUid','$resistUid','0',unix_timestamp(),'0','$attackRank','$resistRank','$rKingLevel')");
	}
	//是否需要发聊天公告
	if($attackRank>$resistRank){    //攻击方的排名必须在防守方的后面
		$resistName = sql_fetch_one_cell("select name from sys_user where uid='$resistUid'");
		$msg="";
		if($isWin&&$resistRank<=10){
			$msg = sprintf($GLOBALS['assemble']['attack_succ_inform'],$attackName,$resistRank,$resistName);
		}
		if(!$isWin&&$resistRank<=10){
			$msg = sprintf($GLOBALS['assemble']['resist_succ_inform'],$resistRank,$resistName,$attackName);
		}
		if(strlen($msg)>0){
			sendSysInform(0,1,0,600,50000,1,16247152,$msg);
		}
	}	
	return $ret;
}
function updatePartUserRank($maxValueRank,$minValueRank,$isUp)   //$isUp=ture表示玩家自己往上爬，否则往下跳
{
	if($isUp){
		$endValueRank = $maxValueRank-1;  //踢掉用户自己这条数据
		$updateUserUps = sql_fetch_rows("select uid,rank from mem_assemble where rank between $minValueRank and $endValueRank order by rank asc");
		foreach($updateUserUps as $updateUserUp)
		{
			$currentRankUp = sql_fetch_one_cell("select rank from mem_assemble where uid={$updateUserUp['uid']}");
			$nextRank = $currentRankUp+1;
			sql_query("update mem_assemble set rank='$nextRank' where uid={$updateUserUp['uid']}");
			sql_query("update sys_user_assemble set rank='$nextRank' where uid={$updateUserUp['uid']}");
		}
	}else {
		$startValueRank = $minValueRank+1;  //踢掉用户自己这条数据
		$updateUserDowns = sql_fetch_rows("select uid,rank from mem_assemble where rank between $startValueRank and $maxValueRank order by rank asc");
		foreach($updateUserDowns as $updateUserDown)
		{
			$currentRankDown = sql_fetch_one_cell("select rank from mem_assemble where uid={$updateUserDown['uid']}");
			$preRank = $currentRankDown-1;
			sql_query("update mem_assemble set rank='$preRank' where uid={$updateUserDown['uid']}");
			sql_query("update sys_user_assemble set rank='$preRank' where uid={$updateUserDown['uid']}");
			//检测下是否需要更新玩家的最高排名
			if(sql_check("select 1 from sys_user_assemble where maxrank>$preRank and uid={$updateUserDown['uid']}"))
			{
				sql_query("update sys_user_assemble set maxrank='$preRank' where uid={$updateUserDown['uid']}");
			}
		}
	}	
}
function getArenaRankRewardInfo($uid)
{
	$rankBefore3UserInfoTmps = sql_fetch_rows("select rank,uid as userid,name from log_assemble_reward where rank<=3");
	$rankBefore3UserInfo = array();
	foreach($rankBefore3UserInfoTmps as $rankBefore3UserInfoTmp)
	{
		$rUid = $rankBefore3UserInfoTmp['userid'];
		$kingLevel = sql_fetch_one_cell("select level from sys_user_level where uid='$rUid'");
		if(empty($kingLevel))$kingLevel=0;
		$rankBefore3UserInfoTmp['level']=$kingLevel;
		$rankBefore3UserInfo[] = $rankBefore3UserInfoTmp;
	}
	
	$rankRewardConfArr = sql_fetch_rows("select * from cfg_activity_rank_reward where actid='1000001'");
	$rankRewardArr = parseRewardGood($rankRewardConfArr);
	$userRank = sql_fetch_one("select rank,reward from log_assemble_reward where uid='$uid'");
	if(empty($rankRewardArr))$rankRewardArr=array();
	if(empty($userRank))$userRank=null;
	
	$ret = array();
	$ret[] = $rankBefore3UserInfo;
	$ret[] = $rankRewardArr;
	$ret[] = $userRank;
	return $ret;
}
function addAttackArenaReward($attactUid,$resistArenaInfo)
{	
	$lastCid = sql_fetch_one_cell("select lastcid from sys_user where uid='$attactUid'");
	$rewardGid = intval($resistArenaInfo['rewardid']);	
	if(intval($resistArenaInfo['uid'])>1000){
		$rewardType = sql_fetch_one("select type,base from cfg_assemble_reward where `gid`={$resistArenaInfo['rewardid']}");
		if(empty($rewardType))throw new Exception($GLOBALS['hero']['xidian_unvalid']);
		$rewardCount = doCaulRewardCount(false,intval($resistArenaInfo['endtime']),intval($rewardType['base']));
	}else{
		$rewardCount = $resistArenaInfo['count'];
	}
		
	$rewardArray = array();
	if(intval($resistArenaInfo['uid'])<1000||intval($rewardType['type'])==0){
		addGoods($attactUid,$rewardGid,$rewardCount,619);
		$rewardArray['flag']=0;
		$rewardArray['info']=sql_fetch_one("select * from cfg_goods where gid=$rewardGid");
		$rewardArray['count']=$rewardCount;
		$rewardArray['gtype']=0;
	}else if(intval($rewardType['type'])==1){
		$armor = sql_fetch_one("select * from cfg_armor where id='$rewardGid'");
		addArmor($attactUid, $armor, $rewardCount, 619);
		$rewardArray['flag']=1;
		$rewardArray['info']=$armor;
		$rewardArray['count']=$rewardCount;
		$rewardArray['gtype']=1;
	}else if(intval($rewardType['type'])==2){
		if($rewardGid==-1){
			addCityResources($lastCid, 0, 0, 0, 0, $rewardCount);
		}else if($rewardGid==-2){
			addCityResources($lastCid,0, 0, 0, $rewardCount, 0);
		}else if($rewardGid==-3){
			addCityResources($lastCid,$rewardCount, 0, 0, 0, 0);
		}
		$goodName = sql_fetch_one_cell("select name from cfg_assemble_reward where gid={$resistArenaInfo['rewardid']}");
		$rewardArray['flag']=2;
		$rewardArray['info']=$goodName;
		$rewardArray['count']=$rewardCount;
	}
	return $rewardArray;
}
function doCaulRewardCount($isUser,$endtime,$rewardTypeBase)
{
	if($isUser){   //玩家自己的奖励一次性算好 
		$rewardCount = $rewardTypeBase*5;
	}else{	
		$now = sql_fetch_one_cell("select unix_timestamp()");
		$lefttime = $endtime-$now;
		
		$multiple = (18000-$lefttime)/3600;
		if($multiple>5.0)$multiple=5.0;     //奖励最高累计5小时	 
		$rewardCount = max(floor($rewardTypeBase*$multiple),0);													
	}
	return $rewardCount;
}
function resetResistArenaReward($uid)
{
	$rewardInfos = sql_fetch_rows("select * from cfg_assemble_reward");
	$rate = mt_rand(1, 100);
	$sum=0;
	$gid=-10;
	$count=0;
	foreach($rewardInfos as $rewardInfo)
	{
		$sum += $rewardInfo['rate'];
		if($sum>=$rate){
			$gid = $rewardInfo['gid'];
			$count = $rewardInfo['base'];
			break;
		}
	}
	$endtime = sql_fetch_one_cell("select unix_timestamp()")+18000;    //5小时倒计时
	sql_query("update mem_assemble set `rewardid`='$gid',`count`='$count',`endtime`='$endtime' where uid='$uid'");	
}
function parseRewardGood($rewardArr)
{
	$rewardObjArr = array();
	$rankIndex = 1;
	foreach($rewardArr as $rewardObj)
	{
		$rewardStr = $rewardObj['reward'];
		$goods = explode ( ",", $rewardStr );
		$goodcnt = $goods [0];
		for($i = 1; $i < $goodcnt * 3; $i += 3) {  //只会循环一次，每个奖励都是一个good或armor
			$type = $goods [$i];
			$gid = $goods [$i + 1];
			$cnt = $goods [$i + 2];
			if($type==0){
				$goodObj= sql_fetch_one("select * from cfg_goods where gid='$gid'");
			}else if($type==1){
				$goodObj= sql_fetch_one("select * from cfg_armor where id='$gid'");
			}
			$goodObj['rewardType'] = $type;
			$goodObj['rewardCount'] = $cnt;
			$goodObj['minrank'] = $rewardObj['minrank'];
			$goodObj['maxrank'] = $rewardObj['maxrank'];
		}
		$rewardObjArr[]=$rankIndex;
		$rewardObjArr[]=$goodObj;		
		$rankIndex++;
	}
	return $rewardObjArr;
}
function getArenaRankReward($uid)
{
	$userRankInfo = sql_fetch_one("select * from log_assemble_reward where uid='$uid'");
	if(empty($userRankInfo))throw new Exception($GLOBALS['assemble']['reward_not_exist']);
	if(intval($userRankInfo['reward'])>0) throw new Exception($GLOBALS['king']['has_get_reward']);
	
	$rank = $userRankInfo['rank'];
	$rewardInfo = sql_fetch_one_cell("select reward from cfg_activity_rank_reward where actid='1000001' and minrank<=$rank and maxRank>=$rank");
	if(empty($rewardInfo))throw new Exception($GLOBALS['kingReward']['not_find_data']);
	
	$ret = array();
	sql_query("update log_assemble_reward set reward='1',time=unix_timestamp() where uid='$uid'");
		
	$ret[] = $userRankInfo['rank'];
	$ret[] = parseAndAddReward($uid, $rewardInfo, 619, 619, 619, 619);
	
	return $ret;	
}
function clearArenaCoolTime($uid)
{
	if(!sql_check("select 1 from sys_user_assemble where uid='$uid' and next_challenge_time>unix_timestamp()")){
		throw new Exception($GLOBALS['assemble']['time_not_cooling']);
	}
	
	if(!sql_check("select 1 from sys_user where uid='$uid' and money>=10"))throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
	addMoney($uid, -10, 619);
	sql_query("update sys_user_assemble set next_challenge_time=unix_timestamp() where uid='$uid'");
	
	$ret = array();
	$ret[] = 1;    //随便传个参数告诉客户端
	return $ret;
}
function getArenaReward($uid,$param)
{
	$rewardUid = intval(array_shift($param));
	if($rewardUid != $uid)throw new Exception($GLOBALS['assemble']['reward_target_error']);
	
	$userArenaInfo = sql_fetch_one("select * from mem_assemble where uid='$uid'");
	if(empty($userArenaInfo))throw new Exception($GLOBALS['assemble']['user_not_exist']);
	if(sql_check("select 1 from mem_assemble where uid='$uid' and endtime>unix_timestamp()"))throw new Exception($GLOBALS['assemble']['reward_cooling_exist']);
	
	$rewardObj = addAttackArenaReward($uid,$userArenaInfo);	
	if(is_array($rewardObj['info'])){
		$rewardObj['info']['flag'] = $rewardObj['flag'];
		$rewardObj['info']['count'] = $rewardObj['count'];
		$rewardObj['info']['gtype'] = $rewardObj['gtype'];	
		$subRet[] = 1;
		$addArr[] = $rewardObj['info'];   //为了套用原来的物品弹出框，这里再套一层
		$subRet[] = $addArr;
	}else{
		$subRet[] = 0;
		$subRet[] = $rewardObj['info'].'*'.$rewardObj['count'];
	}	
	resetResistArenaReward($uid);
	
	$ret[] = $subRet;		
	return $ret;
}
function parseWinChatInform($winTimes,$attackName)
{
	$msg = "";
	switch(intval($winTimes))
	{
		case 3:
			$msg = sprintf($GLOBALS['assemble']['attack_succ_inform_3'],$attackName);
			break;
		case 5:
			$msg = sprintf($GLOBALS['assemble']['attack_succ_inform_5'],$attackName);
			break;
		case 10:
			$msg = sprintf($GLOBALS['assemble']['attack_succ_inform_10'],$attackName);
			break;
		case 15:
			$msg = sprintf($GLOBALS['assemble']['attack_succ_inform_15'],$attackName);
			break;
		case 30:
			$msg = sprintf($GLOBALS['assemble']['attack_succ_inform_30'],$attackName);
			break;
		default:break;		
	}
	return $msg;
}
function refreshAssembleRankInfo($uid,$param)
{
	$index = intval(array_shift($param));
	$indexArr = array(0,1);
	if(!in_array($index, $indexArr))throw new Exception($GLOBALS['useMojiaGoods']['invalid_param']);
	if(!sql_check("select 1 from mem_assemble where uid='$uid'"))throw new Exception($GLOBALS['assemble']['user_not_exist']);
	if(!sql_check("select 1 from sys_user_assemble where uid='$uid'"))throw new Exception($GLOBALS['assemble']['user_not_exist']);
	if(sql_check("select 1 from sys_user_assemble where uid='$uid' and next_refresh_time>unix_timestamp()"))throw new Exception($GLOBALS['assemble']['refresh_cooling_exist']);	
	
	$ret = array();	
	$ret[] = loadUserAssembleRecord($uid);;
	$ret[] = loadWinRecord();
	$ret[] = loadArenaUserData($uid,$index);
	sql_query("update sys_user_assemble set next_refresh_time=unix_timestamp()+6 where uid='$uid'");	
	return $ret;
}
function gotoSelectedRank($uid,$param)
{
	$gotoRank = intval(array_shift($param));
	$rankArr = array(50,100,200,300,500);
	if(!in_array($gotoRank, $rankArr))throw new Exception($GLOBALS['assemble']['goto_rank_error']);
	
	$userRank = sql_fetch_one_cell("select rank from mem_assemble where uid='$uid'");
	if(empty($userRank))throw new Exception($GLOBALS['assemble']['user_not_exist']);
	if(intval($userRank)==$gotoRank)throw new Exception($GLOBALS['assemble']['goto_not_need']);
	$maxRank = sql_fetch_one_cell("select max(rank) from mem_assemble");
	if($gotoRank>intval($maxRank)){
		$tip = sprintf($GLOBALS['assemble']['goto_over_max'],$maxRank);
		throw new Exception($tip);
	}
	
	if(sql_check("select 1 from sys_user where uid='$uid' and money<$gotoRank"))throw new Exception($GLOBALS['buyGoods']['no_enough_YuanBao']);
	addMoney($uid, -$gotoRank, 619);
	
	if($userRank>$gotoRank){  //往上爬
		updatePartUserRank($userRank,$gotoRank,true);
		if(sql_check("select 1 from sys_user_assemble where uid='$uid' and maxrank>$gotoRank")){
			sql_query("update sys_user_assemble set maxrank='$gotoRank' where uid='$uid'");
		}
	}else {   //往下爬
		updatePartUserRank($gotoRank,$userRank,false);
	}
	sql_query("update sys_user_assemble set `rank`='$gotoRank' where uid='$uid'");
	sql_query("update mem_assemble set `rank`='$gotoRank' where uid='$uid'");
	$userRankInfo = sql_fetch_one("select rank,maxrank from sys_user_assemble where uid='$uid'");
	if(empty($userRankInfo))$userRankInfo=null;
	
	$arr=array();
	$arr[] = $userRankInfo;
	$arr[] = loadArenaUserData($uid,0);
	return $arr;
}