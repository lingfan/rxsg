<?php
//---------------
//城池补偿:合区后玩家将只保留主城进入新区,主城中的将领和兵力将随主城保存在新区，既主城的所有数据都在，科技和任务全清0重来
//1、城池补偿:A普通城池=500+10级建筑*60 B:县城1000 C：郡城3000 D:洲城5000 E:都城8000
//2、城池防御补偿：每种城防返回元宝=城防数量*城防ID/100
//3、城池内兵种补偿:每种兵返回元宝=兵种数量*兵种所占人口数量/100
//4、将领补偿
//5、只保留培养期间的孩子信息，孩子成名将后，将作为名将处理，不在保留信息，对于结识的红颜(合服者可以自己解决 代码40行有说明)
//6、君主将保留原来的四维，等级变成1级!
//========将领四维平均值 
//将领返回元宝=将领等级*2+（初统-平统）*5+（初内-平内）*2+（初勇-平勇）*4+（初智-平智）*3 孩子将视为名将;普通将要达到100级才有元宝返回
//========链接服务器数据库设置
require_once("Bandutils.php");
function Get_BandUserInfo($BandDb,$BandNum){//将用户信息写入数据库
     $userinfosql = sql_fetch_rows("select * from $BandDb.sys_user where uid>1000 and officepos>4 ");
	 $counreco= count($userinfosql);
	 $recordnum =0;
     if(empty($userinfosql)) return $recordnum;
	 foreach ($userinfosql as $userinfo){//逐行获取结果集中的记录，得到数组row,数组row的下标对应着数据库中的字段值
         $olduid = $userinfo['uid']; //合服的uid
		 $name ='s'.$BandNum.'_'.$userinfo['name']; //合服后君主名
		 $passport = $userinfo['passport'];//合服前帐号
		 $oldpasswd =  $userinfo['passport'];
		 $oldunionid = $userinfo['union_id'];
		 $testinfo = sql_fetch_one("select * from $BandDb.test_passport where passport='$passport' limit 1");
		 $nowusername = sql_fetch_one("select uid,name,passport from bloodwar.sys_user where passport='$passport' limit 1");//相对是否有相同的账号了
		 if(!empty($nowusername)){//相同帐号处理方法将账号后加上其所在的区的数
		     $oldusername=$nowusername['name'];
			 $oldBandnum = 0;
			 if(preg_match('|(\d+)|',$oldusername,$r)) $oldBandnum=$r[1];
			 if($oldBandnum==$BandNum) continue;//相同区帐号相同不处理
			 $newpassport=$passport.$oldBandnum;
			 sql_query("update bloodwar.sys_user set `passport`='$newpassport' where uid='$nowusername[uid]'");
			 sql_query("replace into bloodwar.test_passport (passport,password,super) values('$newpassport','$testinfo[password]','$testinfo[super]') ");//将注册账号写入新区		
			 sql_query("insert into bloodwar.banduser (uid,name,passport,bandnum,state) values ('$nowusername[uid]','$oldusername','$oldpasswd','$oldBandnum',0)");
			 $passport .=$BandNum;
			}
		 //写入新区，得到新区的uid
		 sql_insert("insert into bloodwar.sys_user (`name`,`passtype`,`passport`,`state`,`group`,`sex`,`face`,`prestige`,`warprestige`,`money`,`rank`,`lastcid`,`union_id`,`union_pos`,`nobility`,`officepos`,`flagchar`,`regtime`,`domainid`,`war_attack_prestige`,`war_defence_prestige`,`gift`,`last_pay`,`honour`,`armor_column`,`achivement_count`,`achivement_point`) 
		     values ('$name','$userinfo[passtype]','$passport','1','0','$userinfo[sex]','$userinfo[face]','$userinfo[prestige]','$userinfo[warprestige]','$userinfo[money]',0,0,0,0,'$userinfo[nobility]','$userinfo[officepos]','$userinfo[flagchar]',unix_timestamp(),0,0,0,'$userinfo[gift]',0,'$userinfo[honour]','$userinfo[armor_column]',0,0) ");
         $newuid = sql_fetch_one_cell("select last_insert_id()");
		 $nextname = sql_fetch_one("select * from bloodwar.banduser where passport='$oldpasswd' limit 1");//检测多区是否有相同的账号了
		 if(!empty($nextname)){//有的话
		      sql_query("insert into bloodwar.banduser (uid,name,passport,bandnum,state) values ('$newuid','$name','$oldpasswd','$BandNum',0)");
	 	      $oldpasswd .= $BandNum;
			  sql_query("update bloodwar.sys_user set `passport`='$oldpasswd' where uid='$newuid'");
			  $passport = $oldpasswd;
			}
		 sql_query("replace into bloodwar.test_passport (passport,password,super) values('$passport','$testinfo[password]','$testinfo[super]') ");//将注册账号写入新区		
		 CallBackUsersGoods($olduid,$newuid,$BandDb);//将玩家的物品写入到新区
		 CallBackUsersMainCity($olduid,$newuid,$BandDb);//恢复玩家主城数据 //保留玩家培养中的孩子 已经和恢复主城将领放一起了，不用在这了
		 //CallBackUsersWife($olduid,$newuid,$BandDb);//保留玩家结识的红颜 将//去掉保留，加上//将删除保留
		 TotallUsersMoney($olduid,$newuid,$oldunionid,$BandDb);//统计玩家补偿元宝数量
		 SetBandUserState($newuid);//设置玩家为新手状态并发信件
		 $recordnum++;
		 sql_query("update banduser set passport='$counreco',bandnum='$recordnum' where uid='1'");
		}
	  return $recordnum;
    }
function CallBackUsersMainCity($olduid,$newuid,$BandDb){//生成、恢复玩家主城数据
      $province=intval(mt_rand(1,13));
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
	  $heros=sql_fetch_rows("select hid from bloodwar.sys_city_hero where uid=0 and cid='$cid'");
	  foreach ($heros as $hero) {	//清除在该地的武将和军队		
		 throwHeroToField($hero);
		}
	  sql_query("delete from bloodwar.sys_troops where uid=0 and state=4 and cid='$cid'"); //清除城池野地驻军
	  sql_query("delete from bloodwar.mem_city_wounded where cid=$cid");//清除伤兵，逃兵，俘虏
	  sql_query("delete from bloodwar.mem_city_lamster where cid=$cid");
	  sql_query("delete from bloodwar.mem_city_captive where cid=$cid");
	  sql_query("update bloodwar.mem_world set ownercid='$cid',`type`='0' where wid='$wid'");//修改所在地的属性
	  $oldmaincid = sql_fetch_one_cell("select cid from  $BandDb.sys_city where uid='$olduid' and type=5 ");//得到原来主城坐标
	  $oldcityname = sql_fetch_one_cell("select name from  $BandDb.sys_city where uid='$olduid' and type=5 ");//得到原来主城名称
	  sql_query("replace into bloodwar.sys_city (`cid`,`uid`,`name`,`type`,`state`,`province`) values ('$cid','$newuid','$oldcityname','5','2','$province') "); //新建主城
	  $buildok = sql_fetch_one_cell("select level from $BandDb.sys_building where cid='$oldmaincid' and bid=6 ");
	  if($buildok>0){//有主城
	     sql_query("replace into bloodwar.sys_building (`cid`,`xy`,`bid`,`level`,`state`)(select $cid,xy,bid,level,0 from $BandDb.sys_building where cid='$oldmaincid' and state=0 )");//恢复城池建筑
	     $maincity = sql_fetch_one("select * from $BandDb.mem_city_resource where cid='$oldmaincid' limit 1");
		 if(!empty($maincity)){//恢复城池生产
             sql_query("replace into bloodwar.mem_city_resource (`cid`,`people`,`food`,`wood`,`rock`,`iron`,`gold`,`food_max`,`wood_max`,`rock_max`,`iron_max`,`gold_max`,`food_add`,`wood_add`,`rock_add`,`iron_add`,`lastupdate`) 
		       values ('$cid','$maincity[people]','$maincity[food]','$maincity[wood]','$maincity[rock]','$maincity[iron]','$maincity[gold]','$maincity[food_max]','$maincity[wood_max]','$maincity[rock_max]','$maincity[iron_max]','$maincity[gold_max]','$maincity[food_add]','$maincity[wood_add]','$maincity[rock_add]','$maincity[iron_add]',unix_timestamp())");
	        }else{
			   sql_query("replace into bloodwar.mem_city_resource (`cid`,`people`,`food`,`wood`,`rock`,`iron`,`gold`,`food_max`,`wood_max`,`rock_max`,`iron_max`,`gold_max`,`food_add`,`wood_add`,`rock_add`,`iron_add`,`lastupdate`) values ('$cid','50','5000','5000','5000','5000','5000','10000','10000','10000','10000','1000000',100,100,100,100,unix_timestamp())");
			}
	     updateCityResourceAdd($cid);
		 updateCityPeopleMax($cid);
		 updateCityGoldMax($cid); 
		}else{//没主城就只能给他一个新城了
		  sql_query("replace into bloodwar.sys_building (`cid`,`xy`,`bid`,`level`) values ('$cid','120','6','1')");
		  sql_query("replace into bloodwar.mem_city_resource (`cid`,`people`,`food`,`wood`,`rock`,`iron`,`gold`,`food_max`,`wood_max`,`rock_max`,`iron_max`,`gold_max`,`food_add`,`wood_add`,`rock_add`,`iron_add`,`lastupdate`) values ('$cid','50','5000','5000','5000','5000','5000','10000','10000','10000','10000','1000000',100,100,100,100,unix_timestamp())");
		}
	  sql_query("replace into bloodwar.sys_city_res_add (cid,food_rate,wood_rate,rock_rate,iron_rate,chief_add) values ('$cid',80,80,80,80,0)");//初始化生产率
	  sql_query("replace into bloodwar.mem_city_schedule (cid,create_time,next_good_event,next_bad_event) values ('$cid',unix_timestamp(),unix_timestamp()-(unix_timestamp()+8*3600)%86400 + 86400 + 86400 * rand(),unix_timestamp()-(unix_timestamp()+8*3600)%86400 + 86400 + 86400 * rand())");
	  sql_query("replace into bloodwar.mem_user_schedule (uid,start_new_protect) values ('$newuid',unix_timestamp())"); //城池定时器
	  sql_query("replace into bloodwar.sys_technic (uid,tid,level,cid,state,state_starttime,state_endtime) (select $newuid,tid,level,$cid,state,state_starttime,state_endtime from $BandDb.sys_technic where uid='$olduid')");//恢复科技
	  sql_query("replace into bloodwar.sys_city_technic (cid,tid,level) select cid,tid,level from bloodwar.sys_technic where uid='$newuid' ");//恢复科技
	  CallBackUsersSoldier($oldmaincid,$cid,$BandDb);//恢复兵力和城防
	  CallBackUsersHero($olduid,$oldmaincid,$newuid,$cid,$BandDb);//恢复主城将领
	  sql_query("update bloodwar.sys_user set lastcid='$cid' where uid='$newuid'");//设置玩家最后所在位置
	  return $cid;
    }
function CallBackUsersGoods($olduid,$newuid,$BandDb){ //玩家物品
      sql_query("replace into bloodwar.sys_goods (uid,gid,count) (select $newuid,gid,count from $BandDb.sys_goods where uid='$olduid' and count>0 ) ");//保留普通物品
	  sql_query("replace into bloodwar.sys_things (uid,tid,count) (select $newuid,tid,count from $BandDb.sys_things where uid='$olduid' and count>0 ) ");//保留任务物品
	  sql_query("replace into bloodwar.sys_user_book (bid,uid,hid,level) (select bid,'$newuid',0,level from $BandDb.sys_user_book where uid='$olduid' ) ");//保留技能书
	  sql_query("replace into bloodwar.sys_user_armor (`uid`,`armorid`,`hp`,`hp_max`,`hid`,`strong_level`,`strong_value`,`embed_pearls`,`embed_holes`,`deified`,`active_special`,`strong_times`,`combine_level`,`best_quality`) 
		 (select $newuid,armorid,hp,hp_max,sid,strong_level,strong_value,embed_pearls,embed_holes,deified,0,strong_times,combine_level,best_quality from $BandDb.sys_user_armor where uid='$olduid' ) ");//保留装备
	  sql_query("replace into bloodwar.sys_user_tie_deify_attribute(`attid`,`sid`,`value`) (select a.attid,ua.sid,a.value from bloodwar.sys_user_armor ua left join $BandDb.sys_user_tie_deify_attribute a on a.sid=ua.hid where a.sid=ua.hid and ua.uid='$newuid') ");//恢复装备炼化效果
	  sql_query("update bloodwar.sys_user_armor set hid='0' where uid='$newuid'");
	}
function CallBackUsersHero($olduid,$oldcid,$newuid,$newcid,$BandDb){//主城将领
     sql_query("replace into bloodwar.sys_city_hero(uid,name,npcid,sex,face,cid,state,level,exp,command_base,affairs_base,bravery_base,wisdom_base,loyalty,herotype)
	   (select $newuid,name,0,sex,face,hid,0,level,exp,command_base,affairs_base,bravery_base,wisdom_base,loyalty,herotype from $BandDb.sys_city_hero where cid='$oldcid' and uid='$olduid' and herotype!=1000) ");//将原hid放入cid保存
	 if (!sql_check("select 1 from $BandDb.sys_city_hero where uid='$olduid' and herotype=1000 ")){//创建君主将
		  sql_query("insert into bloodwar.sys_city_hero(uid,name,sex,face,cid,state,level,command_base,affairs_base,bravery_base,wisdom_base,loyalty,herotype) (select uid,name,sex,face,$newcid,0,1,50,1,1,1,100,1000 from bloodwar.sys_user where uid='$newuid' ) ");
		}else{//存在就恢复君主将数据并将君主将等级重置成1级，统率加50
		  $jzinfo = sql_fetch_one("select * from $BandDb.sys_city_hero where uid='$olduid' and herotype=1000 limit 1");
	      $jzlevel = $jzinfo['level'];
		  $jzoldhid = $jzinfo['hid'];
		  $jzcommand =50+$jzlevel;
		  sql_query("insert into bloodwar.sys_city_hero(uid,name,sex,face,cid,state,level,command_base,affairs_base,bravery_base,wisdom_base,loyalty,herotype) (select uid,name,sex,face,$jzoldhid,0,1,$jzcommand,$jzlevel,$jzlevel,$jzlevel,100,1000 from bloodwar.sys_user where uid='$newuid' ) ");
	    }
	 sql_query("replace into bloodwar.sys_hero_skill(`hid`,`skill`) select ua.hid,a.skill from bloodwar.sys_city_hero ua left join $BandDb.sys_hero_skill a on a.hid=ua.cid where a.hid=ua.cid and ua.uid='$newuid'");//恢复玩家将领技能
	 CallBackUsersChild($olduid,$oldcid,$newuid,$BandDb);//保留孩子将
	 sql_query("update bloodwar.sys_city_hero set cid='$newcid' where uid='$newuid' ");//设置将领所在城池位置
	 sql_query("update bloodwar.sys_city_hero set level=120 where uid='$newuid' and level>120");//将超级名将恢复成普通将的等级
	 sql_query("replace into bloodwar.mem_hero_blood(`hid`,`force`,`force_max`,`energy`,`energy_max`) (select hid,'150','150','150','150' from bloodwar.sys_city_hero where cid='$newcid' and uid='$newuid' ) ");//生成将领的BLOOD和精力
	}
function CallBackUsersChild($olduid,$oldcid,$newuid,$BandDb){//在培养期间的孩子
     $childinfos = sql_fetch_rows("select * from $BandDb.sys_user_child where uid='$olduid'");//保留培养中的孩子和已经成为了名将的孩子
	 if(!empty($childinfos)){
	     foreach ($childinfos as $childinfo){//生成一条孩子记录
		     $state = 0;
			 $newhid = 0;
			 $outhid=$childinfo['out_hid'];
			 if($outhid>0){//已成名将看是否保留没有，如果没保留就断续下一条，
			      if(!sql_check("select 1 from $BandDb.sys_city_hero where hid='$outhid' and cid='$oldcid' ")) continue;
			      $state =4;
				  $newhid = sql_fetch_one_cell("select hid from bloodwar.sys_city_hero where cid='$outhid' and uid='$newuid'");
				}
			 $shid =sql_insert("insert into bloodwar.sys_user_child (`uid`,`name`,`sex`,`face`,`command_base`,`affairs_base`,`bravery_base`,`wisdom_base`,`command_add`,`affairs_add`,`bravery_add`,`wisdom_add`,`qixin_multip`,`wenpo_multip`,`upgrade_value`,`is_change`,`out_hid`)
			   values ('$newuid','$childinfo[name]','$childinfo[sex]','$childinfo[face]','$childinfo[command_base]','$childinfo[affairs_base]','$childinfo[bravery_base]','$childinfo[wisdom_base]','$childinfo[command_add]','$childinfo[affairs_add]','$childinfo[bravery_add]','$childinfo[wisdom_add]','$childinfo[qixin_multip]','$childinfo[wenpo_multip]','$childinfo[upgrade_value]','0','$newhid') ");
             $oldshid = $childinfo['hid'];
			 $mHid = sql_fetch_one_cell("select mHid from $BandDb.mem_marry_relation where shid='$oldshid' ");
		     sql_query("insert into bloodwar.mem_marry_relation (`id`,`uid`,`mHid`,`shid`,`state`) values ('$shid','$newuid','$mHid','$shid','$state') ");
	 	    }
	    }
	}
function CallBackUsersWife($olduid,$newuid,$BandDb){//保留玩家结识的红颜
      sql_query("replace into bloodwar.mem_marry_hero_favor (`uid`,`hid`,`favor`,`state`,`count`,`coolingEndtime`,`proposeCoolTime`) 
	     (select $newuid,hid,favor,state,count,coolingEndtime,proposeCoolTime from $BandDb.mem_marry_hero_favor where uid='$olduid' ) ");
	  sql_query("replace into bloodwar.mem_marry_during (`uid`,`hid`,`starttime`,`endtime`,`isSpeed`) 
	     (select $newuid,hid,starttime,endtime,isSpeed from $BandDb.mem_marry_during where endtime<unix_timestamp() and uid='$olduid' ) ");
	}
function CallBackUsersSoldier($oldmaincid,$cid,$BandDb){//主城城防、兵力
      sql_query("replace into bloodwar.sys_city_defence (cid,did,count) (select $cid,did,count from $BandDb.sys_city_defence where cid='$oldmaincid' and count>0 ) ");
	  sql_query("replace into bloodwar.sys_city_soldier (cid,sid,count) (select $cid,sid,count from $BandDb.sys_city_soldier where cid='$oldmaincid' and count>0 ) ");
	}	
function TotallUsersMoney($olduid,$newuid,$oldunionid,$BandDb){//sys_user_donate
      $totalmoney =0;
	  $BandMss ='';
      $usemoney = sql_fetch_one_cell("select donate from $BandDb.sys_user_donate where uid='$olduid' and unionid='$oldunionid' ");//联盟贡献将转化成商票
	  if(!empty($usemoney)){
	      $totalmoney =ceil($usemoney/100);
		  $BandMss .='联盟贡献补偿:'.$totalmoney.'商票！<br/>';
		  sql_query("insert into bloodwar.sys_goods (`uid`,`gid`,`count`) values ('$newuid',19302,'$totalmoney') on duplicate key update count='$totalmoney'");//商票id
	  	}
	  $cityinfos = sql_fetch_rows("select cid,type from $BandDb.sys_city where uid='$olduid' ");//城池补偿
	  if(empty($cityinfos)){ 
	     if($totalmoney>0){
		      sendSysMail($newuid,'联盟贡献补偿',$BandMss);
			  return 1;
		    }
	     return 0;
		}
	  $citystypemoney=array(500,1000,3000,5000,8000);//补偿基数
	  $cmoney=0;
	  $dmoney=0;
	  $smoney=0;
	  $hmoney=0;
	  foreach ($cityinfos as $cityinfo){
	      $type = $cityinfo['type'];
		  $cid = $cityinfo['cid'];
		  if($type<5){
		     $citymoney = $citystypemoney[$type];//城池补偿
			 if($type==0) {
			     $cityleve10=sql_fetch_one_cell("select count(level) from $BandDb.sys_building where level>9 and cid='$cid'");
			     $cityleve10 = empty($cityleve10)?0:$cityleve10*60;
				 $citymoney = $citymoney+$cityleve10;
				}
	         $defmoney = sql_fetch_one_cell("select SUM(count*did) from $BandDb.sys_city_defence where cid='$cid' ");//城防补偿
	         $defmoney= empty($defmoney)?0:$defmoney/100;
		     $soldiermoney = sql_fetch_one_cell("select SUM(count*sid) from $BandDb.sys_city_soldier where cid='$cid' ");//兵力补偿
	         $soldiermoney = empty($soldiermoney)?0:$soldiermoney/10000;
		     $heromeoney = CountUserCityHero($olduid,$cid,$BandDb);//将领补偿
			}else if($type==5){
			 $heromeoney =  sql_fetch_one_cell("select count(hid)*100 from $BandDb.sys_city_hero where cid='$cid' and (npcid>0 || herotype=10001)");
			}
		  $cmoney=$cmoney+$citymoney;
	      $dmoney=$dmoney+$defmoney;
	      $smoney=$smoney+$soldiermoney;
	      $hmoney=$hmoney+$heromeoney;
		}
	  $tmoney = $cmoney+$dmoney+$smoney+$hmoney;
	  $BandMss .='城池补偿:'.$cmoney.'<br/>'.'城防补偿:'.$dmoney.'<br/>'.'兵力补偿:'.$smoney.'<br/>'.'将领补偿:'.$hmoney.'<br/>'.'---合计---:'.$tmoney;
	  sendSysMail($newuid,'玩家合服补偿',$BandMss);
	  sql_query("update bloodwar.sys_user set `money`=money+'$tmoney' where uid='$newuid'");	 
	  return $tmoney;
	}
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
function SetBandUserState($uid){//设置玩家为新手状态
	 $passtype = sql_fetch_one_cell("select passtype from bloodwar.sys_user where uid='$uid'");
	 if ($passtype==='tw') {
		 sql_insert("insert into bloodwar.sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1006','temp','0',unix_timestamp())");
	    }else{
		 $temp=$GLOBALS['user']['customer_help'];
		 sql_insert("insert into bloodwar.sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1006','$temp','0',unix_timestamp())");
		 $temp=$GLOBALS['user']['welcometo_bloodwar'];
		 sql_insert("insert into bloodwar.sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1007','$temp','0',unix_timestamp())");
		 $temp=$GLOBALS['user']['game_instructions'];
		 sql_insert("insert into bloodwar.sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1008','$temp','0',unix_timestamp())");
		 $temp=$GLOBALS['user']['new_player_remind'];
		 sql_insert("insert into bloodwar.sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','1009','$temp','0',unix_timestamp())");
	     $a=sql_fetch_one_cell("select start_new_protect + 604800-unix_timestamp() from bloodwar.mem_user_schedule where uid='$uid'");
		 $a=floor($a/86400);
		 $mid=11-$a;
		 sql_query("insert into bloodwar.sys_alarm (`uid`,`task`) values ('$uid',1) on duplicate key update `task`=1");
		 if($mid>=6&&$mid<=11){
		  	 $onemail=sql_fetch_rows("select * from bloodwar.sys_mail_sys_box where contentid='$mid' and uid='$uid'");
			 if(empty($onemail)){
				 sql_insert("insert into bloodwar.sys_mail_sys_box (`uid`,`contentid`,`title`,`read`,`posttime`) values ('$uid','$mid','".$GLOBALS['doLogin']['protect_user_info']."','0',unix_timestamp())");
				 sql_query("insert into bloodwar.sys_alarm (`uid`,`mail`) values ('$uid',1) on duplicate key update `mail`=1");
				}
			}
		}
	 sql_query("insert into bloodwar.sys_alarm (`uid`,`mail`) values ('$uid',1) on duplicate key update `mail`=1");
	 sql_query("insert into bloodwar.sys_user_task (uid,tid,state) values ('$uid',1,0) on duplicate key update state=state");
	 sql_query("insert into bloodwar.sys_user_task (uid,tid,state) values ('$uid',80,0) on duplicate key update state=state");
	 sql_query("insert into bloodwar.sys_user_task (uid,tid,state) values ('$uid',290,0) on duplicate key update state=state");
	 sql_query("insert into bloodwar.sys_online (uid,lastupdate,onlineupdate,onlinetime) values ('$uid',unix_timestamp()-100,unix_timestamp(),100) on duplicate key update uid='$uid'");
	 sql_query("insert into bloodwar.sys_user_sign (uid,time,state) values ('$uid',unix_timestamp(),1) on duplicate key update state=1");
	 $addTime=86400*3;
	 sql_query("insert into mem_user_buffer (uid,buftype,bufparam,endtime) values ('$uid','10333',0,unix_timestamp()+$addTime) on duplicate key update endtime=endtime + 86400" );
	 completeTaskWithTaskid($uid, 291);
	 addGoods($uid,50101,1,4);//送1级的新手礼包
	}
function delBandSameUser(){
      $deluserinfo = sql_fetch_rows("select * from bloodwar.banduser where state=1 and uid>1 ");
      if(empty($deluserinfo)) return 0;
	  $useend =0;
	  $bandss =0;
      foreach ($deluserinfo as $deluser){
	      if($useend==0){
		     $uidinfo=$deluser['uid'];
		    }else{
			 $uidinfo .=','.$deluser['uid'];
			}
	      $useend++;
		  $delsave =0;
	      $savepassport = sql_fetch_rows("select uid from bloodwar.banduser where state=0 and passport='$deluser[passport]' ");
		  if(!empty($savepassport)){//要转元宝和将领
		      $delsave=CallBackMonarch($savepassport,$deluser['uid']);
			  if($delsave==1){
			      if($bandss==0){
			         $suidinfo = sql_fetch_one_cell("select uid from bloodwar.banduser where state=0 and passport='$deluser[passport]' limit 1");
			         $bandss++;
					}else{
					 $savebuid = sql_fetch_one_cell("select uid from bloodwar.banduser where state=0 and passport='$deluser[passport]' limit 1");
			         $suidinfo .=','.$savebuid;
					}
				} 
		    }
		  sql_query("delete from bloodwar.banduser  where uid='$deluser[uid]'");	//删除放弃的号
		}
	  if(!empty($suidinfo)){//消除重复帐号
	      sql_query("delete from bloodwar.banduser  where uid in($suidinfo) ");
	    }
      if(!empty($uidinfo)){
	      sql_query("delete from bloodwar.sys_user  where uid in($uidinfo) ");//删除用户帐号
	      sql_query("update bloodwar.sys_city set uid=894 where uid in($uidinfo) ");///删除城池
		  sql_query("delete from bloodwar.sys_goods  where uid in($uidinfo) ");
		  sql_query("delete from bloodwar.sys_things  where uid in($uidinfo) ");
		  sql_query("delete from bloodwar.sys_user_armor  where uid in($uidinfo) ");
		  sql_query("delete from bloodwar.sys_user_child  where uid in($uidinfo) ");
		  sql_query("delete from bloodwar.sys_user_task  where uid in($uidinfo) ");
		}
	}
function CallBackMonarch($saveuid,$deluid){
      $snum=0;
	  foreach($saveuid as $suid){
	     if($snum==0){
		     $uid = $suid['uid'];
		    }else{
		     $uid .=','.$suid['uid'];
			}
	     $snum++;
	    }
	  //如果玩家还有2个或2个以上的保留帐号,将以官职，爵位，声望由高到底方式来确定将补偿放入到那个帐号！
      $saveuserinfo = sql_fetch_one("select * from bloodwar.sys_user where uid in($uid) order by officepos desc,nobility desc,prestige desc limit 1 ");
	  $deluserinfo = sql_fetch_one("select * from bloodwar.sys_user where uid='$deluid'");
	  $savecid = $saveuserinfo['lastcid'];
	  $saveuid = $saveuserinfo['uid'];
	  $delcid = $deluserinfo['lastcid'];
	  $buchan = ($deluserinfo['nobility']-1)>0?(($deluserinfo['nobility']-1)*100):0;//爵位补偿
	  $gift = $deluserinfo['gift'];//原有礼金
	  $money = $deluserinfo['money']+$buchan;//原有元宝+爵位补偿元宝
	  sql_query("delete from bloodwar.sys_city_hero  where  uid='$deluid' and herotype=1000");
	  sql_query("update bloodwar.sys_city_hero set uid='$saveuid',cid='$savecid',state=0 where uid='$deluid' and herotype!=1000");//将删除帐号将领转入保留帐号
	  sql_query("update bloodwar.sys_user_child set uid='$saveuid' where uid='$deluid'");//将删除帐号孩子将领转入保留帐号
	  sql_query("update bloodwar.mem_marry_relation set uid='$saveuid' where uid='$deluid'");//将删除帐号培养孩子转入保留帐号
	  sql_query("update bloodwar.sys_user set money=money+'$money',gift=gift+'$gift' where uid='$saveuid'");//将删除帐号元宝写入保留帐号
	  sql_query("update bloodwar.sys_user_armor set uid='$saveuid' where uid='$deluid'");//将删除帐号装备转入保留帐号
	  $BandMss='弃号原有元宝(已含合服补偿):'.$deluserinfo['money'].'<br/>'.'弃号原有礼金:'.$gift.'<br/>'.'爵位补偿元宝:'.$buchan.'<br/>'.'---合计---:'.$money.'元宝！';
	  sendSysMail($saveuid,'玩家弃号补偿',$BandMss);//发信件
	  return $snum;
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
function CreatNPCForce($userinfo,$sortid){//创建NPC势力
     $c_types = $userinfo['citytype'];
	 if($sortid==1){//创建君主将
	     $heroid=2; 
	     if(sql_check("select 1 from bloodwar.sys_user where uid='$userinfo[uid]' ")){//存在
		      sql_query("update bloodwar.sys_user set name='$userinfo[name]' where uid='$userinfo[uid]' ");//更名
			  $lastcid=sql_fetch_one_cell("select lastcid from bloodwar.sys_user where uid='$userinfo[uid]' ");//看看有没有主城
			  if($lastcid>0){//有主城
			      $oldhid=sql_fetch_one_cell("select hid from bloodwar.sys_city_hero where uid='$userinfo[uid]' and cid='$lastcid' limit 1");//看看有没有主将
			      if(!empty($oldhid)){//有主将,就重改主将属性
				       sql_query("update bloodwar.sys_city_hero set name='$userinfo[name]',command_base='$userinfo[command]',affairs_base='$userinfo[affairs]',bravery_base='$userinfo[bravery]',wisdom_base='$userinfo[wisdom]' where uid='$userinfo[uid]' and hid='$oldhid' ");
					}else{//没主将,就生成一个
					  $oldhid =CreatNpcForceHero($userinfo,$lastcid,$heroid);
					}
			     sql_query("update bloodwar.sys_city set type='$userinfo[province]' where uid='$userinfo[uid]' and cid='$lastcid' ");//更改主城类型
			     $msgg=sql_fetch_one("select * from bloodwar.sys_city_hero where uid='$userinfo[uid]' and hid='$oldhid' ");
				 return $msgg;//更改成功返回
				}
		    }else{//不存在,创建一个
			  sql_insert("insert into bloodwar.sys_user (`uid`,`name`,`passtype`,`passport`,`state`,`group`,`sex`,`face`,`prestige`,`warprestige`,`money`,`rank`,`lastcid`,`union_id`,`union_pos`,`nobility`,`officepos`,`regtime`,`domainid`,`war_attack_prestige`,`war_defence_prestige`,`gift`,`last_pay`,`honour`,`armor_column`,`achivement_count`,`achivement_point`) 
		          values ('$userinfo[uid]','$userinfo[name]','npc','$userinfo[uid]','0','4','0','0','32000000',0,'0',0,'0',0,0,'0','0',unix_timestamp(),0,0,0,'0',0,'0','0',0,0) ");
        	}
		}else{
		  if (!sql_check("select 1 from bloodwar.sys_user where uid='$userinfo[uid]' ")){//没有创建NPC君主
		       return null;//错误代码@请先创建NPC君主		  
		    }
		  if($c_types ==0){//野地将
		      $oldhid =CreatNpcForceHero($userinfo,0,0);
		      $msgg=sql_fetch_one("select * from bloodwar.sys_city_hero where hid='$oldhid' ");
			  return $msgg;
		    }
		  $heroid =1;
		  $userinfo['citytype']=$c_types-1;
		}
	  $newcids=0;
	  do{
	     $newcids = CreatNPCForceCity($userinfo);//建城
	    }while($newcids<1);
	  $g_level = sql_fetch_one_cell("select level from bloodwar.sys_building where `cid`='$newcids' and `bid`='6' ");//官府等级
	  $npcnum =sql_fetch_one_cell("select npcvalue from bloodwar.cfg_city_npcvalue where level='$g_level' ");//基础兵力数量
	  $npcValue = $npcnum*(1+$userinfo['citytype']+($userinfo['soldiers']/10));//实际兵力数量
	  CreatNpcForceSoldiers($npcValue,$userinfo['province'],$newcids);//兵种与数量
	  $newhid=CreatNpcForceHero($userinfo,$newcids,$heroid);//生成将领
	  $msgg=sql_fetch_one("select * from bloodwar.sys_city_hero where uid='$userinfo[uid]' and hid='$newhid' ");				
	  return $msgg;
	}
function CreatNpcForceSoldiers($npcValue,$province,$cid){//随机生成兵种
     $sodides='12,1,2,3,4,5,6,7,8,9,10,11,12';
	 $sodiertype[14]='9,87,88,89,90,91,92,93,94,95';
	 $sodiertype[15]='9,78,79,80,81,82,83,84,85,86';
	 $sodiertype[16]='9,51,52,53,54,55,56,57,58,59';
	 $sodiertype[17]='9,60,61,62,63,64,65,66,67,68';
	 $sodiertype[18]='9,69,70,71,72,73,74,75,76,77';
	 $soldiers = $province>13?$sodiertype[$province]:$sodides;//在少数民族就出少数民族的兵种
	 $soldiersarray = explode(",", $soldiers);
	 $soldiervalue=array(0, 23, 31, 70, 90, 135, 140, 298, 285, 875, 1000, 1375, 2900, 31, 90, 135, 140, 285, 26, 89, 127, 128, 263, 31, 90, 135, 140, 285,31, 90, 135, 140, 285,
      23,31, 70, 90, 135, 140, 298, 285, 875, 1000, 1375, 2900,285, 2900, 90, 135, 140, 298,70, 90, 135, 140, 298, 285, 1000, 1375, 2900, 70, 90, 135, 140, 298, 285, 1000, 1375, 2900,
      70, 90, 135, 140, 298, 285, 1000, 1375, 2900, 70, 90, 135, 140, 298, 285, 1000, 1375, 2900, 70, 90, 135, 140, 298, 285, 1000, 1375, 2900); 
	 $totalRnd = 0;
	 $valueMap = array();
	 $npcSoldiers ='';
	 $typecount=0;
	 foreach ($soldiersarray as $sid) {
	     $valueMap[$sid]=$sid;
		 $typecount++;
	    }
	 sql_query("delete from sys_city_soldier where `cid`='$cid' ");
	 foreach ($valueMap as $k=>$v){
		 $npcSoldiers.=$k.",";
		 $count = (int)ceil($npcValue *$v *0.1 /$soldiervalue[$k]);
		 $npcSoldiers.= $count.",";
		 sql_query("insert into sys_city_soldier (`cid`,`sid`,`count`) values ('$cid','$k','$count')");
	    }
	}
function CreatNpcForceHero($userinfo,$cid,$heroid){//0野地1/将领/2存在君主没城//cfg_rumor_hero//cfg_npc_hero要对这两个表进行打操作
      $level=mt_rand(65,95);
	  $herosex=$userinfo['herosex'];
	  $heroface= $herosex>0?mt_rand(1007,1065):mt_rand(1,10);
	  $npchid = sql_fetch_one_cell("select npcid+1 from cfg_npc_hero order by npcid desc limit 1");//降序选择最大的那条记录
	  sql_query("insert into cfg_npc_hero(`npcid`,`uid`,`name`,`sex`,`face`,`affairs_base`,`bravery_base`,`wisdom_base`,`province`,`introduce`,`type`) 
	       values ('$npchid','$userinfo[uid]','$userinfo[heroname]','$herosex','$heroface','$userinfo[affairs]','$userinfo[bravery]','$userinfo[wisdom]','$userinfo[province]','$userinfo[herobrief]','0' ) ");
	  sql_query("insert into cfg_rumor_hero (`npcid`,`price`) values ('$npchid','10')");
	  $total_exp = sql_fetch_one_cell ( "select total_exp from cfg_hero_level where level='$level'" );
	  if($heroid==0){//野地名将
	      $sql = "insert into sys_city_hero (`uid`,`name`,`npcid`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`command_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`herotype`)
		       values ('0','$userinfo[heroname]','$npchid','$herosex','$heroface','0','4','$level','$total_exp','$userinfo[affairs]','$userinfo[bravery]','$userinfo[wisdom]','$userinfo[command]','0','0','0','70','0')";
	      $hid = sql_insert($sql);
		  $fieldhero=sql_fetch_one("select * from bloodwar.sys_city_hero where  hid='$hid' ");
		  $newcid=getFieldHeros($fieldhero,$userinfo['province']);
		  $forcemax=100+floor($bravery/3);
	      $energymax=100+floor($wisdom /3);	  
	      sql_query("insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`) values ('$hid',$forcemax,$forcemax,$energymax,$energymax)");
	      $wid=cid2wid($newcid);
		  $g_level= sql_fetch_one_cell("select level from mem_world where wid='$wid'");
		  $g_level =$g_level<1?1:$g_level;
		  $npcnum =sql_fetch_one_cell("select npcvalue from bloodwar.cfg_field_npcvalue where level='$g_level' ");//基础兵力数量
	      $npcValue = $npcnum*(1+$userinfo['citytype']+($userinfo['soldiers']/10));//实际兵力数量
	      CreatNpcForceSoldiers($npcValue,$userinfo['province'],$newcid);//兵种与数量
		  return $hid;
        }else if($heroid==2){//主将
		   $oldsex =sql_fetch_one_cell("select sex from bloodwar.sys_user where uid='$userinfo[uid]' ");
	       $oldface =sql_fetch_one_cell("select face from bloodwar.sys_user where uid='$userinfo[uid]' ");
		   if(empty($oldsex)||empty($oldface)){
		       sql_query("update bloodwar.sys_user set lastcid='$cid',sex='$herosex',face='$heroface' where uid='$userinfo[uid]' ");
		    }else{
		      $herosex = $oldsex;
		      $heroface = $oldface;
			}
		}
	  $sql = "insert into sys_city_hero (`uid`,`name`,`npcid`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`command_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`herotype`)
		  values ('$userinfo[uid]','$userinfo[heroname]','$npchid','$herosex','$heroface','$cid','1','$level','$total_exp','$userinfo[affairs]','$userinfo[bravery]','$userinfo[wisdom]','$userinfo[command]','0','0','0','70','0')";
	  $hid = sql_insert($sql);
	  $forcemax=100+floor($bravery/3);
	  $energymax=100+floor($wisdom /3);	  
	  sql_query("insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`) values ('$hid',$forcemax,$forcemax,$energymax,$energymax)");
	  sql_query("update bloodwar.sys_city set chiefhid='$hid' where cid='$cid'");
	  return $hid;	
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
	  sql_query("replace into bloodwar.sys_city_technic(`cid`,`tid`,`level`)(select $cid,tid,level from bloodwar.sys_city_technic where cid='$buildingcid' )");
	  //上城防
      sql_query("replace into bloodwar.sys_city_defence(`cid`,`did`,`count`)(select $cid,did,count from bloodwar.sys_city_defence where cid='$buildingcid' )");
	  //城池定时器
	  sql_query("replace into bloodwar.mem_city_schedule (`cid`,`create_time`,`next_good_event`,`next_bad_event`) values ('$cid',unix_timestamp(),unix_timestamp()-(unix_timestamp()+8*3600)%86400 + 86400 + 86400 * rand(),unix_timestamp()-(unix_timestamp()+8*3600)%86400 + 86400 + 86400 * rand())");
	  sql_query("insert into bloodwar.mem_user_schedule (uid,start_new_protect) values ('$newuid',unix_timestamp()) on duplicate key update start_new_protect=unix_timestamp()");
	  return $cid;
    }
function getFieldHeros($hero,$province){//生成野地将
	 $hid = $hero['hid'];
	 $findtimes=10;
	 while($findtimes > 0){
	     $wid = sql_fetch_one_cell("select wid from mem_world where ownercid=0 and province='$province' and type > 1 and state=0 order by rand() limit 1");
		 $newcid = wid2cid($wid);
		 $oldhero = sql_fetch_one("select * from sys_city_hero where uid=0 and cid='$newcid'");
		 if (empty($oldhero)){ 
			 sql_query("update sys_city_hero set cid='$newcid',state=4,uid='0',loyalty=70 where hid='$hid'");
			 return $newcid;
		    }else{
		     if ($oldhero['npcid'] > 0){
				  continue;
			    }else{
			     sql_query("update sys_city_hero set cid=$newcid,state=4,uid='0',loyalty=70 where hid='$hid'");
			     sql_query("delete from sys_city_hero where hid=$oldhero[hid]");
			     sql_query("delete from mem_hero_blood where hid='$oldhero[hid]'");
			     if(!isActHero($hero["herotype"]))
					sql_query("insert into sys_recruit_hero (`name`,`npcid`,`sex`,`face`,`cid`,`level`,`exp`,`affairs_add`,`bravery_add`,`wisdom_add`,`affairs_base`,`bravery_base`,`wisdom_base`,`loyalty`,`gold_need`,`gen_time`) values ('$oldhero[name]','$oldhero[npcid]','$oldhero[sex]','$oldhero[face]','0','$oldhero[level]','$oldhero[exp]','$oldhero[affairs_add]','$oldhero[bravery_add]','$oldhero[wisdom_add]','$oldhero[affairs_base]','$oldhero[bravery_base]','$oldhero[wisdom_base]','66',0,unix_timestamp())");
				 $troop = sql_fetch_one("select * from sys_troops where uid=0 and cid='$newcid' and hid=$oldhero[hid]");
				 if (!empty($troop)){
				     sql_query("update sys_troops set hid=$hid where id=$troop[id]");
				    }  
				 return $newcid;
			    }
		    }
	    }
	}
?>