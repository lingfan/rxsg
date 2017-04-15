<?php 
require_once("./utils.php");

/**
 * 随机产生圆盘的物品
 * @param $uid
 * @return unknown_type
 */
function getGoods($uid, $param)
{
	$openLottery = sql_fetch_one_cell("select value from mem_state where state = 150");
	if(empty($openLottery) || $openLottery == "" || $openLottery == false)
	{
		$openLottery = 0;
	}
	if($openLottery != 0) {
		throw new Exception($GLOBALS['lottery']['not_available']);
	}
	checkLottoryTime();
	//$first = array_shift($param);
	$infor = sql_fetch_one("select * from mem_lottery_goods where uid=$uid");
	
	$lastid = 0;
	if(empty($infor) || $infor['got']==1) //这个奖已经领过了
	{
		$total = 0;
		//1~8等
		$records = getAllLevelGoods();
		$record_str = "";
		$rcount = 1;
		for($i=0; $i<8; $i++){
			for($j=0; $j<count($records[$i]); $j++){
				$ids = logIDs($records[$i][$j]);
				$record_str = $record_str."".$ids;
				
				if($rcount!=8)
					$record_str = $record_str.",";
					
				$rcount++;
			}
		}
		if(empty($infor)){
			sql_query("insert into mem_lottery_goods(uid, records, `time`, win, got, restart_count) values($uid, '$record_str',  current_date(), '-1,0,0', 0, 0)");
			$lastid = sql_fetch_one_cell("select LAST_INSERT_ID()");  
		}
		else{
			sql_query("update mem_lottery_goods set records='$record_str', `time`=current_date(), win='-1,0,0', got=0, restart_count=0 where uid=$uid"); //每次Round restart_count 都需要清0
			$lastid = $infor["id"];
		}
		$infor = sql_fetch_one("select * from mem_lottery_goods where uid=$uid");//刷新
		

		//$type = array_shift($param);
		//$id = array_shift($param);
	}
	else{
		$records = retrieveGoods($infor['records']);
		$lastid = $infor['id'];
	}
	
	$ret = array();
	
	//$tmp = randWin($uid, $records, $lastid);
	
	//$win_type = $tmp[0];  $win_id = $tmp[1];
	$ret[] = $records;
	$win_str = $infor["win"];
	$ary = explode(",", $win_str);
	$ret[] = $ary[0];
	$ret[] = $ary[1];
	$ret[] = $ary[2];
	$ret[] = $infor['restart_count'];
	//$ret[] = $win_type;
	//$ret[] = $win_id;
	
	$tcount = getTodayCount($uid);
	$ret[] = $tcount;
	return $ret;
	
}

function startLottery($uid)
{
	//增加爵位限制，公士及以上的爵位才能使用幸运宝盒
	$nobility = sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
    $nobility = getBufferNobility($uid,$nobility);//推恩
    if ($nobility<1){
    	throw new Exception($GLOBALS['lottery']['nobility_limit']);
    }
    
	$openLottery = sql_fetch_one_cell("select value from mem_state where state = 150");
	if(empty($openLottery) || $openLottery == "" || $openLottery == false)
	{
		$openLottery = 0;
	}
	if($openLottery != 0) {
		throw new Exception($GLOBALS['lottery']['not_available']);
	}
	checkLottoryTime();
	$count = getTodayCount($uid);
	if($count >= 1) //第一次免费
	{
		if(useGoodsToPayAgain($uid,19989,0)){//幸运宝盒使用机会 无限制
		}else if(useGoodsToPayAgain($uid,10191,4)){//虎尾 每天最多用4次			
		}else if(useGoodsToPayAgain($uid,159,20)){//159	幸运之钥  每天最多用20次
		}else {
			$ret = array();
			$ret[] = -1;
			$ret[] = -2;//使用次数达到上限
			$ret[] = -1;//
			$ret[] = $count;
			return $ret;				
		}
	}
	
	$infor = sql_fetch_one("select * from mem_lottery_goods where uid=$uid and got=0");
	if(empty($infor)){
		throw new Exception($GLOBALS['waigua']['invalid']);
	}
	
	$records = retrieveGoods($infor['records']);
	$tmp = randWin($uid, $records);
	$ret = array();
	$ret[] = $tmp[0];
	$ret[] = $tmp[1];
	$ret[] = $tmp[2];
	
	sql_query("insert into log_lottery(uid, `time`, gid, `type`) values($uid, NOW(), $tmp[1], $tmp[0])");
	
	$count = getTodayCount($uid);
	$ret[] = $count;
//	useMoney($uid, $count);  //扣元宝

	return $ret;
}

