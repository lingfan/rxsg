<?php
require_once ("./interface.php");
require_once ("./utils.php");
require_once ("./GoodsFunc.php");
require_once ("./HeroSkillFunc.php");

function getHeroBuffer($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$chibiherogood=sql_fetch_rows("select h.hid,c.gid,c.name,h.endtime from mem_hero_buffer h left join cfg_goods c on c.gid=h.buftype where  h.hid=$hid and h.endtime>unix_timestamp()  and  ( (h.buftype>=160040 and h.buftype<=160051) or  (h.buftype>=160023 and h.buftype<=160031))");
	$woldherogood=sql_fetch_rows ( "select h.hid,c.gid,c.name,h.endtime from mem_hero_buffer h left join cfg_goods c on c.gid=h.buftype+25 where h.buftype<>5 and (h.buftype<160023 or (h.buftype>=19057 and h.buftype<=19058)) and h.hid='$hid' and h.endtime>unix_timestamp()" );
	return array_merge($chibiherogood,$woldherogood);
	//return sql_fetch_rows ( "select h.hid,c.gid,c.name,h.endtime from mem_hero_buffer h left join cfg_goods c on c.gid=h.buftype+25 where h.buftype<>5 and h.hid='$hid' and h.endtime>unix_timestamp()" );
}

function deleteHero($uid, $hid) {
	$hero = sql_fetch_one ( "select * from sys_city_hero where uid='$uid' and hid='$hid' limit 1" );
	if (empty ( $hero )) {
		throw new Exception ( $GLOBALS ['dismissHero'] ['cant_dissmiss_this'] );
	}
	if ($hero ['state'] != 0) {
		throw new Exception ( $GLOBALS ['dismissHero'] ['only_dissmiss_free_hero'] );
	}
	if(intval($hero['herotype']) == 1000)    //君主将不能被删除
	{
		throw new Exception($GLOBALS ['dismissHero']['king_hero_can_not_delete']);
	}
	deleteHeroBaseAdd ( $hid );
	throwHeroToField ( $hero );
	$cid = $hero ["cid"];
	updateCityHeroChange ( $uid, $cid );
}

function dismissHero($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$hero = sql_fetch_one ( "select * from sys_city_hero where cid='$cid' and uid='$uid' and hid='$hid'" );
	if (empty ( $hero )) {
		throw new Exception ( $GLOBALS ['dismissHero'] ['cant_dissmiss_this'] );
	}
	if ($hero ['state'] != 0) {
		throw new Exception ( $GLOBALS ['dismissHero'] ['only_dissmiss_free_hero'] );
	}
	if ($hero ['herotype'] == 1000) {
		throw new Exception ($GLOBALS['user_hero']['cannot_dismiss']);
	}
	
	//解决将领装备丢失的问题
	if (sql_check ( "select * from sys_user_armor where uid=$uid and hid=$hid" )) {
		throw new Exception ( $GLOBALS ['dismissHero'] ['has_armor'] );
	}
	deleteHeroBaseAdd ( $hid );
	
	if(intval($hero['herotype'])==10001){   //解雇孩子
		$childHid = sql_fetch_one_cell("select hid from sys_user_child where out_hid='$hid'");	
		sql_query("delete from sys_user_child where uid='$uid' and hid='$childHid'");
		sql_query("update mem_marry_relation set state='2' where shid='$childHid' and uid='$uid'");
		sql_query("insert into log_child_status(`hid`,`uid`,`out_hid`,`state`,`time`) values('$childHid','$uid','$hid','2',unix_timestamp()) on duplicate key update state='2',time=unix_timestamp()");
	}
	throwHeroToField ( $hero );
	
	if($hero['npcid']!=0)
	{
		//将名将从sys_lioniza里的数据，
		sql_query("insert into log_lionize(uid,npcid,`type`,`count`,`time`)  select  uid,npcid,11,0,unix_timestamp() from sys_lionize where uid=$uid and npcid=$hid");
		sql_query ( "delete from sys_lionize where uid=$uid and npcid=$hid" );
		//如果是名将 清除专属任务 

		$taskminid = 10*($hero['npcid']+40000);

		sql_query("delete from sys_user_task where tid>$taskminid and tid<($taskminid+10)");//清 原master的专属任务
		sql_query("delete from sys_user_goal where gid>$taskminid and gid<($taskminid+10)");//清 user goal
		sql_query("delete from sys_attack_position where tid>$taskminid and tid<($taskminid+10)");//清 市井传闻 的目标
	}
		
	updateCityHeroChange ( $uid, $cid );
	return getCityInfoHero ( $uid, $cid );
}


function deleteHeroBaseAdd($hid) {
	$row = sql_fetch_one ( "select sum(command_base_add_on) as command_base_add_on, sum(affairs_base_add_on) as affairs_base_add_on ,sum(bravery_base_add_on) as bravery_base_add_on,sum(wisdom_base_add_on) as  wisdom_base_add_on from  sys_city_hero_base_add where hid = $hid" );
	if (empty ( $row ))
	return;
	$bravery_base_add_on = 0 - intval ( $row ["bravery_base_add_on"] );
	$wisdom_base_add_on = 0 - intval ( $row ["wisdom_base_add_on"] );
	$affairs_base_add_on = 0 - intval ( $row ["affairs_base_add_on"] );
	$command_base_add_on = 0 - intval ( $row ["command_base_add_on"] );
	sql_query ( "update sys_city_hero set bravery_base=bravery_base+$bravery_base_add_on,wisdom_base=wisdom_base+$wisdom_base_add_on,affairs_base=affairs_base+$affairs_base_add_on,command_base=command_base+$command_base_add_on where hid = $hid" );
	sql_query ( "delete from sys_city_hero_base_add where hid = " . $hid );
	sql_query("update mem_hero_buffer set endtime=unix_timestamp() where hid = " . $hid);//清武魂等时效道具效果
	//标记，防止上限检测出错
	$log_type = 4;
	sql_query("insert into log_act (uid, actid, sort, type, count, log_type, time) select 0, $hid, 2, gid, 1, $log_type, unix_timestamp() from cfg_goods where gid between 10150 and 10155");
	
}

//可以设置类型 cxy_09.9.1
function deleteHeroBaseAddPlus($hid,$type) {
	$row = sql_fetch_one ( "select sum(command_base_add_on) as command_base_add_on, sum(affairs_base_add_on) as affairs_base_add_on ,sum(bravery_base_add_on) as bravery_base_add_on,sum(wisdom_base_add_on) as  wisdom_base_add_on from  sys_city_hero_base_add where `type`=$type and hid = $hid" );
	if (empty ( $row ))
	return;
	$bravery_base_add_on = 0 - intval ( $row ["bravery_base_add_on"] );
	$wisdom_base_add_on = 0 - intval ( $row ["wisdom_base_add_on"] );
	$affairs_base_add_on = 0 - intval ( $row ["affairs_base_add_on"] );
	$command_base_add_on = 0 - intval ( $row ["command_base_add_on"] );
	sql_query ( "update sys_city_hero set bravery_base=bravery_base+$bravery_base_add_on,wisdom_base=wisdom_base+$wisdom_base_add_on,affairs_base=affairs_base+$affairs_base_add_on,command_base=command_base+$command_base_add_on where hid = $hid" );
	sql_query ( "delete from sys_city_hero_base_add where `type`=$type and hid = " . $hid );
	
	//名将属性自动修复 --CY
	$npcHero = sql_fetch_one("select affairs_base, bravery_base,wisdom_base from cfg_npc_hero where npcid=$hid");
	if (!empty($npcHero)) {
		$row = sql_fetch_one ( "select sum(affairs_base_add_on) as affairs_base_add_on ,sum(bravery_base_add_on) as bravery_base_add_on,sum(wisdom_base_add_on) as  wisdom_base_add_on from  sys_city_hero_base_add where hid = $hid" );
		$npc_affairs_base=$npcHero['affairs_base']+intval($row['affairs_base_add_on']);
		$npc_bravery_base=$npcHero['bravery_base']+intval($row['bravery_base_add_on']);
		$npc_wisdom_base=$npcHero['wisdom_base']+intval($row['wisdom_base_add_on']);
		sql_query ( "update sys_city_hero set bravery_base=$npc_bravery_base,wisdom_base=$npc_wisdom_base,affairs_base=$npc_affairs_base where hid = $hid" );
	}
}

function upgradeHero($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$hero = sql_fetch_one ( "select * from sys_city_hero where cid='$cid' and uid='$uid' and hid='$hid'" );

	if (empty ( $hero )) {
		throw new Exception ( $GLOBALS ['upgradeHero'] ['cant_upgrade_this'] );
	}
	//if ($hero["state"] > 1 ) //不在城池里
	if (isHeroInCity ( $hero ["state"] ) == 0) {
		throw new Exception ( $GLOBALS ['upgradeHero'] ['cant_upgrade_out_hero'] );
	}

	//马来订制 卡片签到系统 名将可升到120
	$yysType = sql_fetch_one_cell("select value from mem_state where state=197");
	$maxHeroLevel=getMaxHeroLevel($hero['npcid']);
	if (sql_check("select 1 from sys_city_hero where hid=$hid and herotype=1000")) {
		$maxHeroLevel=100;
		if (checkHeroLevel($uid,10,100)) {
			$maxHeroLevel=125;
		}
	}
	if($yysType==60 || $yysType==55555555){
		if($hero['npcid']>0){
			if($hero['level']>=$maxHeroLevel){
				sql_query("update sys_city_hero set level=$maxHeroLevel where hid='$hid'");
				throw new Exception($GLOBALS['upgradeHero']['level_100']);
			}
		}else if($hero['level']>=$maxHeroLevel){
			//强制避免将领等级过大的bug
			if($hero['level']>$maxHeroLevel){
				sql_query("update sys_city_hero set level='$maxHeroLevel' where hid='$hid'");
			}
			throw new Exception($GLOBALS['upgradeHero']['level_100']);
		}
	}else{
		if ($hero ['level'] >= $maxHeroLevel) {
			//强制避免将领等级过大的bug
			if ($hero ['level'] > $maxHeroLevel) {
				sql_query ( "update sys_city_hero set level='$maxHeroLevel' where hid='$hid'" );
			}
			throw new Exception ( $GLOBALS ['upgradeHero'] ['level_100'] );
		}
	}
	$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid'" );
	$total_exp = sql_fetch_one_cell ( "select total_exp from cfg_hero_level where level='$hero[level]'" );
	$upgrade_exp = sql_fetch_one_cell ( "select upgrade_exp from cfg_hero_level where level=" . ($hero ['level'] + 1) );
	if (($hero ['exp'] - $total_exp) >= $upgrade_exp) {
	    if( $hero['level']==119){
		      if(isMyInBigHero($hid)){ 
                  $attValue=0.05;
	              $bravery_base_add_on = floor($hero['bravery_base']*$attValue);			
		          $wisdom_base_add_on = floor( $hero['wisdom_base']*$attValue);		
		          $affairs_base_add_on = floor($hero['affairs_base']*$attValue);
		          $command_base_add_on = floor( $hero['command_base']*$attValue);
		          $attack_add_on = 125;		
		          $defence_add_on = 125;			  
				  sql_query ( "update sys_city_hero set level=level+6 where hid='$hid'" );
                  insertHeroBaseAdds($hid,$uid,1,$bravery_base_add_on,$wisdom_base_add_on, $affairs_base_add_on,$command_base_add_on,$attack_add_on,$defence_add_on);				  
				}
		       else
                 sql_query ( "update sys_city_hero set level=level+1 where hid='$hid'" );			   
			}
		  else if( $hero['level']==124){
		      $attValue=0.05;
	          $bravery_base_add_on = floor($hero['bravery_base']*$attValue);			
		      $wisdom_base_add_on = floor( $hero['wisdom_base']*$attValue);		
		      $affairs_base_add_on = floor($hero['affairs_base']*$attValue);
		      $command_base_add_on = floor( $hero['command_base']*$attValue);
		      $attack_add_on = 125;		
		      $defence_add_on = 125;	  
		      sql_query ( "update sys_city_hero set level=125 where hid='$hid'" );
			  insertHeroBaseAdds($hid,$uid,1,$bravery_base_add_on,$wisdom_base_add_on, $affairs_base_add_on,$command_base_add_on,$attack_add_on,$defence_add_on);
		    }
		    else
		      sql_query ( "update sys_city_hero set level=level+1 where hid='$hid'" );
		if (sql_check("select 1 from sys_city_hero where herotype=1000 and hid=$hid")) {
			if(sql_check("select 1 from mem_assemble where uid='$uid'")){
				sql_query("update mem_assemble set level={$hero['level']}+1 where uid='$uid'");
			}
		}
	} else {
		throw new Exception ( $GLOBALS ['upgradeHero'] ['no_enough_exp'] );
	}
	regenerateHeroAttri ( $uid, $hid );
	completeTask ( $uid, 86 );
	updateCityHeroChange ( $uid, $cid );
	return getCityInfoHero ( $uid, $cid );
}
function insertHeroBaseAdds($hid,$uid,$type,$bravery_base_add_on,$wisdom_base_add_on, $affairs_base_add_on,$command_base_add_on,$attack_add_on,$defence_add_on){
	$sql = "insert into sys_city_hero_base_add (uid,hid,bravery_base_add_on,wisdom_base_add_on,affairs_base_add_on,command_base_add_on,type) values ($uid,$hid,$bravery_base_add_on,$wisdom_base_add_on,$affairs_base_add_on,$command_base_add_on,$type)";
	sql_query($sql);
	$sql = "update sys_city_hero set bravery_base=greatest(0,bravery_base+$bravery_base_add_on),wisdom_base=greatest(0,wisdom_base+$wisdom_base_add_on),affairs_base=greatest(0,affairs_base+$affairs_base_add_on),command_base=greatest(0,command_base+$command_base_add_on),attack_add_on=greatest(0,attack_add_on+$attack_add_on),defence_add_on=greatest(0,defence_add_on+$defence_add_on) where hid = $hid";
	sql_query($sql);
}
function isMyInBigHero($hid){
		$bighids=array('156','200','222','261','441','455','497','549','563','577','791','832','856','863','1006','1011','1015');
		foreach ($bighids as $bighid){
			if($hid==$bighid){
				return true;
			}
		}
		return false;
	}
