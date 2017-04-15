<?php
require_once("global.php");
require_once("utils.php");

function loadUserValidSkillBook($uid)
{
	$ret = array ();
	$skillBooks = sql_fetch_rows ( "select *,a.id as xid from sys_user_book a left join cfg_book b on a.bid=b.skill and a.level=b.level where a.uid='$uid' and a.hid=0" ); //增加获取书的数量
	$bookShelfNum = sql_fetch_one_cell("select num from sys_user_bookShelfNum where uid='$uid'");
	if(empty($bookShelfNum))
	{
		$ret[] = 100;
	}else 
	{
		$ret[] = $bookShelfNum;
	}
	$ret[] = $skillBooks;
	return $ret;
}

function AddSkillBookShelf($uid)
{
	$moneyNum = sql_fetch_one_cell("select money from sys_user where uid='$uid'");
	if(intval($moneyNum)<50)
	{
		throw new Exception($GLOBALS['add_bookShelfNum']['no_enough_YuanBao']);
	}
		
	$curNum = sql_fetch_one_cell("select num from sys_user_bookShelfNum where uid='$uid'");
	if(intval($curNum)>=500)
	{
		throw new Exception($GLOBALS['add_bookShelfNum']['armor_column_full']);
	}
	addMoney($uid, -50, 31);  //31表示增加技能书栏扣除的元宝
	sql_query("insert into sys_user_bookShelfNum(`uid`,`num`) values('$uid','110') on duplicate key update num=num+10");
	$newNum = sql_fetch_one_cell("select num from sys_user_bookShelfNum where uid='$uid'");
	
	$ret[] = $newNum;
	return $ret;
}

function sellBook($uid,$param)
{
	$cid=intval(array_shift($param));
	$xid=intval(array_shift($param));
	checkCityExist($cid, $uid);
	$book=sql_fetch_one("select * from sys_user_book where id='$xid' and uid='$uid' and hid=0");
	if(empty($book)){
		// 提示该技能书不能卖
		throw new Exception($GLOBALS['book']['no_skill_book']);
	}
	$gold=$book['level']*5000;
	//去除该技能书
	sql_query("delete from sys_user_book where id='$xid' and uid='$uid' and hid=0");
	addCityResources($cid, 0, 0, 0, 0, $gold);
	$gold=sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
	$ret=array();
	$ret[]=$xid;
	$ret[]=$cid;
	$ret[]=$gold;
	return $ret;
}
function loadUserSkillBook($uid, $param) {
	$mBid = intval(array_shift ( $param ));
	$ret = array ();
	$skillBooks = sql_fetch_rows ( "select a.*,b.name,b.level,b.skill,b.attr_type,b.attr,b.description from sys_user_book a left join cfg_book b on a.bid=b.id and a.level=b.level where b.skill='$mBid' and a.uid='$uid' and a.hid=0" ); //增加获取书的数量
	$ret[] = $skillBooks;
	return $ret;
}

function getHeroSkill($uid, $param) {
	$hid = intval(array_shift ( $param ));
	$ret = array ();
	$ret[] = $hid;
	$isSkillExist = sql_fetch_one ( "select * from sys_hero_skill where `hid`='$hid'" );  //判断将领是否已经具备某种技能
	$isSkillOn = sql_fetch_one ( "select * from sys_user_book where `hid`='$hid'" );  //判定将领是否穿戴了技能书
	if (!empty ( $isSkillExist )) 
		{
			if(empty($isSkillOn))
			{
				$heroSkills = sql_fetch_rows ( "select * from sys_hero_skill s left join cfg_book c on s.skill=c.skill where s.hid='$hid'" );
			}else 
			{
				$heroSkills = sql_fetch_rows ( "select a.*,b.level from cfg_book a left join sys_user_book b on a.skill=b.bid where b.hid='$hid' and b.level=a.level limit 1" );
			}
			$ret[]= 1;
			$ret[] = $heroSkills;
			$isapparel = sql_fetch_one ( "select * from sys_user_book where `hid`='$hid'" );
			if (! empty ( $isapparel )) {
			$ret[] = $isapparel['id'];
			}
		}
		$ret[]=0;
		return $ret;
	}

