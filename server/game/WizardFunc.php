<?php
/*
require_once ("./interface.php");
require_once './TaskFunc.php';
require_once './GoodsFunc.php';
require_once './BattleFunc.php';
$GLOBALS['help_msg']="
#help on  开启巫师指令
#help off  关闭巫师指令，恢复聊天
#user money +1000    加很多元宝
#user gift  +1000    加礼金
#user nobility 列侯   爵位达到列侯
#user officepos 丞相   官职达到丞相
#thing 流离百姓  1000 物品+1000（可指定道具，任务物品，装备名）
#city autobuild 自动建筑满级，发元宝和很多兵
#city res wuxian 资源无限，包括黄金，粮食等无限
#city res +具体数值  资源设置，包括黄金，粮食等具体值
#city soldier +1000 本城池所有兵+1000
#city addbuilding xy 建筑名字 建筑等级   
如：#city addbuilding 120 官府 2   城内位置值104,105,114,115,120,124,125,134,135,144,145,153,154,155,199城外位置值1,2,10,11,12,13,21,22,23,24,31,32,33,34,35,41,42,43,44,45,46,51,52,53,54,55,56,60,61,62,63,64,65,70,71,72,73,81,82,100,101,102,103,110,111,112,113,122,123,132,133,140,141,142,143,150,151,152
#troop arrive  所有军队立即到达
#troop wudi 所有出征军队无敌了
#troop captive 关羽   抓名将去了噢
#troop invade 101,200  立马出征无敌军队占领城池[101,200]
#troop invade_slow 101,200   慢慢打郡城州城，如果要打郡城会先派兵占领1/3县城,需要多次执行该命令
#troop invade_fast 101,200   立刻占领郡城州城，如果要打郡城，会先把1/3县城改为同联盟

#finish huangjin  黄巾之乱捐献任务完成，只剩捐献粮食
#finish dongzhuo1  讨伐董卓第一阶段捐献任务完成，只剩流离百姓1
#finish dongzhuo2  讨伐董卓第二阶段捐献任务完成，只剩珍珠1

#fuben huangjin end 完成副本：黄巾之乱

#report clear 删除所有战报
#field dropall 放弃所有野地

#dailyTask 刷新日常任务

#openBox 礼盒名称  礼盒个数         
#gather +秒数  设置采集时间
#battle starttime 2009-9-9 18:08 设置副本开启时间，开了以后再用
更多帮助请查看: http://sgtest.uuyx.com/wizard.txt";


function executeCommand($uid,$param) {	
	$msg=array_shift($param);	
	if (!strstr($msg,"#")) return;
	$ret="";
	if ($msg=="#help on") $ret="已开启";
	else if ($msg=="##"){		
		return $GLOBALS['help_msg'];	
	}	
	$msg=trim(str_replace("#","",$msg));
	$msg=str_ireplace("　"," ",$msg);//
	$msg=str_ireplace("  "," ",$msg);
	$words=split(" ",$msg);	
	$user=sql_fetch_one("select * from sys_user where uid=$uid");
	$cid=$user["lastcid"];	
	$command=$words[0]; //命令类型
	if ($command=="city"){
		if ($words[1]=="autobuild"){ //自动建设
			$_GET["passport"]=$user["passport"];
			require_once 'autofortest.php';		
			$ret="自动创建牛逼角色成功！";		
		}
		if ($words[1]=="res"){ 
			if ($words[2]=="wuxian"){ //资源无限							
				sql_query("update mem_city_resource set food=100000000,wood=100000000,iron=100000000,rock=100000000,gold=100000000 where cid = $cid");
				$ret="资源无限成功！";
			}else {
				$res_count= '('.$words[2].')';
				sql_query("update mem_city_resource set food=$res_count,wood=$res_count,iron=$res_count,rock=$res_count,gold=$res_count where cid = $cid");
	   	 		$ret="资源设置成功";
			}
		}
		if ($words[1]=="soldier"){
			for($sid=1;$sid<=12;$sid++){
				sql_query("insert into sys_city_soldier values($cid,$sid,$words[2]) on duplicate key update count=count+$words[2]");	
			}
			for($sid=45;$sid<=50;$sid++){
				sql_query("insert into sys_city_soldier values($cid,$sid,$words[2]) on duplicate key update count=count+$words[2]");	
			}
			$ret="增加兵力成功！";
		}		
		if ($words[1]=="addbuilding"){
			addBuilding($uid,$words[2],$words[3],$words[4]);
			$ret="增加建筑成功！";
		}		
	}	
	else if ($command=="user"){ 
		if($words[1]=="nobility"){
			sql_query("update sys_user set nobility = (select id from cfg_nobility where name='$words[2]') where uid = $uid");
		}else if($words[1]=="officepos"){
			sql_query("update sys_user set officepos = (select id from cfg_office_pos where name='$words[2]') where uid = $uid");
		}
		else{
			if (strstr($words[2],"+") ||strstr($words[2],"-"))
				sql_query("update sys_user set $words[1] = $words[1] ".$words[2]." where uid =$uid");
			else 
				sql_query("update sys_user set $words[1] =  ".$words[2]." where uid =$uid");
		}
		$ret="成功！";
	}
	else if ($command=="thing"){
		if($words[1]=='clear'){
			sql_query("delete from sys_things where uid=$uid");
			sql_query("delete from sys_goods where uid=$uid");
			sql_query("delete from sys_user_armor where uid=$uid and hid=0");
			return "所有道具清理成功！";
		}		
		
		$name = $words[1];
		$count = intval($words[2]);
		$type = 5;
		$ret="物品[$name]增加($count)成功！";
		
		$tid=sql_fetch_one_cell("select tid from cfg_things where name='$name'");
		if ($tid){
			addThings($uid,$tid,$count,5);
			return $ret;
		}
		$gid=sql_fetch_one_cell("select gid from cfg_goods where name='$name'");
		if ($gid){
			addGoods($uid,$gid,$count,5);
			return $ret;
		}
		$armor=sql_fetch_one("select * from cfg_armor where name='$name'");
		if ($armor){
			addArmor($uid,$armor,$count,5);
			return $ret;
		}
		$bid=sql_fetch_one_cell("select id from cfg_book where name='$name' and level=1");
		if ($bid){
			addBook($uid,$bid,$count,5);
			return $ret;
		}
		$ret = 'Argument Error!';		
	}
	else if ($command=="troop"){ 
		if ($words[1]=="arrive"){
			sql_query("update sys_troops set endtime=unix_timestamp() where uid =$uid");
			$ret="所有军队立即到达！";
		}
		else if ($words[1]=="wudi"){
			sql_query("update sys_troops set  soldiers='3,8,1000000,10,1000000,11,1000000' where uid =$uid");
			$ret="所有出征军队无敌了！";
		}else if( $words[1]=="invade_fast" ) {
			$targetcid=getcidbystr($words[2]);			
			$task=4;	
			$union_id=sql_fetch_one_cell("select union_id from sys_user where uid =$uid");			
			$targetcity=sql_fetch_one("select * from sys_city where cid = $targetcid");
			$targetuid=$targetcity["uid"];
			$type =$targetcity["type"];
			$province =$targetcity["province"];//州
			$targetwid=cid2wid($targetcid);
			if ($type>=2){
				if ($union_id<=0){
					require_once 'UnionFunc.php';
					sql_query("update sys_user set prestige=200000000 where uid =$uid");
					createunion($uid,array($user['name']));
					$union_id=sql_fetch_one_cell("select union_id from sys_user where uid =$uid");
				}
				if ($type==2){ //打郡城,要占领1/3县城		
					$jun=sql_fetch_one_cell("select jun from mem_world where wid=$targetwid");
					$ourCount=sql_fetch_one_cell("select count(1) from mem_world a, sys_city b,sys_user c where a.ownercid=b.cid and b.uid =c.uid and b.type = 1 and a.province = $province and jun = $jun and c.union_id =  ".$user['union_id']);
					$rows=sql_fetch_rows("select * from mem_world a,sys_city b where a.ownercid=b.cid and a.type =0 and b.type = 1 and a.province = $province and a.jun = $jun ");
					$needCount = ceil(count($rows)/3);
					$leftCount = $needCount-$ourCount;		
					foreach ($rows as $row) {						
						if ($leftCount<=0) break;
						$auser=sql_fetch_one("select uid,union_id from sys_user where uid=".$row["uid"]);						
						if($union_id != $auser["union_id"] && $auser["uid"]!=$targetuid ){
							sql_query("update sys_user set union_id=$union_id where uid = ".$auser["uid"]);							
							$leftCount--;	
						}
					}
					$ret="走，打郡城去了，速度！";					
				}				
				else if ($type==3){	//打州城，要占领1/3郡城	
					$ourCount=sql_fetch_one_cell("select count(1) from sys_city a,sys_user b where a.uid=b.uid and b.union_id =$union_id and a.province = $province and a.type = 2"); //本盟有多少郡城
					$rows=sql_fetch_rows("select * from sys_city a,sys_user b where a.uid=b.uid and a.province = $province and a.type = 2 ");
					$needCount = ceil(count($rows)/3);
					$leftCount = $needCount-$ourCount;		
					foreach ($rows as $row) {						
						if ($leftCount<=0) break;
						$auser=sql_fetch_one("select uid,union_id from sys_user where uid=".$row["uid"]);						
						if($union_id != $auser["union_id"] && $auser["uid"]!=$targetuid ){
							sql_query("update sys_user set union_id=$union_id where uid = ".$auser["uid"]);							
							$leftCount--;	
						}
					}			
					$rows=sql_fetch_rows("select hid from sys_city_hero where uid=$targetuid and hid !=$targetuid");
					foreach ($rows as $row) {
						sql_query("update sys_city_hero set state = 0 where  hid =".$row["hid"]);				
						deleteHero($targetuid,$row["hid"]);
					}		
					sql_query("update sys_city_hero set uid = 0 where uid=$targetuid and hid !=$targetuid");					
					$ret="走，打州城去了，速度！";
				}
			}
			else 
				$ret="无敌军队已出发执行任务！";		
			troop_go_now($uid,$cid,$targetcid,$task);		
			sql_query("update sys_troops set  soldiers='4,6,4000000,8,4000000,10,4000000,11,4000000',endtime=unix_timestamp() where uid =$uid");
			sql_query("update sys_city_soldier set count = 0 where cid = $cid");													
			
		}else if( $words[1]=="invade_slow" ) {
			$targetcid=getcidbystr($words[2]);			
			$task=4;
			if ($words[1]=="plunder") $task=3;
			try{
				troop_go($uid,$cid,$targetcid,$task);
			}catch (Exception  $e){
				sql_query("update sys_troops set  soldiers='4,6,4000000,8,4000000,10,4000000,11,4000000',endtime=unix_timestamp() where uid =$uid");
				sql_query("update sys_city_soldier set count = 0 where cid = $cid");				
				throw $e;
			}				
			$ret="无敌军队已出发执行任务！";		
		}else if (in_array($words[1],array("invade","plunder"))){
			$targetcid=getcidbystr($words[2]);			
			$task=4;
			if ($words[1]=="plunder") $task=3;			
			troop_go_now($uid,$cid,$targetcid,$task);						
			sql_query("update sys_troops set  soldiers='4,6,4000000,8,4000000,10,4000000,11,4000000',endtime=unix_timestamp() where uid =$uid");
			sql_query("update sys_city_soldier set count = 0 where cid = $cid");							
			
			$ret="无敌军队已出发执行任务！";
		}else if (in_array($words[1],array("fight"))){
			$targetcid=getcidbystr($words[2]);							
			troop_go_now($uid,$cid,$targetcid,3);			
			
			$wid=cid2wid($targetcid);		
			$world=sql_fetch_one("select level,type from mem_world where wid = $wid");		
			$level=$world["level"];
			$countArray=array(2,4,8,20,35,110,150,400,800,1600,3500);
			$count=$countArray[$level]*1.1;
			sql_query("update sys_troops set  soldiers='1,7,$count',endtime=unix_timestamp() where uid =$uid");
			sql_query("update sys_city_soldier set count = 0 where cid = $cid");							
			
			$ret="走，打架去了！";
		}else if ($words[1]=="captive"){
			$heroname=trim($words[2]);
			$hero=sql_fetch_one("select cid,hid from sys_city_hero where name='$heroname' and hid<2000 order by hid asc limit 1");
			$targetcid=$hero["cid"];
			$hid=$hero["hid"];
			troop_go($uid,$cid,$targetcid,4);
			require_once 'utils.php';
			$wid=cid2wid($targetcid);							
			$world=sql_fetch_one("select level,type from mem_world where wid = $wid");
			$type=$world["type"];
			sql_query("update sys_city_hero set loyalty=0 where hid=$hid");			
			//select `count` from sys_things where uid="+uid+" and tid="+XStringUtil::toString(20000+npcid)
			$tid=20000+$hid;
			sql_query("insert into sys_things values($uid,$tid,1) on duplicate key update count=count+1");
			if ($type>0){//野地
				$level=$world["level"];
				$countArray=array(2,4,8,20,35,110,150,400,800,1600,3500);
				$count=$countArray[$level]*1.1;
				sql_query("update sys_troops set  soldiers='1,7,$count',endtime=unix_timestamp() where uid =$uid");
			}else{ //城池
				sql_query("update sys_troops set  soldiers='4,6,4000000,8,4000000,10,4000000,11,4000000',endtime=unix_timestamp() where uid =$uid");
				sql_query("update sys_city_soldier set count = 0 where cid = $cid");			
			}
			$ret="走，抓".$heroname."去了！";
		}

	}	
	else if ($command=="report"){ 
		if ($words[1]=="clear"){ 
			sql_query("delete from sys_report where uid =$uid");
			$ret="所有战报已清空！";
		}		
	}
	else if ($command=="sql"){ 
		$strSql=substr($msg,3);
		//sql_query($strSql);
		$ret="sql执行成功！";
	}else if ($command=="finish"){ 		
		if ($words[1]=="huangjin"){ //黄巾之乱马上完成
			sql_query("update huangjin_progress set curvalue =  maxvalue ");
			sql_query("update huangjin_progress set curvalue =  maxvalue-1 where tid=12001");			
			$ret="黄巾之乱捐献任务完成，只剩捐献粮食！";
		}
		if ($words[1]=="dongzhuo1"){ 
			sql_query("update  dongzhuo_progress set curvalue =  maxvalue where tid between 15001 and 15004");
			sql_query("update  dongzhuo_progress set curvalue =  maxvalue-1 where tid=15002");		
			addThings($uid,21,2000,1);					
			$ret="讨伐董卓第一阶段捐献任务完成，只剩流离百姓1";
		}
		if ($words[1]=="dongzhuo2"){ 
			sql_query("update dongzhuo_progress set curvalue =  maxvalue ");
			sql_query("update dongzhuo_progress set curvalue =  maxvalue-1 where tid=15301");			
			$ret="讨伐董卓第二阶段捐献任务完成，只剩珍珠1";
		}	
	}
	else if ($command=="field"){
		if ($words[1]=="dropall"){
			 dropAllFields($uid,$cid);		
			 $ret="放弃所有野地成功！";
		}		
	}
	else if ($command=="openBox")
	{
		$boxName = $words[1];
		$boxCount = $words[2];
		$gid = sql_fetch_one_cell("select gid from cfg_goods where name = '$boxName'");
		
		if(sql_check("select * from sys_goods where uid = $uid and gid = $gid"))
		{
			sql_query("update sys_goods set `count` = `count`+$boxCount where uid = $uid and gid = $gid");
		}else {
			sql_query("insert into sys_goods(uid,gid,`count`) values($uid,$gid,$boxCount)");
		}
		
		$ary = array();
		$ary[] = $gid;
		$goodsStat = array();
		$details = sql_fetch_rows("select name from cfg_box_details where srcid = $gid");
		$testdet = "";
		foreach($details as $detailName)
		{
			$testdet = $testdet.$detailName['name']."\n";
			$goodsStat[$detailName['name']] = 0;                                                     
		}
		for($i = 0;$i< intval($boxCount);$i++)
		{
			$goodsRet = 0;
			$goodInfo = array();
			try {
				$goodsRet = useGoods($uid,$ary);
				$goodInfo = $goodsRet[2][0];
			}
			catch(Exception  $e)
			{
				
				$exceptionStr = $e->getMessage();
				$goodInfo['name'] = substr($exceptionStr,6,6);
				$goodInfo['count'] = 10000;
			}
			//$goodsRet = useGoods($uid,$ary);
			//$goodInfo = $goodsRet[2][0];
			$name = $goodInfo['name'];
			$count = 0;
			if(!empty($goodInfo['count']))
			{
				$count = intval($goodInfo['count']);
			}
			else if(!empty($goodInfo['cnt']))
			{
				$count = intval($goodInfo['cnt']);
			}else {
				return "错误";
			}
			if(intval($count) >1)
			$name = $goodInfo['name']."*".$count;
			
//			if(empty($goodsStat[$name]))
//			{
//				return $name."###\n".$testdet;
//				
//			}
			$goodsStat[$name] += 1;
			
		}
		$strRet = "#openBox $boxName $boxCount\n##############################\n";
		
		foreach($goodsStat as $goodName => $goodCount)
		{
			$strRet = $strRet.$goodName."\t".$goodCount."\n";			
		}
		$strRet = $strRet."##############################"."\n";
		
		return $strRet;
	}else if ($command=="battle") {
		$subType=$words[1];
		$currentbattle=firstGetUserBattleInfo($uid,0);
		if($subType=="starttime") {
			$settime=$words[2]." ".$words[3];
			sql_query("update sys_user_battle_field set starttime=unix_timestamp('$settime'),endtime=starttime+7200 where id=$currentbattle[battlefieldid]");
		}
	}else if ($command=="dailyTask") {
		sql_query("update cfg_task_schedule set next_reset=unix_timestamp(curdate()) where `interval`=86400");
		$strRet = $strRet."一分钟后日常任务刷新"."\n";
		return $strRet;
	}else if($command=="openActBox"){//(10123-50000)
		$boxName = $words[1];
		$boxCount = $words[2];
		$gid = sql_fetch_one_cell("select gid from cfg_goods where name = '$boxName'");
		$valid=sql_fetch_one_cell("select count(1) from cfg_box_details where srctype=0 and srcid=".intval($gid));
		
		if($boxCount>1000 || $valid<=0){
			return "Argument Error!";
		}
		
		$cid = sql_fetch_one_cell("select lastcid from sys_user where uid=$uid");
		sql_query("insert into sys_goods (uid, gid, count) values($uid,$gid,$boxCount) on duplicate key update count=count+$boxCount");
		$results=array();
		for($i=1;$i<=$boxCount;$i++){
			$getname = false;
			try{
				srand();		
				$msg = openDefaultBox($uid, $cid, $gid,0);				
			} catch ( Exception $e ) {
				$getname = $e->getMessage ();				
			}
			if(!$getname){
				$getname = $msg[0]['name'].$msg[0]['count'];
			}
			if (isset($results[$getname])){
				$results[$getname] += 1;
			}else{
				$results[$getname] = 1;
			}
		}
		ksort($results);
		$strRet = "#openActBox $boxName $boxCount\n#####################\n";
		foreach ($results as $resultName=>$resultCount) {
			$strRet = $strRet.$resultName."\t".$resultCount."\n";
		}
		$strRet = $strRet."#####################\n";
		return $strRet;
		
	}else if($command=="paytest"){
		$money = intval($words[1]);
		if($money<=0){
			return "Argument Error!";
		}
		$passtype='localtest';
		$code='test';
		$payname='testcenter';
		$type=0;
		$orderid=sql_fetch_one_cell("select count(1)+1 from pay_log");

		$passport = $user['passport'];
		$now = sql_fetch_one_cell("select unix_timestamp()");
		$today = $now - (($now + 8 * 3600)%86400);
		sql_query("update sys_user set money=money+'$money' where uid='$uid'");
	    sql_query("insert into log_money (uid,count,time,type) values ('$uid','$money',unix_timestamp(),$type)");
	    sql_query("insert into pay_log (orderid,type,payname,passport,passtype,money,code,time) values ('$orderid','$type','$payname','$passport','$passtype','$money','$code',unix_timestamp())");
	    sql_query("insert into pay_day_money (day,money) values ('$today','$money') on duplicate key update `money`=`money`+'$money'"); 
    
	    @include("./paygift.php");
	    
	    return "充值 $money 成功！";
	}else if($command=="gather"){
		$gatherTime = '('.$words[1].')';
		sql_query("update sys_gather set starttime=unix_timestamp()-$gatherTime where troopid in (select id from sys_troops where uid='$uid')");
	    $ret="采集时间设置成功";
	}else if($command=="fuben"){
		$battleCommand = $words[1];		
		if ($battleCommand=='ready') {
			$battleID = intval($words[2]);
			sql_query("update sys_user_battle_field set state = 2,starttime=0,endtime=0,finishtime=0,winner=-1,progress=0 where id=$battleID");
			return "{$battleID}号战场已进入准备状态";
		}		
		
		$battleInfo = sql_fetch_one("select bid,battlefieldid,unionid from sys_user_battle_state where uid=$uid limit 1");
		if(empty($battleInfo)){
			return "Error: 你未进入战场副本！";
		}
		$bid = $battleInfo['bid'];
		$battleID = $battleInfo['battlefieldid'];
		if($battleCommand=='start'){
			if ($bid==2001 || $bid==4001) {//官渡 董卓
				sql_query("update sys_user_battle_field set state = 0,starttime=unix_timestamp(),endtime=unix_timestamp()+3600*8 where bid=$bid and id = $battleID");
				$ret="你所在的战场副本已开启";
			}
		}else if($battleCommand=='end'){
			$winner = $battleInfo['unionid'];
			if($bid==1001){
				$battlefield=sql_fetch_one("select * from sys_user_battle_field where bid = $bid  order by id desc limit 1");
				$rows = sql_fetch_rows("select id from cfg_task_goal where tid in (60000,60001,60002)");
				foreach ($rows as $row) {
					$id=$row["id"];
					sql_query("delete from sys_user_goal where gid =$id and uid = ".$battlefield["createuid"]);
					sql_query("insert into sys_user_goal(uid,gid) select createuid,$id from sys_user_battle_field where bid = $bid  order by id desc limit 1;");
				}
				
			}
			if ($bid==4001) {
				$winner = 9;//7失败
				sql_query("update sys_user_battle_state set unionid=7 where unionid=$winner and uid<>$uid");
				sql_query("update sys_user_battle_state set unionid=$winner where uid=$uid");
			}
			sql_query("update sys_user_battle_field set endtime=unix_timestamp(),winner = $winner,state = 1, type=1 where id = $battleID");//提前冻结
			$ret="你所在的战场副本已胜利结束";
		}
	}else if($command=="hourrun"){
		$toDate = $words[1];
		$toHour = $words[2];
		$dateStr = "$toDate $toHour:00";
		exec("cd /backup_act4;/usr/local/php-cgi/bin/php /backup_act4/hour_run.php '$dateStr'",$ret);
		return "$dateStr --\n\n".implode("\n",$ret);
	}else if($command=="clearActData"){
		exec("/backup_act4/dd.sh",$ret);
		return "清理本期活动数据\n\n".implode("\n",$ret);
	}
	return "**".$ret;
}
function dropAllFields($uid,$cid){
	sql_query("update sys_troops set state=1,endtime=unix_timestamp() where state = 4");
	$worlds=sql_fetch_rows("select * from mem_world where type>0 and ownercid=$cid");
	foreach ($worlds as $world) {
		$wid=$world["id"];
		discardField($uid,array($cid,$wid));
	}		
}
function troop_go($uid,$cid,$targetcid,$task){
	$user=sql_fetch_one("select * from sys_user where uid=$uid");
	$city=sql_fetch_one("select * from sys_city where cid = $targetcid");
	$type =$city["type"];
	$province =$city["province"];//州
	$targetwid=cid2wid($targetcid);
	if ($type==2){ //打郡城,要占领1/3县城
		troop_go_jun($uid,$cid,$targetcid,$task);
	}
	else if ($type==3){ //打州城		
		troop_go_province($uid,$cid,$targetcid,$task); 		
	}else 
		troop_go_now($uid,$cid,$targetcid,$task);  //其他的随便打
}
function troop_go_province($uid,$cid,$targetcid,$task){	
	$user=sql_fetch_one("select * from sys_user where uid=$uid");
	$city=sql_fetch_one("select * from sys_city where cid = $targetcid");
	$type =$city["type"];
	$province =$city["province"];//州
	$targetwid=cid2wid($targetcid);
	$ourCount=sql_fetch_one_cell("select count(1) from mem_world a, sys_city b,sys_user c where a.ownercid=b.cid and b.uid =c.uid and b.type =2 and a.province = $province and c.union_id =  ".$user['union_id']);
	$rows=sql_fetch_rows("select * from mem_world a,sys_city b where a.ownercid=b.cid and a.type =0 and b.type = 2 and a.province = $province ");
	$needCount = ceil(count($rows)/3);
	$leftCount = $needCount-$ourCount;		
	foreach ($rows as $row) {
			if ($row["uid"] != $uid){
				if ($leftCount<=0)break;
				troop_go_jun($uid,$cid,$row["cid"],$task);
				$leftCount--;	
			}
	}
	if ($leftCount<=0){//符合条件的直接攻打郡城		
		$targetuid=sql_fetch_one_cell("select uid from sys_city where cid = $targetcid");
		$rows=sql_fetch_rows("select cid from sys_city_hero where uid='$targetuid' and herotype<>100 and cid != $targetcid");
		foreach ($rows as $row) {
			troop_go_now($uid,$cid,$row["cid"],$task);
		}
		
		if (count($rows)==0)
			troop_go_now($uid,$cid,$targetcid,$task); 		
	}
}
function troop_go_jun($uid,$cid,$targetcid,$task){	
	$user=sql_fetch_one("select * from sys_user where uid=$uid");
	$city=sql_fetch_one("select * from sys_city where cid = $targetcid");
	$type =$city["type"];
	$province =$city["province"];//州
	$targetwid=cid2wid($targetcid);
	if ($type==2){ //打郡城,要占领1/3县城
		$jun=sql_fetch_one_cell("select jun from mem_world where wid=$targetwid");		
		//本联盟我拥有的县城数
		$ourCount=sql_fetch_one_cell("select count(1) from mem_world a, sys_city b,sys_user c where a.ownercid=b.cid and b.uid =c.uid and b.type = 1 and a.province = $province and jun = $jun and c.union_id =  ".$user['union_id']);
		$rows=sql_fetch_rows("select * from mem_world a,sys_city b where a.ownercid=b.cid and a.type =0 and b.type = 1 and a.province = $province and a.jun = $jun ");
		$needCount = ceil(count($rows)/3);
		$leftCount = $needCount-$ourCount;		
		foreach ($rows as $row) {
			if ($row["uid"] != $uid){
				if ($leftCount<=0)break;
				troop_go_now($uid,$cid,$row["cid"],$task);
				$leftCount--;	
			}
		}
		if ($leftCount<=0){//符合条件的直接攻打郡城
			troop_go_now($uid,$cid,$targetcid,$task); 		
		}
	}
}
function troop_go_now($uid,$cid,$targetcid,$task){	
	$user=sql_fetch_one("select * from sys_user where uid=$uid");
	sql_query("insert into sys_city_soldier select $cid,sid,1000 from cfg_soldier where sid between 1 and 13  on duplicate key update count=count+1000");	
	$union_id=$user["union_id"];
	if ($union_id<=0){
		require_once 'UnionFunc.php';
		sql_query("update sys_user set prestige=200000000 where uid =$uid");
		createunion($uid,array($user['name']));
	}
	sql_query("update sys_union set prestige=200000000 where id=(select union_id from sys_user where uid=$uid )");
	sql_query("update mem_city_resource set food=300000000,wood=300000000,iron=300000000,rock=300000000,gold=300000000 where cid = $cid");
	$hid=sql_fetch_one_cell("select hid from sys_city_hero where uid = $uid and cid=$cid and state = 0  limit 1");
	if (empty($hid)){
		throw new Exception("没有空闲的将领可以出征了！");
	}
	sql_query("update sys_city_hero set hero_health=0 where hid=$hid");
	sql_query("update mem_hero_blood set `force`=100,`energy`=100 where hid=$hid");	
	$secondadd=0;
	$soldiers="3,3,1,4,1,9,100,";
	$resource=	"0,0,0,0,0,";
	$usegoods=false;
	$param=array($hid,$targetcid,$task,$secondadd,$soldiers,$resource,$usegoods);
	
	$targetuid = sql_fetch_one_cell("select uid from sys_city where cid = $targetcid");
	if ($targetuid>1000 && !sql_check("select * from mem_user_inwar where (uid='$uid' and targetuid='$targetuid') or (targetuid='$uid' and uid='$targetuid')")){		
		require_once 'GoodsFunc.php';
		addGoods($uid,157,10,0); 
		useTaoNiShengZhi($uid,array($targetuid,$targetcid));
	}
	require_once 'GroundFunc.php';
	starttroop($uid,$cid,$param);
	//将对方的民心改为0，民怨改为100
	sql_query("update mem_city_resource set morale=0 , complaint=100 where cid= $targetcid");	
}
function getCidByStr($cidStr){
	$cidStr=trim($cidStr);
	$words=split(",",$cidStr);
	$x=$words[0];
	$y=$words[1];
	return $y*1000+$x;
	
}
function addBuilding($uid,$xy,$name,$level){
//	$passport=$_GET["passport"];
//	if ($passport=="") exit("need passport");
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
	//$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
	$bid= sql_fetch_one_cell("select bid from cfg_building where `name`='$name'");
	sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ('$cid',$xy,'$bid',$level )");
}
*/
	
?>