function addHeroPoint($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$affairs = intval(array_shift ( $param ));
	$bravery = intval(array_shift ( $param ));
	$wisdom = intval(array_shift ( $param ));
	$levelAdd = 0;
	$hero = sql_fetch_one ( "select * from sys_city_hero where cid='$cid' and uid='$uid' and hid='$hid'" );
	if (empty ( $hero )) {
		throw new Exception ( $GLOBALS ['addHeroPoint'] ['cant_find_hero'] );
	}

	//if ($hero["state"] > 1 ) //不在城池里
	if (isHeroInCity ( $hero ["state"] ) == 0) {
		throw new Exception ( $GLOBALS ['addHeroPoint'] ['cant_add_out_hero'] );
	}
	if ($hero['herotype'] == 1000) {//君主将三倍于普通将领级别属性加成
		$levelAdd = 3*$hero["level"];
	} else {
		$levelAdd = $hero ["level"];
	}
	$heroType = sql_fetch_one_cell("select herotype from sys_city_hero where hid=$hid");
	if (checkHeroLevel($uid,8,70) && $heroType==1000) {
		$levelAdd += 50;
	}
	if ($affairs + $bravery + $wisdom <= $hero ['affairs_base'] + $hero ['bravery_base'] + $hero ['wisdom_base'] + $levelAdd) {
		$affairs_add = $affairs - $hero ['affairs_base'];
		$bravery_add = $bravery - $hero ['bravery_base'];
		$wisdom_add = $wisdom - $hero ['wisdom_base'];

		if ($hero ['affairs_add'] > $affairs_add || $hero ['bravery_add'] > $bravery_add || $hero ['wisdom_add'] > $wisdom_add) {
			$msg = "$uid:" . "affairs_add=$affairs_add, bravery_add=$bravery_add, wisdom_add=$wisdom_add";
			system ( "echo \"$msg\" >> /waigua.uid" );
			throw new Exception ( $GLOBALS ['hero'] ['xidian_unvalid'] );
		}

		sql_query ( "update sys_city_hero set affairs_add='$affairs_add',bravery_add='$bravery_add',wisdom_add='$wisdom_add' where hid='$hid'" );
		regenerateHeroAttri ( $uid, $hid );
		updateCityHeroChange ( $uid, $cid );
		$chiefhid = sql_fetch_one_cell ( "select chiefhid from sys_city where cid='$cid'" );
		if ($chiefhid == $hid) {
			updateCityChiefResAdd ( $cid, $hid );
		}
		completeTask ( $uid, 87 );
	} else {
		throw new Exception ( $GLOBALS ['addHeroPoint'] ['no_extra_potential'] );
	}
	return getCityInfoHero ( $uid, $cid );
}
function clearHeroPoint($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$hero = sql_fetch_one ( "select * from sys_city_hero where cid='$cid' and uid='$uid' and hid='$hid'" );
	if (empty ( $hero )) {
		throw new Exception ( $GLOBALS ['clearHeroPoint'] ['cant_find_hero'] );
	}
	if (isHeroInCity ( $hero ["state"] ) == 0) //if ($hero["state"] > 1) //不在城池里
	{
		throw new Exception ( $GLOBALS ['clearHeroPoint'] ['cant_clean_out_hero'] );
	}
	useXiShuiDan ( $uid, $hid );
	regenerateHeroAttri ( $uid, $hid );
	updateCityHeroChange ( $uid, $cid );
	return getCityInfoHero ( $uid, $cid );
}
function changeHeroName($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$newName = trim ( array_shift ( $param ) );
	if (mb_strlen ( $newName, "utf-8" ) > MAX_HERO_NAME) {
		throw new Exception ( $GLOBALS ['changeHeroName'] ['name_too_long'] );
	} else if ((! (strpos ( $newName, '\'' ) === false)) || (! (strpos ( $newName, '\\' ) === false))) {
		throw new Exception ( $GLOBALS ['changeHeroName'] ['invalid_char'] );
	} else if (strlen ( $newName ) == 0) {
		throw new Exception ( $GLOBALS ['changeHeroName'] ['input_valid_name'] );
	}
	$lowername = strtolower ( $newName );
	if (sql_check ( "select * from cfg_baned_name where instr('$lowername',`name`)>0" )) {
		throw new Exception ( $GLOBALS ['changeHeroName'] ['invalid_char'] );
	}
	//$newName = addslashes ( $newName );
	$hero = sql_fetch_one ( "select * from sys_city_hero where cid='$cid' and uid='$uid' and hid='$hid'" );
	if (empty ( $hero )) {
		throw new Exception ( $GLOBALS ['changeHeroName'] ['cant_find_hero'] );
	}
	if($hero['herotype']==10001){   //小孩的名字不能在普通将领面板进行修改
		throw new Exception($GLOBALS['marrySystem']['child_name_not_change_here']);  
	} 
	if (isHeroInCity ( $hero ["state"] ) == 0) //if ($hero["state"] > 1) //不在城池里
	{
		throw new Exception ( $GLOBALS ['changeHeroName'] ['cant_change_out_hero'] );
	}
	if ($hero ['npcid'] != 0) {
		throw new Exception ( $GLOBALS ['changeHeroName'] ['cant_change_famous_hero'] );
	}
//	if ($hero ['herotype'] != 0) {                              // 活动将领又可以改名啦
//		throw new Exception ( $GLOBALS ['changeHeroName'] ['cant_change_act_hero'] );
//	}
    if ($hero ['herotype'] == 27250) {
		throw new Exception ( $GLOBALS ['changeHeroName'] ['cant_change_famous_hero'] );
	}
	if ($hero ['herotype'] == 142 || ($hero ['herotype'] >143 && $hero ['herotype']<5000)) {                 
		throw new Exception ($GLOBALS['hero']['canot_change_name']);
	}
	
	sql_query ( "update sys_city_hero set name='$newName' where hid='$hid'" );

	return getCityInfoHero ( $uid, $cid );
}
function largessHero($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$typeidx = array_shift ( $param );
	$hero = sql_fetch_one ( "select * from sys_city_hero where cid='$cid' and uid='$uid' and hid='$hid'" );
	if (empty ( $hero )) {
		throw new Exception ( $GLOBALS ['largessHero'] ['cant_find_hero'] );
	}
	if(intval($hero['herotype'])==1000)  //君主将不能进行赏赐
	{
		throw new Exception ( $GLOBALS['useGoods']['king_cannot_use'] );
	}
	//if ($hero["state"] > 1) //不在城池里
	if (isHeroInCity ( $hero ["state"] ) == 0) {
		throw new Exception ( $GLOBALS ['largessHero'] ['cant_largess_out_hero'] );
	}
	$now = sql_fetch_one_cell ( "select unix_timestamp()" );
	$last_largess = sql_fetch_one_cell ( "select last_largess from mem_hero_schedule where hid='$hid'" );
	if ($now - $last_largess < 900) //15分钟内不能再次赏赐
	{
		$delta = 900 - ($now - $last_largess);

		$msg = sprintf ( $GLOBALS ['largessHero'] ['wait_duration'], MakeTimeLeft ( $delta ) );
		throw new Exception ( $msg );
	}
	//洛神玉佩加成忠诚度上限
	$yupeiAdd=0;
	$yupeiCount = sql_fetch_one_cell("select count(*) from sys_user_armor a, sys_hero_armor b where a.sid=b.sid and b.hid=$hid and b.armorid=12010 and a.active_special=1");
	if (!empty($yupeiCount)) {
		if ($yupeiCount == 1)
			$yupeiAdd = 20;
		elseif ($yupeiCount == 2)
			$yupeiAdd = 50;
	}
	$loyaltyadd=0;
	if ($typeidx == 0) //赏赐黄金
	{
		$city_gold = sql_fetch_one_cell ( "select gold from mem_city_resource where cid='$cid'" );
		$salary = $hero ['level'] * 20 + (max ( $hero ['affairs_base'] + $hero ['affairs_add'] - 90, 0 ) + max ( $hero ['bravery_base'] + $hero ['bravery_add'] - 90, 0 ) + max ( $hero ['wisdom_base'] + $hero ['wisdom_add'] - 90, 0 )) * 50;
		$largess_gold = $salary * 5;
		if ($city_gold < $largess_gold) {
			throw new Exception ( $GLOBALS ['largessHero'] ['no_enough_gold'] );
		}
		if ($salary < 3500)
		$loyaltyadd = mt_rand ( 1, 20 );
		else if ($salary < 6500)
		$loyaltyadd = mt_rand ( 1, 10 );
		else if ($hero ['loyalty'] < 30)
		$loyaltyadd = mt_rand ( 1, 5 );
		else
		throw new Exception ( $GLOBALS ['largessHero'] ['no_need_gold'] );
		sql_query ( "update mem_city_resource set gold=gold-'$largess_gold' where cid='$cid'" );
		//sql_query ( "update sys_city_hero set loyalty=LEAST(100+$yupeiAdd,loyalty+'$loyaltyadd') where hid='$hid'" );
	} else if ($typeidx < 10) //珍珠等
	{
		$gid = $typeidx + 29;
		$my_goods_count = sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'");
		if (empty($my_goods_count)||($my_goods_count <= 0)) throw new Exception($GLOBALS['largessHero']['no_this_prop']);

		addGoods($uid,$gid,-1,7);
		$GOODS_LOYALTY_ADD = array(5,10,15,20,25,30,40,50,60);
		$loyaltyadd = $GOODS_LOYALTY_ADD[$typeidx-1];
		//sql_query("update sys_city_hero set loyalty=LEAST(100+$yupeiAdd,loyalty+$loyaltyadd) where hid='$hid'");
		//完成成长任务
		if($gid == 30) {
			completeTaskWithTaskid($uid, 319); //武曲
		}
	}
	if(intval($hero['herotype'])==10001)  //孩子出来的将领忠诚度可以为300
	{
		sql_query ( "update sys_city_hero set loyalty=LEAST(300+$yupeiAdd,loyalty+'$loyaltyadd') where hid='$hid'" );
	}else 
	{
		sql_query ( "update sys_city_hero set loyalty=LEAST(100+$yupeiAdd,loyalty+'$loyaltyadd') where hid='$hid'" );
	}
	sql_query ( "insert into mem_hero_schedule (hid,last_trick,last_largess) values ('$hid',0,'$now') on duplicate key update last_largess=$now" );
	return getCityInfoHero ( $uid, $cid );
}
function loadHeroGoods($uid,$param){
	$sql="select gid,name,description,value  from cfg_goods where gid=116 or  (gid>=26 and gid<=29) or (gid>=10152 and gid<=10155) or (gid>=160040 and gid<=160051)   or  (gid>=160023 and gid<=160031) or gid=19082 or gid=19083 or (gid>=19202 and gid<=19205)";
//	$sql="select gid,name,description,value  from cfg_goods where (gid>=160040 and gid<=160051)   or  (gid>25 and gid<30) or gid=30 or (gid>=160023 and gid<=160031)";
	//$sql="select gid,name,description,value  from cfg_goods where description  like '%赏赐给将领%'";
	return sql_fetch_rows($sql);
}
//function praiseHero($uid, $param)
//{
//	//为了坑能用，加个没用的参数
//	$funcitonname = array_shift($param);
//	
//	$cid = array_shift ( $param );
//	$hid = array_shift ( $param );
//	$typeidx = array_shift ( $param );
//	$hero = sql_fetch_one ( "select * from sys_city_hero where cid='$cid' and uid='$uid' and hid='$hid'" );
//	if (empty ( $hero )) {
//		throw new Exception ( $GLOBALS ['largessHero'] ['cant_find_hero'] );
//	}
//	//if ($hero["state"] > 1) //不在城池里
//	if (isHeroInCity ( $hero ["state"] ) == 0) {
//		throw new Exception ( $GLOBALS['pairse']['cant_largess_out_hero'] );
//	}
//	if ($typeidx < 5) //虎符,文曲星符，武曲星符，智多星符,猛油火罐
//	{
//		$gid = $typeidx + 26;
//		if ($typeidx == 4)
//		$gid = 116;
//		$hufu = sql_fetch_one_cell ( "select count from sys_goods where uid='$uid' and gid='$gid'" );
//		if (empty ( $hufu ) || ($hufu <= 0))
//		throw new Exception ("not_enough_goods$gid");
//		addGoods ( $uid, $gid, - 1, 7 );
//		$buftype = $typeidx + 1;
//		if ($typeidx == 4)
//		$buftype = 91;
//		sql_query ( "insert into mem_hero_buffer (hid,buftype,endtime) values ('$hid','$buftype',unix_timestamp()+86400) on duplicate key update endtime=GREATEST(endtime,unix_timestamp())+86400" );
//		if ($typeidx < 2) //如果是虎符或文曲星，而且将领是城守的话，要重新算资源加成
//		{
//			$chiefhid = sql_fetch_one_cell ( "select chiefhid from sys_city where cid='$cid'" );
//			if (! empty ( $chiefhid ) && $chiefhid == $hid) {
//				if (!empty($cid))
//				sql_query ( "update sys_city_res_add set resource_changing=1 where cid='$cid'" );
//			}
//		}
//		regenerateHeroAttri ( $uid, $hid );
//		//完成成长任务
//		if($gid == 28) {
//			completeTaskWithTaskid($uid, 300); //武曲
//		} else if($gid == 27) {
//			completeTaskWithTaskid($uid, 301); //文曲
//		} else if($gid == 29) {
//			completeTaskWithTaskid($uid, 302); //智多星
//		}
//	}else {//
//		$gid=$typeidx-8 + 10155;//这里有两个东西未启用
//		$msg = enhanceHero($uid,$hid,$gid);
//		if(strpos($msg,sprintf($GLOBALS['act']['msg_tip'],''))===false){//返回值含有msg_tip说明出错了
//			$msg = false;
//		}
//	}
//	//sql_query("insert into mem_hero_schedule (hid,last_trick,last_largess) values ('$hid',0,unix_timestamp()) on duplicate key update last_largess=unix_timestamp()");
//	$ret= getCityInfoHero ( $uid, $cid );
//	if($msg){
//		$ret[] = $msg;
//	}
//	return $ret;	
//}
function  newPraiseHero($uid, $param){
	$funcitonname = array_shift($param);
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$gid = intval(array_shift ( $param ));
	$buftype=$gid;
	$hero = sql_fetch_one ( "select * from sys_city_hero where cid='$cid' and uid='$uid' and hid='$hid'" );
	if (empty ( $hero )) {
		throw new Exception ( $GLOBALS ['largessHero'] ['cant_find_hero'] );
	}else if (isHeroInCity ( $hero ["state"] ) == 0) {
		throw new Exception ( $GLOBALS['pairse']['cant_largess_out_hero'] );
	}
	if(intval($hero['herotype'])==1000)  //君主将不能进行嘉赏
	{
		throw new Exception ( $GLOBALS['useGoods']['king_cannot_use'] );
	}
	checkGoodsRepeatUse($uid,$gid,$hid);
	$goodCount = sql_fetch_one_cell ( "select count from sys_goods where uid='$uid' and gid='$gid'" );
	if (empty ( $goodCount ) || ($goodCount <= 0)){		
		throw new Exception ("not_enough_goods$gid");
	}	
	
	addGoods ( $uid, $gid, - 1, 7 );
	sql_query ( "insert into mem_hero_buffer (hid,buftype,endtime) values ('$hid','$buftype',unix_timestamp()+3600) on duplicate key update endtime=GREATEST(endtime,unix_timestamp())+3600" );
	regenerateHeroAttri ( $uid, $hid );
	
	$ret= getCityInfoHero ( $uid, $cid );
	return $ret;	
}

