<?php
require_once ("./interface.php");
require_once ("./utils.php");
require_once ("./GoodsFunc.php");
require_once("./ActFunc.php");

function getShopInfo($uid, $param) {
	$ret = array ();
	//五铢钱
	$ret [] = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='10960'");
	//积分
	$ret [] = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='888888'");
	$ret [] = sql_fetch_rows ( "SELECT * FROM cfg_shop WHERE onsale=1 AND starttime<=UNIX_TIMESTAMP() AND endtime>UNIX_TIMESTAMP() AND `group` not in (6,7) AND (gid<160001 or gid>160051) and (commend='0' or commend='2') ORDER BY POSITION,id" );//gid z 160001 到160051直接是赤壁道具，不让出现在商场
	$ret [] = sql_fetch_rows ( "SELECT * FROM cfg_shop WHERE onsale=1 AND starttime<=UNIX_TIMESTAMP() AND endtime>UNIX_TIMESTAMP() AND `group` not in (6,7) AND (gid<160001 or gid>160051) and (commend='1' or commend='2') ORDER BY POSITION,id" );	
	$wuZhuGoods = sql_fetch_rows("select g.*,c.price,c.type from cfg_goods_copper c left join cfg_goods g on c.gid=g.gid where c.type='0' and c.onsale='1' and (c.commend='1' or c.commend=2)");
	$wuZhuThings = sql_fetch_rows("select t.*,c.price,c.type from cfg_goods_copper c left join cfg_things t on c.gid=t.tid where c.type='1' and c.onsale='1' and (c.commend='1' or c.commend=2)");
	$ret[] = array_merge($wuZhuGoods,$wuZhuThings);
	$copperGoods = sql_fetch_rows("select g.*,c.price,c.type from cfg_goods_copper c left join cfg_goods g on c.gid=g.gid where c.type='0' and c.onsale='1' and (c.commend='0' or c.commend=2)");
	$copperThings = sql_fetch_rows("select t.*,c.price,c.type from cfg_goods_copper c left join cfg_things t on c.gid=t.tid where c.type='1' and c.onsale='1' and (c.commend='0' or c.commend=2)");
	$ret[] = array_merge($copperGoods,$copperThings);
	completeTask($uid,541);
	return $ret;
}

function getGoodsHeroAttr($uid, $param) {
	$hid = intval(array_shift ( $param ));
	$hinfo = sql_fetch_one ( "select * from cfg_hero where id=$hid" );
	if (empty ( $hinfo )) {
		throw new Exception ( $GLOBALS ['buyGoods'] ['no_tip'] );
	}
	$ret = array ();
	$ret [] = $hinfo;
	return $ret;
}

function getGoodsArmorAttr($uid, $param) {
	$aid = intval(array_shift ( $param ));
	$armorinfo = sql_fetch_one ( "select * from cfg_armor where id=$aid" );
	if (empty ( $armorinfo )) {
		throw new Exception ( $GLOBALS ['buyGoods'] ['no_tip'] );
	}
	$ret = array ();
	$ret [] = $armorinfo;
	
	return $ret;
}

function addBattleHero($uid, $hid, $cnt, $cityId) {
	$cityId=intval($cityId);
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
	sql_query ( "insert into log_goods (uid,gid,count,time,type) values ('$uid','$chid','$cnt',unix_timestamp(), 12)" ); //属于商场购买,但是属于勋章换来的Hero


}

function addBattleArmor($uid, $aid, $cnt) {
	$armor_column = sql_fetch_one_cell ( "select `armor_column` from sys_user where uid=$uid" );
	$curCount = sql_fetch_one_cell ( "select count(*) from sys_user_armor where uid='$uid' and hid=0" );
	if ($curCount + $cnt >= $armor_column) {
		throw new Exception ( $GLOBALS['buyGoods']['armor_box_full'] );
	}
	
	$arminfo = sql_fetch_one ( "select * from cfg_armor where id='$aid'" );
	if (empty ( $arminfo ))
		throw new Exception ( $GLOBALS ['buyGoods'] ['can_not_exchange'] );
	$hp = $arminfo ['ori_hp_max'];
	sql_query ( "insert into sys_user_armor (uid,armorid,hp, hp_max, hid) values ('$uid','$aid',$hp*10, '$hp', 0)" );
	updateBattleOpenState($uid,$aid);
	sql_query ( "insert into log_goods (uid,gid,count,time,type) values ('$uid','$aid','$cnt',unix_timestamp(), 11)" ); //属于商场购买,但是属于勋章换来的装备
}

