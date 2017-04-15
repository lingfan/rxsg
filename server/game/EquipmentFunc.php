<?php 
require_once("./interface.php");
require_once("./utils.php");
require_once("./ArmorFunc.php");


function debug($msg)
{
	throw new Exception($msg);
}

function loadUnActiveHorseArmor($uid)
{
	$ret = array();
	$armors = sql_fetch_rows("select * from sys_user_armor s left join cfg_armor c on s.armorid=c.id   where uid=$uid and (embed_holes='' or embed_holes is null) and hid=0 and c.part = 12");	
    $ret[] = addSpecialArr($armors);
    $ret[] = getArmorNewAttribute($armors);
    $ret[] = getArmorEmbedGoods($armors);
    $ret[] = getTieInfo($armors, -1);
	$ret[] = getTieArmorAttribute($armors);
	$ret[] = getDeifyAttribute($armors);
	$ret[] = getFusionAttribute($armors);
	return $ret;
}

function loadUnActivePartArmor($uid, $param)
{
	$ret = array();
	$part=intval(array_shift($param));
	$type = intval(array_shift($param));
	if($type==0)   //所有品质的装备
	{
		$armors = sql_fetch_rows("select * from sys_user_armor s left join cfg_armor c on s.armorid=c.id where uid=$uid and (embed_holes='' or embed_holes is null) and hid=0 and c.part='$part'");
	}else 
	{
		$armors = sql_fetch_rows("select * from sys_user_armor s left join cfg_armor c on s.armorid=c.id where uid=$uid and (embed_holes='' or embed_holes is null) and hid=0 and c.part='$part' and c.type='$type'");
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


/**
 * 升级材料
 * @param $uid
 * @param $param
 * @return unknown_type
 */
/*function updateMaterials($uid, $param)
{
	//客户端传过来一个合成公式，比如gid,count, gid,count
	$mid1 = array_shift($param);
	$count1 = array_shift($param);
	$mid2 = array_shift($param);
	$count2 = array_shift($param);
	$mid3 = array_shift($param);
	$count3 = array_shift($param);
	$exp = makeExp($mid1, $count1, $mid2, $count2, $mid3, $count3);
	$recipe = sql_fetch_one( "SELECT * FROM cfg_recipe WHERE recipe=$exp" );
	if( empty($recipe) ){
		throw new Exception($GLOBALS['equipment']['no_recipe']);
	}
	
	if(true == probability($recipe['probability']))
		addGoods($uid, $recipe['gid'], 1, 0);
		
	//扣除材料
	addGoods($uid, $mid1, -$count1, 0);
	addGoods($uid, $mid2, -$count2, 0);
	addGoods($uid, $mid3, -$count3, 0);
}*/

/**
 * 装备镶嵌
 * @param $uid
 * @param $param
 * @return unknown_type
 */
function armorEmbed($uid, $param)
{
	$armor_id = array_shift($param);
	$pearl_id = array_shift($param);
	$hole_pos = array_shift($param);
	
	$phole = "$pearl_id,".$hole_pos.",";
	
	sql_query("UPDATE sys_user_armor SET embed_pearl=concat(embed_pearl, '$phole') ON DUPLICATE KEY UPDATE WHERE uid=$uid AND armorid=$armor_id");
} 


/**
 * 初始化数据
 * @param $uid
 * @return unknown_type
 */
function loadEquitmentInfor($uid, $param)
{
	$ret = array();
	$step = array_shift($param);
//	$xilianIndex=intval(array_shift($param));
//	if($xilianIndex>2 || $xilianIndex<0)
//		throw new Exception($GLOBALS['xilian']['param_error']);
//	$xilianGidArr=array(12079,12080,12081);
//	$xilianGid=$xilianGidArr[$xilianIndex];
	
	$ret[] = 0;
	//新增高级强化宝珠，gid 11170,宝珠保护符 gid 11172
	$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = b.gid) as `count` from cfg_goods b where b.gid in (202, 203, 204, 205,11170,11172,10667,10778,12157,12158,12159,12160,12161)");
	if($step < 0 )
		$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where ((a.gid>=300 and a.gid<=379) or a.gid=205 ) and `group`=4 order by a.gid");
	return $ret;
}
//***********************强化****************************************//

function getEquipmentGoods($uid, $param)
{
	$gid = array_shift($param);
	$obj = sql_fetch_one("select * from sys_goods where uid=$uid and gid=$gid");
	if(empty($obj) || $obj['count']==0)
		throw new Exception($GLOBALS['equipment']['no_goods']);
	$ret=array();
	$ret[] = $obj;
	return $ret;
}

function strongLimit($armor, $cid)
{
	$part = $armor["part"];
	$type = $armor["type"];   //1：灰装\r\n2：白装\r\n3：绿装\r\n4：蓝装\r\n5：紫装\r\n6：橙装\r\n7：红装',
	$strong_level = $armor["strong_level"];
	
	if($strong_level >= 15) 		//装备强化扩展到15级
		throw new Exception($GLOBALS['equipment']['strong_level_limit']);

	if ($part == 12) { 	//坐骑
		$tid = 22;			//驯马技巧
		$info = "strong_limit_technic_barn";
	}
	else {
		$tid = 21;			//打造技巧
		$info = "strong_limit_technic_blacksmith";
	}
	// 装备强化等级的上限是打造技术的等级, 坐骑强化等级的上限是驯化技术的等级
	$level_limit_by_technic = sql_fetch_one_cell("select level from sys_city_technic where cid='$cid' and `tid`='$tid'"); // (cid, tid) is unique
	if($strong_level <= 9)	//9级和9级以下装备强化时，科技需比当前等级大1
	{
		if( empty($level_limit_by_technic) || $strong_level>=$level_limit_by_technic )
		{
			$msg = sprintf($GLOBALS['equipment'][$info], $strong_level+1);
			throw new Exception($msg);
		}
	}
	if ($strong_level >= 10)	//10级和10级以上装备强化时，科技需要为10级
	{
		if( empty($level_limit_by_technic) || $level_limit_by_technic <10 )
		{
			$msg = sprintf($GLOBALS['equipment'][$info], 10);
			throw new Exception($msg);
		}
	}
	if( ($type== 1 && $strong_level>=3) || ($type==2 && $strong_level>=6)  || ($type==3 && $strong_level>=9) )
	{
		$msg = sprintf($GLOBALS['equipment']['strong_limit'], $GLOBALS['battle']['strong_limit_array'][$type], $strong_level);
		throw new Exception($msg);
	}
	//强化蓝装到11级和以上时，需要熔炼达到三级,但坐骑例外
	if($part!=12 && $type == 4 && $strong_level >= 10 && $armor["combine_level"] <= 2 )
	{
		throw new Exception($GLOBALS['equipment']['blue_strong_limit']);
	}
}

function doStrong($uid, $param)
{
	$cid = intval(array_shift($param));
	$gid1= intval(array_shift($param));
	$good1Count = intval(array_shift($param)); //0, 1 //count
	$gid2= intval(array_shift($param));
	$good2Count = intval(array_shift($param)); //0, 1
	$sid = intval(array_shift($param));
	
	
	
	if($good1Count > 1 || $good1Count < 0) {
		throw new Exception($GLOBALS['waigua']['forbidden']);
	}
	
	if($good2Count > 1 || $good2Count < 0) {
		throw new Exception($GLOBALS['waigua']['forbidden']);
	}
	
	if($gid1 != 212 && $gid1 != 203) {		//212，伯乐符，203，天工符
		throw new Exception($GLOBALS['equipment']['wrong_item']);
	}
	if($gid2 != 213 && $gid2 != 204) {		//213师皇针，204，乾坤宝珠
		throw new Exception($GLOBALS['equipment']['wrong_item']);
	}
	
	
	$is_zuoji = array_shift($param);
	
	
	$ret = array();
	$armor = sql_fetch_one("select * from sys_user_armor s left join cfg_armor c on s.armorid=c.id where s.sid=$sid and s.uid=$uid");
	
	if(empty($armor))
	{
		$ret[] = 0;
		if($is_zuoji == 1){
			$ret[] = $GLOBALS['equipment']['no_such_horse'];
		} else {
			$ret[] = $GLOBALS['equipment']['no_such_armor'];
		}
		return $ret;
	}
	
	if($armor['hid']!=0)
		throw new Exception($GLOBALS['equipment']['armor_in_hero']);
	if(($armor['part']==12&&$is_zuoji!=1)||$armor['part']!=12&&$is_zuoji==1){
		throw new Exception($GLOBALS['equipment']['data_exception']);
	}
	
		
	if($good1Count==1)
	{
		$goods = sql_fetch_one("select * from sys_goods where gid=$gid1 and uid=$uid");
		if(empty($goods) || $goods['count']<=0)
			if($is_zuoji == 1)
				throw new Exception("not_enough_goods212");
			else
				throw new Exception("not_enough_goods203");
	}
	
	if( $good2Count==1 )
	{
		$goods = sql_fetch_one("select * from sys_goods where gid=$gid2 and uid=$uid");
		if(empty($goods) || $goods['count']<=0)
		if($is_zuoji == 1)
			throw new Exception("not_enough_goods213");
		else
			throw new Exception("not_enough_goods204");
	}
	
	$startlevel=$armor['strong_level'];	
	$strong_value = $armor["strong_value"];
	$nlevel = intval($armor["strong_level"]) + 1;
	
	if($is_zuoji == 1)
	{
		if($startlevel>=0 && $startlevel <= 9)
		{
			$stuff = sql_fetch_one("select * from sys_goods where gid=214 and uid=$uid"); //灵通甘草
			if(empty($stuff) || $stuff['count']<=0)
			{
				throw new Exception($GLOBALS['equipment']['no_tlgc_goods']);
			}
		}
		else if ($startlevel >= 10 && $startlevel<=15) 
		{
			$stuff = sql_fetch_one("select * from sys_goods where gid=11171 and uid=$uid"); //高级灵通甘草
			if(empty($stuff) || $stuff['count']<=0)
			{
				throw new Exception($GLOBALS['equipment']['no_gjltgc_goods']);
			}
		}
	}
	else
	{	$is_zuoji=0;
		if($startlevel>=0 && $startlevel <= 9)
		{
			$strong_pearl = sql_fetch_one("select * from sys_goods where gid=205 and uid=$uid"); //强化宝珠
			if(empty($strong_pearl) || $strong_pearl['count']<=0)
			{
				throw new Exception($GLOBALS['equipment']['no_strong_pearl']);
			}
		}
		if($startlevel >= 10 && $startlevel<=15)
		{
			$strong_pearl = sql_fetch_one("select * from sys_goods where gid=11170 and uid=$uid"); //高级强化宝珠
			if(empty($strong_pearl) || $strong_pearl['count']<=0)
			{
				throw new Exception($GLOBALS['equipment']['no_high_strong_pearl']);
			}
		}

	}
	strongLimit($armor, $cid);
	
	if($is_zuoji == 1){	//前面不是判断过了吗
		if($armor['part']!=12)
			throw new Exception($GLOBALS['equipment']['not_zuoji']);
	}
	else{
		if($armor['part']==12)
			throw new Exception($GLOBALS['equipment']['is_zuoji']);
	}
	$inact = sql_fetch_one_cell("select actid from cfg_act where type=10001 and unix_timestamp()>starttime and unix_timestamp()<endtime limit 1");//是否在强化活动期间
	$incount = sql_fetch_one_cell("select count(*) from log_armor_strong where sid=$sid and uid=$uid and startlevel=$startlevel");
	if(!empty($incount)) sendSysInform(0,1,0,600,50000,1,49151,$incount);
	$inact=sql_fetch_one_cell("select actid from cfg_act where actid=4422 and unix_timestamp()>starttime and unix_timestamp()<endtime limit 1");//是否在强化活动期间
	if(empty($inact)){
		$inact=false;
	}else{
		$inact=true;
	}
	$next_level_infor = sql_fetch_one("select * from cfg_strong_probability where level=$nlevel");
	
	if(empty($next_level_infor))
		throw new Exception($GLOBALS['equipment']['cannot_strong']);
	
	
	$succ_add = $good1Count * 7; //提高7%成功率	
	if($inact){
		$succ_add+=3;//活动期间概率加3
	}
	//表示确实进行了强化，没有throw exception
	$ret[] = 1;
	//坐骑上11及之后的等级强化配置不由表中给出。
	if(($is_zuoji==1 && $nlevel<=10) || $is_zuoji!=1)
	{
		$randRate=rand(1,10000);
		$suc_val=intval($next_level_infor['suc_value']) *(1+ $succ_add/100);
		if($randRate <= $suc_val*100 || $incount>49)
			$is_succ=true;
		else 
			$is_succ=false;	
	}
	else 	//强化10以上坐骑,概率另给了，所以另算,其他配置一样，如强化增加属性值，掉级概率100%
	{
		$rateArr=array(11=>5,12=>5,13=>5,14=>3,15=>1);
		$randRate=rand(1,10000);
		$suc_val=$rateArr[$nlevel]*(1+$succ_add/100);
		if($randRate <= $suc_val*100 || $incount>79)
			$is_succ=true;
		else 
			$is_succ=false;		
	}
	
	$is_succ=reCalculateSuccess($armor,$inact,$is_succ);
	if( $is_succ )
	{
		$best_quality=$armor['best_quality'];
		if($best_quality==null || $best_quality=="")//若装备没有极品属性，算一个给他
		{
			$best_quality=getBestQuality($next_level_infor['xilian_rate'],$armor['type']);  
		}
		if(!empty($best_quality))
		{
			sql_query("update sys_user_armor set strong_times=0, strong_value=$next_level_infor[strong_value], strong_level=$next_level_infor[level],best_quality='$best_quality' where uid=$uid and sid=$sid");
		}
		else 
		{
			sql_query("update sys_user_armor set strong_times=0, strong_value=$next_level_infor[strong_value], strong_level=$next_level_infor[level] where uid=$uid and sid=$sid");
		}
		$strong_value = $next_level_infor['strong_value'];
		$endlevel=$startlevel+1;
		if ($is_zuoji) {
			strongActOnce($uid,$next_level_infor[level]);//坐骑强化活动
		}
	//发公告
		if($next_level_infor['level']>=7)
		{
			$name = sql_fetch_one_cell("select name from sys_user where uid=$uid");
			$armor_name = sql_fetch_one_cell("select name from cfg_armor where id=$armor[armorid]");
			$msg = sprintf($GLOBALS['equipment']['strong_7'], $name, $armor_name, $next_level_infor['level']);
			sendSysInform(0,1,0,60,0,1,49151,$msg);
			//sql_query("insert into sys_inform (type,inuse,starttime,endtime,`interval`,scrollcount,color,msg) values (0,1,unix_timestamp(),unix_timestamp()+60,0,1,49151,'$msg')");
		}
		$ret[] = 0;
		/***************************装备强化活动BEGIN*******************************/
		//6abc,a：0 装备  1坐骑 2都可以，bc强化到XX级 ，11强化成功即可，不分级别
		$actmsg=checkAndDoStrongArmorAct($uid,$armor['armorid'],$nlevel,$is_zuoji);
		/***************************装备强化活动END*******************************/
		completeTask($uid,530);		//强化装备到1级的任务，应加个判断
		if($next_level_infor['level']==6){
			completeTaskWithTaskid($uid,103803);//活动任务，强化任意一件装备到6级
		}
	}
	else
	{
		$zero_value =  intval($next_level_infor['zero_value']);		//回0概率
		$degrade_value = intval($next_level_infor['degrade_value']);	//降级概率
		$intact_value = intval($next_level_infor['intact_value']);		//完好无损概率 
		
		$p_value = pValue();
		sql_query("update sys_user_armor set strong_times=strong_times+1 where uid=$uid and sid=$sid");
		if($good2Count==0)
		{//归零，降级，完整
			if( fitPValue($p_value, 1, $zero_value) ){//归零
				sql_query("update sys_user_armor set strong_value=0, strong_level=0 where uid=$uid and sid=$sid");
				$strong_value=0;
				$endlevel=0;
				$ret[]=1;
			}
			elseif(fitPValue($p_value, $zero_value+1, $zero_value+$degrade_value)){	//降级
				$before_level = intval($next_level_infor['level']) - 2;
				$before_level_infor = sql_fetch_one("select * from cfg_strong_probability where level=$before_level");
				sql_query("update sys_user_armor set strong_value=$before_level_infor[strong_value], strong_level=$before_level_infor[level] where uid=$uid and sid=$sid");
				$strong_value= $before_level_infor['strong_value'];
				$endlevel=$startlevel-1;
				$ret[]=2;
			}
			elseif( fitPValue($p_value, $zero_value+$degrade_value+1, $zero_value+$degrade_value+$intact_value) ){//无损失
				$ret[]=3;
				$endlevel=$startlevel;
			}
		}
		else	//可以购买道具“乾坤宝珠”，在强化失败后装备不会消失,还有师皇针
		{ 
/*			if(fitPValue($p_value, $zero_value+1, $zero_value+$degrade_value)){	//降级
				$before_level = intval($next_level_infor['level']) - 2;
				$before_level_infor = sql_fetch_one("select * from cfg_strong_probability where level=$before_level");
				sql_query("update sys_user_armor set strong_value=$before_level_infor[strong_value], strong_level=$before_level_infor[level] where uid=$uid and sid=$sid");
				$strong_value=$before_level_infor['strong_value'];
				$ret[]=2;
					}
			else  if( fitPValue($p_value, $zero_value+$degrade_value+1, $zero_value+$degrade_value+$intact_value) )无损失  */
				$endlevel=$startlevel;
				$ret[]=3;
		}
	}
	
	if($good1Count==1)
	{
		addGoods($uid, $gid1, -1, 0);
	}
	
	if( $good2Count==1 )
	{
		addGoods($uid, $gid2, -1, 0);
	}
	
	if( $is_zuoji == 1)
	{
		if($startlevel<=9)
			addGoods($uid, 214, -1, 0);
		else
			addGoods($uid,11171,-1,0);
	}
	else 
	{
		if($startlevel<=9)
			addGoods($uid, 205, -1, 0);
		else
			addGoods($uid,11170,-1,0);
	}

	if( $is_zuoji == 1) logUserAction($uid,16);
	else  logUserAction($uid,15);
	$armorid=$armor['armorid'];
	$usegoods=$gid1.','.$good1Count.','.$gid2.','.$good2Count;
	$isSuccInt = $is_succ?1:0;
	sql_query("insert into log_armor_strong values ('$uid','$sid','$armorid','$is_zuoji','$startlevel','$endlevel','$usegoods','$isSuccInt',unix_timestamp())");
	$ret[] = $strong_value;
	if($is_succ && $actmsg){
		$ret[] ="，".$actmsg;
	}
	$ret[]=$best_quality;
	
	return $ret; 
}

/*
 * 装备强化过程中赋予极品属性,$rate 给极品属性概率,$type装备的颜色
 */
function getBestQuality($rate,$type)
{
	$rand=mt_rand(1, 100);
	if($rand<=$rate)
	{
		$sumRate=sql_fetch_one_cell("select count(1) from cfg_xilian");
		$rand_id=mt_rand(1, $sumRate);
		$xilian=sql_fetch_one("select * from cfg_xilian where id=$rand_id");
		$propertyArr=explode(",", $xilian['property']);
		//$rand_pro=mt_rand(0, count($propertyArr)-1);//随机算一个属性
		$rand_pro=getProperty($propertyArr);
		$cfg_property=array(0=>"生命",1=>"攻击",2=>"防御",3=>"射程",4=>"速度",5=>"负重");
		//$property=$propertyArr[$rand_pro];
		$cfg_xilian_type=sql_fetch_one("select * from cfg_xilian_type where type=$type");
		$value=mt_rand($cfg_xilian_type['minAdd'], $cfg_xilian_type['maxAdd']); //随机算 一个属性值
		return $rand_id.",".$rand_pro.",".$value.",".$xilian[name].",".$cfg_property[$rand_pro];   //兵种.属性.值
	}
	return null;
}
//根据cfg_xilian表中property字段来获得极品洗练属性
function getProperty($propertyArr)
{
	$length=count($propertyArr);
	$sumRate=0;$pro=0;
	for ($i=1;$i<$length;$i=$i+2)
	{
		$sumRate+=$propertyArr[$i];  //第二个字段是概率，以防配置的不是总100，先求个和
	}
	$randRate=mt_rand(1, $sumRate);
	$sumRate=0;
	for($i=1;$i<$length;$i=$i+2)
	{
		$sumRate+=$propertyArr[$i];
		if ($randRate<=$sumRate) 
		{
			$pro=$propertyArr[$i-1];
			break;
		}
	}
	return $pro;
}
function strongActOnce($uid,$level) {
	@date_default_timezone_set('Asia/Shanghai');
	$startTime=sql_fetch_one_cell("select unix_timestamp('2011-12-15 16:00:00')");//2011-08-27 16:00:00
	$endTime=sql_fetch_one_cell("select unix_timestamp('2011-12-22 16:00:00')");  //2011-08-28 16:00:00
	$now=time();
	if ($now < $startTime || $now >$endTime) return ;
	$level = intval($level);
	switch ($level) {
		case 4:
			sql_query("insert into sys_goods(uid,gid,count) values($uid,112,1) on duplicate key update count=count+1");
			sql_query("insert into sys_goods(uid,gid,count) values($uid,214,1) on duplicate key update count=count+1");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,112,1,unix_timestamp(),4)");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,214,1,unix_timestamp(),4)");
			break;
		case 5:
			sql_query("insert into sys_goods(uid,gid,count) values($uid,213,3) on duplicate key update count=count+3");
			sql_query("insert into sys_goods(uid,gid,count) values($uid,214,1) on duplicate key update count=count+1");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,213,3,unix_timestamp(),4)");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,214,1,unix_timestamp(),4)");
			break;
		case 6:
			sql_query("insert into sys_goods(uid,gid,count) values($uid,212,2) on duplicate key update count=count+2");
			sql_query("insert into sys_goods(uid,gid,count) values($uid,214,1) on duplicate key update count=count+1");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,112,2,unix_timestamp(),4)");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,214,1,unix_timestamp(),4)");
			break;
		case 7:
			sql_query("insert into sys_goods(uid,gid,count) values($uid,10615,1) on duplicate key update count=count+1");
			sql_query("insert into sys_goods(uid,gid,count) values($uid,214,2) on duplicate key update count=count+2");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,10615,1,unix_timestamp(),4)");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,214,2,unix_timestamp(),4)");
			break;
		case 8:
			sql_query("insert into sys_goods(uid,gid,count) values($uid,10616,1) on duplicate key update count=count+1");
			sql_query("insert into sys_goods(uid,gid,count) values($uid,214,2) on duplicate key update count=count+2");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,10616,1,unix_timestamp(),4)");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,214,2,unix_timestamp(),4)");
			break;
		case 9:
			sql_query("insert into sys_goods(uid,gid,count) values($uid,10617,1) on duplicate key update count=count+1");
			sql_query("insert into sys_goods(uid,gid,count) values($uid,214,3) on duplicate key update count=count+3");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,10617,1,unix_timestamp(),4)");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,214,3,unix_timestamp(),4)");
			break;
		case 10:
			sql_query("insert into sys_goods(uid,gid,count) values($uid,10618,1) on duplicate key update count=count+1");
			sql_query("insert into sys_goods(uid,gid,count) values($uid,214,5) on duplicate key update count=count+5");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,10618,1,unix_timestamp(),4)");
			sql_query("insert into log_goods(uid,gid,count,time,type) values($uid,214,5,unix_timestamp(),4)");
			break;
		default:break;
	}
}



