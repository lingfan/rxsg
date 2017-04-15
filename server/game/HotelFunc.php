<?php
require_once("./interface.php");
require_once("./utils.php");
require_once("./GoodsFunc.php");
require_once("./HeroFunc.php");
require_once './ActFunc.php';

define("XUANSHANGLING_ID",141);

//检查并重载姓名表
function reloadNameTable($tableName,$fileName)
{
	if (0 == sql_fetch_one_cell("select count(*) from $tableName"))
	{
		$lines = file("../data/$fileName");
		$sql = "insert into $tableName values ";
		$id = 1;
		foreach($lines as $line)
		{
			$line = substr($line,0,strlen($line) - 2);
			if (strlen($line) > 0)
			{
				$sql .= "('".$id."','".$line."'),";
				$id++;
			}
		}
		$sql = substr($sql,0,strlen($sql)-1);
		sql_query($sql);
	}
}

function generateName($tableName)
{
	$cnt = sql_fetch_one_cell("select count(*) from $tableName");
	$id = mt_rand(1,$cnt);
	return sql_fetch_one_cell("select `name` from $tableName where `id`='$id'");
}

function generateHeroName($sex)
{
	if ($sex == 0)	//girl
	{
		return generateName("mem_cfg_firstname").generateName("mem_cfg_girlname");
	}
	else
	{
		return generateName("mem_cfg_firstname").generateName("mem_cfg_boyname");
	}
}
function generateBaiYueHero($cid,$level)
{
	$heroname = $GLOBALS['Hotel']['Womens'];
    //生成一个随机性别
    $sex = 0;//10分之一的机率
    //生成将领姓名
    $htype=mt_rand(0,100);
    if($htype<30) $idx=mt_rand(6,11);
    else $idx=mt_rand(0,5);
    $herotype=15+$idx;
    $name = $heroname[$idx];
    //男人有859个头像，女人有105个头像 
    $face = ($sex==0)?mt_rand(100,145):mt_rand(1001,1070);
    //生成1到客栈级别*5的英雄   
    $hlevel = mt_rand(1,$level * 7);
    
    //生成三项基本属性的比值
    $affairs_rate = mt_rand(300,900);
    $bravery_rate = mt_rand(300,900);
    $wisdom_rate = mt_rand(300,900);
    
    $all_rate = $affairs_rate + $bravery_rate + $wisdom_rate;
    
    $hero_level_info = sql_fetch_one("select * from cfg_hero_level where level='$hlevel'");
    if (empty($hero_level_info)) throw new Exception($GLOBALS['generateRecruitHero']['no_data_of_this_level']);
    
    
    $all_base = rand(50,150);
    $affairs_base = floor($all_base * $affairs_rate / $all_rate);
    $bravery_base = floor($all_base * $bravery_rate / $all_rate);
    $wisdom_base  = floor($all_base * $wisdom_rate  / $all_rate);
    
    $affairs_add = round($hlevel * $affairs_rate / $all_rate);
    $bravery_add = round($hlevel * $bravery_rate / $all_rate);
    $wisdom_add  = $hlevel - $affairs_add - $bravery_add;
    
    $hero_exp = $hero_level_info['total_exp'];
    
    //忠诚度默认70
    $loyalty = 70;
    //需要黄金＝等级*1000
    //$gold_need = $hlevel * 1000;
    $gold_need=($hlevel*20+(max($affairs_base+$affairs_add-90,0)+max($bravery_base+$bravery_add-90,0)+max($wisdom_base+$wisdom_add-90,0))*50)*50;
    $sql = "insert into sys_recruit_hero (`name`,`sex`,`face`,`cid`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`gold_need`,`gen_time`,`herotype`) values ('$name','$sex','$face','$cid','$hlevel','$hero_exp','$affairs_base','$bravery_base','$wisdom_base','$affairs_add','$bravery_add','$wisdom_add','$loyalty','$gold_need',unix_timestamp(),'$herotype')";
    sql_query($sql);
}
/**
 * 从cfg_recruit_hero表中的配置来生成招募英雄，一般对应 客栈招将领活动
 * 
 * return false:表示没有触发招募将领
 *        true 招募到了
 */
function generateHeroFromConfig($cid,$level)
{
	$tresult = getAvailableActByType(2001);
	if (!$tresult)return false;
	$actid = $tresult["actid"];
	$hero = pickOneFromHeroConfig($actid);
	
	$name=$hero["heroname"];
	$herotype=$hero["herotype"];
//	$userhavecnt = $hero["userhavecnt"];
	//if ($userhavecnt>0 && $userhavecnt<=intval(sql_fetch_one_cell("select count(*) from sys_city_hero where cid='$cid' and herotype = $herotype")))
	//	return false;

	$affairs_base=mt_rand($hero["min_affairs_base"],$hero["max_affairs_base"]);
	$bravery_base=mt_rand($hero["min_bravery_base"],$hero["max_bravery_base"]);
	$wisdom_base=mt_rand($hero["min_wisdom_base"],$hero["max_wisdom_base"]);
	$command_base=mt_rand($hero["min_command_base"],$hero["max_command_base"]);
	$loyalty=mt_rand($hero["min_loyalty"],$hero["max_loyalty"]);
	$face = mt_rand($hero["min_face"],$hero["max_face"]);
	$hlevel = $hero["level"];
	$sex = $hero["sex"];
	$minLevel = $hero["min_level"];
	$maxLevel = $hero["max_level"];
	if ($hlevel<=0){//生成介于最小与最大级别之间的将领，级别不会超出1到客栈级别*5
		if($minLevel<1){
			$minLevel = 1;
		}
		if($maxLevel<$minLevel ||$maxLevel>$level * 5){
			$maxLevel = $level * 5;
		}		
		$hlevel = mt_rand($minLevel,$maxLevel);
	}
	$hero_level_info = sql_fetch_one("select * from cfg_hero_level where level='$hlevel'");
	if (empty($hero_level_info)) throw new Exception($GLOBALS['generateRecruitHero']['no_data_of_this_level']);
	if ($affairs_base==0&&$bravery_base==0&&$wisdom_base==0 ){ //属性都随机
		//生成一个随机性别
		$sex = (mt_rand(0,9) == 0)?0:1;//10分之一的机率
		 

	  
		//生成三项基本属性的比值
		$affairs_rate = mt_rand(300,900);
		$bravery_rate = mt_rand(300,900);
		$wisdom_rate = mt_rand(300,900);
		$all_rate = $affairs_rate + $bravery_rate + $wisdom_rate;
		$all_base = rand(50,150);
		$affairs_base = floor($all_base * $affairs_rate / $all_rate);
		$bravery_base = floor($all_base * $bravery_rate / $all_rate);
		$wisdom_base  = floor($all_base * $wisdom_rate  / $all_rate);
	  
		$affairs_add = round($hlevel * $affairs_rate / $all_rate);
		$bravery_add = round($hlevel * $bravery_rate / $all_rate);
		$wisdom_add  = $hlevel - $affairs_add - $bravery_add;

		//忠诚度默认70
		$loyalty = 70;
		 
	}else{ //属性在一定范围内随机
		$total=$affairs_base+$bravery_base+$wisdom_base;
		$affairs_add = round($hlevel * $affairs_base / $total);
		$bravery_add = round($hlevel * $bravery_base / $total);
		$wisdom_add = $hlevel - $affairs_add - $bravery_add;
		$command_add = 0;
	}
	if ($face ==0) //男人有859个头像，女人有105个头像 
	$face = ($sex==0)?mt_rand(100,145):mt_rand(1001,1070);
	 
	$hero_exp = $hero_level_info['total_exp'];
	$gold_need=($hlevel*20+(max($affairs_base+$affairs_add-90,0)+max($bravery_base+$bravery_add-90,0)+max($wisdom_base+$wisdom_add-90,0))*50)*50;
	$sql = "insert into sys_recruit_hero (`name`,`sex`,`face`,`cid`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,command_base,`affairs_add`,`bravery_add`,`wisdom_add`,command_add,`loyalty`,`gold_need`,`gen_time`,`herotype`) values ('$name','$sex','$face','$cid','$hlevel','$hero_exp','$affairs_base','$bravery_base','$wisdom_base','$command_base','$affairs_add','$bravery_add','$wisdom_add','$command_add','$loyalty','$gold_need',unix_timestamp(),'$herotype')";
	sql_query($sql);
	return true;
}


function generateRecruitHero($cid,$level)
{
	//生成一个随机性别
	$sex = (mt_rand(0,9) == 0)?0:1;//10分之一的机率
	//生成将领姓名
	$name = generateHeroName($sex);
	//男人有859个头像，女人有105个头像 
	$face = ($sex==0)?mt_rand(100,145):mt_rand(1001,1070);
	//生成1到客栈级别*5的英雄  ,客栈等级超过10级时，刷出的将领最高等级不超过50级，但客栈中刷出40级以上的将领数占客栈将领总数的50%.
	$hlevel=0;
	if ($level < 11) {
		$hlevel = mt_rand(1,$level * 5);
	} else {
		$tmp=mt_rand(0,1);
		if ($tmp==0) {
			$hlevel = mt_rand(1,39);
		} else {
			$hlevel = mt_rand(40,50);
		}
	}

	//生成三项基本属性的比值
	$affairs_rate = mt_rand(300,900);
	$bravery_rate = mt_rand(300,900);
	$wisdom_rate = mt_rand(300,900);

	$all_rate = $affairs_rate + $bravery_rate + $wisdom_rate;

	$hero_level_info = sql_fetch_one("select * from cfg_hero_level where level='$hlevel'");
	if (empty($hero_level_info)) throw new Exception($GLOBALS['generateRecruitHero']['no_data_of_this_level']);


	$all_base = rand(50,170);
	$command_base =0;
	if($all_base >110 and $all_base <= 150) {
		$command_base = rand(1,5);
	} elseif ($all_base >150 and $all_base <= 170) {
		$command_base = rand(6,10);
	}
	$affairs_base = floor($all_base * $affairs_rate / $all_rate);
	$bravery_base = floor($all_base * $bravery_rate / $all_rate);
	$wisdom_base  = floor($all_base * $wisdom_rate  / $all_rate);

	$affairs_add = round($hlevel * $affairs_rate / $all_rate);
	$bravery_add = round($hlevel * $bravery_rate / $all_rate);
	$wisdom_add  = $hlevel - $affairs_add - $bravery_add;

	$hero_exp = $hero_level_info['total_exp'];

	//忠诚度默认70
	$loyalty = 70;
	//需要黄金＝等级*1000
	//$gold_need = $hlevel * 1000;
	$gold_need=($hlevel*20+(max($affairs_base+$affairs_add-90,0)+max($bravery_base+$bravery_add-90,0)+max($wisdom_base+$wisdom_add-90,0))*50)*50;
	$sql = "insert into sys_recruit_hero (`name`,`sex`,`face`,`cid`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`command_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`gold_need`,`gen_time`) values ('$name','$sex','$face','$cid','$hlevel','$hero_exp','$affairs_base','$bravery_base','$wisdom_base','$command_base','$affairs_add','$bravery_add','$wisdom_add','$loyalty','$gold_need',unix_timestamp())";
	sql_query($sql);
}
function regenerateRecruitHero($cid,$level)
{
	sql_query("delete from sys_recruit_hero where `cid`='$cid'");
	//将领生成算法
	//	reloadNameTable("mem_cfg_firstname","name_first.txt");
	//	reloadNameTable("mem_cfg_boyname","name_boy.txt");
	//	reloadNameTable("mem_cfg_girlname","name_girl.txt");
	for ($i = 0; $i < $level; $i++)
	{
		generateRecruitHero($cid,$level);
	}
}
function doGetRecruitHero($uid,$cid,$level)
{
	$sort = 7;//7表示将领
	$log_type = 0;//0表示未用招贤榜刷出活动将领
	$countLimitPerDay = 1;//不用招贤榜每天最多出1个活动将领

	$ret = array();
	if ($level > 0)
	{
	    $last_reset_recruit = sql_fetch_one_cell("select last_reset_recruit from mem_city_schedule where cid='$cid'");

	    if (empty($last_reset_recruit))
	    {
	    	sql_query("insert into mem_city_schedule (cid,last_reset_recruit) values ('$cid',unix_timestamp()) on duplicate key update last_reset_recruit=unix_timestamp()");
	    	$last_reset_recruit = 0;
	    }
	    $now = sql_fetch_one_cell("select unix_timestamp()");
	    $blocksize = (10800/GAME_SPEED_RATE) / $level;
	    $last_block = floor(($last_reset_recruit+8*3600) / $blocksize);
	    $curr_block = floor(($now+8*3600) / $blocksize);
	    $blockdelta = $curr_block - $last_block;
	    if ($blockdelta > 0)
	    {
	        $oldheroes = sql_fetch_rows("select * from sys_recruit_hero where `cid`='$cid' order by id limit $blockdelta");  
	        foreach($oldheroes as $hero)
	        {
	            sql_query("delete from sys_recruit_hero where id=".$hero['id']);
	        }           
	        $heroCount = sql_fetch_one_cell("select count(*) from sys_recruit_hero where cid='$cid'");
		
	      	$tresult = getAvailableActByType(2001);
	      	if($tresult){
	      		$actid = $tresult['actid'];
				$total = sql_fetch_one_cell("select sum(count) from log_act where uid=$uid and actid=$actid and sort=$sort and log_type=$log_type and time>=unix_timestamp(curdate())");
				if($total >=$countLimitPerDay){//不用招贤榜刷出的活动将领已到上限
					$rate = 0;
				}else {
					$rate=5;//保留5%的概率刷出活动将领给不使用招贤榜玩家
				}
	      		if($last_reset_recruit==0){//使用招贤榜
					$rate=$tresult["rate"];
				}
	      	}
									
	        if ($tresult&&(mt_rand(1,100)<=$rate))	
	        {	        	
	        	$hasActHero=false;
	        	mt_srand(mt_rand());
	        	$idx=mt_rand($heroCount,$level-1);
	        	for ($i = $heroCount; $i < $level; $i++)
		        {
		        	if ((!$hasActHero)&&$i==$idx)
		        	{
		        		mt_srand(mt_rand());
		    			generateHeroFromConfig($cid,$level);
		        		$hasActHero = true;
		        	}
		        	else
		        	{
		            	generateRecruitHero($cid,$level);
		            }
		        }
		        if($last_reset_recruit!=0){//没用招贤榜
		        	sql_query("insert into log_act (uid, actid, sort, type, count, log_type, time) values ($uid, $actid, $sort, 0, 1, $log_type, unix_timestamp())");
		        }
	        }
	        else
	        {
		        for ($i = $heroCount; $i < $level; $i++)
		        {
		            generateRecruitHero($cid,$level);
		        }  
	        }
	        sql_query("update mem_city_schedule set last_reset_recruit=unix_timestamp() where cid='$cid'");      
	    }

	    $heroes = sql_fetch_rows("select * from sys_recruit_hero where `cid`='$cid' order by id desc");
		foreach($heroes as $hero)
		{
			$recruit = new HeroRecruit();
			$recruit->id = $hero['id'];
			$recruit->hname = $hero['name'];
			$recruit->sex = (int)$hero['sex'];
			$recruit->face = (int)$hero['face'];
			$recruit->cid = $hero['cid'];
			$recruit->level = $hero['level'];
			$recruit->affairs_base = $hero['affairs_base'];
			$recruit->bravery_base = $hero['bravery_base'];
			$recruit->wisdom_base = $hero['wisdom_base'];
			$recruit->command_base = $hero['command_base'];
			$recruit->command_add = $hero['command_add'];
			$recruit->affairs_add = $hero['affairs_add'];
			$recruit->bravery_add = $hero['bravery_add'];
			$recruit->wisdom_add = $hero['wisdom_add'];
			$recruit->loyalty = $hero['loyalty'];
			$recruit->gold_need = $hero['gold_need'];
			$recruit->isActHero = isActHero($hero['herotype']);
			$ret[] = $recruit;
		}
    }
	return $ret;
}
//读取满足要求可以结交的武将
function getJiejiaoHero($uid,$cid,$param)
{
	/*
	//将之前接的名将任务也放到sys_lionize里，好感度为0；
	$heros = sql_fetch_rows("select distinct b.group from sys_user_task a left join cfg_task b on b.id=a.tid  left join cfg_task_group c on c.id=b.group where a.tid>20000 and a.tid<40000 and c.type=2 and uid=$uid");
	foreach($heros as &$hero)
	{
		$hid=$hero['group']/10-2000;
		if(!sql_check("select * from sys_lionize where uid=$uid and npcid=$hid"))
		{
			sql_query("insert into sys_lionize (uid,npcid,friend,state) values ($uid,$hid,0,0) on duplicate key update state=0");
		}
	}*/
	$ret = array();
	//$groupid = 
	//$groupid = "select DISTINCT floor((b.group-20000)/10) from sys_user_task a left join cfg_task b on a.tid=b.id left join cfg_task_group c on b.group=c.id where a.uid=$uid and c.type=2";
	//$npcCount = sql_fetch_one_cell("select count(DISTINCT b.group) from sys_user_task a left join cfg_task b on a.tid=b.id left join cfg_task_group c on b.group=c.id where a.uid=$uid and c.type=2");			
	$npcid=sql_fetch_rows("select a.*,b.name as npcname,c.introduce,d.state,d.friend,e.friend as npcstate from sys_lionize d left join cfg_npc_hero c on d.npcid=c.npcid left join sys_city_hero a on c.npcid=a.npcid left join sys_user b on a.uid=b.uid left join sys_lionize e on b.uid=e.uid and a.npcid=e.npcid where d.uid=$uid");
	$npccount=sql_fetch_rows("select * from sys_lionize");
	$ret[]=$npcid;
	$ret[]=$npccount;
	$ret[]=isPersuadeClose()?1:0;
	return $ret;
	
}