function checkGoodsRepeatUse($uid,$gid,$hid){
	if($gid == 160026 || $gid == 160027 || $gid == 160028)
	{
		if(sql_check("select 1 from mem_user_buffer where uid = $uid and buftype = 7"))
		{
			throw new Exception ($GLOBALS['HeroFunc']['baguazheng1']);
		}
	    if($gid == 160028){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160027"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['baguazheng2']);
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160026"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['baguazheng3']);
			}
		}
		if($gid == 160027){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160028"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160028");
				
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160026"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['baguazheng4']);
			}
		}
		if($gid == 160026){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160027"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160027");
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160028"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160028");
			}
		}
	}
	if($gid == 160029 || $gid == 160030 || $gid == 160031)
	{
		if(sql_check("select 1 from mem_user_buffer where uid = $uid and buftype = 6"))
		{
			throw new Exception ($GLOBALS['HeroFunc']['xianzhenzhangu1']);
		}
		if($gid == 160031){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160030"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['xianzhenzhangu2']);
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160029"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['xianzhenzhangu3']);
			}
		}
		if($gid == 160030){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160031"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160031");
				
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160029"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['xianzhenzhangu4']);
			}
		}
		if($gid == 160029){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160030"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160030");
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160031"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160031");
			}
		}
	}
	if($gid == 160040 || $gid == 160041 || $gid == 160042)
	{
		if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 1"))
		{
			throw new Exception ($GLOBALS['HeroFunc']['hufu1']);
		}
		if($gid == 160040){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160041"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['hufu2']);
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160042"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['hufu3']);
			}
		}
		if($gid == 160041){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160040"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160040");
				
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160042"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['hufu4']);
			}
		}
		if($gid == 160042){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160040"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160040");
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160041"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160041");
			}
		}
	}
	if($gid == 160043 || $gid == 160044 || $gid == 160045)
	{
	 	if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 2"))
		{
			throw new Exception ($GLOBALS['HeroFunc']['wenquxin1']);
		}
		if($gid == 160045){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160044"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['wenquxin2']);
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160043"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['wenquxin3']);
			}
		}
		if($gid == 160044){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160045"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160045");
				
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160043"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['wenquxin4']);
			}
		}
		if($gid == 160043){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160044"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160044");
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160045"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160045");
			}
		}
	}
	if($gid == 160046 || $gid == 160047 || $gid == 160048)
	{
	if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 4"))
		{
			throw new Exception ($GLOBALS['HeroFunc']['zhuiduoxin1']);
		}
		if($gid == 160048){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160047"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['zhuiduoxin2']);
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160046"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['zhuiduoxin3']);
			}
		}
		if($gid == 160047){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160048"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160048");
				
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160046"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['zhuiduoxin4']);
			}
		}
		if($gid == 160046){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160048"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160048");
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160047"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160047");
			}
		}
	}
	if($gid == 160049 || $gid == 160050 || $gid == 160051)
	{
	if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 3"))
		{
			throw new Exception ($GLOBALS['HeroFunc']['wuquxin1']);
		}
		if($gid == 160051){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160050"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['wuquxin2']);
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160049"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['wuquxin3']);
			}
		}
		if($gid == 160050){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160051"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160051");
				
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160049"))
			{
				throw new Exception ($GLOBALS['HeroFunc']['wuquxin4']);
			}
		}
		if($gid == 160049){
			if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160051"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160051");
			}
		    if(sql_check("select 1 from mem_hero_buffer where hid = $hid and buftype = 160050"))
			{
				sql_query("delete from mem_hero_buffer where hid = $hid and buftype = 160050");
			}
		}
	}
}
/**
 * 
 *以前的处理方法
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @return unknown
 */
function oldPraiseHero($uid, $param)
{
	//为了坑能用，加个没用的参数
	$funcitonname = array_shift($param);
	
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$typeidx = array_shift ( $param );
	$hero = sql_fetch_one ( "select * from sys_city_hero where cid='$cid' and uid='$uid' and hid='$hid'" );
	if (empty ( $hero )) {
		throw new Exception ( $GLOBALS ['largessHero'] ['cant_find_hero'] );
	}
	if(intval($hero['herotype'])==1000)  //君主将不能进行嘉赏
	{
		throw new Exception ( $GLOBALS['useGoods']['king_cannot_use'] );
	}
	//if ($hero["state"] > 1) //不在城池里
	if (isHeroInCity ( $hero ["state"] ) == 0) {
		throw new Exception ( $GLOBALS['pairse']['cant_largess_out_hero'] );
	}
	if ($typeidx < 5) //虎符,文曲星符，武曲星符，智多星符,猛油火罐
	{
		$gid = $typeidx + 26;
		if ($typeidx == 4)
		$gid = 116;
		$hufu = sql_fetch_one_cell ( "select count from sys_goods where uid='$uid' and gid='$gid'" );
		if (empty ( $hufu ) || ($hufu <= 0))
		throw new Exception ("not_enough_goods$gid");
		checkOhterConflictGoods($gid,$hid);
		addGoods ( $uid, $gid, - 1, 7 );
		$buftype = $typeidx + 1;
		if ($typeidx == 4)
		$buftype = 91;
		sql_query ( "insert into mem_hero_buffer (hid,buftype,endtime) values ('$hid','$buftype',unix_timestamp()+86400) on duplicate key update endtime=GREATEST(endtime,unix_timestamp())+86400" );
		if ($typeidx < 2) //如果是虎符或文曲星，而且将领是城守的话，要重新算资源加成
		{
			$chiefhid = sql_fetch_one_cell ( "select chiefhid from sys_city where cid='$cid'" );
			if (! empty ( $chiefhid ) && $chiefhid == $hid) {
				if (!empty($cid))
				sql_query ( "update sys_city_res_add set resource_changing=1 where cid='$cid'" );
			}
		}
		regenerateHeroAttri ( $uid, $hid );
		//完成成长任务
		if($gid == 28) {
			completeTaskWithTaskid($uid, 300); //武曲
		} else if($gid == 27) {
			completeTaskWithTaskid($uid, 301); //文曲
		} else if($gid == 29) {
			completeTaskWithTaskid($uid, 302); //智多星
		}
	}else if($typeidx>=19057 && $typeidx<=19058)
	{
		$gid = $typeidx + 25;
		$buftype = $typeidx;
		if(!checkGoods($uid, $gid))
		{
			throw new Exception ($GLOBALS['good']['not_enough_goods83']);
		}	
		$buftypes = array(19057,19058);
		checkSpecialCondition($hid,$gid,$buftypes);
		sql_query ( "insert into mem_hero_buffer (hid,buftype,endtime) values ('$hid','$buftype',unix_timestamp()+3600) on duplicate key update endtime=GREATEST(endtime,unix_timestamp())+3600" );
		addGoods ( $uid, $gid, -1, 7 );
		regenerateHeroAttri ( $uid, $hid );
	}else{//
		$gid=$typeidx-8 + 10155;//这里有两个东西未启用
		if($typeidx>=10&&$typeidx<=13)  //将领的2级丹药
		{
			$gid = $typeidx-8 + 19200;
		}
		if(intval($hero['herotype'])==1000){throw new Exception($GLOBALS['useGoods']['king_cannot_use']);}
		$msg = enhanceHero($uid,$hid,$gid);
		if(strpos($msg,sprintf($GLOBALS['act']['msg_tip'],''))===false){//返回值含有msg_tip说明出错了
			$msg = false;
		}
	}
	//sql_query("insert into mem_hero_schedule (hid,last_trick,last_largess) values ('$hid',0,unix_timestamp()) on duplicate key update last_largess=unix_timestamp()");
	$ret= getCityInfoHero ( $uid, $cid );
	if($msg){
		$ret[] = $msg;
	}
	return $ret;	
}

function checkSpecialCondition($hid,$gid,$buftypes)
{
	$now = sql_fetch_one_cell("select unix_timestamp()");
	$isUsedBufType = -1111;
		
	$horseType = sql_fetch_one_cell("select c.type from cfg_armor c left join sys_hero_armor s on c.`id`=s.`armorid` where s.`hid`='$hid' and s.`spart`='120'");
	if($gid==19082)    //马鞭
	{
		if(empty($horseType) || ($horseType!=4 && $horseType!=5))
		{
			throw new Exception($GLOBALS['HeroFunc']['armorType_is_wrong']);
		}
	}else if($gid==19083)   //汗血马鞭
	{
		if(empty($horseType) || $horseType!=5)
		{
			throw new Exception($GLOBALS['HeroFunc']['armorType_is_wrong']);
		}
	}
	
	foreach ($buftypes as $buftype)   //清除掉原来的效果
	{
		$result = sql_fetch_one("select * from mem_hero_buffer where hid='$hid' and buftype='$buftype' and endtime>='$now'");
		if(!empty($result))
		{
			if($result['buftype']==19058 && $gid==19082)  //如果玩家已经使用了汗血马鞭再使用马鞭道具就不让玩家使用
			{
				throw new Exception($GLOBALS['HeroFunc']['mabian']);
			}
			$isUsedBufType = $buftype;
			sql_query("delete from mem_hero_buffer where hid='$hid' and buftype='$isUsedBufType' limit 1");
		}
	}	
}

function checkOhterConflictGoods($gid,$hid)
{
	if($gid == 26){
		if(sql_check("select 1 from mem_hero_buffer where buftype in (160040,160041,160042) and hid = $hid"))
			sql_query("delete from mem_hero_buffer where buftype in(160040,160041,160042)and hid = $hid");
	}
	if($gid == 27){
		if(sql_check("select 1 from mem_hero_buffer where buftype in (160043,160044,160045) and hid = $hid"))
			sql_query("delete from mem_hero_buffer where buftype in (160043,160044,160045)and hid = $hid");
	}
	if($gid == 28){
		if(sql_check("select 1 from mem_hero_buffer where buftype in (160049,160050,160051) and hid = $hid"))
			sql_query("delete from mem_hero_buffer where buftype in (160049,160050,160051)and hid = $hid");
	}
	if($gid == 29){
		if(sql_check("select 1 from mem_hero_buffer where buftype in (160046,160047,160048) and hid = $hid"))
			sql_query("delete from mem_hero_buffer where buftype in (160046,160047,160048)and hid = $hid");
	}
}
/**
 * 
 * 周盈科重新写的方法
 *
 * @param unknown_type $uid
 * @param unknown_type $param
 * @return unknown
 */