function  reCalculateSuccess($armor,$inact,$success){//根据是不是在活动期间重新计算下成功的可能性
	if($inact){
		$stong_times=$armor['strong_times'];
		$level=$armor['strong_level']+1;
		$maxtimes=1000;
		if($level==1||$level==2||$level==3){
			$maxtimes=$level;
		}
		else if($level==4){
			$maxtimes=5;
		}
		else if($level==5){
			$maxtimes=7;
		}
		else if($level==6){
			$maxtimes=14;
		}
		else if($level==7){
			$maxtimes=20;
		}
		else if($level==8){
			$maxtimes=30;
		}
		else if($level==9){
			$maxtimes=40;
		}else if($level==10){
			$maxtimes=50;
		}else if($level==11){
			$maxtimes=5;
		}else if($level==12){
			$maxtimes=10;
		}else if($level==13){
			$maxtimes=15;
		}else if($level==14){
			$maxtimes=30;
		}else if($level==15){
			$maxtimes=50;
		}
		if ($stong_times>=$maxtimes-1){
			return true;
		}else{
			return $success;
		}
	}else{
		return $success;
	}
}
//***********************强化****************************************//

//**************************镶嵌*************************************************
function initHoles($uid, $param)
{
	$cid = intval(array_shift($param));
	$sid = intval(array_shift($param));
	$armor = sql_fetch_one("select * from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$uid' and a.sid=$sid limit 1");
	
	if(empty($armor)) throw new Exception($GLOBALS['equipment']['no_such_armor']);
	$part = intval($armor['part']);
	if(!empty($armor['embed_holes']) && $armor['embed_holes']!="")
	{
		if($part != 12)
			throw new Exception($GLOBALS['equipment']['already_active']);
		else 
			throw new Exception($GLOBALS['equipment']['already_active_horse']);
	}
		
	$holes = "";
	$holes = "";
	$pearls = "0,0,0,0,0";
	
	$res = sql_fetch_one("select * from mem_city_resource where cid=$cid");
	
	$reduce_gold = $armor['value']*500/10;
	if($res['gold'] < $reduce_gold)
		throw new Exception($GLOBALS['equipment']['not_enough_gold']);
	
	if($part!=12){ 
		$rule = sql_fetch_one_cell("select rule from cfg_armor_hole_rule where `type`='$armor[type]'");
		$holes = parseHoleRule($rule);
		//$pearls = "0,0,0,0,0"; //5个位置的珍珠
	}
	else{
		$holes = "0,0,0,0,0";
	}
	
	sql_query("update sys_user_armor set embed_holes='$holes', embed_pearls='$pearls' where sid=$sid");
	sql_query("update mem_city_resource set gold=gold-$reduce_gold where cid=$cid");
	
	$ret = array();	
	$ret[] = $reduce_gold;
	
	if ($part!=12) {
		logUserAction($uid,14);
	}else{
		logUserAction($uid,25);
	}
	completeTaskWithTaskid($uid,103802);//活动任务激活任意一个装备
	return $ret;
	
}

