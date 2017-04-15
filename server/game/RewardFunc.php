<?php 
require_once("./ActFunc.php");

function getLoginReward($uid,$param){	
	//include_once 'GoodsFunc.php';		
	//include_once 'TaskFunc.php';	
	return;
	$rewardtype=array_shift($param);
	//登陆类型不存在处理
	if($rewardtype<=0 || $rewardtype>4){
		throw new Exception($GLOBALS['login_reward']['no_reward_type']);
	}
	$cid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
	$nobility=sql_fetch_one_cell("select nobility from sys_user where uid=$uid");
	
	if ($nobility<1) //公士以上才可领取
		throw new Exception($GLOBALS['login_reward']['not_enough_nobility']);
		
	$date = sql_fetch_one_cell("select date(now())");	
	if ($rewardtype==4){ //充值后可领取
		$user=sql_fetch_one("select * from sys_user where uid='$uid'");	
		$passtype = $user["passtype"];
		$passport = $user["passport"];
		$today_in_money=sql_fetch_one_cell("select sum(money) from pay_log where passport='$passport' and passtype='$passtype' and money >0 and date(from_unixtime(time))='$date'");
		if ($today_in_money<=0)		
			throw new Exception($GLOBALS['login_reward']['today_no_pay']);
	}

	
	$row=sql_fetch_one("select a.id as rewardid,a.goal_id,a.gettime,b.*,b.name as getname from sys_user_login_reward a,cfg_box_details b where a.boxdetail_id =b.id and  uid = $uid and date='$date' and rewardtype= $rewardtype ");
	if (empty($row)) {
		getLoginInfo($uid,array());
		$row=sql_fetch_one("select a.id as rewardid,a.goal_id,a.gettime,b.*,b.name as getname from sys_user_login_reward a,cfg_box_details b where a.boxdetail_id =b.id and  uid = $uid and date='$date' and rewardtype= $rewardtype ");
	}
	$id =$row["rewardid"];
	if ($row["gettime"]>0)
		throw new Exception($GLOBALS['login_reward']['today_has_get']);
	
	if($row['srctype']==10){//登录活动ACT
		$srctype = 10;
	}else{
		$srctype=100+$rewardtype-1;
	}
	$srcid = $row["srcid"];
	$sort = $row["sort"];
	$type = $row["type"];		
	$count = $row["count"];	
	$goal_id = $row["goal_id"];
	if ($goal_id>0){
		$goal = sql_fetch_one("select * from cfg_login_reward_goal where id =$goal_id");
		if (checkGoalComplete($uid,$goal)==false){
			if ($rewardtype==3)
				throw new Exception($GLOBALS['sys']['not_enough_money']);
			else
				throw new Exception($GLOBALS['login_reward']['no_enough_thing']);
		}
	}	
	openDefaultGoodsNow($uid,$cid,$srcid,$srctype,$sort,$type,$count);
	if ($goal_id>0)		
		reduceGoal($uid,$cid,$goal,55,55);	
	
	sql_query("update sys_user_login_reward set gettime=unix_timestamp() where  id = $id ");
	$ret = array();
	$ret[]=$rewardtype;
	$ret[]=$row["getname"];	
	return $ret;
}


function getLoginInfo($uid,$param){
	include_once 'ReportFunc.php';
	include_once 'GoodsFunc.php';
	$user=sql_fetch_one("select * from sys_user where uid='$uid'");
	$ret=getLoginAnnounce($uid,$param); //登陆信息
	$ret[] = getSignState($uid, null);
	return $ret;
//	$passtype = $user["passtype"];
//	$passport = $user["passport"];
//	
//	$nobility=sql_fetch_one_cell("select nobility from sys_user where uid=$uid");
//	$ret[]=$nobility;//爵位
//	//if ($nobility>=1){ //公士以上才可领取
//		$date = sql_fetch_one_cell("select date(now())");
//		$today_first_login=false;
//		if ("0"==sql_fetch_one_cell("select count(1) from sys_user_login_reward where uid = $uid and date='$date' "))
//			 $today_first_login=true;
//		if ($today_first_login){
//			for($rewardtype=1;$rewardtype<=4;$rewardtype++){
//				$srctype=100+$rewardtype-1;
//				$srcid = 1;
//				if ($srctype==100){
//					if (mt_rand(1,100) <= (20+($nobility-1)*3))//获得道具
//						$srcid=2;					
//				}
//				$row=getOpenDefaultGoodsResult($uid,$srctype,$srcid);	   				
//				$boxdetail_id =$row['id'];				
//				$goal_id=sql_fetch_one_cell("select id from cfg_login_reward_goal where rewardtype = $rewardtype order by rand() limit 1");
//				if($goal_id=="") $goal_id=0;
//				sql_query("insert into sys_user_login_reward (uid,rewardtype,date,time,boxdetail_id,goal_id) values ($uid,$rewardtype,'$date',unix_timestamp(),$boxdetail_id,$goal_id)");
//			}
//			//活动ACT Begin
//			checkAndDoLoginAct($uid,$date);//登录活动 5元宝和充值赠送可能更新为活动物品
//			//活动ACT End
//			
//		}
//			for($rewardtype=1;$rewardtype<=4;$rewardtype++){
//				$goal_id=sql_fetch_one_cell("select goal_id from sys_user_login_reward where rewardtype = $rewardtype and uid = $uid order by time desc limit 1");
//				if($goal_id=="") $goal_id=0;
//				if($rewardtype == 2) {
//					$goal = sql_fetch_one("select * from cfg_login_reward_goal where id =$goal_id");
//					$type2finish = checkGoalComplete($uid,$goal);					
//				} else if($rewardtype == 3) {
//					$goal = sql_fetch_one("select * from cfg_login_reward_goal where id =$goal_id");
//					$type3finish = checkGoalComplete($uid,$goal);
//				}
//			}			
//		$ret[]=sql_fetch_one_cell("select sum(money) from pay_log where passport='$passport' and passtype='$passtype' and date(from_unixtime(time))='$date' ");	//今日充值数额
//		$rows=sql_fetch_rows("select a.*,b.*,b.name as  getname,c.name as goalname from sys_user_login_reward a left join cfg_box_details b on a.boxdetail_id = b.id left join cfg_login_reward_goal c on a.goal_id = c.id where uid = $uid and date='$date' order by a.rewardtype ");
//		$cid = sql_fetch_one_cell("select lastcid from sys_user where uid='$uid'");
//		$ret[]=$rows;			
//		$government_level=0;
//		if ("1"==sql_fetch_one_cell("select count(1) from sys_goods where uid = $uid and gid=50101")){			
//			$government_level = sql_fetch_one_cell("select level from sys_building where cid='$cid' and bid=".ID_BUILDING_GOVERMENT);
//		}
//		$ret[]=$government_level;
//		
//		$ret[]= sql_fetch_rows("select *,name as getname from cfg_box_details where srctype=102 and srcid='1'");
//		$ret[]= sql_fetch_rows("select *,name as getname from cfg_box_details where srctype=103 and srcid='1'");
//		
//		$new_pack_number= sql_fetch_one_cell("select gid-50100 from sys_goods where uid = $uid and count=1 and  gid between 50101 and 50110 LIMIT 1");
//		if (empty($new_pack_number)) $new_pack_number =0;
//		$ret[]=$new_pack_number;
//	//}	
//	$ret[] = $type2finish;
//	$ret[] = $type3finish;
}

function finishOpenAnnounce($uid, $param)
{
	completeTaskWithTaskid($uid,292);
}


?>