<?php
function HandleBattle(){       
	 $battles = sql_fetch_rows("select * from mem_battle where nexttime<=unix_timestamp()");
	 if(!empty($battles)){
		 foreach ($battles as $key => $battle) {
			  if(sql_fetch_one_cell("select state from sys_battle where id='$battle[id]'")==1){
				 sql_query("delete from mem_battle where id='$battle[id]'");
				}else{
				  updateBattle($battle['id']);
				}
			}       
		}
	}
function updateBattle($battleid){
	  if(!isset($_SESSION['battle'][$battleid])){
		 $_SESSION['battle'][$battleid]['mem'] = sql_fetch_one("select * from mem_battle where id='$battleid'");
		 $_SESSION['battle'][$battleid]['sys'] = sql_fetch_one("select * from sys_battle where id='$battleid'");
		}
	  $fieldrange = $_SESSION['battle'][$battleid]['mem']['fieldrange'];//战场距离  
	  $round = sql_fetch_one_cell("select round from mem_battle where id='$battleid'");//取得回合
	  return_new_place($battleid,$round);
	}
function return_new_place($battleid,$round){
	  if(!isset($_SESSION['battle'][$battleid])){
		 $_SESSION['battle'][$battleid]['mem'] = sql_fetch_one("select * from mem_battle where id='$battleid'");
		 $_SESSION['battle'][$battleid]['sys'] = sql_fetch_one("select * from sys_battle where id='$battleid'");        
		}
	  $minfo = $_SESSION['battle'][$battleid]['mem'];
	  $sinfo = $_SESSION['battle'][$battleid]['sys'];
	  $attackpos = $minfo['attackpos'];//攻击方军队位置
	  $resistpos = $minfo['resistpos'];//防守放军队位置
	  $fieldrange = $minfo['fieldrange'];//战场距离
	  $attackuid = $sinfo['attackuid'];//进攻方uid
	  $resistuid = $sinfo['resistuid'];//防守方uid
	  $attackhid = $minfo['attackhid'];//进攻方将领
	  $resisthid = $minfo['resisthid'];//防守方将领
	  $attackstartcid = $minfo['attackcid'];
	  $resiststartcid = $minfo['resistcid'];
	  if($minfo['type']>3){//战场战加科技
		 $attackstartcid = $minfo['attackstartcid'];
		 $resiststartcid = $minfo['resiststartcid'];
		 if($minfo['type']==5) $resiststartcid = 215265;
		}
	  if(!isset($_SESSION['battle'][$battleid]['fightsoldier'])){//参战军队设置,注意战斗一回合后更新session//检测变量是否设置
		  // 满统:基础速度*（1+科技加成+马速）—1=行军距离;超统：基础速度*（1+科技加成）+基础速度*（马速/*满统兵力/总兵力）
		  //==========攻击方
		  $aheroinfo = getUsersHeroBattleAdd($attackhid,$attackstartcid,1);//将领加成数据
	      $attacksoldiers = $minfo['attacksoldiers'];//进攻方军队,
	      $attackCount = getsoldierCounts($attacksoldiers);//进攻方兵力总数
	      $attacksoldiersArray = getsoldierarray($attacksoldiers,$attackpos);//生成进攻方军队位置
	      $_SESSION['soldierInfo'] = sql_fetch_rows("select * from cfg_soldier order by `sid`");//二维数组 key+1做sid使用
	      $commandSoldiers = ($aheroinfo['command'] * 100) /$attackCount;//攻击方满统兵力
	      if($commandSoldiers>1) $commandSoldiers=1;
	      foreach ((array)$attacksoldiersArray as $key => $value) {
		     if($value['ratio']) $attacksoldiersArray[$key] =$value;
		     $attacksoldiersArray[$key]['type'] = 1;//步兵
		     $sid = $value['sid'];
		     $a_sidtype = $_SESSION['soldierInfo'][$sid-1]['type'];//转化成对应的兵种
		     if($a_sidtype!=7 && $a_sidtype!=8 && $a_sidtype!=45 && $a_sidtype!=48){//步兵速度加成                              名将额外速度加成
			     $attacksoldiersArray[$key]['speed'] = $_SESSION['soldierInfo'][$sid-1]['speed']*(1+$aheroinfo['xingjunspeed']+ $aheroinfo['herospeed']*$commandSoldiers/100);
			    }else{//骑兵速度加成
			      $attacksoldiersArray[$key]['speed'] = $_SESSION['soldierInfo'][$sid-1]['speed']*(1+$aheroinfo['jiayuspeed'] + $aheroinfo['herospeed']*$commandSoldiers/100);
			    }
		     if($sid!=6 && $sid!=10 && $sid!=12 && $sid!=49 && $sid!=46){//射程加成                      名将额外抛射加成                    
			     $attacksoldiersArray[$key]['gongjifanwei'] = $_SESSION['soldierInfo'][$sid-1]['range']*(1+$aheroinfo['heroshoot']*$commandSoldiers/100);  
			    }else{
			     $attacksoldiersArray[$key]['gongjifanwei'] = $_SESSION['soldierInfo'][$sid-1]['range']*(1+$aheroinfo['shoot']) + ($aheroinfo['heroshoot']*$commandSoldiers); 
			    }
		     $attacksoldiersArray[$key]['hp'] = $_SESSION['soldierInfo'][$sid-1]['hp']*(1+$aheroinfo['blood']*$commandSoldiers)+$aheroinfo['heroblood']*$commandSoldiers;
		     $attacksoldiersArray[$key]['ap'] = $_SESSION['soldierInfo'][$sid-1]['ap']*((1+$aheroinfo['gongji'])+$aheroinfo['attack']*$commandSoldiers/100) + $aheroinfo['heroattack']*$commandSoldiers;//攻击加成
		     $attacksoldiersArray[$key]['dp'] = $_SESSION['soldierInfo'][$sid-1]['dp']*((1+$aheroinfo['fangyu'])+$aheroinfo['defence']*$commandSoldiers/100) + $aheroinfo['herodefence']*$commandSoldiers;//防御加成
		     $attacksoldiersArray[$key]['stype'] = $a_sidtype;
		    }
	     //========
	     $resistsoldiers = $minfo['resistsoldiers'];//防守方军队
	     $resistCount = getsoldierCounts($resistsoldiers);//防守方兵力总数
	     $resistsoldiersArray = getsoldierarray($resistsoldiers,$resistpos);
	     $rheroinfo = getUsersHeroBattleAdd($resisthid,$resiststartcid,$minfo['type']);
	     $commandSoldiers = ($rheroinfo['command'] * 100) /$resistCount;//防守方满统兵力
	     if($commandSoldiers>1) $commandSoldiers=1;
	     if($resistuid<1000) $commandSoldiers=1;
	     foreach ((array)$resistsoldiersArray as $key => $value) {
		     if($value['ratio']) $resistsoldiersArray[$key] =$value;
		     $resistsoldiersArray[$key]['type'] = 1;//步兵
		     $sid = $value['sid'];
		     $a_sidtype = $_SESSION['soldierInfo'][$sid-1]['type'];//转化成对应的兵种
		     if($a_sidtype!=7 && $a_sidtype!=8 && $a_sidtype!=45 && $a_sidtype!=48){
			     $resistsoldiersArray[$key]['speed'] = $_SESSION['soldierInfo'][$sid-1]['speed']*(1+$rheroinfo['xingjunspeed'] + $rheroinfo['herospeed']*$commandSoldiers/100);
			    }else{//器械            
			     $resistsoldiersArray[$key]['speed'] = $_SESSION['soldierInfo'][$sid-1]['speed']*(1+$rheroinfo['jiayuspeed']+ $rheroinfo['herospeed']*$commandSoldiers/100);
			    }
		     if($sid!=6 && $sid!=10 && $sid!=12 && $sid!=49 && $sid!=46){
			     $resistsoldiersArray[$key]['gongjifanwei'] = $_SESSION['soldierInfo'][$sid-1]['range']+($rheroinfo['heroshoot']*$commandSoldiers); 
			    }else{
			     $resistsoldiersArray[$key]['gongjifanwei'] = $_SESSION['soldierInfo'][$sid-1]['range']*(1+$rheroinfo['shoot']) + ($rheroinfo['heroshoot']*$commandSoldiers); 
			    }
		     $resistsoldiersArray[$key]['hp'] = $_SESSION['soldierInfo'][$sid-1]['hp']*(1+$rheroinfo['blood']*$commandSoldiers)+$rheroinfo['heroblood']*$commandSoldiers;
		     $resistsoldiersArray[$key]['ap'] = $_SESSION['soldierInfo'][$sid-1]['ap']*((1+$rheroinfo['gongji'])+$rheroinfo['attack']*$commandSoldiers/100) + $rheroinfo['heroattack']*$commandSoldiers;
		     $resistsoldiersArray[$key]['dp'] = $_SESSION['soldierInfo'][$sid-1]['dp']*((1+$rheroinfo['fangyu'])+$rheroinfo['defence']*$commandSoldiers/100) + $rheroinfo['herodefence']*$commandSoldiers;
		     $resistsoldiersArray[$key]['stype'] = $a_sidtype;          
		    }       
	     if(!isset($_SESSION['battle']['defence'])){
		     $_SESSION['battle']['defence'] = sql_fetch_rows("select * from cfg_defence");//取得城防id基本信息
		    }
	     if($_SESSION['battle'][$battleid]['mem']['wallhp'] !=0){
				 $defence = $_SESSION['battle'][$battleid]['mem']['resistdefence'];//取得城防信息
				 $defence_key = count($resistsoldiersArray);
				 $resistsoldiersArray[$defence_key]['sid'] = 18;
				 $resistsoldiersArray[$defence_key]['type'] = 3;
				 $resistsoldiersArray[$defence_key]['count'] = $_SESSION['battle'][$battleid]['mem']['wallhp'];//城墙生命;
				 $resistsoldiersArray[$defence_key]['range'] = 100;
				 $resistsoldiersArray[$defence_key]['speed'] = 0;
				 $resistsoldiersArray[$defence_key]['gongjifanwei'] = 0;
				 $resistsoldiersArray[$defence_key]['hp'] = $_SESSION['battle'][$battleid]['mem']['wallhp'];//城墙生命
				 $resistsoldiersArray[$defence_key]['attack'] = 0;
				 $defenceArray = explode(',', $defence);
				 $defenceCount = array_shift($defenceArray);
				 for ($i = 1; $i < $defenceCount+1; $i++){
					  $did = array_shift($defenceArray);//取城防类型
					  $resistsoldiersArray[$defence_key+$i]['sid'] = array_shift($defenceArray);//sid
					  $resistsoldiersArray[$defence_key+$i]['type'] = 2;
					  $resistsoldiersArray[$defence_key+$i]['did'] = $did;
					  $resistsoldiersArray[$defence_key+$i]['range'] = 100;//位置
					  $cnt = array_shift($defenceArray);
					  if ($cnt < 0) $cnt = 0;   
					  $resistsoldiersArray[$defence_key+$i]['count'] = $cnt;
					  $resistsoldiersArray[$defence_key+$i]['speed'] = 0;
					  $resistsoldiersArray[$defence_key+$i]['gongjifanwei'] = $_SESSION['battle']['defence'][$did-1]['range'];
					  $resistsoldiersArray[$defence_key+$i]['hp'] = $_SESSION['battle']['defence'][$did-1]['hp'];
					  $resistsoldiersArray[$defence_key+$i]['ap'] = $_SESSION['battle']['defence'][$did-1]['ap'];
					  $resistsoldiersArray[$defence_key+$i]['dp'] = $_SESSION['battle']['defence'][$did-1]['dp'];
					  $resistsoldiersArray[$defence_key+$i]['attack'] = 0;
					}   
		    }
		   //速度处理结束
		  foreach ((array)$attacksoldiersArray as $key => $value) {
			  if($value['ratio']){
				 $attacksoldiersArray[$key] =$value;
				}
			 $endsoldier[$key] = $value;
			 $endsoldier[$key]['attack'] = 1;//攻击方
			}
		  $newcount = count($endsoldier);
		  foreach ((array)$resistsoldiersArray as $k => $v) {
			  if($v['ratio']){
				 $resistsoldiersArray[$k] =$v;
				}
			  $endsoldier[$newcount+$k] = $v;
			  $endsoldier[$newcount+$k]['attack'] = 0;//防守方
			}
		  //得到合并后的数组 按照速度冒泡 从快到慢
		  $newcount = count($endsoldier);
		  for ($i=0; $i < $newcount ; $i++) { 
			 for ($j=$newcount-1; $j>$i ; $j--) { 
				 if($endsoldier[$j]['speed'] > $endsoldier[$j-1]['speed']){
					 $x = $endsoldier[$j];
					 $endsoldier[$j] = $endsoldier[$j-1];
					 $endsoldier[$j-1] = $x;
					}
				}
			}
		  $_SESSION['battle'][$battleid]['fightsoldier'] = $endsoldier;//压入session
		}
	  if(!isset($_SESSION['battle'][$battleid]['resistnpc'])){
		  if(is_npc($resistuid)){
			  $resistnpc = 1;//判断防守方是否是npc
			}else{
			  $resistnpc = 0;
			}
		  $_SESSION['battle'][$battleid]['resistnpc'] = $resistnpc;
		}else{
		  $resistnpc = $_SESSION['battle'][$battleid]['resistnpc'];//判断防守方是否是npc
		}
	  if(!isset($_SESSION['battle'][$battleid]['attacknpc'])){
		 if(is_npc($attackuid)){
			 $attacknpc = 1;//判断防守方是否是npc
			}else{
			 $attacknpc = 0;
			}
		 $_SESSION['battle'][$battleid]['attacknpc'] = $attacknpc;  
		}else{
		  $attacknpc = $_SESSION['battle'][$battleid]['attacknpc'];//判断攻击方是否是npc
		}
	  $fightsoldier = $_SESSION['battle'][$battleid]['fightsoldier'];//按速度排好行动顺序的存活兵种
	  //===========
	  //设置NPC兵力出战情况
	  $myattack_troopsid=$_SESSION['battle'][$battleid]['sys']['attacktroop'];
	  $myattacktroop_info = sql_fetch_one("select * from sys_troops where id='$myattack_troopsid'");
	  $mynpctask=$myattacktroop_info['task'];//用于判断是不是占领
	  $mynpctarget_cid=$myattacktroop_info['targetcid'];
	  $mynpctarget_wid = cid2wid($mynpctarget_cid);
	  $mynpcw_info = sql_fetch_one_cell("select type from mem_world where wid='$mynpctarget_wid'");//用于判断是不是城池战
	  //==========
	  $report = '';
	  foreach ($fightsoldier as $key => $value) {
		 if(empty($fightsoldier[$key]) || $value['type'] == 3){//战斗类型，野战:0,攻城战:1,抢掠战2
			 continue;
			}
		 if($value['type'] == 1){
			 $sid = $value['sid'];
			}else{
			  $sid = $value['did'];
			}
		 $stype = $value['stype'];
		 $speed = $value['speed'];
		 $pos = $value['range'];
		 $range = getrangbetween($fightsoldier,$fieldrange,$key);//返回两军距离
		 $gongjifanwei = $value['gongjifanwei'];//攻击范围
		 $istarget = 0;//是否可以攻击
		 $shanghai = 0+999;//攻击力
		 $siwang = 0;//攻击对方死亡 伤兵数
		 $target_sid = 0;//攻击目标
		 $target_type = 0;//攻击类型
		 $target_start = 0;//原兵数
		 $target_end = 0;//留存数
		 $able_fanji = 0;//是否可以反击
		 $fanji_shanghai = 0;//反击伤害
		 $target_start_d = 0;//反击原兵数
		 $fanji_siwang = 0;//反击伤兵
		 $fanji_end = 0;//反击留存
		 if($value['type'] == 1){
			 $is_city = 1;//是否是城防，有城防
			}else{
			  $is_city = 2;//是否是城防
			}
		 if($value['attack']==1){//进攻方
			 $is_attack=1;
			 $tactics = sql_fetch_one("select * from mem_battle_tactics where battleid='$battleid' and attack=1 and stype='$stype' limit 1");
			 if($tactics['action'] == 1){//前进
				 $newrange = $pos - $speed;
				 if($speed < $range){
					 $fightsoldier[$key]['range'] = $newrange;
					}else{
					 $speed = $range;
					 $fightsoldier[$key]['range'] = $pos - $range;
					}    
				}
			 if($tactics['action'] == 3){//后退
				 $newrange = $pos + $speed;
				 if($newrange > $fieldrange){
					 $speed = $fieldrange - $pos;
					 $fightsoldier[$key]['range'] = $fieldrange;
					}else{
					  $fightsoldier[$key]['range'] = $newrange;
					}    
				}
			}else{//防守方
			 $is_attack=2;
			 $tactics = sql_fetch_one("select * from mem_battle_tactics where battleid='$battleid' and attack=0 and stype='$stype' limit 1");
			 if($tactics['action'] == 1){//前进
				 $newrange = $pos + $speed;
				 if($newrange < $pos+$range){
					 $fightsoldier[$key]['range'] = $newrange;
				 }else{
					 $speed = $range;
					 $fightsoldier[$key]['range'] = $pos+$range;
				 }   
			 }
			 if($tactics['action'] == 3){//后退
				 $newrange = $pos - $speed;
				 if($newrange > 0){
					 $fightsoldier[$key]['range'] = $newrange;
				 }else{
					$speed = $pos;
					$fightsoldier[$key]['range'] = 0;
				 }   
			 }
		    }
		  $target = $tactics['target'];//取得攻击目标
		  $abletarget = getabletarget($fightsoldier,$key);//取得可攻击目标数组
		  if(count($abletarget)>0){
			 $is_has = array_search($target,$abletarget);
			 if($is_has != false){
				 $target_key = $is_has;
				 if($target==15) $target_key =getjtsid($fightsoldier);
				}else{
				 $target_keys = array_keys($abletarget);
				 $fuck = rand(1,count($target_keys));
				 $target_key = $target_keys[$fuck-1];//就是被打的兵的键值   
				}
			 $target_pos = $fightsoldier[$target_key]['range'];//取得目标位置
			 $target_count = $fightsoldier[$target_key]['count'];
			 $target_hp = $fightsoldier[$target_key]['hp'];
			 $target_ap = $fightsoldier[$target_key]['ap'];
			 $target_dp = $fightsoldier[$target_key]['dp'];
			 $istarget = 1;
			 $target_sid = $fightsoldier[$target_key]['sid'];//攻击目标
			 if($target==15 && $is_has != false) $target_sid =3;
			 $target_type = $fightsoldier[$target_key]['type'];//攻击类型
			 $target_start = $target_count;
			 //攻击代码
			 if($value['type'] == 2 && $value['did'] != 3){//城防 触发型攻击
				 $fanji_siwang = rand(0,$value['count']);
				 $fightsoldier[$key]['count'] = $value['count'] - $fanji_siwang;//更新
				 if($fightsoldier[$key]['count'] <= 0){
					 $fightsoldier[$key] = null;
					 unset($fightsoldier[$key]);
				 }
				 if($value['ap'] == 0){
					 $siwang = $fanji_siwang;
				    }else{
					 $shanghai = $fanji_siwang*$value['ap']*$value['ap']/($value['ap']+$target_dp);
					 $siwang = $shanghai/$target_hp;
					 $siwang = floor($siwang);
				    }
				  $target_end = $target_count - $siwang;
			    }else{//正常攻击或箭塔攻击       //$value['count']进攻方攻击时候的兵数
				  $a_r_num=$fightsoldier[$key]['count'];
				  $shanghai= $a_r_num*$value['ap']*$value['ap']/($value['ap']+$target_dp);
				  if($target_sid==3 && $sid==6 && $target_type==2) $shanghai= floor($shanghai/50);
				  if($target_type == 3){//打城墙
					 $peoplenum=sql_fetch_one_cell("select people_need from cfg_soldier where sid='$sid'");
					 $shanghai = floor($a_r_num*$value['ap']*$peoplenum/100);
					 $siwang = $shanghai;
					 $target_end = $target_count - $siwang;
					 if($target_end <= 0){
						 $_SESSION['battle'][$battleid]['mem']['wallhp'] = 0;
						 foreach ($fightsoldier as $key1 => $value1) {
							 if($value1['type'] == 2){
								 $fightsoldier[$key1] = null;
								 unset($fightsoldier[$key1]);
							    }
						    }
					    }else{
						  $_SESSION['battle'][$battleid]['mem']['wallhp'] = $target_end;
					    }
				    }else{
					  $shanghai = floor($shanghai); 
					  $siwang = $shanghai/$target_hp;
					  $siwang = floor($siwang);
					  $target_end = $fightsoldier[$target_key]['count'] - $siwang;
					  $fightsoldier[$target_key]['count'] = $target_end;
				    }
			    }
			  //反击代码
			  if($target_end > 0){//城防类的只有箭塔能反击
				 if($fightsoldier[$target_key]['type'] == 1 || ($fightsoldier[$target_key]['type']==2 && $fightsoldier[$target_key]['did'] == 3)){
				     $fightsoldier[$target_key]['count'] = $target_end;
				     $fanji_gongjifanwei = $fightsoldier[$target_key]['gongjifanwei'];//取得被打兵的攻击范围
				     $fanji_attack = $fightsoldier[$target_key]['attack'];//是攻击方还是防守方
				     if($fanji_attack == 1){//攻击方
						  if($fightsoldier[$key]['did'] == 3 && $fightsoldier[$key]['type'] == 2){							    
								$fanji_stype = $fightsoldier[$target_key]['stype'];
								if($fanji_stype==6 || $fanji_stype==10 || $fanji_stype==12){//当为城防类型时只有箭塔为反击目标 并且当反击兵种为远程进攻兵种时才能反击
									if($target_pos - $fanji_gongjifanwei < $fightsoldier[$key]['range']){
										$able_fanji = 1;//是否可以反击
										$fanji_shanghai = $fightsoldier[$target_key]['ap']*$fightsoldier[$target_key]['ap']/($fightsoldier[$target_key]['ap']+$value['dp']);
										if($value['attack']!=1){
										  $fanji_shanghai = floor($fanji_shanghai);
										}else{
										  $fanji_shanghai = floor($fanji_shanghai);
										}
										$target_start_d = $fightsoldier[$key]['count'];//反击原兵数
										$fanji_siwang = $fanji_shanghai/$value['hp'];//反击伤兵
										$fanji_siwang = floor($fanji_siwang);
										if($value['count'] - $fanji_siwang > 0){
											$fanji_end = $fightsoldier[$key]['count'] - $fanji_siwang;//反击留存
											$fightsoldier[$key]['count'] = $fanji_end;
											$value['count']= $fanji_end;
											if($value['count']<=0 || $fightsoldier[$key]['count']<=0){
											 $fightsoldier[$key]['count'] = 0;
											 $value['count']= 0;
											 $fanji_end=0;
											}
										}else{
											$fanji_siwang = $value['count'];//反击死亡
											$fanji_end = 0;//反击留存   
											$fightsoldier[$key]=null;
											unset($fightsoldier[$key]);
										}
									}                                   
								}
							}else{
							  if($target_pos - $fanji_gongjifanwei < $fightsoldier[$key]['range']){
								  $able_fanji = 1;//是否可以反击
								  //$fanji_shanghai = $fightsoldier[$target_key]['count']*$fightsoldier[$target_key]['ap']*$fightsoldier[$target_key]['ap']/($fightsoldier[$target_key]['ap']+$value['dp']);
								  $fanji_shanghai = $target_start*$fightsoldier[$target_key]['ap']*$fightsoldier[$target_key]['ap']/($fightsoldier[$target_key]['ap']+$value['dp']);
								  $fanji_shanghai = floor($fanji_shanghai);//
								  $target_start_d = $fightsoldier[$key]['count'];//反击原兵数
								  if($value['hp']<=0) $value['hp']=1;
								  $fanji_siwang = $fanji_shanghai/$value['hp'];//反击伤兵
								  $fanji_siwang = floor($fanji_siwang);
								  if($value['count'] - $fanji_siwang > 0){
										$fanji_end = $fightsoldier[$key]['count'] - $fanji_siwang;//反击留存
										$fightsoldier[$key]['count'] = $fanji_end;
										$value['count']= $fanji_end;
										if($value['count']<=0 || $fightsoldier[$key]['count']<=0){
											 $fightsoldier[$key]['count'] = 0;
											 $value['count']= 0;
											 $fanji_end=0;
										}
									}else{
										$fanji_siwang = $value['count'];//反击死亡
										$fanji_end = 0;//反击留存   
										$fightsoldier[$key]=null;
										unset($fightsoldier[$key]);
									}
														
								}                               
							}

						}else{//防御方
						  if($target_pos + $fanji_gongjifanwei > $fightsoldier[$key]['range']){
							  $able_fanji = 1;//是否可以反击
							  //$fanji_shanghai = $fightsoldier[$target_key]['count']*$fightsoldier[$target_key]['ap']*$fightsoldier[$target_key]['ap']/($fightsoldier[$target_key]['ap']+$value['dp']);
							  $fanji_shanghai = $target_start*$fightsoldier[$target_key]['ap']*$fightsoldier[$target_key]['ap']/($fightsoldier[$target_key]['ap']+$value['dp']);
							  $fanji_shanghai = floor($fanji_shanghai);//防御方防御反击
							  $target_start_d = $fightsoldier[$key]['count'];//反击原兵数
							  $fanji_siwang = $fanji_shanghai/$value['hp'];//反击伤兵
							  $fanji_siwang = floor($fanji_siwang);
							  if($value['count'] - $fanji_siwang > 0){
									$fanji_end = $fightsoldier[$key]['count'] - $fanji_siwang;//反击留存
									$fightsoldier[$key]['count'] = $fanji_end;
									$value['count']= $fanji_end;
									if($value['count']<=0 || $fightsoldier[$key]['count']<=0){
											 $fightsoldier[$key]['count'] = 0;
											 $value['count']= 0;
											 $fanji_end=0;
									}
								}else{
									$fanji_siwang = $fanji_siwang;//$value['count'];//反击死亡
									$fanji_end = 0;//反击留存
									$fightsoldier[$key]=null;
									unset($fightsoldier[$key]);                             
								}                       
							}
						}
					}else{//被触发型城防干掉的
					   $fightsoldier[$target_key]['count'] = $fanji_end;
					}
				}else{
				  $siwang = $target_start;//不确定
				  $target_end = 0;
				  $fightsoldier[$target_key] = null;
				  unset($fightsoldier[$target_key]);//删除
				}           
			}
		 //战报
		 //第二个参数 1 军队 2 城池 既城防
		 $report .= $is_attack.'.000000,'.$is_city.'.000000,'.$sid.'.000000,'.$tactics['action'].'.000000,'.$speed.'.000000,'.$istarget.'.000000,'.$shanghai.'.000000,'.$target_type.'.000000,'.$target_sid.'.000000,'.$target_start.'.000000,'.$siwang.'.000000,'.$target_end.'.000000,'.$able_fanji.'.000000,'.$fanji_shanghai.'.000000,'.$target_start_d.'.000000,'.$fanji_siwang.'.000000,'.$fanji_end.'.000000;';
		}
	   $_SESSION['battle'][$battleid]['fightsoldier'] = array_values($fightsoldier);//重新索引数组
	   //更新mem_battle
	   $attackmem = 0;
	   $resistmem = 0;
	   $defencemem = 0;
	   $attackstr = '';
	   $resiststr = '';
	   $attackstrpos = '';
	   $resiststrpos = '';
	   $defencestrpos = '';
	   foreach ($fightsoldier as $key => $value) {
		 if($value['attack'] == 1){
			 if($value['count'] >0){
				 $attackmem = $attackmem+1;
				 $attackstr .= $value['sid'].','.$value['count'].',';
				 $attackstrpos .= $value['sid'].','.$value['range'].',';
				}
			}
		 if($value['attack'] == 0){
			 if($value['count'] >0 && $value['type']==1){
				 $resistmem = $resistmem+1;
				 $resiststr .= $value['sid'].','.$value['count'].',';
				 $resiststrpos .= $value['sid'].','.$value['range'].',';
				}
			 if($value['count'] >0 && $value['type']==2){
				 $defencemem = $defencemem+1;
				 $defencestrpos .= $value['did'].','.$value['range'].','.$value['count'].',';
				}
			}

		}
	  if($resistmem == 0  && $_SESSION['battle'][$battleid]['mem']['wallhp'] == 0 ) $_SESSION['battle'][$battleid]['battle_end'] = 0;//攻击方成功
	  if($attackmem == 0)  $_SESSION['battle'][$battleid]['battle_end'] = 1; //防守方胜利
	  if($attackmem == 0 && $resistmem == 0)  $_SESSION['battle'][$battleid]['battle_end'] = 3;//未知
	  if($round>40 ) $_SESSION['battle'][$battleid]['battle_end'] = 2;//平局
	  $attackstrpos = $attackmem.','.$attackstrpos;//最新攻击方位置
	  $resiststrpos = $resistmem.','.$resiststrpos;//最新防守方位置
	  $attackstr = $attackmem.','.$attackstr;//最新攻击方数量
	  $resiststr = $resistmem.','.$resiststr;//最新防守方数量
	  $defencestrpos = $defencemem.','.$defencestrpos;//最新城防信息
	  $time = time()+25;
	  $newwallhp = $_SESSION['battle'][$battleid]['mem']['wallhp'];//最新城墙生命
	  sql_query("update mem_battle set resistpos='$resiststrpos',wallhp='$newwallhp',attackpos='$attackstrpos',attacksoldiers='$attackstr',resistsoldiers='$resiststr',nexttime='$time',`round`=`round`+1,`resistdefence`='$defencestrpos'  where id='$battleid'");
	  sql_query("insert into sys_battle_report (`battleid`,`round`,`report`) values('$battleid','$round','$report')");
	  //判断是否结束
	  if(isset($_SESSION['battle'][$battleid]['battle_end'])){//战斗结束处理
		  $battle_end = $_SESSION['battle'][$battleid]['battle_end'];
		  $membattleinfo=sql_fetch_one("select * from mem_battle where id='$battleid'");
		  if($membattleinfo['type']<5){
			 sql_query("update mem_world set `state`=0 where wid='$mynpctarget_wid'");
			 $myres = getBattleResult($membattleinfo,$battle_end);
			 if($battle_end == 0){
				 sql_query("update sys_battle set state=1,result=0 where id='$battleid'");//战斗胜利
				 if($membattleinfo['type']==4)
					 sql_query("update sys_troops set cid=targetcid,state=4 where battleid='$battleid'");
					else{
					 $my_resource='0,';
					  if(!empty($myres))
						 $my_resource=$myres['gold'].','.$myres['food'].','.$myres['wood'].','.$myres['rock'].','.$myres['iron'].',';
					  $end_troopstate = sql_fetch_one_cell("select state from sys_troops where id='$myattack_troopsid' ");
					  if($end_troopstate!=4)
					     sql_query("update sys_troops set state=1,starttime=unix_timestamp(),endtime=unix_timestamp()+pathtime,`resource`='$my_resource'  where battleid='$battleid'");
					}
				}else if($battle_end == 1){
				  sql_query("update sys_battle set state=1,result=1 where id='$battleid'");//战斗失败
				  sql_query("delete from sys_troops where id='$myattack_troopsid'");
				}else{
				  sql_query("update sys_battle set state=1,result=2 where id='$battleid'");//战斗平局
				  sql_query("update sys_troops set state=1,starttime=unix_timestamp(),endtime=unix_timestamp()+pathtime where battleid='$battleid'");
				}
			}else{//洛阳战场结束处理
				if($battle_end == 0){
				  sql_query("update sys_battle set state=1,result=0 where id='$battleid'");//战斗胜利
				  sql_query("update sys_luoyang_troops set cid=targetcid,state=0,soldiers='$membattleinfo[attacksoldiers]',starttime=unix_timestamp(),endtime=unix_timestamp()+5,battleid=0 where battleid='$battleid' and uid>1000");
				  sql_query("delete from sys_luoyang_troops where battleid='$battleid'");
				}else if($battle_end == 1){
				  sql_query("update sys_battle set state=1,result=1 where id='$battleid'");//战斗失败
				  sql_query("delete from sys_luoyang_troops where battleid='$battleid' and uid>1000");
				  sql_query("update sys_city_hero set state=0 where hid='$attackhid'");
				  sql_query("update sys_luoyang_troops set state=0,starttime=unix_timestamp(),endtime=unix_timestamp()+5,battleid=0 where battleid='$battleid'");
				}else{
				  sql_query("update sys_battle set state=1,result=2 where id='$battleid'");//战斗平局
				  sql_query("update sys_luoyang_troops set state=0,soldiers='$membattleinfo[resistsoldiers]',starttime=unix_timestamp(),endtime=unix_timestamp()+5,battleid=0 where battleid='$battleid'");
				}
			}
		  $_SESSION['battle'][$battleid] = null;
		  unset($_SESSION['battle'][$battleid]);//删除session
		}
	}
