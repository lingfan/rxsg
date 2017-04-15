<?php
/**
 * 
 * 黄巾之乱开始时调用这个方法,在黄金state被设置0后
 *
 */
function huangjinstart(){
	
}
/**
 * 
 * 黄巾之乱结束时调用这个方法，在黄金state被设置1后
 *
 */
function huangjinfinish(){
		sql_query("update sys_user_task set  state=1 where tid between 11000 and 15000");//删除黄巾之乱的史诗任务
		sql_query("delete from sys_user_task where tid in(select id from cfg_task where  `group` in(1000,2000,3000,4000)  )");//删除黄巾之乱的日常任务
		//插入黄金之乱结束时的任务讨伐有功
		sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and id in (155005,155006)) on duplicate key update state=0");
        sql_query("update mem_state  set value=unix_timestamp() where state=6");//保存结束时间
		//dongzhuo1start();本来想马上调用的，但想了想下个史诗任务开始应该有一个缓冲期，所以没在这调用！
}
/**
 * 
 * 董卓第一阶段开始在state=7的value被设置1时调用,这个方法没机会调用，因为是c++开启，所以没被调用
 *
 */
function dongzhuo1start(){//开始第一阶段董卓任务,把他放在黄巾之乱结束后调用！
    //开启董卓史诗
    sql_query("update mem_state set  value=1 where state=7");
	//设置董卓日常任务
    sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and (id>=420494 and id<=420520)) on duplicate key update state=0");
	//设置董卓史诗任务
    sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and (id>=15001 and id<=15504)) on duplicate key update state=0");
    //将普通城设置成十常的城池
	sql_query("update sys_city set uid = 896 where uid = 895");
}
/**
 * 
 * 董卓第一阶段结束在state=7的value被设置2时调用
 *
 */
function dongzhuo1finish(){
	$title=  $GLOBALS['dongzuojj']["mailtitle"];
	$content= $GLOBALS['dongzuojj']["mailcontent"];
	sendAllSysMail($title,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$content);
	sendSysInform(0,1,0,300,1800,1,49151,$content);
	sql_query("delete from sys_user_task where  (tid>=420494 and tid<=420520)");//删除第一阶段刷出的日常任务
	//10100,10200,10500,15000  ('讨伐阉竖','收复失地','御赐兵甲','犒赏三军')的对应任务组的id号,删除第一阶段的史诗任务
	$deletesql="update sys_user_task set state=1 where tid in(select id from cfg_task where `group` in (10100,10200,10500,15000))";
	sql_query($deletesql);
}
/**
 * 
 * 董卓第二阶段开始在state=7的value被设置2时调用
 *
 */