function praiseHero($uid, $param)
{
	$gid=$param[3];
	if(($gid>=160040&&$gid<=160051)||($gid>=160023&&$gid<=160031)){//周科科新添加的
		//对于新加的物品使用的处理
		return newPraiseHero($uid,$param);
	}else if(($gid>25&&$gid <30)||$gid==116||($gid<=10155&&$gid>=10152)||($gid>=19082&&$gid<=19083) ||($gid>=19202&&$gid<=19205)){//原来还是原封不动的按原来的处理
		$index=gidToOldIndex($gid);
		$param[3]=$index;
		return oldPraiseHero($uid,$param);
	}
}
function gidToOldIndex($gid){//为了把现在的gid转换到原来的列表的位置便于利用oldPraiseHero方法
	switch ($gid){
		case 26:return 0;
		case 27:return 1;
		case 28:return 2;
		case 29:return 3;
		case 116:return 4;
		case 10152:return 5;
		case 10153:return 6;
		case 10154:return 7;
		case 10155:return 8;
		case 19082:return 19057;
		case 19083:return 19058;
	//	case 19201:return 9;
		case 19202:return 10;
		case 19203:return 11;
		case 19204:return 12;
		case 19205:return 13;
		default:return -1;
	}
}
function largessHeroWithGid($uid, $param) //用于虎符,文曲星符，武曲星符，智多星符
{
	//为了坑能用，加个没用的参数
	$funcitonname = array_shift($param);
	
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$gid = intval(array_shift ( $param ));
	$hero = sql_fetch_one ( "select * from sys_city_hero where cid='$cid' and uid='$uid' and hid='$hid'" );
	if (empty ( $hero )) {
		throw new Exception ( $GLOBALS ['largessHero'] ['cant_find_hero'] );
	}
	//if ($hero["state"] > 1) //不在城池里
	if (isHeroInCity ( $hero ["state"] ) == 0) {
		throw new Exception ( $GLOBALS ['largessHero'] ['cant_largess_out_hero'] );
	}
	if (($gid>=10150&&$gid<=10155) || ($gid>=19202&&$gid<=19205)){//
		$msg = enhanceHero($uid,$hid,$gid);
		if(strpos($msg,sprintf($GLOBALS['act']['msg_tip'],''))===false){//返回值含有msg_tip说明出错了
			$msg = false;
		}
	}else {		
		$hufu = sql_fetch_one_cell ( "select count from sys_goods where uid='$uid' and gid='$gid'" );
		if (empty ( $hufu ) || ($hufu <= 0))
		throw new Exception ( "not_enough_goods$gid" );
		checkGoodsRepeatUse($uid,$gid,$hid);
		checkOhterConflictGoods($gid,$hid);
		addGoods ( $uid, $gid, - 1, 7 );
		$buftype = $gid - 25;
		if(($gid>=160040&&$gid<=160051)||($gid>=160023&&$gid<=160031)){
			$buftype=$gid;			
			sql_query ( "insert into mem_hero_buffer (hid,buftype,endtime) values ('$hid','$buftype',unix_timestamp()+3600) on duplicate key update endtime=GREATEST(endtime,unix_timestamp())+3600" );
		}else{
			sql_query ( "insert into mem_hero_buffer (hid,buftype,endtime) values ('$hid','$buftype',unix_timestamp()+86400) on duplicate key update endtime=GREATEST(endtime,unix_timestamp())+86400" );
		}
		//sql_query ( "insert into mem_hero_buffer (hid,buftype,endtime) values ('$hid','$buftype',unix_timestamp()+86400) on duplicate key update endtime=GREATEST(endtime,unix_timestamp())+86400" );
		
		regenerateHeroAttri($uid,$hid);
		
		if ($gid == 26 || $gid == 27||$gid==160043||$gid==160044||$gid==160045) //如果是虎符或文曲星，而且将领是城守的话，要重新算资源加成
		{
			$chiefhid = sql_fetch_one_cell ( "select chiefhid from sys_city where cid='$cid'" );
			if (! empty ( $chiefhid ) && $chiefhid == $hid) {
				if (!empty($cid))
				sql_query ( "update sys_city_res_add set resource_changing=1 where cid='$cid'" );
			}
		}
		//sql_query("insert into mem_hero_schedule (hid,last_trick,last_largess) values ('$hid',0,unix_timestamp()) on duplicate key update last_largess=unix_timestamp()");
	}
	
	//完成成长任务
	if($gid == 28) {
		completeTaskWithTaskid($uid, 300); //武曲
	} else if($gid == 27) {
		completeTaskWithTaskid($uid, 301); //文曲
	} else if($gid == 29) {
		completeTaskWithTaskid($uid, 302); //智多星
	}
	$ret= getCityInfoHero($uid,$cid);
	if($msg){
		$ret[] = $msg;
	}
	return $ret;	
}

function releaseHero($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid'" );
	if (empty ( $hero ))
	throw new Exception ( $GLOBALS ['releaseHero'] ['hero_not_exist'] );
	if ($hero ['state'] != 5)
	throw new Exception ( $GLOBALS ['releaseHero'] ['hero_not_captive'] );

	throwHeroToField ( $hero );
	sql_query ( "delete from mem_hero_summon where hid='$hid'" );
	sql_query ( "delete from sys_hero_captive where hid='$hid'" );
	sql_query("insert into log_lionize(uid,npcid,`type`,`count`,`time`)  select  uid,npcid,12,0,unix_timestamp() from sys_lionize where uid=$uid and npcid=$hid");
	sql_query ( "delete from sys_lionize where npcid='$hid' and uid=$uid" );
	//updateCityHeroChange($uid,$cid);
	return getCityInfoHero ( $uid, $cid );
}

function rejectHero($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid'" );
	if (empty ( $hero ))
	throw new Exception ( $GLOBALS ['releaseHero'] ['hero_not_exist'] );
	if ($hero ['state'] != 6)
	throw new Exception ( $GLOBALS ['releaseHero'] ['hero_not_coming'] );

	throwHeroToField ( $hero );
	//updateCityHeroChange($uid,$cid);
	sql_query ( "delete from mem_hero_summon where hid='$hid'" );
	return getCityInfoHero ( $uid, $cid );
}

function getNpcIntroduce($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid'" );
	if (empty ( $hero ))
	throw new Exception ( $GLOBALS ['getNpcIntroduce'] ['no_hero_info'] );
	if ($hero ['npcid'] == 0)
	throw new Exception ( $GLOBALS ['getNpcIntroduce'] ['not_famous_hero'] );
	$npchero = sql_fetch_one ( "select * from cfg_npc_hero where npcid='$hero[npcid]'" );
	if (empty ( $npchero ))
	throw new Exception ( $GLOBALS ['getNpcIntroduce'] ['not_famous_hero'] );
	$ret = array ();
	$ret [] = $npchero;
	return $ret;
}

function makeHeroSummonNeed($hid, $level) {
	//生成需求
	$typeCount = 1 + floor ( ($level - 50) / 10 ); //生成几种道具
	$totalValue = $level * $level / 10;
	$goodsArray = sql_fetch_rows ( "select * from cfg_goods where gid >= 30 and gid <= 38" );
	$goodsCount = count ( $goodsArray );
	if ($typeCount > $goodsCount)
	$typeCount = $goodsCount;
	while ( $typeCount > 0 && $totalValue > 0 ) {
		$typeCount --;
		$goodsCount = count ( $goodsArray );
		$idx = mt_rand ( 0, $goodsCount - 1 );
		$goods = $goodsArray [$idx];
		$cnt = ceil ( $totalValue / $goods ['value'] );
		if ($typeCount == 0) //最后一个了
		{
			$real_cnt = $cnt;
		} else {
			$real_cnt = mt_rand ( 1, $cnt );
			$totalValue -= $real_cnt * $goods ['value'];
		}
		sql_query ( "insert into mem_hero_summon (hid,gid,name,count) values ('$hid','$goods[gid]','$goods[name]',$real_cnt)" );
		array_splice ( $goodsArray, $idx, 1 );
	}
}

function getHeroSummonGold($hero) {

	return ($hero ['level'] * 20 + (max ( $hero ['affairs_base'] + $hero ['affairs_add'] - 90, 0 ) + max ( $hero ['bravery_base'] + $hero ['bravery_add'] - 90, 0 ) + max ( $hero ['wisdom_base'] + $hero ['wisdom_add'] - 90, 0 )) * 50) * 50;
	/*
	 //黄金需求
	 if ($hero['npcid'] > 0)
	 {
	 return $hero['level'] * 100;
	 }
	 else
	 {
	 return $hero['level'] * 20;
	 }*/
}

function getSummonNeed($hero) {
	$hid = $hero ['hid'];
	$need = $GLOBALS ['trySummonHero'] ['hero_need'];
	$need .= $GLOBALS ['trySummonHero'] ['gold'] . getHeroSummonGold ( $hero );
	//宝物需求


	//ACT BEGIN活动接口
	$goods=getActSummonNeed($hero);
	//ACT END活动接口

	if (count($goods)==0 && $hero['level'] >= 50)//没有活动时才执行正常程序
	{
		$goods = sql_fetch_rows ( "select * from mem_hero_summon where hid='$hid'" );
		if (count ( $goods ) == 0) {
			makeHeroSummonNeed ( $hid, $hero ['level'] );
			$goods = sql_fetch_rows ( "select * from mem_hero_summon where hid='$hid'" );
		}
		foreach ( $goods as $good ) {
			$need .= "，" . $good ['name'] . $good ['count'];
		}
	}
	
	if($hero['npcid']>0)
	{
		$need .=$GLOBALS['hero']['summon_hero_msg'];
	}
	$need .= "。";
	return $need;
}

function tryAcceptHero($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid'" );
	if (empty ( $hero ))
	throw new Exception ( $GLOBALS ['trySummonHero'] ['no_hero_info'] );
	if ($hero ['state'] != 6) {
		throw new Exception ( $hero ['name'] . $GLOBALS ['tryAcceptHero'] ['hero_not_coming'] );
	}
	$need = getSummonNeed ( $hero );
	$ret = array ();
	$ret [] = $hid;
	$ret [] = $need;
	return $ret;
}

function sureAcceptHero($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid'" );
	if (empty ( $hero ) || ($hero ['cid'] != $cid))
	throw new Exception ( $GLOBALS ['sureSummonHero'] ['hero_not_exist'] );
	if ($hero ['state'] != 6)
	throw new Exception ( $GLOBALS ['tryAcceptHero'] ['hero_not_coming'] );

	$gold_need = getHeroSummonGold ( $hero );
	$mygold = sql_fetch_one_cell ( "select gold from mem_city_resource where cid='$cid'" );
	if ($mygold < $gold_need)
	throw new Exception ( $GLOBALS ['sureSummonHero'] ['no_enough_gold'] );
	$goodsList = sql_fetch_rows ( "select * from mem_hero_summon where hid='$hid'" );
	if (count ( $goodsList ) <= 0 && $hero['level']>=50) {//防止外挂搞不用珠宝招降
		getSummonNeed($hero);
		$goodsList = sql_fetch_rows ( "select * from mem_hero_summon where hid='$hid'" );
	}
	if (count ( $goodsList ) > 0) {
		foreach ( $goodsList as $goods ) {
			if (! sql_check ( "select * from sys_goods where uid='$uid' and gid='$goods[gid]' and count >= $goods[count]" )) {
				$msg = sprintf ( $GLOBALS ['sureSummonHero'] ['no_enough_goods'], $goods ['name'], $hero ['name'] );
				throw new Exception ( $msg );
			}
		}
		//有足够的宝物的话，开始扣
		foreach ( $goodsList as $goods ) {
			addGoods ( $uid, $goods ['gid'], - $goods ['count'], 7 );
		}
		//清招降要求
		sql_query ( "delete from mem_hero_summon where hid='$hid'" );
	}
	//扣黄金
	addCityResources ( $cid, 0, 0, 0, 0, - $gold_need );

	if($hero['npcid']>0)
	{
		$groupid=($hero['npcid']+2000)*10+1;//清原先的公共任务
		sql_query ( "delete from sys_hero_task where uid=$uid and `group`=$groupid");
		sql_query ( "insert into sys_lionize(`uid`, `npcid`, `friend`,`state`) values($uid,$hid,100,2) on duplicate key update friend=100,`state`=2" );
	}

	//招人
	sql_query ( "update sys_city_hero set state=0,loyalty=80 where hid='$hid'" );
	updateCityHeroChange ( $uid, $cid );
	return getCityInfoHero ( $uid, $cid );
}