/**
 * Enter description here...
 *
 * @param unknown_type $uid
 * @param unknown_type $gid
 * @param unknown_type $dayUseLimit 0表示无限制
 * @return unknown
 */
function useGoodsToPayAgain($uid,$gid,$dayUseLimit) {
	if((sql_fetch_one_cell("select count from sys_goods where uid='$uid' and gid='$gid'")>=1)
	&& ($dayUseLimit==0 || sql_fetch_one_cell("select count(*) from log_goods where uid='$uid' and gid='$gid' and type=0 and count=-1 and time>=unix_timestamp(curdate())")<$dayUseLimit))
	{//
		reduceGoods($uid,$gid,1);
		return true;
	}
	return false;//没道具 或使用超过限制了
}


function getLotteryReward($uid)
{
	$openLottery = sql_fetch_one_cell("select value from mem_state where state = 150");
	if(empty($openLottery) || $openLottery == "" || $openLottery == false)
	{
		$openLottery = 0;
	}
	if($openLottery != 0) {
		throw new Exception($GLOBALS['lottery']['not_available']);
	}
	checkLottoryTime();
	$infor = sql_fetch_one_cell("select uid from mem_lottery_goods where uid=$uid and got=0");
	if (empty($infor)) {
		throw new Exception($GLOBALS['waigua']['invalid']);
	}
	addCount($uid);
	return getGoods($uid, NULL); 
}

function autoGetReward($uid)
{
	$openLottery = sql_fetch_one_cell("select value from mem_state where state = 150");
	if(empty($openLottery) || $openLottery == "" || $openLottery == false)
	{
		$openLottery = 0;
	}
	if($openLottery != 0) {
		throw new Exception($GLOBALS['lottery']['not_available']);
	}
	checkLottoryTime();
	$infor = sql_fetch_one_cell("select uid from mem_lottery_goods where uid=$uid and got=0");
	if (empty($infor)) {
		throw new Exception($GLOBALS['waigua']['invalid']);
	}
	
	$ret = array();
	$winObj = addCount($uid);
	
	$newRoundGoods = getGoods($uid, NULL);

	$ret[] = $winObj;
	$ret[] = $newRoundGoods;
	return $ret;
}

function getTodayCount($uid)
{
	$count = sql_fetch_one_cell("select count(*) from log_lottery where uid=$uid and (unix_timestamp()-unix_timestamp(time))<=3600");
//	$count = sql_fetch_one_cell("select count(*) from log_lottery where uid=$uid and date(`time`)=current_date()");
	if(empty($count))
		$count = 0;
	return $count;
}

/**
 * 生产中将id
 * @param $records1
 * @param $records2
 * @param $records3
 * @param $records4
 * @return unknown_type
 */
function randWin($uid, $records, $is_restart_req=false, $last_win_id=-100, $last_win_type=-1 )
{
	//中奖概率, 7-8等, 5-6等, 2-4等, 1等
	$prob = specialProp($uid);//array(10, 190, 300, 500, 1000, 1500, 2500, 4000);
	$win_rand = mt_rand(1, 10000);
	$win_id = -1;
	$win_type = 0; //cfg_goods
	$win_count = 1;
	
	$win_index = 7; //几等奖,表示8等奖
	$sum = 0;
	for($i=0; $i<8; $i++)
	{
		$sum += $prob[$i];
		if($sum>=$win_rand) {
			$win_index = $i;
			break;
		}
	}
	
	for($i=$win_index; $i<8; $i++)//保证能获得奖
	{
		$win_record = $records[$i];
		$win_record_count = count($win_record);
		
		if($win_record_count == 0) 
			continue;
		else{
			$win_record_index = mt_rand(0, $win_record_count-1);
			
			if($win_record[$win_record_index]['gid']!=null){
				$win_id = $win_record[$win_record_index]['gid'];
				$win_type=0;
				$win_count = $win_record[$win_record_index]['count'];
			}
			else{
				$win_id = $win_record[$win_record_index]['id'];
				$win_type=1; //装备
				//$win_count = $win_record[$win_record_index]['count'];
			}
			if($last_win_id==$win_id && $last_win_type==$win_type)
				continue;
			else
				break;
		}
	}
	
	if($last_win_id==$win_id && $last_win_type==$win_type){ //保证要获奖
		for($i=7; $i>=0; $i--){
			$win_record = $records[$i];
			$win_record_count = count($win_record);
			if($win_record_count == 0) 
				continue;
			
			else{
				$win_record_index = mt_rand(0, $win_record_count-1);
				
				if($win_record[$win_record_index]['gid']!=null){
					$win_id = $win_record[$win_record_index]['gid'];
					$win_type=0;
					$win_count = $win_record[$win_record_index]['count'];
				}
				else{
					$win_id = $win_record[$win_record_index]['id'];
					$win_type=1; //装备
					//$win_count = $win_record[$win_record_index]['count'];
				}
				if($last_win_id==$win_id && $last_win_type==$win_type)
					continue;
				else
					break;
			}
		}
	}
	if($is_restart_req == true)
		sql_query("update mem_lottery_goods set win='$win_type,$win_id,$win_count', `restart_count`=`restart_count`+1 where uid=$uid");
	else
		sql_query("update mem_lottery_goods set win='$win_type,$win_id,$win_count' where uid=$uid");
	$ret = array();
	$ret[] = $win_type;
	$ret[] = $win_id;
	$ret[] = $win_count;
	return $ret;
}