function addCreditAndMedal($uid, $credit, $medal, $medalTypeId) {
	sql_query ( "update sys_user set honour=honour+'$credit' where uid='$uid'" );
	//sql_query ( "update sys_things set count=count+'$medal' where uid='$uid' and tid='$medalTypeId'" );
	addThings($uid,$medalTypeId,$medal,2);
	//sql_query("insert into log_money (uid,count,time,type) values ('$uid','$money',unix_timestamp(),'$type')");
}

function addGongunAndMedal($uid, $credit, $medal, $medalTypeId) {
	//sql_query ( "update sys_user set gongxun=gongxun+'$credit' where uid='$uid'" );
	//sql_query ( "update sys_things set count=count+'$medal' where uid='$uid' and tid='$medalTypeId'" );
	addThings($uid,$medalTypeId,$medal,2);
	sendChibiRemoteRequest($uid,"addGongxun",$credit);
	//sql_query("insert into log_money (uid,count,time,type) values ('$uid','$money',unix_timestamp(),'$type')");
}

/**
 * 是不是赤壁里面的商品，如果是的话就返回true
 *
 * @param unknown_type $goods
 * @return unknown
 */

function isChiBiGoods($gid){
	$chibibattleid=2;//赤壁商城的battlefieldid
	$sql="SELECT a.id,b.battleBelong FROM cfg_shop a,cfg_things b WHERE a.onsale=1  AND (a.`group`=6 or a.`group`=7 or a.`battleshop`= 1) AND a.medalTypeId=b.tid and b.battleBelong=".$chibibattleid." and a.id=".$gid;
	if(sql_fetch_one($sql)){
		return true;
	}else{
		return false;
	}
}
/**
 * 
 *是不是孟获物品
 *
 * @param unknown_type $idshop
 * @return unknown
 */
function isMenghuoGoods($id){
	if($id>=41001&&$id<=41009){//孟获物品的id号
		return true;
	}else{
		return false;
	}
}

