<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html" charset="utf-8">
<title>将领排行</title>
</head> 
<body bgcolor="#CCFFCC" > 
<?php
  require_once("./utils.php");
  //sql_query(" update sys_hero_act set gifs=500 where gifs<500 ");	
  /*$day = sql_fetch_one_cell("select DATE_FORMAT(NOW(),'%w')");
	$hour = sql_fetch_one_cell("select DATE_FORMAT(NOW(),'%k')");
	$userNames = sql_fetch_one("select * from sys_user where uid='$uid'");
	$flags=intval($userNames['flagchar']);
	echo $day .'$'.$hour.'<br/>';
	echo $flags;
    sql_query("update cfg_npc_hero set face=npcid where sex=1 ");
	*/
	//$cnt1=sql_fetch_one_cell("select SUM(bravery_base) from cfg_npc_hero where type=0 ");
	//$cnt2=sql_fetch_one_cell("select SUM(affairs_base) from cfg_npc_hero where type=0 ");
	//$cnt3=sql_fetch_one_cell("select SUM(wisdom_base) from cfg_npc_hero where type=0 ");
	//$cnt4=sql_fetch_one_cell("select SUM(b.command_base) from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 ");
 
	//$cnt=sql_fetch_one_cell("select SUM(a.bravery_base+b.affairs_base+b.wisdom_base+b.command_base) from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 ");
 // echo $cnt/920;
  //echo '<br/>'.($cnt1/920);
 // echo '<br/>'.($cnt2/920);
 // echo '<br/>'.($cnt3/920);
 // echo '<br/>'.($cnt4/920);

 // $heroranks=sql_fetch_rows("select * from cfg_npc_hero  where uid=npcid order by npcid asc ");
// sql_query("insert into sys_hero_act_reward (hid,name,goods,type) (select a.npcid,a.name,'1,-100,1000',2 from cfg_npc_hero a left join sys_city_hero b on a.npcid=b.hid where type=0 order by a.bravery_base desc limit 1,100)");
//sql_query("insert into sys_hero_act(uid,hid,name,goods,type,state) (select uid,npcid,name,'1,-100,100',0,0 from cfg_npc_hero order by npcid asc )");
//sql_query(" update sys_hero_act set type=4,goods='3,-100,1000,8892,1,8891,1' where uid=hid or uid=262 ");	
 //$hid=864;cfg_npc_hero_copy
 //$heroranks=sql_fetch_rows("delete  from cfg_npc_hero_copy where bravery_base+affairs_base+wisdom_base>260 order by npcid desc  ");
 //echo $heroranks['npcid'].'<br/>'.$heroranks['name'].'<br/>';  
 //$heroranks=sql_fetch_rows("select a.*,b.command_base from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 order by b.bravery_base desc limit 0,100 ");
 //$heroranks = sql_fetch_rows("select a.*,b.command_base from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 order by b.affairs_base desc limit 0,100 ");
 //$heroranks=sql_fetch_rows("select a.*,b.command_base from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 order by b.wisdom_base desc limit 0,100 ");
 //$heroranks=sql_fetch_rows("select a.*,b.command_base from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 order by b.command_base desc limit 0,53 ");
 //$heroranks=sql_fetch_rows("select a.*,b.command_base from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 order by (b.bravery_base+b.affairs_base+b.wisdom_base+b.command_base) asc ");
//$heroranks=sql_fetch_rows("select a.*,b.command_base from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  a.bravery_base+a.affairs_base+a.wisdom_base+b.command_base>360 and type=0 order by b.command_base desc limit 0,53 ");
// $heroranks=sql_fetch_rows("select * from cfg_npc_hero  where  uid=npcid or uid=262 ");
 //$heroranks=sql_fetch_rows("select a.*,b.command_base from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 order by b.bravery_base desc  ");
 //$heroranks=sql_fetch_rows("select a.*,b.command_base from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 order by b.affairs_base desc  ");
 //$heroranks=sql_fetch_rows("select a.*,b.command_base from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 order by b.wisdom_base desc  ");
 //$heroranks=sql_fetch_rows("select a.*,b.command_base from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 order by b.command_base desc ");

 $heroranks=sql_fetch_rows("select a.*,b.command_base from cfg_npc_hero a LEFT JOIN  sys_city_hero b on a.npcid=b.hid where  type=0 order by (b.bravery_base+b.affairs_base+b.wisdom_base+b.command_base) desc limit 0,50 ");

 //echo $heroranks[0]['npcid'].'<br/>'.$heroranks[0]['name'].'<br/>';  
  $msg='<table border="1" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" width="505">
       <tr>
	        <td align="center" valign="middle" class="TitleBlueWhite">将领id</td>
	        <td align="center" valign="middle" class="TitleBlueWhite">将领名称</td>
	        <td align="center" valign="middle" class="TitleBlueWhite">统率</td>
			<td align="center" valign="middle" class="TitleBlueWhite">勇武</td>
	        <td align="center" valign="middle" class="TitleBlueWhite">内政</td>
	        <td align="center" valign="middle" class="TitleBlueWhite">智谋</td>	
            <td align="center" valign="middle" class="TitleBlueWhite">合计</td>	
            <td align="center" valign="middle" class="TitleBlueWhite">手下</td>					
      </tr>';  