//领悟技能
function resetHeroSkill($uid,$param) {
	
	$cid = intval(array_shift($param));
	$hid = intval(array_shift($param));
	
	$ret =array();
	$ret[] = $hid;
	
	$hero = sql_fetch_one("select * from sys_city_hero where uid=$uid and hid=$hid");
	if ($hero['state'] != 0) {
		throw new Exception($GLOBALS['book']['hero_not_free']);
	}
	if (empty($hero)) {
		throw new Exception($GLOBALS['book']['not_your_hero']);
	}
	$count = sql_fetch_one_cell("select count from sys_goods where gid=10668 and uid=$uid");
	$gid = '10668';
	if (empty($count)) {
		$msg = "not_enough_goods$gid";
		//throw new Exception($GLOBALS['book']['no_skill_book']);
		throw new Exception($msg);
	}
	if (sql_check("select 1 from sys_user_book where hid=$hid")) {
		throw new Exception($GLOBALS['book']['cannot_reset_skill']);
	}
	
	$skillId=0;
	$rate = rand(1,100);
	$allSkill = sql_fetch_rows("select * from cfg_skill_rate");
	foreach ($allSkill as $skil) {
		$rate -= intval($skil['rate']);
		if ($rate <=0) {
			$skillId = $skil['skill_id'];
			break;
		}
	}
	$heroSkill = sql_fetch_one("select * from sys_hero_skill where `hid`='$hid'");
	if (empty($heroSkill)) {
		sql_query("insert into sys_hero_skill(`hid`,`skill`) values('$hid','$skillId')");
	} else {
		sql_query("update sys_hero_skill set skill='$skillId' where hid='$hid'");
	}
	reduceGoods($uid,10668,1);
	
	$newSkillRes = sql_fetch_rows("select * from sys_hero_skill s left join cfg_book c on s.skill=c.skill where s.hid='$hid' limit 1");
	$ret[] = $newSkillRes;
	return $ret;
}

function setHeroBook($uid,$param) {
	
	$hid = intval(array_shift($param));
	$id = intval(array_shift($param));
	$cid = intval(array_shift($param));
	
	$ret = array();
	$ret[] = $hid;
	
	$state = sql_fetch_one_cell("select state from sys_city_hero where hid=$hid");
	if (!empty($state)) {
		throw new Exception($GLOBALS['book']['hero_not_free']);
	}
	$book = sql_fetch_one("select * from sys_user_book where uid=$uid and id=$id");
	if (empty($book)) {//先判断有没有书
		throw new Exception($GLOBALS['book']['no_book']); 
	}
	if (!empty($book['hid'])) {//在判断该书有没有装备到其他将领身上
		$mHname = sql_fetch_one_cell("select name from sys_city_hero where hid={$book['hid']}");
		$msg = sprintf($GLOBALS['book']['on_hero'],$mHname);
		throw new Exception($msg);
	}
	$heroBook = sql_fetch_one("select * from sys_user_book where uid=$uid and hid=$hid");
	if (!empty($heroBook)) {
		throw new Exception($GLOBALS['book']['hero_has_book']);
	}
	// 验证这个技能书是否确实该将领可以装备
	$canUseBook = sql_fetch_one_cell("select a.id from cfg_book a,sys_hero_skill b where a.skill=b.skill and b.hid=$hid limit 1");
	if (empty($canUseBook) || $canUseBook != $book['bid']) {
		throw new Exception($GLOBALS['book']['skill_tobo_book']);
	}
	$bookId = intval($book['bid']);
	if(($bookId == 8)||($bookId == 9)||($bookId == 10)||($bookId == 11)||($bookId == 12)||($bookId == 13))
	{
		updateCityResourceAdd($cid);
	}
	/*
	//生效效果
	$attrValue = sql_fetch_one_cell("select attr from cfg_book where id={$book['bid']} and level={$book['level']}");
	if (empty($attrValue)) {
		$attrValue = 0;
	}
	if ($book['bid'] == 4) {//运筹帷幄
		sql_query("update sys_city_hero set command_base=command_base+$attrValue where hid=$hid ");
	} elseif ($book['bid'] == 5) {//骁勇善战
		sql_query("update sys_city_hero set bravery_base=bravery_base+$attrValue where hid=$hid ");
	} elseif ($book['bid'] == 6) {//政绩通达
		sql_query("update sys_city_hero set affairs_base=affairs_base+$attrValue where hid=$hid ");
	} elseif ($book['bid'] == 7) {//足智多谋
		sql_query("update sys_city_hero set wisdom_base=wisdom_base+$attrValue where hid=$hid ");
	} elseif ($book['bid'] == 14) {//千里奔袭
		sql_query("update sys_city_hero set speed_add_on=speed_add_on+$attrValue where hid=$hid ");
	}
	*/

	sql_query("update sys_user_book set hid=$hid where id=$id");
	
	
	
	regenerateHeroAttri ( $uid, $hid );
	updateCityHeroChange ( $uid, $cid );
	$heroResult = getCityInfoHero ( $uid, $cid);
	
	$ret[] = $heroResult;
	
	$level = sql_fetch_one_cell("select `level` from sys_user_book where hid='$hid' and id='$id'");
	$result = sql_fetch_rows("select * from cfg_book c left join sys_user_book s on s.`bid`=c.`skill` where s.`hid`='$hid' and s.`level`=c.`level` and s.`level`='$level'");
	
	
	$ret[] = $result;
	return $ret;
}