function isPersuadeClose()
{
	//pcy 去除游说功能
	$open_time=sql_fetch_one_cell("select value from mem_state where state=6");
	if($open_time>1311696000){	//2011-07-27 之后的服务器不开启
		return true;
	}
	return false;
}

function tryLionizeHero($uid,$cid,$param)
{
	$id = intval(array_shift($param));
	$hid = sql_fetch_one_cell("select typeid from sys_city_rumor where id='$id'");	
	//$hid = $rumor['typeid'];
	$hero = sql_fetch_one("select * from sys_city_hero where hid='$hid'");
	//$gold_need = getHeroSummonGold($hero);
	$gold_need=$hero['level']*500;
	$ret = array ();
	$ret [] = $hid;
	$ret [] = $gold_need;
	return $ret;
}

//结交指定武将
function LionizeHero($uid,$cid,$param)
{
//	$id = array_shift($param);
//	$rumor = sql_fetch_one("select * from sys_city_rumor where id='$id'");	
//	$hid = $rumor['typeid'];
	
	$hid = intval(array_shift($param));
	
	if(0==sql_fetch_one_cell("select npcid from sys_city_hero where hid=$hid"))
	{
		throw new Exception($GLOBALS['recruitHero']['not_hero']); 
	}
	
	$limitCount=2;
	$endtime=sql_fetch_one_cell("select endtime from mem_user_buffer where uid='$uid' and buftype=22 and endtime>unix_timestamp()");
    if(!empty($endtime))
    {
    	$limitCount=5;
    }
    $loccount = sql_fetch_one_cell("select count(*) from sys_lionize where uid=$uid and state=1");
	if($loccount>=$limitCount)
    {
    	 
    	if(!empty($endtime)) throw new Exception($GLOBALS['recruitHero']['no_site']);
    	else throw new Exception("ask_to_use_qiuxianzhao");    
    }
    
    
	
	
	//$hid = array_shift($param);
	$mystate = sql_fetch_one_cell("select state from sys_lionize where uid=$uid and npcid=$hid");
	if($mystate!=0)
	{
		//好感度大于0是表明玩家与该名将已结交
		throw new Exception($GLOBALS['recruitHero']['need_not']);
	}
	$hero = sql_fetch_one("select * from sys_city_hero where hid='$hid'");
	//$gold_need = getHeroSummonGold($hero);
	$gold_need=$hero['level']*500;
	$citygold = sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
	if ($gold_need > $citygold)
    {
        throw new Exception($GLOBALS['recruitHero']['no_enough_gold']);
    }
    
    $mynobility = sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
    $mynobility = getBufferNobility ( $uid, $mynobility);
    if($mynobility<2)
    {
    	throw new Exception($GLOBALS['recruitHero']['no_enough_nobility']);
    }
    addCityResources($cid,0,0,0,0,-$gold_need);
    sql_query("insert into sys_lionize(uid,npcid,friend,state) values ($uid,$hid,10,1) on duplicate key update friend=10,state=1");
   // sql_query("update sys_lionize set state=1,friend=10 where uid=$uid and npcid=$hid");
    $ret = array();
    $ret[]= $hid;
    $ret[]=$GLOBALS['recruitHero']['jiejiao_succ'];
    //return $ret;
    completeTask($uid,999);
    completeTaskWithTaskid($uid,331);
    logUserAction($uid,7);
    throw new Exception($GLOBALS['recruitHero']['jiejiao_succ']);
    
}

//生成名将任务列表，每周第一次点击生成，好感度奖励上限20；
function getHeroPublicTask($uid,$cid,$param)
{
	$hid=intval(array_shift($param));
	$state= sql_fetch_one_cell("select `state` from sys_lionize where uid=$uid and npcid=$hid");
	if(empty($state))
	{
		throw new Exception($GLOBALS['getHeroPublicTask']['have_bay']);
	}
	if($state==2||$state==3)
	{
		throw new Exception($GLOBALS['getHeroPublicTask']['state_err']);
	}
	$state=array_shift($param);
	$group=$hid*10+20001;
	$ret = array();
	$taskcount = sql_fetch_one_cell("select count(*) from sys_hero_task where uid=$uid and `group`=$group");
	if($taskcount==0)
	{
		createTaskList(intval($state/20),$uid,$group);
	}

	//throw new Exception(sql_fetch_one_cell("select count(*) from sys_hero_task c left join cfg_task a on c.tid=a.id left join cfg_task_reward b on a.id=b.tid where c.uid=$uid and c.group=$group and c.state=0"));
	$tasklist = sql_fetch_rows("select a.*,b.name as reward,c.group from sys_hero_task c left join cfg_task a on c.tid=a.id left join cfg_task_reward b on a.id=b.tid where c.uid=$uid and c.group=$group and c.state=0");
	$ret[] = $tasklist;
	return $ret;
	//throw new Exception($state);
}

//根据不同好感度生成任务
function createTaskList($tasklevel,$uid,$group)
{
	$hid=($group-20001)/10;
	$herosex=sql_fetch_one_cell("select sex from sys_city_hero where hid=$hid");
	switch ($tasklevel)
	{
		case 0:
			{
				$tempcount=0;
				while ($tempcount<6)
				{
					$locid=mt_rand(401,427);
					if(($herosex==0) && ($locid==401))
					{
						continue;
					}
					if(0==sql_fetch_one_cell("select count(*) from sys_hero_task where uid=$uid and `group`=$group and tid=$locid"))
					{
						sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");
						$tempcount++;
					}					
				}
				//throw new Exception($tempcount);
				//throw new Exception(sql_fetch_one_cell("select count(*) from sys_hero_task c left join cfg_task a on c.tid=a.id left join cfg_task_reward b on a.id=b.tid where c.uid=$uid and c.group=$group and c.state=0"));
				
				break;
			}
		case 1:
			{
				$tempcount=0;
				while ($tempcount<5)
				{
					$locid=mt_rand(401,427);
					if(($herosex==0) && ($locid==401))
					{
						continue;
					}
					if(0==sql_fetch_one_cell("select count(*) from sys_hero_task where uid=$uid and `group`=$group and tid=$locid"))
					{
						sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");
						$tempcount++;
					}					
				}
				
				$tempcount=0;
				while ($tempcount<1)
				{
					$locid=mt_rand(501,527);
					if(($herosex==0) && ($locid==501))
					{
						continue;
					}
					sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");
					$tempcount++;
				}
				
				break;
			}
		case 2:
			{
				$tempcount=0;
				while ($tempcount<3)
				{
					$locid=mt_rand(401,427);
					if(($herosex==0) && ($locid==401))
					{
						continue;
					}
					if(0==sql_fetch_one_cell("select count(*) from sys_hero_task where uid=$uid and `group`=$group and tid=$locid"))
					{
						sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");
						$tempcount++;
					}					
				}
				$tempcount=0;
				while ($tempcount<2)
				{
					$locid=mt_rand(501,527);
					if(($herosex==0) && ($locid==501))
					{
						continue;
					}
					if(0==sql_fetch_one_cell("select count(*) from sys_hero_task where uid=$uid and `group`=$group and tid=$locid"))
					{
						sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");
						$tempcount++;
					}					
				}
				break;
			}
		case 3:
			{		
				$tempcount=0;
				while ($tempcount<1)
				{		
					$locid=mt_rand(401,427);
					if(($herosex==0) && ($locid==401))
					{
						continue;
					}					
					sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");
					$tempcount++;
				}								
				
				$tempcount=0;
				while ($tempcount<1)
				{	
					$locid=mt_rand(501,527);
					if(($herosex==0) && ($locid==501))
					{
						continue;
					}						
					sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");					
					$tempcount++;
				}
					
				$tempcount=0;
				while ($tempcount<1)
				{	
					$locid=mt_rand(601,627);
					if(($herosex==0) && ($locid==601))
					{
						continue;
					}	
					sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");
					$tempcount++;
				}
				break;
			}
		default: 
			{
				switch (mt_rand(1,3))
				{
					case 1:
						{
							$tempcount=0;
							while ($tempcount<1)
							{
								$locid=mt_rand(701,727);
								if(($herosex==0) && ($locid==701))
								{
									continue;
								}
								sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");
								$tempcount++;
							}
							break;
						}
					case 2:
						{
							$tempcount=0;
							while ($tempcount<2)
							{
								$locid=mt_rand(601,627);
								if(($herosex==0) && ($locid==601))
								{
									continue;
								}
								if(0==sql_fetch_one_cell("select count(*) from sys_hero_task where uid=$uid and `group`=$group and tid=$locid"))
								{
									sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");
									$tempcount++;
								}					
							}
							break;
						}
					case 3:
						{
							$tempcount=0;
							while ($tempcount<2)
							{
								$locid=mt_rand(501,527);
								if(($herosex==0) && ($locid==501))
								{
									continue;
								}
								if(0==sql_fetch_one_cell("select count(*) from sys_hero_task where uid=$uid and `group`=$group and tid=$locid"))
								{
									sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");
									$tempcount++;
								}					
							}
							
							$tempcount=0;
							while ($tempcount<1)
							{
								$locid=mt_rand(601,627);
								if(($herosex==0) && ($locid==601))
								{
									continue;
								}
								sql_query("insert into sys_hero_task (uid,`group`,tid,`state`) values($uid,$group,$locid,0)");
								$tempcount++;
							}
							break;	
						}					
				}
				break;
			}
	}
}