function buyGoods($uid, $param) {
	$id = intval(array_shift ( $param ));
	$cnt = intval ( array_shift ( $param ) );
	$paytype = intval ( array_shift ( $param ) );
	if ($paytype != 0 && $paytype != 1) {
		throw new Exception ( $GLOBALS ['buyGoods'] ['invalid_pay_type'] );
	}
	
	if ($cnt < 1)
		throw new Exception ( $GLOBALS ['buyGoods'] ['invalid_amount'] );
		
	$gid = intval(array_shift($param)); //比较猥琐的判断方法，如果参数为空了，array_shift返回的是NULL
	if(!empty($gid)){
		$id = sql_fetch_one_cell("select * from cfg_shop where gid = '$gid' and onsale = 1");
		$goods = sql_fetch_one ( "select * from cfg_shop where gid='$gid' and onsale=1 and starttime<=unix_timestamp() and endtime>unix_timestamp()" );
		if (empty ( $goods ))
			throw new Exception ( $GLOBALS ['buyGoods'] ['stop_sale'] );
	}
	else {
		$goods = sql_fetch_one ( "select * from cfg_shop where id='$id' and onsale=1 and starttime<=unix_timestamp() and endtime>unix_timestamp()" );
		if (empty ( $goods ))
			throw new Exception ( $GLOBALS ['buyGoods'] ['stop_sale'] );
	}
	
	if(($goods['rebate']==1)&&($paytype==1)){
		throw new Exception ( $GLOBALS ['buyGoods'] ['invalid_rebate'] );
	}
	$moneyNeed = $cnt * $goods ['price'];
	if (! lockUser ( $uid ))
		throw new Exception ( $GLOBALS ['pacifyPeople'] ['server_busy'] );
	$userInfo = sql_fetch_one ( "select nobility, money,gift from sys_user where uid='$uid'" );
	//推恩
	$userInfo ['nobility'] = getBufferNobility ( $uid, $userInfo ['nobility'] );
	//元宝
	$userMoney = $userInfo ['money'];
	//礼金
	$userGift = $userInfo ['gift'];
	if ($paytype == 0 && ($userMoney < $moneyNeed))
		throw new Exception ( $GLOBALS ['buyGoods'] ['no_enough_YuanBao'] );
	if ($paytype == 1 && ($userGift < $moneyNeed))
		throw new Exception ( $GLOBALS ['buyGoods'] ['no_enough_Gift'] );
	
	if (($id == 121) && ($userInfo ['nobility'] < 1))
		throw new Exception ( $GLOBALS ['buyGoods'] ['nobility_limit'] );
	if ($goods ['totalCount'] < 2000000000) {
		if ($goods ['totalCount'] == 0)
			throw new Exception ( $GLOBALS ['buyGoods'] ['sold_out'] );
	}
	if (($goods ['userbuycnt'] > 0) || ($goods ['daybuycnt'] > 0) || ($goods ['totalCount'] < 2000000000)) //属于限制商品
	{	
		$rebate = false;//特价商品的购买限制只适用于促销档期
		if($goods['rebate']>0){
			$rebate = true;
			$rebateStartTime=$goods['starttime'];
		}
		
		if ($rebate) {
			$totalBuycnt = intval ( sql_fetch_one_cell ( "select sum(count) from log_shop where shopid = '$id' and time>=$rebateStartTime" ) );
		}else {		
			$totalBuycnt = intval ( sql_fetch_one_cell ( "select sum(count) from log_shop_buy_cnt where `sid`='$id'" ) );
		}
		if (($goods ['totalCount'] < 2000000000) && ($totalBuycnt+$cnt>$goods ['totalCount'])) {
			$validnum = $goods ['totalCount']-$totalBuycnt;
			if($validnum>0){
				$msg = sprintf ( $GLOBALS['buyGoods']['reach_remain_amount_TotalLimit'],$goods ['totalCount'], $totalBuycnt, $validnum );
				throw new Exception ( $msg );
			}else {
				throw new Exception ( $GLOBALS ['buyGoods'] ['sold_out'] );
			}		
		}
		
		if ($rebate) {
			$buycnt = intval ( sql_fetch_one_cell ( "select sum(count) from log_shop where uid ='$uid' and shopid = '$id' and time>=$rebateStartTime" ) );
		}else {
			$buycnt = intval ( sql_fetch_one_cell ( "select `count` from log_shop_buy_cnt where uid='$uid' and `sid`='$id'" ) );
		}
		if (($goods ['userbuycnt'] > 0) && ($buycnt + $cnt > $goods ['userbuycnt'])) {
			if ($goods ['userbuycnt'] > $buycnt) {
				$remain = $goods ['userbuycnt'] - $buycnt;
				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_remain_amountLimit'], $goods ['userbuycnt'], $buycnt, $remain );
				throw new Exception ( $msg );
			} else {
				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_buy_limit'], $goods ['userbuycnt'] );
				throw new Exception ( $msg );
			}
		}
		
		$todaybuycnt = intval ( sql_fetch_one_cell ( "select sum(count) from log_shop where uid ='$uid' and shopid = '$id' and date(now())=date(from_unixtime(time))" ) );
		if (($goods ['daybuycnt'] > 0) && ($todaybuycnt + $cnt > $goods ['daybuycnt'])) {
			if ($goods ['daybuycnt'] > $todaybuycnt) {
				$remain = $goods ['daybuycnt'] - $todaybuycnt;
				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_remain_amount_todayLimit'], $goods ['daybuycnt'], $buycnt, $remain );
				throw new Exception ( $msg );
			} else {
				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_buy_todayLimit'], $goods ['daybuycnt'] );
				throw new Exception ( $msg );
			}
		}
		sql_query ( "insert into log_shop_buy_cnt (`uid`,`sid`,`count`) values ('$uid','$id','$cnt') on duplicate key update `count`=`count`+'$cnt'" );
	}

	//一手交货 
	$logGoodType=2;
	if($paytype==0){
		$logGoodType=61;
	}else{
		$logGoodType=62;
	}
	if(isChiBiGoods($id)){
		//发送到远程赤壁里面去调用远程的chibi里面的方法
		addChibiGoods( $uid, $goods ['gid'], $goods ['pack'] * $cnt, 2);
	}else if(isMenghuoGoods($id)){
		addMenghuoGood($uid,$goods['gid'],$goods['pack']*$cnt,2);
	}else{
		addGoods ( $uid, $goods ['gid'], $goods ['pack'] * $cnt, $logGoodType );
	}
	//一手交钱
	if ($paytype == 0)
		addMoney ( $uid, - $moneyNeed, 10 );
	else if ($paytype == 1)
		addGift ( $uid, - $moneyNeed, 10 );
		
	sql_query ( "update sys_user set last_pay='$paytype' where uid='$uid'" );
	sql_query ( "insert into log_shop (uid,shopid,count,price,time,paytype) values ('$uid','$id','$cnt','$goods[price]',unix_timestamp(),$paytype)" );
	
	completeTask ( $uid, 366 );
	completeTask ( $uid, 531 );
	completeTaskWithTaskid ( $uid, 294 );
	
	unlockUser ( $uid );
	$ret = array ();
	$ret [] = $paytype;
	
	$actMsg = false;
	if ($paytype == 0){
		$ret [] = $userMoney - $moneyNeed;
		$actMsg = checkAndDoShopAct($moneyNeed, $uid);//查看和执行商城活动
	}else if ($paytype == 1) {
		$ret [] = $userGift - $moneyNeed;
	}
	sql_query ( "update sys_user set last_pay='$paytype' where uid='$uid'" );
	

	if ($actMsg){
		$ret[] = $actMsg;
	}
	
	return $ret;
}