function parseHoleRule($rule)
{
	$ary = explode(",", $rule);
	$ret = "";
	for($i=0; $i<count($ary); $i++)
	{
		if($ary[$i] == "0")
			$ret = $ret."1"; //初级打孔器
		elseif($ary[$i] == "N/A")
			$ret = $ret."3"; //孔不开
		elseif ($ary[$i] == "-1")
			$ret = $ret."2"; //高级打孔器
		elseif ($ary[$i] == "-2")
			$ret = $ret."4"; //特级金刚钻
		else{
			$tmp = getHole(1, intval($ary[$i]));
			$ret = $ret.$tmp;
		}
		if($i < count($ary)-1)
			$ret = $ret.",";
	}
	return $ret;	
}

function getHole($min, $max)
{
	if(isSucc($min, $max))
		return "0"; //开孔
	else 
		return "1"; //需要初级打孔器
}

/*function dismantlePearl($uid,$param)
{
	$sid=array_shift($param);
	$pos=array_shift($param);
	$armor = sql_fetch_one("select * from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$uid' and a.sid=$sid limit 1");
	if(empty($armor))
	{
		throw new Exception($GLOBALS['equipment']['no_such_armor']);
	}
	$pearlArr = explode(",", $armor['embed_pearls']);
	$pearl=$pearlArr[$pos];
	$goods = sql_fetch_one("select * from sys_goods where gid=11178 and uid=$uid");
	$needCount=getDismantleCount($pearl);	//得到拆卸该宝珠需要的拆卸符数量
	if(empty($goods) || $goods['count']<$needCount)
	{
		$have=empty($goods)?0:$goods['count'];
		$left=$needCount-$have;
		throw new Exception("not_enough_goods11178#$left");
	}
	$str = assembleEmbedHoles($pearlArr, $pos, 0);
	sql_query("update sys_user_armor set embed_pearls='$str' where uid=$uid and sid=$sid");
	addGoods($uid,$pearl,1,0);//sql_query("insert into sys_goods values($uid,$pearl,1) on duplicate key update count=count+1 ");
	addGoods($uid, 11178, -$needCount, 0);
	$armor['embed_pearls'] = $str;
	$ret[] = $armor;
	$gids = explode(",", $armor['embed_pearls']);
	$objs = array();
	for($i=0; $i<count($gids); $i++)
	{
		if($gids[$i] ==0)
			array_push($objs, 0);
		else{
			$record = sql_fetch_one("select * from cfg_goods where  gid=$gids[$i] ");
			array_push($objs, $record);
		}
	}
	$ret[] = $objs;
	return $ret;	
} */

