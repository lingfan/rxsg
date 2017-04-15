<?php
require_once ("interface.php");
require_once ("BattleFunc.php");
class ShaChang {
	/*
	 * 
	 * 查看军队详情
	 */
	function scViewTroop($tid) {
		return null;
		$troop = sql_fetch_one ( "select * from sys_sc_troops where id=$tid" );
		$uid = $troop ['uid'];
		$hid = $troop ['hid'];
		$username = sql_fetch_one_cell ( "select name from sys_user where uid=$uid" );
		$unionname = sql_fetch_one_cell ( "select name from sys_union where id=(select union_id from sys_user where uid=$uid limit 1) limit 1" );
		if(empty($unionname)){
			$unionname="";
		}
		$hero = sql_fetch_one ( "select hid,level,name from sys_city_hero where hid=$hid" );
		$ret = array ('uid' => $uid, 'name' => $username, 'unionname' => $unionname, 'heroinfo' => $hero, 'soldiers' => $troop ['soldiers'] );
		return $ret;
	}
	/*
	 * 
	 * 查看军队列表
	 */
	function getTroopList($tid) {
		return null;
		$uid = getUID ();
		$this->checkRemain($uid);
		$sccount = sql_fetch_one_cell ( "select remain from sys_sc_user where uid=$uid" );
		if($sccount===false){
			$sccount=5;
		}

//		if (empty ( $sccount )) {
//			$sccount = 5;
//		}
		$ret = array ();
		//$ret [] = strtotime ( 'now' );
		$ret [] = sql_fetch_one_cell("select value-unix_timestamp() from mem_state where state=28");
		$ret [] = 5-$sccount;
		$ret [] = $this->hasTroop ( $uid );
		$win=sql_fetch_one_cell("select win from sys_sc_user where uid=$uid");
		if(empty($win)){
			$win=0;
		}
		$ret[]=$win;
		if ($tabIndex == 0) { //普通区
			$otroops=sql_fetch_rows ( "select sys_sc_waiting_queue.state, sys_user.name as uname,sys_user.uid,sys_city_hero.name as hname,sys_city_hero.level,sys_city_hero.hid,sys_sc_troops.id,sys_sc_troops.battleid from `sys_sc_waiting_queue` left join sys_sc_troops on sys_sc_troops.uid=sys_sc_waiting_queue.uid left join sys_user on sys_user.uid=sys_sc_troops.uid left join sys_city_hero on sys_city_hero.hid=sys_sc_troops.hid where sys_sc_troops.uid<>$uid  order by sys_sc_waiting_queue.id asc" );
			$mtroops=sql_fetch_rows ( "select sys_sc_waiting_queue.state, sys_user.name as uname,sys_user.uid,sys_city_hero.name as hname,sys_city_hero.level,sys_city_hero.hid,sys_sc_troops.id,sys_sc_troops.battleid from `sys_sc_waiting_queue` left join sys_sc_troops on sys_sc_troops.uid=sys_sc_waiting_queue.uid left join sys_user on sys_user.uid=sys_sc_troops.uid left join sys_city_hero on sys_city_hero.hid=sys_sc_troops.hid where sys_sc_troops.uid=$uid  order by sys_sc_waiting_queue.id desc" );
			$ret [] = array_merge($mtroops,$otroops);
		} else {
			$ret [] = array ();
		}
		return $ret;
	}
	//配兵-获取玩家的所有配兵方案，单击配兵时弹出的数据请求。
	function getScSoldies() {
		return null;
		$uid = getUID ();
		return sql_fetch_rows ( "select * from sys_sc_soldier where uid=$uid order by `type`" );
	}
	/**
	 * 
	 * 保存方案
	 *
	 * @param unknown_type $param
	 * @return unknown
	 */
	function saveScheme($param) {
		return null;
		$uid = getUID ();
		$type = $param ['type'];
		$soldiers = $param ['soldiers'];
		
		//检查过来的配兵方案是否符合条件。
		$flag = $this->checkScSoldiers ( $soldiers );
		if (! $flag) {
			throw new Exception ( $GLOBALS['shachange']['toomanysoldier'] );
		}
		//保存兵力到相应的配兵方案中
		if (sql_check ( "select 1 from sys_sc_soldier where uid=$uid and type=$type" )) {
			sql_query ( "update sys_sc_soldier set soldiers='$soldiers' where uid=$uid and type=$type" );
		} else {
			sql_query ( "insert into sys_sc_soldier(`uid`,`type`,`soldiers`) values($uid,$type,'$soldiers')" );
		}
	}
	/**
	 * 
	 * 修改方案
	 *
	 * @param unknown_type $param
	 * @return unknown
	 */
	function changeScheme($param) {
		return null;
		$uid = getUID ();
		$hid = $param ['hid'];
		$soldiers = $param ['soldiers'];
			//检查过来的配兵方案是否符合条件。
		$flag = $this->checkScSoldiers ( $soldiers );
		if($this->getStartTime()===false){
			throw new Exception ($GLOBALS['shachange']['enter_7']);
		}
		if (! $flag) {
			throw new Exception ( $GLOBALS['shachange']['toomanysoldier'] );
		}
		$state = sql_fetch_one_cell ( "select state from sys_sc_waiting_queue where uid=$uid" );
		if($state===false){
			return $this->doCreateScheme($uid,$hid,$soldiers);
		}else if($state==0){
			return $this->doChangeScheme($uid,$hid,$soldiers);
		}else{
			throw new Exception ($GLOBALS['shachange']['war_doing'] );
		}

	}
	private function doChangeScheme($uid,$hid,$soldiers){
		return null;
		$ret = array ();
		$changetype = $GLOBALS['shachange']['change_success']; //默认为修改队列
		$code = 1;
		sql_query ( "update sys_sc_troops set soldiers='$soldiers',hid='$hid' where uid='$uid'" );
		$ret [] = $code;
		$ret [] = $changetype;
		return $ret;
	}
	private function doCreateScheme($uid,$hid,$soldiers){
		return null;
//		$maxcount = 20000;
		$freecount = 5;
		$maxtroopcount = 200;
		
		//检查玩家是否在沙场系统中，如果不在，为玩家创建一个账号。
		if (! sql_check ( "select 1 from sys_sc_user where uid=$uid" )) {
			sql_query ( "INSERT INTO `sys_sc_user` (`uid`, `point`, `remain`) VALUES ($uid, 0, $freecount)" );
		}
//		$gcount = sql_fetch_one_cell ( "select `count` from sys_sc_user where uid=$uid" );
		$rcount = sql_fetch_one_cell ( "select `remain` from sys_sc_user where uid=$uid" );
		$ret = array ();
		$changetype = $GLOBALS['shachange']['create_success']; //默认为修改队列
		$code = 1;
		$hstate=sql_fetch_one_cell("select state from sys_city_hero where hid=$hid and uid=$uid");
		if($hstate===false){
			throw new Exception ($GLOBALS['sureSummonHero']['hero_not_exist']);
		}elseif ($hstate!=0){
			throw new Exception ($GLOBALS['StartTroop']['hero_is_busy']);
		}
		
		//检测参加次数
		if ($rcount===false) {
		}else{
			if ($rcount <= 0) {
				$code = 0;
				$changetype = $GLOBALS['shachange']['enter_1']. $freecount . $GLOBALS['shachange']['enter_2'];
				$ret [] = $code;
				$ret [] = $changetype;
				return $ret;
			}
		}
		$this->scEnroll($uid);
		$nextstarttime=$this->getNextTime();
		$starttime=time();
		$pathtime=$nextstarttime-$starttime;
		$cid=getCID();
		sql_query ( "update sys_sc_troops set soldiers='$soldiers',hid='$hid' where uid='$uid'" );
//		sql_query ( "update sys_troops set cid=$cid, soldiers='$soldiers',hid='$hid',`arrive_time`=$nextstarttime,`endtime`=$nextstarttime, `starttime`=$starttime,`pathtime`=$pathtime,`state`=0,`task`=1  where uid='$uid'" );
		sql_query ( "update sys_sc_user set `remain`=greatest(`remain`-1,0) where uid=$uid" );
		$this->checkRemain($uid);
		$ret [] = $code;
		$ret [] = $changetype;
		return $ret;
	}
	
