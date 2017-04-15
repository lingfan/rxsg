<?php
function UpdateUsersCurse(){//天灾，人祸，天赐,排行系统
      $nowtime = date('is');
	  if ($nowtime < 12) {
	     $newttime = sql_fetch_one_cell("select unix_timestamp()");
	     $oldtime = sql_fetch_one_cell("select value from mem_state  where state=10");
		 if($newttime-$oldtime>300){//保证整点只运行一次
		      $day = sql_fetch_one_cell("select DATE_FORMAT(NOW(),'%w')");
			  $hour = sql_fetch_one_cell("select DATE_FORMAT(NOW(),'%k')");
		      updateCityCalamity();//天灾，人祸，天赐,兵变
	          HandleUsersRank();//排行更新
              updateherorun();//将领逃跑
			  HandleTaskAutoDonate();//自动增加史诗任务
			  LouYangTaskStart($day,$hour);//检测洛阳史诗是否达到开启条件！
			  if($hour==16) check_user_act();//每天16点检测一次新人王和联盟排名
			  if($day==2 && $hour==20 )GetUserBattleKing();//每周六 18点发放一次战场排名奖励
		    }
		}
    }
function updateCityCalamity(){
       $citys = sql_fetch_rows('select r.*,c.name,c.uid from mem_city_resource r,sys_city c where r.cid=c.cid and c.uid>1000 and c.state<3 and r.vacation=0 and (r.food<=0 or r.gold<=0 or r.rock>r.rock_max or r.iron>r.iron_max or r.food>r.food_max or r.wood>r.wood_max or r.people>r.people_max or r.gold>r.gold_max or r.morale<40)');
       //zhaoan = sql_fetch_rows('select c.cid from sys_city c,mem_user_buffer b where b.buftype=20 and b.uid=c.uid');
	   $zhaoan = sql_fetch_rows('select c.cid from sys_city c,mem_user_buffer b where b.buftype=10333 and b.uid=c.uid');
       if ($zhaoan) {
            foreach ($zhaoan as $c) {
                $hasz[] = $c['cid'];
            }
        }
       $comma = '';
       $sname = $GLOBALS['battle']['patrol_report_soldier'];
       $tname = $GLOBALS['curse']['resource'];//人祸
	   $manname = $GLOBALS['manna']['resource'];//天赐
       foreach ($citys as $city) {
            $cid = $city['cid'];
            $uid = $city['uid'];
            $cityname = (((($city['name'] . '（') . $cid % 1000) . ',') . floor($cid / 1000)) . '）';
			if ($city['food'] <= 0 && ($hasz && !in_array($cid, $hasz) || !$hasz)) {
                  $has = sql_fetch_rows("select * from sys_troops where `cid`='{$cid}' and `state`=4 and `uid`='{$uid}'");
                  if ($has) {
                      foreach ($has as $v) {
                         $id .= $comma;
                         $id .= $v['id'];
                         $comma = ',';
                        }
                      $con = $cityname . $GLOBALS['curse']['troops_back'];
                      sendReport($uid, 3, 16, $cid, $cid, $con);
                    } else {
                      $soldiers = sql_fetch_rows("select * from sys_city_soldier where `cid`='{$cid}' and `count`>0");
                      if ($soldiers) {
                         $con1 = '';
                         foreach ($soldiers as $v) {
                             if ($v['count'] > 0) {
                                 $count = round($v['count'] * 0.2);
                                 if ($count > 0) {
                                     $sArray[$v['sid']] = $count;
                                     $con1 .= (($sname[$v['sid']] . '&nbsp;-&nbsp;') . $count) . '<br/>';
                                     $lamster = $count * 0.2;
                                     $lArray[$v['sid']] = round($count * 0.8);
                                    }
                                }
                            }
                         if ($sArray) {
                             if ($lArray) {
                                 addCityLamsters($cid, $lArray, 1);
                                }
                             addCitySoldiers($cid, $sArray, 0);
                             $con = ($cityname . $GLOBALS['curse']['troops_lost']) . $con1;
                             sendReport($uid, 3, 16, $cid, $cid, $con);
                             refreshFoodArmyUsers($cid);
                            }
                        }
                    }
            }
            if ($city['gold'] <= 0) {
                sql_query("update sys_city_hero set `loyalty`=`loyalty`-1 where `cid`='{$cid}'");
                $con = $cityname . $GLOBALS['curse']['gold_less'];
                sendReport($uid, 3, 16, $cid, $cid, $con);
            }
			$gold = $city['gold'];
            $gold_max = $city['gold_max'];
            $food = $city['food'];
            $food_max = $city['food_max'];
            $wood = $city['wood'];
            $wood_max = $city['wood_max'];
            $iron = $city['iron'];
            $iron_max = $city['iron_max'];
            $rock = $city['rock'];
            $rock_max = $city['rock_max'];
            $people = $city['people'];
            $people_max = $city['people_max'];
            $morale = $city['morale'];
            $moraleadd = 0;
			$manna = 0;//天赐
            if ($gold > $gold_max) {
                $moraleadd += 5;
                $gcnt = round($gold * 0.1);
                if (!$gcnt) {
                    $gcnt = $gold;
                }
                $ngold = $gold * 0.9;
                $con = ($cityname . $tname[0]) . $gcnt;
                sendReport($uid, 3, 16, $cid, $cid, $con);
            } else {
                $ngold = $gold;
				$manna++;
            }
            if ($food > $food_max) {
                $moraleadd += 5;
                $fcnt = round($food * 0.1);
                if (!$fcnt) {
                    $fcnt = $food;
                }
                $nfood = $food * 0.9;
                $con = ($cityname . $tname[1]) . $fcnt;
                sendReport($uid, 3, 16, $cid, $cid, $con);
            } else {
                $nfood = $food;
				$manna++;
            }
            if ($wood > $wood_max) {
                $moraleadd += 5;
                $wcnt = round($wood * 0.1);
                if (!$wcnt) {
                    $wcnt = $wood;
                }
                $nwood = $wood * 0.9;
                $con = ($cityname . $tname[2]) . $wcnt;
                sendReport($uid, 3, 16, $cid, $cid, $con);
            } else {
                $nwood = $wood;
				$manna++;
            }
            if ($rock > $rock_max) {
                $moraleadd += 5;
                $rcnt = round($rock * 0.1);
                if (!$rcnt) {
                    $rcnt = $rock;
                }
                $nrock = $rock * 0.9;
                $con = ($cityname . $tname[3]) . $rcnt;
                sendReport($uid, 3, 16, $cid, $cid, $con);
            } else {
                $nrock = $rock;
				$manna++;
            }
            if ($iron > $iron_max) {
                $moraleadd += 5;
                $icnt = round($iron * 0.1);
                if (!$icnt) {
                    $icnt = $iron;
                }
                $niron = $iron * 0.9;
                $con = ($cityname . $tname[4]) . $icnt;
                sendReport($uid, 3, 16, $cid, $cid, $con);
            } else {
                $niron = $iron;
				$manna++;
            }
            if ($people > $people_max) {
                $moraleadd += 5;
                $pcnt = round($people * 0.1);
                if (!$pcnt) {
                    $pcnt = $people;
                }
                $npeople = $people * 0.9;
                $con = ($cityname . $tname[5]) . $pcnt;
                sendReport($uid, 3, 16, $cid, $cid, $con);
            } else {
                $npeople = $people;
				$manna++;
            }
            if ($morale < 35) {
                $pcnt1 = round($npeople * 0.1);
                if (!$pcnt1) {
                    $pcnt1 = $npeople;
                }
                $npeople = $npeople * 0.9;
                $con = ($cityname . $tname[6]) . $pcnt1;
                sendReport($uid, 3, 16, $cid, $cid, $con);
            }
			if($manna>2){//至少3项没超过最大值就给1次天赐！
			  $mrate = floor(mt_rand(0,100)/10);
			  if($mrate>8) $mrate = mt_rand(3,8);
			  $moraleadd -= 5;
			  if($moraleadd<1) $moraleadd = 1;
			  switch($mrate){
			     case 0:{ 
				      $gcnt = round($ngold * 0.1);
                      if (!$gcnt) { 
                         $gcnt = $ngold;
                        }
                      $ngold = $ngold * 1.1;
					  break;
					}
			     case 1:{ 
				      $gcnt = round($nfood * 0.1);
                      if (!$gcnt) { 
                         $gcnt = $nfood;
                        }
                      $nfood = $nfood * 1.1;
					  break;
					}
				 case 2:{ 
				      $gcnt = round($nwood * 0.1);
                      if (!$gcnt) { 
                         $gcnt = $nwood;
                        }
                      $nwood = $nwood * 1.1;
					  break;
					}
			     case 3:{ 
				      $gcnt = round($nrock * 0.1);
                      if (!$gcnt) { 
                         $gcnt = $nrock;
                        }
                      $nrock = $nrock * 1.1;
					  break;
					}
			     case 4:{ 
				      $gcnt = round($niron * 0.1);
                      if (!$gcnt) { 
                         $gcnt = $niron;
                        }
                      $niron = $niron * 1.1;
					  break;
					}
				 case 5:{ 
				      $gcnt = round($npeople * 0.1);
                      if (!$gcnt) { 
                         $gcnt = $npeople;
                        }
                      $npeople = $npeople * 1.1;
					  break;
					}
				 case 6:{ 
				      $gcnt = round($ngold * 0.1);
                      if (!$gcnt) { 
                         $gcnt = $ngold;
                        }
                      $ngold = $ngold * 1.1;
					  break;
					}
				 case 7:{ //添加珠宝每样一个
				      $gcnt = 1;
                      for($i=30;$i<39;$i++)
					    addGoods($uid,$i,1,3);
					  break;
					}
				 case 8:{ //名将投奔
				      $npcid = mt_rand(1,1033);
					  $type=mt_rand(3,6);
					  if($type==6){
					     $mrate=9;
                         $gcnt = CreatNewHero($uid,$cid,$npcid,6,1);
					     $mcon = ($cityname . $manname[$mrate]) . $gcnt;
					     sendSysInform(0,1,0,600,50000,1,14972979,$mcon);
					    }else{
						 $gcnt = CreatNewHero($uid,$cid,$npcid,$type,0);
						}
					  break;
					}
			    }
			  $con = ($cityname . $manname[$mrate]) . $gcnt;
			  sendReport($uid, 3, 18, $cid, $cid, $con);
			}
			if ($moraleadd || $npeople) {
                sql_query("update mem_city_resource set `morale`=GREATEST(0,`morale`-{$moraleadd}),`morale_stable`=GREATEST(0,LEAST(100-`tax`-`complaint`,100)),`wood`='{$nwood}',`rock`='{$nrock}',`iron`='{$niron}',`food`='{$nfood}',`gold`='{$ngold}',`people`=round({$npeople}) where cid='{$cid}'");
            }
		}
        if ($id) {
            sql_query("delete from sys_gather where `troopid` in ({$id})");
            sql_query("update sys_troops set `state`=1,`starttime`=unix_timestamp(),`endtime`=unix_timestamp()+`pathtime` where `id` in ({$id})");
        }
    }