//$i=1;
  foreach ($heroranks as $herorank){
	//$cnt=sql_fetch_one_cell("select count(*) from cfg_npc_hero where uid='$herorank[npcid]' ");
	//$tot=($herorank['command_base']-69)*20;
    $tot= $herorank['command_base']+$herorank['bravery_base']+$herorank['affairs_base']+$herorank['wisdom_base'] ;
    $info.='<tr><td>'.$herorank['npcid'].'</td>'.'<td>'.$herorank['name'].'</td>'.'<td>'.$herorank['command_base'].'</td>'.'<td>'.$herorank['bravery_base'].'</td>'.'<td>'.$herorank['affairs_base'].'</td>'.'<td>'.$herorank['wisdom_base'].'</td>'.'<td>'.$tot.'</td>'.'<td>'.$cnt.'</td></tr>';
    //$info.='<tr><td>'.$herorank['npcid'].'</td>'.'<td>'.$herorank['name'].'</td>'.'<td>'.$herorank['face'].'</td></tr>';
	//$pe=sql_fetch_one_cell("select rank from sys_hero_act set where hid='$herorank[npcid]' ");		
    //if($pe) {}	
	// else //名将基本奖励500元宝，超过四维平均值的(273)，每超一点奖励20元宝，NPC君主基本奖励为名将基本奖励+1000+手下人数*100,四维综合排名前100的将5600-名次*20；
	//$tot=$tot+(101-$i)*10;
	/*
	$cgcnt = sql_fetch_one_cell("select goods from sys_hero_act where hid='$herorank[npcid]' ");
	$cstt='1,8887,';
	if(empty($cgcnt) || $cgcnt=='') $ss='0';
	else {
	  $sttt=explode(",", $cgcnt);
	  $ss=$sttt[2];
	 
	}
	if($i<11)
	{$ss +=3;}
    else if($i>10 && $i<21)
		{$ss +=2;}
	else 
	  {$ss +=1;}
    $cstt =$cstt.$ss;  
	//echo $cstt.'<br/>';
	//sql_query(" update sys_hero_act set goods='$cstt',gifs=gifs+'$tot' where hid='$herorank[npcid]' ");
	sql_query(" update sys_hero_act set goods='$cstt' where hid='$herorank[npcid]' ");
	//$cnt=sql_fetch_one_cell("select count(*) from cfg_npc_hero where uid='$herorank[npcid]' ");
	$i++;
	*/
 }
 $msg.=$info.'</table>';
 echo $msg;
 //$ss=mt_rand(1,35)%8;
// echo $ss;

 ?>