//1,城守；2,出征，3,战斗,4，驻守,5,俘虏, 6,投奔， 7,主将, 8,军师 9,流亡,10 历练,11 历练返回',
//游说指定武将
function PersuadeHero($uid,$cid,$param)
{
	$cid=intval($cid);
	if(isPersuadeClose()){
		throw new Exception($GLOBALS['persuadeHero']['closed']);
	}
	
	if(!cityHasHeroPosition($uid,$cid))
	{
		throw new Exception($GLOBALS['recruitHero']['hotel_level_low']);
	}
	
	
	$hid=intval(array_shift($param));
	
	
	$hero = sql_fetch_one("select * from sys_city_hero where npcid=$hid");//名将主人的id
	/***********/
	if ($hid == 894) {
		throw new Exception($GLOBALS['beizhanluoyang']["lubu_exception"]);
	}
	/***********/
	//如果，名将是npc的加限制
	if($hero['uid']<1000){
		if (sql_check("select 1 from mem_state where state=197 and value=60") && sql_check("select 1 from mem_state where state=50 and value=1")) {
			throw new Exception($GLOBALS['persuadeHero']['persuade_err']);
		}
		
		//史诗任务未完成的不能游说  不在城里的不管。
		if(sql_check("select * from mem_state where state=5 and value<>1") && sql_check("select * from sys_city where cid='$hero[cid]'")){
			throw new Exception($GLOBALS['persuadeHero']['sstask_doing']);
		}	
		

		if(sql_check("select * from sys_user where uid=$hid and passtype='npc' and flagchar<>''")){
			throw new Exception($GLOBALS['persuadeHero']['its_boss']);
		}
		//史诗任务完成后 有手下的名将布能游说。

		$followercount = sql_fetch_one_cell("select count(*) from sys_city_hero where uid=$hid and herotype<>100");

		if($followercount>1 && $hid<1000){
			$locheroname = sql_fetch_one_cell("select name from sys_city_hero where npcid=$hid");
			$havefollower = sprintf($GLOBALS['persuadeHero']['have_follower'],$locheroname,$followercount-1);
			throw new Exception($havefollower);
		}
		
		
	}
	
	$myfriend = sql_fetch_one_cell("select friend from sys_lionize where uid=$uid and npcid=$hid");//我与该名将的好感度
	
	
	if(100>$myfriend)
	{
		throw new Exception($GLOBALS['persuadeHero']['no_enough_friend']);
	}
	
	$state = sql_fetch_one_cell("select `state` from sys_lionize where uid=$uid and npcid=$hid");
	
	if($state==2)
	{
		throw new Exception($GLOBALS['persuadeHero']['zijiren']);
	}
	else if($state==3)
	{
		throw new Exception($GLOBALS['persuadeHero']['fulu']);
	}
	
	
	//如果该将领不在 城内就弹出提示；1，7，8，重伤
	if($hero['state']==2 || $hero['state']==3 || $hero['state']==5 || $hero['state']==6 || $hero['state']==9 || $hero['state']==10 || $hero['state']==11)
	{
		throw new Exception($GLOBALS['persuadeHero']['not_here']);
	}
	
	//在野外驻守状态的名将，是有主名将的不可以游说
	if($hero['state']==4 && $hero['uid']!=0)
	{
		throw new Exception($GLOBALS['persuadeHero']['not_here']);
	}
	
	$masterfriend = sql_fetch_one_cell("select max(friend) from sys_lionize where state=2 and npcid=$hid");
	if(80<=$masterfriend)
	{
		throw new Exception($GLOBALS['persuadeHero']['too_high_friend']);
	}	
	
	//每天只能使用一次
	$lastusetime= sql_fetch_one_cell("select last_persuade from mem_user_schedule where uid=$uid");
	$now = sql_fetch_one_cell("select unix_timestamp()");	
	if($now<=($lastusetime+3600*24))
	{
		throw new Exception($GLOBALS['persuadeHero']['time_err']);
	}	
	$userjinnang = sql_fetch_one_cell("select sum(count) from sys_goods where uid='$uid' and gid=13");
	if ($userjinnang < 15)
	{
		$tempnum = 15 - $userjinnang;
		throw new Exception("not_enough_goods13#$tempnum");
	}
	
	//开始执行
	addGoods($uid,13,-15,0);
	
	sql_query("insert into mem_user_schedule (uid,last_persuade) values ($uid,unix_timestamp()) on duplicate key update last_persuade=unix_timestamp()");
		
	//$loyalty = sql_fetch_one_cell("select loyalty from sys_user where npcid=$hid");
	$loyalty=$hero['loyalty'];
	
	$ret = array();
	//防止名将同时被多人游说，再算一次好感度
	$masterfriend = sql_fetch_one_cell("select max(friend) from sys_lionize where state=2 and npcid='$hid'");
	if (empty($masterfriend)&&$hero['uid']<=1000) {
		$masterfriend = 50;//npc的将领好感度默认50
	}
	if($masterfriend<80 && mt_rand(0,($loyalty+$masterfriend))<$myfriend/2.5)//游说成功 概率=使用方好感度/(主人好感度+忠诚度)//游说概率降低2.5倍
	{
		sql_query("update sys_lionize set state=2 where uid=$uid and npcid=$hid");
        sql_query("insert into log_lionize(uid,npcid,`type`,`count`,`time`)  select  uid,npcid,4,friend,unix_timestamp() from sys_lionize where state=2 and uid=$uid and npcid=$hid");
 		sql_query("insert into log_lionize(uid,npcid,`type`,`count`,`time`)  select  uid,npcid,15,0,unix_timestamp() from sys_lionize where state=2 and uid<>$uid and npcid=$hid");		
		sql_query("delete from sys_lionize where state=2 and uid<>$uid and npcid=$hid");	

		if($hero['state']==4)//驻守状态被游说要清理部队
		{
			sql_query("delete from sys_troops where hid=$hid");//
		}
		
		sql_query("delete from sys_hero_rest where hid=$hid");
		
		//把原来修养状态的重新变为重伤状态
		sql_query ("update sys_city_hero set hero_health=1 where hero_health=2 and hid='$hid' limit 1");
		
		
		deleteHeroBaseAdd($hid);//修改把所有额外基础属性都清了 by CY
		
		//城守
		if($hid==sql_fetch_one_cell("select chiefhid from sys_city where cid='$hero[cid]'"))
		{
			sql_query("update sys_city set chiefhid=0 where cid='$hero[cid]'");
		}
		//主将
		if($hid==sql_fetch_one_cell("select generalid from sys_city where cid='$hero[cid]'"))
		{
			sql_query("update sys_city set generalid=0 where cid='$hero[cid]'");
		}
		//军师
		if($hid==sql_fetch_one_cell("select counsellorid from sys_city where cid='$hero[cid]'"))
		{
			sql_query("update sys_city set counsellorid=0 where cid='$hero[cid]'");
		}
		
		if($hero['uid']>1000)
		{
			

			$content = $GLOBALS['PersuadeHero']['PersuadeHero_mail_content'];
			$title = $hero['name'].$GLOBALS['PersuadeHero']['PersuadeHero_mail_title'];
			sendSysMail($hero['uid'],$title,$content);
			
			/*
			$cityname = sql_fetch_one_cell("select `name` from sys_city where cid=$cid");
			$x = $cid % 1000;
			$y = floor($cid / 1000);
			
			
		    $mid = sql_insert("insert into sys_mail_content (`content`,`posttime`) values ('$content',unix_timestamp())");	    
		    sql_insert("insert into sys_mail_box (`uid`,`name`,`fromuid`,`fromname`,`contentid`,`title`,`read`,`recvstate`,`sendstate`,`posttime`) values ('$hero[uid]','$receiver[name]','$uid','$leader[name]','$mid','$title','0','0','0',unix_timestamp())");
			*/
		}
		else {
			$bravery=$hero['bravery_base']+$hero['bravery_add'];
			$wisdom=$hero['wisdom_base']+$hero['wisdom_add'];
			$forcemax=100+floor($hero['level']/5)+floor($bravery/3);
			$energymax=100+floor($hero['level']/5)+floor($wisdom/3);
			//体力百分比
			$forcepercent = sql_fetch_one_cell("select value from sys_hero_attribute where attid=10005 and hid=$hid");
			if(empty($forcepercent)) $forcepercent = 0;
			$forceAdd += floor($forcemax * $forcepercent / 100);
			//精力百分比
			$energypercent = sql_fetch_one_cell("select value from sys_hero_attribute where attid=10006 and hid=$hid");
			if(empty($energypercent)) $energypercent = 0;
			$energyAdd += floor($energymax * $energypercent / 100);
			$braveryAdd=0;
			$wisdomAdd=0;
			$forcemax += floor(($braveryAdd + $bravery % 3) / 3) + $forceAdd;
			$energymax += floor(($wisdomAdd + $wisdom % 3)/ 3) + $energyAdd;
			sql_query("insert into mem_hero_blood (hid,`force`,force_max,`energy`,energy_max) values ($hid,$forcemax,$forcemax,$energymax,$energymax) on duplicate key update `force`=$forcemax,force_max=$forcemax,`energy`=$energymax,energy_max=$energymax");
		}
		
		$tasks=sql_fetch_rows("select s.tid from sys_user_task s left join cfg_task_goal c on s.tid=c.tid where s.uid=$uid and s.state=0 and s.tid>400000 and s.tid<500000 and (c.sort=10 or c.sort=12) and c.type=$hid");
		
		foreach ($tasks as $loctasks)
		{
			sql_query("insert into sys_attack_position (uid,tid,cid,`state`) values ($uid,'$loctasks[tid]','$hero[cid]',1) on duplicate key update `state`=1");
		}
		
		
		
		sql_query("delete from sys_hero_armor where hid=$hid");
		sql_query("update sys_user_book set hid=0 where hid=$hid");
		sql_query("update sys_user_armor set hid=0 where hid=$hid");
		/*
		$row = sql_fetch_one ( "select sum(command_base_add_on) as command_base_add_on, sum(affairs_base_add_on) asaffairs_base_add_on ,sum(bravery_base_add_on) as bravery_base_add_on,sum(wisdom_base_add_on) as  wisdom_base_add_on from  sys_city_hero_base_add where type=3 and hid = $hid" );
		if (!empty ( $row ))
		{				
			$bravery_base_add_on = 0 - intval ( $row ["bravery_base_add_on"] );
			$wisdom_base_add_on = 0 - intval ( $row ["wisdom_base_add_on"] );
			$affairs_base_add_on = 0 - intval ( $row ["affairs_base_add_on"] );
			$command_base_add_on = 0 - intval ( $row ["command_base_add_on"] );
			sql_query ( "update sys_city_hero set bravery_base=bravery_base+$bravery_base_add_on,wisdom_base=wisdom_base+$wisdom_base_add_on,affairs_base=affairs_base+$affairs_base_add_on,command_base=command_base+$command_base_add_on where hid = $hid" );
			sql_query ( "delete from sys_city_hero_base_add where type=3 and hid = $hid");
		}*/
		
		
		$taskminid = 10*($hid+40000);

		sql_query("delete from sys_user_task where tid>$taskminid and tid<($taskminid+10)");//清 原master的专属任务
		sql_query("delete from sys_user_goal where gid>$taskminid and gid<($taskminid+10)");//清 user goal
		sql_query("delete from sys_attack_position where tid>$taskminid and tid<($taskminid+10)");//清 市井传闻 的目标
		
		/*
		sql_query("update sys_city_hero set `state`=0 where npcid=$hid");
		sql_query("update sys_city_hero set loyalty=40 where npcid=$hid");
		*/
		
		
		
		sql_query("update sys_city_hero set uid=$uid,cid=$cid,`state`=0,loyalty=40 where npcid=$hid");
   
		regenerateHeroAttri($uid,$hid);
		
		updateCityHeroChange($uid,$cid);
		$ret[]=$GLOBALS['persuadeHero']['persuade_succ'];
		
		$uname = sql_fetch_one_cell ( "select name from sys_user where uid='$uid';" );
		$msg = sprintf ( $GLOBALS['PersuadeHero']['npc'], $hero ["name"], $uname );
		sendSysInform(0,1,0,600,50000,1,16738740,$msg);
	}
	else {
		$ret[]=$GLOBALS['persuadeHero']['persuade_err'];
	}
	return $ret;
}