function updateherorun(){//将领逃跑
    $captives = sql_fetch_rows('select * from sys_hero_captive where `captivetime`>=unix_timestamp()-86400');
    $nowtime = $_SERVER['REQUEST_TIME'];
    if ($captives) {
        foreach ($captives as $v) {
            $nohid[] = $v['hid'];
        }
    }
    $heros = sql_fetch_rows('select * from sys_city_hero where `loyalty`<50 and `uid`>1000 and not exists(select * from sys_user_state where `vacend`<=unix_timestamp() and `uid`=sys_city_hero.uid)');
    foreach ($heros as $v) {
        $hero[$v['hid']] = $v;
    }
    $comma = '';
    if ($hero) {
        foreach ($hero as $hid => $v) {
            if ($v['state'] == 5) {
                if ($nohid && in_array($hid, $nohid)) {
                    $lost = 0;
                } else {
                    $lost = 1;
                }
            } else {
                $rate = mt_rand(1, 600);
                if ($rate < $v['loyalty']) {
                    $lost = 1;
                } else {
                    $lost = 0;
                }
            }
            $cityname = getNamePosition($v['cid'], 1);
            if ($lost) {
                $command = $v['command_base'] + $v['level'];
                $affairs = $v['affairs_base'] + $v['affairs_add'];
                $bravery = $v['bravery_base'] + $v['bravery_add'];
                $wisdom = $v['wisdom_base'] + $v['wisdom_add'];
                $content = sprintf($GLOBALS['curse']['hero_lost1'], $cityname, $v['name'], $v['level'], $command, $affairs, $bravery, $wisdom, $v['loyalty']);
                throwHeroToField($v);
                updateCityHeroChange($v['uid'], $v['cid']);
                $losthid .= $comma;
                $losthid .= $hid;
                $comma = ',';
            } else {
                $command = ($v['command_base'] + $v['level']) + $v['command_add_on'];
                $affairs = ($v['affairs_base'] + $v['affairs_add']) + $v['affairs_add_on'];
                $bravery = ($v['bravery_base'] + $v['bravery_add']) + $v['bravery_add_on'];
                $wisdom = ($v['wisdom_base'] + $v['wisdom_add']) + $v['wisdom_add_on'];
                $content = sprintf($GLOBALS['curse']['hero_lost'], $cityname, $v['name'], $v['level'], $command, $affairs, $bravery, $wisdom, $v['loyalty']);
            }
            sendReport($v['uid'], '0', '16', $v['cid'], $v['cid'], $content);
        }
    }
    if ($losthid) {
        sql_query("delete from sys_hero_captive where `hid` in ({$losthid})");
    }
}
function addCityLamsters($cid, $soldiers, $add){//兵变
    $comma = '';
    foreach ($soldiers as $sid => $count) {
        $sql .= $comma;
        $sql .= "('{$cid}','{$sid}','{$count}')";
        $comma = ',';
        if ($add) {
            $fuhao = '+';
        } else {
            $fuhao = '-';
        }
    }
    $sql1 = "insert into mem_city_lamster (`cid`,`sid`,`count`) values {$sql} on duplicate key update `count`=GREATEST(0,`count` {$fuhao} values(count))";
    sql_query($sql1);
}
//============================================战斗报告
//========================科技、城防、招兵、资源
function UpdateUsersTechnic(){//科技更新
    $rows = sql_fetch_rows("select * from mem_technic_upgrading where state_endtime <= unix_timestamp()");
    if (!empty($rows)){
        foreach ($rows as $row) {
		    $id = $row['id'];
            $cid = $row['cid'];
            $tid = $row['tid'];
            $level = $row['level'];
			$uid = sql_fetch_one_cell("select uid from sys_city where `cid`='{$cid}'");
			sql_query("update sys_technic set `level` ='{$level}',`state`=0 where `id`='{$id}'");
            sql_query("delete from mem_technic_upgrading where `id`='{$id}'");
			checkUsersTechnic($uid);
            switch ($tid) {
            case 1:
                completeTask($uid, 46);
                break;
            case 2:
                completeTask($uid, 47);
                break;
            case 3:
                completeTask($uid, 48);
                break;
            case 4:
                completeTask($uid, 49);
                break;
            case 5:
                completeTask($uid, 50);
                break;
            case 6:
                completeTask($uid, 51);
                break;
            case 7:
                completeTask($uid, 52);
                break;
            case 8:
                completeTask($uid, 53);
                break;
            case 9:
                completeTask($uid, 54);
                break;
            case 10:
                completeTask($uid, 55);
                break;
            case 11:
                completeTask($uid, 56);
                break;
            case 12:
                completeTask($uid, 57);
                break;
            case 13:
                completeTask($uid, 58);
                break;
            case 14:
                completeTask($uid, 59);
                break;
            case 15:
                completeTask($uid, 60);
                break;
            case 16:
                completeTask($uid, 61);
                break;
            case 17:
                completeTask($uid, 62);
                break;
            case 18:
                completeTask($uid, 63);
                break;
            case 19:
                completeTask($uid, 64);
                break;
            case 20:
                completeTask($uid, 65);
                break;
            }
            $chiefhid = sql_fetch_one_cell("SELECT counsellorid FROM sys_city WHERE `cid`='{$cid}'");
            if ($chiefhid == 0) {
                $chiefhid = sql_fetch_one_cell("SELECT chiefhid FROM sys_city WHERE `cid`='{$cid}'");
            }
            if ($chiefhid > 0) {
                $value = sql_fetch_one_cell("select upgrade_time from cfg_technic_level where `level`='{$level}' and `tid`='{$tid}'");
                addHeroExp($chiefhid, ($value / 2) * GAME_SPEED_RATE);
            }
        }
    }
}
function UpdateUsersSoldier(){//军队更新
    $rows = sql_fetch_rows("select * from mem_city_draft  where state_endtime <= unix_timestamp()");
    if (!empty($rows)) {
        foreach ($rows as $row) {
            $id = $row['id'];
            $cid = $row['cid'];
            $sid = $row['sid'];
            $count = $row['count'];
            $xy = $row['xy'];
			$uid = sql_fetch_one_cell("select uid from sys_city where `cid`='{$cid}'");
			sql_query("Delete from sys_city_draftqueue where `id`='{$id}'");
            sql_query("Delete from mem_city_draft WHERE `id`='{$id}'");
            addCitySoldier($cid, $sid, $count);
            $nextid = sql_fetch_one_cell("select id from sys_city_draftqueue where `cid`='{$cid}' and `xy`='{$xy}' order by `queuetime` limit 1");
            if (!empty($nextid)) {
                sql_query("update sys_city_draftqueue set `state`=1,`state_starttime`=unix_timestamp() where `id`='{$nextid}'");
                sql_query("insert into mem_city_draft (select `id`,`cid`,`xy`,`sid`,`count`,`state_starttime`+`needtime` from sys_city_draftqueue where `id`='{$nextid}')");
            }
            $addprestige = $GLOBALS['soldier']['usepeople'][$sid] * $count;
            sql_query("update sys_user set `prestige`=`prestige`+'{$addprestige}',`warprestige`=`warprestige`+'{$addprestige}' where `uid`='{$uid}'");
            refreshFoodArmyUsers($cid);
            $chiefhid = sql_fetch_one_cell("SELECT `generalid` FROM sys_city WHERE `cid`='{$cid}'");
            if ($chiefhid == 0) {
                $chiefhid = sql_fetch_one_cell("SELECT `chiefhid` FROM sys_city WHERE `cid`='{$cid}'");
            }
            if ($chiefhid > 0) {
                addHeroExp($chiefhid, $addprestige * 10);
            }
        }
    }
}
function UpdateUsersDeinforce(){//城防更新
    $rows = sql_fetch_rows("SELECT * FROM mem_city_reinforce WHERE state_endtime <= unix_timestamp()");
    if (!empty($rows)) {
        foreach ($rows as $row) {
            $id = $row['id'];
            $cid = $row['cid'];
            $did = $row['did'];
            $count = $row['count'];
			$uid = sql_fetch_one_cell("select uid from sys_city where `cid`='{$cid}'");
			addCityDefence($cid, $did, $count);
            sql_query("Delete from sys_city_reinforcequeue where `id`='{$id}'");
            sql_query("Delete from mem_city_reinforce WHERE `id`='{$id}'");
            sql_query('Delete from sys_city_defence where `count`=0');
            $nextid = sql_fetch_one_cell("select id from sys_city_reinforcequeue where `cid`='{$cid}' order by `queuetime` limit 1");
            if (!empty($nextid)) {
                sql_query("update sys_city_reinforcequeue set `state`=1,`state_starttime`=unix_timestamp() where `id`='{$nextid}'");
                sql_query("insert into mem_city_reinforce (select `id`,`cid`,`did`,`count`,`state_starttime`+`needtime` from sys_city_reinforcequeue where `id`='{$nextid}')");
            }
            $chiefhid = sql_fetch_one_cell("SELECT `generalid` FROM sys_city WHERE `cid`='{$cid}'");
            if ($chiefhid == 0) {
                $chiefhid = sql_fetch_one_cell("SELECT `chiefhid` FROM sys_city WHERE `cid`='{$cid}'");
            }
            if ($chiefhid > 0) {
                addHeroExp($chiefhid, $count * 10);
            }
		} 
    }
}
//===============================排行更新
function HandleUsersRank(){
     sql_query("update mem_state set `value`=unix_timestamp() where state=10");
      //========玩家排名
     sql_query("delete from rank_user");
     $rankuses = sql_fetch_rows("select uid,name,nobility,prestige from sys_user where uid>1000 and prestige>5 and state<>3 order by nobility desc,prestige desc");
     $i=1;
     if($rankuses) foreach($rankuses as $Key=>$rankuse){
	         $nobility=sql_fetch_one_cell("select name from cfg_nobility where id='$rankuse[nobility]'");
             sql_query("insert into rank_user(`rank`,`uid`,`name`,`nobility`,`prestige`) values ($i,'$rankuse[uid]','$rankuse[name]','$nobility','$rankuse[prestige]')");
             sql_query("update sys_user set rank=$i where uid='$rankuse[uid]'");
			 $i++;
        }
     sql_query("update rank_user set rank_user.union=(select n.name from sys_union n,sys_user u where n.id=u.union_id and u.uid=rank_user.uid)");
     sql_query("update rank_user set city=(select count(uid) from sys_city where uid=rank_user.uid)");
     sql_query("update rank_user set people=(select sum(r.people) from mem_city_resource r,sys_city c where r.cid=c.cid and c.uid=rank_user.uid)");
     sql_query("update sys_user set rank=$i where rank=0 and uid>1000");
     //=======联盟
     sql_query('TRUNCATE TABLE `rank_union`');
     sql_query('insert into rank_union(`uid`,`name`,`leader`,`member`,`prestige`) select n.id,n.name,u.name,n.member,n.prestige from sys_union n,sys_user u where n.leader=u.uid order by n.prestige desc');
     sql_query('update rank_union set famouscity=(select count(c.type) from sys_city c,sys_user u where c.type>0 and rank_union.uid=u.union_id and u.uid=c.uid)');
     sql_query('TRUNCATE TABLE `sys_union_city`');
     sql_query('insert into sys_union_city(`unionid`,`count`) select uid,famouscity from rank_union where famouscity>0 order by uid');
     //=======将领
     sql_query('TRUNCATE TABLE `rank_hero`');
     sql_query('TRUNCATE TABLE `rank_hero_affairs`');
     sql_query('TRUNCATE TABLE `rank_hero_bravery`');
     sql_query('TRUNCATE TABLE `rank_hero_wisdom`');
     sql_query('TRUNCATE TABLE `rank_hero_command`');
     sql_query('insert into rank_hero(`hid`,`name`,`user`,`level`,`command`,`affairs`,`bravery`,`wisdom`) select hid,name,uid,level,command_base+command_add_on+level,affairs_base+affairs_add+affairs_add_on,bravery_base+bravery_add+bravery_add_on,wisdom_base+wisdom_add+wisdom_add_on from sys_city_hero where (npcid>0 or (npcid=0 and uid>1000)) and level>10 order by level desc,command_base+command_add_on desc');
     sql_query('update rank_hero set user=(select name from sys_user where uid=rank_hero.user)');
     sql_query('insert into rank_hero_bravery(`hid`,`name`,`user`,`level`,`affairs`,`bravery`,`wisdom`,`command`) select hid,name,user,level,affairs,bravery,wisdom,command from rank_hero order by bravery desc');
     sql_query('insert into rank_hero_wisdom(`hid`,`name`,`user`,`level`,`affairs`,`bravery`,`wisdom`,`command`) select hid,name,user,level,affairs,bravery,wisdom,command  from rank_hero order by wisdom desc');
     sql_query('insert into rank_hero_affairs(`hid`,`name`,`user`,`level`,`affairs`,`bravery`,`wisdom`,`command`) select hid,name,user,level,affairs,bravery,wisdom,command from rank_hero order by affairs desc');
     sql_query('insert into rank_hero_command(`hid`,`name`,`user`,`level`,`affairs`,`bravery`,`wisdom`,`command`) select hid,name,user,level,affairs,bravery,wisdom,command from rank_hero order by command desc');
     //========君主排名
	 sql_query('TRUNCATE TABLE `rank_user_level`');
	 sql_query("insert into rank_user_level(`uid`,`name`,`levelname`,`level`,`nobility`,`battle`,`prestige`,`hero_level`) 
	     select u.uid,u.name,e.name,s.level,u.nobility,h.bravery_add*10,u.prestige,h.level from rank_user u,cfg_user_level e,sys_city_hero h ,sys_user_level s where s.level=e.level and u.uid=s.uid and h.uid=s.uid and h.herotype=1000 order by s.level desc");
          
   
	 //===军事
	 sql_query("delete from rank_military_attack");
	 sql_query('TRUNCATE TABLE `rank_military`');
     sql_query('TRUNCATE TABLE `rank_military_defence`');
	 $rankuses = sql_fetch_rows("select uid,name,war_attack_prestige,war_defence_prestige,union_id from sys_user where uid>1000 and rank>0 and state in(0,1,2,4) order by war_attack_prestige desc,nobility desc");
     $i=1;
	 if($rankuses) foreach($rankuses as $Key=>$rankuse){
	     $rankunion=sql_fetch_one_cell("select name from sys_union where id='$rankuse[union_id]'");
	     $rankarmy=sql_fetch_one_cell("select sum(c.people_need*s.count) from sys_city_soldier s,cfg_soldier c,sys_city t where s.cid=t.cid and s.sid=c.sid and t.uid='$rankuse[uid]'");
	     sql_query("insert into rank_military_attack(`rank`,`name`,`unionname`,`attack`,`defence`,`army`,`uid`) values ($i,'$rankuse[name]','$rankunion','$rankuse[war_attack_prestige]','$rankuse[war_defence_prestige]','$rankarmy','$rankuse[uid]')");
         $i++;
	    }
     sql_query("insert into rank_military(`name`,`unionname`,`attack`,`defence`,`army`,`uid`) select name,unionname,attack,defence,army,uid from rank_military_attack order by army desc");
     sql_query("insert into rank_military_defence(`name`,`unionname`,`attack`,`defence`,`army`,`uid`) select name,unionname,attack,defence,army,uid from rank_military_attack order by defence desc");
     //=====战场
	 sql_query("delete from rank_battle_total");
     sql_query('TRUNCATE TABLE `rank_battle_day`');
     sql_query('TRUNCATE TABLE `rank_battle_week`');
	 $rankuses = sql_fetch_rows("select uid,name,honour,union_id from sys_user where honour>0 order by honour desc");
     $i=1;
	 if($rankuses) foreach($rankuses as $Key=>$rankuse){
	     $rankunion=sql_fetch_one_cell("select name from sys_union where id='$rankuse[union_id]'");
	     $day=sql_fetch_one_cell("select sum(honour) from log_battle_honour where finishtime>(unix_timestamp()-86400) and uid='$rankuse[uid]'");
	     $week=sql_fetch_one_cell("select sum(honour) from log_battle_honour where finishtime>(unix_timestamp()-604800) and uid='$rankuse[uid]'");
		 sql_query("insert into rank_battle_total(`rank`,`uid`,`unionname`,`name`,`total`,`day`,`week`) values($i,'$rankuse[uid]','$rankunion','$rankuse[name]','$rankuse[honour]','$day','$week') ");
         $i++;
	    }
     sql_query('insert into rank_battle_day(`uid`,`name`,`unionname`,`total`,`day`,`week`) select uid,name,unionname,total,day,week from rank_battle_total order by day desc');
     sql_query('insert into rank_battle_week(`uid`,`name`,`unionname`,`total`,`day`,`week`) select uid,name,unionname,total,day,week from rank_battle_total order by week desc');
     sql_query("delete from sys_login_announce");
	 $maxname=sql_fetch_one("select * from rank_user where rank=1");
	 $maxpos=sql_fetch_one_cell("select p.name from cfg_office_pos p,sys_user u where u.uid='$maxname[uid]' and u.officepos=p.id");
	 $maxnob=sql_fetch_one_cell("select nobility from rank_user where rank=1");
	 $maxattack=sql_fetch_one_cell("select name from rank_military_attack where rank=1 and attack>0");
	 $maxdefence=sql_fetch_one_cell("select name from rank_military_defence where rank=1 and defence>0");
	 sql_query("insert into sys_login_announce(`maxname`,`maxpos`,`maxnob`,`maxattack`,`maxdefence`,`id`) values ('$maxname[name]','$maxpos','$maxnob','$maxattack','$maxdefence',1) ");
    }
//=========新人王，联盟排行,战场奖励
function check_user_act(){
     $actstart = sql_fetch_one("select * from sys_user_act where id=1");
	 $atime=time();
     if($actstart['state']==1 && $actstart['con']==0){
	     if($actstart['time']<($atime-86400*7)){//开服七天
		      sql_query("update sys_user_act set state=2,con=1,time=$atime where id=1");
			  GetUserNewKing();//发新人王奖
		    }
	    }
     if($actstart['state']==2 && $actstart['con']==1){
	     if($actstart['time']<($atime-86400*3)){//开服十天
		      sql_query("update sys_user_act set state=3,con=3,time=$atime where id=1");
			  GetUserUnionKing();//发联盟排行奖
		    }
	    }
	}
function GetUserNewKing(){//发新人王奖,第一名，名将1，龙渊普通箱子7个，第二名，名将 1 龙渊普通箱子5个，第三名 名将1龙渊普通箱子3个！
     $useranks = sql_fetch_rows("select * from rank_user_level where rank<4");
	 if(empty($useranks)) return null;
	 $getheroid[1]=88;//王双,橙色
	 $getheroid[2]=291;//纪灵，紫色
	 $getheroid[3]=632;//杨任，紫色
     foreach($useranks as $Key=>$userank){
	      $uid=$userank['uid'];
		  $type=$userank['rank'];
		  $npcid=$getheroid[$type];
	      $cid = sql_fetch_one_cell("select cid from sys_city where type=5 and uid='$uid'");
		  $msg='恭喜玩家:'.$userank['name'].'获得新人王排行第'.$type.'名';
		  sendSysInform(0,1,0,600,50000,1,14972979,$msg);
	      CreatNewHero($uid,$cid,$npcid,$type,1);//发将
		  $gid=8887;//龙渊装备箱子
		  $cnt=9-$type*2;
		  addGoods($uid,$gid,$cnt,3);
	    }
    }
function GetUserUnionKing(){//发联盟排行奖,第一名成员，联盟礼包5个，第二名成员联盟礼包3个，第三名成员联盟礼包1!
     $useranks = sql_fetch_rows("select * from rank_union where rank<4");
     if(empty($useranks)) return null;
	 foreach($useranks as $Key=>$userank){  
	      $union_id = $userank['uid']; 
		  $rank=$userank['rank']; 
		  $gid=8894;//联盟礼包自己做
		  $cnt=7-2*$rank;
		  $usersids = sql_fetch_rows("select uid,name from sys_user where union_id='$union_id'");
		  if(empty($usersids)) continue;
		  $msg='恭喜联盟:'.$userank['name'].'获得联盟排行第'.$rank.'名';
		  sendSysInform(0,1,0,600,50000,1,14972979,$msg);
		  foreach($usersids as $Key=>$usersid){//对联盟所有成员发放奖励
		     $uid=$usersid['uid'];
		     addGoods($uid,$gid,$cnt,3);
		    }
		}
    }
function GetUserBattleKing(){//发放战场前10的奖励,第一名，战场礼包3+200000荣誉+15汉室勋章，第二名战场礼包2+150000荣誉+10汉室勋章，第三名战场礼包1+100000荣誉+5汉室勋章，
      $useranks = sql_fetch_rows("select * from rank_battle_week where rank<11 and week>0");
	  if(empty($useranks)) return null;
	  foreach($useranks as $Key=>$userank){  
	      $uid = $userank['uid']; 
		  $rank=$userank['rank'];
		  $honour =110000-$rank*10000; 
		  if($rank<4){
		     $honour=250000-$rank*50000;
			 $cnt=4-$rank;
		     addGoods($uid,8893,$cnt,3);//战场礼包自己做
			 $cnt=20-$rank*5;
			 addThings($uid,30000,$cnt,3);//汉室勋章
			 $msg='恭喜玩家:'.$userank['name'].'获得战场排行第'.$rank.'名!';
		     sendSysInform(0,1,0,600,50000,1,14972979,$msg);
			}
		  sql_query("update sys_user set honour=honour+'$honour' where uid='$uid'");
		}
    }
function CreatNewHero($uid,$cid,$npcid,$type,$changle){
	 $hero = sql_fetch_one("select * from cfg_npc_hero where npcid=$npcid limit 1");
	 $heroCommand=sql_fetch_one_cell("select command_base from sys_city_hero where npcid=$npcid limit 1");
	 if ($npcid <= 1032 && $npcid >= 1027) {
			$heroCommand = rand(80,100);
		} elseif ($npcid == 1033) {
			$heroCommand = 108;
		}
	 $affairsRate = 0;
	 $braveryRate = 0;
	 $wisdomRate  = 0;
	 $commandRate = 0;
	 switch($type){
	      case 1:{
		     $affairsRate = mt_rand(98,100);
		     $braveryRate = mt_rand(100,101);
		     $wisdomRate  = mt_rand(99,101);
		     $commandRate = 50;
			 break;
			}
		  case 2 or 3:{
		     $affairsRate = mt_rand(80,85);
		     $braveryRate = mt_rand(80,85);
		     $wisdomRate  = mt_rand(80,85);
		     $commandRate = 30;
			 break;
			}
		  case 4:{
		     $affairsRate = mt_rand(70,75);
		     $braveryRate = mt_rand(70,75);
		     $wisdomRate  = mt_rand(70,75);
		     $commandRate = 15;
			 break;
		    }
		  case 5:{
		     $affairsRate = mt_rand(60,65);
		     $braveryRate = mt_rand(60,65);
		     $wisdomRate  = mt_rand(60,65);
		     $commandRate = 5;
			 break;
		    }
	     case 6:{
		     $affairsRate = 100;
		     $braveryRate = 100;
		     $wisdomRate  = 100;
		     $commandRate = 100;
			 break;
		    }
		}
	    $affairs = floor($hero['affairs_base']*($affairsRate/100));
	    $bravery = floor($hero['bravery_base']*($braveryRate/100));
	    $wisdom = floor($hero['wisdom_base']*($wisdomRate/100));
	    $command = floor($heroCommand*($commandRate/100));
		echo $hero['affairs_base'];
		$heroName=$hero['name'];
		if($changle==1){//不能更改将领名字
	         $heroType = 27250;
			 if($type==6){
			     $affairs = $hero['affairs_base'];
	             $bravery = $hero['bravery_base'];
	             $wisdom =  $hero['wisdom_base'];
	             $command = $heroCommand;
				}
		    }
		 else {
		     $heroType = 20250+$type*1000;//可以更改将领名字
			 $heroNames = sql_fetch_rows("select name from sys_recruit_hero where sex='$hero[sex]'");
			 $hnumcnt = count($heroNames);
			 $hnumok = mt_rand(0,($hnumcnt-1));
			 $i=0;
			 foreach ($heroNames as $row){
			     if($i==$hnumok)
			       {$heroName = $row['name'];break;}
				 $i++;
			    }
		    }
	    $sql = "insert into sys_city_hero (`uid`,`name`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`affairs_base`,`bravery_base`,`wisdom_base`,`command_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`loyalty`,`herotype`) values 
									  ('$uid','$heroName','$hero[sex]','$hero[face]','$cid','0','1','0','$affairs','$bravery','$wisdom','$command','0','0','0','100','$heroType')";
	    $forcemax=100+floor($bravery/3);
	    $energymax=100+floor($wisdom /3);
	    $hid = sql_insert($sql);
	    sql_query("insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`) values ('$hid',$forcemax,$forcemax,$energymax,$energymax)");
	    regenerateHeroAttri($uid,$hid);
	    updateCityHeroChange($uid,$cid);
        return 	$heroName;	
	}
//==========市场
function HandleTrade(){
     $rows = sql_fetch_rows("SELECT * FROM sys_city_trade WHERE endtime>1 and  endtime <= unix_timestamp()");
     if ($rows) {
         foreach ($rows as $row) {
             $id = $row['id'];
             $cid = $row['cid'];
             $buycid = $row['buycid'];
             $gold = $row['gold'];
             $restype = $row['restype'];
             $count = $row['count'];
             sql_query("update mem_city_resource set `gold`=`gold`+'{$gold}' where `cid`='{$cid}'");
             switch ($restype) {
                 case 0:
                  sql_query("update mem_city_resource set `food`=`food`+'{$count}' where `cid`='{$buycid}'");
                  $restype++;
                  break;
                 case 1:
                  sql_query("update mem_city_resource set `wood`=`wood`+'{$count}' where `cid`='{$buycid}'");
                  $restype++;
                  break;
                 case 2:
                  sql_query("update mem_city_resource set `rock`=`rock`+'{$count}' where `cid`='{$buycid}'");
                  $restype++;
                  break;
                 case 3:
                  sql_query("update mem_city_resource set `iron`=`iron`+'{$count}' where `cid`='{$buycid}'");
                  $restype++;
                  break;
                 case 4:
                  sql_query("update mem_city_resource set `gold`=`gold`+'{$count}' where `cid`='{$buycid}'");
                  $restype = 0;
                  break;
                }
             sql_query("Delete from mem_city_trade where `id`={$id}");
             sql_query("Delete from sys_city_trade where `id`={$id}");
             $uid = sql_fetch_one_cell("select uid from sys_city where `cid`='{$cid}'");
             $youruid = sql_fetch_one_cell("select uid from sys_city where `cid`='{$buycid}'");
             completeTask($uid, 25);
             completeTask($youruid, 26);
             $mycityname = getCityNamePosition($cid);
             $buycityname = getCityNamePosition($buycid);
             $resourcename = $GLOBALS['report']['resources'][$restype];
             $content = ((('与' . $buycityname) . '的买卖已达成，商队已经返回') . $mycityname) . '。';
             $content .= (('</br>出售：' . $resourcename) . ' ') . $count;
             $content .= '</br>获得：黄金 ' . $gold;
             sendReport($uid, '0', '15', $cid, $buycid, $content);
             $content = ((('与' . $mycityname) . '的买卖已达成，商队已经返回') . $buycityname) . '。';
             $content .= (('</br>购买：' . $resourcename) . ' ') . $count;
             $content .= '</br>支付：黄金 ' . $gold;
             sendReport($youruid, '0', '15', $cid, $buycid, $content);
            }
        }
    }
function HandleAutoTrans(){
     $rows = sql_fetch_rows('select * from mem_city_autotrans where end_time-60<=unix_timestamp()');
     if ($rows) {
         foreach ($rows as $v) {
             if (!$v['trans_type']) {
                 sql_query("delete from mem_city_autotrans where `id`='{$v['id']}'");
                } else {
                  sql_query("update mem_city_autotrans set `start_time`=`start_time`+86400,`end_time`=`end_time`+86400 where `id`='{$v['id']}'");
                }
             switch (${$v['res_type']}) {
                 case 0:
                  if (sql_fetch_one_cell("select food from mem_city_resource where `cid`='{$v['fromcid']}'") < $v['count']) {
                     return;
                    }
                  sql_query("update mem_city_resource set `food`=GREATEST(0,`food`-'{$v['count']}') where `cid`='{$v['fromcid']}'");
                  break;
                 case 1:
                  if (sql_fetch_one_cell("select wood from mem_city_resource where `cid`='{$v['fromcid']}'") < $v['count']) {
                     return;
                    }
                  sql_query("update mem_city_resource set `wood`=GREATEST(0,`wood`-'{$v['count']}') where `cid`='{$v['fromcid']}'");
                  break;
                 case 2:
                  if (sql_fetch_one_cell("select rock from mem_city_resource where `cid`='{$v['fromcid']}'") < $v['count']) {
                     return;
                    }
                  sql_query("update mem_city_resource set `rock`=GREATEST(0,`rock`-'{$v['count']}') where `cid`='{$v['fromcid']}'");
                  break;
                 case 3:
                  if (sql_fetch_one_cell("select iron from mem_city_resource where `cid`='{$v['fromcid']}'") < $v['count']) {
                      return;
                    }
                  sql_query("update mem_city_resource set `iron`=GREATEST(0,`iron`-'{$v['count']}') where `cid`='{$v['fromcid']}'");
                  break;
                 case 4:
                  if (sql_fetch_one_cell("select gold from mem_city_resource where `cid`='{$v['fromcid']}'") < $v['count']) {
                     return;
                    }
                  sql_query("update mem_city_resource set `gold`=GREATEST(0,`gold`-'{$v['count']}') where `cid`='{$v['fromcid']}'");
                  break;
                }
             sql_query("insert into sys_city_trade (cid,state,restype,count,price,gold,distance,endtime,buycid,`unionid`,limittime) values ('{$v['fromcid']}','1','{$v['res_type']}','{$v['count']}','0','0','{$v['distance']}','{$v['end_time']}','{$v['tocid']}','0','0')");
            }
        }
    }
function gettargetarmysoldiers($soldiers){
     $soldier = explode(',',$soldiers);
     if(empty($soldier)) return 0;	 
	 $num = 0;
	 for($i = 0; $i < $soldier['0']; $i++){
	     $m = 2*$i+1;
		 $sid = $soldier[$m];
		 $neednum = $soldier[$m+1];
		 $peoples = sql_fetch_one_cell("select people_need from cfg_soldier where sid='$sid'");
		 $num = $num + ($neednum*$peoples);
		}	
      return $num;
	}
function HandleNextTask(){
     $huangjinvalue=sql_fetch_one_cell("select value from mem_state where state=5");
	 if($huangjinvalue==0){
	     //黄巾之乱日常任务id
		 //sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and id in (6001,6101,6102,6201,6202,6211,6212)) on duplicate key update state=0");
		 //sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and `group` in (1000,2000,3000,4000)) on duplicate key update state=0");
		 //黄巾之乱史诗任务id
		 //sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and `group` in(10100,10200,10300,10400,11000,12000,13000,14000)) on duplicate key update state=0");
	     //黄巾之乱史诗结束后的任务id
		 //sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and id in (10501,10619,10620,10621,10622,10623,155005,155006)) on duplicate key update state=0");
	     //删除其它史诗任务id
		 //sql_query("delete from sys_user_task where tid in (select id from cfg_task where `group` in (10700,15000,15100,15200,15300,15400,15500,15600,112001,112002,112003,112014,112015,112016,112017,112018,112019,112020,112021,112022))");
		}else if($huangjinvalue==1){//黄巾之乱史诗结束了
	      $dongzhuovalue=sql_fetch_one_cell("select value from mem_state where state=7");
	      switch($dongzhuovalue){
		     case 0:{//设置黄巾结束后的任务
			       $shichangshistate = sql_fetch_one_cell("select value from mem_state where state=119"); 
				   if($shichangshistate==0){
				      sql_query("update mem_state set  value=unix_timestamp() where state=120");//保存结束时间点
					  sql_query("update mem_state set  value=1 where state=119");//判断标志设成不是第一次结束了
				      sql_query("delete from sys_user_task  where tid in(select id from cfg_task where  `group` in(1000,2000,3000,4000) )");//删除黄巾之乱的日常任务
				      sql_query("delete from sys_user_task  where tid between 11000 and 15000 ");//删除黄巾之乱史阶段的史诗任务
					  //插入黄巾之乱结束之后的讨伐有功、收复失地、擒拿酋首。
					  sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and id in (10501,10619,10620,10621,10622,10623,155005,155006)) on duplicate key update state=0");
	                }else if($shichangshistate==1){//判断是否开启十常侍史诗
					  $shichangshitime = sql_fetch_one_cell("select value from mem_state where state=120"); 
					  $scs_starttime = time();
					  if($scs_starttime>($shichangshitime+43200)) {//43200设置史诗结束后12小时后开启新的史诗任务
					      sql_query("update mem_state set  value=1 where state=7");//开启十常侍任务
					      sql_query("update mem_state set  value=0 where state=119");//判断标志变成0
						  sql_query("update mem_state set  value=unix_timestamp() where state=6");//设置系统自动捐助的开始时间
					    
						}
					}
				    break;
				}  
			 case 1:{//十常侍
			      $shichangshistate = sql_fetch_one_cell("select value from mem_state where state=119"); 
				  if($shichangshistate==0){//设置十常侍史诗任务
				      sql_query("update mem_state set value=1 where state=119");//判断标志设成不是第一次结束了
				      //删除黄巾之乱的所有任务
					  sql_query("delete from sys_user_task  where tid in(select id from cfg_task where  `group` in(1000,2000,3000,4000,6000,10100,10200,10300,10400,10500,10600,11000,12000,13000,14000,15600) )");
				      //在刷给用户刷出十常侍的日常任务后，把各个组的第一个设置用户可见
					  sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,1 from sys_user_task, cfg_task where tid=243 and state=1 and `group` in(112014,112015,112016,112017))  on duplicate key update state=1"); 
			          sql_query("update sys_user_task set state=0 where  tid in(420494,420498,420503,420512)"); 
					  sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and id in (6301,6302)) on duplicate key update state=0");
					  //插入十常侍史诗任务
		     		  sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and `group` in(10700,15000,15100,15200,15300,15400,15500)) on duplicate key update state=0");
                   	  //将普通城设置成十常的城池
					  sql_query("update sys_city set uid = 896 where uid = 895");
					}else if($shichangshistate==1){//判断十党侍任务是否结束
					  $shitasknums = sql_fetch_rows("select tid from dongzhuo_progress where curvalue>=(maxvalue/2) and tid>15000 and tid <15005");
					  $shitasknumes = sql_fetch_rows("select tid from dongzhuo_progress where curvalue>=maxvalue and tid>15000 and tid <15005");
                      $shitasknum = count($shitasknums);//每个任务完成50%及以上开启
					  $shitasknume = count($shitasknumes);//完成所有任务的一半
					  if($shitasknum==4 || $shitasknume==2){
					      sql_query("update mem_state set  value=unix_timestamp() where state=120");//保存结束时间点
					      sql_query("update mem_state set  value=2 where state=119");
						  $title=  $GLOBALS['dongzuojj']["mailtitle"];
	                      $content= $GLOBALS['dongzuojj']["mailcontent"];
	                      sendAllSysMail($title,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$content);
	                      sendSysInform(0,1,0,300,1800,1,49151,$content);
						  //十常侍结束后给的任务
						  sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and `group` in(112003)) on duplicate key update state=0");
                   		}
					}else if($shichangshistate==2){
					  $shichangshitime = sql_fetch_one_cell("select value from mem_state where state=120"); 
					  $scs_starttime = time();
					  if($scs_starttime>($shichangshitime+43200)) {//43200设置史诗结束后12小时后开启新的史诗任务
					      sql_query("update mem_state set  value=2 where state=7");//开启讨伐董卓任务
					      sql_query("update mem_state set  value=0 where state=119");//判断标志变成0
						  sql_query("update mem_state set  value=unix_timestamp() where state=6");//设置系统自动捐助的开始时间
					    }
					}
			       break;
				}  
			 case 2:{//董卓
			       $shichangshistate = sql_fetch_one_cell("select value from mem_state where state=119"); 
				   if($shichangshistate==0){//设置讨伐董卓史诗任务
				      sql_query("update mem_state set  value=1 where state=119");//判断标志设成不是第一次结束了
					  //删除十常侍的日常任务
				      sql_query("delete from sys_user_task  where tid in(select id from cfg_task where  `group` in(6000,112014,112015,112016,112017) )");
				      //在刷给用户刷出讨伐董卓的日常任务后，把各个组的第一个设置用户可见
				      sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,1 from sys_user_task, cfg_task where tid=243 and state=1 and `group` in(112018,112019,112020,112021))  on duplicate key update state=1"); 
			          sql_query("update sys_user_task set state=0 where tid in(420464,420468,420473,420482)");
					  //插入第二阶段的新加史诗任务讨伐有功, 慷慨资助,勤王之师,奇珍异宝,济寒赈贫
					  sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and id in (6401,6402,103005,103006,103007,103008,103009,420521,420522,40523,420524,420525)) on duplicate key update state=0");
					  //普通城中所有旗号为“宦”的城池和所有没有旗号的城池变成旗号为“董”的城池
	                  sql_query("update sys_city set uid = 659 where uid = 896");
	                  ////将汉灵帝的城池改为十常侍的
	                  sql_query("update sys_city set uid = 659 where uid=710 and type<=1");
		              sql_query("update sys_city_soldier a, sys_city b set sid = sid + 5 where a.cid = b.cid and count > 0 and b.uid = 659 and sid between 23 and 27");
	                  sql_query("update sys_city_hero set uid = 659 where uid = 896 and cid in (select cid from sys_city where uid = 659)");
	                }else if($shichangshistate==1){//判断讨伐董卓任务结束没有
					  $shitasknumes = sql_fetch_rows("select tid from dongzhuo_progress where curvalue>=maxvalue");
                      $shitasknume = count($shitasknumes);//完成所有任务
					  if($shitasknume>=46){
					      sql_query("update mem_state set  value=unix_timestamp() where state=120");//保存结束时间点
					      sql_query("update mem_state set  value=2 where state=119");//设置结束标志
						  $title=  $GLOBALS['taodong']["mailtitle"];
	                      $content= $GLOBALS['taodong']["mailcontent"];
	                      sendAllSysMail($title,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$content);
	                      sendSysInform(0,1,0,300,1800,1,49151,$content);
						}
					}else if($shichangshistate==2){
					  $shichangshitime = sql_fetch_one_cell("select value from mem_state where state=120"); 
					  $scs_starttime = time();
					  if($scs_starttime>($shichangshitime+43200)) {//43200设置史诗结束后12小时后开启新的史诗任务
					      sql_query("update mem_state set  value=3 where state=7");//开启备战洛阳任务
					      sql_query("update mem_state set  value=0 where state=119");//判断标志变成0
						  sql_query("update mem_state set  value=unix_timestamp() where state=6");//设置系统自动捐助的开始时间
					    }
					}
			       break;
				}  
			 case 3:{
			      $shichangshistate = sql_fetch_one_cell("select value from mem_state where state=119"); 
				  if($shichangshistate==0){//设置备战洛阳任务
				      sql_query("update mem_state set  value=1 where state=119");//判断标志设成不是第一次结束了
					  sql_query("update mem_state set  value=unix_timestamp() where state=120");//保存结束时间点
				      //把所有董卓的任务删除
					  sql_query("delete from sys_user_task where tid in (select id from cfg_task where `group` in (15000,15100,15200,15300,15400,15500,15600))");
			          //保留讨伐董卓日常任务
			          sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0  from sys_user_task, cfg_task where tid=243 and state=1 and `group` in (10700)) on duplicate key update state=0");//如果没有经过董卓第二阶段，这个论功行赏任务组就没有，但是实际是需要的
			          //刷出日常任务后把各个组的第一个任务设置为可见
			          sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,1 from sys_user_task, cfg_task where tid=243 and state=1 and id>=420464 and id<=420490)  on duplicate key update state=1");
			          sql_query("update sys_user_task set state=0 where tid in(420464,420468,420473,420482)");
				      sql_query("update mem_state set  value=1 where state=8");//打开备战洛阳任务
					  sql_query("update mem_state set  value=0 where state=119");//判断标志变成0
					  sql_query("update mem_state set  value=unix_timestamp() where state=6");//设置系统自动捐助的开始时间
					  //插入洛阳阶段史诗任务
					  sql_query("insert into sys_user_task (uid,tid,state) (select uid,id,0 from sys_user_task, cfg_task where tid=243 and state=1 and id >=112001 and id <=112004) on duplicate key update state=0");
			   		  $title=  $GLOBALS['beizhanluoyang']["mailtitle"];
	                  $content= $GLOBALS['beizhanluoyang']["mailcontent"];
	                  sendAllSysMail($title,"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$content);
	                  sendSysInform(0,1,0,300,1800,1,49151,$content);
					}
			      break;;
				}  
		    }
	    }
    }