function  getDismantleCount($gid)
{
	if($gid>=300 && $gid<=379)
	{
		$needArr=array(1,2,3,4,6,8,10,14,20,60);
		$level=$gid%10+1;
		$needCount=$needArr[$level-1];
	}
	else if($gid>=17500 && $gid<=17539)
	{
		$needArr=array(120,240,500,1000,2000);
		$level=$gid%5+11;
		$needCount=$needArr[$level-11];
	}
	else if($gid>=10830 && $gid<=10839)
	{
		$needArr=array(15,30,60,120,240,500,1000,2000,4000,8000);
		$level=$gid%10+1;
		$needCount=$needArr[$level-1];
	}
	else 
	{
		throw new Exception($GLOBALS['dismantle']['wrong_pearl_gid']);
	}
	return $needCount;
}
function openHole($uid, $param)
{
	$sid = intval(array_shift($param)); //装备id
	$gid = intval(array_shift($param));
	$pos = array_shift($param);
	$goods = sql_fetch_one("select * from sys_goods where gid=$gid and uid=$uid");
	$armor = sql_fetch_one("select * from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$uid' and a.sid=$sid limit 1");
	if(empty($armor))
	{
		throw new Exception($GLOBALS['equipment']['no_such_armor']);
	}
	if($gid==201) //化石粉
	{
		$pearlArr = explode(",", $armor['embed_pearls']);
		$pearl=$pearlArr[$pos];
		$needCount=getDismantleCount($pearl);	//得到拆卸该宝珠需要的化石粉数量
		$useType=array_shift($param);
		$count=array_shift($param);
		if( ($useType==0 && $count!=1) || ($useType==1 && $count!=$needCount) || ($useType!=0 && $useType!=1) )
		{
			throw new Exception($GLOBALS['useGoods']['invalid_data']);
		}
		$needCount=($useType==1)?$needCount:1;
		if(empty($goods) || $goods['count']<$needCount)
		{
			$have=empty($goods)?0:$goods['count'];
			$left=$needCount-$have;
			throw new Exception("not_enough_goods$gid#$left");
		}
		$str = assembleEmbedHoles($pearlArr, $pos, 0);
		sql_query("update sys_user_armor set embed_pearls='$str' where uid=$uid and sid=$sid");
		if($useType==1)
			addGoods($uid,$pearl,1,0);//sql_query("insert into sys_goods values($uid,$pearl,1) on duplicate key update count=count+1 ");
		addGoods($uid, $gid, -$needCount, 0);
		$armor['embed_pearls'] = $str;
		$ret[] = $armor;
	}else if($gid==12156)  //五彩化石粉
	{
		$pearlArr = explode(",", $armor['embed_pearls']);
		$pearl=$pearlArr[$pos];
		$useType=array_shift($param);
		if($useType!=0&&$useType!=1)throw new Exception($GLOBALS['useGoods']['invalid_data']);
		if(empty($goods)||intval($goods['count'])<1)throw new Exception("not_enough_goods$gid#1");
		$str = assembleEmbedHoles($pearlArr, $pos, 0);
		sql_query("update sys_user_armor set embed_pearls='$str' where uid=$uid and sid=$sid");
		if($useType==1){
			addGoods($uid,$pearl,1,0);
		}
		addGoods($uid, $gid, -1, 0);
		$armor['embed_pearls'] = $str;
		$ret[] = $armor;
	}
	else{
	//开孔
		if(empty($goods) || $goods['count']<=0)
			throw new Exception("not_enough_goods$gid");
		$ary = breakUpEmbedHoles( $armor['embed_holes'] );
		
		$ret_str = assembleEmbedHoles($ary, $pos, 0); //0表示开孔
		
		sql_query("update sys_user_armor set embed_holes='$ret_str' where uid=$uid and sid=$sid");
		addGoods($uid, $gid, -1, 0);
		
		$armor['embed_holes'] = $ret_str;
		$ret = array();
		$ret[] = $armor;
		
		//return $ret;
	}
	$gids = explode(",", $armor['embed_pearls']);
	$objs = array();
	for($i=0; $i<count($gids); $i++)
	{
		if($gids[$i] ==0)
			array_push($objs, 0);
		else{
			$record = sql_fetch_one("select * from cfg_goods where  gid=$gids[$i] ");
			array_push($objs, $record);
		}
	}
	$ret[] = $objs;
	$ret[] =$gid;
	return $ret;
} 

/**
 * 获取装备镶嵌的珍珠 描述
 * @param $uid
 * @param $param
 * @return unknown_type
 */
function loadEmbedPearlByArmor($uid, $param)
{
	$gidstr = array_shift($param);
	$ret = array();
	$objs = array();
	$gids = explode(",", $gidstr);
	for($i=0; $i<count($gids); $i++)
	{
		if($gids[$i] ==0)
			array_push($objs, 0);
		else{
			$record = sql_fetch_one("select * from cfg_goods where  gid=$gids[$i] ");
			array_push($objs, $record);
		}
	}
	$ret[] = $objs;
	return $ret;
}

/**
 * 获取用户所有的镶嵌珠宝
 * @param $uid
 * @return unknown_type
 */
function loadEmbedPearl($uid,$param)
{
	$mCurPos = array_shift($param);
	$ret = array();
	if(intval($mCurPos)==4)  //最后一个孔，加载聚魂珠
	{
		$ret[] = sql_fetch_rows("select * from sys_goods g left join cfg_goods f on f.gid=g.gid where g.uid='$uid' and g.`count` > 0 and g.gid between 10830 and 10839");
	}else
	{
		$ret[] = sql_fetch_rows("select * from sys_goods g left join cfg_goods f on f.gid=g.gid where g.uid='$uid' and g.`count` > 0 and ((g.gid>=300 and g.gid<=379) or (g.gid>=17500 && g.gid<=17539) ) and f.group=4 order by f.`group`,f.position");
	}	
	return $ret;
}


function loadEmbedPearl_OpenTab($uid)
{
	//300-379,每10个分别是原先的1到10级宝珠，17500-17539，每五个分别是增加的11-15级宝珠
	$temp=sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where ((a.gid>=300 and a.gid<=379) or a.gid=205 or (a.gid>=17500 && a.gid<=17539)) and `group`=4 order by a.gid");
	$tempRet=array();
	//宝珠排序，先是强化宝珠,然后是1-15统率，内政，勇武，智谋，体力，精力，攻击，防御
	$tempRet=array_merge(array_slice($temp,0,1),array_slice($temp,1,10),array_slice($temp,81,5),
												  array_slice($temp,11,10),array_slice($temp,86,5),
												  array_slice($temp,21,10),array_slice($temp,91,5),
												  array_slice($temp,31,10),array_slice($temp,96,5),
												  array_slice($temp,41,10),array_slice($temp,101,5),
												  array_slice($temp,51,10),array_slice($temp,106,5),
												  array_slice($temp,61,10),array_slice($temp,111,5),
												  array_slice($temp,71,10),array_slice($temp,116,5));						
	$ret = array();
	//$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where ((a.gid>=300 and a.gid<=379) or a.gid=205 or (a.gid>=17500 && a.gid<=17539)) and `group`=4 order by a.gid");
	$ret[]=$tempRet;
	return $ret;
}

/**
 * 获取打孔道具的数量
 * @param $uid
 * @param $param
 * @return unknown_type
 */
function onLoadDaKong($uid, $param)
{
	$val = array_shift($param);
	$gid = 0;

	if($val == 1)
		$gid = 206; //初级打孔器
	if($val ==2)
		$gid = 207; //高级打孔器
	
	$ret = array();
	$ret[] = sql_fetch_one_cell("select `count` from sys_goods where gid=$gid and uid=$uid");
	return $ret;
}

/**
 * 镶嵌
 */
function embedLimit($armor, $goods)
{
	$strong_level = $armor["strong_level"];	
	if($goods['gid']>=300 and $goods['gid']<=379){
		$level = (intval($goods['gid']) - 300) % 10 + 1;
		if($strong_level < $level)
			throw new Exception($GLOBALS['equipment']['embed_limit']);
	}
	if ($goods['gid']>=17500 && $goods['gid']<=17539) 	//新增的11到15级镶嵌宝珠
	{
		$level = intval($goods['gid']) % 5 +11;
		if($strong_level < $level)
			throw new Exception($GLOBALS['equipment']['embed_limit']);
	}
	if($goods['gid']>=10830&&$goods['gid']<=10839)
	{
		$level1 = (intval($goods['gid'])-10800)%10+1;
		if($strong_level < $level1)
			throw new Exception($GLOBALS['equipment']['embed_limit']);
	}
}