function moveHeroBook($uid,$param) {
	$hid = intval(array_shift($param));
	$id = intval(array_shift($param));
	$cid = intval(array_shift($param));
	
	$ret = array();
	$ret[] = $hid;
	$state = sql_fetch_one_cell("select state from sys_city_hero where hid=$hid");
	if (!empty($state)) {
		throw new Exception($GLOBALS['book']['hero_not_free']);
	}
	$book = sql_fetch_one("select * from sys_user_book where uid=$uid and id=$id");
	if (empty($book)) {//先判断有没有书
		throw new Exception($GLOBALS['book']['no_book']); 
	}
	$heroBook = sql_fetch_one("select * from sys_user_book where uid=$uid and hid=$hid and bid={$book['bid']}");
	if (empty($heroBook)) {
		throw new Exception($GLOBALS['book']['hero_no_book']);
	}
	
	$bookId = intval($book['bid']);
	if(($bookId == 8)||($bookId == 9)||($bookId == 10)||($bookId == 11)||($bookId == 12)||($bookId == 13))
	{
		updateCityResourceAdd($cid);
	}
	/*
	//去掉效果
	$attrValue = sql_fetch_one_cell("select attr from cfg_book where id={$book['bid']} and level={$book['level']}");
	if (empty($attrValue)) {
		$attrValue = 0;
	}
	if ($book['bid'] == 4) {//运筹帷幄
		sql_query("update sys_city_hero set command_base=command_base-$attrValue where hid=$hid ");
	} elseif ($book['bid'] == 5) {//骁勇善战
		sql_query("update sys_city_hero set bravery_base=bravery_base-$attrValue where hid=$hid ");
	} elseif ($book['bid'] == 6) {//政绩通达
		sql_query("update sys_city_hero set affairs_base=affairs_base-$attrValue where hid=$hid ");
	} elseif ($book['bid'] == 7) {//足智多谋
		sql_query("update sys_city_hero set wisdom_base=wisdom_base-$attrValue where hid=$hid ");
	} elseif ($book['bid'] == 14) {//千里奔袭
		sql_query("update sys_city_hero set speed_add_on=speed_add_on-$attrValue where hid=$hid ");
	}
	//regenerateHeroAttri ( $uid, $hid );
	*/
	sql_query("update sys_user_book set hid=0 where id=$id");
	
	regenerateHeroAttri ( $uid, $hid );
	updateCityHeroChange ( $uid, $cid );
	$heroResult = getCityInfoHero ( $uid, $cid);
	
	$ret[] = $heroResult;
	
	$result = sql_fetch_rows("select * from sys_user_book s left join cfg_book c on s.bid=c.skill and s.hid='$hid' limit 1");
	
	$ret[] = $result;
	return $ret;
}