function exchangeLiquan($uid, $param) {
	$code = trim ( array_shift ( $param )  );
	if (empty ( $code ))
		throw new Exception ( $GLOBALS ['exchangeLiquan'] ['code_notNull'] );
	
	if (! preg_match ( "/[a-zA-Z0-9]{10}/", $code ))
		throw new Exception ( $GLOBALS ['exchangeLiquan'] ['invalid_code'] );
	
	$item = sql_fetch_one ( "select * from sys_ticket where code='$code' limit 1" );
	if (empty ( $item )) {
		throw new Exception ( $GLOBALS ['exchangeLiquan'] ['invalid_code'] );
	} else if ($item ['uid'] > 0) {
		throw new Exception ( $GLOBALS ['exchangeLiquan'] ['used_code'] );
	} else if (($item ['binduid'] > 0) && ($item ['binduid'] != $uid)) {
		throw new Exception ( $GLOBALS ['exchangeLiquan'] ['code_bind'] );
	}
	
	//pcy 新的编码规则，处理不同类型的礼券
	$limit=0;
	$pattern=substr($code,0,3);
	//LQA开头的，表示这种类型的礼券，一个账号限使用一个限使用
	if($pattern=='LQA'){
		$limit=1;
	}else{
		for ($i=2;$i<10;$i++){
			if($pattern=='LQ'.$i){
				$limit=$i;
				break;
			}
		}
	}
	if($limit>0){	//有使用数量限制
		$used=sql_fetch_one_cell("select count(1) from sys_ticket where uid='$uid' and contentid='$item[contentid]' and code like '".$pattern."%'");
		if(!empty($used) && $used>=$limit){
			throw new Exception ( sprintf($GLOBALS['exchangeLiquan']['reach_limit'],$limit,$used) );
		}
	}
	
	
	$content = sql_fetch_one ( "select * from sys_ticket_content where id='$item[contentid]'" );
	sql_query ( "update sys_ticket set uid='$uid',time=unix_timestamp() where id='$item[id]'" );
	if (! lockUser ( $uid ))
		throw new Exception ( $GLOBALS ['pacifyPeople'] ['server_busy'] );
	$ret=parseAndAddReward($uid,$content['content'],8,3,8,3);
	unlockUser ( $uid );
	$ret2 = array ();
	$ret2 [] = $ret;
	return $ret2;
}

//function getMedalRecord($uid, $param) {
//	//30000~30003 tid: 汉室勋章	平定黄巾勋章	袁军官渡勋章	曹军官渡勋章
//	return sql_fetch_rows ( "select * from sys_things as A, cfg_things as B where A.uid='$uid' and A.tid = B.tid and A.tid>=30000 and A.tid<=30010" );
//}