function getTheTask($uid,$cid,$param)
{
	$hotelLevel = sql_fetch_one_cell("select level from sys_building where cid='$cid' and bid=".ID_BUILDING_HOTEL);
	if($hotelLevel<5)
	{
		throw new Exception($GLOBALS['getTheTask']['hotel_level_low']);
	}
	$tid   = intval(array_shift($param));
	$group = intval(array_shift($param));
	$hid=($group-20001)/10;
	$state= sql_fetch_one_cell("select `state` from sys_lionize where uid=$uid and npcid=$hid");
	if(empty($state))
	{
		throw new Exception($GLOBALS['getHeroPublicTask']['have_bay']);
	}
	sql_query("update sys_hero_task set state=1,endtime=unix_timestamp()+3600*24 where uid=$uid and `group`=$group and tid=$tid");
	//sql_query("insert into sys_user_task (uid,tid,`state`) values($uid,$tid,0)");
	$ret = array();
	$ret[]= $tid;
    $ret[]= $group;
    $ret[]=$GLOBALS['getTheTask']['get_succ'];
    return $ret;
	
}

function getHotelInfo($uid,$cid)
{
	//在这里做一个手脚，在玩家取客栈信息的时候，自动补齐一个野兵的将领
	$npcHeroCount = sql_fetch_one_cell("select count(*) from sys_recruit_hero where `cid`=0");
	if ($npcHeroCount < 1000)
	{
		mt_srand(time());
		generateRecruitHero(0,5);
	}


	$hotel = sql_fetch_one("select * from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_HOTEL." order by level desc limit 1");
	if (empty($hotel))
	{
		throw new Exception($GLOBALS['getHotelInfo']['no_hotel_built']);
	}
	return doGetBuildingInfo($uid,$cid,$hotel['xy'],ID_BUILDING_HOTEL,$hotel['level']);
}

//开始招人
function recruitHero($uid,$cid,$param)
{
	$id = intval(array_shift($param));
    $tmpHero = sql_fetch_one("select * from sys_recruit_hero where cid='$cid' and `id`='$id'");
    if (!empty($tmpHero))
    {                             
        if (cityHasHeroPosition($uid,$cid))
        {
            $citygold = sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
            if ($tmpHero['gold_need'] > $citygold)
            {
                throw new Exception($GLOBALS['recruitHero']['no_enough_gold']);
            }
            
       		if($tmpHero[herotype]>10){//活动将领拥有上限
				$hero=sql_fetch_one("select * from cfg_recruit_hero where herotype = '$tmpHero[herotype]'");
				$userhavecnt = $hero["userhavecnt"];
				if ($userhavecnt>0 && $userhavecnt<=intval(sql_fetch_one_cell("select count(*) from sys_city_hero where uid='$uid' and herotype = '$tmpHero[herotype]'"))) {
					throw new Exception(sprintf($GLOBALS['recruitHero']['already_Have_One'],$userhavecnt,$tmpHero["name"]));
				}
			}
            
            //花钱
            addCityResources($cid,0,0,0,0,-$tmpHero['gold_need']);
            //招人
            $sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`command_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`command_add_on`,`loyalty`,`herotype`) values ('$uid','$tmpHero[name]','$tmpHero[sex]','$tmpHero[face]','$cid','0','$tmpHero[level]','$tmpHero[exp]','$tmpHero[affairs_base]','$tmpHero[bravery_base]','$tmpHero[wisdom_base]','$tmpHero[command_base]','$tmpHero[affairs_add]','$tmpHero[bravery_add]','$tmpHero[wisdom_add]','$tmpHero[command_add]','$tmpHero[loyalty]','$tmpHero[herotype]')";
            $hid = sql_insert($sql);
            $forcemax=100+floor($tmpHero['level']/5)+floor(($tmpHero['bravery_base']+$tmpHero['bravery_add'])/3);
            $energymax=100+floor($tmpHero['level']/5)+floor(($tmpHero['wisdom_base']+$tmpHero['wisdom_add'])/3);
            sql_query("insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`) values ('$hid',100,$forcemax,100,$energymax)");
            
            $retmsg = checkAndDoRecruitHeroAct($uid, $cid, $tmpHero['herotype']);//招募将领活动
            //砍人
            sql_query("delete from sys_recruit_hero where id='$id'");
            updateCityHeroChange($uid,$cid);  
            completeTask($uid,84);
            //成就系统：：综合（可造之材）
            if($tmpHero['affairs_base']>=80||$tmpHero['bravery_base']>=80||$tmpHero['wisdom_base']>=80){
            	finishAchivement($uid,35);
            }
        }
        else
        {
            throw new Exception($GLOBALS['recruitHero']['hotel_level_low']);
        }
    }
    $ret=getHotelInfo($uid,$cid);
	if($retmsg){
		$ret[]=sprintf($GLOBALS['act']['msg_tip'],$retmsg);
	}
	return $ret;          
}  

function onCancelFriend($uid,$cid,$param)
{
	$hid = intval(array_shift($param));
	$cid = intval($cid);
	
	$state = sql_fetch_one_cell("select `state` from sys_lionize where uid=$uid and npcid=$hid");
	if($state==2)
	{
		throw new Exception($GLOBALS['cancelHeroFriend']['cancel_fail_zijiren']);
	}else if($state==3)
	{
		throw new Exception($GLOBALS['cancelHeroFriend']['cancel_fail_fulu']);
	}
	
	$taskgroup=$hid*10+20001;
	
	sql_query("delete from sys_hero_task where uid='$uid' and `group`=$taskgroup");
	sql_query("insert into log_lionize(uid,npcid,`type`,`count`,`time`)  select  uid,npcid,17,0,unix_timestamp() from sys_lionize where uid=$uid and npcid=$hid");
	sql_query("delete from sys_lionize where uid=$uid and npcid=$hid");
	$ret = array();
	$ret[]=$GLOBALS['cancelHeroFriend']['cancel_succ'];
	return $ret;
}

function resetRecruitHero($uid,$cid,$param)
{
	useZhaoXinLin($uid,$cid);
	return getHotelInfo($uid,$cid);
}
function addHeroRumor($cid,$npcid,$price)
{
	$heroname = sql_fetch_one_cell("select name from cfg_npc_hero where npcid='$npcid'");
	sql_query("insert into sys_city_rumor (cid,name,type,typeid,price) values ('$cid','$heroname',0,$npcid,$price)");
}
function generateHeroRumor($cid)
{

	$cityHeroRumors = sql_fetch_one_cell("select group_concat(typeid) from sys_city_rumor where cid='$cid' and type=0");
	if (empty($cityHeroRumors))
	{
		$filter = "";
	}
	else
	{
		$filter = "where npcid not in ($cityHeroRumors) ";
	}
	$rumor = sql_fetch_one("select * from cfg_rumor_hero $filter order by rand() limit 1");
	addHeroRumor($cid,$rumor['npcid'],$rumor['price']);
}
function addThingRumor($cid,$tid,$price)
{
	
	$thingname = sql_fetch_one_cell("select name from cfg_things where tid='$tid'");

	sql_query("insert into sys_city_rumor (cid,name,type,typeid,price) values ('$cid','$thingname',1,$tid,$price)");
}
function generateThingRumor($cid)
{

	$cityThingRumors = sql_fetch_one_cell("select group_concat(typeid) from sys_city_rumor where cid='$cid' and type=1");

	if (empty($cityThingRumors))
	{
		$filter = "";
	}
	else
	{
		$filter = "where tid not in ($cityThingRumors) ";
	}
	$rumor = sql_fetch_one("select * from cfg_rumor_thing $filter order by rand() limit 1");
	if (empty($rumor)) return false;
	addThingRumor($cid,$rumor['tid'],$rumor['price']);
	return true;
}