function HandleTaskAutoDonate(){//自动增加史诗任务
      $huangjinvalue=sql_fetch_one_cell("select value from mem_state where state=5");
      if($huangjinvalue==0){
	      sql_query("update huangjin_progress set  curvalue=curvalue+(maxvalue*0.003)");//增加0.3%
		  sql_query("update huangjin_progress set  curvalue=maxvalue where curvalue>maxvalue");//防止上限超100%
	    }else{
		  $dongzhuovalue=sql_fetch_one_cell("select value from mem_state where state=7");
		  if($dongzhuovalue==1 || $dongzhuovalue==2){
		  	  sql_query("update dongzhuo_progress set  curvalue=curvalue+(maxvalue*0.003) ");
              sql_query("update dongzhuo_progress set  curvalue=maxvalue where curvalue>maxvalue");	//防止上限超100%		  
			}
		}	  
    }
function HandleLuoYangBegin(){//洛阳战场战斗处理
       $lystarttime = sql_fetch_one_cell("select value from mem_state where state=2001");
	   $lystates = sql_fetch_one_cell("select value from mem_state where state=2000");
       if($lystarttime>0 && $lystates==1){
	      $endtime=time();
		  if($endtime-$lystarttime>7200){//战场返回处理
		      sql_query("insert into sys_troops(uid,cid,hid,targetcid,task,state,starttime,pathtime,endtime,soldiers) 
				 (select uid,startcid,hid,215265,1,1,unix_timestamp(),35,unix_timestamp()+35,soldiers from sys_luoyang_troops where uid>1000 ) on duplicate key update state=1");
		      sql_query("update mem_state set  value=0 where state=2000");
			  sql_query("update mem_state set  value=0 where state=2001");
		      sql_query("delete from sys_luoyang_troops");
			}else{//战场派遣处理
			  $gotocid=mt_rand(1,3);//随机派往三个城门中的一个
	          sql_query("update sys_luoyang_troops set cid='$gotocid',targetcid='$gotocid',starttime=unix_timestamp(),endtime=unix_timestamp()+5 where cid>15 and uid>1000");
		    }
		}
    }
function LouYangTaskStart($day,$hour){//洛阳战场设置,整点检测一次
      $dongzhuovalue=sql_fetch_one_cell("select value from mem_state where state=7");
      if($dongzhuovalue==3){//只有在讨伐董卓阶段任务完成后才检测
	      if($day<24 && $hour==14){//$day 用来控制天，$hour用来控制时 每天下午14点开放 ，要使这生效，还要修改LuoyangFunc.php 对应的时间
		      sql_query("update mem_state set  value=1 where state=2000");
		      sql_query("update mem_state set  value=unix_timestamp() where state=2001");
			  LouYangTroopTaskStart();//设置洛阳各路守军
		      $content='讨董联盟已准备就绪，各路英雄可以开始攻打洛阳，成就不朽霸业了！';
	          sendSysInform(0,1,0,300,1800,1,49151,$content);
			} 
		}
    }
function LouYangTroopTaskStart(){//洛阳战场守军生成
     $louyangtroops = sql_fetch_rows("select * from cfg_luoyang_troop");//取出城守与兵力相关的量
     $i=1;
     foreach($louyangtroops as $louyangtroop){//添加洛阳守军id
         $npcValue = $louyangtroop['npcValue'];
	     $soldierType = $louyangtroop['sids'];
	     $cid= $louyangtroop['cid'];
	     $hid= $louyangtroop['hid'];
         $soldiers = LouYangCreateSoldier($npcValue,$soldierType,3);//生成城内军队；第三个参数越大生成的城内军队越多
	     sql_query("insert ignore sys_luoyang_troops (`id`,`uid`,`cid`,`hid`,`targetcid`,`state`,`starttime`,`pathtime`,`endtime`,`soldiers`,`battleid`,`startcid`,`unionid`,`lastcc`)
	       values ('$i','659','$cid','$hid','$cid','0','0','0','0','$soldiers','0','0','0','0') on duplicate key update `soldiers`='$soldiers'");
	 	 $i++;
	 	}
	}
function LouYangCreateSoldier($npcValue,$soldiers,$level){//洛阳兵种生成函数
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
    }
?>