function trySummonHero($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid'" );
	if (empty ( $hero ))
	throw new Exception ( $GLOBALS ['trySummonHero'] ['no_hero_info'] );
	if ($hero ['state'] != 5) {
		throw new Exception ( $hero ['name'] . $GLOBALS ['trySummonHero'] ['hero_not_captive'] );
	}
	$nobility_need = floor ( ($hero ['level'] - 1) / 10 );
	$mynobility = sql_fetch_one_cell ( "select nobility from sys_user where uid='$uid'" );
	//推恩
	$mynobility = getBufferNobility ( $uid, $mynobility );
	if ($mynobility < $nobility_need) {
		$nobility_name = sql_fetch_one_cell ( "select name from cfg_nobility where id='$nobility_need'" );

		$msg = sprintf ( $GLOBALS ['trySummonHero'] ['no_enough_nobility'], $nobility_name );
		//throw new Exception("我的主公，必定是威震天下的英雄，你连\"".$nobility_name."\"都没有达到，我是不会跟随你的。");
		throw new Exception ( $msg );
	}
	$need = getSummonNeed ( $hero );
	$ret = array ();
	$ret [] = $hid;
	$ret [] = $need;
	return $ret;
}
function sureSummonHero($uid, $param) {
	$cid = intval(array_shift ( $param ));
	$hid = intval(array_shift ( $param ));
	$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid'" );
	if($hero['uid']!=$uid){
		throw new Exception($GLOBALS['sureSummonHero']['err']);
	}
	if (empty ( $hero ) || ($hero ['cid'] != $cid))
	throw new Exception ( $GLOBALS ['sureSummonHero'] ['hero_not_exist'] );
	if ($hero ['state'] != 5)
	throw new Exception ( $GLOBALS ['sureSummonHero'] ['hero_not_captive'] );
	$nobility_need = floor ( ($hero ['level'] - 1) / 10 );
	$mynobility = sql_fetch_one_cell ( "select nobility from sys_user where uid='$uid'" );
	//推恩
	$mynobility = getBufferNobility ( $uid, $mynobility );
	if ($mynobility < $nobility_need) {
		$nobility_name = sql_fetch_one_cell ( "select name from cfg_nobility where id='$nobility_need'" );
		$msg = sprintf ( $GLOBALS ['trySummonHero'] ['no_enough_nobility'], $nobility_name );
		throw new Exception ( $msg );
	}
	$gold_need = getHeroSummonGold ( $hero );
	$mygold = sql_fetch_one_cell ( "select gold from mem_city_resource where cid='$cid'" );
	if ($mygold < $gold_need)
	throw new Exception ( $GLOBALS ['sureSummonHero'] ['no_enough_gold'] );
	//ACT BEGIN活动接口
	$retmsg="";//招将得到东西，要给用户提示
	$retmsg=sureActSummonHero($uid,$cid,$hero);
	//ACT END活动接口
	if($retmsg==="NoSuchAct"){//没有活动才执行正常程序
		$goodsList = sql_fetch_rows ( "select * from mem_hero_summon where hid='$hid'" );
		if (count ( $goodsList ) <= 0 && $hero['level']>=50) {//防止外挂搞不用珠宝招降
			getSummonNeed($hero);
			$goodsList = sql_fetch_rows ( "select * from mem_hero_summon where hid='$hid'" );
		}		
		if (count ( $goodsList ) > 0) {
			foreach ( $goodsList as $goods ) {
				if (! sql_check ( "select * from sys_goods where uid='$uid' and gid='$goods[gid]' and count >= $goods[count]" )) {
					$msg = sprintf ( $GLOBALS ['sureSummonHero'] ['no_enough_goods'], $goods ['name'], $hero ['name'] );
					//throw new Exception("你没有足够的".$goods['name']."，不能招降".$hero['name']."。");
					throw new Exception ( $msg );
				}
			}
			//有足够的宝物的话，开始扣
			foreach ( $goodsList as $goods ) {
				addGoods ( $uid, $goods ['gid'], - $goods ['count'], 7 );
			}
			//清招降要求
			sql_query ( "delete from mem_hero_summon where hid='$hid'" );
		}
	}
	//扣黄金
	addCityResources ( $cid, 0, 0, 0, 0, - $gold_need );
	//招降名將
	if ($hero ["npcid"] > 0) {
		$friend = sql_fetch_one_cell ( "select friend from sys_lionize where uid=$uid and npcid=$hid" );
		$rate = 10 + 0.9 * $friend;
		$temprate = mt_rand ( 1, 100 );
		if ($temprate > $rate) {
			sql_query ( "insert into sys_lionize(`uid`, `npcid`, `friend`,`state`) values($uid,$hid,0,3) on duplicate key update friend=friend+5" );
			//log
        	sql_query("insert into log_lionize(uid,npcid,`type`,`count`,`time`)  select  uid,npcid,2,friend,unix_timestamp() from sys_lionize where uid=$uid and npcid=$hid");
        	
			throw new Exception ( $GLOBALS ['sureSummonHero'] ['summon_fail'] );
		}
		//招降名将 发公告
		$uname = sql_fetch_one_cell ( "select name from sys_user where uid='$uid';" );
		//====
		$firstcapture=check_firsthero($uid,$uname,$hid);
		//if($firstcapture==1){//抓将活动
		//  $getherogoods=get_firstherogoods($uid,$hid);
		//  $msg ="恭喜玩家:".$uname.",第一个降服名将: ".$hero ["name"].$getherogoods;
		//  sendSysInform(0,1,0,600,50000,1,16738740,$msg);
		//}
		//else{
		if($firstcapture==0){
		  $msg = sprintf ( $GLOBALS ['summon_hero'] ['npc'], $hero ["name"], $uname );
		  sendSysInform(0,1,0,600,50000,1,16738740,$msg);
		}
		//}
		//======
		if (defined ( "USER_FOR_51" ) && USER_FOR_51) {
			require_once ("51utils.php");
			add51HeroEvent ( $hero ["name"] );
		}
		if (defined ( "PASSTYPE" )) {
			require_once 'game/agents/AgentServiceFactory.php';
			AgentServiceFactory::getInstance ( $uid )->addHeroEvent ( $hero ["name"] );
		}
		sql_query ( "insert into sys_lionize(`uid`, `npcid`, `friend`,`state`) values ($uid,$hid,0,2) on duplicate key update friend=100,`state`=2" );
		sql_query("insert into log_lionize(uid,npcid,`type`,`count`,`time`)  select  uid,npcid,2,friend,unix_timestamp() from sys_lionize where uid=$uid and npcid=$hid");
 	}
	//把原来修养状态的重新变为重伤状态
	sql_query ( "update sys_city_hero set hero_health=1 where hero_health=2 and hid='$hid' limit 1" );
	//招人
	sql_query ( "update sys_city_hero set state=0,loyalty=50,uid='$uid' where hid='$hid'" );
	//清除俘虏记录
	sql_query ( "delete from sys_hero_captive where hid='$hid'" );
	if ($hero ['npcid'] > 0) {
		$taskid = 30000 + $hero ['npcid'];
		$goalid = sql_fetch_one_cell ( "select id from cfg_task_goal where tid=$taskid" );
		completeTask ( $uid, $goalid );
	}
	updateCityHeroChange ( $uid, $cid );
	$ret=getCityInfoHero($uid,$cid);
	if($retmsg!=""&&$retmsg!=="NoSuchAct")
	$ret[] = sprintf($GLOBALS['act']['msg_tip'],$retmsg);
	return $ret;
}

/**
 * 装备属性排序，为了计算强化
 * @param $attr
 * @return unknown_type
 */