function addTroopRumor($cid,$tid,$price)
{	
	$Troopname = sql_fetch_one_cell("select name from cfg_troop_task where tid='$tid'");
	sql_query("insert into sys_city_rumor (cid,name,type,typeid,price) values ('$cid','$Troopname',2,$tid,$price)");
}
//市井传闻
function getRumorList($uid,$cid,$param)
{
	$hotellevel = sql_fetch_one_cell("select b.level from sys_building b,sys_user u where b.cid=u.lastcid and b.bid=".ID_BUILDING_HOTEL." and u.uid='$uid' limit 1");
	if($hotellevel<5)
	{
		throw new Exception($GLOBALS['getRumor']['hotel_level_low']);
	}
	else
	{
		$last_reset_rumor = sql_fetch_one_cell("select last_reset_rumor from mem_city_schedule where cid='$cid'");
		if (empty($last_reset_rumor))
		{
			sql_query("insert into mem_city_schedule (cid,last_reset_rumor) values ('$cid',unix_timestamp()) on duplicate key update last_reset_rumor=unix_timestamp()");
			$last_reset_rumor = 0;
		}
		$now = sql_fetch_one_cell("select unix_timestamp()");
		$blocksize = (3600/GAME_SPEED_RATE) / $hotellevel;
		$last_block = floor(($last_reset_rumor+8*3600) / $blocksize);
		$curr_block = floor(($now+8*3600) / $blocksize);
		$blockdelta = $curr_block - $last_block;
		if ($blockdelta > 0)
		{
			sql_query("delete from sys_city_rumor where cid='$cid' order by id limit $blockdelta");
			$rumorCount = sql_fetch_one_cell("select count(*) from sys_city_rumor where cid='$cid'");


			for ($i = $rumorCount; $i < $hotellevel; $i++)
			{
				//if (mt_rand() & 1) //武将
				{
					generateHeroRumor($cid);
				}
				/*else
				 {
				 if (!generateThingRumor($cid))      //如果任务物品没有新的话，就再放一个将领
				 {
				 generateHeroRumor($cid);
				 }
				 }*/
			}
			sql_query("update mem_city_schedule set last_reset_rumor=unix_timestamp() where cid='$cid'");
		}
	}

	$ret = array();
	$rumors = sql_fetch_rows("select * from sys_city_rumor where `cid`='$cid' order by id desc");
	$count = sql_fetch_one_cell("select count(*) from sys_lionize where uid=$uid and `state`=1");
	$ret[]=$rumors;
	//$ret[]=$count;
	/*
	 foreach($rumors as &$rumor)
	 {
	 if ($rumor['type'] == 0)    //武将消息
	 {
	 $rumor['intro'] = sql_fetch_one_cell("select introduce from cfg_npc_hero where id='$rumor[typeid]'");
	 }
	 else if ($rumor['type'] == 1) //任务物品
	 {
	 $rumor['intro'] = sql_fetch_one_cell("select description from cfg_things where id='$rumor[typeid]'");
	 }
	 }
	 */
	return $rumors;
}
function getRumor($uid,$cid,$param)
{
	$id = array_shift($param);
	$rumor = sql_fetch_one("select * from sys_city_rumor where id='$id'");
	if (empty($rumor)) throw new Exception($GLOBALS['getRumor']['never_heard']);
	$ret = array();
	$ret[] = $rumor;
	
	if ($rumor['type'] == 0)  //将领
	{
		$ret[] = sql_fetch_one("select * from cfg_npc_hero where npcid='$rumor[typeid]'");
		$hero  = sql_fetch_one("select * from sys_city_hero where npcid='$rumor[typeid]'");		
		if (empty($hero))
		{
			$ret[] = $GLOBALS['getRumor']['dont_know_where_he_is'];
			$ret[] = false;     //是否有详细信息
		}else{
			$herowid = cid2wid($hero['cid']);
			$provincename = sql_fetch_one_cell("select p.name from mem_world w,cfg_province p where w.province=p.id and w.wid='$herowid'");

			$msg = sprintf($GLOBALS['getRumor']['pay_for_hero'],$hero['name'],$provincename,$rumor['price']);
			$ret[] = $msg;
			$ret[] = true;   //是否有详细信息
		}
		//$ret[] = sql_check("select * from cfg_npc_task where npcid='$rumor[typeid]'");
		$ret[]=true;
	}
	else if ($rumor['type'] == 1)   //任务物品
	{
		$rumorthing = sql_fetch_one("select f.*,r.type,r.price,r.introduce from cfg_rumor_thing r,cfg_things f where r.tid='$rumor[typeid]' and f.tid=r.tid");
		$ret[] = $rumorthing;
		if ($rumorthing['type'] == 0)   //一般物品
		{
			$ret[] = "";
			$ret[] = false; //打听详细    
		}
		else if ($rumorthing['type'] == 1)  //有地点的物品
		{
			$taskid = sql_fetch_one_cell("select tid from cfg_task_goal where sort=5 and type='$rumorthing[tid]' and tid in(select tid from sys_user_task where uid=$uid and state=0)");
			//$goalcid = sql_fetch_one_cell("select cid from sys_attack_position where uid=$uid and state=0 and tid=$taskid ");
			//if($goalcid==null)
			//{
				$x = mt_rand(1,500);
				$y = mt_rand(1,500);
				$goalcid=$y*1000+$x;
				sql_query("insert into sys_attack_position (uid,cid,tid,goods,`name`,`state`) values ('$uid','$goalcid','$taskid',1,0,0) on duplicate key update cid='$goalcid',`state`=0");
			//}
			
			$heropos = getPosDescription($goalcid);
			$msg = sprintf($GLOBALS['askDetail']['hero_location'],$rumorthing['name'],$heropos);
			$ret[] = $msg;
			$ret[] = false;
			/*
			$thingcid = sql_fetch_one_cell("select cid from sys_thing_position where thingid=".$rumorthing['tid']);
			if (empty($thingcid))
			{
				$ret[] = $GLOBALS['getRumor']['dont_know_where_it_is'];
				$ret[] = false; //打听详细
			}
			else
			{
				$thingwid = cid2wid($thingcid);
				$provincename = sql_fetch_one_cell("select p.name from mem_world w,cfg_province p where w.province=p.id and w.wid='$thingwid'");

				$msg = sprintf($GLOBALS['getRumor']['pay_for_staff'],$rumorthing['name'],$provincename,$rumorthing['price']);
				//$ret[] = "听说，".$rumorthing['name']."在".$provincename."。如果你给我".$rumorthing['price']."个元宝，我就告诉你更准确的情报。";  
				$ret[] = $msg;
				$ret[] = true; //打听详细
			}*/
		}
		else if ($rumorthing['type'] == 2)  //特殊说明的物品
		{
			$ret[] = $rumorthing['introduce'];
			$ret[] = true;
		}
		//$ret[] = sql_check("select * from cfg_thing_task where thingid='$rumorthing[tid]'"); //任务 
		$ret[]=false;
	}
	else if ($rumor['type'] == 2)
	{
		//throw new Exception("sdfdfs");
		$rumortroop = sql_fetch_one("select f.*,r.type,r.price,r.introduce from cfg_rumor_troop r,cfg_troop_task f where r.tid='$rumor[typeid]' and f.tid=r.tid");
		$ret[] = $rumortroop;
		$taskid = sql_fetch_one_cell("select tid from cfg_task_goal where (sort=11 or sort=12) and type='$rumortroop[tid]' and tid in(select tid from sys_user_task where uid=$uid and state=0)");
		//$goalcid = sql_fetch_one_cell("select cid from sys_attack_position where uid=$uid and state=0 and tid=$taskid ");
		//if($goalcid==null)
		//{
			$x = mt_rand(1,500);
			$y = mt_rand(1,500);
			$goalcid=$y*1000+$x;
			sql_query("insert into sys_attack_position (uid,cid,tid,goods,`name`,`state`) values ('$uid','$goalcid','$taskid',1,0,0) on duplicate key update cid='$goalcid',`state`=0");
		//}		
		$heropos = getPosDescription($goalcid);
		$msg = sprintf($GLOBALS['askDetail']['hero_location'],$rumortroop['name'],$heropos);
		$ret[] = $msg;
		$ret[] = false;
		$ret[] = false;
	}
	return $ret;
}
function searchRumor($uid,$cid,$param)
{
	$input = trim(array_shift($param));
	$input = addslashes($input);
	
	if (empty($input)) throw new Exception($GLOBALS['searchRumor']['input_name_to_seartch']);
	$hotellevel = sql_fetch_one_cell("select level from sys_building where cid=$cid and bid=".ID_BUILDING_HOTEL." limit 1");
	if ($hotellevel <= 0) throw new Exception($GLOBALS['searchRumor']['no_hotel_built']);
	//判断城池黄金
	if(!lockUser($uid)) throw new Exception($GLOBALS['pacifyPeople']['server_busy']);
	
	$citygold = sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
	if ($citygold < 1000) throw new Exception($GLOBALS['searchRumor']['no_enough_gold']);

	$rumorHero = sql_fetch_rows("select * from cfg_rumor_hero r,cfg_npc_hero n where r.npcid=n.npcid and n.name like '%$input%' order by rand() limit $hotellevel");

	
	//任务物品
	$rumorThing = array();
	
	$mythings = sql_fetch_rows("select type from cfg_task_goal where sort=5 and tid in (select tid from sys_user_task where uid=$uid and state=0 and tid>400000) and type in(select tid from cfg_things where name like '%$input%' and tid in(select tid from cfg_rumor_thing))");
	
	if (count($mythings)>0)
	{
	 
		$rumorThing = sql_fetch_rows("select * from cfg_rumor_thing r,cfg_things t where r.tid=t.tid and t.name='$input' order by rand() limit 1");
	}
	
	//任务军队
	$rumorTroop = array();
	$mytroop = sql_fetch_rows("select type from cfg_task_goal where sort in(11,12) and tid in (select tid from sys_user_task where uid=$uid and state=0 and tid>400000) and type in(select tid from cfg_troop_task where name like '%$input%' and tid in(select tid from cfg_rumor_troop))");
	
	if (count($mytroop)>0)
	{
	 	
		$rumorTroop = sql_fetch_rows("select * from cfg_rumor_troop r,cfg_troop_task t where r.tid=t.tid and t.name='$input' order by rand() limit 1");
	}
	
	
	$newRumorCount = count($rumorHero) + count($rumorThing) + count($rumorTroop);
	if ($newRumorCount <= 0)
	{
		throw new Exception($GLOBALS['searchRumor']['no_useful_info']);
	}
	sql_query("delete from sys_city_rumor where cid='$cid' order by id limit $newRumorCount");
	foreach($rumorHero as $rumor)
	{
		addHeroRumor($cid,$rumor['npcid'],$rumor['price']);
	}
	foreach($rumorThing as $rumor)
	{
		addThingRumor($cid,$rumor['tid'],$rumor['price']);
	}
	foreach($rumorTroop as $rumor)
	{
		addTroopRumor($cid,$rumor['tid'],$rumor['price']);
	}
	sql_query("update mem_city_schedule set last_reset_rumor=unix_timestamp() where cid='$cid'");
	sql_query("update mem_city_resource set gold=gold-1000 where cid='$cid'");
	unlockUser($uid);
	completeTask($uid,367);
	return sql_fetch_rows("select * from sys_city_rumor where `cid`='$cid' order by id desc");
}
function moreRumor($uid,$cid,$param)
{
	if(!lockUser($uid)) throw new Exception($GLOBALS['pacifyPeople']['server_busy']);
	$citygold = sql_fetch_one_cell("select gold from mem_city_resource where cid='$cid'");
	if ($citygold < 100) throw new Exception($GLOBALS['moreRumor']['no_enough_gold']);
	sql_query("update mem_city_resource set gold=gold-100 where cid='$cid'");
	sql_query("update mem_city_schedule set last_reset_rumor=0 where cid='$cid'");
	unlockUser($uid);
	return getRumorList($uid,$cid,$param);
}
function getPosDescription($cid)
{
	$ret = "";
	$worldtype = sql_fetch_one_cell("select type from mem_world where wid=".cid2wid($cid));
	if ($worldtype == 0)
	{
		$ret .= sql_fetch_one_cell("select name from sys_city where cid='$cid'");
	}
	else
	{
		$ret .= sql_fetch_one_cell("select name from cfg_world_type where type='$worldtype'");
	}

	$ret .= "[".($cid % 1000).",".floor($cid/1000)."]";
	return $ret;
}
function askDetail($uid,$cid,$param)
{
	$id = intval(array_shift($param));
	$paytype=array_shift($param);
	if($paytype!=0&&$paytype!=1){
		throw new Exception($GLOBALS['buyGoods']['invalid_pay_type']);
	}
	
	$rumor = sql_fetch_one("select * from sys_city_rumor where id='$id'");
	if (empty($rumor)) throw new Exception($GLOBALS['askDetail']['never_heard']);
	$ret=array();
	$ret[] = $rumor;
	if(!lockUser($uid)) throw new Exception($GLOBALS['pacifyPeople']['server_busy']);
	
	
	$userInfo = sql_fetch_one("select money,gift from sys_user where uid='$uid'");
	
	
	//元宝
	$userMoney=$userInfo['money'];
	//礼金
	$userGift=$userInfo['gift'];
	//if ($money < $rumor['price']) throw new Exception($GLOBALS['askDetail']['no_enough_YuanBao']);
	
	if ($paytype==0&&($userMoney < $rumor['price']))	throw new Exception($GLOBALS['askDetail']['no_enough_YuanBao']);
	if ($paytype==1&&($userGift < $rumor['price']))	throw new Exception($GLOBALS['askDetail']['no_enough_Gift']);
	
	
	
	if ($rumor['type'] == 0)  //将领
	{
		$hero  = sql_fetch_one("select * from sys_city_hero where npcid='$rumor[typeid]'");
		if (empty($hero)) throw new Exception($GLOBALS['askDetail']['no_info_of_hero']);
		$heroinfo = sql_fetch_one("select * from cfg_npc_hero where npcid='$rumor[typeid]'");

		
		//名将专属任务   搜索将领 掠夺该所在地
		$mytask = sql_fetch_rows("select a.tid from cfg_task_goal a,sys_user_task b  where a.sort in (10,12) and a.type='$rumor[typeid]' and a.tid=b.tid and  b.uid=$uid and b.state=0 and b.tid>400000");
		if($mytask!=null)
		{
			foreach($mytask as $loctask)
			{
				sql_query("insert into sys_attack_position (uid,cid,tid,goods,`name`,`state`) values ('$uid','$hero[cid]','$loctask[tid]',1,0,0) on duplicate key update cid='$hero[cid]',`state`=0");
			}
		}
		
		
		$ret[] = $heroinfo ;

		$heropos = getPosDescription($hero['cid']);

		$msg = sprintf($GLOBALS['askDetail']['hero_location'],$rumor['name'],$heropos);
		//$msg = "据可靠消息，".$rumor['name']."在".$heropos."。行动一定要快，不要被别人抢先了。";    

		$ret[] = $msg;
		$ret[] = false; //详细消息已经看过了         
		$ret[] = true;//sql_check("select * from cfg_npc_task where npcid='$rumor[typeid]'");//是否有抓将任务

		//添加一个公文公告
		$reportcontent = $heroinfo['name'];
		if (!empty($heroinfo['zi']))
		{
			$reportcontent .= $GLOBALS['askDetail']['word'].$heroinfo['zi'];
		}
		$reportcontent .= "。".$heroinfo['introduce']."<br/>";
		$reportcontent .= $msg;
		//扣钱
		$tid=$heroinfo['npcid']+20000;
		if($paytype==0)
			addMoney($uid,-$rumor['price'],90);
		else if($paytype==1)
			addGift($uid,-$rumor['price'],90);
			
		
		sql_query("insert into sys_things (uid,tid,count) values ('$uid','$tid','1') on duplicate key update `count`='1'");
		
		//活动临时事件 打听将领
		$act_temp=sql_fetch_one("select rate from cfg_act where type=5 and unix_timestamp() between starttime and endtime limit 1");//临时活动事件
		if(!empty($act_temp)){
			sql_query("insert into temp_act_event (uid, type, eid, count) values ($uid, 1003,10000,1) on duplicate key update count=count+1");
		}
		//addThings($uid,$heroinfo['npcid']+20000,1,0);
	}
	else if ($rumor['type'] == 1)  //任务物品   
	{
		
		$thinginfo = sql_fetch_one("select f.*,r.type,r.detail from cfg_rumor_thing r,cfg_things f where r.tid='$rumor[typeid]' and f.tid=r.tid");
		$ret[] = $thinginfo;
		if ($thinginfo['type'] == 0)    //一般物品
		{
			$ret[] = "";
			$ret[] = false; //打听详细 
		}
		else if ($thinginfo['type'] == 1)   //有地点的物品
		{
			$thingcid = sql_fetch_one_cell("select cid from sys_thing_position where thingid=".$thinginfo['tid']);
		
			if (empty($thingcid))
			{
				$msg = sprintf($GLOBALS['askDetail']['no_info_of_staff'],$thinginfo['name']);
				//$ret[] = "我没有关于".$thinginfo['name']."的消息，不劳您费元宝啦。";
				$ret[] = $msg;
				$ret[] = false; //打听详细
			}
			else
			{

				$thingpos = getPosDescription($thingcid);

				$msg = sprintf($GLOBALS['askDetail']['staff_location'],$rumor['name'],$thingpos);
				//$msg = "据可靠消息，".$rumor['name']."在".$thingpos."。行动一定要快，不要被别人抢先了。"; 
				$ret[] = $msg;
				$ret[] = false; //详细消息已经看过了
				$reportcontent = $thinginfo['description']."<br/>".$msg;
				//扣钱
				//addMoney($uid,-$rumor['price'],90);
				if($paytype==0)
					addMoney($uid,-$rumor['price'],90);
				else if($paytype==1)
					addGift($uid,-$rumor['price'],90);
			}
		}
		else if ($thinginfo['type'] == 2)   //特殊物品
		{
			$ret[] = $thinginfo['detail'];
			$ret[] = false;
			$reportcontent = $thinginfo['description']."<br/>".$thinginfo['detail'];
			//扣钱
			//addMoney($uid,-$rumor['price'],90);
			
			if($paytype==0)
				addMoney($uid,-$rumor['price'],90);
			else if($paytype==1)
				addGift($uid,-$rumor['price'],90);
		}
	}

	if (!empty($reportcontent))
	{
		sendReport($uid,'rumor',19,$cid,$cid,$reportcontent);
//		completeTask($uid,367);
	}
	unlockUser($uid);
	logUserAction($uid,9);
	return $ret;
}
function recordTask($uid,$cid,$param)
{
	$id = intval(array_shift($param));
	$rumor = sql_fetch_one("select * from sys_city_rumor where id='$id'");
	if (!empty($rumor) &&$rumor['type'] == 0)
	{
		$npcid = $rumor['typeid'];
		$taskid1 = 20000+$npcid;
		$taskid2 = 30000+$npcid;
		if(sql_check("select uid from sys_user_task where uid='$uid' and (tid='$taskid1' or tid='$taskid2') and state=0"))
		{
			throw new Exception($GLOBALS['recordTask']['task_already_recorded']);
		}
		$npcowner=sql_fetch_one_cell("select uid from sys_city_hero where hid='$npcid'");
		if($npcowner==$uid)
		{
			throw new Exception($GLOBALS['recordTask']['npc_hero_exist']);
		}
		$taskcount=sql_fetch_one_cell("select count(distinct(t.`group`)) from sys_user_task u,cfg_task t where u.tid=t.id and u.uid='$uid' and u.state=0 and u.tid>20000 and u.tid<40000");
		if($taskcount>=25)
		{
			throw new Exception($GLOBALS['recordTask']['task_list_full']);
		}
		sql_query("delete a from sys_user_goal a ,cfg_task_goal b where a.uid='$uid' and a.gid=b.id and (b.tid='$taskid1' or b.tid='$taskid2')");
		sql_query("insert into sys_user_task (uid,tid,state) values ('$uid','$taskid1',0) on duplicate key update state=0");
		sql_query("insert into sys_user_task (uid,tid,state) values ('$uid','$taskid2',0) on duplicate key update state=0");
		//sql_query("insert into sys_lionize (uid,npcid,friend,state) values ($uid,$npcid,0,0) on duplicate key update state=0");
	}
	else if ($rumor['type'] == 1)
	{
		$thingid = $rumor['typeid'];
		$taskid = sql_fetch_one_cell("select taskid from cfg_thing_task where thingid='$thingid'");
		if (empty($taskid))
		{
			throw new Exception($GLOBALS['recordTask']['no_task_related_to_staff']);
		}
	}
	else
	{
		throw new Exception($GLOBALS['recordTask']['no_rumor_to_record']);
	}
	/*$existtask = sql_fetch_one("select * from sys_user_task where uid='$uid' and tid='$taskid'");
	 if (!empty($existtask))
	 {
	 if ($existtask['state'] == 0)
	 {
	 throw new Exception($GLOBALS['recordTask']['task_already_recorded']);
	 }
	 else if ($existtask['state'] == 1)
	 {
	 throw new Exception($GLOBALS['recordTask']['task_accomplished']);
	 }
	 }*/
	logUserAction($uid,8);
	throw new Exception($GLOBALS['recordTask']['task_record_succ']);
}
//鉴定宝藏
function treasureIdentify($uid,$cid,$param){
	//判断是否有鉴宝图
	if (!checkGoods($uid,118))
	{
		throw new Exception("not_enough_goods118");
	}
	
	$paytype=array_shift($param);
//	if($paytype!=0&&$paytype!=1){
//		throw new Exception($GLOBALS['buyGoods']['invalid_pay_type']);
//	}
	
//	$passtype = sql_fetch_one_cell("select passtype from sys_user where uid='$uid'");
//	if ($passtype==='tw') {
//		$cost=3;
//	}else{
//		$cost=10;
//	}
	$cost=1000;
	$userInfo = sql_fetch_one("select money,gift from sys_user where uid='$uid'");
	//元宝
	$userMoney=$userInfo['money'];
	//礼金
	$userGift=$userInfo['gift'];
	//if ($money < $rumor['price']) throw new Exception($GLOBALS['askDetail']['no_enough_YuanBao']);
	if($userMoney-$cost>=0)
		addMoney($uid,(0-$cost),53);
	else if($userGift-$cost>=0)
		addGift($uid,(0-$cost),53);
	  else 
		throw new Exception($GLOBALS['treasure']['not_enough_money']);//钱够不够
	/*if(!checkMoney($uid,10)){
		throw new Exception($GLOBALS['treasure']['not_enough_money']);
	}*/
	//隨機
	$y = floor($cid / 1000);
	$x = ($cid % 1000);

	$y = floor($y / 10);
	$x = floor($x / 10);


	//100个格子以内
	$wstart=($y*100+$x)*100;
	$wend=$wstart+100;

	//选择非平地,随机选一种野地
	$worlds= sql_fetch_rows("select type,wid from mem_world where wid>'$wstart' and wid < '$wend' and province<=13 and type>1 and level>0");
	$max=count($worlds);
	//预防所有野地都是0
	if($max==0){
		unlockUser($uid);
		throw new Exception($GLOBALS['treasure']['has_not']);
	}

	//随机选一个
	$index=mt_rand(0,$max-1);
	$wid=$worlds[$index]['wid'];
	$targetcid=wid2cid($wid);
	reduceGoods($uid,118,1);
	//加入这个用户宝藏图，一天失效
	sql_query("insert into mem_treasure_map (uid,cid,endtime) values('$uid','$targetcid',unix_timestamp()+86400 ) ");
	//隨機
	$y = floor($targetcid / 1000);
	$x = ($targetcid % 1000);

	$ftype=$worlds[$index]['type'];
	if($ftype==2)
	$fieldname =$GLOBALS['fileName']['2'];
	if($ftype==3)
	$fieldname =$GLOBALS['fileName']['3'];
	if($ftype==4)
	$fieldname =$GLOBALS['fileName']['4'];
	if($ftype==5)
	$fieldname =$GLOBALS['fileName']['5'];
	if($ftype==6)
	$fieldname =$GLOBALS['fileName']['6'];

	if($ftype==7)
	$fieldname =$GLOBALS['fileName']['7'];

	$msg=sprintf($GLOBALS['treasure']['report'],$fieldname,$x,$y,MakeEndTime(sql_fetch_one_cell("select unix_timestamp()+86400")));
	
	$actmsg = checkAndDoTreasureIdentifyAct($uid, $cid);
	if($actmsg){
		$actmsg = sprintf($GLOBALS['treasure']['act_msg'],$actmsg);
		$msg.="<br/><br/>".$actmsg;
		$actmsg ="\n".$actmsg;
	}	
	
	sendReport($uid,"trick",31,$cid,$cid,$msg);
	unlockUser($uid);	
	
	throw new Exception($GLOBALS['treasure']['succ'].$actmsg);
}