	//报名
	private function scEnroll($uid) {
		return null;
		//让玩家加入到队列中
		if (! sql_check ( "select 1 from sys_sc_waiting_queue where uid=$uid" )) {
			sql_query ( "insert into sys_sc_waiting_queue (`uid`,`state`) values('$uid',0) on duplicate key update  state=0" );
		} else {
			sql_query ( "update sys_sc_waiting_queue set state=0 where uid=$uid  on duplicate key update state=0" );
		}
		//给玩家军队
		if (! sql_check ( "select 1 from sys_sc_troops where uid=$uid" )) {
			sql_query ( "insert into sys_sc_troops (`uid`,`hid`,`soldiers`,`battleid`) values('$uid',0,'',0)" );
//			sql_query ( "insert into `sys_troops` (`uid`,`hid`,`soldiers`,`battleid`,`battlefieldid`,`endtime`) values('$uid',0,'',0,10001,unix_timestamp()+2000)");
		} else {
			sql_query ( "update sys_sc_troops set hid=0,soldiers='',battleid=0 where uid=$uid" );
		}
		sql_query("insert into sys_things(tid,uid,count)values(104001,$uid,0) on duplicate key update `count`=`count`");
	}
	//退出
	function scQuit($tid) {
		return null;
		$uid = getUID ();
		$state = sql_fetch_one_cell ( "select state from sys_sc_waiting_queue where uid=$uid" );
		if($state==1){
			throw new Exception ( $GLOBALS['shachange']['war_doing'] );
		}
		sql_query ( "delete from sys_sc_waiting_queue where uid=$uid" );
		sql_query ( "delete from sys_sc_troops where uid=$uid" );
//		sql_query ( "delete from sys_troops where uid=$uid and battlefieldid=10001" );
		sql_query ( "update sys_sc_user set `remain`=`remain`+1 where uid=$uid" );
		$this->checkRemain($uid);
//		$this->insertTestData();
	}
	function insertTestData(){
		for($i=6;$i<36;$i++){
			$this->doCreateScheme($i,$i,"1,2,222");
			sql_query("insert into test_passport(passport)values($i)");
		}
	}
	/**
	 * 
	 * 领取奖励
	 *
	 */
	function getReceive() {
		include_once 'TaskFunc.php';	
		$uid = getUID ();
		$dhc = 4;
		$win = sql_fetch_one_cell ( "select `win` from sys_sc_user where uid=$uid" ); //赢取的次数
		$goodscount = floor ( $win / $dhc ); //每三场胜利兑换一个沙场令
		if (empty ( $goodscount )) {
			throw new Exception ($GLOBALS['shachange']['enter_3']);
		}
		$ratetotal=sql_fetch_one_cell("select count(rate) from cfg_box_details where id between 593 and 629");
		$goods=sql_fetch_rows("select sort,type,rate,name,`count`,inform from cfg_box_details where id between 593 and 629");
		$rnum=rand(0,$ratetotal);
		$ratetoo=0;
		$selectedindex=0;
		for($i=0;$i<sizeof($goods);$i++){
			$ratetoo+=$goods[$i]['rate'];
			if ($rnum<$ratetoo){
				$selectedindex=$i;
				break;
			}
		}
		$reward=$goods[$selectedindex];
		$oldcount=$reward['count'];
		$oldname=$reward['name'];
		if($oldcount>1){
			$oldname=str_replace($oldcount,"",$oldname);
		}
		$oldcount=$oldcount*$goodscount;
		$reward['count']=$oldcount;
		giveReward($uid,getCID(),$reward);
		$msg = sprintf($GLOBALS['shachange']['getrewar'],$oldname,$oldcount);
		$reduce = $dhc * $goodscount;
//		$ret = sql_fetch_rows ( "select *,'$goodscount' as count from cfg_goods where gid=164" );
		sql_query ( "update sys_sc_user set win=win-$reduce where uid=$uid" );
//		$has = sql_fetch_one_cell ( "select `count` from sys_goods where uid=$uid and gid=164" );
//		if ($has === false) {
//			sql_query ( "insert into sys_goods(uid,gid,count)values($uid,164,$goodscount)" );
//		} else {
//			sql_query ( "update sys_goods set count=count+$goodscount where gid=164 and uid=$uid" );
//		}
		$ret=array();
		$win=sql_fetch_one_cell("select win from sys_sc_user where uid=$uid");
		if(empty($win)){
			$win=0;
		}
		if(!empty($reward['inform'])){
			$uname=sql_fetch_one_cell("select name from sys_user where uid='$uid'");
			$fmsg=sprintf($GLOBALS['shachange']['getrewar_inform'],$uname,$oldname,$oldcount);
			sendSysInform(0,1,0,300,1800,1,49151,$fmsg);
		}
		$ret[]=$win;
		$ret[]=$msg;
		return $ret;
	}
	/*
	 * 加载商品列表
	 */
	function loadSCGoods() {
		$uid = getUID ();
		$ret = array ();
		$ret [] = sql_fetch_rows ( "select cfg_sc_goods.price,cfg_goods.* from cfg_sc_goods left join cfg_goods on cfg_sc_goods.gid=cfg_goods.gid" );
		$ret [] = sql_fetch_one_cell ( "select point from sys_sc_user where uid=$uid" );
		return $ret;
	}
	function exchangeScore($arr) {
		$gid = array_shift ( $arr );
		$count = array_shift ( $arr );
		$uid = getUID ();
		$good = sql_fetch_one ( "select * from cfg_sc_goods where gid=$gid" );
		$pointneed = $count * $good ['price'];
		$point = sql_fetch_one_cell ( "select point from sys_sc_user where uid=$uid" );
		if ($pointneed > $point) {
			throw new Exception ( $GLOBALS['shachange']['enter_4'] );
		}
		sql_query ( "update sys_sc_user set point=greatest(0,`point`-$pointneed) where uid=$uid" );
		addGoods($uid,$gid,$count,1);
		//sql_query ( "insert into sys_goods(uid,gid,count) values($uid,$gid,$count) on duplicate key update count=count+$count" );
		return 1;
	}
	/**
	 * 
	 * 获得战场的状态，是开启还是排队
	 *
	 */
	function getNextTime(){
		return null;
		$time=sql_fetch_one_cell("select value from mem_state where state=28");
		return $time;
	}