function doEmbed($uid, $param)
{
	$sid = intval(array_shift($param));
	$pos = array_shift($param);
	$gid = intval(array_shift($param));
	
	$is_zuoji = array_shift($param);
	
	$armor = sql_fetch_one("select * from sys_user_armor s left join cfg_armor c on s.armorid=c.id where s.sid=$sid and s.uid=$uid");
	
	if(empty($armor)) {
		$ret = array();
		$ret[] = 0;
		$ret[] = $GLOBALS['equipment']['no_such_armor'];
		return $ret;
	}
	if(($armor['part']==12&&$is_zuoji!=1)||$armor['part']!=12&&$is_zuoji==1){
		throw new Exception($GLOBALS['equipment']['data_exception']);
	}
	if(empty($armor["embed_holes"]) || $armor["embed_holes"]==""){
		throw new Exception($GLOBALS['equipment']['no_pos']);
	}else{
		$holesArray=split(",",$armor["embed_holes"]);
		//$pearlsArray=split(",",$armor["embed_pearls"]);
		if($holesArray[$pos]!=0){
			throw new Exception($GLOBALS['equipment']['no_pos']);
		}
	}
	if($armor['hid']!=0)
		throw new Exception($GLOBALS['equipment']['armor_in_hero']);
	
	//战神马装只能给紫色品质的坐骑进行装备
	if($gid>=12178&&$gid<=12182)
	{
		if(intval($armor['part'])!=12) throw new Exception($GLOBALS['equipment']['data_exception']);
		//if(intval($armor['type'])!=5) throw new Exception($GLOBALS['equipment']['can_not_exist']);
		if(intval($armor['type'])<5) throw new Exception($GLOBALS['equipment']['can_not_exist']);
	}
		
	if($is_zuoji == 1){
		if($armor['part']!=12)
			throw new Exception($GLOBALS['equipment']['not_zuoji']);
		if($pos != intval(intval($gid-400)/20)){
			 if($gid>=502 && $gid<=582){
			 	if($pos != intval(intval($gid-400)/20)-5){
			 		throw new Exception($GLOBALS['equipment']['invalid_pos']);
			 	}
			 }else if($gid>=1400 && $gid<=1500)
			 {
				 if($pos != intval(intval($gid-1400)/20)){
			 		throw new Exception($GLOBALS['equipment']['invalid_pos']);
			 	}
			 }else if($gid>=12178 && $gid<=12182)
			 {
			 	if($pos != intval($gid-12178)){
			 		throw new Exception($GLOBALS['equipment']['invalid_pos']);
			 	}
			 }
			 else {
				throw new Exception($GLOBALS['equipment']['invalid_pos']);
			 }
		}
	}
	else{
		if($armor['part']==12)
			throw new Exception($GLOBALS['equipment']['is_zuoji']);
	}
	
	$old_pearls = $armor["embed_pearls"];
	
	$goods = sql_fetch_one("select * from sys_goods where uid=$uid and gid=$gid");
	
	if($is_zuoji == 1){
		$hero_level = $armor['hero_level'];
		if(($goods['gid']>=400 and $goods['gid']<=500) || ($goods['gid']>=502 and $goods['gid']<=582) || ($goods['gid']>=1400 and $goods['gid']<=1500)){
			if( (((intval($goods['gid']) - 400) % 20 + 1) * 10) > $hero_level)
				throw new Exception($GLOBALS['equipment']['zuoji_goods_level0']);
		}else if($goods['gid']>=12178 && $goods['gid']<=12182){
			//if($hero_level<100) throw new Exception($GLOBALS['equipment']['zuoji_goods_level0']);
			//if(intval($armor['type'])!=5) throw new Exception($GLOBALS['equipment']['can_not_exist']);
			if(intval($armor['type'])<5) throw new Exception($GLOBALS['equipment']['can_not_exist']);
		}else{
			throw new Exception($GLOBALS['equipment']['not_zuoji_goods']);
		}
		
		if($gid==409||$gid==429||$gid==449||$gid==469||$gid==489){//冰封马、 龙渊、白虎 装只能给冰封马穿
			if($armor['id']==53016 || $armor['id']==53040 || $armor['id']==53052){
				
			} else
			  throw new Exception($GLOBALS['equipment']['not_zuoji_goods_2']);
		}
		if($gid==410||$gid==430||$gid==450||$gid==470||$gid==490){//赤龙、 龙渊、白虎 专属
			if($armor['id']==12011 || $armor['id']==53040 || $armor['id']==53052){
				
			} else
			  throw new Exception($GLOBALS['equipment']['not_zuoji_goods_2']);
		}
		if($gid==1401||$gid==1421||$gid==1441||$gid==1461||$gid==1481)   //的卢、龙渊、白虎专属
		{
			if($armor['id']==12015 || $armor['id']==53040 || $armor['id']==53052){
				
			} else
			  throw new Exception($GLOBALS['equipment']['not_zuoji_goods_2']);
		}
		if($gid==408||$gid==428||$gid==448||$gid==468||$gid==448)   //龙渊、白虎专属
		{
			if($armor['id']==53040 || $armor['id']==53052){
				
			} else
			  throw new Exception($GLOBALS['equipment']['not_zuoji_goods_2']);
		}
	}
	
	if($is_zuoji != 1){
		embedLimit($armor, $goods);
		//一种类型的珠子只能镶嵌一个
		//checkEmbedGoodLimit($old_pearls,$gid);
	}
	
	if(empty($goods) || $goods['count']<=0 && $armor['part']!=12)
	{
		throw new Exception($GLOBALS['equipment']['no_embed_pearl']);
	}
	if(empty($goods) || $goods['count']<=0 && $armor['part']==12)
	{
		throw new Exception($GLOBALS['equipment']['no_embed_zuoji_armor']);
	}
	
	$ary = explode(",", $old_pearls);
	if ($ary[$pos]!=0) {//这个位置有东西
		throw new Exception($GLOBALS['waigua']['invalid']);
	}
	
	
	$pearls = assembleEmbedHoles($ary, $pos, $gid,$is_zuoji);
	
	$ret = array();
	
	//确实尝试了镶嵌，没有throw exception
	$ret[] = 1;
	
	$ret[] = 0;
	if($pearls != $old_pearls)
	{ 
		sql_query("update sys_user_armor set embed_pearls='$pearls' where sid=$sid and uid=$uid");
		addGoods($uid, $gid, -1, 13);
		$ret[] = $pearls;
		$ret[] = sql_fetch_rows("select * from cfg_goods where gid in ($pearls)");
		if($gid==325){//活动任务，镶嵌一个6级勇武宝珠到任意装备
			completeTaskWithTaskid($uid,103804);
		}
	}

	return $ret;
}

function checkEmbedGoodLimit($embedGidStr,$curGid)
{
	$gidArr = explode(",",$embedGidStr);
	$type=getGoodType($curGid);
	
	for($i=0;$i<count($gidArr);$i++)
	{
		$gidTmp = $gidArr[$i];
		if($type==getGoodType($gidTmp)) throw new Exception("同一件装备不能镶嵌同一类型宝珠");
	}
}
function getGoodType($gid)
{
	$type=-1;   //
	if($gid>=300&&$gid<400)
	{
		$type = ($gid/10)%10;
	}else if($gid>=17500 && $gid<=17539)
	{
		$mod = $gid%100;
		if($mod<5)$type=0;
		else if($mod<10)$type=1;
		else if($mod<15)$type=2;
		else if($mod<20)$type=3;
		else if($mod<25)$type=4;
		else if($mod<30)$type=5;
		else if($mod<35)$type=6;
		else if($mod<40)$type=7;
	}
	return $type;
}

//**************************镶嵌*************************************************//