</body>
</html>
<?php  
 /*
 $heroinfos=sql_fetch_rows("select * from sys_city_hero where uid=0 and  npcid>0 ");
 $i=0;
 foreach ($heroinfos as $heroinfo){
	     $hid=$heroinfo['hid'];
         $cid=$heroinfo['cid'];
         $province=sql_fetch_one_cell("select province from sys_city where cid=$cid ");		
         if(!empty($province)) if($province<14)
              echo $hid.'<br/>'; 	
             			  
             //if($hid==261 || $hid==608 ||$hid==398 ||$hid==832 ||$hid==979){
		         //$cid=$herocid[$hid]；
		         //sql_query("update sys_city_hero set cid='$cid',state=1 where hid='$hid' ");
			     //sql_query("update sys_city set  chiefhid='$hid' where cid='$cid' ");
		       // }
			//}
	}
/* function  replaceWidHeros(){//将野地将放在少数民族以外的区域，并将5个少数民族主寨中放入5个对应的君主将
     $herocid=array(608=>455475,261=>485035,398=>235025,832=>15395,979=>35095);
     $heroinfos=sql_fetch_rows("select * from sys_city_hero where uid=0 and  npcid>0 ");
     foreach ($heroinfos as $heroinfo){
	     $hid=$heroinfo['hid'];
         $wid=cid2wid($heroinfo['cid']);
         $province=sql_fetch_one_cell("select province from mem_world where `wid`='$wid' ");
         if($province>13) throwHeroToField($heroinfo);    
         if($hid==261 || $hid==608 ||$hid==398 ||$hid==832 ||$hid==979){
		     $cid=$herocid[$heroinfo['hid']]；
		     sql_query("update sys_city_hero set cid='$cid',state=1 where hid='$hid' ");
			 sql_query("update sys_city set  chiefhid='$hid' where cid='$cid' ");
		    }
		}
 	}
/*
	$userinfo['uid']=897;
$userinfo['cityname']='你好';
$userinfo['citytype']=1;
$userinfo['heroname']='测试';
$userinfo['affairs']=100;
$userinfo['bravery']=100;
$userinfo['wisdom']=100;
$userinfo['command']=100;
$g_level = sql_fetch_one_cell("select level from bloodwar.sys_building where `cid`='218241' and `bid`='6' ");//官府等级
echo '官府:'.$g_level.'<br/>';
$npcnum =sql_fetch_one_cell("select npcvalue from bloodwar.cfg_city_npcvalue where level='$g_level' ");//基础兵力数量
echo '基础兵:'.$npcnum.'<br/>';
$npcValue = $npcnum*(1+2+(220/10));//实际兵力数量

$citysoldiers=CreatNpcForceSoldiers($npcValue,$userinfo['province'],218241);//兵种与数量
if($citysoldiers) echo 'good';
//for($i=1;$i<13;$i++){
 //  $oldsex =sql_fetch_one_cell("select count from sys_city_soldier where cid='218241' and sid='$i'");

//$msg=CreatNpcForceHero($userinfo,$cid,$heroid);
//echo $oldsex;}
function CreatNpcForceSoldiers($npcValue,$province,$cid){//随机生成兵种
     $sodides='12,1,2,3,4,5,6,7,8,9,10,11,12';
	 $sodiertype[14]='9,87,88,89,90,91,92,93,94,95';
	 $sodiertype[15]='9,78,79,80,81,82,83,84,85,86';
	 $sodiertype[16]='9,51,52,53,54,55,56,57,58,59';
	 $sodiertype[17]='9,60,61,62,63,64,65,66,67,68';
	 $sodiertype[18]='9,69,70,71,72,73,74,75,76,77';
	 $soldiers = $province>13?$sodiertype[$province]:$sodides;//在少数民族就出少数民族的兵种
	 echo '实际兵:'.$npcValue.'*111<br/>';
	 if(empty($soldiers)) echo 'no:'.$npcValue.'*111<br/>';
	 else echo 'good';
	 $soldiersarray = explode(",", $soldiers);
	 echo $soldiersarray[0];
	 $soldiervalue=array(0, 23, 31, 70, 90, 135, 140, 298, 285, 875, 1000, 1375, 2900, 31, 90, 135, 140, 285, 26, 89, 127, 128, 263, 31, 90, 135, 140, 285,31, 90, 135, 140, 285,
      23,31, 70, 90, 135, 140, 298, 285, 875, 1000, 1375, 2900,285, 2900, 90, 135, 140, 298,70, 90, 135, 140, 298, 285, 1000, 1375, 2900, 70, 90, 135, 140, 298, 285, 1000, 1375, 2900,
      70, 90, 135, 140, 298, 285, 1000, 1375, 2900, 70, 90, 135, 140, 298, 285, 1000, 1375, 2900, 70, 90, 135, 140, 298, 285, 1000, 1375, 2900); 
	 $totalRnd = 0;
	 $valueMap = array();
	 $npcSoldiers ='';
	 $typecount=0;
	 foreach ($soldiersarray as $sid) {
	     $valueMap[$sid]=$sid;
		 echo $sid.'<br/>';
	     $typecount++;
	    }
	 sql_query("delete from sys_city_soldier where `cid`='$cid' ");
	 foreach ($valueMap as $k=>$v){
		 $npcSoldiers.=$k.",";
		 $npcSoldiers.= (int)ceil($npcValue *$v *0.1 /$soldiervalue[$k]).",";
		 echo (int)ceil($npcValue *$v *0.1 /$soldiervalue[$k]).'<br/>';
		 $count = (int)ceil($npcValue *$v *0.1 /$soldiervalue[$k]);
		 sql_query("insert into sys_city_soldier (`cid`,`sid`,`count`) values ('$cid','$k','$count')");
	    }
	  return 1;
    }
function CreatNpcForceHero($userinfo,$cid,$heroid){//0野地1/将领/2存在君主没城
      $level=mt_rand(65,95);
	  $herosex=mt_rand(0,1);
	  $heroface= $herosex>0?mt_rand(1007,1065):mt_rand(1,10);
	  if($heroid==0){//野地名将
	      //没写
		  return 1;
        }else if($heroid==2){
		   $oldsex =sql_fetch_one_cell("select sex from bloodwar.sys_user where uid='$userinfo[uid]' ");
	       $oldface =sql_fetch_one_cell("select face from bloodwar.sys_user where uid='$userinfo[uid]' ");
		   if(empty($oldsex)||empty($oldface)){
		       sql_query("update bloodwar.sys_user set lastcid='$cid',sex='$herosex',face='$heroface' where uid='$userinfo[uid]' ");
		    }else{
		      $herosex = $oldsex;
		      $heroface = $oldface;
			}
		}
	  $sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`command_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`herotype`)
		  values ('$userinfo[uid]','$userinfo[heroname]','$herosex','$heroface','$cid','1','$level','0','$userinfo[affairs]','$userinfo[bravery]','$userinfo[wisdom]','$userinfo[command]','0','0','0','70','27250')";
	  $hid = sql_insert($sql);
	  $forcemax=100+floor($bravery/3);
	  $energymax=100+floor($wisdom /3);	  
	  sql_query("insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`) values ('$hid',$forcemax,$forcemax,$energymax,$energymax)");
	  return 1;	
    }
function CreatNPCForceCity($userinfo){//生成NPC城池
      $province=$userinfo['province'];
	  $newuid=$userinfo['uid'];
	  $oldcityname=$userinfo['cityname'];
	  $citytype=$userinfo['citytype'];
	  $provinceLandCount = sql_fetch_one_cell("select count(*) from bloodwar.mem_world where type=1 and ownercid=0 and province='$province' and state=0");
	  $targetcid=sql_fetch_one_cell("select cid from bloodwar.sys_city where uid>897 and province='$province' order by rand() limit 1");
	  if(empty($targetcid)){
		 $targetwid=sql_fetch_one_cell("select wid from bloodwar.mem_world where type=0 and province='$province' order by rand() limit 1");
		 if(empty($targetwid)){
				$targetwid=sql_fetch_one_cell("select wid from bloodwar.mem_world where province='$province' order by rand() limit 1");
			}
		 $targetcid=wid2cid($targetwid);
		}
	  $ypos=floor($targetcid/1000);
	  $xpos=floor($targetcid-$ypos*1000);
	  $xrange=15;
	  $yrange=15;
	  $xmin=floor(($xpos-$xrange)/10);
	  $xmax=floor(($xpos+$xrange)/10);
	  $ymin=floor(($ypos-$yrange)/10);
	  $ymax=floor(($ypos+$yrange)/10);
	  $widarray=array();
	  for($j=$ymin;$j<=$ymax;$j++){
		 for($k=$xmin;$k<=$xmax;$k++){
			 $widarray[]=($j*100+$k)*100;
			}
		}
	  $arrsize=count($widarray);
	  if($arrsize==0) return 0;
	  $tryCount=0;
	  do{
		  $minwid=$widarray[mt_rand(0,$arrsize-1)];
		  $maxwid=$minwid+100;
 		  $wid = sql_fetch_one_cell("select wid from bloodwar.mem_world where type=1 and province='$province' and ownercid=0 and state=0 and wid>'$minwid' and wid<'$maxwid' order by rand() limit 1");
		  $tryCount++;
		}while(empty($wid)&&$tryCount<10);
	  if(empty($wid)) return 0;
	  $cid = wid2cid($wid);
	  //清除在该地的武将和军队
	  $heros=sql_fetch_rows("select hid from bloodwar.sys_city_hero where uid=0 and cid='$cid'");
	  foreach ($heros as $hero) {			
		 throwHeroToField($hero);
		}
	  //清除城池野地驻军
	  sql_query("delete from bloodwar.sys_troops where uid=0 and state=4 and cid='$cid'");
	  //清除伤兵，逃兵，俘虏
	  sql_query("delete from bloodwar.mem_city_wounded where cid=$cid");
	  sql_query("delete from bloodwar.mem_city_lamster where cid=$cid");
	  sql_query("delete from bloodwar.mem_city_captive where cid=$cid");
	  //修改所在地的属性
      sql_query("update bloodwar.mem_world set ownercid='$cid',`type`='0' where wid='$wid'");
	  //新建主城
	  sql_query("replace into bloodwar.sys_city (`cid`,`uid`,`name`,`type`,`state`,`province`) values ('$cid','$newuid','$oldcityname','$citytype','0','$province') ");
	  //根据城池类型随机生成城池建筑
	  $buildingcid = sql_fetch_one_cell("select cid from sys_city where uid<897 and type='$citytype' order by rand() limit 1");
	  sql_query("replace into bloodwar.sys_building (`cid`,`xy`,`bid`,`level`,`state`)(select $cid,xy,bid,level,0 from bloodwar.sys_building where cid='$buildingcid' and state=0 )");
	  //给资源
      $maincity = sql_fetch_one("select * from bloodwar.mem_city_resource where cid='$buildingcid' limit 1");
	  if(empty($maincity)){
	      sql_query("replace into bloodwar.mem_city_resource (`cid`,`morale`,`morale_stable`,`complaint`,`tax`,`people`,`food`,`wood`,`rock`,`iron`,`gold`,`food_max`,`wood_max`,`rock_max`,`iron_max`,`gold_max`,`food_add`,`wood_add`,`rock_add`,`iron_add`,`lastupdate`) values ('$cid','90','90','0','10','94140','50000000','25000000','25000000','25000000','125000000','25000000','25000000','25000000','25000000','25000000',100,100,100,100,unix_timestamp())");
		}else{
	      sql_query("replace into bloodwar.mem_city_resource (`cid`,`morale`,`morale_stable`,`complaint`,`tax`,`people`,`food`,`wood`,`rock`,`iron`,`gold`,`food_max`,`wood_max`,`rock_max`,`iron_max`,`gold_max`,`food_add`,`wood_add`,`rock_add`,`iron_add`,`lastupdate`) 
		     values ('$cid','90','90','0','10','$maincity[people]','$maincity[food]','$maincity[wood]','$maincity[rock]','$maincity[iron]','$maincity[gold]','$maincity[food_max]','$maincity[wood_max]','$maincity[rock_max]','$maincity[iron_max]','$maincity[gold_max]','$maincity[food_add]','$maincity[wood_add]','$maincity[rock_add]','$maincity[iron_add]',unix_timestamp())");
	    }
	  //上生产      	  
	  sql_query("replace into bloodwar.sys_city_res_add (cid,food_rate,wood_rate,rock_rate,iron_rate,chief_add) values ('$cid',80,80,80,80,0)");
	  //删除不满足条件的建筑;
	  $govermentlevel = sql_fetch_one_cell("select level from bloodwar.sys_building where `cid`='$cid' and `bid`='6'" );
	  delUserBuildings($cid,$govermentlevel);
	  //将这个城池的等级和官府等级一样
	  sql_query("update bloodwar.mem_world set `level`='$govermentlevel',`maxlevel`='$govermentlevel' where wid='$wid'");
	  //上科技
	  sql_query("replace into bloodwar.sys_city_technic(`cid`,`tid`,`level`)(select $cid,tid,level from bloodwar.sys_city_technic where cid='$buildingcid')");
	  //上城防
      sql_query("replace into bloodwar.sys_city_defence(`cid`,`did`,`count`)(select $cid,did,count from bloodwar.sys_city_defence where cid='$buildingcid' )");
	  //城池定时器
	  sql_query("replace into bloodwar.mem_city_schedule (`cid`,`create_time`,`next_good_event`,`next_bad_event`) values ('$cid',unix_timestamp(),unix_timestamp()-(unix_timestamp()+8*3600)%86400 + 86400 + 86400 * rand(),unix_timestamp()-(unix_timestamp()+8*3600)%86400 + 86400 + 86400 * rand())");
	  sql_query("insert into bloodwar.mem_user_schedule (uid,start_new_protect) values ('$newuid',unix_timestamp()) on duplicate key update start_new_protect=unix_timestamp()");
	  return $cid;
    }
function delUserBuildings($cid,$govermentlevel){
	 $VALID_GRID_ARRAY = array(
		 "",
		 "10,60,11,21,31,41,51,61,22,32,42,52",
		 "10,60,70,11,21,31,41,51,61,71,22,32,42,52,62",
		 "10,60,70,11,21,31,41,51,61,71,22,32,42,52,62,33,43,53",
		 "10,60,70,11,21,31,41,51,61,71,81,22,32,42,52,62,72,33,43,53,63",
		 "10,60,70,1,11,21,31,41,51,61,71,81,12,22,32,42,52,62,72,23,33,43,53,63",
		 "10,60,70,1,11,21,31,41,51,61,71,81,12,22,32,42,52,62,72,23,33,43,53,63,34,44,54",
		 "10,60,70,1,11,21,31,41,51,61,71,81,12,22,32,42,52,62,72,82,23,33,43,53,63,73,34,44,54,64",
		 "10,60,70,1,11,21,31,41,51,61,71,81,12,22,32,42,52,62,72,82,23,33,43,53,63,73,34,44,54,64,45,55,65",
		 "10,60,70,1,11,21,31,41,51,61,71,81,2,12,22,32,42,52,62,72,82,13,23,33,43,53,63,73,24,34,44,54,64,45,55,65",
		 "10,60,70,1,11,21,31,41,51,61,71,81,2,12,22,32,42,52,62,72,82,13,23,33,43,53,63,73,24,34,44,54,64,35,45,55,65,46,56"
	    );
	 if($govermentlevel<10){
		 $bid=$VALID_GRID_ARRAY[$govermentlevel];
		 sql_query("delete from bloodwar.sys_building where `cid`='$cid' and `xy`<100 and `xy` not in ($bid)");
	    }
    }
/*
  $conn=mysql_connect('localhost','root','root') or die("error connecting") ; //连接数据库 
  mysql_query("set names 'utf8'"); //数据库输出编码 应该与你的数据库编码保持一致.南昌网站建设公司百恒网络PHP工程师建议用UTF-8 国际标准编码. 
  mysql_select_db('rxsg');
  $table = 'sys_user';
  $query = mysql_query('show TABLES'); //查找所有表
  While($row = mysql_fetch_assoc($query)){
     $data[] = $row['Tables_in_huodong_saifenzj']; //数据库名
    }c
   if (in_array(strtolower($table), $data)){ //判断是否存在
      echo 'Table exist';exit; //表存在
    }else{
	  echo '表不存在！';
	
	}*/