	private function checkScSoldiers($soldiers) {
		return null;
		if (empty ( $soldiers ))
			return true;
		$soldierArray = explode ( ",", $soldiers );
		$numSoldiers = array_shift ( $soldierArray );
		$soldierAllCount = 0;
		for($i = 0; $i < $numSoldiers; $i ++) {
			$sid = array_shift ( $soldierArray );
			$count = array_shift ( $soldierArray );
			if ($count < 0)
				$count = 0;
			$soldierAllCount += sql_fetch_one_cell ( "select people_need*$count from cfg_soldier where sid=$sid" );
		}
		if ($soldierAllCount > 1000000) {
			return false;
		} else {
			return true;
		}
	}
	private function hasTroop($uid) {
		return null;
		$ret = sql_fetch_one_cell ( "select id from `sys_sc_troops` where uid=$uid limit 1" );
		if (empty ( $ret )) {
			return false;
		} else {
			return true;
		}
	}
	public function getStartTime(){
		return null;
		$mtime=sql_fetch_one_cell("select now()");
		$tstamp=strtotime($mtime);
		$time=date("H",$tstamp);
//		return true;//测试用
		if($time>=11&&$time<15){
			return true;
		}
		if($time>=19&&$time<24){
//		if($time>=16&&$time<18){
			return true;
		}
		if($time>=0&&$time<1){
			return true;
		}
		return false;
	}
	/**
	 * 
	 * 修正玩家还有多少次bug
	 *
	 * @param unknown_type $uid
	 */
	function checkRemain($uid){
		return null;
		$sccount = sql_fetch_one_cell ( "select remain from sys_sc_user where uid=$uid" );
		if($sccount<0){
			$sccount=0;
			sql_query("update sys_sc_user set remain='$sccount' where uid='$uid'");
		}
		if($sccount>5){
			$sccount=5;
			sql_query("update sys_sc_user set remain='$sccount' where uid='$uid'");
		}
	}	
}
?>