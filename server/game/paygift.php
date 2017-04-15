<?php 

require_once("./utils.php");
require_once './TaskFunc.php';

$uid = $user['uid'];
$passport= sql_fetch_one_cell("select passport from sys_user where uid=$uid limit 1");	
$now = sql_fetch_one_cell("select unix_timestamp()");
$openTime = sql_fetch_one_cell("select value from mem_state where state=6");//开服时间
$softsign = sql_check("select value from mem_state where state=98 and value=1");//软合服标志
$lastsTime = 3600*24*30;//1个月
$endtime=$openTime+$lastsTime;
$mailtitle = '充值活动奖励';
/**********************************************普通开服活动Begin***************************************************/
if(!$softsign){ //软合服 不上开服活动
	//活动1：首充100		
	if ($now <=$openTime+$lastsTime)				
	{
		$payCount = sql_fetch_one_cell("select count(1) from pay_log where passport='$passport' and time <= $endtime");
		if($payCount<=1 && $money>=100){
		
	        giveGoods($uid,166,1,6); 
	        giveGoods($uid,86,1,6);    
	        $giftnameStr='【高级徭役令*1,金砖*1】';
			$mailcontent = "尊敬的玩家：\n\n       感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动内容：\n\n首次充值，且一次性充值满100元宝 高级徭役令*1个, 金砖1\n\n首次充值，且一次性充值满1000元宝。 金砖1，珠宝盒*2，墨家残卷*3、墨家图纸*2、墨家宝典*1、木牛流马*2、免战牌*1、鲁班残页*3、考工记精编*1、鲁班草图、黄金礼匣*1、黄金钥匙*1\n\n首次充值，且一次性充值满3000元宝 金砖*1，高级热血铠甲*1、高级热血长枪*1、高级热血肩甲*1、高级热血腰甲*1、高级热血护腕*1、高级热血战靴*1、白虎*1\n\n活动期间，累计充值满500元宝 “天降奇兵”兵符*1，建筑图纸1\n\n活动期间，累计充值满1500元宝 推恩令1，金砖1，白银礼匣1，白银钥匙1 \n\n活动期间，累计充值满5000元宝 “天降奇兵”兵符*10、金砖1，高级青囊书1，亡灵套装匣\n\n活动期间，累计充值满10000元宝 盟主令1，炼化鼎10，安民告示1，高级迁城令1\n\n活动期间，累计充值满30000元宝 名将套装宝匣1，强化宝珠10，乾坤宝珠10，伯乐包1\n\n活动期间，累计充值满50000元宝 七星宝刀*1，10级镶嵌宝珠匣*10，灵通甘草10，礼金1000，强化宝珠10，乾坤宝珠10\n\n活动详情请关注官网和论坛。";	
			$mailcontent = sprintf($mailcontent,$giftnameStr);
			sendSysMail ( $uid, $mailtitle, $mailcontent );
	    }
	}
		//活动2：首充1000		
	if ($now <=$openTime+$lastsTime)				
	{
		$payCount = sql_fetch_one_cell("select count(1) from pay_log where passport='$passport' and time <= $endtime");
		if($payCount<=1 && $money>=1000){
		
	        giveGoods($uid,86,1,6); 
	        giveGoods($uid,8,3,6); 
	        giveGoods($uid,9,2,6); 
	        giveGoods($uid,10,1,6); 
	        giveGoods($uid,11,2,6); 
	        giveGoods($uid,12,1,6);   
	        giveGoods($uid,67,3,6);  
	        giveGoods($uid,61,1,6);  
	        giveGoods($uid,69,1,6); 
	        giveGoods($uid,18,1,6);
	        giveGoods($uid,21,1,6); 
	        $giftnameStr='【金砖*1,墨家残卷*3,墨家图纸*2,墨家宝典*1,木牛流马*2,免战牌*1,鲁班残页*3,考工记精编*1,鲁班草图*1,黄金礼匣*1,黄金钥匙*1】';
			$mailcontent = "尊敬的玩家：\n\n       感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动内容：\n\n首次充值，且一次性充值满100元宝 高级徭役令*1个, 金砖1\n\n首次充值，且一次性充值满1000元宝。 金砖1，珠宝盒*2，墨家残卷*3、墨家图纸*2、墨家宝典*1、木牛流马*2、免战牌*1、鲁班残页*3、考工记精编*1、鲁班草图、黄金礼匣*1、黄金钥匙*1\n\n首次充值，且一次性充值满3000元宝 金砖*1，高级热血铠甲*1、高级热血长枪*1、高级热血肩甲*1、高级热血腰甲*1、高级热血护腕*1、高级热血战靴*1、白虎*1\n\n活动期间，累计充值满500元宝 “天降奇兵”兵符*1，建筑图纸1\n\n活动期间，累计充值满1500元宝 推恩令1，金砖1，白银礼匣1，白银钥匙1 \n\n活动期间，累计充值满5000元宝 “天降奇兵”兵符*10、金砖1，高级青囊书1，亡灵套装匣\n\n活动期间，累计充值满10000元宝 盟主令1，炼化鼎10，安民告示1，高级迁城令1\n\n活动期间，累计充值满30000元宝 名将套装宝匣1，强化宝珠10，乾坤宝珠10，伯乐包1\n\n活动期间，累计充值满50000元宝 七星宝刀*1，10级镶嵌宝珠匣*10，灵通甘草10，礼金1000，强化宝珠10，乾坤宝珠10\n\n活动详情请关注官网和论坛。";	
			$mailcontent = sprintf($mailcontent,$giftnameStr);
			sendSysMail ( $uid, $mailtitle, $mailcontent );
	    }
	}
		//活动3：首充3000		
	if ($now <=$openTime+$lastsTime)				
	{
		$payCount = sql_fetch_one_cell("select count(1) from pay_log where passport='$passport' and time <= $endtime");
		if($payCount<=1 && $money>=3000){
		
	        giveGoods($uid,86,1,6); 
	        giveArmor($uid,10159,1,6);
	        giveArmor($uid,11003,1,6);
	        giveArmor($uid,11004,1,6);
	        giveArmor($uid,11005,1,6);
	        giveArmor($uid,11006,1,6);
	        giveArmor($uid,11007,1,6);
	        giveArmor($uid,11008,1,6);
	        $giftnameStr='【金砖*1,白虎*1,高级热血铠甲*1,高级热血长枪*1,高级热血肩甲*1,高级热血腰甲*1,高级热血护腕*1,高级热血战靴*1】';
			$mailcontent = "尊敬的玩家：\n\n       感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动内容：\n\n首次充值，且一次性充值满100元宝 高级徭役令*1个, 金砖1\n\n首次充值，且一次性充值满1000元宝。 金砖1，珠宝盒*2，墨家残卷*3、墨家图纸*2、墨家宝典*1、木牛流马*2、免战牌*1、鲁班残页*3、考工记精编*1、鲁班草图、黄金礼匣*1、黄金钥匙*1\n\n首次充值，且一次性充值满3000元宝 金砖*1，高级热血铠甲*1、高级热血长枪*1、高级热血肩甲*1、高级热血腰甲*1、高级热血护腕*1、高级热血战靴*1、白虎*1\n\n活动期间，累计充值满500元宝 “天降奇兵”兵符*1，建筑图纸1\n\n活动期间，累计充值满1500元宝 推恩令1，金砖1，白银礼匣1，白银钥匙1 \n\n活动期间，累计充值满5000元宝 “天降奇兵”兵符*10、金砖1，高级青囊书1，亡灵套装匣\n\n活动期间，累计充值满10000元宝 盟主令1，炼化鼎10，安民告示1，高级迁城令1\n\n活动期间，累计充值满30000元宝 名将套装宝匣1，强化宝珠10，乾坤宝珠10，伯乐包1\n\n活动期间，累计充值满50000元宝 七星宝刀*1，10级镶嵌宝珠匣*10，灵通甘草10，礼金1000，强化宝珠10，乾坤宝珠10\n\n活动详情请关注官网和论坛。";	
			$mailcontent = sprintf($mailcontent,$giftnameStr);
			sendSysMail ( $uid, $mailtitle, $mailcontent );
	    }
	}
	//活动：每日首充10
	//if ($now <=$openTime+$lastsTime){
		//$payCount = sql_fetch_one_cell("select count(1) from pay_log where passport='$passport' and time >= unix_timestamp(curdate())");
	//	if($payCount<=1 && $money>=10){//1.活动期间当天第一次充值 2.充值数量达到要求 
	//		giveGoods($uid,86,1,6);
	//		$giftnameStr='【金砖*1】';
	//		$mailcontent = "尊敬的玩家：\n\n感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动时间：新服开启2周内（24*14小时）\n\n活动内容： 新服充值五重好礼回馈！\n\n1. 活动期间，首次在新服充值，且一次性充值满100元宝，即可获得价值300元宝的【徭役令1、典民令1、建筑图纸1、鲁班残页2、墨家残卷2、珍珠2、桃园兄弟会之剑（武器）1】。\n\n2. 活动期间，玩家每日的首次充值，且一次性充值满10元宝，即可获得系统赠送的【金砖1】。\n\n3. 活动期间，玩家累积充值满1000元宝，即送【金砖1，金牛1，粮食辎重盒1，木材辎重盒1，石料辎重盒1，铁锭辎重盒1，白银礼盒1，白银钥匙1】。每个玩家限送1次。\n\n4. 活动期间，玩家累积充值满15000元宝，即送【白虎1个】。每个玩家限送1次。\n\n5. 活动期间，玩家累积充值满50000元宝，即送【亡灵套装盒1个】，内含亡灵套装全套12件装备。每个玩家限送1次。\n\n    活动详情请关注官网或咨询客服。";	
	//		$mailcontent = sprintf($mailcontent,$giftnameStr);
	//		sendSysMail ( $uid, $mailtitle, $mailcontent );
	//	}
//	}
	//活动3：累计充值500、1500、5000、50000
	if ($now <=$openTime+$lastsTime){
		$total = sql_fetch_one("select sum(money) as totalMoney, max(time) as lastTime from pay_log where passport='$passport'");
		$totalMoney = $total ["totalMoney"];
		$lastTime = $total ["lastTime"];
		$beforeTotal = sql_fetch_one_cell ( "select sum(money) as totalMoney from pay_log where passport='$passport' and time <=$lastTime-1" );
		if($totalMoney>=500&&$beforeTotal<500){
		        giveGoods($uid,40,1,6);
		        giveGoods($uid,10461,1,6);
		        $giftnameStr='【“天降奇兵”兵符*1，建筑图纸*1】';
				$mailcontent = "尊敬的玩家：\n\n       感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动内容：\n\n首次充值，且一次性充值满100元宝 高级徭役令*1个, 金砖1\n\n首次充值，且一次性充值满1000元宝。 金砖1，珠宝盒*2，墨家残卷*3、墨家图纸*2、墨家宝典*1、木牛流马*2、免战牌*1、鲁班残页*3、考工记精编*1、鲁班草图、黄金礼匣*1、黄金钥匙*1\n\n首次充值，且一次性充值满3000元宝 金砖*1，高级热血铠甲*1、高级热血长枪*1、高级热血肩甲*1、高级热血腰甲*1、高级热血护腕*1、高级热血战靴*1、白虎*1\n\n活动期间，累计充值满500元宝 “天降奇兵”兵符*1，建筑图纸1\n\n活动期间，累计充值满1500元宝 推恩令1，金砖1，白银礼匣1，白银钥匙1 \n\n活动期间，累计充值满5000元宝 “天降奇兵”兵符*10、金砖1，高级青囊书1，亡灵套装匣\n\n活动期间，累计充值满10000元宝 盟主令1，炼化鼎10，安民告示1，高级迁城令1\n\n活动期间，累计充值满30000元宝 名将套装宝匣1，强化宝珠10，乾坤宝珠10，伯乐包1\n\n活动期间，累计充值满50000元宝 七星宝刀*1，10级镶嵌宝珠匣*10，灵通甘草10，礼金1000，强化宝珠10，乾坤宝珠10\n\n活动详情请关注官网和论坛。";	
				$mailcontent = sprintf($mailcontent,$giftnameStr);
				sendSysMail ( $uid, $mailtitle, $mailcontent );
		}
		if($totalMoney>=1500&&$beforeTotal<1500){
		        giveGoods($uid,124,1,6);
		        giveGoods($uid,86,1,6);
		        giveGoods($uid,17,1,6);
		        giveGoods($uid,20,1,6);
		        $giftnameStr='【推恩令*1，金砖*1，白银礼匣*1，白银钥匙*1】';
				$mailcontent = "尊敬的玩家：\n\n       感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动内容：\n\n首次充值，且一次性充值满100元宝 高级徭役令*1个, 金砖1\n\n首次充值，且一次性充值满1000元宝。 金砖1，珠宝盒*2，墨家残卷*3、墨家图纸*2、墨家宝典*1、木牛流马*2、免战牌*1、鲁班残页*3、考工记精编*1、鲁班草图、黄金礼匣*1、黄金钥匙*1\n\n首次充值，且一次性充值满3000元宝 金砖*1，高级热血铠甲*1、高级热血长枪*1、高级热血肩甲*1、高级热血腰甲*1、高级热血护腕*1、高级热血战靴*1、白虎*1\n\n活动期间，累计充值满500元宝 “天降奇兵”兵符*1，建筑图纸1\n\n活动期间，累计充值满1500元宝 推恩令1，金砖1，白银礼匣1，白银钥匙1 \n\n活动期间，累计充值满5000元宝 “天降奇兵”兵符*10、金砖1，高级青囊书1，亡灵套装匣\n\n活动期间，累计充值满10000元宝 盟主令1，炼化鼎10，安民告示1，高级迁城令1\n\n活动期间，累计充值满30000元宝 名将套装宝匣1，强化宝珠10，乾坤宝珠10，伯乐包1\n\n活动期间，累计充值满50000元宝 七星宝刀*1，10级镶嵌宝珠匣*10，灵通甘草10，礼金1000，强化宝珠10，乾坤宝珠10\n\n活动详情请关注官网和论坛。";	
				$mailcontent = sprintf($mailcontent,$giftnameStr);
				sendSysMail ( $uid, $mailtitle, $mailcontent );
		}
		if($totalMoney>=5000&&$beforeTotal<5000){
		        giveGoods($uid,165,1,6);
		        giveGoods($uid,86,1,6);
		        giveGoods($uid,10461,10,6);
		        giveGoods($uid,50117,1,6);
		        $giftnameStr='【亡灵套装匣*1，高级青囊书*1，金砖*1，“天降奇兵”兵符*10】';
				$mailcontent = "尊敬的玩家：\n\n       感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动内容：\n\n首次充值，且一次性充值满100元宝 高级徭役令*1个, 金砖1\n\n首次充值，且一次性充值满1000元宝。 金砖1，珠宝盒*2，墨家残卷*3、墨家图纸*2、墨家宝典*1、木牛流马*2、免战牌*1、鲁班残页*3、考工记精编*1、鲁班草图、黄金礼匣*1、黄金钥匙*1\n\n首次充值，且一次性充值满3000元宝 金砖*1，高级热血铠甲*1、高级热血长枪*1、高级热血肩甲*1、高级热血腰甲*1、高级热血护腕*1、高级热血战靴*1、白虎*1\n\n活动期间，累计充值满500元宝 “天降奇兵”兵符*1，建筑图纸1\n\n活动期间，累计充值满1500元宝 推恩令1，金砖1，白银礼匣1，白银钥匙1 \n\n活动期间，累计充值满5000元宝 “天降奇兵”兵符*10、金砖1，高级青囊书1，亡灵套装匣\n\n活动期间，累计充值满10000元宝 盟主令1，炼化鼎10，安民告示1，高级迁城令1\n\n活动期间，累计充值满30000元宝 名将套装宝匣1，强化宝珠10，乾坤宝珠10，伯乐包1\n\n活动期间，累计充值满50000元宝 七星宝刀*1，10级镶嵌宝珠匣*10，灵通甘草10，礼金1000，强化宝珠10，乾坤宝珠10\n\n活动详情请关注官网和论坛。";	
				$mailcontent = sprintf($mailcontent,$giftnameStr);
				sendSysMail ( $uid, $mailtitle, $mailcontent );
		}
		if($totalMoney>=10000&&$beforeTotal<10000){//累积充值达到N元宝
		        giveGoods($uid,15,1,6);
		        giveGoods($uid,155,10,6);
		        giveGoods($uid,58,1,6);
		        giveGoods($uid,82,1,6);
		        $giftnameStr='【盟主令*1，炼化鼎*10，安民告示*1，高级迁城令*1】';
				$mailcontent = "尊敬的玩家：\n\n       感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动内容：\n\n首次充值，且一次性充值满100元宝 高级徭役令*1个, 金砖1\n\n首次充值，且一次性充值满1000元宝。 金砖1，珠宝盒*2，墨家残卷*3、墨家图纸*2、墨家宝典*1、木牛流马*2、免战牌*1、鲁班残页*3、考工记精编*1、鲁班草图、黄金礼匣*1、黄金钥匙*1\n\n首次充值，且一次性充值满3000元宝 金砖*1，高级热血铠甲*1、高级热血长枪*1、高级热血肩甲*1、高级热血腰甲*1、高级热血护腕*1、高级热血战靴*1、白虎*1\n\n活动期间，累计充值满500元宝 “天降奇兵”兵符*1，建筑图纸1\n\n活动期间，累计充值满1500元宝 推恩令1，金砖1，白银礼匣1，白银钥匙1 \n\n活动期间，累计充值满5000元宝 “天降奇兵”兵符*10、金砖1，高级青囊书1，亡灵套装匣子\n\n活动期间，累计充值满10000元宝 盟主令1，炼化鼎10，安民告示1，高级迁城令1\n\n活动期间，累计充值满30000元宝 名将套装宝匣1，强化宝珠10，乾坤宝珠10，伯乐包1\n\n活动期间，累计充值满50000元宝 七星宝刀*1，10级镶嵌宝珠匣*10，灵通甘草10，礼金1000，强化宝珠10，乾坤宝珠10\n\n活动详情请关注官网和论坛。";	
				$mailcontent = sprintf($mailcontent,$giftnameStr);
				sendSysMail ( $uid, $mailtitle, $mailcontent );
		}
		if($totalMoney>=30000&&$beforeTotal<30000){//累积充值达到N元宝
		        giveGoods($uid,50235,1,6);
		        giveGoods($uid,205,10,6);
		        giveGoods($uid,204,10,6);
		        giveGoods($uid,10016,1,6);
		        $giftnameStr='【名将套装宝匣*1，强化宝珠*10，乾坤宝珠*10，伯乐包*1】';
				$mailcontent = "尊敬的玩家：\n\n       感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动内容：\n\n首次充值，且一次性充值满100元宝 高级徭役令*1个, 金砖1\n\n首次充值，且一次性充值满1000元宝。 金砖1，珠宝盒*2，墨家残卷*3、墨家图纸*2、墨家宝典*1、木牛流马*2、免战牌*1、鲁班残页*3、考工记精编*1、鲁班草图、黄金礼匣*1、黄金钥匙*1\n\n首次充值，且一次性充值满3000元宝 金砖*1，高级热血铠甲*1、高级热血长枪*1、高级热血肩甲*1、高级热血腰甲*1、高级热血护腕*1、高级热血战靴*1、白虎*1\n\n活动期间，累计充值满500元宝 “天降奇兵”兵符*1，建筑图纸1\n\n活动期间，累计充值满1500元宝 推恩令1，金砖1，白银礼匣1，白银钥匙1 \n\n活动期间，累计充值满5000元宝 “天降奇兵”兵符*10、金砖1，高级青囊书1，亡灵套装匣\n\n活动期间，累计充值满10000元宝 盟主令1，炼化鼎10，安民告示1，高级迁城令1\n\n活动期间，累计充值满30000元宝 名将套装宝匣1，强化宝珠10，乾坤宝珠10，伯乐包1\n\n活动期间，累计充值满50000元宝 七星宝刀*1，10级镶嵌宝珠匣*10，灵通甘草10，礼金1000，强化宝珠10，乾坤宝珠10\n\n活动详情请关注官网和论坛。";	
				$mailcontent = sprintf($mailcontent,$giftnameStr);
				sendSysMail ( $uid, $mailtitle, $mailcontent );
		}
		if($totalMoney>=50000&&$beforeTotal<50000){//累积充值达到N元宝
		        giveGoods($uid,214,10,6);
		        //giveGoods($uid,0,1000,6);
		        sql_query("update sys_user set gift=gift+1000 where uid=$uid limit 1");
		        giveGoods($uid,204,10,6);
		        giveGoods($uid,205,10,6);
		        giveGoods($uid,10462,10,6);
		        giveArmor($uid,52001,1,6);
		        $giftnameStr='【七星宝刀*1，10级镶嵌珠宝匣*10，灵通甘草*10，礼金*1000，强化宝珠*10，乾坤宝珠*10】';
				$mailcontent = "尊敬的玩家：\n\n       感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动内容：\n\n首次充值，且一次性充值满100元宝 高级徭役令*1个, 金砖1\n\n首次充值，且一次性充值满1000元宝。 金砖1，珠宝盒*2，墨家残卷*3、墨家图纸*2、墨家宝典*1、木牛流马*2、免战牌*1、鲁班残页*3、考工记精编*1、鲁班草图、黄金礼匣*1、黄金钥匙*1\n\n首次充值，且一次性充值满3000元宝 金砖*1，高级热血铠甲*1、高级热血长枪*1、高级热血肩甲*1、高级热血腰甲*1、高级热血护腕*1、高级热血战靴*1、白虎*1\n\n活动期间，累计充值满500元宝 “天降奇兵”兵符*1，建筑图纸1\n\n活动期间，累计充值满1500元宝 推恩令1，金砖1，白银礼匣1，白银钥匙1 \n\n活动期间，累计充值满5000元宝 “天降奇兵”兵符*10、金砖1，高级青囊书1，亡灵套装匣\n\n活动期间，累计充值满10000元宝 盟主令1，炼化鼎10，安民告示1，高级迁城令1\n\n活动期间，累计充值满30000元宝 名将套装宝匣1，强化宝珠10，乾坤宝珠10，伯乐包1\n\n活动期间，累计充值满50000元宝 七星宝刀*1，10级镶嵌宝珠匣*10，灵通甘草10，礼金1000，强化宝珠10，乾坤宝珠10\n\n活动详情请关注官网和论坛。";	
				$mailcontent = sprintf($mailcontent,$giftnameStr);
				sendSysMail ( $uid, $mailtitle, $mailcontent );
		}
	}
}
/**********************************************普通开服活动End***************************************************/
/**********************************************软合服开服活动Begin***************************************************/
else{
	if ($now <=$openTime+$lastsTime){
		$total = sql_fetch_one("select sum(money) as totalMoney, max(time) as lastTime from pay_log where passport='$passport' and time between $openTime and $endtime");
		$totalMoney = $total ["totalMoney"];
		$lastTime = $total ["lastTime"];
		$beforeTotal = sql_fetch_one_cell ( "select sum(money) as totalMoney from pay_log where passport='$passport' and time <=$lastTime-1 and time>=$openTime" );
		if($totalMoney>=100&&$beforeTotal<100){//充值满100元宝 强化宝珠
		        giveGoods($uid,205,1,6);
		        $giftnameStr='【强化宝珠*1】';
				$mailcontent = "尊敬的玩家：\n\n感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动时间：本服务器开启2周内（24*14小时）\n\n    活动内容：活动期间，玩家充值满100元宝，即送强化宝珠1个；充值满1000元宝送6级勇武镶嵌宝珠*1；充值满10000元宝送灵通甘草*20；充值满30000元宝送灭寂驹*1。活动详情请关注官网。";	
				$mailcontent = sprintf($mailcontent,$giftnameStr);
				sendSysMail ( $uid, $mailtitle, $mailcontent );
		}
		if($totalMoney>=1000&&$beforeTotal<1000){//充值满1000元宝 六级勇武镶嵌宝石*1
		        giveGoods($uid,325,1,6);
		        $giftnameStr='【6级勇武镶嵌宝珠*1】';
				$mailcontent = "尊敬的玩家：\n\n感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动时间：本服务器开启2周内（24*14小时）\n\n    活动内容：活动期间，玩家充值满100元宝，即送强化宝珠1个；充值满1000元宝送6级勇武镶嵌宝珠*1；充值满10000元宝送灵通甘草*20；充值满30000元宝送灭寂驹*1。活动详情请关注官网。";	
				$mailcontent = sprintf($mailcontent,$giftnameStr);
				sendSysMail ( $uid, $mailtitle, $mailcontent );
		}
		if($totalMoney>=10000&&$beforeTotal<10000){//充值满10000元宝	送灵通甘草*20
		        giveGoods($uid,214,20,6);
		        $giftnameStr='【灵通甘草*20】';
				$mailcontent = "尊敬的玩家：\n\n感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动时间：本服务器开启2周内（24*14小时）\n\n    活动内容：活动期间，玩家充值满100元宝，即送强化宝珠1个；充值满1000元宝送6级勇武镶嵌宝珠*1；充值满10000元宝送灵通甘草*20；充值满30000元宝送灭寂驹*1。活动详情请关注官网。";	
				$mailcontent = sprintf($mailcontent,$giftnameStr);
				sendSysMail ( $uid, $mailtitle, $mailcontent );
		}
		if($totalMoney>=30000&&$beforeTotal<30000){//充值满30000元宝	送灭寂驹*1
		        giveArmor($uid,53016,1,6);
		        $giftnameStr='【灭寂驹*1】';
				$mailcontent = "尊敬的玩家：\n\n感谢您参与本次充值活动，恭喜您获得了%s。请您到宝物或装备栏查收。\n\n活动时间：本服务器开启2周内（24*14小时）\n\n    活动内容：活动期间，玩家充值满100元宝，即送强化宝珠1个；充值满1000元宝送6级勇武镶嵌宝珠*1；充值满10000元宝送灵通甘草*20；充值满30000元宝送灭寂驹*1。活动详情请关注官网。";	
				$mailcontent = sprintf($mailcontent,$giftnameStr);
				sendSysMail ( $uid, $mailtitle, $mailcontent );
		}
	}
}
/**********************************************软合服开服活动End***************************************************/

@include("./actpaygift.php");

?>