function logIDs($rs)
{
	$ret = "";
	
	$count = $rs['count'];
	if($rs['gid']!=null){
		$gid = $rs['gid'];
		$ret = $ret."0,$gid,$count";
	}
	else{
		$id = $rs['id'];
		$ret = $ret."1,$id,$count";
	}
	
	return $ret;
}

function retrieveGoods($ids)
{
	$ret = array(8); //数组的数组
	for($i=0; $i<8; $i++)
		$ret[$i] = array();
	$id_ary = explode(",", $ids);
	for($i=0; $i<count($id_ary); $i+=3)
	{
		$type = $id_ary[$i];
		$id = $id_ary[$i+1];
		$count = $id_ary[$i+2];
		if($type==1){ //装备{
			$goods = sql_fetch_one("select * from cfg_armor where id=$id");
		}
		elseif($type==0){
			$goods = sql_fetch_one("select * from cfg_goods where gid=$id");
		}
		$goods['count'] = $count;
		$ret[intval($goods['level'])-1][] = $goods;
	}
	return $ret;
}

function  getAllLevelGoods() // 1~8级别
{
	$ret = array();
	$total = 0;
	//1等
	$count = mt_rand(0,1);
	$records1 = getGoodsByType($count, 1, $total);
	$total += count($records1);

	//2等
	$count = mt_rand(0,1);
	$records2 = getGoodsByType($count, 2, $total);
	$total += count($records2);
	
	//3等
	$count = mt_rand(0,1);
	if($total==0)
		$count = 1;
	$records3 = getGoodsByType($count, 3, $total);
	$total += count($records3);
	
	//7等
	$count = mt_rand(1,2);
	$records7 = getGoodsByType($count, 7, $total);
	$total += count($records7);
	
	//8等
	$count = mt_rand(1,2);
	$records8 = getGoodsByType($count, 8, $total);
	$total += count($records8);
	
	//4等
	$count = mt_rand(0,2);
	$records4 = getGoodsByType($count, 4, $total);
	$total += count($records4);
	
	//5等
	$count = mt_rand(0,2);
	$records5 = getGoodsByType($count, 5, $total);
	$total += count($records5);
	
	//6等
	$count = mt_rand(0,2);
	if($total+$count < 8)
		$count = 8-$total; //保证8个
	$records6 = getGoodsByType($count, 6, $total, 1);
	$total += count($records6);
	
	$ret[] = $records1; $ret[] = $records2; $ret[] = $records3;$ret[] = $records4;
	$ret[] = $records5;$ret[] = $records6; $ret[] = $records7;$ret[] = $records8;
	return $ret;
}

function getGoodsByType($count, $level, $total, $is_last=0)
{
	$ret = array();
	if($total+$count > 8)
		$count = max(0, 8-$total);
	
	for($i=0; $i<$count; $i++)
	{
		$type = randType();
		if($level == 8 || $level == 7)
			$type = 1;
		$record = "";
		switch($type){
			case 0:
				$record = sql_fetch_one("select * from cfg_goods where gid<10000 and `group` in (4, 5) and `level`=$level order by rand() limit 1");
				if($record==false) break;
				$record['count'] = intval( (mt_rand(0, 30)/100) * (intval($record['level'])-1) + 1 );
				break;
			case 1:
				$record = sql_fetch_one("select * from cfg_goods where gid<10000 and `group` in (0,1,2,3) and `level`=$level order by rand() limit 1");
				if($record==false) break;
				$record['count'] = 1;
				break;
			case 2:
				$record = sql_fetch_one("select * from cfg_armor where `level`=$level order by rand() limit 1");
				if($record==false) break;
				$record['count'] = 1;
				break;
			case 3:
				$record = sql_fetch_one("select * from cfg_goods where gid=0");
				$record['count'] = intval(600/pow($level, 2));
				break;
		}
		if($record!=false)
			$ret[] = $record;
	}
	if($is_last){
		if($total + count($ret) < 8)
		{
			$left = 8 - $total - count($ret);
			for($i=0; $i<$left; $i++){
				$record = sql_fetch_one("select * from cfg_goods where gid<10000 and `group` in (0,1,2,3) and `level`=6 order by rand() limit 1");
				$record['count'] = 1;
				$ret[] = $record;
			}
		}
	}
	return $ret;
}