function addRewardTask($uid,$cid,$param){
	$targetcidx = intval(array_shift($param));
	$targetcidy = intval(array_shift($param));
	$tasktype = intval(array_shift($param));
	$goal = intval(array_shift($param));
	$day = intval(array_shift($param));
	$money = intval(array_shift($param));

	$level = sql_fetch_one_cell("select level from sys_building where cid='$cid' and bid=10");
	if($level<5){
		throw new Exception($GLOBALS['reward_task']['no_level']);
	}
	
	//爵位必须达到大夫	
	$nobility=sql_fetch_one_cell("select nobility from sys_user where uid='$uid'");
	$nobility = getBufferNobility($uid,$nobility);
	if ($nobility<5){
	    throw new Exception($GLOBALS['reward_task']['nobility_low']);
	}
	
	//判断是否有委托文书
	if (!checkGoods($uid,XUANSHANGLING_ID)){
		throw new Exception("not_enough_goods141");
	}
	//悬赏奖励不能是0
	if($money<10){
		throw new Exception($GLOBALS['reward_task']['money_zero']);
	}
	//检查有没有发布超过十个

	$count=sql_fetch_one_cell("select count(*) from sys_pub_reward_task where uid='$uid' and state=0 ");
	if($count>=10){
		throw new Exception($GLOBALS['reward_task']['too_much']);
	}

	//悬赏天数检查
	if($day<=0||$day>10){
		throw new Exception($GLOBALS['reward_task']['day_error']);
	}

	if($tasktype<0||$tasktype>2){
		//不存在的任务类型
		throw new Exception($GLOBALS['reward_task']['task_type_error']);
	}else if($tasktype==2){
		//占领土地
		if($goal!=0&&$goal!=1){
			throw new Exception($GLOBALS['reward_task']['goal_error']);
		}
	}else if($tasktype==0||$tasktype==1){
		//掠夺或者消灭兵力
		if($goal<0||$goal>100000000){
			throw new Exception($GLOBALS['reward_task']['goal_error']);
		}
	}
	//钱够不够
	if(!checkMoney($uid,$money)){
		throw new Exception($GLOBALS['reward_task']['not_enough_money']);
	}

	$targetcid=$targetcidx+$targetcidy*1000;
	$targetname=sql_fetch_one_cell("select name from sys_city where cid='$targetcid'");
	if(empty($targetname)){
		$wid=cid2wid($targetcid);
		$type=sql_fetch_one_cell("select type from mem_world where wid='$wid'");
		$targetname=getFieldName($type);
	}
	$endtime=$day*86400;
	$now = sql_fetch_one_cell("select unix_timestamp();");
	$endtime+=$now;
	$endtime=$endtime + 3600-$endtime%3600;
	$todo= genRewardTaskTodo($targetcid,$targetname,$endtime,$tasktype,$goal);
	reduceGoods($uid,XUANSHANGLING_ID,1);
	addMoney($uid,(0-$money),54);
	$sqlcode="insert into sys_pub_reward_task (uid,targetcid,targetname,type,goal,endtime,money,number,state,todo) values('$uid','$targetcid','$targetname','$tasktype','$goal','$endtime','$money',0,0,'$todo') ";
	sql_query($sqlcode);

	$ret=array();
	$ret[]=$sqlcode;
	return $ret;

}

function genRewardTaskTodo($targetcid,$targetname,$endtime,$type,$goal){
	$result="";
	$pos=getPosition($targetcid);
	$time=MakeEndTime($endtime);
	if($type==0)
	return sprintf($GLOBALS['recordTask']['task_content_0'],"",$time,$targetname,$pos,$goal);
	else if($type==1)
	return sprintf($GLOBALS['recordTask']['task_content_1'],"",$time,$targetname,$pos,$goal);
	else if($type==2){
		if($goal==0)
		return sprintf($GLOBALS['recordTask']['task_content_2'],"",$time,$targetname,$pos);
		else if($goal==1)
		return sprintf($GLOBALS['recordTask']['task_content_3'],"",$time,$targetname,$pos);
	}
}

function resetSystemRewardTask($uid,$cid,$param)
{
	//useTaskMagic($uid,$cid);
/*	$start = array_shift($param);
	$end = array_shift($param);
	$anotherversion = array_shift($param);
	if($anotherversion) {
		$start += 80000;
		$end += 80000;
	} else {
		$start += 90000;
		$end += 90000;
	}
	if($end > $start + 20) {
		$end = $start + 20;
	}
	sql_query("delete from sys_user_sys_task where uid='$uid'");
	for($i = $start; $i <=$end; $i++) 
	{
		sql_query("insert into sys_user_sys_task (uid,tid) values($uid,".$i.") ");
	}
    sql_query("update mem_user_schedule set last_reset_sys_task=unix_timestamp() where uid='$uid'");      
	
	
	return getSystemRewardTaskList($uid,$cid,$param);*/
}

function fetchSystemRewardTask($uid,$cid,$param)
{
	$id=intval(array_shift($param));
	$tid=intval(array_shift($param));
	
	$task = sql_fetch_one_cell("select tid from sys_city_sys_task where cid=$cid and tid=$tid and state=1");
	if(!empty($task)){
		throw new Exception($GLOBALS['sysRecordTask']['task_already_exist']);
	}
	sql_query("update sys_city_sys_task set state=1 where id=$id");
	sql_query("insert into  sys_user_task values($uid,$tid,0) on duplicate key update state=0");
	
	
	throw new Exception($GLOBALS['sysRecordTask']['task_record_succ']);
	
}