//****************************合成材料****************************************************
//材料合成
function synthStuff($uid, $param)
{
	$cid = intval(array_shift($param));
	$targe_gid = intval(array_shift($param));
	$count = intval(array_shift($param));
	if($count <=0 )
		throw new Exception($GLOBALS['equipment']['synth_count']);
	$dj = array_shift($param); //是否使用道具0, 1, gid=202巧手服
	$pearlProtect=array_shift($param); //是否使用道具宝珠保护符0，1，gid=11172
	$synth_count = $count; //最终合成个数
	
	if($dj == 1)
	{ //使用道具巧手符
		$goods = sql_fetch_one("select * from sys_goods where uid=$uid and gid=202");
		if(empty($goods) || $goods["count"] < $count)
		throw new Exception("not_enough_goods202");
	}
	if($pearlProtect == 1)
	{ //使用道具宝珠合成符
		$goods = sql_fetch_one("select * from sys_goods where uid=$uid and gid=11172");
		if(empty($goods) || $goods["count"] < $count)
		throw new Exception("not_enough_goods11172");	
	}
	
	$chujiArr=array(300,310,320,330,340,350,360,370);
	if(in_array($targe_gid,$chujiArr)){ //1级宝石不能合成, 300~379
		throw new Exception($GLOBALS['equipment']['linit_level1']);
	}
	
/*	if( $targe_gid==205 || ($targe_gid%10==0) ) //从基础材料到镶嵌宝石或者强化宝石，成功率为100%
	{
		if($dj == 1){ //使用道具
			$goods = sql_fetch_one("select * from sys_goods where uid=$uid and gid=202");
			if(empty($goods) || $goods["count"] <= 0)
				throw new Exception("not_enough_goods202");
			
		}
	}*/
	if($targe_gid!=205)	//合成镶嵌宝石成功率100%，不用计算
	{	
		// 合成技术对目标等级的限制
		$level_limit_by_technic = sql_fetch_one_cell("select level from sys_city_technic where cid='$cid' and tid='23'"); // (cid, tid) is unique
		if($targe_gid>=300 && $targe_gid<=379)		//前10级
		{
			$target_level = $targe_gid % 10 + 1;
			if( empty($level_limit_by_technic) || $target_level > $level_limit_by_technic )
			{
				$msg = sprintf($GLOBALS['equipment']['sync_staff_limit_technic'], $target_level);
				throw new Exception($msg);
			}
		}	
		else if ($targe_gid>=17500 && $targe_gid<=17539) //后五级
		{
			$target_level = $targe_gid % 5 +11;
			if( empty($level_limit_by_technic) || $level_limit_by_technic<10 )	//后五级需要合成技巧10级
			{
				$msg = sprintf($GLOBALS['equipment']['sync_staff_limit_technic'], 10);
				throw new Exception($msg);
			}
		}
		$rate = array(80,70, 60, 50, 40, 30, 20, 10, 10, 10,10,8,5,3,1);	//新增后面五级概率
		$prop=$rate[$target_level-1];
		if(($targe_gid>=300 && $targe_gid<=379) && $dj == 1){ //使用道具,前10级巧手符功能不做改变，将错就错，送福利
			$level = $targe_gid%10 + 1; //目标等级 
			$limit = 19683/pow(3, $level-1);//9-$targe_gid%10
			if($count <= $limit)
				$prop = 100;
			else{
				$synth_count = $limit + calculateProbability($count-$limit, $prop);
			}
		}
		else	//没有使用巧手符，或者用了巧手符且合成11到15级宝珠
		{
			$rate_add=$dj*10;//正常做法，巧手符增加10%成功率
			$prop=$prop*(1+$rate_add/100);
			$synth_count = calculateProbability($count,$prop);
		}
	}
	
	$recipe = sql_fetch_one_cell("select recipe from cfg_recipe where gid=$targe_gid");
	$tmp_ary = explode(",", $recipe);
	$need_gid = "";
	for($i=0; $i<count($tmp_ary); $i+=2)
	{
		if($i+1 == count($tmp_ary)-1)
			$need_gid = $need_gid."$tmp_ary[$i]";
		else
			$need_gid = $need_gid."$tmp_ary[$i], ";
	}	
	$dict = parseStuffRecipe($recipe);
	$rows = sql_fetch_rows("select * from sys_goods where uid=$uid and gid in ($need_gid)");	
	if(empty($rows) || count($rows)!=count($dict) ){
		throw new Exception($GLOBALS['equipment']['no_stuff']);
	}
	foreach ($rows as $stuff)
	{
		if($stuff['count'] < intval($dict["$stuff[gid]"])*$count )
		{
			throw new Exception($GLOBALS['equipment']['no_stuff']);
		}
	}	
	//合成材料
	addGoods($uid, $targe_gid, $synth_count, 99);
//		sql_query("insert into sys_goods(`uid`, `gid`, `count`) values($uid, $targe_gid, $synth_count) on duplicate key update `count`=`count`+$synth_count");
	
	if($pearlProtect==1)		//使用了保护符，合成失败的次数不扣除材料,即只扣除成功的
	{
		foreach ($rows as $stuff)
		{
			$needPear = intval($dict["$stuff[gid]"])*$synth_count;
			addGoods($uid, $stuff['gid'], 0 - $needPear, 99);
		}
	}
	else 
	{
		foreach ($rows as $stuff)
		{
			$needPear = intval($dict["$stuff[gid]"])*$count;
			addGoods($uid, $stuff['gid'], 0 - $needPear, 99);
		}
	}
	
	
	if($dj == 1)
	{ //使用道具
		//sql_query("update sys_goods set `count`=`count`-1 where uid=$uid and gid=202");
		addGoods($uid, 202, 0 - $count, 0);
	}
	if($pearlProtect==1)
	{
		addGoods($uid,11172,0 - $count ,0);
	}	
		$ret = array();
		$ret[] = $synth_count;
		$ret[] = $need_gid;
		$ret[] = $needPear;
		logUserAction($uid,23);//材料合成日志
		return $ret;	
}

function calculateProbability($count, $prop)
{
	$ret = 0;
	for($i = 0; $i < $count; $i++)
	{
		if(mt_rand(1, 10000) <= $prop*100 )
		{
			$ret ++;
		}
	}
	return $ret;
}
		
function loadStuff($uid, $param)
{
	$gid = intval(array_shift($param));
	$ret = array();
	$ret[] = $gid;
	$chujiArr=array(205,300,310,320,330,340,350,360,370);
	if (in_array($gid,$chujiArr)) //强化宝珠, 初级镶嵌宝珠
	{
		$recipe = sql_fetch_one_cell("select recipe from cfg_recipe where gid=$gid");
		
		$tmp_ary = explode(",", $recipe);
		$need_gid = "";
		
		for($i=0; $i<count($tmp_ary); $i+=2)
		{
			if($i+1 == count($tmp_ary)-1)
				$need_gid = $need_gid."$tmp_ary[$i]";
			else
				$need_gid = $need_gid."$tmp_ary[$i], ";
		}
		$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in ($need_gid) order by a.gid");
		//$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in (209, 210, 211) order by a.gid");
		$ret[] = $recipe;
	}
	/*
	else if($gid==300) //1统率,  32 | 琉璃,  34 | 玛瑙  ,   38 | 夜明珠 
	{
		$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in (32, 34, 38) order by a.gid");
	}
	else if($gid==310) //1级内政宝珠  30 | 珍珠	35 |水晶		36|翡翠
	{
		$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in (30, 35, 36) order by a.gid");
	}
	else if($gid==320) //1级勇武镶嵌宝石 	 31 | 珊瑚 	33|琥珀		37|玉石
	{
		$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in (31, 33, 37) order by a.gid");
	}
	else if($gid==330) //1级智谋镶嵌宝石   32 | 琉璃	34| 玛瑙	  38 |夜明珠
	{
		$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in (32, 34, 38) order by a.gid");
	}
	else if($gid==340) //1级体力镶嵌宝石  珊瑚	1	水晶	1	翡翠
	{
		$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in (31, 35, 36) order by a.gid");
	}
	else if($gid==350)//1级精力镶嵌宝石 珍珠	1	琥珀	1	玉石
	{
		$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in (30, 33, 37) order by a.gid");
	}
	else if($gid==360) //1级攻击镶嵌宝石 珊瑚	1	玛瑙	1	玉石	1
	{
		$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in (31, 34, 37)  order by a.gid");
	}
	else if($gid==370) // 琥珀	1	玛瑙	1	水晶
	{
		$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in (33, 34, 35) order by a.gid");
	}*/
	else
	{	//11级宝珠和10级宝珠的gid不是减一的关系
		$elevenArr=array(17500=>309,17505=>319,17510=>329,17515=>339,17520=>349,17525=>359,17530=>369,17535=>379);
		if(array_key_exists($gid,$elevenArr))
		{
			$tmp=$elevenArr[$gid];
			$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in ($tmp) and `group`=4 order by a.gid");
		}
		else 
		{
			$tmp = $gid - 1;
			$ret[] = sql_fetch_rows("select *,(select `count`  from sys_goods where uid=$uid and gid = a.gid) as `count`  from cfg_goods  a where a.gid in ($tmp) and `group`=4 order by a.gid");
		}
		$ret[] = "$tmp,3"; //配方
	}
	return $ret; 
}

//*****************************合成材料*************************************************//

//------------------------------------基础函数
function isSucc($min, $max)
{
	return probability($min, $max);
}
/**
 * 
 * @param $value 100为基数
 * @return unknown_type
 */
function probability($min, $max)
{
	$ret = rand(1, 100);
	return ($min<=$ret && $ret<=$max);
}

function fitPValue($pvalue, $min, $max)
{
	return ($min<=$pvalue && $pvalue<=$max);
}

function pValue()
{
	return rand(1, 100);
}

function makeExp()
{
	$numargs = func_num_args();
	$arg_list = func_get_args();
	$ret = "";
	for ($i = 0; $i < $numargs; $i++) {
		if ($i!=$numargs-1)
        	$ret = $ret."$arg_list[$i],";
        else
        	$ret = $ret."$arg_list[$i]";
    }
    return $ret;
	
}

/**
 * 分解打孔
 * @param $holes
 * @return unknown_type
 */
function breakUpEmbedHoles($holes)
{
	$ary = explode(",", $holes);
	return $ary;
}
/**
 * 组装打孔
 * @param $ary
 * @param $rep_pos
 * @param $value
 * @return unknown_type
 */
function assembleEmbedHoles($ary, $rep_pos, $value,$is_zuoji=0)
{
	$ary[$rep_pos] = $value;
	$ret = "";

	if(intval($is_zuoji)!=1&&intval($value)!=0)   //装备镶嵌宝珠的时候最后一个位置只能镶嵌聚魂珠
	{
		if(intval($rep_pos)==4)  
		{
			if(intval($value)<10830||intval($value)>10839)
			{
				throw new Exception($GLOBALS['goods']['good_position_error']);
			}
		}else
		{
			if(intval($value)>=10830&&intval($value)<=10839)
			{
				throw new Exception($GLOBALS['goods']['good_position_error']);
			}
		}
	}
		
	for($i=0;$i<count($ary); $i++)
	{
		if($ary[$i] == "") $ary[$i] = 0;
		if($i==count($ary)-1)
			$ret = $ret."$ary[$i]";
		else
			$ret = $ret."$ary[$i],";
	}
	return $ret;
}

function parseStuffRecipe($recipe)
{
	$ary = explode(",", $recipe);
	$dict = array();
	for($i=0; $i<count($ary); $i+=2)
	{
		$dict["$ary[$i]"] = $ary[$i+1];
	}
	return $dict;
}