function sortArmorAttr($armor) {
	$ret = array ();
	$attributes = explode ( ",", $armor ['attribute'] );
	$attriCount = count ( $attributes );
	if ($attriCount == 0 || $attributes [0] * 2 + 1 != $attriCount) {
		return $ret;
	}
	//	$types = array();
	$idx = 0;
	$types = array();
	$value = array();
	for($i = 1; $i < $attriCount; $i = $i + 2) {
		$types[] = $attributes [$i];
		$value[] = $attributes [$i + 1];
		$idx ++;
	}
	for($i = 0; $i < $idx; $i++)
	{
		for($j = $idx - 1; $j >= $i + 1; $j--)
		{
			if($value[$j - 1] < $value[$j])
			{
				$tmp = $types[$j - 1];
				$types[$j - 1] = $types[$j];
				$types[$j] = $tmp;
				$tmp = $value[$j - 1];
				$value[$j - 1] = $value[$j];
				$value[$j] = $tmp;
			}
		}
	}
	$result = array();
	for($i = 0; $i < $idx; $i++)
	{
		$result[$types[$i]] = $value[$i];
	}
	return $result;
}
function sortmyArmorAttr($armor){//对特殊装备强化的处理 //1：统帅\r\n2：内政\r\n3：勇武\r\n4：智谋\r\n5：体力\r\n6：精力\r\n7：生命\r\n8：攻击\r\n9：防御\r\n10：射程\r\n11：速度\r\n12：负重',
	 $ret = array ();
	 $attributes = explode ( ",", $armor ['attribute'] );
	 $attriCount = count ( $attributes );
	 if ($attriCount == 0 || $attributes [0] * 2 + 1 != $attriCount){
		 return $ret;
	    }
     for($i = 1; $i < $attriCount; $i = $i + 2){		 
		 $result[$i]=$attributes [$i + 1];
	    }
	 return $result;
	}
	//处理套装属性及各种新加的不能在sys_city_hero表里面存储的属性
	function updateHeroNewAttribute($uid, $hid) {
		$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid' and uid=$uid" );

		if (empty ( $hero )) {
			throw new Exception ( $GLOBALS ['hero'] ['not_your_hero'] );
		}

		sql_query ( "delete from sys_hero_attribute where hid=$hid" );
		$tieids = sql_fetch_rows ( "select a.tieid from sys_hero_armor ha left join cfg_armor a on a.id=ha.armorid where hid=$hid group by tieid" );
		foreach ( $tieids as $id ) {
			$tieid = $id ['tieid'];
			$count = sql_fetch_one_cell ( "select count(*) from sys_hero_armor ha left join cfg_armor a on a.id=ha.armorid left join sys_user_armor ua on ua.armorid=a.id where ha.hid=$hid and a.tieid=$tieid and ua.hp>0" );
			$attrs = sql_fetch_rows ( "select * from cfg_tie_attribute where precond<=$count and tieid=$tieid" );
			foreach ( $attrs as $attr ) {
				$value = $attr ['value'];
				$attid = $attr ['attid'];
				sql_query ( "insert into sys_hero_attribute(`attid`, `hid`, `value`) values($attid, $hid, $value) on duplicate key update value=value+$value" );
			}
		}

	}
	
	function warmBloodGoldSpearSpecial($hid)
	{
		$count = sql_fetch_one_cell("select count(*) from sys_user_armor a, sys_hero_armor b where a.sid=b.sid and b.hid=$hid and b.armorid=12018 and a.active_special=1");
		if($count>=2)	//将领穿戴两把或两把以上激活特效的金枪，可以增加将领速度1点
			return 1;
		else 
			return 0;
	}
	
	function regenerateHeroAttri($uid, $hid) {      //重算属性
		$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid' and uid=$uid" );

		if (empty ( $hero )) {
			throw new Exception ( $GLOBALS ['hero'] ['not_your_hero'] );
		}
		sql_query ( "delete from sys_hero_attribute where hid=$hid" );

		$armors = sql_fetch_rows ( "select * from sys_user_armor u left join sys_hero_armor h on h.hid=u.hid and h.sid=u.sid left join cfg_armor c on c.id=h.armorid where u.uid='$uid' and u.hid='$hid' and u.hp>0" );

		//$armors=sql_fetch_rows("select * from sys_user_armor u,sys_hero_armor h left join cfg_armor c on c.id=h.armorid where u.uid='$uid' and u.hid='$hid' and h.sid=u.sid and u.hp>0");


		$level = $hero ['level'];
		$command = $hero ['level'] + $hero ['command_base'];
		$affairs = $hero ['affairs_base'] + $hero ['affairs_add'];
		$bravery = $hero ['bravery_base'] + $hero ['bravery_add'];
		$wisdom = $hero ['wisdom_base'] + $hero ['wisdom_add'];

		$commandAdd = 0;
		$forceAdd = 0;
		$energyAdd = 0;
		$affairsAdd = 0;
		$braveryAdd = 0;
		$wisdomAdd = 0;
		$speedAdd = 0;
		$attackAdd = 0;
		$defenceAdd = 0;
		$rangeAdd = 0;

		$levelvalue = array (0, 2, 2, 2, 2, 2, 2, 3, 3, 3, 6,3,3,5,5,7);//扩充到15级

		foreach ( $armors as $armor ) {
			//装备熔炼加成
			if (!empty($armor ['combine_level'])) {
				$combine_attrs = sql_fetch_one_cell("select attr from cfg_armor_level_attr where level={$armor ['combine_level']}");
				if (!empty($combine_attrs)) {
					$attributes = explode ( ",", $combine_attrs);
					$attriCount = count ( $attributes );
					if ($attriCount != 0 && $attributes [0] * 2 + 1 == $attriCount){
						for($i = 1; $i < $attriCount; $i = $i + 2) {
							$type = $attributes [$i];
							if ($type == 1)
							$commandAdd = $commandAdd + $attributes [$i + 1];
							else if ($type == 2)
							$affairsAdd = $affairsAdd + $attributes [$i + 1];
							else if ($type == 3)
							$braveryAdd = $braveryAdd + $attributes [$i + 1];
							else if ($type == 4)
							$wisdomAdd = $wisdomAdd + $attributes [$i + 1];
							else if ($type == 5)
							$forceAdd = $forceAdd + $attributes [$i + 1];
							else if ($type == 6)
							$energyAdd = $energyAdd + $attributes [$i + 1];
							else if ($type == 8)
							$attackAdd = $attackAdd + $attributes [$i + 1];
							else if ($type == 9)
							$defenceAdd = $defenceAdd + $attributes [$i + 1];
							else if ($type == 11)
							$speedAdd = $speedAdd + $attributes [$i + 1];
						}
					}
				}
			}
			$attributes = explode ( ",", $armor ['attribute'] );
			$attriCount = count ( $attributes );
			if ($attriCount == 0 || $attributes [0] * 2 + 1 != $attriCount)
			continue;
			for($i = 1; $i < $attriCount; $i = $i + 2) {
				$type = $attributes [$i];
				if ($type == 1)
				$commandAdd = $commandAdd + $attributes [$i + 1];
				else if ($type == 2)
				$affairsAdd = $affairsAdd + $attributes [$i + 1];
				else if ($type == 3)
				$braveryAdd = $braveryAdd + $attributes [$i + 1];
				else if ($type == 4)
				$wisdomAdd = $wisdomAdd + $attributes [$i + 1];
				else if ($type == 5)
				$forceAdd = $forceAdd + $attributes [$i + 1];
				else if ($type == 6)
				$energyAdd = $energyAdd + $attributes [$i + 1];
				else if ($type == 8)
				$attackAdd = $attackAdd + $attributes [$i + 1];
				else if ($type == 9)
				$defenceAdd = $defenceAdd + $attributes [$i + 1];
				else if ($type == 11)
				$speedAdd = $speedAdd + $attributes [$i + 1];
			}

			//强化加成
			//1：统帅\r\n2：内政\r\n3：勇武\r\n4：智谋\r\n5：体力\r\n6：精力\r\n7：生命\r\n8：攻击\r\n9：防御\r\n10：射程\r\n11：速度\r\n12：负重',
			$strong_value = intval ( $armor ['strong_value'] );
			$sattr = sortArmorAttr ( $armor );
			//=====
			if($armor['armorid']>53028) $sattr = sortmyArmorAttr( $armor );	
           	//=====
			$strong_level = intval ( $armor ['strong_level'] );
			for($i = 1; $i <= $strong_level; $i ++) {
				if ($i <= 15)		//扩充15级
				$strong_value = $levelvalue [$i];
				else
				$strong_value = 0;
				while ( $strong_value > 0 ) {
					foreach ( $sattr as $key => $val ) {
						if ($strong_value <= 0)
						break;
						$type = $key;
						if ($type == 1)
						$commandAdd = $commandAdd + 1;
						else if ($type == 2)
						$affairsAdd = $affairsAdd + 1;
						else if ($type == 3)
						$braveryAdd = $braveryAdd + 1;
						else if ($type == 4)
						$wisdomAdd = $wisdomAdd + 1;
						else if ($type == 5)
						$forceAdd = $forceAdd + 1;
						else if ($type == 6)
						$energyAdd = $energyAdd + 1;
						else if ($type == 8)
						$attackAdd = $attackAdd + 1;
						else if ($type == 9)
						$defenceAdd = $defenceAdd + 1;
						else if ($type == 11)
						$speedAdd = $speedAdd + 1;
						$strong_value --;
					}
				}
			}

			if (! empty ( $armor ['embed_pearls'] ) && $armor ['embed_pearls'] != "") {
				//镶嵌加成
				//$embedPearls = sql_fetch_rows("select * from cfg_goods where gid in ($armor[embed_pearls])");
				$embed_ids = explode ( ",", $armor ["embed_pearls"] );
				for($j = 0; $j < count ( $embed_ids ); $j ++) {
					if ($embed_ids [$j] == 0)
					continue;
					$ePearl = sql_fetch_one ( "select * from cfg_goods where gid=$embed_ids[$j]" );
					if (! empty ( $ePearl )) {
						if ($ePearl ['gid'] == 0)
						continue;
						$attrs = explode ( ",", $ePearl ['attr'] );
						for($i = 0; $i < count ( $attrs ); $i = $i + 2) {
							$type = $attrs [$i];
							if ($type == 1)
							$commandAdd = $commandAdd + $attrs [$i + 1];
							else if ($type == 2)
							$affairsAdd = $affairsAdd + $attrs [$i + 1];
							else if ($type == 3)
							$braveryAdd = $braveryAdd + $attrs [$i + 1];
							else if ($type == 4)
							$wisdomAdd = $wisdomAdd + $attrs [$i + 1];
							else if ($type == 5)
							$forceAdd = $forceAdd + $attrs [$i + 1];
							else if ($type == 6)
							$energyAdd = $energyAdd + $attrs [$i + 1];
							else if ($type == 8)
							$attackAdd = $attackAdd + $attrs [$i + 1];
							else if ($type == 9)
							$defenceAdd = $defenceAdd + $attrs [$i + 1];
							else if ($type == 11)
							$speedAdd = $speedAdd + $attrs [$i + 1];
						}
					}
				}
			}

			//套装神化加成
			$armorsid = $armor ['sid'];
			$deifyAttrs = sql_fetch_rows ( "select tda.*, ca.type from sys_user_tie_deify_attribute tda left join cfg_attribute ca on ca.attid=tda.attid where tda.sid=$armorsid" );
			if (empty ( $deifyAttrs ))
			continue;
			$deifyAttr = $deifyAttrs [0];
			$attid = $deifyAttr ['attid'];
			$value = $deifyAttr ['value'];
			sql_query ( "insert into sys_hero_attribute(`attid`, `hid`, `value`) values($attid, $hid, $value) on duplicate key update value=value+$value" );
			$type = $deifyAttr ['type'];
			if ($type == 1)
			$commandAdd = $commandAdd + $value;
			else if ($type == 2)
			$affairsAdd = $affairsAdd + $value;
			else if ($type == 3)
			$braveryAdd = $braveryAdd + $value;
			else if ($type == 4)
			$wisdomAdd = $wisdomAdd + $value;
			else if ($type == 5)
			$forceAdd = $forceAdd + $value;
			else if ($type == 6)
			$energyAdd = $energyAdd + $value;
			else if ($type == 8)
			$attackAdd = $attackAdd + $value;
			else if ($type == 9)
			$defenceAdd = $defenceAdd + $value;
			else if ($type == 11)
			$speedAdd = $speedAdd + $value;

		}

		//套装属性
		$tieids = sql_fetch_rows ( "select a.tieid from sys_hero_armor ha left join cfg_armor a on a.id=ha.armorid where hid=$hid group by tieid" );
		foreach ( $tieids as $id ) {
			$tieid = $id ['tieid'];
			if($tieid == 0) continue;
			$count = sql_fetch_one_cell ( "select count(*) from sys_hero_armor ha left join cfg_armor a on a.id=ha.armorid left join sys_user_armor ua on ua.sid=ha.sid where ha.hid=$hid and a.tieid=$tieid and ua.hp>0 and ua.uid=$uid" );
			$attrs = sql_fetch_rows ( "select ta.*, ca.type from cfg_tie_attribute ta left join cfg_attribute ca on ca.attid=ta.attid where precond<=$count and tieid=$tieid" );
			foreach ( $attrs as $attr ) {
				$value = $attr ['value'];
				$attid = $attr ['attid'];
				sql_query ( "insert into sys_hero_attribute(`attid`, `hid`, `value`) values($attid, $hid, $value) on duplicate key update value=value+$value" );
				$type = $attr ['type'];
				if ($type == 1)
				$commandAdd = $commandAdd + $value;
				else if ($type == 2)
				$affairsAdd = $affairsAdd + $value;
				else if ($type == 3)
				$braveryAdd = $braveryAdd + $value;
				else if ($type == 4)
				$wisdomAdd = $wisdomAdd + $value;
				else if ($type == 5)
				$forceAdd = $forceAdd + $value;
				else if ($type == 6)
				$energyAdd = $energyAdd + $value;
				else if ($type == 8)
				$attackAdd = $attackAdd + $value;
				else if ($type == 9)
				$defenceAdd = $defenceAdd + $value;
				else if ($type == 11)
				$speedAdd = $speedAdd + $value;
			}
		}

		//装备属性
		$newtypeattributes = sql_fetch_rows ( "select aa.*, ca.type from cfg_armor_attribute aa left join sys_user_armor ua on ua.armorid=aa.armorid left join cfg_attribute ca on ca.attid=aa.attid where ua.uid=$uid and ua.hid=$hid and ua.hp>0" );
		foreach ( $newtypeattributes as $attr ) {
			$value = $attr ['value'];
			$attid = $attr ['attid'];
			sql_query ( "insert into sys_hero_attribute(`attid`, `hid`, `value`) values($attid, $hid, $value) on duplicate key update value=value+$value" );
			$type = $attr ['type'];
			if ($type == 1)
			$commandAdd = $commandAdd + $value;
			else if ($type == 2)
			$affairsAdd = $affairsAdd + $value;
			else if ($type == 3)
			$braveryAdd = $braveryAdd + $value;
			else if ($type == 4)
			$wisdomAdd = $wisdomAdd + $value;
			else if ($type == 5)
			$forceAdd = $forceAdd + $value;
			else if ($type == 6)
			$energyAdd = $energyAdd + $value;
			else if ($type == 8)
			$attackAdd = $attackAdd + $value;
			else if ($type == 9)
			$defenceAdd = $defenceAdd + $value;
			else if ($type == 11)
			$speedAdd = $speedAdd + $value;
		}
		//统率百分比
		$commandpercent = sql_fetch_one_cell ( "select value from sys_hero_attribute where attid=10001 and hid=$hid" );
		if (empty ( $commandpercent ))
		$commandpercent = 0;
		$commandAdd += floor ( $command * $commandpercent / 100 );
		//内政百分比
		$affairspercent = sql_fetch_one_cell ( "select value from sys_hero_attribute where attid=10002 and hid=$hid" );
		if (empty ( $affairspercent ))
		$affairspercent = 0;
		$affairsAdd += floor ( $affairs * $affairspercent / 100 );
		//勇武百分比
		$braverypercent = sql_fetch_one_cell ( "select value from sys_hero_attribute where attid=10003 and hid=$hid" );
		if (empty ( $braverypercent ))
		$braverypercent = 0;
		$braveryAdd += floor ( $bravery * $braverypercent / 100 );
		//智谋百分比
		$wisdompercent = sql_fetch_one_cell ( "select value from sys_hero_attribute where attid=10004 and hid=$hid" );
		if (empty ( $wisdompercent ))
		$wisdompercent = 0;
		$wisdomAdd += floor ( $wisdom * $wisdompercent / 100 );
		//攻击百分比
		$attackpercent = sql_fetch_one_cell ( "select value from sys_hero_attribute where attid=10008 and hid=$hid" );
		if (empty ( $attackpercent ))
		$attackpercent = 0;
		$attackAdd += floor ( $bravery * 10 * $attackpercent / 100 );
		//防御百分比
		$defencepercent = sql_fetch_one_cell ( "select value from sys_hero_attribute where attid=10009 and hid=$hid" );
		if (empty ( $defencepercent ))
		$defencepercent = 0;
		$defenceAdd += floor ( $wisdom * 10 * $defencepercent / 100 );
/**/
	if(hasHeroBuffer($hid,160040)){//如果有青铜虎符则加10%100；原来的虎符是个操蛋的东西，对统帅的增加是在前台算的，并且是很多地方，如招贤馆，将领列表都得算。所以不和他保持一致了。
			$commandAdd+=$command*10/100;
//			$commandAdd+=($hero ['level']+$hero ['command_base'])*10/100;
		}
		if(hasHeroBuffer($hid,160041)){//
			$commandAdd+=$command*20/100;
//			$commandAdd+=($hero ['level']+$hero ['command_base'])*20/100;
		}
		if(hasHeroBuffer($hid,160042)){//
			$commandAdd+=$command*30/100;
//			$commandAdd+=($hero ['level']+$hero ['command_base'])*30/100;
		}
		if(hasHeroBuffer($hid,160043)){//
			$affairsAdd+=$affairs*20/100;
//			$affairsAdd+=ceil($hero ['affairs_base']*20/100);
		}
		if(hasHeroBuffer($hid,160044)){//
			$affairsAdd+=$affairs*10/100;
//			$affairsAdd+=$hero ['affairs_base']*10/100;
		}
		if(hasHeroBuffer($hid,160045)){//
			$affairsAdd+=$affairs*5/100;
//			$affairsAdd+=$hero ['affairs_base']*5/100;
		}
		if(hasHeroBuffer($hid,160046)){//
			$wisdomAdd+=$wisdom*20/100;
//			$wisdomAdd+=ceil($hero ['wisdom_base']*20/100);
		}
		if(hasHeroBuffer($hid,160047)){//
			$wisdomAdd+=$wisdom*10/100;
//			$wisdomAdd+=$hero ['wisdom_base']*10/100;
		}
		if(hasHeroBuffer($hid,160048)){//
			$wisdomAdd+=$wisdom*5/100;
//			$wisdomAdd+=$hero ['wisdom_base']*5/100;
		}
		if(hasHeroBuffer($hid,160049)){//
			$braveryAdd+=$bravery*20/100;
//			$braveryAdd+=ceil($hero ['bravery_base']*20/100);
		}
		if(hasHeroBuffer($hid,160050)){//
			$braveryAdd+=$bravery*10/100;
//			$braveryAdd+=$hero ['bravery_base']*10/100;
		}
		if(hasHeroBuffer($hid,160051)){//
			$braveryAdd+=$bravery*5/100;
//			$braveryAdd+=$hero ['bravery_base']*5/100;
		}
		
//		if(hasHeroBuffer($hid,160026)){//增加防御30%的上品八封阵图
//			$defenceAdd+=0.3*($wisdom+$wisdomAdd)*10;
//		}
//		if(hasHeroBuffer($hid,160027)){//增加防御20%的上品八封阵图
//			$defenceAdd+=0.2*($wisdom+$wisdomAdd)*10;
//		}
//		if(hasHeroBuffer($hid,160028)){//增加防御10%的上品八封阵图
//			$defenceAdd+=0.1*($wisdom+$wisdomAdd)*10;
//		}
//		if(hasHeroBuffer($hid,160029)){//增加攻击防御的
//			$attackAdd+=0.3*($bravery+$braveryAdd)*10;
//		}
//		if(hasHeroBuffer($hid,160030)){//增加攻击防御的
//			$attackAdd+=0.2*($bravery+$braveryAdd)*10;
//		}
//		if(hasHeroBuffer($hid,160031)){//增加攻击防御的
//			$attackAdd+=0.1*($bravery+$braveryAdd)*10;
//		}
		
		if (sql_check ( "select * from mem_hero_buffer where hid='$hid' and buftype=3 and endtime>unix_timestamp()" )) {
			$bravery = floor ( $bravery * 1.25 );
		}
		if (sql_check ( "select * from mem_hero_buffer where hid='$hid' and buftype=4 and endtime>unix_timestamp()" )) {
			$wisdom = floor ( $wisdom * 1.25 );
		}

		//根据将领佩戴的技能书核算
		$book=sql_fetch_one("select * from sys_user_book u left join cfg_book c on u.bid=c.id and u.level=c.level where u.uid=$uid and u.hid=$hid");
		
		if(!empty($book))
		{
			$bookAttrValue=intval($book['attr']);
			if ($book['bid'] == 4) {//运筹帷幄
				$commandAdd+=$bookAttrValue;
			} elseif ($book['bid'] == 5) {//骁勇善战
				$braveryAdd+=$bookAttrValue;
			} elseif ($book['bid'] == 6) {//政绩通达
				$affairsAdd+=$bookAttrValue;
			} elseif ($book['bid'] == 7) {//足智多谋
				$wisdomAdd+=$bookAttrValue;
			} elseif ($book['bid'] == 14) {//千里奔袭
				$speedAdd+=$bookAttrValue;
			}
		}
		
		//马鞭、汗血马鞭
		if(hasHeroBuffer($hid,19057)){
			$speedAdd=$speedAdd+2;
		}
		if(hasHeroBuffer($hid,19058)){
			$speedAdd=$speedAdd+5;
		}
		if (checkHeroLevel($uid,9,80)) {
			$speedAdd=$speedAdd+10;
		}
		$forcemax = 100 + floor ( $level / 5 ) + floor ( $bravery / 3 );
		$energymax = 100 + floor ( $level / 5 ) + floor ( $wisdom / 3 );
		//体力百分比
		$forcepercent = sql_fetch_one_cell ( "select value from sys_hero_attribute where attid=10005 and hid=$hid" );
		if (empty ( $forcepercent ))
		$forcepercent = 0;
		$forceAdd += floor ( $forcemax * $forcepercent / 100 );
		//精力百分比
		$energypercent = sql_fetch_one_cell ( "select value from sys_hero_attribute where attid=10006 and hid=$hid" );
		if (empty ( $energypercent ))
		$energypercent = 0;
		$energyAdd += floor ( $energymax * $energypercent / 100 );

		$forcemax += floor ( ($braveryAdd + $bravery % 3) / 3 ) + $forceAdd;
		$energymax += floor ( ($wisdomAdd + $wisdom % 3) / 3 ) + $energyAdd;
		
		//热血金枪特效加成
		$speedAdd+=warmBloodGoldSpearSpecial($hid);	
		
		//末日之刃特效
		$armorAttributeAddon = sql_fetch_rows("select a.attid,a.value from sys_armor_addon a,sys_hero_armor b where a.sid=b.sid and b.hid=$hid");
		foreach ($armorAttributeAddon as $attidArr) {
			$attid = $attidArr['attid'];
			$value = $attidArr['value'];
			 sql_query ( "insert into sys_hero_attribute(`attid`, `hid`, `value`) values($attid, $hid, $value) on duplicate key update value=value+$value" );
			 if ($attid == 10008) {
			 	$attackAdd = intval($attackAdd *0.01*(100+$value));
			 } elseif ($attid == 10009) {
			 	$defenceAdd = intval($defenceAdd * 0.01*(100+$value));
			 }  elseif ($attid == 11) {
			 	$speedAdd = $speedAdd + $value;
			 }
		}
		
		$sql = "update sys_city_hero set command_add_on=$commandAdd, affairs_add_on=$affairsAdd, bravery_add_on=$braveryAdd, wisdom_add_on=$wisdomAdd";
		$sql = $sql . ",force_max_add_on=$forceAdd,energy_max_add_on=$energyAdd,speed_add_on=$speedAdd,attack_add_on=$attackAdd,defence_add_on=$defenceAdd where hid='$hid'";
		sql_query ( $sql );
		sql_query ( "update mem_hero_blood set force_max=$forcemax, energy_max=$energymax,`force`=LEAST(`force`,$forcemax),`energy`=LEAST(`energy`,$energymax) where hid='$hid'" );
		
	}
	function hasHeroBuffer($hid,$buftype){
		if (sql_check ( "select * from mem_hero_buffer where hid='$hid' and buftype=$buftype and endtime>unix_timestamp()" )) {
			return true;
		}
		return false;
	}
	//流亡在外的将领列表
	function getExileHeros($uid, $param) {
		$uid=intval($uid);
		$heros = sql_fetch_rows ( "select a.loyalty as loyalty,h.hid,h.name as name,h.level,(h.command_base+h.level+h.command_add_on) as command,(h.affairs_base+h.affairs_add+h.affairs_add_on) as affairs,(h.bravery_base+h.bravery_add+h.bravery_add_on) as bravery,(h.wisdom_base+h.wisdom_add+h.wisdom_add_on) as wisdom from mem_hero_exile a join sys_city_hero h on a.hid = h.hid where a.uid = $uid and h.uid<=897" );
		$ret = array ();
		$ret [] = $heros;
		return $ret;
	}
	//召回旧部
	function tryCallbackHero($uid, $cid, $param) {
		$cid = intval(array_shift ( $param ));
		$hid = intval(array_shift ( $param ));
		$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid'" );
		if (empty ( $hero ))
		throw new Exception ( $GLOBALS ['tryCallback'] ['no_hero_info'] );
		$heroexile = sql_fetch_one ( "select * from mem_hero_exile where hid = $hid and uid = $uid" );
		if (empty ( $heroexile )) {
			throw new Exception ( $hero ['name'] . $GLOBALS ['tryCallback'] ['hero_not_exile'] );
		}
		if (cityHasHeroPosition ( $uid, $cid ) == false) { //招贤馆等级是否够
			throw new Exception ( $GLOBALS ['tryCallbackHero'] ['hotel_level_low'] );
		}
		$need = getSummonNeed ( $hero );
		$ret = array ();
		$ret [] = $hid;
		$ret [] = $need;
		return $ret;
	}
	function sureCallbackHero($uid, $cid, $param) {
		$cid = intval(array_shift ( $param ));
		$hid = intval(array_shift ( $param ));
		
		if (!sql_check("select cid from sys_city where uid='$uid' and cid='$cid'")) {
			throw new Exception ( $GLOBALS['waigua']['invalid'] );
		}
		
		$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid'" );

		if (empty ( $hero ))
		throw new Exception ( $GLOBALS ['tryCallback'] ['hero_not_exist'] );

		$exilehero = sql_fetch_one ( "select * from mem_hero_exile where hid='$hid'" );
		if (empty ( $exilehero ))
		throw new Exception ( $GLOBALS ['tryCallback'] ['hero_not_exile'] );

		if (cityHasHeroPosition ( $uid, $cid ) == false) { //招贤馆等级是否够
			throw new Exception ( $GLOBALS ['tryCallbackHero'] ['hotel_level_low'] );
		}

		$loyalty = intval ( $exilehero ["loyalty"] );
		$gold_need = getHeroSummonGold ( $hero );
		$mygold = sql_fetch_one_cell ( "select gold from mem_city_resource where cid='$cid'" );
		if ($mygold < $gold_need)
		throw new Exception ( $GLOBALS ['sureSummonHero'] ['no_enough_gold'] );
		$goodsList = sql_fetch_rows ( "select * from mem_hero_summon where hid='$hid'" );
		if (count ( $goodsList ) <= 0 && $hero['level']>=50) {//防止外挂搞不用珠宝招降
			getSummonNeed($hero);
			$goodsList = sql_fetch_rows ( "select * from mem_hero_summon where hid='$hid'" );
		}
		if (count ( $goodsList ) > 0) {
			foreach ( $goodsList as $goods ) {
				if (! sql_check ( "select * from sys_goods where uid='$uid' and gid='$goods[gid]' and count >= $goods[count]" )) {
					$msg = sprintf ( $GLOBALS ['sureSummonHero'] ['no_enough_goods'], $goods ['name'], $hero ['name'] );
					throw new Exception ( $msg );
				}
			}
			//有足够的宝物的话，开始扣
			foreach ( $goodsList as $goods ) {
				addGoods ( $uid, $goods ['gid'], - $goods ['count'], 7 );
			}

		}
		//扣黄金
		addCityResources ( $cid, 0, 0, 0, 0, - $gold_need );
		//忠诚值+10
		$loyalty = $loyalty + 10;
		
		$maxLoyalty = 100;
//		if ($loyalty >= 100)
//		$loyalty = 100;
		//召回
		if(intval($hero['herotype'])==10001)
		{
			$maxLoyalty=300;
			$childHid = sql_fetch_one_cell("select hid from sys_user_child where out_hid='$hid'");	
			sql_query("update mem_marry_relation set state='4' where uid='$uid' and shid=(select hid from sys_user_child where out_hid='$hid')");
			sql_query("insert into log_child_status(`hid`,`uid`,`out_hid`,`state`,`time`) values('$childHid','$uid','$hid','4',unix_timestamp()) on duplicate key update state='4',time=unix_timestamp()");
		}
		sql_query ( "update sys_city_hero set state=0,uid='$uid',cid='$cid',loyalty=LEAST($loyalty,$maxLoyalty) where hid='$hid'" );
		
		//清将领流亡状态
		sql_query ( "delete from mem_hero_exile where hid='$hid'" );
		if ($hero ['npcid'] > 0) {
			$taskid = 30000 + $hero ['npcid'];
			$goalid = sql_fetch_one_cell ( "select id from cfg_task_goal where tid=$taskid" );
			completeTask ( $uid, $goalid );
			
			//好感度处理
			sql_query("insert into sys_lionize(uid,npcid,friend,state) values ($uid,$hid,100,2) on duplicate key update friend=100,state=2");
		}

		$forcemax = 100 + floor ( $hero ['level'] / 5 ) + floor ( ($hero ['bravery_base'] + $hero ['bravery_add']) / 3 );
		$energymax = 100 + floor ( $hero ['level'] / 5 ) + floor ( ($hero ['wisdom_base'] + $hero ['wisdom_add']) / 3 );
		sql_query ( "insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`) values ('$hid',100,$forcemax,100,$energymax) on duplicate key update force_max=$forcemax,energy_max=$energymax" );
		/*
		 * 重新洗点
		 sql_query("update sys_city_hero set affairs_add=0,bravery_add=0,wisdom_add=0 where hid='$hid'");		 
		 */
		regenerateHeroAttri($uid,$hid);
		updateCityHeroChange ( $uid, $cid );
		return getCityInfoHero ( $uid, $cid );
	}

	function isHeroInCity($state) {
		if ($state == 0 || $state == 1 || $state == 7 || $state == 8)
		return 1;
		return 0;
	}

	//将领历练类型列表
	function getHeroExprTypes($uid, $cid, $param) {
		$exprTypes = sql_fetch_rows ( "select * from cfg_hero_expr_types" );
		return $exprTypes;
	}

	//城池内正在历练的将领
	function getExprHeros($uid, $cid, $param) {
		$uid=intval($uid);
		$cid=intval($cid);
		$exprHeros = sql_fetch_rows ( "select a.name as heroname,unix_timestamp() as curtime,a.hid,a.cid,a.uid,a.state,a.hero_health,a.herotype,b.starttime,b.endtime,b.hours,c.name as exprname,level  from sys_city_hero a left join sys_hero_expr b  on a.hid = b.hid left join cfg_hero_expr_types c on b.type=c.type where a.cid =$cid and a.uid = $uid and a.state in(0,10,11) and a.hero_health=0" );
		$exprTypes = sql_fetch_rows ( "select * from cfg_hero_expr_types where type in(1,2)" );
		//array_unshift($exprTypes,array("name"=>"---"));
		$ret = array ();
		$nowtime = sql_fetch_one_cell ( "select unix_timestamp()" );
		$ret [] = $nowtime;
		$ret [] = $exprHeros;
		$ret [] = $exprTypes;
		return $ret;
	}
	//需要修养的将领
	function getRestHeros($uid, $cid, $param) {
		$cid = intval($cid);

		$restHeros = sql_fetch_rows ( "select *  from sys_city_hero h left join mem_hero_blood m on m.hid=h.hid left join sys_hero_rest r on m.hid=r.hid where h.`cid`='$cid' and h.uid='$uid' and h.hero_health !=0 order by h.hero_health desc,r.endtime desc" );
		$hurtHeros = sql_fetch_rows ( "select * from sys_city_hero h left join mem_hero_blood m on m.hid=h.hid where h.`cid`='$cid' and h.uid='$uid' and h.hero_health = 2" );

		$ret = array ();
		$nowtime = sql_fetch_one_cell ( "select unix_timestamp()" );
		$ret [] = $nowtime;
		$ret [] = $restHeros;
		$ret [] = $hurtHeros;
		logUserAction($uid,6);
		return $ret;
	}

	//将领开始修炼
	function beginExprHero($uid, $cid, $param) {
		$uid=intval($uid);
		$cid=intval($cid);
		$expstarttime=1301043600;//3.15 11:00:00历练更新，暂停历练
		$expsendtime=1301472000;//3.15 16:00:00历练更新结束，重开历练/56小时
		$nowtime=time();
		if($nowtime<=$expsendtime&&$nowtime>=$expstarttime){
			 throw new Exception ($GLOBALS['update']['exp_update_alert']);
		}
		$vacendTime = sql_fetch_one_cell("select vacend from sys_user_state where uid='$uid'");
		if(!empty($vacendTime) && intval($vacendTime)>$nowtime)   //如果玩家当前处于休假状态，就不让将领进行修炼
		{
			return ;
		}
		$hid = array_shift ( $param );
		$cid = array_shift ( $param );
		$exprType = array_shift ( $param );
		$hours = array_shift ( $param );
		$carraymoney = array_shift ( $param );
		$hid=intval($hid);
		$cid=intval($cid);
		$exprType=intval($exprType);
		$hours=intval($hours);
		$carraymoney=intval($carraymoney);
		if ($hours<1) {
			throw new Exception ($GLOBALS['waigua']['invalid']);
		}
		
		$heroInfo = sql_fetch_one( "select state,level,herotype from sys_city_hero where hid = $hid" );
		$heroState = $heroInfo['state'];
		$heroLevel = $heroInfo['level'];
		$heroType = $heroInfo['herotype'];
		if ($heroState != 0)
		throw new Exception ( $GLOBALS ['heroexpr'] ['hero_expr_hero_not_kong'] );
		
		if ($heroType == 1000)
		throw new Exception ($GLOBALS['user_hero']['cannot_expr']);

		$maxHeroCount = 2;
		if (sql_fetch_one_cell ( "select count(1) from mem_user_buffer where buftype=100 and uid = $uid" ))
		$maxHeroCount = 5;
		$heroCount = sql_fetch_one_cell ( "select count(1) from sys_hero_expr where cid = $cid" );
		if ($heroCount == 5)
		throw new Exception ( $GLOBALS ['heroexpr'] ['hero_expr_count_reach_max'] );
		if ($heroCount >= $maxHeroCount) {
			$ret[]=1;
			$ret[]=$GLOBALS['heroexpr']['toomany_hero_expr'];
			return $ret;
		}

		$exprTypeData = sql_fetch_one ( "select * from cfg_hero_expr_types where type=$exprType" );
		if (empty ( $exprTypeData )) {
			throw new Exception ( "waigua gua le!" );
		}
		$need_money = $hours * intval ( $exprTypeData ["hour_money"] );
		$need_gold = $heroLevel * $hours * intval ( $exprTypeData ["hour_gold"] );
		if($hours>$exprTypeData ["max_hour"]||$hours<$exprTypeData ["min_hour"]){
			$msg=sprintf($GLOBALS['heroexpr']['hero_expr_time_error'],$exprTypeData['name'],$exprTypeData ["min_hour"],$exprTypeData ["max_hour"]);
			throw new Exception ($msg);
		}
		$city_Gold = sql_fetch_one_cell ( "select gold from mem_city_resource where cid = $cid" );
		if ($city_Gold < $need_gold)
		throw new Exception ( $GLOBALS ['heroexpr'] ['hero_expr_not_enough_gold'] );

		$have_money = sql_fetch_one_cell ( "select money from sys_user where uid = $uid" );
		if ($have_money < $carraymoney + $need_money)
		throw new Exception ( $GLOBALS ['heroexpr'] ['hero_expr_not_enough_money'] );
		if ($carraymoney < 0) {
			throw new Exception ( $GLOBALS ['heroexpr'] ['hero_expr_not_enough_money'] );
		}
		sql_query ( "update mem_city_resource set gold = gold - $need_gold where cid = $cid" );

		addMoney ( $uid, 0 - ($carraymoney + $need_money), 120 );
		logUserAction($uid,21);
		sql_query ( "insert into sys_hero_expr (`uid`,`cid`,`hid`,`type`,`starttime`,`endtime`,`hours`,`carrymoney`,`accTimes`,`state`) values ('$uid','$cid','$hid',$exprType,unix_timestamp(),unix_timestamp()+3600*$hours,$hours,$carraymoney,0,0)" );
		sql_query ( "update sys_city_hero set state = 10 where hid = $hid" );
		completeTaskWithTaskid($uid, 312);
		$ret[]=0;
		return $ret;
	}
	//取消历练
	function cancelHeroExpr($uid, $cid, $param) {
		$uid=intval($uid);
		$hid = intval(array_shift ( $param ));
		$item = sql_fetch_one ( "select b.hour_expr*(unix_timestamp()-starttime)/3600 as exp_add,(unix_timestamp()-starttime)/3600 as hours  from sys_hero_expr a,cfg_hero_expr_types b  where a.type = b.type and  hid = $hid" );
		if (empty ( $item ))
		return;
		logActionCountback($uid,21);//将领历练次数
		sql_query ( "update sys_hero_expr set state = 1,endtime= if(unix_timestamp()-starttime>hours*1800,endtime,2*unix_timestamp()-starttime ) where hid = $hid" );
		sql_query ( "update sys_city_hero set state = 11 where hid = $hid and state = 10" );
	}

	function fasterHeroExpr($uid, $cid, $param) {
		$uid=intval($uid);
		$cid=intval($cid);
		static $MIN_REDUCE_TIME = 1800;//最小缩短30分钟
		$hid = array_shift ( $param );
		$hid=intval($hid);
		$item = sql_fetch_one ( "select * from sys_hero_expr where hid = $hid" );
		if (empty ( $item ))
		return;
		if ($item ["state"] == 0) { //通关文书
			if (! checkGoods ( $uid, 143 )) {
				throw new Exception ( "not_enough_goods143" );
			}
			$oldEndTime = $item['endtime'];
			$curTime = $GLOBALS['now'];
			$reduceTime = intval(floor(($oldEndTime - $curTime) * 0.3));
			if ($reduceTime < $MIN_REDUCE_TIME) {//确保最小缩短30分钟
				$reduceTime = $MIN_REDUCE_TIME;
			}
			$newEndTime = $curTime + ($oldEndTime - $curTime - $reduceTime);
			sql_query ( "update sys_hero_expr set endtime=$newEndTime,accTimes=accTimes+1 where hid = $hid" );
			reduceGoods ( $uid, 143, 1 );
		} else { //急召令
			if (! checkGoods ( $uid, 144 ))
			throw new Exception ( "not_enough_goods144" );
			//获得经验 = 已经历练时间× 每小时获得经验数H + 10a 。    
			$carraymoney = $item ["carrymoney"];
			if ($carraymoney > 0)
			addMoney ( $uid, $carraymoney, 121 );
			reduceGoods ( $uid, 144, 1 );
			$exp_add = intval ( $item ["exp_add"] ) + 10 * rand ( $item ["hours"], 2 * $item ["hours"] );
			sql_query ( "delete from sys_hero_expr where hid = $hid" );
			sql_query ( "update sys_city_hero set state = 0,exp=exp+$exp_add where hid = $hid" );
		}
		unlockUser ( $uid );
	}