//创建表
//$sql = "CREATE TABLE shaifen_he (`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(20) NOT NULL ,`phone` varchar(12) NOT NULL ,`jobcode` varchar(20) NOT NULL ,`score` float NOT NULL DEFAULT '0',`qq` varchar(15) DEFAULT '',`status` tinyint(1) NOT NULL DEFAULT '1',PRIMARY KEY (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
//$query = mysql_query($sql);

  function CountUserCityHero($olduid,$oldcid,$BandDb){
      $heroinfos = sql_fetch_rows("select * from $BandDb.sys_city_hero where cid='$oldcid' and uid='$olduid' ");
      if(empty($heroinfos)) return 0;
	  $Command=69.437;
      $Affairs=66.0989;
      $Bravery=67.6946;
      $Wisdom=69.1054;
	  $heromoney =0;
	  foreach ($heroinfos as $heroinfo){
	      $herolevel = $heroinfo['level'];
	      if($heroinfo['herotype']==10001 || $heroinfo['npcid']>0 ){
		      $heroc = ($heroinfo['command_base']-$Command)>0?(($heroinfo['command_base']-$Command)*5):0;
		      $heroa = ($heroinfo['affairs_base']-$Affairs)>0?(($heroinfo['affairs_base']-$Affairs)*2):0;
			  $herob = ($heroinfo['bravery_base']-$Bravery)>0?(($heroinfo['bravery_base']-$Bravery)*4):0;
			  $heros = ($heroinfo['wisdom_base']-$Wisdom)>0?(($heroinfo['wisdom_base']-$Wisdom)*3):0;
		 	  $heromoney=$heromoney + $heroc+ $heroa+ $herob+ $herolevel*2;
			}else{
			  if($heroinfo['level']>99) $heromoney=$heromoney+$heroinfo['level'];
			}
	    }
      return ceil($heromoney);
	}
   //sql_query("replace into bloodwar.sys_building (`cid`,`xy`,`bid`,`level`,`state`)(select $cid,xy,bid,level,0 from $BandDb.sys_building where cid='$oldmaincid' and state=0 )");
	   
  //Get_BandUserInfo($BandDb,$BandNum);
  function Get_BandUserInfo($BandDb,$BandNum){//将用户信息写入数据库
     $userinfosql = sql_fetch_rows("select * from $BandDb.sys_user where uid>1000 and officepos>4 ");
	 $recordnum =0;
     if(empty($userinfosql)) return $recordnum;
	 foreach ($userinfosql as $userinfo){//逐行获取结果集中的记录，得到数组row,数组row的下标对应着数据库中的字段值
         $olduid = $userinfo['uid']; //合服的uid
		 $name ='s'.$BandNum.'_'.$userinfo['name']; //合服后君主名
		 $passport = $userinfo['passport'];//合服前帐号
		 $passport1 = $userinfo['passport'];
		 $nowusername = sql_fetch_one("select uid,name,passport from bloodwar.sys_user where passport='$passport' limit 1");//相对是否有相同的账号了
		 if(!empty($nowusername)){//相同帐号处理方法将账号后加上其所在的区的数
		      $oldusername=$nowusername['name'];
			  $oldBandnum = 0;
			  if(preg_match('|(\d+)|',$oldusername,$r)) $oldBandnum=$r[1];
			  if($oldBandnum==$BandNum) continue;
			  $newpassport=$passport.$oldBandnum;
			  $passport .=$BandNum;
			   echo $passport1.'旧'.$passport.'新'.$newpassport.'<br/>';
			}
		
		 $recordnum++;
		}
	  return $recordnum;
    }
  function Get_BandUserGoods($olduid,$newuid,$BandDb){//恢复玩家所有的东西
     //===========日常物品
	 $goodsinfos = sql_fetch_rows("select * from $BandDb.sys_goods where uid='$olduid'");
     if(!empty($goodsinfos)){ 
	     foreach ($goodsinfos as $usergood){
	         $goodid = $usergood['gid'];
		     $goodcount = $usergood['count'];
		     addGoods($newuid,$goodid,$goodcount,3);
		    }
         unset($goodsinfos);
	     unset($usergood);
	    }
	 echo '日常物品';
	 //===========任务物品
	 $thingsinfos = sql_fetch_rows("select * from $BandDb.sys_things where uid='$olduid'");
	 if(!empty($thingsinfos)){ 
         foreach ($thingsinfos as $userthing){
	         $thingid = $userthing['tid'];
		     $thingcount = $userthing['count'];
		     addThings($newuid,$thingid,$thingcount,3);
		    }
         unset($thingsinfos);
	 	 unset($userthing);
		}
	 echo '任务物品';
	 //===========装备
	 $armorinfos = sql_fetch_rows("select * from $BandDb.sys_user_armor where uid='$olduid'");
	 if(!empty($armorinfos)){ 
         foreach ($armorinfos as $userarmor){
	         sql_query("replace into bloodwar.sys_user_armor (`uid`,`armorid`,`hp`,`hp_max`,`hid`,`strong_level`,`strong_value`,`embed_pearls`,`embed_holes`,`deified`,`active_special`,`strong_times`,`combine_level`,`best_quality`) 
		      values ('$newuid','$userarmor[armorid]','$userarmor[hp]','$userarmor[hp_max]','0','$userarmor[strong_level]','$userarmor[strong_value]','$userarmor[embed_pearls]','$userarmor[embed_holes]','$userarmor[deified]','0','$userarmor[strong_times]','$userarmor[combine_level]','$userarmor[best_quality]') ");
		    }
    	 unset($armorinfos);
	     unset($userarmor);
		}
	 echo '装备物品';
	}
  //if(Get_BandUserInfo($BandDb,1)) echo '成功';
  //HandleLuoYangBegin();
   
  //GetUserUnionKing();
	//$day = sql_fetch_one_cell("select DATE_FORMAT(NOW(),'%w')");
//	          $hour = sql_fetch_one_cell("select DATE_FORMAT(NOW(),'%k')");
//echo $day.':'.$hour;
//if($day==2 && $hour==20 )GetUserBattleKing();	
//LouYangTroopTaskStart();
/*function LouYangTroopTaskStart(){
     $louyangtroops = sql_fetch_rows("select * from cfg_luoyang_troop");
     $i=1;
     foreach($louyangtroops as $louyangtroop){
         $npcValue = $louyangtroop['npcValue'];
	     $soldierType = $louyangtroop['sids'];
	     $cid= $louyangtroop['cid'];
	     $hid= $louyangtroop['hid'];
         $soldiers = LouYangCreateSoldier($npcValue,$soldierType,5);
	     sql_query("insert ignore sys_luoyang_troops (`id`,`uid`,`cid`,`hid`,`targetcid`,`state`,`starttime`,`pathtime`,`endtime`,`soldiers`,`battleid`,`startcid`,`unionid`,`lastcc`)
	       values ('$i','659','$cid','$hid','$cid','0','0','0','0','$soldiers','0','0','0','0') on duplicate key update `soldiers`='$soldiers'");
	 	 $i++;
	 	}
	}
function LouYangCreateSoldier($npcValue,$soldiers,$level){
	 $times=pow(2,$level);
	 $npcValue=$npcValue*$times;
	 $soldiersarray = explode(",", $soldiers);
	 $soldiervalue=$GLOBALS['soldier']['soldiervalue']; 
	 $totalRnd = 0;
	 $valueMap = array();
	 $npcSoldiers ="";
	 $typecount=0;
	 foreach ($soldiersarray as $sid) {
	     $valueMap[$sid]=$sid;
	     $typecount++;
	    }
	 foreach ($valueMap as $k=>$v){
		 $npcSoldiers.=$k.",";
		 $npcSoldiers.= (int)ceil($npcValue *$v *0.1 /$soldiervalue[$k]).",";
	    }
	 $npcSoldiers=$typecount.",".$npcSoldiers;
	 return $npcSoldiers;
    }*/
  function Get_usetroop_goodsw($uid,$cid,$tragetcid,$tasktype){
     $goodname ='';
	 $acts=sql_fetch_into_arrays("select actid, rate from cfg_act where type='4980' and starttime<=unix_timestamp() and endtime>=unix_timestamp()");
	 if(empty($acts)) return $goodname;
	 $actnum=count($acts['actid']);
	 $actrates = mt_rand(0,$actnum-1);
	 $actid=$acts['actid'][$actrates];
	 $actrate=$acts['rate'][$actrates];
	 if (isLucky($actrate,35)){//4980为掠夺占领活动
         /* $troopacts=sql_fetch_into_arrays("select * from cfg_box_details where srctype=4000 and srcid='$actid'");
	      if(empty($troopacts)) return $goodname;
		  $troopactnum=count($troopacts['srcid']);
		  $tpactrates = mt_rand(0,$troopactnum-1);
		  $srcid = $troopacts['srcid'][$tpactrates];
		  $srctype = $troopacts['srctype'][$tpactrates];
		  $sort = $troopacts['sort'][$tpactrates];
		  $type = $troopacts['type'][$tpactrates];
		  $count = $troopacts['count'][$tpactrates];
		  $goodnames .= openDefaultGoodsNow($uid,$cid,$srcid,$srctype,$sort,$type,$count,1,0);
		  $targetname = getNamePosition($tragetcid,$tasktype);
		  $usename = sql_fetch_one_cell("select name from sys_user where uid='$uid'");
		  $msg ='恭喜【'.$usename.'】经过激战,在'.$targetname.$goodnames;
		  $goodname='<tr> <td colspan="6" height="25" class="TextArmyCount">'.$goodnames.'</td></tr>';
          sendSysInform(0,1,0,300,1800,1,49151,$msg);	*/
          $troopacts=sql_fetch_into_arrays("select * from cfg_box_details where srctype=4000 and srcid='$actid'");
	      if(empty($troopacts)) return $goodname;
		  $troopactnum=count($troopacts['srcid']);
		  $tpactrates = mt_rand(0,$troopactnum-1);
		  $srcid = $troopacts['srcid'][$tpactrates];
		  $srctype = $troopacts['srctype'][$tpactrates];
		  $sort = $troopacts['sort'][$tpactrates];
		  $type = $troopacts['type'][$tpactrates];
		  $count = $troopacts['count'][$tpactrates];
		  $uname = sql_fetch_one_cell("select name from sys_user where uid='$uid'");
		  $uname = addslashes($uname);
		  $goodnames .= sql_fetch_one_cell("select name from cfg_goods where gid='$type'");
		  //$goodnames .= openDefaultGoodsNow($uid,$cid,$srcid,$srctype,$sort,$type,$count,1,0);
		  $targetname = getNamePosition($tragetcid,$tasktype);
		  $usename = sql_fetch_one_cell("select name from sys_user where uid='$uid'");
		  $msg ='恭喜【'.$usename.'】经过激战,在'.$targetname.'获得【'.$goodnames.'*'.$count.'】';
		  $goodname='<tr> <td colspan="6" height="25" class="TextArmyCount">'.'获得【'.$goodnames.'*'.$count.'】'.'</td></tr>';
          sendSysInform(0,1,0,300,1800,1,49151,$msg);			  
		}
	 return $goodname;
    }
	function getNamePosition($cid,$type,$id=0)
{
	if($type==0) $cityname = sql_fetch_one_cell("select c.name from mem_world m left join cfg_world_type c on c.type=m.type where m.wid=".cid2wid($cid));
	elseif($type==4&&$cid>1000000){
		$cityname = sql_fetch_one_cell("select name from sys_battle_city where `cid`='$cid'");
		return " 战场【".$cityname ."】";
	}else $cityname = sql_fetch_one_cell("select name from sys_city where `cid`='$cid'");
	return $cityname ."（".($cid%1000).",".floor($cid/1000)."）";
}
	                  //($uid,$cid,$srcid,$srctype,$sort,$type,$count,$basecount=1,$limit=0){
  //sql_query("update huangjin_progress set  `curvalue`='0'");
  //HandleTaskAutoDonate(); 
  //$crenum=floor(mt_rand(0,100)/10);
  //$ss = 56;
  //echo $crenum.'<br/>'.floor($ss/10);
 // sql_query("delete from sys_user_task  where tid in(select id from cfg_task where  `group` in(10100,10200,10300,10400,10500,10600,15600) )");//删除黄巾之乱的日常任务
	//			      sql_query("delete from sys_user_task  where tid between 11000 and 15000 ");//删除黄巾之乱史阶段的史诗任务
	//$shitasknums = sql_fetch_rows("select tid from dongzhuo_progress where curvalue>=(maxvalue/2) and tid>15000 and tid <15005");
    // $shitasknum = count($shitasknums);
	// echo $shitasknum;               				
  //  sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task , cfg_task  where tid=243 and state=1 and (id>=10619 and id<=10623)) on duplicate key update state=0");
	
 // sql_query("insert into sys_user_task (uid,tid,state) values (1001,10619,0) on duplicate key update state=0");
  //sql_query("update sys_user_task set  state=0 where tid between 11000 and 15000 ");
 // triggerBeiZhanLuoYang1();
	//function triggerBeiZhanLuoYang1(){
	//sql_query("update mem_state set value=1 where state=8");//把开启洛阳的标识设置为开启
	//sql_query("INSERT INTO `sys_city_hero` (`hid`, `uid`, `name`, `npcid`, `sex`, `face`, `cid`, `state`, `level`, `exp`, `command_base`, `command_add_on`, `affairs_base`, `bravery_base`, `wisdom_base`, `affairs_add`, `bravery_add`, `wisdom_add`, `affairs_add_on`, `bravery_add_on`, `wisdom_add_on`, `force_max_add_on`, `energy_max_add_on`, `speed_add_on`, `attack_add_on`, `defence_add_on`, `loyalty`, `herotype`, `hero_health`) VALUES (894, 659, '吕布', 894, 1, 177, 215265, 1, 86, 20833500, 107, 0, 16, 122, 31, 8, 62, 16, 0, 0, 0, 0, 0, 0, 0, 0, 100, 0, 0) on duplicate key update state=1");//新插入一个将领
	//sql_query("INSERT INTO `cfg_npc_hero` (`npcid`, `uid`, `name`, `zi`, `sex`, `face`, `affairs_base`, `bravery_base`, `wisdom_base`, `province`, `introduce`, `type`) VALUES (894, 659, '吕布', '奉先', 1, 177, 16, 122, 31, 0, '先后为丁原和董卓的义子，生性反复无常，履次背叛他人，最后因部下背叛而死。', 0) on duplicate key update `face`=177");//新插入一个将领
//	sql_query("update sys_city set chiefhid= 894 where cid = 215265");//改城守
//	sql_query("update sys_city_soldier set count=0 where cid=215265 and sid between 1 and 12");//改城守
	/*
	$rows=sql_fetch_rows("select id from cfg_task where id >=112001 and id <=112007 and id<>112005");
	foreach($rows as $row){
		$tid=$row["id"];
		sql_query("insert into sys_user_task (uid,tid,state) (select uid,$tid,0 from sys_user) on duplicate key update state=0");
	}
*/
	//sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task,  cfg_task where tid=243 and state=1 and id >=112001 and id <=112007 and id<>112005) on duplicate key update state=0");			
	//$title=  $GLOBALS['beizhanluoyang']["mailtitle"];
	//$content= $GLOBALS['beizhanluoyang']["mailcontent"];
	//sendAllSysMail($title,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$content);
	//sendSysInform(0,1,0,300,1800,1,49151,$content);
//}	
  //get_cityuses_name(1001);
  //$newttime = sql_fetch_one_cell("select unix_timestamp()");
  //$oldtime = sql_fetch_one_cell("select value from mem_state  where state=10");
  //$newttime = date('G',$newttime);
  //$oldtime = date('G',$oldtime);
 // echo $newttime-$oldtime;
  /*echo httpcopy("http://127.0.0.1/","db.php");

function httpcopy($url, $file="", $timeout=60) {
    $file = empty($file) ? pathinfo($url,PATHINFO_BASENAME) : $file;
    $dir = pathinfo($file,PATHINFO_DIRNAME);
    !is_dir($dir) && @mkdir($dir,0755,true);
    $url = str_replace(" ","%20",$url);

    if(function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $temp = curl_exec($ch);
        if(@file_put_contents($file, $temp) && !curl_error($ch)) {
            return $file;
        } else {
            return false;
        }
    } else {
        $opts = array(
            "http"=>array(
            "method"=>"GET",
            "header"=>"",
            "timeout"=>$timeout)
        );
        $context = stream_context_create($opts);
        if(@copy($url, $file, $context)) {
            return $file;
        } else {
            return false;
        }
    }
}
*/
  /*
  $url ='http://www.kelia.cn/server/config/';
	$filename = 'db.php';
	$save_dir='d:';
    getFiless($url,$save_dir,$filename,1);
	function getFiless($url,$save_dir='',$filename='',$type=0){
      if(trim($url)==''){
         return false;
        }
      if(trim($save_dir)==''){
         $save_dir='./';
        }
      if(0!==strrpos($save_dir,'/')){
         $save_dir.='/';
        }
      //创建保存目录
      if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
         return false;
        }
      //获取远程文件所采用的方法
      if($type){
         $ch=curl_init();
         $timeout=5;
         curl_setopt($ch,CURLOPT_URL,$url);
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
         curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
         $content=curl_exec($ch);
         curl_close($ch);
        }else{
         ob_start();
         readfile($url);
         $content=ob_get_contents();
         ob_end_clean();
        }
      $size=strlen($content);
      //文件大小
      $fp2=@fopen($save_dir.$filename,'a');
      fwrite($fp2,$content);
      fclose($fp2);
      unset($content,$url);
      return array('file_name'=>$filename,'save_path'=>$save_dir.$filename);
    }
*/

//调用

 /*
  $fpA = fopen("http://127.0.0.1/server/config/db.php", "rb");
  $fpB = fopen("d:/aFromA.php", "wb");
  while($data=fread($fpA,1024)){
    fwrite($fpB,$data);
   }
  fclose($fpA);
  fclose($fpB);
*/
  //$contents = file_get_contents("http://www.kelia.cn/server/config/db.php");
 // echo $contents;
 // $cid=245349;
 // $con='你好！';
 /* $uid=1001;
  $cid=112348;
  //$ret=checkFeildCount11($cid);
 // echo $ret[0];
 // echo '<br/>'.$ret[1];
  // for($i=1;$i<45;$i++)
  // sendReport($uid, 3, $i, $cid, $cid, $con.$i);

  $cityname = '长乐宫(348,112)';
  $manname = $GLOBALS['manna']['resource'];//天赐
  $npcid =870; //mt_rand(1,1026);
  $type=6;//mt_rand(3,5);
  $mrate=8;
  if($type==6){
     $mrate=9;
     $gcnt = CreatNewHero($uid,$cid,$npcid,6,1);
     $mcon = ($cityname . $manname[$mrate]) . $gcnt;
     sendSysInform(0,1,0,600,50000,1,14972979,$mcon);
    }else{
     $gcnt = CreatNewHero($uid,$cid,$npcid,$type,0);
    }
   $con = ($cityname . $manname[$mrate]) . $gcnt;
   sendReport($uid, 3, 18, $cid, $cid, $con);
  function UpdateUsersCityBuilding($uid,$cid,$level,$type){
      if($type<>5 && $level>10)$level=10;
	  sql_query("update sys_building set level='$level' where cid='$cid'");
	  UpdateUsersCityResource($uid,$cid);
    }
  function CreatBuildingInTheCity($uid,$cid,$level,$type){
     if($type<>5 && $level>10)$level=10;
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,120,6,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,100,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,110,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,140,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,150,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,101,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,111,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,141,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,151,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,102,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,112,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,122,9,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,132,9,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,142,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,152,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,103,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,113,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,123,9,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,133,9,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,143,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,114,15,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,104,12,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,153,5,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,124,10,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,134,11,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,144,7,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,154,13,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,105,19,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,115,17,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,125,18,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,135,14,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,145,16,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,155,8,$level )");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,199,20,$level )");
     UpdateUsersCityResource($uid,$cid);
	}
function CreatOutsideTheBuilding($uid,$cid,$level){
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,10,2,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,60,2,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,70,2,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,1,2,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,11,2,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,21,3,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,31,3,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,41,3,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,51,3,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,61,3,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,71,4,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,81,4,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,2,4,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,12,4,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,22,4,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,32,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,42,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,52,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,62,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,72,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,82,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,13,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,23,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,33,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,43,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,53,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,63,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,73,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,24,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,34,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,44,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,54,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,64,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,35,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,45,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,55,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,65,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,46,1,$level)");
     sql_query("replace into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,56,1,$level)");
     UpdateUsersCityResource($uid,$cid);
	}
function QCreatOutsideTheBuilding($uid,$cid,$level){
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,10,2,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,60,2,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,70,2,$level)on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,1,2,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,11,2,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,21,3,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,31,3,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,41,3,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,51,3,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,61,3,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,71,4,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,81,4,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,2,4,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,12,4,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,22,4,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,32,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,42,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,52,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,62,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,72,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,82,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,13,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,23,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,33,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,43,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,53,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,63,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,73,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,24,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,34,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,44,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,54,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,64,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,35,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,45,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,55,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,65,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,46,1,$level) on duplicate key update level=$level");
     sql_query("insert into sys_building (`cid`,`xy`,`bid`,`level`) values ($cid,56,1,$level) on duplicate key update level=$level");
     UpdateUsersCityResource($uid,$cid);
	}
function CreatTechnicInTheCity($cid,$level){
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'1','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'2','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'3','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'4','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'5','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'6','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'7','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'8','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'9','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'10','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'11','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'12','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'13','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'14','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'15','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'16','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'17','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'18','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'19','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'20','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'21','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'22','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'23','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'24','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'25','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'26','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'27','$level')");
     sql_query("replace into sys_city_technic (`cid`,`tid`,`level`) values ($cid,'28','$level')");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'1',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'2',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'3',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'4',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'5',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'6',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'7',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'8',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'9',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'10',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'11',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'12',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'13',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'14',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'15',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'16',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'17',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'18',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'19',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'20',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'21',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'22',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'23',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'24',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'25',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'26',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'27',$level,$cid) on duplicate key update level=$level");
     sql_query("insert into sys_technic (uid,tid,level,cid) values ($uid,'28',$level,$cid) on duplicate key update level=$level");
   	}
function CreatSoldierInTheCity($cid,$stype,$scount){
	 sql_query("insert into sys_city_soldier values($cid,$stype,$scount) on duplicate key update count =count+$scount;");
    }*/
?>