function dongzhuo2start(){
	//插入第二阶段的日常任务
	/*
	$rows=sql_fetch_rows("select id from cfg_task where id>=420464 and id<=420490");
	foreach($rows as $row){
		$tid=$row["id"];
		if($tid==420464||$tid==420468||$tid==420473||$tid==420482){//把每个组里面的第一个任务立刻刷个用户			
			sql_query("insert into sys_user_task (uid,tid,state) (select uid,$tid,0 from sys_user_task where tid=243 and state=1) on duplicate key update state=0");
		}else{
			sql_query("insert into sys_user_task (uid,tid,state) (select uid,$tid,1 from sys_user_task where tid=243 and state=1) on duplicate key update state=1");			
		}
	}
	*/
	sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and (id>=420464 and id<=420490)) on duplicate key update state=0");
	sql_query("update sys_user_task set state=0 where tid in(420464,420468,420473,420482)");
	//插入第二阶段的新加史诗任务讨伐有功, 慷慨资助,勤王之师,奇珍异宝,济寒赈贫 
	sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and id in (103005,103006,103007,103008,103009)) on duplicate key update state=0");
	
	//普通城中所有旗号为“宦”的城池和所有没有旗号的城池变成旗号为“董”的城池
	/*
	$citys = sql_fetch_rows("select cid from sys_city a,mem_world b  where a.cid = b.ownercid and b.state = 0 and uid = 896");
	foreach ($citys as $city)
	{
		$curCid = array_shift($city);
		sql_query("update sys_city set uid = 659 where cid = $curCid");
	}
	
	$citys = sql_fetch_rows("select cid from sys_city a,mem_world b  where a.cid = b.ownercid and b.state = 0 and uid = 710 and a.type<=1");
	foreach ($citys as $city)
	{
		$curCid = array_shift($city);
		sql_query("update sys_city set uid = 659 where cid = $curCid");
	}
*/
	//普通城中所有旗号为“宦”的城池和所有没有旗号的城池变成旗号为“董”的城池
	sql_query("update sys_city set uid = 659 where uid = 896");
	////将汉灵帝的城池改为十常侍的
	sql_query("update sys_city set uid = 659 where uid=710 and type<=1");
	
	sql_query("update sys_city_soldier a, sys_city b set sid = sid + 5 where a.cid = b.cid and count > 0 and b.uid = 659 and sid between 23 and 27");
	sql_query("update sys_city_hero set uid = 659 where uid = 896 and cid in (select cid from sys_city where uid = 659)");
	sql_query("delete from sys_user_task where tid=84014");//84014号随机任务：刺探董卓。由于城池更改已经无法完成了。所以删除
	//如果服务器已经过了董卓第二阶段，这个论功行赏任务组就没有，但是实际是需要的
	sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and `group`=10700) on duplicate key update state=0");
	
	sql_query("update sys_user_task set state=1 where tid in(select id from cfg_task where `group`=10400)");//删除老版本的讨伐有功任务组
	sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and `group`=112022) on duplicate key update state=0");//插入新版本的讨伐有功
}
/**
 * 
 * 董卓第二阶段开始在state=7的value被设置3时调用
 *
 */
function dongzhuo2finish(){
	sql_query("update sys_user_task set state=1 where tid in (select id from cfg_task where `group` in (15100,15200,15300,15400,15500,15600))");//把所有董卓的任务删除
//	sql_query("update sys_user_task set state=1 where tid in (select id from cfg_task where `group` in (15600))");//把所有董卓时期的讨伐有功任务组的任务删除
//	sql_query("delete from  sys_user_task  where tid>=420464 and tid<=420490");//把所有董卓的日常任务也删除
}
/**
 * 
 * 开放洛阳开始，在state=8的value被设置1时调用
 *
 */
function luoyangstart(){
	triggerBeiZhanLuoYang();	//要是备战洛阳上的话就把这个注释去掉
}
/**
 * 
 * 用户243(脱离新手保护)任务完成时调用，看本服务器史诗阶段是哪个阶段，然后刷相应的任务给用户
 *
 */