function getUsersHeroBattleAdd($hid,$cid,$battletype){//$battletype值0-3为一般战斗 其它值为战场战
	 $mjact =0;
	 $mjdef =0;
	 $mjblood =0;
	 $myspeed = 0;
	 if ($hid == 0) {
		 $heroadd['command_base'] = 0;
		 $heroadd['command_add_on'] = 0;
		 $heroadd['bravery_base'] = 0;
		 $heroadd['bravery_add'] = 0;
		 $heroadd['attack_add_on'] = 0;
		 $heroadd['wisdom_base'] = 0;
		 $heroadd['wisdom_add'] = 0;
		 $heroadd['defence_add_on'] = 0;
		 $heroadd['speed_add_on'] = 0;
		 $heroadd['affairs_base'] = 0;
		 $heroadd['affairs_add_on'] = 0;
		 $heroadd['level'] = 0;
		 $heroadd['range'] =0;
		} else if ($battletype<4) {
		 $heroadd = sql_fetch_one("select * from sys_city_hero where `hid`='{$hid}'");
		 $heroadd['range']= 0;
		 if($hid<1027 && $hid>0){//名将额外属性加成
			  $mjact  = $heroadd['bravery_base'];//攻击加成
			  $mjdef  = $heroadd['wisdom_base'];//防御加成
			  $mjblood = $heroadd['affairs_base'];//生命值加成
			  $myspeed = ceil($heroadd['speed_add_on']*0.1);//速度加成自生的10%
			  $heroadd['range'] = $heroadd['command_base'];//抛射加成
			}
		} else {
		 $heroadd = sql_fetch_one("select * from cfg_battle_hero where `hid`='{$hid}'");
		 $heroadd['command_base'] = $heroadd['command'];
		 $heroadd['command_add_on'] = 0;
		 $heroadd['bravery_base'] = $heroadd['bravery'];
		 $heroadd['bravery_add'] = 0;
		 $heroadd['attack_add_on'] = 0;
		 $heroadd['wisdom_base'] = $heroadd['wisdom'];
		 $heroadd['wisdom_add'] = 0;
		 $heroadd['defence_add_on'] = 0;
		 $heroadd['speed_add_on'] = $heroadd['speedadd'];
		 $heroadd['affairs_base'] = $heroadd['affairs'];
		 $heroadd['affairs_add_on'] = 0;
		 $heroadd['range']= 0;
		}
	 $goods_command = 0;
	 $goods_blood = 0;
	 $goods_att = 0;
	 $goods_def = 0;
	 $agoodsBufs = sql_fetch_rows("select buftype from mem_hero_buffer WHERE `endtime`>unix_timestamp() and `hid`='{$hid}'");
	 if (!empty($agoodsBufs)) {
		  foreach ($agoodsBufs as $agoodsBuf) {
			 switch ($agoodsBuf['buftype']) {
				 case 1:
				  $goods_command = 0.5;
				  break;
				 case 2:
				  $goods_blood = 0.25;
				  break;
				 case 3:
				  $goods_att = 0.25;
				  break;
				 case 4:
				  $goods_def = 0.25;
				  break;
				}
			}   
		}
	 $tec_command_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='6'");
	 $tec_command_add = empty($tec_command_add) ? 0 : $tec_command_add;
	 $tec_attc_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='9'");
	 $tec_attc_add = empty($tec_attc_add) ? 0 : $tec_attc_add;
	 $tec_def_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='10'");
	 $tec_def_add = empty($tec_def_add) ? 0 : $tec_def_add;
	 $tec_speed_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='12'");
	 $tec_speed_add = empty($tec_speed_add) ? 0 : $tec_speed_add;
	 $tec_jiayu = sql_fetch_one_cell("select level from sys_city_technic where cid='{$cid}' and tid='13'");
	 $tec_jiayu = empty($tec_jiayu) ? 0 : $tec_jiayu;
	 $tec_shoot_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='14'");
	 $tec_shoot_add = empty($tec_shoot_add) ? 0 : $tec_shoot_add;
	 $tec_blood_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='16'");
	 $tec_blood_add = empty($tec_blood_add) ? 0 : $tec_blood_add;
	 $tec_plund_add = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`='20'");
	 $tec_plund_add = empty($tec_plund_add) ? 0 : $tec_plund_add;
	 if($heroadd['range']==0 || $heroadd['range']=='') $heroadd['range']=0;
	 $ret['command'] = ($heroadd['command_base'] + $heroadd['level']) * ((1 + $goods_command) + 0.1 * $tec_command_add) + $heroadd['command_add_on'];
	 $ret['bravery'] = ($heroadd['bravery_base'] + $heroadd['bravery_add']+ $heroadd['bravery_add_on'])+$mjact*(1+0.05 * $tec_attc_add);
	 $ret['wisdom'] = ($heroadd['wisdom_base'] + $heroadd['wisdom_add']+ $heroadd['wisdom_add_on'])+$mjdef*(1+0.05 * $tec_def_add);
	 $ret['attack'] = ($heroadd['bravery_base'] + $heroadd['bravery_add']) * (1 + $goods_att) + $heroadd['bravery_add_on'] + $heroadd['attack_add_on'] / 10;
	 $ret['defence'] = ($heroadd['wisdom_base'] + $heroadd['wisdom_add']) * (1 + $goods_def) + $heroadd['wisdom_add_on'] + $heroadd['defence_add_on'] / 10;
	 $ret['blood'] = (($heroadd['affairs_add'] + $heroadd['affairs_add_on'] + $heroadd['affairs_base']) / 500 + $goods_blood) + 0.05 * $tec_blood_add;
	 $ret['plund'] = 1 + 0.03 * $tec_plund_add;
	 $ret['shoot'] = 0.05*$tec_shoot_add;
	 $ret['gongji'] = 0.05 * $tec_attc_add;
	 $ret['fangyu'] = 0.05 * $tec_def_add;
	 $ret['xingjunspeed'] = 0.1 * $tec_speed_add;
	 $ret['jiayuspeed'] = $tec_jiayu * 0.05;
	 $ret['herospeed'] = $myspeed+$heroadd['speed_add_on'];
	 $ret['heroshoot'] = $heroadd['range'] * (1 + 0.05 * $tec_shoot_add);
	 $ret['heroattack'] = $mjact*(1+0.05 * $tec_attc_add);
	 $ret['herodefence'] = $mjdef*(1+0.05 * $tec_def_add);
	 $ret['heroblood'] = $mjblood*(1+0.05 * $tec_blood_add);
	 return $ret;
	}
function is_npc($uid){//是否是npc玩家
	 if($uid == '' || $uid == 0 || empty($uid)){
		 return true;
		}
	 $user_info = sql_fetch_one("select * from sys_user where `uid`='$uid'");
	 if($user_info['group'] != 0){
		  return true;//是npc
		}else{
		  return false;//正常玩家
		}
	}
function getSoldierCounts($soldierstring){
	 $soldierArray = explode(",",$soldierstring);
	 $numSoldiers=array_shift($soldierArray);
	 $sodiercount=0;
	 for ($i = 0; $i < $numSoldiers; $i++){
		 $sid = array_shift($soldierArray);
		 $cnt = array_shift($soldierArray);
		 $sodiercount+=$cnt;
		}
	 return $sodiercount;
	}
function getsoldierarray($soldiers,$soldierspos){
	 $s_info = explode(',', $soldiers);
	 $pos = explode(',', $soldierspos);
	 for ($i=0; $i <$s_info['0'] ; $i++) {      
		 $m= $i*2;
		 $sid = $s_info[$m+1];
		 $count = $s_info[$m+2];
		 $new[$i]['sid'] = $sid;
		 $new[$i]['count'] = $count;
		 $new[$i]['range'] = $pos[$m+2];
		}
	 return $new;
	}
function getrangbetween($fightsoldier,$fightrange,$xx){
	 $attack = $fightrange;
	 $resist = 0;
	 foreach ($fightsoldier as $key => $value) {
		 if ($value['attack'] == 1 && $value['count'] > 0) {
			 if($value['range']<$attack){
				  $attack = $value['range'];
				}
			}
		 if($value['attack'] == 0 && $value['count'] > 0){
			  if($value['range'] >$resist){
				 $resist = $value['range'];
				}
			}
		}
	 if($fightsoldier[$xx]['attack'] == 1){
		 $range = $fightsoldier[$xx]['range'] - $resist;
		}else{
		 $range = $attack - $fightsoldier[$xx]['range'];
		}
	 return $range;//返回两军距离
	}
function getjtsid($fightsoldier){
      foreach ($fightsoldier as $key => $value) {
	     if($value['type']==2 && $value['did']== 3) {
			  $sid=$key;			 
			  return $sid;
			}
		}
    }
function getabletarget($fightsoldier,$xx){
	 $attack = $fightsoldier[$xx]['attack'];
	 $stype = $fightsoldier[$xx]['stype'];
	 if($attack == 1){
		 $abletarget = $fightsoldier[$xx]['range']-$fightsoldier[$xx]['gongjifanwei'];//可攻击范围
		}else{
		  $abletarget = $fightsoldier[$xx]['range']+$fightsoldier[$xx]['gongjifanwei'];//可攻击范围
		}
	 foreach ($fightsoldier as $key => $value) {
		 if($attack == 1){
			 if($stype == 6 || $stype == 10 || $stype == 12){
				 if($value['attack'] == 0 && $value['range'] > $abletarget){
					 if($value['type'] == 3) $rt[$key] = $value['sid']; 
					  else 
					    if($value['type']==2 && $value['did']== 3) $rt[$key] = 15; 
						  else 					 
					        if($value['type']==1)
					           $rt[$key] = sql_fetch_one_cell("select type from cfg_soldier where sid='$value[sid]'"); 
					}               
				}else{
				  if($value['attack'] == 0 && $value['range'] > $abletarget && $value['type'] != 2){
					  $rt[$key] = sql_fetch_one_cell("select type from cfg_soldier where sid='$value[sid]'");
					  if($value['type'] == 3) $rt[$key] = $value['sid'];                      
					}               
				}
			}else{
			  if($value['attack'] == 1 && $value['range'] < $abletarget){
				  //$rt[$key] = $value['sid'];
				  $rt[$key] = sql_fetch_one_cell("select type from cfg_soldier where sid='$value[sid]'"); 
				}
			}
		}
	 return $rt;//返回可攻击数组
	}
function updateFieldResourceAdds($cid){
	$fields = sql_fetch_rows("select * from mem_world where type>1 and ownercid=".$cid);
	$food_add = 0;
	$wood_add = 0;
	$rock_add = 0;
	$iron_add = 0;
	foreach ($fields as $field){
		$type = $field['type'];
		$level = $field['level'];
		if($level>0){
			switch($type){
				case WT_DESERT:
					$rock_add += (3+2 * $level);
					break;
				case WT_FOREST:
					$wood_add += (3+2 * $level);
					break;
				case WT_GRASS:
					$food_add += (2+ $level);
					break;
				case WT_HILL:
					$iron_add += (3+2 * $level);
					break;
				case WT_LAKE:
					$food_add += (5+3 * $level);
					break;
				case WT_SWAMP:
					$food_add += (3+2 * $level);
					break;
			}
		}
	}
	sql_query("update sys_city_res_add set field_food_add='$food_add',field_wood_add='$wood_add',field_rock_add='$rock_add',field_iron_add='$iron_add' where cid=".$cid);
	updateCityResourceAdd($cid);
}
function getnewsdefence($newsdefence,$olddefence){
	 $def1 = explode(',',$newsdefence);
	 $def2 = explode(',',$olddefence);
	 $dnum1=$def1['0'];
	 $dnum2=$def2['0'];
	 $defence='';
	 for ($i = 0; $i < $dnum2; $i++){
		 $m=3*$i+1;
		 $lastnum=0;
		 $did2 = $def2[$m];//取城防类型
		 for($k =0;$k< $dnum1;$k++){
			  $mt=$k*2+1;
			  if($def1[$mt]==$did2){
				 $lastnum=$def1[$mt+1];
				 break;
				}
			}
		 $defence.= $did2.','.$def2[$m+1].','.$lastnum.',';
		}   
	 $defence = $dnum2.','.$defence;
	 return $defence;    
	}
function getBattleResult($membattletroop, $end){//战斗结束处理 $membattleroop 为mem_battle的一条记录 end为战斗结果
	$row = $membattletroop;//将战斗信息存入$row,实时战斗情况
	$id = $row['id'];//哪次战斗
	$type = $row['type'];//type=1 占领城池，type=2,掠夺城池,type=0掠夺或占领野地,type=4战场战
	$round = $row['round'];//第几回合
	$attacksoldiers = $row['attacksoldiers'];//攻击方的军队
	$resistsoldiers = $row['resistsoldiers'];//防御方的军队
	$state = $row['state'];//0,战斗中，1，战斗结束
	$over_actArray = troop2array($attacksoldiers);//获取进攻方信息
	$over_defArray = troop2array($resistsoldiers);//获取防守方信息
	$sys_battle = sql_fetch_one("select * from sys_battle where `id`='{$id}'");//战斗记录
	$defence = $sys_battle['resistdefence'];//五种城防器械的参战数量num,did,count,...
	if ($type == 1 && $defence) {//type1是占领城池  有城防
		$over_defence = defence2array($defence);//获取城防信息
		foreach ($over_defence as $did => $v) {
			if ($v['cnt'] > 0) {
				$over_defenceArray[$did] = $v['cnt'];
			}
			$old_defenceArray[$did] = $v['oldcnt'];
			$lost_defenceArray[$did] = $v['oldcnt'] - $v['cnt'];//损失
		}
	}
	$uid = sql_fetch_one_cell("select attackuid from sys_battle where `id`='{$id}'");//进攻方uid
	$resistuid = sql_fetch_one_cell("select resistuid from sys_battle where `id`='{$id}'");//防守方uid
	$mtroop = sql_fetch_one("select * from bak_troops where `battleid`='{$id}' and uid='$uid'");//进攻方信息
	$ytroop = sql_fetch_one("select * from bak_troops where `battleid`='{$id}' and uid='$resistuid'");//防守方信息
	sql_query("update sys_troops set `soldiers`='$attacksoldiers' where `id`='$mtroop[id]'");//更新进攻方军队信息
	sql_query("update sys_troops set `soldiers`='$resistsoldiers' where `id`='$ytroop[id]'");//更新防守方军队信息
	$bbid = $mtroop['battlefieldid'];  //战场id
	$troopid1 = $mtroop['id'];//进攻事件id
	$troopid2 = $ytroop['id'];//防守事件id
	$cid = $row['attackcid'];//进攻城市id
	$task = $mtroop['task'];//
	$resistcid = $row['resistcid'];//防守方城市id
	$hid = $row['attackhid'];//进攻将领id
	$resisthid = $row['resisthid'];//防守将领id
	$actArray = troop2array($mtroop['soldiers']);//进攻军队
	$defArray = troop2array($ytroop['soldiers']);//防守军队
	$a_Array = getArraySub($actArray, $over_actArray);
	$d_Array = getArraySub($defArray, $over_defArray);
	$a_value = sol2Value($a_Array);
	$d_value = sol2Value($d_Array);
	$acount = array2count($actArray);
	$dcount = array2count($defArray);
	$adiecount = array2count($a_Array);
	$ddiecount = array2count($d_Array);
	$carrytid = sql_fetch_one_cell("select level from sys_city_technic where `cid`='{$cid}' and `tid`=11");
	$allcarry = getCrray($over_actArray, $carrytid);
	$user = sql_fetch_rows("select * from sys_user where `uid`='{$uid}' or `uid`='{$resistuid}'");
	if ($user['0']['uid'] == $uid) {
		$userInfo1 = $user['0'];
		$userInfo2 = $user['1'];
	} else {
		$userInfo1 = $user['1'];
		$userInfo2 = $user['0'];
	}
	$nobility = $userInfo1['nobility'];
	$wid = cid2wid($resistcid);
	$field = sql_fetch_one("select * from mem_world where `wid`='{$wid}'");
	sql_query("UPDATE mem_world SET state='0' WHERE `wid`='{$wid}'");
	$a_wounded = $userInfo1['state'] == 1 ? 0.8 : 0.19;//攻方伤兵率
	$d_wounded = $userInfo2['state'] == 1 ? 0.8 : 0.19;//防方伤兵率
	$a_capture = $userInfo1['state'] == 1 ? 0.32 : 0.15;//攻方俘虏率
	$mreinforcetype = array();
	$yreinforcetype = array();
	$c_capturenum = array();
	$wcid = $cid;
	$wresistcid = $resistcid;
	if ($type == 4) {//战场
		$wcid = $mtroop['startcid'];
		$wresistcid = $ytroop['startcid'];
		$mreinforce = sql_fetch_rows("select * from sys_battle_reinforce where troopid='{$troopid1}'");
		if ($mreinforce) {
			foreach ($mreinforce as $v) {
				$mreinforcetype[] = $v['sid'];
			}
		}
		if ($resistuid > 1000) {
			$yreinforce = sql_fetch_rows("select * from sys_battle_reinforce where troopid='{$troopid1}'");
			if ($yreinforce) {
				foreach ($yreinforce as $v) {
					$yreinforcetype[] = $v['sid'];
				}
			}
		}
	}
	$qingnang = sql_fetch_rows("select * from mem_user_buffer where (buftype=9 or buftype=165 or buftype=10083) and (`uid`='{$uid}' or `uid`='{$resistuid}') and `endtime`>unix_timestamp()");
	if ($qingnang) {//9是青襄 165是高级青襄 10083上级青襄书
		foreach ($qingnang as $v) {
			if ($v['uid'] == $uid) {
			  $a_wounded += $v['buftype'] == 9 ? 0.3 : 0.6;
			  $a_wounded = $a_wounded > 0.8 ? 0.8 : $a_wounded;
			  $a_capture += $v['buftype'] == 9 ? 0.12 : 0.18;
			  $a_capture = $a_capture > 0.5 ? 0.5 : $a_capture;
			} elseif ($v['uid'] == $resistuid) {
			  $d_wounded += $v['buftype'] == 9 ? 0.3 : 0.6;
			  $d_wounded = $d_wounded > 0.8 ? 0.8 : $d_wounded;
			}
		}
	}
	if ($uid > 1000) {
		foreach ($a_Array as $sid => $cnt) {
			$cnt = floor($cnt * $a_wounded);
			if (!in_array($sid, $mreinforcetype) && $cnt > 0) {
				$a_wounder[$sid] = $cnt;
			}
		}
		if ($a_wounder){
			if($type<4)
			  addCityWounded($wcid, $a_wounder, 1);
		}
	}
	$capturetiele='';
	  foreach ($d_Array as $sid => $cnt) {
		  $cnt = floor($cnt * $d_wounded);
		  $cpturcnt = floor($cnt * $a_capture);
		  $mtsid=mt_rand(0,3);
		  if (!in_array($sid, $yreinforcetype) && $cnt > 0) {
			  $d_wounder[$sid] = $cnt;
			  if($mtsid==1) {
				  $csidname=sql_fetch_one_cell("select name from cfg_soldier where `sid`='$sid'");
				  $c_capturenum[$sid] = $cpturcnt;
				  $capturetiele.=sprintf($GLOBALS['report']['b_count'],$csidname,$cpturcnt);
				}
			}
		}
	if ($resistuid >1000){
	   if ($d_wounder){
		 if($type<4)
		   addCityWounded($wresistcid, $d_wounder, 1);
		}
	}
	$a_lostcount = array2count($a_Array);
	$d_lostcount = array2count($d_Array);
	$alostBlood = $a_lostcount > 0 ? floor(($a_lostcount * 100) / $acount) : 0;
	$dlostBlood = $d_lostcount > 0 ? floor(($d_lostcount * 100) / $dcount) : 0;
	if (($alostBlood > 0 && $uid > 1000) && $hid) {
		sql_query("update mem_hero_blood set `force`=GREATEST(0,`force`-{$alostBlood}) where `hid`='{$hid}'");//进攻将领体力
		sql_query("update sys_user_armor set `hp`=GREATEST(0,`hp`-round({$alostBlood})) where `uid`='{$uid}' and `hid`='{$hid}'");//穿戴装备耐久
	}
	if (($dlostBlood > 0 && $resistuid > 1000) && $resisthid) {
		sql_query("update mem_hero_blood set `force`=GREATEST(0,`force`-{$dlostBlood}) where `hid`='{$resistuid}'");//防守将领体力
		sql_query("update sys_user_armor set `hp`=GREATEST(0,`hp`-round({$dlostBlood})) where `uid`='{$resistuid}' and `hid`='{$resisthid}'");
	}
   if ($uid > 1000) {//进攻方玩家
		$m_herolevel = sql_fetch_one_cell("select level from sys_city_hero where hid='$hid'");//参战将领等级
		$heroNeedExp = sql_fetch_one_cell("select upgrade_exp from cfg_hero_level where level='$m_herolevel'");//升级需要经验
		if($m_herolevel==1) $heroNeedExp = 1000;
		$my_heroeexp = floor($d_value / 100);//根据打死的兵决定得多少经验
		if($my_heroeexp > $heroNeedExp)//应得的大于升级需要
		   $exp1 = mt_rand($heroNeedExp,$my_heroeexp);
		  else
			 $exp1 = mt_rand($my_heroeexp,$heroNeedExp);
		$hero_type = sql_fetch_one_cell("select herotype from sys_city_hero where hid='$hid'");
		if($hero_type==1000) $exp1 = 0;//君主将不能得经验
		addHeroExp($hid, $exp1);
	}
	if ($resistuid > 1000 && $resisthid > 0) {//防守方玩家  且有城守
		$m_herolevel = sql_fetch_one_cell("select level from sys_city_hero where hid='$hid'");
		$heroNeedExp = sql_fetch_one_cell("select upgrade_exp from cfg_hero_level where level='$m_herolevel'");
		if($m_herolevel==1) $heroNeedExp = 1000;
		$my_heroeexp = floor($a_value / 100);
		if($my_heroeexp > $heroNeedExp){
		   $exp2 = mt_rand($heroNeedExp,$my_heroeexp);
		}else{
			 $exp2 = mt_rand($my_heroeexp,$heroNeedExp);
		}
		$hero_type = sql_fetch_one_cell("select herotype from sys_city_hero where hid='$resisthid'");
		if($hero_type==1000) $exp2 = 0;
		addHeroExp($hid, $exp2);
	}
	$prestige1 = $userInfo1['prestige'];
	if ($prestige1 <= 0) {
		$prestige1 = 1;
	}
	$apeople = sol2people($a_Array);
	$dpeople = sol2people($d_Array);
	if ($resistuid < 1000) {
		$prestige3 = floor(($dpeople - $apeople) * min(1, ($dpeople / $prestige1)));
		if ($prestige3 + $prestige1 < 0) {
			$prestige3 = 0;
		}
	} else {
		$prestige2 = $userInfo2['prestige'];
		if ($prestige2 <= 0) {
			$prestige2 = 1;
		}
		$prestige3 = floor((($dpeople - $apeople) * (1 + min($prestige1 / $prestige2, 2))) / 10);
		$prestige4 = 0 - $prestige3;//防守方声望
		if ($prestige3 + $prestige1 < 0) {
			$prestige3 = 0;
		}
		if ($prestige4 + $prestige2 < 0) {
			$prestige4 = 0;
		}
		addUserPrestige($resistuid, $prestige4);
	}
	addUserPrestige($uid, $prestige3);
	if ($bbid > 0) {
		//$honour = floor($dpeople / 4000);
		$honour = floor($dpeople / 400);//加大10倍
		if ($honour > 0) {
			$getgoods .= sprintf($GLOBALS['report']['goods'], '战场荣誉', $honour);
			sql_query("update sys_user set `honour`=`honour`+'{$honour}' where `uid`='{$uid}'");
		}
	} else {//掉物品
		$ratebase = log($dcount);
		$ratebase *= $ratebase * $ratebase;
		$rate = mt_rand(0, 8000);
		$rate = $mtroop['battlefieldid'] > 0 ? 0 : $rate;
		if ($rate < $ratebase) {
			$goodsid = getOpenDefaultGoodsResult($uid, 101, 1);
			addGoods($uid, $goodsid['type'], '1', '1');
			$getgoods .= sprintf($GLOBALS['report']['goods'], $goodsid['name'], 1);
		}
	}
	if ($uid > 1000 || $resistuid > 1000) {
		$foodUse = $GLOBALS['soldier']['fooduse'];
		$usepeople = $GLOBALS['soldier']['usepeople'];
		if (is_array($over_actArray)) {
			foreach ($over_actArray as $sid => $cnt) {
				$afoodUse += $foodUse[$sid] * $cnt;
				$ausepeople += $usepeople[$sid] * $cnt;
			}
		}
		if (is_array($over_defArray)) {
			foreach ($over_defArray as $sid => $cnt) {
				$dfoodUse += $foodUse[$sid] * $cnt;
				$dusepeople += $usepeople[$sid] * $cnt;
			}
		}
	}
	switch ($end) {
	 case 0:
		sql_query("delete from sys_troops where `id`='{$troopid2}'");
		switch ($type) {
		 case 0:
			$res = add_resource($mtroop['resource'], $ytroop['resource'], $allcarry);
			$resource = array_shift($res);
			$robres = array_shift($res);
			$myactgoodname = Get_usetroop_goods($uid,$cid,$resistcid,0);
			if ($task == 3) {
				$robArray = check_resource($robres);
				$check = 1;
				sql_query("update sys_troops set `state`='1',`starttime`=unix_timestamp(),`endtime`=unix_timestamp()+`pathtime`,`soldiers`='{$attacksoldiers}',`resource`='$ytroop[resource]',`fooduse`='{$afoodUse}',`people`='{$ausepeople}' where `id`='{$troopid1}'");
				$mstate = '2';
				if ($resisthid > 0) {
					if ($resistuid > 1000) {
						sql_query("update sys_city_hero set `state`='0' where `hid`='{$resisthid}'");
					} else {
						$hero = sql_fetch_one("select * from sys_city_hero where `hid`='$resisthid' limit 1");
						throwHeroField($hero);
					}
				}
				switch($resistuid){//史诗任务物品
				  case 894:{$m_g_tasktype=1;$renwu .= get_m_usertask_goods($uid,$d_value,1,$m_g_tasktype);break;}
				  case 896:{$m_g_tasktype=2;$renwu .= get_m_usertask_goods($uid,$d_value,20,$m_g_tasktype);break;}
				  case 659:{$m_g_tasktype=3;$renwu .= get_m_usertask_goods($uid,$d_value,61002,$m_g_tasktype);break;}
				  default:{$m_g_tasktype=4;$renwu .= get_m_usertask_goods($uid,$d_value,mt_rand(11113,11117),$m_g_tasktype);break;}
				}
				$taskid = $field['level'] + 121;
				completeTask($uid, $taskid);
			} else {
				if ($resisthid > 0) {
				   $loyrate=mt_rand(2,5);
				   sql_query("update sys_city_hero set `loyalty`=`loyalty`-$loyrate where `loyalty`>0 and `hid`='$resisthid'");
				   if ($resistuid > 1000) {
					   sql_query("update sys_city_hero set `state`='1' where `hid`='{$resisthid}'");
					} else {
						$hero = sql_fetch_one("select * from sys_city_hero where `hid`='{$resisthid}' and `herotype`!=1000");
						if (cityHasHeroPosition($uid, $cid)){
						  if ($hero) {
							  $catch .= catchHero($uid, $cid, $hero, $type);
							}
						}else{
						  throwUsersHeroField($hero);
						} 
					}
				}
				if (checkFeildCount($cid)) {
					sql_query("update mem_world set `updatetime`=unix_timestamp(),`ownercid`='{$cid}' where `wid`='{$wid}'");
					sql_query("update sys_troops set `state`=4,`soldiers`='{$attacksoldiers}',`fooduse`='{$afoodUse}',`people`='{$ausepeople}' where `id`='{$troopid1}'");
					updateFieldResourceAdds($cid);
					$mstate = '4';
					$enter = 1;
				} else {
					sql_query("update sys_troops set `state`=1,`starttime`=unix_timestamp(),`endtime`=unix_timestamp()+`pathtime`,`soldiers`='{$attacksoldiers}',`fooduse`='{$afoodUse}',`people`='{$ausepeople}' where `id`='{$troopid1}'");
					$mstate = '2';
					$warming = sprintf($GLOBALS['report']['warming'], '野地达到上限，无法占领更多野地！');
				}
				switch($resistuid){//史诗任务物品
				  case 894:{$m_g_tasktype=1;$renwu .= get_m_usertask_goods($uid,$d_value,mt_rand(1, 2),$m_g_tasktype);break;}
				  case 896:{$m_g_tasktype=2;$renwu .= get_m_usertask_goods($uid,$d_value,mt_rand(20,21),$m_g_tasktype);break;}
				  case 659:{$m_g_tasktype=3;$renwu .= get_m_usertask_goods($uid,$d_value,mt_rand(61002,61003),$m_g_tasktype);break;}
				  default:{$m_g_tasktype=4;$renwu .= get_m_usertask_goods($uid,$d_value,mt_rand(11113,11117),$m_g_tasktype);break;}
				}
				if ($field['type'] == 1) {
					completeTask($uid, 168);
				}
				if ($field['level'] > 0) {
					$taskid = $field['level'] + 152;
					completeTask($uid, $taskid);
				}
			}
			sql_query("insert into mem_world_schedule(`wid`,`last_create_npc`) values ('{$wid}',unix_timestamp()) on duplicate key update `last_create_npc`=unix_timestamp()");
			break;
		case 1:
			$cityInfo = sql_fetch_one("select r.morale,r.complaint,r.wood,r.rock,r.iron,r.gold,r.food,c.type,c.name,w.type as wtype,w.province,w.jun from mem_city_resource r,sys_city c,mem_world w where r.cid=c.cid and w.type=0 and w.ownercid=c.cid and c.cid='{$resistcid}'");
			$check = check_morale($uid, $resistcid, $cityInfo['type'], $cityInfo['morale'], $cityInfo['complaint']);
			$cityInfo['morale'] = $check['morale'];
			$cityInfo['complaint'] = $check['complaint'];
			$check1 = check_nobility($uid, $nobility);
			$check2 = check_city_type($uid, $cityInfo);
			$con .= $check1['can'];
			if (cityHasHeroPosition($uid, $cid)){
				$heros = chose_hero($resistcid, $resisthid);
				if ($heros) {
					foreach ($heros as $hero) {
						$catch .= catchHero($uid, $cid, $hero, $type);
					}
				}
			}
			sql_query("update sys_city_hero set `loyalty`=`loyalty`-5 where `loyalty`>4 and `cid`='{$resistcid}'");
			if (($check['can'] && $check1['can']) && $check2['can']) {
				sql_query("update mem_city_schedule set last_govern_time=0,last_change_name=0,last_levy_resource=0,last_pacify_people=0,last_trick_morale=0,last_trick_weiweijiuzhao=0,last_trick_chenhuodajie=0,last_trick_maifu=0,last_anming=0,last_adv_move=0 where cid='{$resistcid}'");
				sql_query("update sys_building set `level`=`level`-1,`state`=0 WHERE `cid`='{$resistcid}' and `state`>0");
				sql_query("update sys_user set `lastcid`='{$resistcid}' where `uid`='{$uid}'");
				sql_query("delete from sys_city_draftqueue where `cid`='{$resistcid}'");
				sql_query("delete from mem_city_draft WHERE `cid`='{$resistcid}'");
				sql_query("delete from mem_city_wounded WHERE `cid`='{$resistcid}'");
				sql_query("delete from mem_city_lamster WHERE `cid`='{$resistcid}'");
				sql_query("delete from sys_city_soldier WHERE `cid`='{$resistcid}'");
				sql_query("delete from mem_building_upgrading WHERE `cid`='{$resistcid}'");
				sql_query("delete from mem_building_destroying WHERE `cid`='{$resistcid}'");
				sql_query("delete from mem_city_buffer where `cid`='{$resistcid}'");
				sql_query("delete from sys_city_defence where `cid`='{$resistcid}'");
				sql_query("delete from sys_city_reinforcequeue where `cid`='{$resistcid}'");
				sql_query("delete from mem_city_reinforce where `cid`='{$resistcid}'");
				sql_query("delete from sys_city_tactics where `cid`='{$resistcid}'");
				sql_query("delete from sys_city_rumor where `cid`='{$resistcid}'");
				sql_query("delete from sys_recruit_hero where `cid`='{$resistcid}'");
				$city_cnt = sql_fetch_one_cell("select count(cid) from sys_city where `uid`='{$resistuid}'");
				if (($resistuid > 1000 && $cityInfo['type'] == 0) && $city_cnt <= 1) {
					troopsBack($resistuid, $resistcid);
					$movecid = moveGetCid();
					sql_query("update sys_troops set `startcid`='{$movecid}' where `startcid`='{$resistcid}'");
					sql_query("update sys_troops set `cid`='{$movecid}' where `cid`='{$resistcid}' and `state`=3");
					movecitys($resistuid, $resistcid, $movecid);
				} else {
					sql_query("delete from sys_troops WHERE `cid`='{$resistcid}'");
					$heros = sql_fetch_rows("select * from sys_city_hero where `cid`='{$resistcid}' and `uid`='{$resistuid}'");
					foreach ($heros as $hero) {
						throwHeroToField($hero);
						$losthero .= sprintf($GLOBALS['battle']['losthero'], $hero['name'], $hero['level']);
					}
				}
				sql_query("update sys_city set `uid`='{$uid}' where `cid`='{$resistcid}'");
				sql_query("update sys_building set `level`=`level`-floor(rand()*4) where `cid`='{$resistcid}'");//可能出现负或0的情况
				$level = sql_fetch_one_cell("select level from sys_building where `cid`='{$resistcid}' and `bid`=" . ID_BUILDING_GOVERMENT);
				if($level<=0){
				  $level=1;
				  sql_query("update sys_building set `level`='$level' where `cid`='{$resistcid}' and `bid`=" . ID_BUILDING_GOVERMENT);
				}
				resetCityGoodsAdd($uid, $resistcid);
				delBuildings($resistcid, $level);
				check_city_resource($uid, $resistcid);
				check_Technic($uid);
				addCitySoldiers($resistcid, $over_actArray, 1);
				if ($mtroop['resource'] != '0' && $mtroop['resource'] != '0,0,0,0,0,') {
					$resource = check_resource($mtroop['resource']);
					addCityResources($resistcid, $resource['wood'], $resource['rock'], $resource['iron'], $resource['food'], $resource['gold']);
				}
				sql_query("update sys_city_hero set `cid`='{$resistcid}' where `hid`='{$hid}'");
				$mstate = '1';
				sql_query("update sys_city set `chiefhid`='{$hid}' where `cid`='{$resistcid}'");
				updateCityHeroChange($uid, $resistcid);
				$colorss=49151;
				$tarcitynamess=getPosition($resistcid);
				if($cityInfo['type']>0){$colorss=16738740;$tarcitynamess=getCityNamePosition($resistcid);}
				$msg = $GLOBALS['battle']['citytype'][$cityInfo['type']].$tarcitynamess.'经过激战被【'.$userInfo1['name'].'】成功占领！快去祝贺他吧！！';
				sql_query("insert into sys_inform (`type`,`inuse`,`starttime`,`endtime`,`interval`,`scrollcount`,`color`,`msg`) values (0,1,unix_timestamp(),unix_timestamp()+600,50000,1,$colorss,'{$msg}')");
				$enter = 1;
				sql_query("delete from sys_troops where `id`='{$troopid1}'");
				if(($cityInfo['province']==6 || $cityInfo['province']==12) && $cityInfo['type']>0 )
				   sql_query("replace into sys_user_goal (`uid`,`gid`) values ('$uid',3101471)");
			} else {
				$lastwintime = $_SERVER['REQUEST_TIME'] - $sys_battle['starttime'];
				if (empty($lastwintime)) {
					$lastwintime = 0;
				}
				if (($lastwintime > 86400 || $nobility >= 5 && $resistuid < 1000) || $lastwintime == 0) {
					$robresource = get_city_resource($resistcid, 1);
					$res = add_resource($mtroop['resource'], $robresource, $allcarry);
					$resource = array_shift($res);
					$robres = array_shift($res);
					$resource=$robres;
					$robArray = check_resource($robres);
					addCityResources($resistcid, 0 - $robArray['wood'], 0 - $robArray['rock'], 0 - $robArray['iron'], 0 - $robArray['food'], 0 - $robArray['gold']);
				} else {
					$resource = $mtroop['resource'];
				}
				sql_query("update sys_troops set `state`='1',`starttime`=unix_timestamp(),`endtime`=unix_timestamp()+`pathtime`,`soldiers`='{$attacksoldiers}',`resource`='{$resource}',`fooduse`='{$afoodUse}',`people`='{$ausepeople}' where `id`='{$troopid1}'");
				$mstate = '2';
				if ($type == 1 && is_array($over_defenceArray)) {
					addCityDefences($resistcid, $over_defenceArray, 1);
				}
			}
			if ($resisthid >0) {
				if($resistuid>1000){
				 sql_query("update sys_city_hero set `state`='{$state}' where `hid`='{$resisthid}' and `uid`='{$resistuid}'");
				}
				else{
				  sql_query("update sys_city_hero set `state`=1 where `hid`='{$resisthid}' and `uid`<>'$uid'");
				}
			}
			switch($resistuid){
				  case 894:{
					 $m_g_tasktype=1;
					 $renwu .= get_m_usertask_goods($uid,$d_value,1,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,2,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,3,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$row['level'],4,$m_g_tasktype);
					 break;
					}
				  case 896:{
					 $m_g_tasktype=2;
					 $renwu .= get_m_usertask_goods($uid,$d_value,20,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,21,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,22,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$row['level'],23,$m_g_tasktype);
					 break;
					}
				  case 659:{
					 $m_g_tasktype=3;
					 $renwu .= get_m_usertask_goods($uid,$d_value,61002,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,61003,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,61004,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$row['level'],61001,$m_g_tasktype);
					 break;
					}
				}
			$myactgoodname = Get_usetroop_goods($uid,$cid,$resistcid,0);
			sql_query("insert into mem_world_schedule(`wid`,`last_create_npc`,`last_create_defence`) values ('{$wid}',unix_timestamp(),unix_timestamp()) on duplicate key update `last_create_npc`=unix_timestamp(),`last_create_defence`=unix_timestamp()");
			break;
		case 2:
			$cityInfo = sql_fetch_one("select mem_city_resource.morale,mem_city_resource.complaint,mem_city_resource.wood,mem_city_resource.rock,mem_city_resource.iron,mem_city_resource.gold,mem_city_resource.food,sys_city.type,mem_world.type as wtype,mem_world.province,mem_world.jun from mem_city_resource,sys_city,mem_world where mem_city_resource.cid=sys_city.cid and mem_world.type=0 and mem_world.ownercid=sys_city.cid and sys_city.cid='{$resistcid}'");
			$check = check_morale($uid, $resistcid, $cityInfo['type'], $cityInfo['morale'], $cityInfo['complaint'], '1');
			$lastwintime = $_SERVER['REQUEST_TIME'] - $sys_battle['starttime'];
			if (empty($lastwintime)) {
				$lastwintime = 0;
			}
			if (($lastwintime > 86400 || $nobility >= 5 && $resistuid < 1000) || $lastwintime == 0) {
				$robresource = get_city_resource($resistcid);
				$res = add_resource($mtroop['resource'], $robresource, $allcarry);
				$resource = array_shift($res);
				$robres = array_shift($res);
				$resource=$robres;
				$robArray = check_resource($robres);
				addCityResources($resistcid, 0 - $robArray['wood'], 0 - $robArray['rock'], 0 - $robArray['iron'], 0 - $robArray['food'], 0 - $robArray['gold']);
			} else {
				$resource = $mtroop['resource'];
			}
			sql_query("update sys_troops set `state`='1',`starttime`=unix_timestamp(),`endtime`=unix_timestamp()+`pathtime`,`soldiers`='{$attacksoldiers}',`resource`='{$resource}',`fooduse`='{$afoodUse}',`people`='{$ausepeople}' where `id`='{$troopid1}'");
			$mstate = 2;
			if ($resisthid) {
				sql_query("update sys_city_hero set `state`='{$state}' where `hid`='{$resisthid}'");
			}
			sql_query("update sys_city_hero set `loyalty`=`loyalty`-1 where `cid`='{$resistcid}'");
			//if ($resistuid == 894) {
			//    $renwu = get_task_goods($uid, $d_value, '1');
			//    $renwu .= get_task_goods($uid, $d_value, '2');
			//    $renwu .= get_task_goods($uid, $d_value, '3');
			//}
			switch($resistuid){
				  case 894:{
					 $m_g_tasktype=1;
					 $renwu .= get_m_usertask_goods($uid,$d_value,1,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,2,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,3,$m_g_tasktype);
					 break;
					}
				  case 896:{
					 $m_g_tasktype=2;
					 $renwu .= get_m_usertask_goods($uid,$d_value,20,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,21,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,22,$m_g_tasktype);
					 break;
					}
				  case 659:{
					 $m_g_tasktype=3;
					 $renwu .= get_m_usertask_goods($uid,$d_value,61002,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,61003,$m_g_tasktype);
					 $renwu .= get_m_usertask_goods($uid,$d_value,61004,$m_g_tasktype);
					 break;
					}
				}
			$myactgoodname = Get_usetroop_goods($uid,$cid,$resistcid,0);
			sql_query("insert into mem_world_schedule(`wid`,`last_create_npc`) values ('{$wid}',unix_timestamp()) on duplicate key update `last_create_npc`=unix_timestamp()");
			break;
		case 4:
			sql_query("update sys_troops set `cid`='{$resistcid}',`state`=4,`soldiers`='{$attacksoldiers}',battleid=0 where `id`='{$troopid1}'");
			$mstate = '4';
			$myheroname = sql_fetch_one_cell("select name from sys_city_hero where `hid`='{$hid}'");
			if ($resistuid < 1000) {
				$yheroname = sql_fetch_one_cell("select name from cfg_battle_hero where `hid`='{$resisthid}'");
			} else {
				$yheroname = sql_fetch_one_cell("select name from sys_city_hero where `hid`='{$resisthid}'");
			}
			$battlecity = sql_fetch_one_cell("select name from sys_battle_city where `cid`='{$resistcid}'");
			$msg = (($GLOBALS['battle']['union_name'][$mtroop['battleunionid']] . "军 {$myheroname} 部 在 {$battlecity} 击败了 ") . $GLOBALS['battle']['union_name'][$ytroop['battleunionid']]) . "军 {$yheroname} 部。";
			sql_query("insert into `mem_war_event` (`battleid`, `unionid`,`type`,`content`,`evttime`) values ('{$bbid}', '{$unionid}', 2, '{$msg}',unix_timestamp())");
			if ($resistuid > 1000) {
				if ($resisthid) {
					sql_query("update sys_city_hero set `state`=0 where hid='{$resisthid}'");
				}
				sql_query("delete from sys_battle_reinforce where `troopid`='{$troopid2}'");
			}
			$myhjbid = $cid % 1000;
			if($mtroop['bid']==1001 && $myhjbid==367 && $mtroop['cid']==$resistcid){
			  $isokhlg=sql_fetch_rows("select * from sys_troops where bid='$mtroop[bid]' and cid='$resistcid' and  battleunionid<>'$mtroop[battleunionid]'");
			  $mmsg=count($isokhlg);
			  if($mmsg==1){
				  completeTask($uid,'101966');
				  completeTask($uid,'101982');
				  sql_query("replace into sys_user_task (`uid`,`tid`,`state`) values ('$uid','60015','0')");
				  sql_query("update sys_battle_city set `nextxy`='3,334,275,309,' where xy='367'");
				  $ssmsg ='何进：诸位能积极破敌，解虎牢之围，本将必会为各位在天子面前美言几句！';
				  sql_query("insert into `mem_war_event` (`battleid`, `unionid`,`type`,`content`,`evttime`) values ('{$bbid}', '{$unionid}', 2, '{$ssmsg}',unix_timestamp())");
				}else if($mmsg==4){
				  $ssmsg ='黄巾军围虎牢关，以危京师，天子震怒，汉军清剿虎牢。得天子之令，众军整顿兵马，出关奔赴各地，以平息黄巾之乱。';
				  sql_query("insert into `mem_war_event` (`battleid`, `unionid`,`type`,`content`,`evttime`) values ('{$bbid}', '{$unionid}', 2, '{$ssmsg}',unix_timestamp())");
				  $ssmsg ='何进：天子圣旨，剿灭黄巾。如今，各位整军出发，携手铲除黄巾乱贼，复我汉室之安!';
				  sql_query("insert into `mem_war_event` (`battleid`, `unionid`,`type`,`content`,`evttime`) values ('{$bbid}', '{$unionid}', 2, '{$ssmsg}',unix_timestamp())");
				}
			}
			$event = sql_fetch_rows("select * from cfg_battle_event where (`triggerid`='{$resistcid}'%1000 or `triggerid`='{$resisthid}') and `bid`='{$mtroop['bid']}' and `unionid`='{$mtroop['battleunionid']}'");
			$battlelevel = sql_fetch_one_cell("select level from sys_user_battle_field where `id`='{$bbid}'");
			if ($event) {
				$comma1 = '';
				$comma2 = '';
				foreach ($event as $v) {
					if ($v['triggertype'] == 2) {
						$cando = sql_check("select * from sys_troops where `cid`='{$resistcid}' and `battlefieldid`='{$bbid}' and `battleunionid`<>'{$mtroop['battleunionid']}'") ? 0 : 1;
					} else {
						$cando = 1;
					}
					if ($cando) {
						if ($v['targetid1'] > 50000) {
							$goalid = sql_fetch_one_cell("select id from cfg_task_goal where `tid`='{$v['targetid1']}'");
							if ($goalid) {
								if ($v['targettype'] == 3) {
									if (checkBattleResult($v['bid'], $bbid)) {
										sql_query("update sys_user_battle_field set `finishtime`=unix_timestamp(),`winner`='{$mtroop['battleunionid']}' where `id`='{$bbid}'");
									}
									sql_query("insert into sys_user_goal (`uid`,`gid`) select `uid`,'{$goalid}' from sys_user_battle_state where `battlefieldid`='{$bbid}' and `unionid`='{$mtroop['battleunionid']}' on duplicate key update `gid`=values(gid)");
								} else if ($v['targettype'] == 1) {
									$sql = "insert into sys_user_task (uid,tid,state) select `uid`,'{$v['targetid1']}',0 from sys_user_battle_state where `battlefieldid`='{$bbid}' and `unionid`='{$mtroop['battleunionid']}' on duplicate key update state=0";
									sql_query($sql);
									$sql = '';
								} else {
									completeTask($uid, $goalid);
								}
							}
						} elseif ($v['targetid1'] > 1000) {
							$addhid .= $comma1;
							$addhid .= $v['targetid1'];
							$comma1 = ',';
						}
						if ($v['msg']) {
							$msg = $v['msg'];
							sql_query("insert into `mem_war_event` (`battleid`, `unionid`,`type`,`content`,`evttime`) values ('{$bbid}', '{$unionid}', 2, '{$msg}',unix_timestamp())");
						}
						if ($v['targetid2'] > 1000) {
							$loshid .= $comma2;
							$loshid .= $v['targetid2'];
							$comma2 = ',';
						}
					}
				}
				if ($addhid) {
					$newadd = sql_fetch_rows("select * from cfg_battle_troop where bid='{$mtroop['bid']}' and hid in ({$addhid})");
					if ($newadd) {
						sql_query("delete from sys_troops where battlefieldid='{$bbid}' and hid in ({$addhid})");
						$comma = '';
						foreach ($newadd as $v) {
							$soldiers = createSoldier($v['npcvalue'], $v['soldiers'], $battlelevel);
							$tcid = battleid2cid($bbid, $v['xy']);
							$sql .= $comma;
							$sql .= "({$tcid},0,'{$v['hid']}','{$soldiers}',4,'{$v['drop']}','{$v['rate']}',{$bbid},'{$v['unionid']}',{$v['bid']})";
							$comma = ',';
						}
						$sql = 'insert into sys_troops (cid,uid,hid,soldiers,state,`drop`,rate,battlefieldid,battleunionid,bid) values ' . $sql;
						sql_query($sql);
						$sql = '';
					}
				}
				if ($loshid) {
					sql_query("delete from sys_troops where battlefieldid='{$bbid}' and uid<1000 and hid in ({$loshid})");
				}
			}
			$unionid = resetBattleFieldUid($resistcid);
			if ($unionid == $mtroop['battleunionid']) {
				$tunionid = $unionid == 3 ? 4 : 3;
				$turn = sql_fetch_one("select winpoint,losepoint from sys_battle_city where `cid`='{$resistcid}'");
				sql_query("update sys_battle_winpoint set `point`=LEAST(10000,`point`+{$turn['winpoint']}) where `battlefieldid`='{$bbid}' and `unionid`='{$unionid}'");
				sql_query("update sys_battle_winpoint set `point`=GREATEST(0,`point`-{$turn['losepoint']}) where `battlefieldid`='{$bbid}' and `unionid`='{$tunionid}'");
			}
			$enter = 1;
			break;
		}
		break;
	case 1:
		if ($type == 0 || $type == 4) {
			sql_query("delete from sys_troops where `id`='$mtroop[id]'");
			if($his>0) sql_query("update sys_city_hero set `state`='0' where `hid`='{$hid}'");
			sql_query("update sys_troops set `state`='4',`soldiers`='{$resistsoldiers}',`fooduse`='{$dfoodUse}',`people`='{$dusepeople}' where `id`='{$ytroop[id]}'");
		} else {
			sql_query("delete from sys_troops where `id`='$ytroop[id]' or `id`='$mtroop[id]'");
			if (is_array($over_defArray)) {
				addCitySoldiers($resistcid, $over_defArray, 1);
			}
			if ($type == 1 && is_array($over_defenceArray)) {
				addCityDefences($resistcid, $over_defenceArray, 1);
				sql_query("insert into mem_world_schedule(`wid`,`last_create_npc`,`last_create_defence`) values ('{$wid}',unix_timestamp(),unix_timestamp()) on duplicate key update `last_create_npc`=unix_timestamp(),`last_create_defence`=unix_timestamp()");
			}
		}
		$mstate = '0';
		$loyalty = '5';
		if ($resisthid > 0) {
			sql_query("update sys_city_hero set `state`='{$state}' where `hid`='{$resisthid}'");
		}
		break;
	case 2:
		$mstate = '2';
		if ($resisthid > 0) {
			sql_query("update sys_city_hero set `state`='{$state}' where `hid`='{$resisthid}'");
		}
		if ($type == 0 || $type == 4) {
			sql_query("update sys_troops set `state`='4',`soldiers`='{$resistsoldiers}',`fooduse`='{$dfoodUse}',`people`='{$dusepeople}' where `id`='{$troopid2}'");
		} else {
			if (is_array($over_defArray)) {
				addCitySoldiers($resistcid, $over_defArray, 1);
			}
			if ($type == 1 && is_array($over_defenceArray)) {
				addCityDefences($resistcid, $over_defenceArray, 1);
				sql_query("insert into mem_world_schedule(`wid`,`last_create_npc`,`last_create_defence`) values ('{$wid}',unix_timestamp(),unix_timestamp()) on duplicate key update `last_create_npc`=unix_timestamp(),`last_create_defence`=unix_timestamp()");
			}
			sql_query("delete from sys_troops where `id`='{$troopid2}'");
		}
		sql_query("update sys_troops set `state`='1',`starttime`=unix_timestamp(),`endtime`=unix_timestamp()+`pathtime`,`soldiers`='{$attacksoldiers}',`fooduse`='{$afoodUse}',`people`='{$ausepeople}' where `id`='{$troopid1}'");
		break;
	case 3:
		break;
	}
	if ($hid > 0) {
		sql_query("update sys_city_hero set `state`='{$mstate}',`exp`=`exp`+'{$exp1}',`loyalty`=`loyalty`-'{$loyalty}' where `hid`='{$hid}'");
	}
	sql_query("update sys_troops set `state`=0 where `state`=2 and `targetcid`='{$resistcid}' order by `endtime` asc limit 1");
	if ($type == 1 && is_array($lost_defenceArray)) {
		foreach ($lost_defenceArray as $did => $cnt) {
			if ($cnt > 0) {
				switch ($did) {
				case 1:
					$defenceAdd = 0.05;
					break;
				case 2:
					$defenceAdd = 0.06;
					break;
				case 3:
					$defenceAdd = 0.03;
					break;
				case 4:
					$defenceAdd = 0.07;
					break;
				case 5:
					$defenceAdd = 0.08;
					break;
				}
				$add_defenceArray[$did] = $cnt * $defenceAdd;
			}
		}
		if ($add_defenceArray) {
			addCityDefences($resistcid, $add_defenceArray, 1);
		}
	}
	//报告
	$battype = $type == 0 ? ($resistuid == '897' ? 1 : 2) : 0;
	$mcityname = getNamePosition($cid, $type, $mtroop['battlefieldid']);
	$tcityname = getNamePosition($resistcid, $type, $ytroop['battlefieldid']);
	$con = getreporttitle($mcityname, $tcityname, $mtroop['task'], $mtroop['state']);
	$con .= $GLOBALS['report']['title_2row'][$battype];
	$okcontient=$con;
	if ($end == 0) {
		$mywin='失败';
		$ywin='胜利';
		$myw='胜利';
		$youw='失败';
		$tend = 1;
	} elseif ($end == 1) {
		$ywin='失败';
		$mywin='胜利';
		$myw='失败';
		$youw='胜利';
		$tend = 0;
	} else {
		$ywin='平局';
		$mywin='平局';
		$myw='平局';
		$youw='平局';
		$tend = 2;
	}
	if ($userInfo2['uid'] == 895) {
		$userInfo2['name'] = '山贼';
	}
	$isbattle = $type == 4 && $resistuid < 1000 ? 1 : 0;
	$myhero = $hid > 0 ? getContentBattleHero($hid) : '';
	$youhero = $resisthid > 0 ? getContentBattleHero($resisthid, $isbattle) : '';
	$okcontent .= sprintf($GLOBALS['report']['b_titles'],$round,$myw,$userInfo1['name'],$myw,$userInfo2['name'],$youw,$myhero.$youhero);
	$myw='';
	$youw='';
	$con1='';
	foreach ($actArray as $sid => $cnt) {
		$sname = $GLOBALS['battle']['patrol_report_soldier'][$sid];
		if (array_key_exists($sid, $a_Array)) {
			$lostcnt = $a_Array[$sid];
		} else {
			$lostcnt = 0;
		}
		$myw .= sprintf($GLOBALS['report']['armynumss'], $sname, $cnt, $lostcnt);
		$rpsoinfo3 .= sprintf($GLOBALS['report']['b_count'], $sname, $cnt - $lostcnt);
	}
	foreach ($defArray as $sid => $cnt) {
		$sname = $GLOBALS['battle']['patrol_report_soldier'][$sid];
		if (array_key_exists($sid, $d_Array)) {
			$lostcnt = $d_Array[$sid];
		} else {
			$lostcnt = 0;
		}
		$youw .= sprintf($GLOBALS['report']['armynumss'], $sname, $cnt, $lostcnt);
		$rpsoinfo4 .= sprintf($GLOBALS['report']['b_count'], $sname, $cnt - $lostcnt);
	}
	if ($type == 1 && $defence) {
		foreach ($over_defence as $sid => $v) {
			$sname = $GLOBALS['battle']['patrol_report_defence'][$sid];
			$youw .= sprintf($GLOBALS['report']['armynumss'], $sname, $v['oldcnt'], $v['oldcnt'] - $v['cnt']);
		}
	}
	if ($end == 1 && $round == 1) {
		$okcontent .='<table border="1" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" width="505">';
		$okcontent .=sprintf($GLOBALS['report']['warming'], '我方首轮交战既全军覆没，没有获得任何情报！');
		$okcontent .='</table>';
	} else {
		$okcontent .=sprintf($GLOBALS['report']['b_adtroopss'], '我方军情',$myw);
		$okcontent .=sprintf($GLOBALS['report']['b_adtroopss'], '敌方军情',$youw);
	}
	$con='';
	$con1 .= sprintf($GLOBALS['report']['get'], $prestige3, $exp1, $a_wounded * 100);
	if($type<>4){//获得俘虏
	  $getcaptures=mt_rand(1,10);
	  if($getcaptures<5){
		  if($c_capturenum) {
			 $con=sprintf($GLOBALS['report']['b_sbinfo'],'俘虏明细','军队','数量',$capturetiele);
			 addCityCapture($cid,$c_capturenum,1); 
			}
		}
	}
	if ($getgoods) {
		$con1 .= $getgoods;
		if (isSentGood($goodsid['type'])) {
			$goodsname = $goodsid['name'] . ' 1';
			$msg = sprintf($GLOBALS['msg']['field'], $tcityname, $userInfo1['name'], $goodsname);
			sql_query("insert into sys_inform (`type`,`inuse`,`starttime`,`endtime`,`interval`,`scrollcount`,`color`,`msg`) values (0,1,unix_timestamp(),unix_timestamp()+600,50000,1,'49151','{$msg}')");
		}
	}
	if ($renwu) {
		$con1 .= $renwu;
	}
	if ($catch) {
		$con1 .= $catch;
	}
	if ($warming) {
		$con1 .= $warming;
	}
	if ($losthero) {
		$con1 .= $losthero;
	}
	$con .=sprintf($GLOBALS['report']['detectreport'], '战斗收获');
	$con .=$con1;
	$con .=$myactgoodname;
	$con .=sprintf($GLOBALS['report']['title_end']);
	if ($check) {
		if ($type == 1) {
			$commn .= $check['con'];
			$commn .= $check1['con'];
			$commn .= $check2['con'];
		}
		if ($type == 1 && $enter == 1) {
			$commn .= '占领成功！<br/>';
		} else {
			if (is_array($robArray)) {
				$commn .= sprintf($GLOBALS['report']['resource'], '掠夺战果', $robArray['gold'],$robArray['food'],$robArray['wood'],$robArray['rock'],$robArray['iron']);
				$commn .= '掠夺完成！<br/>';
			} else {
				$commn .= ' 遭到多次掠夺后，该城池充满了警惕，早在入侵者到达之前，就已经将所有资源隐藏起来，掠夺者没有获得任何资源。<br/>';
				$commn .= $type == 1 ? '占领失败！<br/>' : '掠夺失败！<br/>';
			}
		}
	}
	$con .=$commn;
	if ($adiecount < $acount) {
		if ($enter) {
			$con .= ('<br/>军队进入' . $tcityname) . '。';
		} else {
			$myhero = $hid > 0 ? getContentHero($hid) : '';
			$con .= sprintf($GLOBALS['report']['troopback'], '部队返回', $myhero, $rpsoinfo3);
			$con .= ('<br/>军队正在返回' . $mcityname) . '。';
		}
		$okcontent.=$con;
	} else {
		$okcontent.= '<br/> 我军全军覆没，没有军队返回！';
	}
	$title = getReportTitleType($mtroop['task'], $mtroop['state']);
	sendReport($uid, '0', $title, $cid, $resistcid, $okcontent);
	if (!($end == 1 && $round == 1)) {
		sql_query("update sys_report set `battleid`='{$id}' where `id`=(select LAST_INSERT_ID())");
	}
	if ($resistuid > 1000) {
		if ($uid > 1000) {
			sql_query("update sys_user set `war_attack_prestige`=`war_attack_prestige`+'{$dpeople}' where `uid`='{$uid}'");
		}
		sql_query("update sys_user set `war_defence_prestige`=`war_defence_prestige`+'{$apeople}' where `uid`='{$resistuid}'");
		$myhero1 = $hid > 0 ? getContentBattleHero($hid) : '';
		$youhero1 = $resisthid > 0 ? getContentBattleHero($resisthid, $isbattle) : '';
		$yokcontent = sprintf($GLOBALS['report']['yb_titles'],$round,$mywin,$userInfo1['name'],$ywin,$userInfo2['name'],$mywin,$myhero1.$youhero1);
		if ($tend == 1 && $round == 1) {
		   $yokcontent .='<table border="1" cellpadding="0" cellspacing="0" bordercolor="#FFFFFF" width="505">';
		   $yokcontent .=sprintf($GLOBALS['report']['warming'], '我方首轮交战既全军覆没，没有获得任何情报！');
		   $yokcontent .='</table>';
		} else {
		   $yokcontent .=sprintf($GLOBALS['report']['b_adtroopss'], '我方军情',$youw);
		   $yokcontent .=sprintf($GLOBALS['report']['b_adtroopss'], '敌方军情',$myw);
		}
		$yratebase = log($dcount);
		$yratebase *= $yratebase * $yratebase;
		$yrate = mt_rand(0, 8000);
		$yrate = $mtroop['battlefieldid'] > 0 ? 0 : $rate;
		if ($yrate < $yratebase) {
			$ygoodsid = getOpenDefaultGoodsResult($resistuid, 101, 1);
			addGoods($resistuid, $ygoodsid['type'], '1', '1');
			$ygetgoods = sprintf($GLOBALS['report']['goods'], $ygoodsid['name'], 1);
		}
		$ycon =sprintf($GLOBALS['report']['get'],$prestige4,$exp2,$d_wounded*100);
		if ($catch) {
			$ycon .= $catch;
		}
		if ($losthero) {
			$ycon .= $losthero;
		}
		$myactgoodnames = Get_usetroop_goods($uid,$cid,$resistcid,0);
		$ycon .=$ygetgoods;
		$ycon .=$myactgoodnames;
		$ycons =sprintf($GLOBALS['report']['detectreport'], '战斗收获');
		$ycons .=$ycon;
		$ycons .=sprintf($GLOBALS['report']['title_end']);
		if ($movecid) {
			$newcity = getNamePosition($movecid, $type, $ytroop['battlefieldid']);
			$ycons .= '<br/>我方城池被迫迁移至' . $newcity;
		}
		if ($ddiecount < $dcount) {
			$youhero2 = $resisthid > 0 ? getContentHero($resisthid) : '';
			$ycons .= sprintf($GLOBALS['report']['troopback'], '部队返回', $youhero2, $rpsoinfo4);
		} else {
			$ycons .= '<br/> 我军全军覆没，没有军队返回！';
		}
		$yokcontent .=$ycons . $GLOBALS['battle']['youend'];
		$title = $mtroop['task'] + 10;
		sendReport($resistuid, '0', $title, $cid, $resistcid, $yokcontent);
		if (!($tend == 1 && $round == 1)) {
			sql_query("update sys_report set `battleid`='{$id}' where `id`=(select LAST_INSERT_ID())");
		}
		refreshFoodArmyUse($ytroop['cid']);
	}
	if ($uid > 1000) {
		refreshFoodArmyUse($mtroop['cid']);
	}
	if ($end == 1 || $resistuid > 1000) {
		$userinfos = sql_fetch_rows("select u.id,u.name as name,s.uid from sys_union u,sys_user s where u.`id`=s.`union_id` and (s.uid='{$uid}' or s.uid='{$resistuid}')");
		foreach ($userinfos as $v) {
			if ($v['uid'] == $uid) {
				$munionname = $v['name'];
				$munid = $v['id'];
			} elseif ($v['uid'] == $resistuid) {
				$yunionname = $v['name'];
				$yunid = $v['id'];
			}
		}
		if ($munid || $yunid) {
			$mc_temp = explode('（', $mcityname);
			$mcityname = array_shift($mc_temp);
			$tc_temp = explode('（', $tcityname);
			$tcityname = array_shift($tc_temp);
			$names = sql_fetch_rows("select name,uid from sys_user where `uid`='{$uid}' or `uid`='{$resistuid}'");
			foreach ($names as $v) {
				if ($v['uid'] == $uid) {
					$mname = $v['name'];
				} elseif ($v['uid'] == $resistuid) {
					$yname = $v['name'];
				}
			}
			$description = ("{$mname} " . $GLOBALS['report']['type'][$task]) . " {$yname}";
		}
		if ($munid) {
			sql_query("insert into sys_union_report(type,unionid,enemy,origincid,origincity,happencid,happencity,time,description,content) values ('1','{$munid}','{$yunionname}','{$cid}','{$mcityname}','{$resistcid}','{$tcityname}',unix_timestamp(),'{$description}','{$con}')");
		}
		if ($yunid) {
			sql_query("insert into sys_union_report(type,unionid,enemy,origincid,origincity,happencid,happencity,time,description,content) values ('2','{$yunid}','{$munionname}','{$cid}','{$mcityname}','{$resistcid}','{$tcityname}',unix_timestamp(),'{$description}','{$con3}')");
		}
	}
	sql_query("delete from bak_troops where `battleid`='{$id}'");
   if($end<>0)
	  return 0;
	else return $robArray;
 }
?>