function isInBigHero($hid){
		//$bighids=array('10','36','102','107','114','118','177','186','241','255','285','320','321','340','347','357','360','362','381','382','409','456','484','518','541','562','580','620','677','699','725','780','801','856','861','870','894','900','901','902','903','904','905','906');
		$bighids=array('36','102','107','114','118','177','186','241','255','285','320','321','340','347','357','360','362','381','382','409','456','484','518','541','562','580','620','677','699','725','780','801','856','861','870');
		foreach ($bighids as $bighid){
			if($hid==$bighid){
				return true;
			}
		}
		return false;
	}
	/**
 * 接收客户端传过来的参数，对玩家历练奖励进行购买。
 */
function finishCommonExpr($uid,$param) {
	$id = intval(array_shift($param));
	$reward = sql_fetch_one("select * from sys_hero_expr_reward where id=$id and uid=$uid");
	if (empty($reward) || $reward['endtime']<time()) {
		throw new Exception($GLOBALS['heroexpr']['reward_timeout']);
	}
	$curMoney = sql_fetch_one_cell("select money from sys_user where uid=$uid");
	if ($reward['money'] > $curMoney || $reward['money'] < 0) {
		throw new Exception($GLOBALS['buyFromMerchant']['no_enough_YuanBao']);
	}
	if($reward['type']==5){
	  addMoney($uid,0-$reward['money'],121);
	  sql_query("delete from sys_hero_expr_reward where id=$id");
	  throw new Exception("你已经答应对方条件周章赎回自己的君主了！");
      return;	  
	}
	//加物品
	if (empty($reward['details'])) {
		throw new Exception($GLOBALS['heroexpr']['get_reward_error']);
	}
	if (1 == $reward['sort']) {//普通历练道具
		$goods = explode(',',$reward['details']);
		foreach ($goods as $v) {//特殊历练道具
			if (empty($v)) continue;
			addGoods($uid,$v,1,10);
		}
	} elseif (2 == $reward['sort']) {//特殊历练道具
		addGoods($uid,$reward['details'],1,10);
	} elseif (3 == $reward['sort']) {//将领历练装备
		$armor = sql_fetch_one("select * from cfg_armor where id={$reward['details']}");
		addArmor($uid,$armor,1,10);
	} elseif (4 == $reward['sort']) {//将领历练基础属性
		$bravery_base_add_on=0; //1：勇武		
		$wisdom_base_add_on=0;  //2：智力
		$affairs_base_add_on=0; //3：内政		
		$command_base_add_on=0; //4：统率
		$attack_add_on=0; //5：攻击
		$defence_add_on=0; //6：防御

		$attributes = explode(',',$reward['details']);
		for($i=0; $i<$attributes[0]; $i++) {
			$atrrType = $attributes[2*$i+1];
			$atrrValue = $attributes[2*$i+2];
			
			if (1 == $atrrType) {
				$bravery_base_add_on += $atrrValue;
			} elseif (2 == $atrrType) {
				$wisdom_base_add_on += $atrrValue;
			} elseif (3 == $atrrType) {
				$affairs_base_add_on += $atrrValue;
			} elseif (4 == $atrrType) {
				$command_base_add_on += $atrrValue;
			}  elseif (5 == $atrrType) {
				$attack_add_on += $atrrValue;
			}  elseif (6 == $atrrType) {
				$defence_add_on += $atrrValue;
			}
		}
		$sql = "insert into sys_city_hero_base_add (uid,hid,bravery_base_add_on,wisdom_base_add_on,affairs_base_add_on,command_base_add_on,type) values ($uid,{$reward['hid']},$bravery_base_add_on,$wisdom_base_add_on,$affairs_base_add_on,$command_base_add_on,1)";
		sql_query($sql);
		$sql = "update sys_city_hero set bravery_base=bravery_base+$bravery_base_add_on,wisdom_base=wisdom_base+$wisdom_base_add_on,affairs_base=affairs_base+$affairs_base_add_on,command_base=command_base+$command_base_add_on,attack_add_on=attack_add_on+$attack_add_on,defence_add_on=defence_add_on+$defence_add_on where hid = {$reward['hid']}";
		sql_query($sql);
	} elseif (5 == $reward['sort']) {//将领历练宝珠转换
		list($fromGoods,$toGoods) = explode(',',$reward['details']);
		$goodsCount = sql_fetch_one_cell("select count from sys_goods where uid=$uid and gid=$fromGoods");
		if (empty($goodsCount)) {
			throw new Exception($GLOBALS['heroexpr']['goods_not_enough']);
		}
		addGoods($uid,$fromGoods,-1,10);
		addGoods($uid,$toGoods,1,10);
	} elseif (6 == $reward['sort']) {//强化装备
		$curLevel = sql_fetch_one_cell("select strong_level+10 from sys_user_armor where uid=$uid and sid={$reward['details']}");//在返回空和0的情况下，不好判断，故多次一举。
		if (empty($curLevel)) {
			throw new Exception($GLOBALS['equipArmor']['arm_not_exist']);
		}
		$curLevel -= 10;
		if ($curLevel < 0) {
			$curLevel = 0;
		} elseif ($curLevel > 9) {
			$curLevel = 9;
		}
		$info = sql_fetch_one("select * from cfg_strong_probability where level=$curLevel+1");
		if (!empty($info)) {
			sql_query("update sys_user_armor set strong_value={$info['strong_value']}, strong_level={$info['level']} where uid=$uid and sid={$reward['details']}");
		}
	} elseif (7 == $reward['sort']) {//好感度
		if (!sql_check("select 1 from sys_lionize where uid=$uid and npcid={$reward['hid']}")) {
			throw new Exception($GLOBALS['hero']['not_your_hero']);
		}
		sql_query("update sys_lionize set friend=least(friend+1,120) where uid=$uid and npcid={$reward['hid']}");
		sql_query("insert into sys_city_hero_base_add (hid,uid,affairs_base_add_on,bravery_base_add_on,wisdom_base_add_on,type) values({$reward['hid']},$uid,1,1,1,1)");
	} elseif (8 == $reward['sort']) {//加快造兵队列
		$rows = sql_fetch_rows("select * from sys_city_draftqueue where cid={$reward['cid']}");
		if (empty($rows)) {
			throw new Exception($GLOBALS['heroexpr']['no_draftqueue']);
		}
		shuffle($rows);
		$row = array_shift($rows);
		
		$endTime = $row['needtime']*max(0,(1-$reward['details']*0.01));
		$endTime = intval($endTime);
		sql_query("update sys_city_draftqueue set needtime=$endTime where id={$row['id']}");
		if (1 == $row['state']) {
			sql_query("update mem_city_draft set state_endtime={$row['state_starttime']}+$endTime where id={$row['id']}");
		}
	}
	//历练扣元宝
	addMoney($uid,0-$reward['money'],121);
	$sql = "insert into log_hero_expr_reward (`uid`,`cid`,`hid`,`type`,`sort`,`money`,`details`,`endtime`,`time`) values('{$reward['uid']}','{$reward['cid']}','{$reward['hid']}','{$reward['type']}','{$reward['sort']}','{$reward['money']}','{$reward['details']}','{$reward['endtime']}',unix_timestamp())";
	sql_query($sql);
	sql_query("delete from sys_hero_expr_reward where id=$id");
	throw new Exception($GLOBALS['heroexpr']['get_reward_success']);
}