function combineArmor($uid,$param) {
	if (count($param) < 5) throw new Exception($GLOBALS['useMojiaGoods']['invalid_param']);
	$mainSid = intval(array_shift($param));
	$mainFlag = array_shift($param);
	if ($mainFlag != 1) throw new Exception($GLOBALS['useMojiaGoods']['invalid_param']);
	$subSid1 = intval(array_shift($param));
	$subSid2 = intval(array_shift($param));
	$goodsFlag = array_shift($param);
	if (empty($subSid1) || empty($subSid2)) throw new Exception($GLOBALS['waigua']['invalid']);
	if ($goodsFlag>1 || $goodsFlag <0) throw new Exception($GLOBALS['waigua']['invalid']);
	if ($mainSid == $subSid1 || $mainSid==$subSid2 || $subSid1==$subSid2) throw new Exception($GLOBALS['waigua']['invalid']);
	
	$mainArmorId = sql_fetch_one_cell("select armorid from sys_user_armor where sid=$mainSid and uid=$uid");
	if (empty($mainArmorId)) throw new Exception($GLOBALS['waigua']['invalid']);
	$armorPart = sql_fetch_one_cell("select a.part from cfg_armor a,sys_user_armor b where a.id=b.armorid and b.sid=$mainSid");
	if ($armorPart ==  12) throw new Exception($GLOBALS['combine']['zuoji']);
	$subArmor1 = sql_fetch_one("select * from sys_user_armor where uid='$uid' and sid='$subSid1' and armorid='$mainArmorId'");
	$subArmor2 = sql_fetch_one("select * from sys_user_armor where uid='$uid' and sid='$subSid2' and armorid='$mainArmorId'");
	if (empty($subArmor1) || empty($subArmor2)) throw new Exception($GLOBALS['waigua']['invalid']);
	if ($mainArmorId != $subArmor1['armorid'] || $mainArmorId != $subArmor2['armorid']) throw new Exception($GLOBALS['waigua']['invalid']);
	
	if (sql_check("select * from sys_hero_armor where sid in ('$subSid1','$subSid2')")) throw new Exception($GLOBALS['equipment']['armor_in_hero']); 

	//判断下是否在可以熔炼的范围内
	$tieIds = array(10001,10002,10003,10004,11002,12004,12005,12006,12007,15000,15001,15002,15003,15004);
	$tieid = sql_fetch_one_cell("select tieid from cfg_armor where id=$mainArmorId");
//	if (!in_array($tieid,$tieIds))
	//	throw new Exception($GLOBALS['combine']['not_be_combine']); 
	
	//检查级别
	$combineLevel = sql_fetch_one_cell("select combine_level from sys_user_armor where sid=$mainSid");
	$subLevel1 = sql_fetch_one_cell("select combine_level from sys_user_armor where sid=$subSid1");
	$subLevel2 = sql_fetch_one_cell("select combine_level from sys_user_armor where sid=$subSid2");
	if ($combineLevel != $subLevel1 || $combineLevel != $subLevel2) {
		throw  new Exception($GLOBALS['combine']['level_not_eq']); 
	}
	if ($combineLevel >=7) throw  new Exception($GLOBALS['useGoods']['hero_card_6']); 
	//如果用保护符，看看有没有，默认使用保护符
	$usegoods =-11; $success=0;$mustGoods=-11;
	if ($combineLevel >=3) {
		$mustGoods = 10777;
	} else {
		$mustGoods = 10776;
	}
	$count = sql_fetch_one_cell("select count from sys_goods where uid=$uid and gid=$mustGoods");
	if (empty($count)) {
		if(intval($mustGoods)==10776)
		{
			throw new Exception($GLOBALS['equipment']['not_enough_fusionGood1']);
		}elseif (intval($mustGoods)==10777) 
		{
			throw new Exception($GLOBALS['equipment']['not_enough_fusionGood2']);
		}		
	}
	
	if ($goodsFlag == 1) {
		$usegoods = 10778;
		$count = sql_fetch_one_cell("select count from sys_goods where uid=$uid and gid=$usegoods");
		if (empty($count)) {
			//throw new Exception($GLOBALS['equipment']['no_goods']);
			$msg = "not_enough_goods$usegoods";
			throw new Exception($msg);
		}
	}
	//计算概率
	$rateArr = array(1=>75,2=>35,3=>15,4=>10,5=>5,6=>3,7=>1,);
	$rate = $rateArr[$combineLevel+1];
	$tmpRate = rand(1,10000);
	if ($tmpRate < $rate*100) {
		$success = 1;
	} else {
		$success = 0;
	}
	if ($success == 1) 
		sql_query("update sys_user_armor set combine_level=least(7,combine_level+1) where sid=$mainSid");
	//清除合并后的装备
	if ($success == 1) {
		//做备份
		$sql = "insert into log_armor_combine (sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,active_special,strong_times,combine_level,usegoods,success,mainsid,time)".
		" select sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,active_special,strong_times,combine_level,'$usegoods','$success','$mainSid',unix_timestamp() from sys_user_armor where sid=$subSid1";
		@sql_query($sql);
		
		$sql = "insert into log_armor_combine (sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,active_special,strong_times,combine_level,usegoods,success,mainsid,time)".
		" select sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,active_special,strong_times,combine_level,'$usegoods','$success','$mainSid',unix_timestamp() from sys_user_armor where sid=$subSid2";
		@sql_query($sql);
				
		sql_query("delete from sys_user_tie_deify_attribute where sid in ('$subSid1','$subSid2')");
		sql_query("delete from sys_user_armor where sid in ('$subSid1','$subSid2')");
	}
	if($success == 0 && $goodsFlag == 0)
	{
		$sql = "insert into log_armor_combine (sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,active_special,strong_times,combine_level,usegoods,success,mainsid,time)".
		" select sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,active_special,strong_times,combine_level,'$usegoods','$success','$mainSid',unix_timestamp() from sys_user_armor where sid=$mainSid";
		@sql_query($sql);
		
		$sql = "insert into log_armor_combine (sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,active_special,strong_times,combine_level,usegoods,success,mainsid,time)".
		" select sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,active_special,strong_times,combine_level,'$usegoods','$success','$mainSid',unix_timestamp() from sys_user_armor where sid=$subSid1";
		@sql_query($sql);
		
		$sql = "insert into log_armor_combine (sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,active_special,strong_times,combine_level,usegoods,success,mainsid,time)".
		" select sid,uid,armorid,hp,hp_max,hid,strong_level,strong_value,embed_pearls,embed_holes,deified,active_special,strong_times,combine_level,'$usegoods','$success','$mainSid',unix_timestamp() from sys_user_armor where sid=$subSid2";
		@sql_query($sql);
				
		sql_query("delete from sys_user_tie_deify_attribute where sid in ('$subSid1','$subSid2','$mainSid')");
		sql_query("delete from sys_user_armor where sid in ('$subSid1','$subSid2','$mainSid')");
	}
	if ($success == 1 && $combineLevel >=3) {
		$userName = sql_fetch_one_cell("select name from sys_user where uid=$uid");
		$armorName = sql_fetch_one_cell("select name from cfg_armor where id=$mainArmorId");
		$levelName = $GLOBALS['combine']['armor_level'][$combineLevel+1];
		$armorName = $levelName.$armorName;
		$msg = sprintf($GLOBALS['combine']['upgrade_armor'],$userName,$armorName);
		sendSysInform(0,1,0,60,0,1,49151,$msg);
	}
	$time = sql_fetch_one_cell("select unix_timestamp()");
	if ($success==1 && $time >1384416000 && $time <1385020800) {//这里是8月5期活动
		switch ($combineLevel+1) {
			case 1: $tgid=12066;$tcount=1;break;
			case 2: $tgid=12065;$tcount=1;break;
			case 3: $tgid=50278;$tcount=3;break;
			case 4: $tgid=50276;$tcount=3;break;
			case 5: $tgid=12072;$tcount=10;break;
			case 6: $tgid=41910;$tcount=1;break;
			case 7: $tgid=12036;$tcount=1;break;
			default:$tgid=10616;$tcount=0;break;
		}
		if ($tgid != 12012) {
			$tname=sql_fetch_one_cell("select name from cfg_goods where gid=$tgid");
			if ($tgid !=0) {
				addGoods($uid,$tgid,$tcount,5);
			} else {
				addGift($uid,$tcount,5);
			}
		} else {
			$tname = sql_fetch_one_cell("select name from cfg_armor where id=$tgid");
			addArmor($uid,$tgid,$tcount,5);
		}
		$userName = sql_fetch_one_cell("select name from sys_user where uid=$uid");
		$msg = sprintf($GLOBALS['combine']['award'],$userName,$tname,$tcount);
		sendSysInform(0,1,0,60,0,1,49151,$msg);
	}
	addGoods($uid,$mustGoods,-1,0);
	addGoods($uid,$usegoods,-1,0);
	
	$ret = array();
	$fusionPro = sql_fetch_one("select c.*,g.count from cfg_goods c left join sys_goods g on g.gid=c.gid and g.uid=$uid where c.gid=10778");		
	if($success == 1)
	{
		$newArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and b.sid='$mainSid'");
		$ret[]= $success;
		$ret[] = $fusionPro;
		$ret[] = addSpecialArr($newArmor);
		$ret[] = getArmorNewAttribute($newArmor);
	    $ret[] = getArmorEmbedGoods($newArmor);
	    $ret[] = getTieInfo($newArmor, -1);
		$ret[] = getTieArmorAttribute($newArmor);
		$ret[] = getDeifyAttribute($newArmor);
		$ret[] = getFusionAttribute($newArmor);
	}else
	{
		$ret[]= $success;
		$ret[] = $fusionPro;
		$ret[] = $goodsFlag;
	}
	return $ret;
}