function finish243($uid){
	$huangjinvalue=sql_fetch_one_cell("select value from mem_state where state=5");
	$dongzhuovalue=sql_fetch_one_cell("select value from mem_state where state=7");
	$luoyangvalue=sql_fetch_one_cell("select value from mem_state where state=8");
	if($huangjinvalue==0){
	}else if($huangjinvalue==1){//服务器到了黄金阶段结束阶段
		//删除黄巾之乱的日常任务
		sql_query("delete from sys_user_task where uid=$uid and  tid in(select id from cfg_task where  `group` in(1000,2000,3000,4000)  )");
		//删除黄金之乱阶段的史诗任务，不过用户应该本来就没有这阶段的记录
		sql_query("update sys_user_task set  state=1 where tid between 11000 and 15000 and uid=$uid");
		//黄金结束时刷给用户的几个任务，斩杀奸佞，铲除宦党，讨董先锋，救驾功臣，收复失地  刷个用户,这几个其实应该是原系统里面应该已经做了的事的，不过好像没看到处理
		sql_query("insert into sys_user_task (uid,tid,state) (select $uid,id,0 from cfg_task where id in (10501,6301,6302,6401,6402)) on duplicate key update state=0");
		//黄金之乱之后刷个用户御赐兵甲,犒赏三军,交换功劳,这几个其实应该是原系统里面应该已经做了的事的，不过好像没看到处理
		sql_query("insert into sys_user_task (uid,tid,state) (select $uid,id,0 from  cfg_task where `group` in (10100,10200,10400)) on duplicate key update state=0");
		//黄金之乱之后刷个用户讨伐有功任务组
		sql_query("insert into sys_user_task (uid,tid,state) (select $uid,id,0 from  cfg_task where `group` in (15600)) on duplicate key update state=0");
		
		//黄金结束时刷给用户的记录功勋下的几个任务，忠心可嘉，威武之师，仗义疏财，赫赫战功，刷个用户,这几个其实应该是原系统里面应该已经做了的事的，不过好像没看到处理
		sql_query("insert into sys_user_task (uid,tid,state) (select $uid,id,0 from cfg_task where id in (10301,10302,10303,10304)) on duplicate key update state=0");
		
		if($dongzhuovalue==1){//服务器到了十常侍阶段
			//董卓1阶段插入十常侍日常任务
			sql_query("insert into sys_user_task (uid,tid,state) (select $uid as uid,id,1 from cfg_task where id>=420494 and id<=420520)  on duplicate key update state=1"); 
			//在刷给用户第一阶段的日常任务后，把各个组的第一个设置用户可见
			sql_query("update sys_user_task set state=0 where uid=$uid and tid in(420494,420498,420503,420512)"); 
		}else if($dongzhuovalue==2){//服务器到了董卓第二阶段
			//删除第一阶段刷出的日常任务
			sql_query("delete from sys_user_task where  (tid>=420494 and tid<=420520) and uid=$uid");
			
			//董卓二阶段可以插入第二阶段日常任务
			sql_query("insert into sys_user_task (uid,tid,state) (select $uid as uid,id,1 from cfg_task where id>=420464 and id<=420490)  on duplicate key update state=1");
			//刷出日常任务后把各个组的第一个任务设置为可见
			sql_query("update sys_user_task set state=0 where uid=$uid and tid in(420464,420468,420473,420482)");
			//插入第二阶段的新加史诗任务组记录功勋里面的任务讨伐有功, 慷慨资助,勤王之师,奇珍异宝,济寒赈贫 
			sql_query("insert into sys_user_task (uid,tid,state) (select $uid,id,0 from  cfg_task where id in (103005,103006,103007,103008,103009)) on duplicate key update state=0");
			//插入论功行赏任务组的任务
			sql_query("insert into sys_user_task (uid,tid,state) (select $uid,id,0 from  cfg_task where `group` in (10700)) on duplicate key update state=0");
			//下面是修复交换功劳个人错误bug
			sql_query("update sys_user_task set state=1 where tid in(select id from cfg_task where `group`=10400) and uid='$uid'");//删除老版本的交换功劳任务组
			sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and `group`=112022 and uid='$uid') on duplicate key update state=0");//插入新版本的讨伐有功
			
		}else if($dongzhuovalue==3){//董卓之后是洛阳
			//dongzhuo2finish();
			sql_query("update sys_user_task set state=1 where uid=$uid and tid in (select id from cfg_task where `group` in (15100,15200,15300,15400,15500,15600))");//把所有董卓的任务删除
			//这下面三行是起到保留董卓第二阶段日常任务的作用
			sql_query("insert into sys_user_task (uid,tid,state) (select $uid,id,0 from  cfg_task where `group` in (10700)) on duplicate key update state=0");//如果没有经过董卓第二阶段，这个论功行赏任务组就没有，但是实际是需要的
			//董卓二阶段可以插入第二阶段日常任务
			sql_query("insert into sys_user_task (uid,tid,state) (select $uid as uid,id,1 from cfg_task where id>=420464 and id<=420490)  on duplicate key update state=1");
			//刷出日常任务后把各个组的第一个任务设置为可见
			sql_query("update sys_user_task set state=0 where uid=$uid and tid in(420464,420468,420473,420482)");
			
			if($luoyangvalue==1){
				//插入洛阳阶段史诗任务
				sql_query("insert into sys_user_task (uid,tid,state) (select $uid,id,0 from  cfg_task where id >=112001 and id <=112007 and id<>112005) on duplicate key update state=0");
			}
		}
		if($dongzhuovalue>=2){
			//删除黄金之乱结束后刷出的这几个10100,10200,10500,15000  ('讨伐阉竖','收复失地','御赐兵甲','犒赏三军')的对应任务组的id号,删除第一阶段的史诗任务，讨伐阉竖是十常侍第一阶段的
			$deletesql="update sys_user_task set state=1 where uid=$uid and tid in(select id from cfg_task where `group` in (10100,10200,10500,15000))";
			sql_query($deletesql);
			sql_query("delete from sys_user_task where tid=84014 and uid=$uid");//84014号随机任务：刺探董卓。由于城池更改已经无法完成了。所以删除
			sql_query("insert into sys_user_task (uid,tid,state) (select $uid,id,0 from  cfg_task where `group`=112022) on duplicate key update state=0");//插入新版本的讨伐有功
			sql_query("update sys_user_task set state=1 where tid in(select id from cfg_task where `group`=10400) and uid=$uid");//删除老版本的讨伐有功任务组
		}
	}
}
function triggerBeiZhanLuoYang(){
	sql_query("update mem_state set value=1 where state=8");//把开启洛阳的标识设置为开启
	sql_query("INSERT INTO `sys_city_hero` (`hid`, `uid`, `name`, `npcid`, `sex`, `face`, `cid`, `state`, `level`, `exp`, `command_base`, `command_add_on`, `affairs_base`, `bravery_base`, `wisdom_base`, `affairs_add`, `bravery_add`, `wisdom_add`, `affairs_add_on`, `bravery_add_on`, `wisdom_add_on`, `force_max_add_on`, `energy_max_add_on`, `speed_add_on`, `attack_add_on`, `defence_add_on`, `loyalty`, `herotype`, `hero_health`) VALUES (894, 659, '吕布', 894, 1, 177, 215265, 1, 86, 20833500, 107, 0, 16, 122, 31, 8, 62, 16, 0, 0, 0, 0, 0, 0, 0, 0, 100, 0, 0) on duplicate key update state=1");//新插入一个将领
	sql_query("INSERT INTO `cfg_npc_hero` (`npcid`, `uid`, `name`, `zi`, `sex`, `face`, `affairs_base`, `bravery_base`, `wisdom_base`, `province`, `introduce`, `type`) VALUES (894, 659, '吕布', '奉先', 1, 177, 16, 122, 31, 0, '先后为丁原和董卓的义子，生性反复无常，履次背叛他人，最后因部下背叛而死。', 0) on duplicate key update `face`=177");//新插入一个将领
	sql_query("update sys_city set chiefhid= 894 where cid = 215265");//改城守
	sql_query("update sys_city_soldier set count=0 where cid=215265 and sid between 1 and 12");//改城守
	/*
	$rows=sql_fetch_rows("select id from cfg_task where id >=112001 and id <=112007 and id<>112005");
	foreach($rows as $row){
		$tid=$row["id"];
		sql_query("insert into sys_user_task (uid,tid,state) (select uid,$tid,0 from sys_user) on duplicate key update state=0");
	}
*/
	sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task,  cfg_task where tid=243 and state=1 and id >=112001 and id <=112007 and id<>112005) on duplicate key update state=0");			
	$title=  $GLOBALS['beizhanluoyang']["mailtitle"];
	$content= $GLOBALS['beizhanluoyang']["mailcontent"];
	sendAllSysMail($title,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$content);
	sendSysInform(0,1,0,300,1800,1,49151,$content);
}
?>