function randType()
{
	$rand = mt_rand(1, 100);
	if($rand<= 50)
		return 0; //材料
	else if($rand>50 && $rand<=50+25)
		return 1; //道具
	else if($rand>50+25)
		return 2; //装备
//去除礼金 cxy 2009.10.29
//	else 
//		return 3; //礼金
}

function getWin($uid, $type, $id, $count)
{
//	if(getTodayCount($uid)>1){ //其实是20次，因为转的时候就加了一次，领的时候在21次时候进行判断
//		throw new Exception($GLOBALS['lottery']['full_playcount']);
//	}
	//$type = array_shift($param);
	//$id = array_shift($param);
	//$count = array_shift($param);
	$winObj = NULL;
	if($id == 0 && $type==0)//礼金
	{
		$winObj = sql_fetch_one("select * from cfg_goods where gid='$id'");
		if(empty($winObj))
			throw new Exception($GLOBALS['lottery']['no_such_goods']);
		 addGift($uid,$count,1000);
	}
	elseif($type == 1){
		$winObj = sql_fetch_one("select * from cfg_armor where id='$id'");
		if(empty($winObj))
			throw new Exception($GLOBALS['lottery']['no_such_armor']);
		$hp = $winObj['ori_hp_max'];
		//sql_query("insert into sys_user_armor (uid,armorid,hp, hp_max, hid) values ('$uid','$id',$hp*10, '$hp', 0)");
		
		//sql_query("insert into log_goods (uid,gid,count,time,type) values ('$uid',$id,1,unix_timestamp(),101)");
		addArmor($uid,$winObj,1,1000);
		
	}
	elseif($type == 0){
		$winObj = sql_fetch_one("select * from cfg_goods where gid='$id'");
		if(empty($winObj))
			throw new Exception($GLOBALS['lottery']['no_such_goods']);
		
		addGoods($uid,$id,$count,1000);
		//sql_query("insert into sys_goods(uid, gid, `count`) values($uid, $id, $count) on duplicate key update `count`=`count`+$count");
		//sql_query("insert into log_goods (uid,gid,count,time,type) values ('$uid',$id,$count,unix_timestamp(),100)");
		
	}
	else{
		throw new Exception($GLOBALS['lottery']['no_such_goods']);
	}
	//throw new Exception($GLOBALS['lottery']['get_win']);
	
	sql_query("update mem_lottery_goods set got=1 where uid=$uid");
	//发公告
	sendSysInformHere($uid, $type, $id, $count);
	
	$winObj["count"] = $count;
	return $winObj;
	
	
}

function sendSysInformHere($uid, $type, $id, $count=1)
{
	if($type == 1){
		$arminfo = sql_fetch_one("select * from cfg_armor where id='$id'");
		if(!empty($arminfo) && $arminfo['level']<=5){
			$name = sql_fetch_one_cell("select name from sys_user where uid=$uid");
			
			$msg = sprintf($GLOBALS['lottery']['inform_win_goods'], $name, $arminfo['name']);
			sql_query("insert into sys_inform (type,inuse,starttime,endtime,`interval`,scrollcount,color,msg) values (0,1,unix_timestamp(),unix_timestamp()+59,0,1,49151,'$msg')");
		}
	}
	elseif($type == 0){
		$goods = sql_fetch_one("select * from cfg_goods where gid='$id'");
		if(!empty($goods) && $goods['level']<=5){
			$name = sql_fetch_one_cell("select name from sys_user where uid=$uid");
			$goodname = $goods['name'];
			if ($count>0) 
			{
				$goodname=$goods['name']."*".$count;
			}  
			$msg = sprintf($GLOBALS['lottery']['inform_win_goods'], $name, $goodname);
			sql_query("insert into sys_inform (type,inuse,starttime,endtime,`interval`,scrollcount,color,msg) values (0,1,unix_timestamp(),unix_timestamp()+59,0,1,49151,'$msg')");
		}
	}
}

