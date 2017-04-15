<?php
require_once("./ArmorFunc.php");
//第一步，找出所有的npc城池
//第二步，给npc玩家添加装备sys_user_armor
//第三步，把装备穿上npc城守
/**
 * 
 * 
 * 10005 热血套
 * 10007 高级热血套
 * 11002 名将套
 * 12003 冰封套
 
 * 
 */
 echo "start";
armornpchero();
echo "finish";

function armornpchero(){
	$mingjianghid="'518','107','791','255','580','186','285','340','870','699','577','156','780',
	'347','362','563','484','549','801','600','222','805','677','103','688','357','363','190','498',
	'441','117','399','200','676','320','102','562','73','455','567','350','43','865','834','87','488',
	'360','509','744','270','725','493','36','202','785','885','613','817','79','847','55','178','407',
	'69','842','147','175','338','327','445','775','355','861','411','628','391','321','627','271','231',
	'1011','486','164','620','856','99','316','765','77','794','7','529','863','259','781','599','578','579'";
	$citys=sql_fetch_rows("select cid,city.chiefhid,city.uid,city.type from sys_city city  join sys_user user on city.uid=user.uid and city.uid<1000 and city.chiefhid>0 and chiefhid not in($mingjianghid)");
	$mingjiangcitys=sql_fetch_rows("select cid,city.chiefhid,city.uid,city.type from sys_city city  join sys_user user on city.uid=user.uid and city.uid<1000 and city.chiefhid>0 and chiefhid  in($mingjianghid)");
	$herocount=0;
	foreach ($mingjiangcitys as $city) {
		$tieid=11002;
		$hid=$city['chiefhid'];
		$uid=sql_fetch_one_cell("select uid from sys_city_hero where hid='$hid'" );
		if($uid<=0||$uid>1000){
			continue;
		}
		$addedsids=addTieTemp($uid,$tieid);
		equipTieTemp($uid,$addedsids,$hid);
		$herocount++;
	}
	echo "add ".$herocount." \n";
	foreach ($citys as $city) {
		$tieid=10005;
		$citytype=$city['type'];
		if($citytype==0){
			$tieid=10005;
		}
		else if($citytype==1){
			$tieid=10007;			
		}
		else if($citytype==2){
			$tieid=11002;						
		}
		else if($citytype==3){
			$tieid=12003;
		}else if($citytype==4){
			$tieid=12003;
		}
		$hid=$city['chiefhid'];
		$uid=sql_fetch_one_cell("select uid from sys_city_hero where hid='$hid'" );
		if($uid<=0||$uid>1000){
			continue;
		}
		$addedsids=addTieTemp($uid,$tieid);
		equipTieTemp($uid,$addedsids,$hid);
		$herocount++;
		if($herocount%500==0){
		echo "\n".$herocount."\n";	
		}
	}
	echo "add ".$herocount." \n";
	
}
/**
 * 
 * 给npc玩家添加一套装备
 *
 * @param unknown_type $uid
 * @param unknown_type $tieid
 * @return unknown
 */
function addTieTemp($uid,$tieid){
	$armors=sql_fetch_rows("select * from cfg_armor where tieid='$tieid'");
	$addsids=array();
	foreach ($armors as $armor) {
		$sid=addArmorTemp($uid,$armor,1,12);
		array_push($addsids,$sid);
	}
	return $addsids;
	
}
function addArmorTemp($uid,$armor,$cnt,$type,$stronglevel=0)
{
	$strongvalue=sql_fetch_one_cell("select strong_value from cfg_strong_probability where level='$stronglevel'");
	if (empty($strongvalue)) {
		$strongvalue=0;
	}
	for($i=0;$i<$cnt;$i++){
		$armorid=$armor['id'];
		$oldsid=sql_fetch_one_cell("select sid from sys_user_armor where armorid='$armorid' and hid=0 and uid='$uid'");
		if(empty($oldsid)){
			$sid=sql_insert("insert into sys_user_armor (uid,armorid,hp,hp_max,hid,strong_level,strong_value) values ($uid,'{$armor['id']}',{$armor['ori_hp_max']}*10,'{$armor['ori_hp_max']}',0,'$stronglevel','$strongvalue')");
		}else{
			return $oldsid;
		}
	}
	//sql_query("insert into log_armor (uid,armorid,count,time,type) values ($uid,'{$armor['id']}',$cnt,unix_timestamp(),$type)");//临时去掉log
	return $sid;
}
function equipTieTemp($uid,$tieids,$hid){
	foreach ($tieids as $tieid) {
		$param=array();
		array_push($param,$hid);
		array_push($param,$tieid);
		equipArmorTemp($uid,$param);
	}
	
	
}
function equipArmorTemp($uid,$param)
{
	$hid=array_shift($param);
	$sid=array_shift($param);
	$hero = sql_fetch_one ( "select * from sys_city_hero where hid='$hid' and uid=$uid" );
	if (empty ( $hero )) {
		echo "empty ( hero ):".$hid.":".$uid;
		return ;
	}
	$armorInfo=sql_fetch_one("select * from sys_user_armor u left join cfg_armor c on c.id=u.armorid where u.sid='$sid' and u.uid='$uid'");
	$spart=$armorInfo['part']*10;
	
	if($spart==110){
		while (true) {
			$info = sql_fetch_one("select * from sys_hero_armor where hid=$hid and spart=$spart");
			if (!empty($info)) {
				$spart +=1;
			} else {
				break;
			}
		}
		if ($spart > 112) {
			$spart = 112;
		}
	}
	if($spart==90){
		while (true) {
			$info = sql_fetch_one("select * from sys_hero_armor where hid=$hid and spart=$spart");
			if (!empty($info)) {
				$spart +=1;
			} else {
				break;
			}
		}
		if ($spart > 91) {
			$spart = 91;
		}
	}
	if($spart==100){
		while (true) {
			$info = sql_fetch_one("select * from sys_hero_armor where hid=$hid and spart=$spart");
			if (!empty($info)) {
				$spart +=1;
			} else {
				break;
			}
		}
		if ($spart > 101) {
			$spart = 101;
		}
	}
	

	
	$hp=ceil($armorInfo['hp']/10);
	if($hp<=0)
	{
		echo "hp<=0";
		return ;
	}
	$heroInfo=sql_fetch_one("select * from sys_city_hero where hid='$hid'");
	$armorid=$armorInfo['armorid'];

	$oldarmor=sql_fetch_one("select * from sys_hero_armor h left join cfg_armor c on c.id=h.armorid where h.hid='$hid' and h.spart='$spart'");
	if(!empty($oldarmor))	//把旧的装备换下来
	{
		$oldid=$oldarmor['sid'];
		sql_query("update sys_user_armor set hid=0 where sid='$oldid'");
	}
	sql_query("update sys_user_armor set hid='$hid' where sid='$sid'");
	sql_query("insert into sys_hero_armor (hid,spart,sid,armorid) values ($hid,$spart,$sid,$armorid) on duplicate key update sid=$sid,armorid=$armorid");
	if($heroInfo['state']==1)
	{
		updateCityResourceAdd($heroInfo['cid']);
	}
	regenerateHeroAttri($uid,$hid);
}
?>