function generateSysTask($uid, $times)
{ 
     $user = sql_fetch_one("select * from sys_user where uid=$uid");
     $nobility = $user['nobility'];
     //清理掉该玩家原来所有的随机任务
     sql_query("delete from sys_user_goal where uid=$uid and gid in (select a.id from cfg_task_goal a,sys_user_task b where a.tid=b.tid and b.tid between 86000 and 86300 and b.uid=$uid)");
     sql_query("delete from sys_user_task where tid between 86000 and 86300 and uid=$uid");
     $rows = array();
     /*$state = sql_fetch_one_cell("select value from mem_state where state=7");
     if ($state < 1) {
     	 //$set1 = sql_fetch_one_cell("select group_concat(t.tid) from sys_user_task t left join cfg_systask_rate r on r.tid=t.tid where t.uid=$uid and r.nobility<=$nobility and tid between 80000 and 100000");
	     //if(empty($set1)) $set1 = "0";
	     $set2 = sql_fetch_one_cell("select group_concat(tid) from sys_user_task where uid=$uid and tid>=90000 and tid<=99999 and state=0");
	     if(empty($set2)) $set2 = "0";
	     //$rows = sql_fetch_rows("select tid,rate from cfg_systask_rate where (tid>=90000 and tid<92000) and tid not in ($set1) and tid not in ($set2) and nobility <= $nobility");
	     $set3 = sql_fetch_one_cell("select group_concat(tid+10000) from sys_user_task where uid=$uid and tid>=80000 and tid<=81999 and state=0");
	     if(empty($set3)) $set3 = "0";
	     
	     //$rows = sql_fetch_rows("select tid,rate from cfg_systask_rate where (tid>=80000 and tid<82000 or tid>90000 and tid<=92000) and tid not in ($set1) and tid not in ($set2) and tid not in ($set3) and nobility <= $nobility");
	     $rows = sql_fetch_rows("select tid,rate from cfg_systask_rate where (tid>=80000 and tid<82000 or tid>90000 and tid<=92000) and tid not in ($set2) and tid not in ($set3) and nobility <= $nobility");
     }
     if ($state == 1) {//开启十常侍随机任务
	     //$set1 = sql_fetch_one_cell("select group_concat(t.tid) from sys_user_sys_task t left join cfg_systask_rate r on r.tid=t.tid where t.uid=$uid and r.nobility<=$nobility");
	     //if(empty($set1)) $set1 = "0";
	     $set2 = sql_fetch_one_cell("select group_concat(tid) from sys_user_task where uid=$uid and tid>=82000 and tid<84000 and state=0");
	     if(empty($set2)) $set2 = "0";
	     $set3 = sql_fetch_one_cell("select group_concat(tid+10000) from sys_user_task where uid=$uid and tid>=82000 and tid<84000 and state=0");
	     if(empty($set3)) $set3 = "0";
	     
	     //$rows = sql_fetch_rows("select tid,rate from cfg_systask_rate where (tid>=82000 and tid<84000 or tid>=92000 and tid<94000) and tid not in ($set1) and tid not in ($set2) and tid not in ($set3) and nobility <= $nobility");
	     $rows = sql_fetch_rows("select tid,rate from cfg_systask_rate where (tid>=82000 and tid<84000 or tid>=92000 and tid<94000) and tid not in ($set2) and tid not in ($set3) and nobility <= $nobility");
     }
	 if ($state >= 2) {//开启讨伐董卓随机任务
	     //$set1 = sql_fetch_one_cell("select group_concat(t.tid) from sys_user_sys_task t left join cfg_systask_rate r on r.tid=t.tid where t.uid=$uid and r.nobility<=$nobility");
	     //if(empty($set1)) $set1 = "0";
	     $set2 = sql_fetch_one_cell("select group_concat(tid) from sys_user_task where uid=$uid and tid>=84000 and tid<86000 and state=0");
	     if(empty($set2)) $set2 = "0";
	     $set3 = sql_fetch_one_cell("select group_concat(tid+10000) from sys_user_task where uid=$uid and tid>=84000 and tid<86000 and state=0");
	     if(empty($set3)) $set3 = "0";
	     
	     //$rows = sql_fetch_rows("select tid,rate from cfg_systask_rate where (tid>=84000 and tid<86000 or tid>=94000 and tid<96000) and tid not in ($set1) and tid not in ($set2) and tid not in ($set3) and nobility <= $nobility");
	     $rows = sql_fetch_rows("select tid,rate from cfg_systask_rate where (tid>=84000 and tid<86000 or tid>=94000 and tid<96000) and tid not in ($set2) and tid not in ($set3) and nobility <= $nobility");
     }
     */
     $rows = sql_fetch_rows("select tid,rate from cfg_systask_rate where (tid>=86000 and tid<86300) and nobility <= $nobility");
     if(empty($rows)){
     	return false;
     }

     $sumRate = 0;
	 foreach($rows as $row){	 	
     	$sumRate += $row['rate'];
     }
     
     for($i = 0; $i < $times; $i++) 
     {
	     $rate = mt_rand(1,$sumRate);
	     
	     $curRate=0;
	     $sysTask=null;
	     $length = count($rows);
	     for($j = 0; $j < $length - $i; $j++){
	     	$row = $rows[$j];
	     	$curRate += $row['rate'];
	     	if ($curRate>=$rate){
	     		$sysTask=$row;
		     	//if (mt_rand(1, 100) <= 5) {// 若干概率出现有宝石箱奖励的随机任务
				//	$sysTask['tid'] = $sysTask['tid'] - 10000;
				//}
				receiveSysTask($uid, $sysTask['tid']);
	     		$rows[$j] = $rows[$length - $i - 1];
	     		$sumRate -= $sysTask['rate'];
	     		break;
	     	}
	     }
     }     
}

function doGetResetSystemTask($uid)
{
	$level = 10;
	
	$last_reset_sys_task = sql_fetch_one_cell("select last_reset_sys_task from mem_user_schedule where uid='$uid'");

    if (empty($last_reset_sys_task))
    {
    	sql_query("insert into mem_user_schedule (uid,last_reset_sys_task) values ('$uid',unix_timestamp()) on duplicate key update last_reset_sys_task=unix_timestamp()");
    	$last_reset_sys_task = 0;
    }
    $now = sql_fetch_one_cell("select unix_timestamp()");
    //如果过了凌晨5点，刷新任务
    $last_day = floor(($last_reset_sys_task+3*3600) / 86400); 
    $curr_day = floor(($now+3*3600) / 86400);
    if($curr_day > $last_day) {
    	//sql_query("delete from sys_user_sys_task where uid='$uid'");
    	sql_query("delete from sys_user_task where uid='$uid' and tid between 80000 and 100000");
    	if($last_reset_sys_task != 1000) //1000是用户刷新任务标志位，既然不是，就是超过了一天，重置用户刷新次数。
    		sql_query("update mem_user_systask_num set count=0 where uid=$uid");//24小时候，把数量置0
    	$blockdelta = $level;
    } else {//否则，每2个小时刷新一下
	    $blocksize = 3600 * 2;
	    //再加3600是为了保证在 5：00 7：00 9：00刷新，而不是 4：00 6：00 8：00刷
	    $last_block = floor(($last_reset_sys_task+8*3600 + 3600) / $blocksize);
	    $curr_block = floor(($now+8*3600 + 3600) / $blocksize);
	    $blockdelta = $curr_block - $last_block;
    }
    if ($blockdelta > 0)
    {    	
      	$newTasksNum = $blockdelta;
      	if($newTasksNum > $level)
      		$newTasksNum = $level;
    	
      	//$taskCount = sql_fetch_one_cell("select count(*) from sys_user_sys_task where uid='$uid'");
      	$taskCount = sql_fetch_one_cell("select count(*) from sys_user_task where uid='$uid' and tid between 80000 and 100000 and state=0");
      	if($newTasksNum > $level - $taskCount) {
      		$newTasksNum = $level - $taskCount;
      	}
      	if($newTasksNum < 0) {
      		$newTasksNum = 0;
      	}
//    	$numberToRemove = $blockdelta - ($level - $taskCount);
//    	if($numberToRemove < 0) $numberToRemove = 0;
//    	
//    	if($numberToRemove > 0) {
//	        $oldSystasks = sql_fetch_rows("select * from sys_user_sys_task where `uid`='$uid' order by id limit $numberToRemove");  
//	        foreach($oldSystasks as $task)
//	        {
//	            sql_query("delete from sys_user_sys_task where id=".$task['id']);
//	        }
//    	}
      	$now = sql_fetch_one_cell("select unix_timestamp()");
		if($newTasksNum > 0) {
	        generateSysTask($uid, $newTasksNum);
		}
       	sql_query("update mem_user_schedule set last_reset_sys_task=unix_timestamp() where uid='$uid'");      
    }	    
}


function getSystemRewardTaskList($uid,$cid,$param)
{
	//doGetResetSystemTask($uid);

	$itemCount = sql_fetch_one_cell("select count(*) from sys_user_sys_task where uid=$uid");
		
	$ret=Array();
	
	if($itemCount>0)
	{
		$ret[] = sql_fetch_rows("select t.* from sys_user_sys_task c, cfg_task t where c.tid=t.id and c.uid=$uid");		
	}
	else
	{
		$ret[]=array();
	}	  

    return $ret;
}

function getSysTaskDetail($uid,$param)
{                                 
    $tid = intval(array_shift($param));
    
    if($tid<80000 || $tid>99999) {
    	throw new Exception($GLOBALS['sysTask']['invalid_id']);
    }
    $ret = array();

    $ret[] = sql_fetch_one("select * from cfg_task where id='$tid'");
    
    $goals = sql_fetch_rows("select g.*,u.uid, 0 as currentcount from cfg_task_goal g left join sys_user_goal u on u.gid=g.id and u.uid='$uid' where g.tid='$tid' order by g.id");
    $ret[] = $goals;
    
    $rewards = sql_fetch_rows("select * from cfg_task_reward where tid='$tid' order by type asc");
    $ret[] = $rewards;
    return $ret;
}      

function receiveSysTask($uid, $tid)
{
	$tid = intval($tid);
	//if($tid <80000 || $tid > 99999) {
	//	throw new Exception($GLOBALS['sysTask']['not_systask']);
	//}
	//$usertask = sql_fetch_one("select * from sys_user_sys_task where uid=$uid and tid=$tid");
	//if(empty($usertask)) {
	//	throw new Exception($GLOBALS['sysTask']['not_task_of_user']);
	//}
	//清一下过期的随机任务
	//sql_query("update sys_user_task a,sys_user_taskstate b set a.state=1 where a.uid=$uid and a.tid =b.tid and b.uid=$uid and b.endtime<unix_timestamp()");
	
	/*$count = sql_fetch_one_cell("select count(*) from sys_user_task where tid>79999 and tid <100000 and state=0 and uid=$uid");
	if($count >= 10) {
		throw new Exception($GLOBALS['sysTask']['too_much_tasks']);
	}*/
	sql_query("delete from sys_user_sys_task where uid=$uid and tid=$tid");
	/*if (mt_rand(1, 100) <= 5) {// 若干概率出现有宝石箱奖励的随机任务
		$tid = $tid - 10000;
	}*/
	//$starttime = sql_fetch_one_cell("select unix_timestamp()");
	//第二天凌晨5点结束
	//$endtime = floor(($starttime+3*3600) / 86400) * 86400 + 86400;
	//if($endtime < $starttime) $endtime += 86400;
	
	sql_query("insert into sys_user_task (uid,tid,state) values ('$uid','$tid',0) on duplicate key update state=0");
	//sql_query("insert into sys_user_taskstate (`uid`, `tid`, `starttime`, `endtime`) values('$uid', '$tid', '$starttime', '$endtime') on duplicate key update starttime='$starttime', endtime='$endtime'");
	$goals = sql_fetch_rows("select * from cfg_task_goal where tid=$tid");
	foreach($goals as $goal) {
		sql_query("delete from sys_user_goal where gid='$goal[id]' and uid=$uid");
		if($goal['sort'] != 50) {
			continue;
		} else {
			sql_query("insert into sys_user_goal(`gid`, `uid`, `currentcount`) values('$goal[id]', '$uid', 0)");
		}
	}
	//记一下
	//sql_query("insert into log_user_receive_systask(`uid`,`tid`,`timestamp`) values($uid, $tid, unix_timestamp())");
	//$ret = array();
	//$ret[] = 2;
	//$ret[] = $GLOBALS['sysTask']['receive_task_success'];
	completeTaskWithTaskid($uid,332);
	//logUserAction($uid,12);
	//return $ret;
}
/*
function getRewardTaskList($uid,$cid,$param){
	$page = intval(array_shift($param));
	$filter = intval(array_shift($param));
	$orderby = intval(array_shift($param));
	//$unionOnly = intval(array_shift($param));
	//$sellName = array_shift($param);
	$filterResource = "";
	// $filterResource2 = "";
	if ($filter > 0){
		$filterResource = "and type = ".($filter-1);
		// $filterResource2 = "and t.restype = ".($filter-1);
	}

	//$unionid=sql_fetch_one_cell("select union_id from sys_user where uid='$uid'");

	$ret = array();
	//$ret[] = getCityTradeUsing($cid);
	//$ret[] = getCityMarketLevel($cid);
	$itemCount = sql_fetch_one_cell("select count(*) from sys_pub_reward_task where state=0  $filterResource ");
	$pageCount = ceil($itemCount /10);
	if($page>=$pageCount)
	{
		$page=$pageCount-1;
	}
	if($page<0)
	{
		$page=0;
		$pageCount=0;
	}
	if($itemCount>0)
	{
		$pagestart = $page * 10;
		$ret[]=$pageCount;
		$ret[]=$page;
		if($orderby==1)
		$ret[] = sql_fetch_rows("select * from sys_pub_reward_task where state=0  $filterResource order by endtime asc limit $pagestart,10");
		else if($orderby==2)
		$ret[] = sql_fetch_rows("select * from sys_pub_reward_task where state=0  $filterResource order by money desc limit $pagestart,10");
		else
		$ret[] = sql_fetch_rows("select * from sys_pub_reward_task where state=0  $filterResource order by ((`targetcid`%1000-'$cid'%1000)*(`targetcid`%1000-'$cid'%1000)+(floor(`targetcid`/1000)-floor('$cid'/1000))*(floor(`targetcid`/1000)-floor('$cid'/1000))) asc limit $pagestart,10");
	}
	else
	{
		$ret[]=0;
		$ret[]=0;
		$ret[]=array();
	}
	return $ret;
}
*/
/*
function getMyRewardTaskList($uid,$cid,$param){
	$ret=array();
	$ret[] = sql_fetch_rows("select * from sys_pub_reward_task where state=0 and uid='$uid' order by endtime asc ");
	return $ret;
}
*/
/*
function fetchRewardTask($uid,$cid,$param){
	$tid=intval(array_shift($param));
	$nobility = sql_fetch_one_cell("select nobility from sys_user where uid=$uid");
	if($nobility < 5) {
		throw new Exception($GLOBALS['fetchRewardTask']['not_enough_nobility']);
	}
	$task=sql_fetch_one("select uid,type,targetcid,goal from sys_pub_reward_task where id='$tid'");
	if(empty($task)){
		throw new Exception($GLOBALS['fetchRewardTask']['no_task']);
	}
	
	if($task['uid']==$uid){
		throw new Exception($GLOBALS['fetchRewardTask']['my_task']);
	}

	if(sql_check("select uid from sys_user_reward_task where uid='$uid' and tid='$tid'")){
		throw new Exception($GLOBALS['fetchRewardTask']['task_already_recorded']);
	}
	$taskcount=sql_fetch_one_cell("select count(*) from sys_user_reward_task where uid='$uid' ");
	if($taskcount>=25){
		throw new Exception($GLOBALS['fetchRewardTask']['task_list_full']);
	}
	sql_query("insert into sys_user_reward_task (uid,tid,state,type,targetcid,goal) values ($uid,$tid,0,$task[type],$task[targetcid],$task[goal]) on duplicate key update state=0");
	//sql_query("insert into sys_user_task (uid,tid,state) values ($uid,$taskid2,0) on duplicate key update state=0");
	sql_query("update sys_pub_reward_task set number=number+1 where id='$tid'");
	 
	throw new Exception($GLOBALS['recordTask']['task_record_succ']);
}
*/

