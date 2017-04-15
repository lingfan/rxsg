<?php
require_once("./interface.php");
require_once("./utils.php");

function doGetStoreInfo($uid,$cid)
{
	$ret=array();
	$foodbase=sql_fetch_one_cell("select sum(level*(level+1)*50) from sys_building where cid='$cid' and bid='1'");
	$woodbase=sql_fetch_one_cell("select sum(level*(level+1)*50) from sys_building where cid='$cid' and bid='2'");
	$rockbase=sql_fetch_one_cell("select sum(level*(level+1)*50) from sys_building where cid='$cid' and bid='3'");
	$ironbase=sql_fetch_one_cell("select sum(level*(level+1)*50) from sys_building where cid='$cid' and bid='4'");

	$foodbase=$foodbase*100*GAME_SPEED_RATE;
	$woodbase=$woodbase*100*GAME_SPEED_RATE;
	$rockbase=$rockbase*100*GAME_SPEED_RATE;
	$ironbase=$ironbase*100*GAME_SPEED_RATE;
	$storageTechLevel =sql_fetch_one_cell("select level from sys_city_technic where cid='$cid' and tid=15");
	if(empty($storageTechLevel))
	{
		$storageTechLevel=1;
	}
	else if ($storageTechLevel > 0)
	{
		$storageTechLevel=1.0 + 0.1 * $storageTechLevel;
	}
	$ret[]=$foodbase;
	$ret[]=$woodbase;
	$ret[]=$rockbase;
	$ret[]=$ironbase;
	$ret[]=$storageTechLevel;
	$ret[]=sql_fetch_one_cell("select count(*) from sys_building  where cid='$cid' and bid=17");
	$ret[]=GAME_SPEED_RATE*sql_fetch_one_cell("select sum(level*(level+1)*5000) from sys_building where cid='$cid' and bid=17");
	$ret[]=sql_fetch_one("select `food_store`,`wood_store`,`rock_store`,`iron_store` from `sys_city_res_add` where cid='$cid'");
	$ret[]=sql_fetch_one_cell("select count from sys_goods where gid=152 and uid=$uid");

	return $ret;
}

function modifyStoreRate($uid,$cid,$param)
{
	$foodrate=intval(array_shift($param));
	$woodrate=intval(array_shift($param));
	$rockrate=intval(array_shift($param));
	$ironrate=intval(array_shift($param));
	$uid=intval($uid);
	$cid=intval($cid);
	if($foodrate<0||$woodrate<0||$rockrate<0||$ironrate<0)
	{
		throw new Exception($GLOBALS['modifyStoreRate']['negative_store_rate']);
	}
	else if($foodrate+$woodrate+$rockrate+$ironrate>100)
	{
		throw new Exception($GLOBALS['modifyStoreRate']['resource_total_100']);
	}

	sql_query("update sys_city_res_add set food_store='$foodrate',wood_store='$woodrate',rock_store='$rockrate',iron_store='$ironrate',resource_changing=1 where cid='$cid'");
	throw new Exception($GLOBALS['modifyStoreRate']['succ_change_rate']);
}

/**
 * 完成打包，支付相关费用
 *
 * @param  $uid
 * @param  $cid
 * @param  $param 5中资源打包后的数据
 * @return unknown
 */
function payToPack($uid,$cid,$param){
	$uid=intval($uid);
	$cid=intval($cid);
	if (empty($uid)||empty($cid)||empty($param))
		return ;
	//物品类型对应到gid
	$typeToGid = array('gold1'=>86,'gold2'=>85,'food1'=>91,'food2'=>87,'iron1'=>94,'iron2'=>90,'rock1'=>93,'rock2'=>89,'wood1'=>92,'wood2'=>88);
	//物品类型对应到mem_city_resource 字段。
	$typeToCol = array('gold1'=>'gold','gold2'=>'gold','food1'=>'food','food2'=>'food','iron1'=>'iron','iron2'=>'iron','rock1'=>'rock','rock2'=>'rock','wood1'=>'wood','wood2'=>'wood');
	//对数据操作前，进行一次检查
	$totalCopper=0;
	//检查资源是否够
	foreach ($param as $arr) {
		$type = $typeToCol[$arr[0]];
		$type = addslashes($type);
		$resource = sql_fetch_one_cell("select $type from mem_city_resource where cid=$cid");
		if ($resource < $arr[2]) throw new Exception($GLOBALS['payToPack']['not_enough_res']);
		$totalCopper += $arr[3];
	}
	$totalCopper = ceil($totalCopper);
	//检查铜钱是否够
	$curCopper = sql_fetch_one_cell("select count from sys_goods where uid=$uid and gid=152");
	if ($curCopper < $totalCopper)  throw new Exception($GLOBALS['payToPack']['not_enough_moeny']);
	
	//交钱交货
	foreach ($param as $arr) {
		//扣钱
		addGoods($uid,152,-ceil($arr[3]),2);//152是铜钱
		//加打包后的商品
		$type = $typeToGid[$arr[0]];
		addGoods($uid,$type,floor($arr[1]),2);
		$type = $typeToCol[$arr[0]];
		$resource = sql_fetch_one_cell("select $type from mem_city_resource where cid=$cid");
		if ($type == "wood") {
			addCityResources($cid,(0-$arr[2]),0,0,0,0);
		} else if ($type == "rock") {
			addCityResources($cid,0,(0-$arr[2]),0,0,0);
		} else if ($type == "iron") {
			addCityResources($cid,0,0,(0-$arr[2]),0,0);
		} else if ($type == "food") {
			addCityResources($cid,0,0,0,(0-$arr[2]),0);
		} else if ($type == "gold") {
			addCityResources($cid,0,0,0,0,(0-$arr[2]));
		}
	}


	/*//一手交钱
	addMoney($uid,(0-$cost),52);
	//一手交货
	addGoods($uid,$baseUid,$count,2);
	if($index==0)
	addCityResources($cid,0,0,0,0,(0-$resCount));
	else if($index==1)
	addCityResources($cid,0,0,0,(0-$resCount),0);
	else if($index==2)
	addCityResources($cid,(0-$resCount),0,0,0,0);
	else if($index==3)
	addCityResources($cid,0,(0-$resCount),0,0,0);
	else if($index==4)
	addCityResources($cid,0,0,(0-$resCount),0,0);*/
	$ret=array();
	$ret[]=0;
	$ret[]=0;
	return $ret;
}

?>