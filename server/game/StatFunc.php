<?php
//幕僚的统计功能
require_once("./interface.php");
require_once("./utils.php");


function getStats($uid,$param){
	$type=array_shift($param);
	switch($type){
		case 0: return getCityStats($uid) ;
		case 1: return getResourceStats($uid) ;
		case 2: return getProductStats($uid) ;
		case 3: return getHeroStats($uid) ;
		case 4: return getArmyStats($uid) ;
		case 5: return getDefenceStats($uid) ;
		default : return "error";
	}
}

function getCityStats($uid){

	$citys=sql_fetch_rows("select c.cid,c.name,r.people,r.morale,r.complaint,r.tax  from sys_city c left join mem_city_resource r on c.cid=r.cid where uid='$uid'");



	$condition ="in(";
	$cityCount=count($citys);
	$index=0;

	for(;$index<$cityCount;$index++){
		$condition.=$citys[$index]['cid'];
		if($index<$cityCount-1)
		$condition.=',';
		$allCitys[$citys[$index]['cid']]=$citys[$index];
	}
	$condition.=")";

	$buildingCount=sql_fetch_rows("select cid,count(*) from sys_building where cid ".$condition." and state>0 group by cid");

	foreach($buildingCount as $count){
		$allCitys[$count['cid']]['buildingCount']=$count['count(*)'];
	}

	$tecCount=sql_fetch_rows("select cid,count(*) from mem_technic_upgrading where cid ".$condition." group by cid");
	foreach($tecCount as $count){
		$allCitys[$count['cid']]['tecCount']=$count['count(*)'];
	}
	$draftCount=sql_fetch_rows("select cid,count(*) from mem_city_draft where cid ".$condition."  group by cid");

	foreach($draftCount as $count){
		$allCitys[$count['cid']]['draftCount']=$count['count(*)'];
	}
	$defenceCount=sql_fetch_rows("select cid,count(*) from mem_city_reinforce where cid ".$condition." group by cid");

	foreach($defenceCount as $count){
		$allCitys[$count['cid']]['defenceCount']=$count['count(*)'];
	}

	$ret[] = array();
	$ret[] = 0;
	foreach($allCitys as $city){
		$city['cid']=getPos($city['cid']);
		$ret[]=$city;
	}
	return $ret;
}


function getResourceStats($uid){
	$citys=sql_fetch_rows(" select r.cid,c.name,floor(r.gold) as gold,floor(r.food) as food,floor(r.wood) as wood,floor(r.rock) as rock,floor(r.iron) as iron from sys_city c left join  mem_city_resource r on r.cid=c.cid where c.uid='$uid'");
	$ret[] =array();
	$ret[] =1;
	foreach($citys as $city){
		$city['cid']=getPos($city['cid']);
		$ret[]=$city;
	}
	return $ret;
}



function getProductStats($uid){
	#$citys=sql_fetch_rows(" select r.cid,c.name,floor((r.people*r.gold_rate)/100) as gold_add,floor(r.food_add-r.food_army_use) as food_add,floor(r.wood_add) as wood_add,floor(r.rock_add) as rock_add,floor(r.iron_add) as iron_add from sys_city c left join  mem_city_resource r on r.cid=c.cid where c.uid='$uid'");
	$citys=sql_fetch_rows(" select r.cid,c.name,floor( r.people * r.tax* 0.01* r.gold_rate*0.01 * 1 ) as gold_add,r.hero_fee  as hero_fee,floor(r.food_add-r.food_army_use) as food_add,floor(r.wood_add) as wood_add,floor(r.rock_add) as rock_add,floor(r.iron_add) as iron_add from sys_city c left join  mem_city_resource r on r.cid=c.cid where c.uid='$uid'");
//	$citys=sql_fetch_rows(" select r.cid,c.name,floor( r.people * r.tax* 0.01* r.gold_rate*0.01 * 1 - r.hero_fee ) as gold_add,floor(r.food_add-r.food_army_use) as food_add,floor(r.wood_add) as wood_add,floor(r.rock_add) as rock_add,floor(r.iron_add) as iron_add from sys_city c left join  mem_city_resource r on r.cid=c.cid where c.uid='$uid'");
	$ret[] =array();
	$ret[] =2;
	foreach($citys as $city){
		$clevel=sql_fetch_one_cell("select level from  sys_city_technic where cid=$city[cid] and tid=25");
		if(!empty($clevel)){
			$city['gold_add']=$city['gold_add']*($clevel*0.05+1);
		}
		$city['gold_add']=$city['gold_add']-$city['hero_fee'];
		$city['gold_add']=floor($city['gold_add']);
		$city['cid']=getPos($city['cid']);
		$ret[]=$city;
	}
	return $ret;
}