/*function regenerateCommission($cid)
 {
 sql_query("delete from sys_recruit_hero where `cid`='$cid'");
 for ($i = 0; $i < $level; $i++)
 {
 generateRecruitHero($cid);
 }
 }
 //委托任务
 function getCommissions($uid,$cid)
 {
 $last_reset_commission_and_now = sql_fetch_one("select last_reset_commission,unix_timestamp() as now from mem_city_schedule where cid='$cid'");
 $last_reset_commission = 0;
 $now=0;
 if (empty($last_reset_commission_and_now)){
 sql_query("insert into mem_city_schedule (cid,last_reset_commission) values ('$cid',unix_timestamp()) on duplicate key update last_reset_commission=unix_timestamp() ");
 }else{
 $last_reset_commission=$last_reset_commission_and_now['last_reset_commission'];
 $now=$last_reset_commission_and_now['now'];
 if($now-$last_reset_commission>=1800){
 //大于半个小时
 regenerateCommission($cid);
 }
 }
 $commissions=sql_fetch_rows("select * from sys_city_commission_task where cid='$cid'");
 if(empty($commissions)){
 regenerateCommission($cid);
 }
 $ret=array();
 $ret[]=$commissions;
 return $ret[];
 } */
 

function getBlackMarketGoodsList($uid, $param)
{
	resetBlackMarketGoods();
	$ret = array();
	
	$copper = sql_fetch_one_cell("select `count` from sys_goods where uid=$uid and gid=152");
	if(empty($copper)) $copper = 0;
	$ret[] = $copper;
	
	$goods = sql_fetch_rows("select g.*, cc.price, cc.type from sys_goods_copper c left join cfg_goods_copper cc on cc.gid=c.gid and cc.type=c.type left join cfg_goods g on g.gid=c.gid where c.type = 0");
	$things = sql_fetch_rows("select t.*, cc.price, cc.type from sys_goods_copper c left join cfg_goods_copper cc on cc.gid=c.gid and cc.type=c.type left join cfg_things t on t.tid=c.gid where c.type = 1");
	$ret[] = array_merge($goods, $things);
	return $ret;
}

function buyGoodsUsingCopper($uid,$param)
{
	$id = intval(array_shift($param));
	$type = intval(array_shift($param));
	$cnt = intval(array_shift($param));
	$commend = array_shift($param);   //0是积分，1是五珠钱
	
	if ($cnt < 1) throw new Exception($GLOBALS['blackMarket']['invalid_amount']);
	
	$goods = sql_fetch_one("select * from cfg_goods_copper where gid='$id' and type=$type");
	if (empty($goods)) throw new Exception($GLOBALS['blackMarket']['no_this_goods']);
	$moneyNeed = $cnt * $goods['price'];
	if(!lockUser($uid)) throw new Exception($GLOBALS['pacifyPeople']['server_busy']);
	
	$gid=$goods['gid'];
	$goodLeft=0;
	if($commend==0)  
	{
		if ($type==0){
			$sort = 2;
		}else{
			$sort = 5;
		}
		//检查上限
		$row = sql_fetch_one("select * from cfg_box_details where srctype=0 and srcid=152 and sort=$sort and type=$gid");
		if($row){
			if ($row['dayopencount']>0){//每日限制
				$currentCount = sql_fetch_one_cell("select sum(count) from log_act where uid=$uid and actid=152 and sort=$sort and type=$gid and log_type=0 and time>=unix_timestamp(curdate())");
				if($currentCount+$cnt>$row['dayopencount']){
					throw new Exception($GLOBALS['blackMarket']['exceed_limit']);
				}
			}
			if ($row['totalopencount']>0){//总活动限制
				$currentCount = sql_fetch_one_cell("select sum(count) from log_act where uid=$uid and actid=152 and sort=$sort and type=$gid and log_type=0");
				if($currentCount+$cnt>$row['totalopencount']){
					throw new Exception($GLOBALS['blackMarket']['exceed_limit']);
				}
			}
			if ($row['owncount']>0){//全服活动限制
				$currentCount = sql_fetch_one_cell("select sum(count) from log_act where actid=152 and sort=$sort and type=$gid and log_type=0");
				if($currentCount+$cnt>$row['owncount']){
					throw new Exception($GLOBALS['blackMarket']['exceed_limit']);
				}
			}
		}
		if ($row && ($row['dayopencount']>0 ||$row['totalopencount']>0 ||$row['owncount']>0)) {//限制类商品
			sql_query("insert into log_act (uid, actid, sort, type, count, log_type, time) values ($uid, 152, $sort, $gid, $cnt, 0, unix_timestamp())");
		}
		$jifen = sql_fetch_one_cell("select `count` from sys_goods where uid=$uid and gid=888888");
		if(empty($jifen)) $jifen = 0;
		
		if ($jifen < $moneyNeed)	throw new Exception($GLOBALS['blackMarket']['no_enough_copper']);
	
		//一手交货 
		if($type == 0)
			addGoods($uid,$goods['gid'], $cnt, 22);
		else 
			addThings($uid, $goods['gid'], $cnt, 22);
		//一手交钱
		addGoods($uid, 888888, 0 - $moneyNeed, 22);
		sql_query("update sys_user set last_pay='2' where uid='$uid'");
		$goodLeft=$copper - $moneyNeed;		
	}else if($commend==1)   //五珠钱购买
	{
		$wuZhu = sql_fetch_one_cell("select `count` from sys_goods where uid='$uid' and gid='10960'");
		if(empty($wuZhu)) $wuZhu=0;
		if ($wuZhu < $moneyNeed)	throw new Exception($GLOBALS['shop']['wuZhu_not_enough']);
		//一手交货 
		if($type == 0)
			addGoods($uid,$goods['gid'], $cnt, 22);
		else 
			addThings($uid, $goods['gid'], $cnt, 22);
		//一手交钱
		addGoods($uid, 10960, 0 - $moneyNeed, 22);
		sql_query("update sys_user set last_pay='3' where uid='$uid'");
		$goodLeft=$wuZhu - $moneyNeed;	
	} 
	unlockUser($uid);
		
	$ret = array();
	$ret[]=$commend;
	$ret[]=$goodLeft;
	return $ret;
}

function resetBlackMarketGoods() 
{
	$last_reset_black_market = sql_fetch_one_cell("select value from mem_state where state = 1999");
	if(empty($last_reset_black_market)) $last_reset_black_market = 0;
	$now = sql_fetch_one_cell("select unix_timestamp()");
    //如果过了凌晨5点，刷新商品
    $last_day = floor(($last_reset_black_market+3*3600) / 86400); 
    $curr_day = floor(($now+3*3600) / 86400);
    
    if($curr_day <= $last_day) return;
    sql_query("insert into mem_state(`value`, `state`,`description`) values('$now', '1999','黑市商人') on duplicate key update `value`='$now'");
	$goods = getCandidateGoods();
	sql_query("delete from sys_goods_copper");
	foreach($goods as $good)
	{
		$id = $good['gid'];
		$type = $good['type'];
		sql_query("insert into sys_goods_copper(`gid`, `type`) values('$id', '$type')");
	}
}

function getCandidateGoods()
{
	$minGoodsCount = 3;
	$maxGoodsCount = 16;
	
	$commendGoods = getCommendGoods($maxGoodsCount);//推荐商品优先
	$commendNum = count($commendGoods);
	if($commendNum>0){
		$minGoodsCount -= $commendNum;
		$maxGoodsCount -= $commendNum;
	}
	if($maxGoodsCount<=0){
		return $commendGoods;
	}
	
	$unCommendGoods = getUnCommendGoods($minGoodsCount,$maxGoodsCount);
	if($commendNum>0){
		if(count($unCommendGoods)>0){
			return array_merge($commendGoods,$unCommendGoods);
		}
		return $commendGoods;
	}
	return $unCommendGoods;
}
function getCommendGoods($maxGoodsCount=0){
	$sql = "select * from cfg_goods_copper where commend=1 and onsale=1 order by rate desc ";
	if($maxGoodsCount>0){
		$sql = $sql." limit ".intval($maxGoodsCount);
	}
	$goods = sql_fetch_rows($sql);
	return $goods;
}

function getUnCommendGoods($minGoodsCount,$maxGoodsCount){
	$goods = sql_fetch_rows("select * from cfg_goods_copper where commend<>1 and onsale=1");
	$candidates = array();
	foreach($goods as $good)
	{
		$rate = $good['rate'];
		$randomnumber = mt_rand(1, 100);
		if($randomnumber <= $rate)
		{
			$candidates[] = $good;
		}		
	}
	$num = count($candidates);
	if($num < $minGoodsCount) {
		$count = count($goods);
		if($count <= $minGoodsCount) return $goods;
		$j = $count - 1;
		for($i = 0; $i < $count - $num; $i++)
		{
			$same = false;
			$good = $goods[$i];
			foreach($candidates as $candidate)
			{
				if($good['type'] == $candidate['type'] && $good['gid'] == $candidate['gid'])
				{
					$same = true;
				}
			}
			if($same) 
			{
				$goods[$i] = $goods[$j];
				$goods[$j] = $good;
				$j--;
			}
		}
		for($i = $num; $i < $minGoodsCount; $i++)
		{
			$max = $count - $i - 1;
			$rt = mt_rand(0, $max);
			$good = $goods[$rt];
			$candidates[] = $good;
			$goods[$rt] = $goods[$max];
			$goods[$max] = $good;
		}		
		return $candidates;
	} else if ($num > $maxGoodsCount) {
		for($i = $num; $i > $maxGoodsCount; $i--)
		{
			$rt = mt_rand(0, $i - 1);
			$candidate = $candidates[$rt];
			$candidates[$rt] = $candidates[$i - 1];
			$candidates[$i - 1] = $candidate;			
		}
		$ret = array();
		for($i = 0; $i < $maxGoodsCount; $i++)
		{
			$ret[] = $candidates[$i];
		}
		return $ret;
	} else {
		return $candidates;
	}
}
function refreshSysTask($uid,$param) {
	$money =sql_fetch_one_cell("select money from sys_user where uid=$uid");
//	$money = array_shift($param);
	//进行检测，防外挂。
	
	$count = sql_fetch_one_cell("select count from mem_user_systask_num where uid=$uid");
	//if ($count>4) {
	//	throw new Exception($GLOBALS['sysTask']['too_much']);
	//}
	$tmpmoney=0;
	if ($count>0) {
		$tmpmoney = 20;
	}
	if ($tmpmoney>$money) {
		throw new Exception($GLOBALS['sysTask']['money_error']);
	}
	//重置随机任务
	generateSysTask($uid,10);
	//记录刷新次数
	sql_query("update mem_user_systask_num set count=count+1 where uid=$uid");
	//扣钱
	addMoney($uid,-$tmpmoney,81);//81表示随机任务消费。
	//重新刷新任务
	return getTaskTypeGroupList($uid,array(7,));
}
?>