function loadFusionableArmor($uid,$param)
{
	$index = array_shift($param);
	//q4版本优化--新增热血金枪，末日之刃，永恒之恋熔炼
	$fusionArmor = sql_fetch_rows("select a.*,b.combine_level,count(b.armorid) as `count` from cfg_armor a,sys_user_armor b where a.id=b.armorid and b.uid='$uid' and a.part<>'12' and (a.tieid in(10001,10003,10004,10005,10007,10008,11002,12003,12004,12005,12006,12007,15000,15001,15002,15003,15004) or a.id in(52001,12009,12010,12012,12013,12016,12018)) group by a.id,b.combine_level");
	$ret = array();
	$ret[] = $index;
	$ret[] = $fusionArmor;
	return $ret;
}
function loadPartArmor($uid,$param)
{
	$part = intval(array_shift($param));
	$partTie = array_shift($param);
	
	switch (strval($partTie))
	{
		case "0" :
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and (a.tieid in(10001,10003,10004,10005,10007,10008,11002,12003,12004,12005,12006,12007,15000,15001,15002,15003,15004) or a.id in(52001,12009,12010,12012,12013,12016,12018)) order by b.combine_level desc");
			break;
		case "1" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='12003' order by b.combine_level desc");
			break;
		case "2" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='11002' order by b.combine_level desc");
			break;
		case "3" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='10008' order by b.combine_level desc");
			break;
		case "4" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='10004' order by b.combine_level desc");
			break;
		case "5" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='10003' order by b.combine_level desc");
			break;
		case "6" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='10001' order by b.combine_level desc");
			break;
		case "7" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='10005' order by b.combine_level desc");
			break;
		case "8" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='10007' order by b.combine_level desc");
			break;
		case "9" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='12004' order by b.combine_level desc");
			break;
		case "10" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.id in(52001,12009,12010,12012,12013,12016,12018) order by b.combine_level desc");
			break;
		case "11" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='15000' order by b.combine_level desc");
			break;
		case "12" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='15001' order by b.combine_level desc");
			break;
		case "13" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='15002' order by b.combine_level desc");
			break;
		case "14" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='15003' order by b.combine_level desc");
			break;
		case "15" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='15004' order by b.combine_level desc");
			break;
		case "16" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='12006' order by b.combine_level desc");
			break;
		case "17" :	
			$partArmor = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and a.tieid='12007' order by b.combine_level desc");
			break;
		default:break;
	}
	
	$ret = array();
	$ret[] = $part;
	$ret[] = $partTie;
	$ret[] = addSpecialArr($partArmor);
    $ret[] = getArmorNewAttribute($partArmor);
    $ret[] = getArmorEmbedGoods($partArmor);
    $ret[] = getTieInfo($partArmor, -1);
	$ret[] = getTieArmorAttribute($partArmor);
	$ret[] = getDeifyAttribute($partArmor);
	$ret[] = getFusionAttribute($partArmor);

	return $ret;
}

function reloadArmor($uid,$param)
{
	$sidArr = array_shift($param);
	$part = intval(array_shift($param));
	$partTie = array_shift($param);
	
	$sid1 = intval($sidArr[0]);
	$sid2 = intval($sidArr[1]);
	$sid3 = intval($sidArr[2]);
	
	switch (strval($partTie))
	{
		case "0" :
			$Armors = sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and (a.tieid in(10001,10003,10004,10005,10007,10008,11002,12003,12004,12005,12006,12007,15000,15001,15002) or a.id in(52001,12009,12010,12012,12013,12016,12018)) order by b.combine_level desc");
			break;
		case "1" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='12003' order by b.combine_level desc");
			break;
		case "2" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='11002' order by b.combine_level desc");
			break;
		case "3" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='10008' order by b.combine_level desc");
			break;
		case "4" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='10004' order by b.combine_level desc");
			break;
		case "5" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='10003' order by b.combine_level desc");
			break;
		case "6" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='10001' order by b.combine_level desc");
			break;
		case "7" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='10005' order by b.combine_level desc");
			break;
		case "8" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='10007' order by b.combine_level desc");
			break;
		case "9" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='12004' order by b.combine_level desc");
			break;
		case "10" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.id in(52001,12009,12010,12012,12013,12016,12018) order by b.combine_level desc");
			break;
		case "11" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='15000' order by b.combine_level desc");
			break;
		case "12" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='15001' order by b.combine_level desc");
			break;
		case "13" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='15002' order by b.combine_level desc");
			break;
		case "14" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='15003' order by b.combine_level desc");
			break;
		case "15" :	
			$Armors =sql_fetch_rows("select * from cfg_armor a left join sys_user_armor b on a.id=b.armorid where b.uid='$uid' and a.part<>'12' and a.part='$part' and b.hid='0' and b.sid not in('$sid1','$sid2','$sid3') and a.tieid='15004' order by b.combine_level desc");
			break;
		default:break;
	}
	$ret = array();
	$ret[] = $part;
	$ret[] = $partTie;
	$ret[] = addSpecialArr($Armors);
	$ret[] = getArmorNewAttribute($Armors);
    $ret[] = getArmorEmbedGoods($Armors);
    $ret[] = getTieInfo($Armors, -1);
	$ret[] = getTieArmorAttribute($Armors);
	$ret[] = getDeifyAttribute($Armors);
	$ret[] = getFusionAttribute($Armors);
	return $ret;
}
/*
 * 洗练,加载道具
 */
function loadXilianGoods($uid,$param)
{
	//0:祝融之符 12079,1:极品属性清洗符12080,2:极品属性洗练符12081
	$xilianIndex=intval(array_shift($param));
	if($xilianIndex>2 || $xilianIndex<0)
		throw new Exception($GLOBALS['xilian']['param_error']);
	$xilianGidArr=array(12079,12080,12081);
	$gid=$xilianGidArr[$xilianIndex];
	$ret=array();
	$goodsInfo = sql_fetch_one("select * from cfg_goods where gid='$gid'");
	$count=sql_fetch_one_cell("select `count` from sys_goods where gid='$gid' and uid='$uid'");
	if(empty($count)) $count=0;
	$goodsInfo['count']=$count;
	$ret[]=$goodsInfo;
	
	return $ret;
}
/*
 * 进行洗练
 */
function doXilian($uid,$param)
{
	if(count($param)!=3)
	{
		throw new Exception($GLOBALS['xilian']['param_error']);
	}
	$xilianIndex=intval(array_shift($param));
	$gid=intval(array_shift($param));
	$sid=intval(array_shift($param));
	//验证开始
	if($xilianIndex>2 || $xilianIndex<0)
		throw new Exception($GLOBALS['xilian']['param_error']);
	if( ($xilianIndex==0 && $gid!=12079) || ($xilianIndex==1 && $gid!=12080) || ($xilianIndex==2 && $gid!=12081))
		throw new Exception($GLOBALS['xilian']['param_error']);
	$armor = sql_fetch_one("select * from sys_user_armor s left join cfg_armor c on s.armorid=c.id where s.sid=$sid and s.uid=$uid");
	if(empty($armor))
	{
		throw new Exception($GLOBALS['equipment']['no_such_armor']);
	}
	if($xilianIndex==0)  //祝融之符
	{
		$best_quality=$armor['best_quality'];
		if(!empty($best_quality))
			throw new Exception($GLOBALS['xilian']['xilian_exist']);
		//if(!checkGoodsCount($uid, $gid, 1))
		$goodsCount=sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid' ");
		if(empty($goodsCount)) $goodsCount=0;
		if($goodsCount<1)
		{
			throw  new Exception($GLOBALS['xilian']['lack_xilian']);
		}
		$best_quality=getBestQuality(100,$armor['type']);  //第一个参数是出极品属性的概率
		if(!empty($best_quality))
		{
			sql_query("update sys_user_armor set best_quality='$best_quality' where uid='$uid' and sid='$sid' ");
		}
		addGoods($uid, $gid, -1, 0);
		$ret=array($best_quality,1);
		return $ret;	
	}
	else if($xilianIndex==1) //极品属性清洗符
	{
		$best_quality=$armor['best_quality'];
		if(empty($best_quality))
			throw new Exception($GLOBALS['xilian']['xilian_not_exist']);
		//if(!checkGoodsCount($uid, $gid, 1))
		$goodsCount=sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid' ");
		if(empty($goodsCount)) $goodsCount=0;
		if($goodsCount<1)
		{
			throw  new Exception("not_enough_goods".$gid);
		}
		sql_query("update sys_user_armor set best_quality=null where uid='$uid' and sid='$sid'");
		addGoods($uid, $gid, -1, 0);
		$ret=array("",1);
		return $ret;
	}
	else if($xilianIndex==2)
	{
		$best_quality=$armor['best_quality'];
		if(empty($best_quality))
			throw new Exception($GLOBALS['xilian']['xilian_not_exist']);
		$type=$armor['type'];
		$count=0;
		if($type<=3 && $type>=1)
		{
			$count=1;
		}
		else if($type==4)
		{
			$count=2;
		}
		else if($type==5)
		{
			$count=3;
		}
		else if($type==6)
		{
			$count=4;
		}
		else 
		{
			throw new Exception($GLOBALS['xilian']['xilian_not_open']);
		}
		$goodsCount=sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid' ");
		if(empty($goodsCount)) $goodsCount=0;
		if($goodsCount<$count)
		{
			$left=$count-$goodsCount;
			throw new Exception("not_enough_goods".$gid."#".$left);
		}
		$best_quality=getBestQuality(100,$armor['type']);  //第一个参数是出极品属性的概率
		if(!empty($best_quality))
		{
			sql_query("update sys_user_armor set best_quality='$best_quality' where uid='$uid' and sid='$sid' ");
		}
		addGoods($uid, $gid, -$count, 0);
		$ret=array($best_quality,$count);
		return $ret;
	}
}


?>