//合成技能书
function mergeBook($uid,$param) {
	$bid=intval(array_shift($param));
	$level=intval(array_shift($param));
	$flag = array_shift($param);//是否使用技能保护符：0，不使用；1，使用。
	if($level>=10||$level<=0){
		throw new Exception($GLOBALS['book']['skill_dev_incorrect']);
	}
	$books=sql_fetch_rows("select * from sys_user_book where uid='$uid' and  bid='$bid' and level='$level' and hid=0 limit 3");
	$count=count($books);
	if ($count != 3) {
		throw new Exception($GLOBALS['book']['not_your_book']);
	}
	//保护符：要有
	if ($flag && !sql_check("select 1 from sys_goods where gid=10667 and count>=1 and uid=$uid")) {
		//throw new Exception($GLOBALS['book']['no_skill_protect']);
		throw new Exception("not_enough_goods10667");
	}
	$success = false;
	$curLevel = $level;
	$nextLevel = $curLevel + 1;
	$rate = sql_fetch_one_cell("select rate from cfg_book_rate where level=$nextLevel");
	if (empty($rate)) {
		$rate = 10;
	}
	if (rand(1,100) <= $rate) $success = true;
	$bids=$books[0]['id'].','.$books[1]['id'].','.$books[2]['id'];
	//如果使用了技能保护符，材料不损失，否则，就扣掉
	$ret=array();
	if ($success) {
		sql_query("insert into sys_user_book(bid,uid,hid,level) values('$bid','$uid','0',$curLevel+1)");
		sql_query("delete from sys_user_book where id in ($bids)");		
		sql_query("insert into log_book(uid,bid,count,time,level) values('$uid','$bid','-3',unix_timestamp(),$curLevel)");
		sql_query("insert into log_book(uid,bid,count,time,level) values('$uid','$bid','1',unix_timestamp(),$curLevel+1)");
		if ($flag == 1) {
			reduceGoods ( $uid, 10667, 1 );
			sql_query("insert into log_book_strong(uid,bid,startlevel,endlevel,usegoods,time,success) values('$uid','$bid','$curLevel',$curLevel+1,10667,unix_timestamp(),1)");
		} else {
			sql_query("insert into log_book_strong(uid,bid,startlevel,endlevel,usegoods,time,success) values('$uid','$bid','$curLevel',$curLevel+1,0,unix_timestamp(),1)");
		}
		$ret[] = 1;
		$time = sql_fetch_one_cell("select unix_timestamp()");
		if ($time >1339056000 && $time <1339660800) {//这里是春节活动信息，1月18到2月2号
			switch ($curLevel+1) {
				case 2: $tgid=205;$tcount=1;break;
				case 3: $tgid=10778;$tcount=1;break;
				case 4: $tgid=41803;$tcount=1;break;
				case 5: $tgid=214;$tcount=1;break;
				case 6: $tgid=10830;$tcount=1;break;
				case 7: $tgid=50235;$tcount=1;break;
				case 8: $tgid=50278;$tcount=1;break;
				case 9: $tgid=50276;$tcount=1;break;
				case 10: $tgid=50279;$tcount=1;break;
				default:$tgid=10616;$tcount=0;break;
			}
			$tname=sql_fetch_one_cell("select name from cfg_goods where gid=$tgid");
			if ($tgid == 0) {
				addGift($uid,$tcount,5);
			} else {
				addGoods($uid,$tgid,$tcount,5);
			}
			$username = sql_fetch_one_cell("select name from sys_user where uid='$uid'");
			$msg = sprintf($GLOBALS['book']['combine_award'],$username,$tname,$tcount);
			sendSysInform(0,1,0,60,0,1,49151,$msg);
		}
	} else {
		if ($flag == 0) {
			sql_query("delete from sys_user_book where id in ($bids)");
			sql_query("insert into log_book(uid,bid,count,time,level) values('$uid','$bid','-3',unix_timestamp(),$curLevel)");
			sql_query("insert into log_book_strong(uid,bid,startlevel,endlevel,usegoods,time,success) values('$uid','$bid','$curLevel',$curLevel+1,0,unix_timestamp(),0)");
		} else {
			reduceGoods ( $uid, 10667, 1 );
			sql_query("insert into log_book_strong(uid,bid,startlevel,endlevel,usegoods,time,success) values('$uid','$bid','$curLevel',$curLevel+1,10667,unix_timestamp(),0)");
		}
		$ret[] = 0;
	}
	   $ret[] = $flag;
       $ret[] = loadSkillBook_OpenTab($uid,null);
	return $ret;
}


function loadSkillBook_OpenTab($uid,$param)
{
	$ret = array();
	//$ret[] = sql_fetch_rows("select a.*,b.id as `bNum`,count(b.`level`) as `count` from cfg_book a left join sys_user_book b on a.id=b.bid and a.level=b.level where b.uid='$uid' and b.hid=0 group by b.`bid`");
	$ret[] = sql_fetch_rows("select *,(select count(1)  from sys_user_book where uid=$uid and bid = a.skill and level=a.level and hid=0) as `count`  from cfg_book  a");
	$ret[] = sql_fetch_one("select c.*,g.count from cfg_goods c left join sys_goods g on g.gid=c.gid and g.uid=$uid where c.gid=10667");
	return $ret;
}