function getHeroStats($uid){
		$citys=sql_fetch_rows("select h.cid,c.name,h.name as hero,h.level,(h.command_base+h.level+h.command_add_on) as command,(h.affairs_base+h.affairs_add+h.affairs_add_on) as affairs,(h.bravery_base+h.bravery_add+h.bravery_add_on) as bravery,(h.wisdom_base+h.wisdom_add+h.wisdom_add_on) as wisdom,h.loyalty,h.state,h.herotype from sys_city_hero h left join  sys_city c on (c.cid=h.cid and c.uid=h.uid) where h.uid='$uid' order by h.cid");
	$ret[] =array();
	$ret[] =3;
	foreach($citys as $city){
		$city['cid']=getPos($city['cid']);
		if($city[state]==1)
			$city['state']=$GLOBALS['heroState']['1'];
		else if($city[state]==2)
			$city['state']=$GLOBALS['heroState']['2'];
		else if($city[state]==3)
			$city['state']=$GLOBALS['heroState']['3'];
		else if($city[state]==4)
			$city['state']=$GLOBALS['heroState']['4'];
		else if($city[state]==5)
			$city['state']=$GLOBALS['heroState']['5'];
		else if($city[state]==6)
			$city['state']=$GLOBALS['heroState']['6'];
		else if($city[state]==7)
			$city['state']=$GLOBALS['heroState']['7'];
		else if($city[state]==8)
			$city['state']=$GLOBALS['heroState']['8'];
		else if($city[state]==9)
			$city['state']=$GLOBALS['heroState']['9'];
		else if($city[state]==10)
			$city['state']=$GLOBALS['heroState']['10'];
		else if($city[state]==11)
			$city['state']=$GLOBALS['heroState']['11'];
		else
			$city['state']=$GLOBALS['heroState']['0'];
		$ret[]=$city;
	}
	return $ret;
}

function getArmyStats($uid){
	$citys=sql_fetch_rows(" select c.name,c.cid,group_concat(s.sid),group_concat(s.count) from sys_city c left join  sys_city_soldier s on c.cid=s.cid where c.uid='$uid' group by cid");
	$ret[] =array();
	$ret[] =4;
	$soldierName=array("","minfu","yibing","chihou","changqiang","daodun","gongjian","qingqi","tieqi","zizhong","chuangnu","chongche","toushi");
	$soldierName[45]="bing45";
	$soldierName[46]="bing46";
	$soldierName[47]="bing47";
	$soldierName[48]="bing48";
	$soldierName[49]="bing49";
	$soldierName[50]="bing50";
	foreach($citys as $city){
		$sids=split(",",$city['group_concat(s.sid)']);
		$counts=split(",",$city['group_concat(s.count)']);
		if(!empty($sids)){
			$l=count($sids);
			for($i=0;$i<$l;$i++){
				if($counts[$i]>100000){
					$city[$soldierName[$sids[$i]]."Count"]=floor($counts[$i]/10000).$GLOBALS['sys']['wan'];
				}else{
					$city[$soldierName[$sids[$i]]."Count"]=$counts[$i];
				}
			}
		}
		$city['cid']=getPos($city['cid']);
		$ret[]=$city;
	}
	return $ret;
}

function getDefenceStats($uid){
	$citys=sql_fetch_rows(" select c.name,c.cid,group_concat(s.did),group_concat(s.count), b.level from sys_city c left join  sys_city_defence s on c.cid=s.cid left join sys_building b on c.cid=b.cid where c.uid='$uid' and bid=20 group by cid");
	$ret[] =array();
	$ret[] =5;
	$dName=array("","xianjing","juma","jianta","gunmu","leishi");
	foreach($citys as $city){
		$sids=split(",",$city['group_concat(s.did)']);
		$counts=split(",",$city['group_concat(s.count)']);
		$area=0;
		if(!empty($sids)){
			$l=count($sids);
			for($i=0;$i<$l;$i++){
				$city[$dName[$sids[$i]]."Count"]=$counts[$i];
				$area+=$sids[$i]*$counts[$i];
			}
		}
		$city['area']=$city['level']*10000-$area;
		$city['cid']=getPos($city['cid']);
		$ret[]=$city;
	}
	
	return $ret;
}

function getPos($cid){
	return "(".($cid%1000).",".floor($cid/1000).")";
}

?>