function checkHeroLevel($uid,$userLevel,$heroLevel) {
	$mUserLevel = sql_fetch_one_cell("select level from sys_user_level where uid=$uid");
	if (empty($mUserLevel)) {
		$mUserLevel=0;
	}
	$mHeroLevel = sql_fetch_one_cell("select level from sys_city_hero where uid=$uid and herotype=1000");
	if (empty($mHeroLevel)) {
		$mHeroLevel=0;
	}
	
	if ($mHeroLevel>=$heroLevel && $mUserLevel>=$userLevel) {
		return  true;
	} else {
		return  false;
	}
}

function loadKingTactic($uid)
{
	$gid = 10961;    //孙子兵法
	$bingfa = sql_fetch_one("select c.*,s.count from cfg_goods c left join sys_goods s on c.gid=s.gid and s.uid='$uid' where c.gid='$gid'");
	if(empty($bingfa['count']))
	{
		$bingfa['count'] = 0;
	}
	$kingLevel = sql_fetch_one_cell("select level from sys_user_level where uid='$uid'");
	if(empty($kingLevel))
	{
		$kingLevel = 0;
	}
	$ret = array();
	$ret[] = $bingfa;
	$ret[] = $kingLevel;
	
	return $ret;
}

function getKingReward($uid)
{
	$kingInfo = sql_fetch_one("select * from sys_city_hero where uid='$uid' and herotype='1000'");
	if(empty($kingInfo))
	{
		throw new Exception($GLOBALS['king']['not_find_kingHero']);
	}
	$kingLevelInfoExist = sql_fetch_one("select * from sys_user_level where uid='$uid' limit 1");
	if(empty($kingLevelInfoExist))
	{
		sql_query("insert into sys_user_level(`uid`,`level`,`time`,`getrewardtime`) values('$uid',0,0,0)");
	}
	
	$lastUpdate = sql_fetch_one_cell("select substr(from_unixtime(getrewardtime),1,10) from sys_user_level where uid=$uid");
	$curDate = sql_fetch_one_cell("select substr(now(),1,10)");
		
	if($lastUpdate == $curDate)
	{
		throw new Exception($GLOBALS['king']['has_get_reward']);
	}
	return dogetReward($uid ,$kingInfo);
}

function dogetReward($uid ,$kingInfo)
{
	$heroLevel = $kingInfo['level'];
	$gid = 10960;    //五铢钱
	$count=0;
	if(intval($heroLevel)<=9)
	{
		addGoods($uid, $gid, 5, 710);
		$count=5;
	}else if(intval($heroLevel)<=19)
	{
		addGoods($uid, $gid, 10, 710);
		$count=10;
	}else if(intval($heroLevel)<=29)
	{
		addGoods($uid, $gid, 15, 710);
		$count=15;
	}else if(intval($heroLevel)<=39)
	{
		addGoods($uid, $gid, 20, 710);
		$count=20;
	}else if(intval($heroLevel)<=49)
	{
		addGoods($uid, $gid, 30, 710);
		$count=30;
	}else if(intval($heroLevel)<=59)
	{
		addGoods($uid, $gid, 50, 710);
		$count=50;
	}else if(intval($heroLevel)<=69)
	{
		addGoods($uid, $gid, 60, 710);
		$count=60;
	}else if(intval($heroLevel)<=79)
	{
		addGoods($uid, $gid, 80, 710);
		$count=80;
	}else if(intval($heroLevel)<=89)
	{
		addGoods($uid, $gid, 100, 710);
		$count=100;
	}else if(intval($heroLevel)<=99)
	{
		addGoods($uid, $gid, 120, 710);
		$count=120;
	}else if(intval($heroLevel)<=109)
	{
		addGoods($uid, $gid, 150, 710);
		$count=150;
	}else if(intval($heroLevel)<=125)
	{
		addGoods($uid, $gid, 200, 710);
		$count=200;
	}
	sql_query("update sys_user_level set `getrewardtime`=unix_timestamp() where uid='$uid'");
	
	$msg = sprintf($GLOBALS['king']['get_reward_succ'],$count);
	
	$ret = array();
	$ret[]=$msg;
	
	return $ret;
}