function restart($uid, $param)
{
	$openLottery = sql_fetch_one_cell("select value from mem_state where state = 150");
	if(empty($openLottery) || $openLottery == "" || $openLottery == false)
	{
		$openLottery = 0;
	}
	if($openLottery != 0) {
		throw new Exception($GLOBALS['lottery']['not_available']);
	}
	checkLottoryTime();
	$win_id = array_shift($param);
	$win_type = array_shift($param);
	$records = array_shift($param);
	$infor = sql_fetch_one("select * from mem_lottery_goods where uid=$uid and got=0");
	if(empty($infor)){
		throw new Exception($GLOBALS['waigua']['invalid']);
	}
	if($infor['restart_count'] >= 1){
		throw new Exception($GLOBALS['lottery']['restart_limit']);
	}
	
	$records = retrieveGoods($infor['records']);
	$ret = randWin($uid, $records, true, $win_id, $win_type);
	return $ret;
}

//增加一次开奖记录
function addCount($uid)
{
	$openLottery = sql_fetch_one_cell("select value from mem_state where state = 150");
	if(empty($openLottery) || $openLottery == "" || $openLottery == false)
	{
		$openLottery = 0;
	}
	if($openLottery != 0) {
		throw new Exception($GLOBALS['lottery']['not_available']);
	}
	checkLottoryTime();
	/*$type = array_shift($param); //1装备，0道具或材料
	$gid = array_shift($param);
	$count = array_shift($param);
	*/
	
	$rec = sql_fetch_one("select * from mem_lottery_goods where uid=$uid and got=0");
	
	$win_str = $rec["win"];
	$ary = explode(",", $win_str);
	$type = $ary[0];
	$gid = $ary[1];
	$count = $ary[2];
	
	$winObj = getWin($uid, $type, $gid, $count);
	completeTaskWithTaskid($uid, 293);
	//sql_query("update mem_lottery_goods set got=1, `count`=`count`+1 where uid=$uid");
	return $winObj;
}

function checkLotteryMoney($uid)
{
	$use_money = 6; //消耗10个元宝
	$money = sql_fetch_one_cell("select money from sys_user where uid=$uid");
	if(empty($money) || $money<$use_money){
		//throw new Exception($GLOBALS['lottery']['no_money']);
		return false;
	}
	
	return true;
	 
}

function useMoney($uid, $count)
{
	if($count > 1){
		$use_money = 6; //消耗10个元宝
		$ret = array();
		$money = sql_fetch_one_cell("select money from sys_user where uid=$uid");
		if(empty($money) || $money<$use_money){
			$ret[] = 0;
			return $ret;
			//throw new Exception($GLOBALS['lottery']['no_money']);
		} 
		sql_query("update sys_user set money=money-$use_money where uid=$uid");
	}
	$ret[] = 1;
	return $ret;
}

function specialProp($uid)
{
	$prob = array(10, 190, 300, 500, 1000, 1500, 2500, 4000);
	$logs = sql_fetch_rows("select * from log_lottery l left join cfg_goods c on l.gid=c.gid where uid=$uid and to_days(`time`)=TO_DAYS(NOW()) order by `time` desc");
	$count = count($logs);
	if($count%7==6){
		$has_level_better_6 = false;
		for($i=0; $i<6; $i++){
			if($logs[$i]['level']<6){
				$has_level_better_6 = true;
				break;
				//$prob = array(10, 190, 300, 500, 9000, 0, 0, 0);
			}
		}
		if ($has_level_better_6 == false)
			$prob = array(10, 190, 300, 500, 9000, 0, 0, 0);
	}
	else if($count%4==3){
		$has_level_better_7 = false;
		for($i=0; $i<3; $i++){
			if($logs[$i]['level']<7){
				$has_level_better_7 = true;
				break;
			}
		} 
		if($has_level_better_7==false)
			$prob = array(10, 190, 300, 500, 1000, 8000, 0, 0);
	}
	return $prob;
}
/*
for($k=0; $k<500; $k++){
$tt = getAllLevelGoods();
for($i=0; $i<count($tt); $i++){
	$itm = $tt[$i];
	$level = $i+1;
	echo "<<<<<<<<< Level ".$level." >>>>>>>>>>>>>>\n";
	for($j=0; $j<count($itm); $j++){
		$unit = $itm[$j];
		echo $unit['name']."\n";
	}
}
}*/
function checkLottoryTime(){
		$mtime=sql_fetch_one_cell("select now()");
		$tstamp=strtotime($mtime);
		$time=date("H",$tstamp);
		//if($time==23||$time==24){
//		if($time==13||$time==14){//for test
			//return ;
		//}else{
			throw new Exception($GLOBALS['lottery']['not_available_time']);
		//}
}
?>