function buyGoodsBeforeUse($uid, $param) {
	$id = intval(array_shift ( $param ));
	$cnt = intval ( array_shift ( $param ) );
	$paytype = intval ( array_shift ( $param ) );
	if ($paytype != 0 && $paytype != 1) {
		throw new Exception ( $GLOBALS ['buyGoods'] ['invalid_pay_type'] );
	}
	
	if ($cnt < 1)
		throw new Exception ( $GLOBALS ['buyGoods'] ['invalid_amount'] );
	if($paytype==0)
	{
		$goods = sql_fetch_one ( "select * from cfg_shop where id='$id' and onsale=1 and starttime<=unix_timestamp() and endtime>unix_timestamp() and (commend='0' or commend=2)" );
	}else 
	{
		$goods = sql_fetch_one ( "select * from cfg_shop where id='$id' and onsale=1 and starttime<=unix_timestamp() and endtime>unix_timestamp() and (commend='1' or commend=2)" );
	}
	
	if (empty ( $goods ))
		throw new Exception ( $GLOBALS ['buyGoods'] ['stop_sale'] );
		
//	if(($goods['rebate']==1)&&($paytype==1)){
//		throw new Exception ( $GLOBALS ['buyGoods'] ['invalid_rebate'] );
//	}
	$moneyNeed = $cnt * $goods ['price'];
	if (! lockUser ( $uid ))
		throw new Exception ( $GLOBALS ['pacifyPeople'] ['server_busy'] );
	$userInfo = sql_fetch_one ( "select money,gift from sys_user where uid='$uid'" );
	//元宝
	$userMoney = $userInfo ['money'];
	//礼金
	$userGift = $userInfo ['gift'];
	if ($paytype == 0 && ($userMoney < $moneyNeed))
		throw new Exception ( $GLOBALS ['buyGoods'] ['no_enough_YuanBao'] );
	if ($paytype == 1 && ($userGift < $moneyNeed))
		throw new Exception ( $GLOBALS ['buyGoods'] ['no_enough_Gift'] );
	
//	if (($id == 121) && ($userInfo ['nobility'] < 1))
//		throw new Exception ( $GLOBALS ['buyGoods'] ['nobility_limit'] );
	if ($goods ['totalCount'] < 2000000000) {
		if ($goods ['totalCount'] == 0)
			throw new Exception ( $GLOBALS ['buyGoods'] ['sold_out'] );
	}
	if (($goods ['userbuycnt'] > 0) || ($goods ['daybuycnt'] > 0)) //属于限制商品
{
		$buycnt = intval ( sql_fetch_one_cell ( "select `count` from log_shop_buy_cnt where uid='$uid' and `sid`='$id'" ) );
		$todaybuycnt = intval ( sql_fetch_one_cell ( "select sum(count) from log_shop where uid ='$uid' and shopid = '$id' and date(now())=date(from_unixtime(time))" ) );
		if (($goods ['userbuycnt'] > 0) && ($buycnt + $cnt > $goods ['userbuycnt'])) {
			if ($goods ['userbuycnt'] > $buycnt) {
				$remain = $goods ['userbuycnt'] - $buycnt;
				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_remain_amountLimit'], $goods ['userbuycnt'], $buycnt, $remain );
				throw new Exception ( $msg );
			} else {
				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_buy_limit'], $goods ['userbuycnt'] );
				throw new Exception ( $msg );
			}
		}
		if (($goods ['daybuycnt'] > 0) && ($todaybuycnt + $cnt > $goods ['daybuycnt'])) {
			if ($goods ['daybuycnt'] > $todaybuycnt) {
				$remain = $goods ['daybuycnt'] - $todaybuycnt;
				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_remain_amount_todayLimit'], $goods ['daybuycnt'], $buycnt, $remain );
				throw new Exception ( $msg );
			} else {
				$msg = sprintf ( $GLOBALS ['buyGoods'] ['reach_buy_todayLimit'], $goods ['daybuycnt'] );
				throw new Exception ( $msg );
			}
		}
		sql_query ( "insert into log_shop_buy_cnt (`uid`,`sid`,`count`) values ('$uid','$id','$cnt') on duplicate key update `count`=`count`+'$cnt'" );
	}
	//一手交钱
	if ($paytype == 0)
		addMoney ( $uid, - $moneyNeed, 10 );
	else if ($paytype == 1)
		addGift ( $uid, - $moneyNeed, 10 );
	//一手交货 
	$logGoodType=222;
	if($paytype==0){
		$logGoodType=63;
	}else{
		$logGoodType=64;
	}
	addGoods ( $uid, $goods ['gid'], $goods ['pack'] * $cnt, $logGoodType );
		sql_query ( "update sys_user set last_pay='$paytype' where uid='$uid'" );
	sql_query ( "insert into log_shop (uid,shopid,count,price,time,paytype) values ('$uid','$id','$cnt','$goods[price]',unix_timestamp(),$paytype)" );
	
	completeTask ( $uid, 366 );
	completeTask ( $uid, 531 );
	unlockUser ( $uid );
	$ret = array ();
	$ret [] = $goods ['gid'];
	$ret [] = $paytype;
	if ($paytype == 0){
		$ret [] = $userMoney - $moneyNeed;
		$actMsg = checkAndDoShopAct($moneyNeed, $uid);//查看和执行商城活动
	}else if ($paytype == 1) {
		$ret [] = $userGift - $moneyNeed;
	}
	sql_query ( "update sys_user set last_pay='$paytype' where uid='$uid'" );
	return $ret;
}

?>