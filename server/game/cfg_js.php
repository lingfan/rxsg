<?php
define('ROOT_PATH',dirname(dirname(__FILE__)));

require dirname(ROOT_PATH).'/vendor/autoload.php';
require_once("BattleFunc.php");
require_once("UtilsExtend.php");
require_once("HeroExpr.php");
require_once("ReportCron.php");
require_once("OLdBattleCron.php");
require_once("TaskFuncAdd.php");
//==============服务列表
   HandleTroop();//出征及返回处理
   HandleBattle();//战斗处理
   HandleHeroHexprs();//处理将领历练
   UpdateUsersCurse();//天灾、人祸、排行
   UpdateUsersTechnic();//科技更新
   UpdateUsersSoldier();//军队更新
   UpdateUsersDeinforce();//城防更新
   HandleAutoTrans();//自动运输
   HandleTrade();//商场交易
   HandleNextTask();//自动开启十常侍、讨伐董卓史诗任务
   HandleLuoYangBegin();//洛阳战斗处理
//==============功能定义 

function HandleHeroHexprs(){//处理将领历练
     $handleHeroExpr=new HeroExpr();
     $handleHeroExpr->checkHeroExpr();
    }
function HandleTroop(){
	$rows=sql_fetch_rows("select * from sys_troops where `endtime` >0 and `endtime` <= unix_timestamp() and `state` in(0,1) order by endtime asc");
	if(empty($rows)) return null;
	foreach($rows as $row){
		$uid=$row['uid'];
		$hid=$row['hid'];
		$cid=$row['cid'];
		$id=$row['id'];
		$targetcid=$row['targetcid'];
		$task=$row['task'];
		$state=$row['state'];
		$bid=$row['battlefieldid'];
		if($bid==0){
			$tempid=file_get_contents('cron/uptroop');
			if(($id*10+$state)==$tempid) continue;
			else file_put_contents('cron/uptroop', $id.$state);
		}
		$field=check_field_owner($targetcid);
		$s_province=$field['province'];
		$type=battleType($field,$task);
		$cityname=getNamePosition($cid,$type,$bid);
		$targetcityname=getNamePosition($targetcid,$type,$bid);
		$happencid=$targetcid;
		$result=1;
		if($task>1 && $task<7) checkTactics($uid,$id);
		$soldierArray=troop2array($row['soldiers']);
		$inwar=check_inwar($cid,$targetcid,$task);
		if($row['resource']<>'0' && $row['resource']<>'0,0,0,0,0,'){
			$resource=check_resource($row['resource']);
			$gold=$resource['gold'];
			$food=$resource['food'];
			$wood=$resource['wood'];
			$rock=$resource['rock'];
			$iron=$resource['iron'];
		}else $resource=$row['resource'];
		if($hid>0) $hero=getContentHero($hid);
		else $hero='';
		$okmss='';
		$content=getreporttitle($cityname,$targetcityname,$task,$state);
		if($state==0){
		    if($inwar['result']){
			    switch ($task){
				//0运输;1派遣;2侦察;3掠夺;4占领;5防守;6护家;7战场出征;8派遣;9攻击
					case 0:{
						$result=0;
						$content.=$GLOBALS['report']['title_2row']['5'];
						addCityResources($targetcid,$wood,$rock,$iron,$food,$gold);
						sql_query("update sys_troops set state=1,starttime=unix_timestamp(),endtime=pathtime+unix_timestamp(),resource=0 where id='$id'");
						$sname=getreportsoldier($soldierArray);
						$content.=sprintf($GLOBALS['report']['troopback'],'运输军队',$hid>0?getContentHero($hid):'',$sname);
						if(is_array($resource)){
							$content.=sprintf($GLOBALS['report']['resource'],'携带资源',$gold,$food,$wood,$rock,$iron);
						}
					break;}
					case 1:{
						if($inwar['relation']==0&&$field['type']==0&&$uid==$field['uid']){//是自己的城池
							if($hid>0){
								if(cityHasHeroPosition($uid,$targetcid)){
									$content.=$GLOBALS['report']['title_2row']['4'];
									change_hero($uid,$cid,$hid,'0',$uid,$targetcid);
									addCitySoldiers($targetcid,$soldierArray,'1');
									if(is_array($resource)){
										addCityResources($targetcid,$wood,$rock,$iron,$food,$gold);
										$okmss.=sprintf($GLOBALS['report']['resource'],'携带资源',$gold,$food,$wood,$rock,$iron);
									}
									sql_query("delete from sys_troops where `id`='$id'");
								}else{
									$content.=$GLOBALS['report']['title_2row']['6'];
									$result=0;
									sql_query("update sys_troops set state=1,starttime=unix_timestamp(),endtime=pathtime+unix_timestamp() where id='$id'");
								}
							}else{
								$content.=$GLOBALS['report']['title_2row']['4'];
								addCitySoldiers($targetcid,$soldierArray,'1');
								if(is_array($resource)){
									addCityResources($targetcid,$wood,$rock,$iron,$food,$gold);
									$okmss.=sprintf($GLOBALS['report']['resource'],'携带资源',$gold,$food,$wood,$rock,$iron);
								}
								sql_query("delete from sys_troops where `id`='$id'");
							}
						}else{//是盟友或是自己的野地
							if($inwar['relation']==0){
								$targetrow=sql_fetch_one("select * from sys_troops where `uid`='$uid' and `cid`='$cid' and `targetcid`='$targetcid' and `state`=4 order by hid desc limit 1");
								$canin=($targetrow&&!($targetrow['hid']>0&&$hid>0))?1:0;
								if($canin){
									$newsoilders=add_soldier($row['soldiers'],$targetrow['soldiers']);
									if(is_array($resource)){
										$resource=addResource($resource,$targetrow['resource']);
										$okmss.=sprintf($GLOBALS['report']['resource'],'携带资源',$gold,$food,$wood,$rock,$iron);
									}else $resource=$targetrow['resource'];
								}
							}
							if($hid>0) sql_query("update sys_city_hero set `state`='4' where `hid`='$hid'");
							if($canin){//($newsoilders<>$row['soldiers']){
								sql_query("update sys_troops set `hid`=GREATEST('$hid','$targetrow[hid]'),`soldiers`='$newsoilders',`resource`='$resource',`people`='$row[people]'+'$targetrow[people]',`fooduse`='$row[fooduse]'+'$targetrow[fooduse]' where id='$targetrow[id]'");
								sql_query("delete from sys_troops where `id`='$id'");
							}else sql_query("update sys_troops set `state`=4 where `id`='$id'");
							$content.=$GLOBALS['report']['title_2row']['4'];
						}
						$sname=getreportsoldier($soldierArray);
						$content.=sprintf($GLOBALS['report']['troopback'],'派遣到达',$hero,$sname);
						$content.=$okmss;
					break;}
					case 2:{
						$result=0;
						if($field['uid']<1000) checkSodier($field);
						$targetrow=sql_fetch_one("select * from sys_troops where `state`=4 and `id`<>'$id' and (`targetcid`='$targetcid' or `cid`='$targetcid') order by endtime asc limit 1");
						if($type>0 && $type<4){
							$targetInfo=defcityadd($field['uid'],$targetcid,$type,$id,1);
							$patrol=$targetInfo['patrol'];
						}else{
							if($targetrow){
								$targetInfo=battleAdd($targetrow,$type,0,1);
								$patrol=$targetInfo['patrol'];
							}
						}
						if(!$patrol) $patrol=0;
						$rsoldiers=$targetInfo['soldiers'];
						if($patrol>0){//有猴子，战斗
							$myInfo=battleAdd($row,$type,0,1);
							$attack='1,3,'.$soldierArray['3'];
							$resist='1,3,'.$patrol;
							$mid = sql_insert("insert into mem_maneuver (`state`,`attacksoldiers`,`resistsoldiers`,`attackadd`,`resistadd`) values ('0','$attack','$resist','$myInfo[info]','$targetInfo[info]')");
							$res=upManeuver($mid,1);
							$end=$res['end'];
							$round=$res['round'];
							$over_cnt=$patrol-$res['resistdie'];
							$targeArray=troop2array($rsoldiers);
							$act_over_array=getArraySub($soldierArray,array(3=>$res['attackdie']));
							$tar_over_array['3']=$over_cnt;
							if($type>0 && $type<4){
								addCitySoldier($targetcid,3,0-$res['resistdie']);
							}else{
								$tar_over_soldier=array2troop($tar_over_array);
								sql_query("update sys_troops set `soldiers`='$tar_over_soldier' where `id`='$targetrow[id]'");
							}
						}else{
							$end=0;
							$round=1;
						}
						$mcityname=getNamePosition($cid,$type,$row['battlefieldid']);
						$tcityname=getNamePosition($targetcid,$type,0);
						$user=sql_fetch_rows("select * from sys_user where `uid`='$uid' or `uid`='$field[uid]'");
						if($user['0']['uid']==$uid){$userInfo1=$user['0'];$userInfo2=$user['1'];}
						else {$userInfo1=$user['1'];$userInfo2=$user['0'];}
						if($userInfo2['uid']==895) $userInfo2['name']='山贼';
						//取出出发城池科技等级
						switch($end){
							case 0:{
								$myw='胜利';
								$youw='失败';
							}
							case 2:{
								if($field['type']==0) {
								  completeTask($uid,133);
								  $r_cidtype=sql_fetch_one_cell("select type from sys_city where  `cid`='$targetcid'");
								  if($r_cidtype>0) sql_query("replace into sys_user_goal (`uid`,`gid`) values ('$uid',3101467)");
								}
								else completeTask($uid,132);
								$te_level=sql_fetch_one_cell("select level from sys_city_technic where `tid`=7 and `cid`='$cid'");
								if($field['type']>0){//是野地
									if($te_level)
										if($te_level<3) $te_level=6;
										elseif($te_level<6) $te_level=7;
										elseif($te_level<9) $te_level+=2;
										else $te_level=10;
								}
								if($end==2){
									$te_level=floor($te_level/2);
									$myw='平局';
								    $youw='平局';
								}
								$okkcd='';
								$binfouser='';
								if($te_level>=1){//1、3级
									$resources=sql_fetch_one("select * from mem_city_resource where `cid`='$targetcid'");
									if($resources){
										$resourceArray=array(floor($resources['gold']),floor($resources['food']),floor($resources['wood']),floor($resources['rock']),floor($resources['iron']),floor($resources['people']),floor($resources['morale']));
										$rtype=$GLOBALS['report']['resources'];
										foreach($resourceArray as $bid=>$v){
											if(($bid<1||$bid>4)&&$te_level>2||$bid>0&&$bid<5){
											 $name=$rtype[$bid];
											 $okkcd.=sprintf($GLOBALS['report']['b_count'],$name,$v);
											}
										}
										$binfouser.=sprintf($GLOBALS['report']['b_sbinfo'],'资源状况','资源','数量',$okkcd);
									}
								}
								if($te_level>=2){
								    $okkcd='';
									$buildings=sql_fetch_rows("select * from sys_building where `cid`='$targetcid' order by bid,level desc");
									if($buildings){
										$name=$GLOBALS['report']['building'];
										$comma=array();
										$bid=array();
										foreach($buildings as $buid){
											$level[$buid['bid']].=$comma[$buid['bid']];
											$level[$buid['bid']].=$buid['level'];
											$comma[$buid['bid']]=",";
										}
										foreach($level as $bid=>$v){
											if($bid<5){//2级
												$okkcd.=sprintf($GLOBALS['report']['b_count'],$name[$bid],$v);
											}elseif($te_level>=4){//4级
										        $okkcd.=sprintf($GLOBALS['report']['b_count'],$name[$bid],$v);
											}
										}
										$binfouser.=sprintf($GLOBALS['report']['b_sbinfo'],'建筑状况','建筑','等级',$okkcd);
									}
								}
								if($te_level>=5){//5级
								    $okkcd='';
									$defences=sql_fetch_rows("select * from sys_city_defence where `cid`='$targetcid'");
									if($defences){
										$name=$GLOBALS['battle']['patrol_report_defence'];
										foreach($defences as $v){
											if($v['count']>0) {
											  $okkcd.=sprintf($GLOBALS['report']['b_count'],$name[$v['did']],$v['count']);
											}
										}
										$binfouser.=sprintf($GLOBALS['report']['b_sbinfo'],'城防状况','城防','数量',$okkcd);
									}
								}
								if($te_level>=6){//6级
								    $okkcd='';
									if($field['type']>0){
										$temp=sql_fetch_one_cell("select soldiers from sys_troops where `state`=4 and (`cid`='$targetcid' or `targetcid`='$targetcid') limit 1");
										if($temp) $solds=troop2array($temp);
									}else{
										$temp=sql_fetch_rows("select * from sys_city_soldier where `cid`='$targetcid' and `count`>0");
										if($temp){
											foreach($temp as $v){
												$solds[$v['sid']]=$v['count'];
											}
										}
									}
									if($solds){
										$name=$GLOBALS['battle']['patrol_report_soldier'];
										foreach($solds as $sid=>$cnt){
											if($cnt>0){
												if($te_level>=7) {
											     $okkcd.=sprintf($GLOBALS['report']['b_count'],$name[$sid],$cnt);
												}
												else {
												  $okkcd.=sprintf($GLOBALS['report']['b_count'],$name[$sid],count_desc($cnt));
												}
											}
										}
										$binfouser.=sprintf($GLOBALS['report']['b_sbinfo'],'军队状况','军队','数量',$okkcd);
									}
								}
								if($te_level>=7){//7级
								    $okkcd='';
									$mengyous=sql_fetch_rows("select * from sys_troops where `state`=4 and `targetcid`='$targetcid'");
									if($mengyous){//有盟友的军队驻扎
										$name=$GLOBALS['battle']['patrol_report_soldier'];
										foreach($mengyous as $mengyou){
											$sold=$mengyou['soldiers'];
											$soldArray = troop2array($sold);
											foreach($soldArray as $sid=>$cnt){
											  $okkcd.=sprintf($GLOBALS['report']['b_count'],$name[$sid],$count);
											}
											$binfouser.=sprintf($GLOBALS['report']['b_sbinfo'],'盟军状况','盟友军队','数量',$okkcd);
										}
									}
								}
								if($te_level>=8){//8级
								    $okkcd='';
									$has_heros=sql_fetch_rows("select * from sys_city_hero where `cid`='$targetcid'");
									if($has_heros){
										foreach($has_heros as $v){
										  $okkcd.=sprintf($GLOBALS['report']['b_counth'],$v['name'],$v['level'],$v['loyalty']);
										}
										$con.=sprintf($GLOBALS['report']['table'],$con2);
										$binfouser.=sprintf($GLOBALS['report']['b_sbinfoh'],'将领状况','将领','等级','忠诚',$okkcd);
									}
								}
								if($te_level>=9){//9级
								    $okkcd='';
									$technics=sql_fetch_rows("select * from sys_city_technic where `cid`='$targetcid'");
									if($technics){
										$name=$GLOBALS['report']['technics'];
										foreach($technics as $v){
											$okkcd.=sprintf($GLOBALS['report']['b_count'],$name[$v['tid']],$v['level']);
										}
										$binfouser.=sprintf($GLOBALS['report']['b_sbinfo'],'科技状况','科技','等级',$okkcd);
									}
								}
								if($te_level>=10){//10级
								    $okkcd='';
									if($field['uid']>1000){
										$last_time=sql_fetch_one_cell("select lastupdate from sys_online where `uid`='$field[uid]'");
										if(time()-$last_time<=10) $status="在线";
										else $status="离线";
										$last_login=sql_fetch_one_cell("select from_unixtime($last_time)");
										$okkcd.=sprintf($GLOBALS['report']['b_count'],$status,$last_login);
										$binfouser.=sprintf($GLOBALS['report']['b_sbinfo'],'玩家动态','动态','最后活动时间',$okkcd);
									}
								}
								if($act_over_array) $soldiers=array2troop($act_over_array);
								else $soldiers=array2troop($soldierArray);
								sql_query("update sys_troops set `soldiers`='$soldiers',state=1,starttime=unix_timestamp(),endtime=pathtime+unix_timestamp() where `id`='$id'");
								sql_query("update sys_city_hero set `state`=2 where `hid`='$hid'");
							break;}
							case 1:{
							    $myw='失败';
								$youw='胜利';
								$mresult='<td width="50" height="24" class="LoseRed">失败</td>';
								$yresult='<td width="50" class="WinGreen">胜利</td>';
								sql_query("delete from sys_troops where `id`='$id'");
								sql_query("update sys_city_hero set `state`=0 where `hid`='$hid'");
							break;}
						}
						$content1.=getreporttitle($cityname,$targetcityname,$task,$state,0);
						$okcontent=getreporttitle($cityname,$targetcityname,$task,$state,0);
						if($patrol){
							$content.=$GLOBALS['report']['title_2row']['8'];
							$content1.=$GLOBALS['report']['title_2row']['10'];
							$con1.=sprintf($GLOBALS['report']['round'],$round,$GLOBALS['battle']['result'][$end]);
							$tend=$end==0?1:($end==1?0:2);
							$comm.=sprintf($GLOBALS['report']['round'],$round,$GLOBALS['battle']['result'][$tend]);
							$con1.=sprintf($GLOBALS['report']['td_table'],$userInfo1['name'],$mresult,$userInfo2['name'],$yresult);
							$comm.=sprintf($GLOBALS['report']['td_table_y'],$userInfo1['name'],$mresult,$userInfo2['name'],$yresult);
							$rehero=$hid>0?getContentBattleHero($hid):'';
							$rehero.=$targetInfo['hid']>0?getContentBattleHero($targetInfo['hid']):'';
							$con1.=sprintf($GLOBALS['report']['outside'],$rehero);
							$comm.=sprintf($GLOBALS['report']['outside'],$rehero);
							$okcontent.=sprintf($GLOBALS['report']['b_titles'],'1',$myw,$userInfo1['name'],$myw,$userInfo2['name'],$youw,$rehero);
						}else{
							$content.=$GLOBALS['report']['title_2row']['3'];
							$content1.=$GLOBALS['report']['title_2row']['9'];
							$okcontent.=$GLOBALS['report']['title_2row']['3'];
						}
						if($patrol>0){
						    $rarmynums1='';
							foreach($soldierArray as $sid=>$cnt){
								$sname=$GLOBALS['battle']['patrol_report_soldier'][$sid];
								if($sid==3) $lostcnt=$res['attackdie'];
								else $lostcnt=0;
								$rpsoinfo1.=sprintf($GLOBALS['report']['s_count'],$sname,$cnt,$lostcnt);
								$rpsoinfo3.=sprintf($GLOBALS['report']['b_count'],$sname,$cnt-$lostcnt);
								$rarmynums1.=sprintf($GLOBALS['report']['armynums'],$sname,$cnt,$lostcnt);
							}
							$rarmynums2='';
							if($rsoldiers) foreach($targeArray as $sid=>$cnt){
								$sname=$GLOBALS['battle']['patrol_report_soldier'][$sid];
								if($sid==3) $lostcnt=$res['resistdie'];
								else $lostcnt=0;
								$rpsoinfo2.=sprintf($GLOBALS['report']['s_count'],$sname,$cnt,$lostcnt);
								$rpsoinfo4.=sprintf($GLOBALS['report']['b_count'],$sname,$cnt-$lostcnt);
								$rarmynums2.=sprintf($GLOBALS['report']['armynums'],$sname,$cnt,$lostcnt);
							}
							if($end==1 && $round==1) {
							  $con1.=sprintf($GLOBALS['report']['warming'],'我方首轮交战既全军覆没，没有获得任何情报！');
							  $okcontent.=sprintf($GLOBALS['report']['warming'],'我方首轮交战既全军覆没，没有获得任何情报！');
							}
							else{
								$con1.=$GLOBALS['report']['s_title'];
								if($end==2) {$rpsoinfo2="";$rarmynums2="";}
								$con1.=sprintf($GLOBALS['report']['soldier'],$rpsoinfo1,$rpsoinfo2);
								$okcontent.=sprintf($GLOBALS['report']['b_adtroops'],'我方军情',$rarmynums1);
								$okcontent.=sprintf($GLOBALS['report']['b_adtroops'],'敌方方军情',$rarmynums2);
							}
							if($tend==1 && $round==1) $comm.=sprintf($GLOBALS['report']['warming'],'我方首轮交战既全军覆没，没有获得任何情报！');
							else{
								$comm.=$GLOBALS['report']['s_title'];
								if($end==2) $rpsoinfo4="";
								$comm.=sprintf($GLOBALS['report']['soldier'],$rpsoinfo1,$rpsoinfo2);
							}
						
						}
						$content.=sprintf($GLOBALS['report']['table'],$con1);
						$content1.=sprintf($GLOBALS['report']['table'],$comm);
						$okcontent.=$binfouser;
						$content=$okcontent;
					break;}
					case 3:{}
					case 9:{}
					case 4:{
						 if(sql_check("select * from sys_troops where state='3' and targetcid='$targetcid' and `battleid`<>'$row[battleid]'")){
							 sql_query("update sys_troops set state=2 where `id`='$id'");
							 file_put_contents('cron/uptroop', $id."2");
						    } else{
							  if($type<4 && $field['uid']<1000) {$hascreat=checkSodier($field);}
							  if($row['targettroopid']>0) 
							     $targetrow=sql_fetch_one("select * from sys_troops where `state`=4 and `battleunionid`<>$row[battleunionid] and `id`='$row[targettroopid]'");
							   else 
							     $targetrow=sql_fetch_one("select * from sys_troops where `state`=4 and `id`<>'$id' and (`targetcid`='$targetcid' or `cid`='$targetcid') order by endtime asc limit 1");
							  if($targetrow){ 
							     $field['uid']=$targetrow['uid'];
								}
							    else if($type==4){
								 sql_query("update sys_troops set `cid`='$targetcid',`state`=4,`battleid`=0 where `id`='$id'");
								 $content.="目标已离开或被消灭，没有发生战斗。";
								 $sname=getreportsoldier($soldierArray);
								 $content.=sprintf($GLOBALS['report']['troopback'],'战场军队',$hid>0?getContentHero($hid):'',$sname);
								 if(is_array($resource)){
									 $content.=sprintf($GLOBALS['report']['resource'],'携带资源',$gold,$food,$wood,$rock,$iron);
								    }
								 resetBattleFieldUid($cid);
								 $bresult=resetBattleFieldUid($targetcid);
								 $content.="<br/>军队进入".$targetcityname."。";
								 $title=getReportTitleType($task,$state);
								 sendReport($uid,'0',$title,$cid,$happencid,$content);
								 return;
							    }else if($field['type']>0){
								 $content.="野地空无一人，未发生战斗！";
								 $heros=sql_fetch_one("select * from sys_city_hero where `cid`='$targetcid' limit 1");
                                 if($heros) throwHeroField($heros);
							     if($task==3){
								      sql_query("update sys_troops set state=1,starttime=unix_timestamp(),endtime=pathtime+unix_timestamp() where id='$id'");
								      sql_query("update sys_city_hero set `state`=2 where `hid`='$hid'");
								      $taskid=$field['level']+121;
									  completeTask($uid,$taskid);
									  $content.="<br/>军队正在返回".$cityname."。";
									}else if($task==4){
                                      if(checkFeildCount($cid)){
										 sql_query("update mem_world set `updatetime`=unix_timestamp(),`ownercid`='$cid' where `wid`='$field[wid]'");
										 sql_query("update sys_troops set `state`=4 where `id`='$id'");
										 updateFieldResourceAdds($cid);
										 sql_query("update sys_city_hero set `state`='4' where `hid`='$hid'");
										 $content.="<br/>军队进入".$targetcityname."。";
										 if($uid>1000){
										     refreshFoodArmyUse($cid);
										     check_city_resource($uid,$cid);
										    }
										}else{//满了
                                         sql_query("update sys_troops set `state`=1,`starttime`=unix_timestamp(),`endtime`=unix_timestamp()+`pathtime` where `id`='$id'");
										 sql_query("update sys_city_hero set `state`='2' where `hid`='$hid'");
										 $content.=sprintf($GLOBALS['report']['warming'],"野地达到上限，无法占领更多野地！");
										 $content.="<br/>军队正在返回".$cityname."。";
									    }
									  //更新野地任务
									  if($field['type']==1) completeTask($uid,168);
									  if($field['level']>0){
										  $taskid=$field['level']+152;
										  completeTask($uid,$taskid);
									    } 
									}
								  $title=getReportTitleType($task,$state);
								  sendReport($uid,'0',$title,$cid,$happencid,$content);
								  return;
								}
							  $battleid=sql_insert("insert into sys_battle(type,starttime,cid,attackuid,resistuid,attacktroop,result) values ('$type',unix_timestamp(),'$targetcid','$uid','$field[uid]','$id','3')");
							  if($type>0 && $type<4){
							     $targetInfo=defcityadd($field['uid'],$targetcid,$type,$battleid);
								 //$getdefhid=choseHero($targetcid);
							     $resistdefence=$targetInfo['defence'];
							     $walllevel=$targetInfo['wall'];
								 $citytype=sql_fetch_one_cell("select type from sys_city where `cid`='$targetcid'");
								 $wallhp=$citytype>0?$walllevel*2000000:$walllevel*1000000;
								}else {
								  $targetInfo = battleAdd($targetrow,$type,$battleid);
							      $targetarmynum = gettargetarmysoldiers($targetInfo['soldiers']);
								  $attackgetarmynum = gettargetarmysoldiers($row['soldiers']);
							      //if($row['people']>($targetarmynum/0.95)){
								  if(($attackgetarmynum> $targetarmynum) && $type<4){
								      $content.='大军压境之下，敌人落荒而逃，未发生任何战斗！<br/>只有当你派出更少的军队时对方才愿意和你战斗。';
								      $heros=sql_fetch_one("select * from sys_city_hero where `cid`='$targetcid' limit 1");
                                      if($heros) throwUsersHeroField($heros);
								      if($task==3){
									      sql_query("update sys_troops set state=1,starttime=unix_timestamp(),endtime=pathtime+unix_timestamp() where id='$id'");
								          sql_query("update sys_city_hero set `state`=2 where `hid`='$hid'");
								          $taskid=$field['level']+121;
									      completeTask($uid,$taskid);
									      $content.="<br/>军队正在返回".$cityname."。";
									    }else{
										  if(checkFeildCount($cid)){
										     sql_query("update mem_world set `updatetime`=unix_timestamp(),`ownercid`='$cid' where `wid`='$field[wid]'");
										     sql_query("update sys_troops set `state`=4 where `id`='$id'");
										     updateFieldResourceAdds($cid);
										     sql_query("update sys_city_hero set `state`='4' where `hid`='$hid'");
										     $content.="<br/>军队进入".$targetcityname."。";
										     if($uid>1000){
											     refreshFoodArmyUse($cid);
											     check_city_resource($uid,$cid);
											    }
										    }else{//满了
                                             sql_query("update sys_troops set `state`=1,`starttime`=unix_timestamp(),`endtime`=unix_timestamp()+`pathtime` where `id`='$id'");
										     sql_query("update sys_city_hero set `state`='2' where `hid`='$hid'");
										     $content.=sprintf($GLOBALS['report']['warming'],"野地达到上限，无法占领更多野地！");
										     $content.="<br/>军队正在返回".$cityname."。";
									        }
									      if($field['type']==1) completeTask($uid,168);
									      if($field['level']>0){
										     $taskid=$field['level']+152;
										     completeTask($uid,$taskid);
									        } 
										}
								      $title=getReportTitleType($task,$state);
								      sendReport($uid,'0',$title,$cid,$happencid,$content);
								      return;
								    }
								}
							  $hstate=$targetInfo['hstate'];
							  $dsoldiers=$targetInfo['soldiers'];
							  $myInfo=battleAdd($row,$type,$battleid);
							  $fieldrange=getBattleRange($myInfo['info'],$targetInfo['info']);
							  $myInfo['pos']=changePos($myInfo['pos'],$fieldrange,$battleid);
							  $resisttroops.=$targetInfo['id'];
							  sql_query("insert into bak_troops(`id`,`uid`,`cid`,`hid`,`targetcid`,`task`,`state`,`starttime`,`pathtime`,`noback`,`soldiers`,`resource`,`battleid`,`people`,`fooduse`,`lastrun`,`lastlock`,`lastTempt`,`lastacc`,`battlefieldid`,`startcid`,`battleunionid`,`bid` ) values( '$id','$uid','$cid','$hid','$targetcid','$task','$state','$row[starttime]','$row[pathtime]','$row[noback]','$row[soldiers]','$row[resource]','$battleid','$row[people]','$row[fooduse]','$row[lastrun]','$row[lastlock]','$row[lastTempt]','$row[lastacc]','$row[battlefieldid]','$row[startcid]','$row[battleunionid]','$row[bid]') ");
							  sql_query("insert into bak_troops(`id`,`uid`,`cid`,`hid`,`targetcid`,`task`,`state`,`starttime`,`pathtime`,`noback`,`soldiers`,`resource`,`battleid`,`people`,`fooduse`,`lastrun`,`lastlock`,`lastTempt`,`lastacc`,`battlefieldid`,`startcid`,`battleunionid`,`bid` ) values('$targetInfo[id]','$field[uid]','$targetcid','$targetInfo[hid]','$targetrow[targetcid]','$targetrow[task]','$targetrow[state]','0','0','0','$targetInfo[soldiers]','$targetrow[resource]','$battleid','$targetrow[people]','$targetrow[fooduse]','$targetrow[lastrun]','$targetrow[lastlock]','$targetrow[lastTempt]','$targetrow[lastacc]','$targetrow[battlefieldid]','$targetrow[startcid]','$targetrow[battleunionid]','$targetrow[bid]') ");
							  if($task>=9&&$field['uid']>1000 || $task<9) sql_query("UPDATE sys_city_hero SET state='3' WHERE `hid`='$hid' or `hid`='$targetInfo[hid]'");
							    else sql_query("UPDATE sys_city_hero SET state='3' WHERE `hid`='$hid'");
							  sql_query("update sys_troops set `state`='3',`battleid`='$battleid' where `id`='$id' or `id`='$targetInfo[id]'");
							  sql_query("UPDATE mem_world SET state='1' WHERE `wid`='field[wid]'");
							  sql_query("replace into mem_battle(id,type,nexttime,round,attackcid,attackhid,attacksoldiers,attackpos,attackadd,resistcid,resisthid,resistsoldiers,resistpos,resistadd,resistdefence,wallhp,walllevel,fieldrange,state,level,attackstartcid,resiststartcid) values('$battleid','$type',unix_timestamp()+20,'1','$cid','$hid','$row[soldiers]','$myInfo[pos]','$myInfo[info]','$targetcid','$targetInfo[hid]','$dsoldiers','$targetInfo[pos]','$targetInfo[info]','$resistdefence','$wallhp','$walllevel','$fieldrange','$hstate','$field[level]','$row[startcid]','$targetrow[startcid]')");
							  sql_query("UPDATE sys_battle set `resisttroops`='$resisttroops',`resistdefence`='$resistdefence' where `id`='$battleid'");
							}
						   return;
					break;}
					case 7:{
						sql_query("update sys_troops set `cid`='$targetcid',`state`='4' where id='$id'");
						$sname=getreportsoldier($soldierArray);
						$content.=sprintf($GLOBALS['report']['troopback'],'战场军队',$hid>0?getContentHero($hid):'',$sname);
						if(is_array($resource)){
							$content.=sprintf($GLOBALS['report']['resource'],'携带资源',$gold,$food,$wood,$rock,$iron);
						}
					break;}
					case 8:{
						sql_query("update sys_troops set `cid`='$targetcid',`state`=4,`battleid`=0 where `id`='$id'");
						$sname=getreportsoldier($soldierArray);
						$content.=sprintf($GLOBALS['report']['troopback'],'战场军队',$hid>0?getContentHero($hid):'',$sname);
						if(is_array($resource)){
							$content.=sprintf($GLOBALS['report']['resource'],'携带资源',$gold,$food,$wood,$rock,$iron);
						}
						resetBattleFieldUid($cid);
						$bresult=resetBattleFieldUid($targetcid);
						if($row['bid']==1001 && $targetcid%1000==367 && $bresult['unionid']==1){//扫清虎牢关的黄巾军
							completeTask($uid,'101982');
							completeTask($uid,'101966');
						}
						$event=sql_fetch_rows("select * from cfg_battle_event where `triggerid`='$targetcid'%1000 and `bid`='$row[bid]' and `unionid`='$row[battleunionid]'");
						$battlelevel=sql_fetch_one_cell("select level from sys_user_battle_field where `id`='$row[battlefieldid]'");
						if($event){
							$comma1="";
							$comma2="";
							foreach($event as $v){
								if($v['triggertype']==2) $cando=sql_check("select * from sys_troops where `battlefieldid`='$row[battlefieldid]' and `battleunionid`<>'$row[battleunionid]'");
								else $cando=1;
								if($cando){
									if($v['targetid1']>50000){//任务
										$goalid=sql_fetch_one_cell("select id from cfg_task_goal where `tid`='$v[targetid1]'");
										if($goalid){
											if($v['targettype']==3){
												if(checkBattleResult($bid,$v['bid'])){
													sql_query("update sys_user_battle_field set `finishtime`=unix_timestamp(),`winner`='$row[battleunionid]' where `id`='$row[battlefieldid]'");
													sql_query("insert into sys_user_goal (`uid`,`gid`) select `uid`,'$goalid' from sys_user_battle_state where `battlefieldid`='$row[battlefieldid]' and `battleunionid`='$row[battleunionid]' on duplicate key update `gid`=values(gid)");
												}
											}elseif($v['targettype']==1){//增加任务
												sql_query("delete from sys_user_goal where uid='$uid' and gid='$goalid'");
												$sql="insert into sys_user_task (uid,tid,state) select `uid`,'$v[targetid1]',0 from sys_user_battle_state where `battlefieldid`='$row[battlefieldid]' and `unionid`='$row[battleunionid]' on duplicate key update state=0";
												sql_query($sql);
												$sql="";
											}
											else completeTask($uid,$goalid);
										}
									}elseif($v['targetid1']>1000){//增兵
										$addhid.=$comma1;
										$addhid.=$v['targetid1'];
										$comma1=",";
									}
									if($v['msg']){//发消息
									}
									if($v['targetid2']>1000){//消失将领
										$loshid.=$comma2;
										$loshid.=$v['targetid2'];
										$comma2=",";
									}
								}
							}
							if($addhid){
								$newadd=sql_fetch_rows("select * from cfg_battle_troop where bid='$row[bid]' and hid in ($addhid)");
								if($newadd){
									sql_query("delete from sys_troops where battlefieldid='$row[battlefieldid]' and hid in ($addhid)");
									$comma="";
									foreach($newadd as $v){
										$soldiers=createSoldier($v['npcvalue'],$v['soldiers'],$battlelevel);
										$tcid=battleid2cid($row['battlefieldid'],$v['xy']);
										$sql.=$comma;
										$sql.="($tcid,0,'$v[hid]','$soldiers',4,'$v[drop]','$v[rate]',$row[battlefieldid],'$v[unionid]',$v[bid])";
										$comma=",";
									}
									$sql="insert into sys_troops (cid,uid,hid,soldiers,state,`drop`,rate,battlefieldid,battleunionid,bid) values ".$sql;
									//file_put_contents("cron/log1.txt",$sql."\r\n",FILE_APPEND);
									sql_query($sql);
									$sql="";
								}
							}
							if($loshid) sql_query("delete from sys_troops where battlefieldid='$row[battlefieldid]' and hid in ($loshid)");
						}
						$unionid=resetBattleFieldUid($targetcid);
						if($unionid==$row['battleunionid']){
							$tunionid=$unionid==3?4:3;
							$turn=sql_fetch_one("select winpoint,losepoint from sys_battle_city where `cid`='$targetcid'");
							sql_query("update sys_battle_winpoint set `point`=LEAST(10000,`point`+$turn[winpoint]) where `battlefieldid`='$row[battlefieldid]' and `unionid`='$unionid'");
							sql_query("update sys_battle_winpoint set `point`=GREATEST(0,`point`-$turn[losepoint]) where `battlefieldid`='$row[battlefieldid]' and `unionid`='$tunionid'");
						}
					break;}
					default:{}
				}
				$content.=$GLOBALS['report']['br'];
				if($end&&$end<>1||!$end){
					if($result)$content.="<br/>军队进入".$targetcityname."。";
					else $content.="<br/>军队正在返回".$cityname."。";
				}else $content.="<br/>我军全军覆没！";
				//军队吃粮
				if($uid>1000){refreshFoodArmyUse($cid);check_city_resource($uid,$cid);}
				if($field['uid']>1000){
					if($task==0 && $uid<>$field['uid']) sendReport($field['uid'],'0','1',$cid,$happencid,$content);
					elseif($task==2&&$tend<>1) sendReport($field['uid'],'0','12',$cid,$happencid,$content1);
					refreshFoodArmyUse($field['ownercid']);
					check_city_resource($field['uid'],$field['ownercid']);
				}
			}else{
				sql_query("update sys_troops set state=1,starttime=unix_timestamp(),endtime=pathtime+unix_timestamp() where id='$id'");
				sql_query("update sys_troops set `state`=0 where `state`=2 and `targetcid`='$targetcid' order by `endtime` asc limit 1");
				$content.=$inwar['msg'];
				$content.="<br/>军队正在返回".$cityname."。";
			}
		}
		else{
			if($row['task']>7 && $cid<>$row['startcid']){
				sql_query("update sys_troops set `state`=4,`battleid`=0 where `id`='$id'");
				$sname=getreportsoldier($soldierArray);
				$content.=sprintf($GLOBALS['report']['troopback'],'战场军队',$hid>0?getContentHero($hid):'',$sname);
				if(is_array($resource)){
					$content.=sprintf($GLOBALS['report']['resource'],'携带资源',$gold,$food,$wood,$rock,$iron);
				}
				resetBattleFieldUid($cid);
			}else{
				if($hid>0){
					change_hero($uid,$cid,$hid,'0');
				}
				addCitySoldiers($cid,$soldierArray,'1');
				$sname=getreportsoldier($soldierArray);
				$content.=sprintf($GLOBALS['report']['troopback'],'军队返回',$hero,$sname);
				if(is_array($resource)){
					addCityResources($cid,$wood,$rock,$iron,$food,$gold);
					$content.=sprintf($GLOBALS['report']['resource'],'携带资源',$gold,$food,$wood,$rock,$iron);
				}
				sql_query("delete from sys_troops where `id`='$id'");
				refreshFoodArmyUse($cid);
				$content.=$GLOBALS['report']['br'];
				$content.="<br/>军队进入".$cityname."。";
			}
		}
		$title=getReportTitleType($task,$state);
		sendReport($uid,'0',$title,$cid,$happencid,$content);
	}
}
function del($id){
	sql_query("delete from mem_battle where `id`='$id'");
	sql_query("delete from mem_battle_tactics where `battleid`='$id'");
}
function battleTactic($id){
	$tactics=sql_fetch_rows("SELECT * FROM `mem_battle_tactics` WHERE `battleid`='$id'");
	if($tactics){
		foreach($tactics as $tactic){
			$ret[$tactic['attack']][$tactic['stype']]['action']=$tactic['action'];
			$ret[$tactic['attack']][$tactic['stype']]['target']=$tactic['target'];
			$ret[$tactic['attack']][$tactic['stype']]['action2']=$tactic['action2'];
			$ret[$tactic['attack']][$tactic['stype']]['target2']=$tactic['target2'];
		}
	}
	return $ret;
}
function battleInfo($param){
	$id=$param['id'];
	$type=$param['type'];
	$attacksoldiers=$param['attacksoldiers'];
	$resistsoldiers=$param['resistsoldiers'];
	$attackpos=$param['attackpos'];
	$resistpos=$param['resistpos'];
	$attackadd=$param['attackadd'];
	$resistadd=$param['resistadd'];
	$defence=$param['resistdefence'];
	$wallhp=$param['wallhp'];
	$acnt=explode(",",$attacksoldiers);
	if($resistsoldiers) $rcnt=explode(",",$resistsoldiers);
	if($attackpos) $apos=explode(",",$attackpos);
	if($resistpos) $rpos=explode(",",$resistpos);
	if($attackadd) $aadd=explode(";",$attackadd);
	if($resistadd) $radd=explode(";",$resistadd);
	//攻击方数量位置
	$anum=array_shift($acnt);
	for ($i=0;$i<$anum;$i++)
	{
		$sid=array_shift($acnt)*100;
		$cnt[$sid]=array_shift($acnt);
		if($cnt[$sid]>0){
			$ret[$sid]['cnt']=$cnt[$sid];
			if(!$attackpos) $ret[$sid]['pos']=$param['fieldrange']-100;
		}
	}
	if($apos){
		$aposnum=array_shift($apos);
		for ($i=0;$i<$aposnum;$i++)
		{
			$sid=array_shift($apos)*100;
			$pos=array_shift($apos);
			if($cnt[$sid]>0) $ret[$sid]['pos']=$pos;
		}
	}
	//防守方数量位置
	if($rcnt){
		$rnum=array_shift($rcnt);
		for ($i=0;$i<$rnum;$i++)
		{
			$sid=array_shift($rcnt);
			$cnt[$sid]=array_shift($rcnt);
			if($cnt[$sid]>0){
				$ret[$sid]['cnt']=$cnt[$sid];
				if(!$resistpos) $ret[$sid]['pos']=100;
			}
		}
	}
	//城防数量
	if($type==1 && $defence){
		$defenceArray=defence2array($defence);
		foreach($defenceArray as $sid=>$v){
			if($v['cnt']>0){
				$sid*=10000;
				$cnt[$sid]=$v['cnt'];
				$ret[$sid]['cnt']=$cnt[$sid];
				$ret[$sid]['oldcnt']=$v['oldcnt'];
				$ret[$sid]['pos']=0;
			}
		}
	}
	if($rpos){
		$rposnum=array_shift($rpos);
		for ($i=0;$i<$rposnum;$i++)
		{
			$sid=array_shift($rpos);
			$pos=array_shift($rpos);
			if($cnt[$sid]>0) $ret[$sid]['pos']=$pos;
		}
	}
	//攻击方加成
	if($aadd){
		foreach($aadd as $v){
			$info=explode(",",$v);
			$sid=array_shift($info)*100;
			if($cnt[$sid]>0){
				$ret[$sid]['type']=array_shift($info);
				$ret[$sid]['shoot']=array_shift($info);
				$ret[$sid]['act']=array_shift($info);
				$ret[$sid]['def']=array_shift($info);
				$ret[$sid]['blood']=array_shift($info);
				$ret[$sid]['speed']=array_shift($info);
				$ret[$sid]['ap']=array_shift($info);
				$ret[$sid]['dp']=array_shift($info);
				$ret[$sid]['hp']=array_shift($info);
				$ret[$sid]['uid']=array_shift($info);
				$ret[$sid]['state']='1';
			}
		}
	}else{
		$soArray=troop2array($attacksoldiers);
		$solType=checkSoldierType($soArray);
		if(!$solType) $solType=0;
		$soldierinfo=sql_fetch_rows("select * from cfg_soldier where `sid` in ($solType)");
		$soldinfo=soldierinfo($soldierinfo);
		$comma="";
		$info="";
		$stype=$GLOBALS['sid']['type'];
		foreach($soArray as $sid=>$cnt){
			if($cnt>0){
				$sidtype=$stype[$sid];
				$newsid=$sid*100;
				$info.=$comma;
				$info.=$sid.",".$sidtype.",".$soldinfo[$sid]['range'].",".$soldinfo[$sid]['ap'].",".$soldinfo[$sid]['dp'].",".$soldinfo[$sid]['hp'].",".$soldinfo[$sid]['speed'].",".$soldinfo[$sid]['ap'].",".$soldinfo[$sid]['dp'].",".$soldinfo[$sid]['hp'];
				$comma=";";
				$ret[$newsid]['type']=$sidtype;
				$ret[$newsid]['shoot']=$soldinfo[$sid]['range'];
				$ret[$newsid]['act']=$soldinfo[$sid]['ap'];
				$ret[$newsid]['def']=$soldinfo[$sid]['dp'];
				$ret[$newsid]['blood']=$soldinfo[$sid]['hp'];
				$ret[$newsid]['speed']=$soldinfo[$sid]['speed'];
				$ret[$newsid]['ap']=$soldinfo[$sid]['ap'];
				$ret[$newsid]['dp']=$soldinfo[$sid]['dp'];
				$ret[$newsid]['hp']=$soldinfo[$sid]['hp'];
				$ret[$newsid]['uid']=0;
				$ret[$newsid]['state']='1';
			}
		}
		sql_query("update mem_maneuver set `attackadd`='$info' where `id`='$id'");
	}
	//防守方加成
	if($radd){
		foreach($radd as $v){
			$info=explode(",",$v);
			$sid=array_shift($info);
			if($cnt[$sid]>0){
				$ret[$sid]['type']=array_shift($info);
				$ret[$sid]['shoot']=array_shift($info);
				$ret[$sid]['act']=array_shift($info);
				$ret[$sid]['def']=array_shift($info);
				$ret[$sid]['blood']=array_shift($info);
				$ret[$sid]['speed']=array_shift($info);
				$ret[$sid]['ap']=array_shift($info);
				$ret[$sid]['dp']=array_shift($info);
				$ret[$sid]['hp']=array_shift($info);
				$ret[$sid]['uid']=array_shift($info);
				$ret[$sid]['state']='0';
			}
		}
	}else{
		if($resistsoldiers){
			$soArray=troop2array($resistsoldiers);
			$solType=checkSoldierType($soArray);
			if(!$solType) $solType=0;
			$soldierinfo=sql_fetch_rows("select * from cfg_soldier where `sid` in ($solType)");
			$soldinfo=soldierinfo($soldierinfo);
			$comma="";
			$info1="";
			$stype=$GLOBALS['sid']['type'];
			foreach($soArray as $sid=>$cnt){
				if($cnt>0){
					$sidtype=$stype[$sid];
					$info1.=$comma;
					$info1.=$sid.",".$sidtype.",".$soldinfo[$sid]['range'].",".$soldinfo[$sid]['ap'].",".$soldinfo[$sid]['dp'].",".$soldinfo[$sid]['hp'].",".$soldinfo[$sid]['speed'].",".$soldinfo[$sid]['ap'].",".$soldinfo[$sid]['dp'].",".$soldinfo[$sid]['hp'];
					$comma=";";
					$ret[$sid]['type']=$sidtype;
					$ret[$sid]['shoot']=$soldinfo[$sid]['range'];
					$ret[$sid]['act']=$soldinfo[$sid]['ap'];
					$ret[$sid]['def']=$soldinfo[$sid]['dp'];
					$ret[$sid]['blood']=$soldinfo[$sid]['hp'];
					$ret[$sid]['speed']=$soldinfo[$sid]['speed'];
					$ret[$sid]['ap']=$soldinfo[$sid]['ap'];
					$ret[$sid]['dp']=$soldinfo[$sid]['dp'];
					$ret[$sid]['hp']=$soldinfo[$sid]['hp'];
					$ret[$sid]['uid']=0;
					$ret[$sid]['state']='0';
				}
			}
			sql_query("update mem_maneuver set `resistadd`='$info1' where `id`='$id'");
		}
	}
	if($wallhp>0){
		$sid=180000;
		$ret[$sid]['shoot']=0;
		$ret[$sid]['act']=0;
		$ret[$sid]['def']=0;
		$ret[$sid]['blood']=1;
		$ret[$sid]['speed']=0;
		$ret[$sid]['ap']=0;
		$ret[$sid]['dp']=8000;
		$ret[$sid]['hp']=1;
		$ret[$sid]['uid']=0;
		$ret[$sid]['pos']=100;
		$ret[$sid]['cnt']=$wallhp;
		$ret[$sid]['type']=18;
		$ret[$sid]['state']='0';
	}
	return $ret;
}
function cmp($a,$b){
	if($a["speed"]==$b["speed"]) return 0;
	return ($a["speed"]>$b["speed"])?-1:1; 
}
function action($action,$state){
	$ret=0;
	switch ($action){
		case 1:{$ret=1;break;}
		case 2:{$ret=0;break;}
		case 3:{$ret=-1;break;}
	}
	if($state==1) $ret*=-1;
	return $ret;
}
function checkPos($state,$pos,$endpos,$min,$max,$fieldrange){
	$ret=$endpos;
	switch ($state){
		case 0:{//防守
			$ret=($endpos>$min)?$min:(($endpos<0)?0:$endpos);
			$ret=($ret<0)?0:(($ret>$fieldrange)?$fieldrange:$ret);
		break;}
		case 1:{//进攻
			$ret=($endpos<$max)?$max:(($endpos>$fieldrange)?$fieldrange:$endpos);
			$ret=($ret<0)?0:(($ret>$fieldrange)?$fieldrange:$ret);
		break;}
	}
	return $ret;
}
function getTarget($infos,$attack,$target){
	foreach($infos as $sid=>$info){
		if($info['type']==$target && $info['state']<>$attack['state'] && $attack['shoot']>=abs($attack['pos']-$info['pos'])){
			return $sid;
		}
	}
	if(empty($targetInfo)){
		$i=0;
		$targetsid=array();
		foreach($infos as $sid=>$info){
			if($info['state']<>$attack['state'] && $sid<10000 && $attack['shoot']>=abs($attack['pos']-$info['pos'])){
				$targetsid[$i]=floor($sid);
				$i++;
			}
		}
		if($targetsid){
			//if($attack['type']==14) $ret=$targetsid[0];
			//else
			$ret=$targetsid[mt_rand(0,$i-1)];
		}
		if(!$ret&&$infos['180000']&&$attack['state']==1) $ret='180000';
	}
	return $ret;
}
function checkBattleEnd_over($i,$j,$k){
	if($i==0 && ($j>0 || $k>0)) $end=1;
	elseif($i>0 && $j==0 && $k==0) $end=0;
	elseif($i>0 && ($j>0 || $k>0)) $end=2;
	else $end=3;
	return $end;
}
function checkBattleEnd($state){
	switch($state){
		case 0:{$end=0;
		break;}
		case 1:{$end=1;
		break;}
		case 2:{$end=2;
		break;}
	}
	return $end;
}
function get_task_goods($uid,$value,$type){
	if($type<4){
		if(mt_rand(0,1)>0){
			$count=(floor($value/5)>2000)?2000:floor($value/5);
			$things=sql_fetch_one_cell("select count from sys_things where `uid`='$uid' and `tid`='$type'");
			$count=($things>6000)?floor($count/10):floor($count*(1-$things/6000));
			sql_query("insert into sys_things (`uid`,`tid`,`count`) values ('$uid','$type','$count') on duplicate key update `count`=`count` + '$count'");
			return sprintf($GLOBALS['report']['goods'],$GLOBALS['report']['renwu'][$type],$count);
		}
	}else{
		if(mt_rand(0,10)<=$value){
			sql_query("insert into sys_things (`uid`,`tid`,`count`) values ('$uid','$type','1') on duplicate key update `count`=`count` + '1'");
			return sprintf($GLOBALS['report']['goods'],$GLOBALS['report']['renwu'][$type],'1');
		}
	}
}
function get_m_usertask_goods($uid,$value,$tid,$type){//type=1黄巾物品; type=2 十常待物品; type=3董卓物品.；type=4野地活动物品
      switch($type){
	     case 1:{
		      if($tid<4){//1-4 
		          if(mt_rand(0,1)>0){
			         $count=(floor($value/5)>2000)?2000:floor($value/5);
					 $things=sql_fetch_one_cell("select count from sys_things where `uid`='$uid' and `tid`='$tid'");
					 $count=($things>60000)?floor($count/10):floor($count*(1-$things/60000));
					 if($count==0) $count==100;
					 sql_query("insert into sys_things (`uid`,`tid`,`count`) values ('$uid','$tid','$count') on duplicate key update `count`=`count` + '$count'");
			         return sprintf($GLOBALS['report']['goods'],$GLOBALS['report']['renwu'][$tid],$count);
		            }
	            }else if($tid==4){
		          if(mt_rand(0,10)<=$value){
			         sql_query("insert into sys_things (`uid`,`tid`,`count`) values ('$uid','$tid','1') on duplicate key update `count`=`count` + '1'");
			         return sprintf($GLOBALS['report']['goods'],$GLOBALS['report']['renwu'][$tid],'1');
		            }
	            }
				 break;
			}
	     case 2:{
		      if($tid>19 && $tid<23){ //20--23 是十常待物品id
		          if(mt_rand(0,1)>0){
			         $count=(floor($value/5)>2000)?2000:floor($value/5);
			         $things=sql_fetch_one_cell("select count from sys_things where `uid`='$uid' and `tid`='$tid'");
			         $count=($things>60000)?floor($count/10):floor($count*(1-$things/60000));
			         if($count==0) $count==100;
					 sql_query("insert into sys_things (`uid`,`tid`,`count`) values ('$uid','$tid','$count') on duplicate key update `count`=`count` + '$count'");
			         return sprintf($GLOBALS['report']['goods'],$GLOBALS['report']['renwu'][$tid],$count);
		            }
	            }else if($tid==23){
		          if(mt_rand(0,10)<=$value){
			         sql_query("insert into sys_things (`uid`,`tid`,`count`) values ('$uid','$tid','1') on duplicate key update `count`=`count` + '1'");
			         return sprintf($GLOBALS['report']['goods'],$GLOBALS['report']['renwu'][$tid],'1');
		            }
	            }
			     break;
			}
		 case 3:{
		      if($tid>61001 && $tid<61005){ //61001--61004 是董卓物品id
		          if(mt_rand(0,1)>0){
			         $count=(floor($value/5)>2000)?2000:floor($value/5);
			         $things=sql_fetch_one_cell("select count from sys_things where `uid`='$uid' and `tid`='$tid'");
			         $count=($things>60000)?floor($count/10):floor($count*(1-$things/60000));
			         if($count==0) $count==100;
					 sql_query("insert into sys_things (`uid`,`tid`,`count`) values ('$uid','$tid','$count') on duplicate key update `count`=`count` + '$count'");
			         return sprintf($GLOBALS['report']['goods'],$GLOBALS['report']['renwu'][$tid],$count);
		            }
	            }else if($tid==61001){
		          if(mt_rand(0,10)<=$value){
			         sql_query("insert into sys_things (`uid`,`tid`,`count`) values ('$uid','$tid','1') on duplicate key update `count`=`count` + '1'");
			         return sprintf($GLOBALS['report']['goods'],$GLOBALS['report']['renwu'][$tid],'1');
		            }
	            }
			     break;
			}
		  case 4:{
		     if($tid>11112 && $tid<11118){ //11113--11117 是野地活动物品id
		          if(mt_rand(0,1)>0){
			         $count=(floor($value/5)>20000)?20000:floor($value/5);
			         $things=sql_fetch_one_cell("select count from sys_things where `uid`='$uid' and `tid`='$tid'");
			         $count=($things>160000)?floor($count/10):floor($count*(1-$things/60000));
			         if($count==0) $count==5000;
					 sql_query("insert into sys_things (`uid`,`tid`,`count`) values ('$uid','$tid','$count') on duplicate key update `count`=`count` + '$count'");
			         return sprintf($GLOBALS['report']['goods'],$GLOBALS['report']['renwu'][$tid],$count);
		            }
	            }
			  break;
			}
	    }
    }
function Get_usetroop_goods($uid,$cid,$tragetcid,$tasktype){//$tasktype值是 0 野地 ,1、2城池，4战场.
     $goodname ='';
	 $acts=sql_fetch_into_arrays("select actid, rate from cfg_act where type='4980' and starttime<=unix_timestamp() and endtime>=unix_timestamp()");
	 if(empty($acts)) return $goodname;
	 $actnum=count($acts['actid']);
	 $actrates = mt_rand(0,$actnum-1);
	 $actid=$acts['actid'][$actrates];
	 $actrate=$acts['rate'][$actrates];
	 $mynpctarget_wid = cid2wid($tragetcid);
	 $mynpcw_info = sql_fetch_one("select * from mem_world where wid='$mynpctarget_wid'");//用于判断是不是城池战
	 $g_cnt=1;
	 $actnum=340;
	 if($mynpcw_info['type']==0){ //日常任务 秣兵历马 物品掉落
	     $targetuid = sql_fetch_one_cell("select uid from sys_city where cid='$tragetcid'");
		 if($mynpcw_info['province']==10 || $targetuid==725) {$actid=273;$g_cnt=mt_rand(1,5);$actnum=110;}
		  else if($targetuid==518){$actid=289;$g_cnt=mt_rand(1,5);$actnum=140;}
	    } 
	 if (isMyLucky($actrate,$actnum)){//4980为掠夺占领活动
          $troopacts=sql_fetch_into_arrays("select * from cfg_box_details where srctype=4000 and srcid='$actid'");		  
	      if(empty($troopacts)) return $goodname;
		  $troopactnum=count($troopacts['srcid']);
		  $tpactrates = mt_rand(0,$troopactnum-1);
		  $srcid = $troopacts['srcid'][$tpactrates];
		  $srctype = $troopacts['srctype'][$tpactrates];
		  $sort = $troopacts['sort'][$tpactrates];
		  $type = $troopacts['type'][$tpactrates];
		  $count = $troopacts['count'][$tpactrates] * $g_cnt;
		  
		  $uname = sql_fetch_one_cell("select name from sys_user where uid='$uid'");
		  $uname = addslashes($uname);
		  if($sort==2){
		     $goodnames .= sql_fetch_one_cell("select name from cfg_goods where gid='$type'");
			 if($type==(-100)){
			     sql_query("update sys_user set money=money+'".$count."' where uid='".$uid."'");
			     sql_query("insert into log_gift (uid,gid,count,time,type) values ('$uid','$type','$count',unix_timestamp(),'3')");
			    }else
			      addGoods($uid,$type,$count,3);
			}
			else{
			  $goodnames .= sql_fetch_one_cell("select name from cfg_things where tid='$type'");
			  addThings($uid,$type,$count,3);
			}
		  $targetname = getNamePosition($tragetcid,$tasktype,0);
		  $usename = sql_fetch_one_cell("select name from sys_user where uid='$uid'");
		  $msg ='恭喜【'.$usename.'】经过激战,在'.$targetname.'获得:【'.$goodnames.'*'.$count.'】';
		  $goodname='<tr> <td colspan="6" height="25" class="TextArmyCount">'.'获得:【'.$goodnames.'*'.$count.'】'.'</td></tr>';
          sendSysInform(0,1,0,300,1800,1,49151,$msg);	  
		}
	 return $goodname;
    }
function isMyLucky($luckyResult, $maxResult, $minResult=1) {
	$rand = mt_rand ($minResult, $maxResult);
	if ($luckyResult >= $rand) {
		return true;
	}
	return false;
}
function chose_hero($cid,$hid=0){
	$heros=sql_fetch_rows("select * from sys_city_hero where (`cid`='$cid' and (`state`<2 or `state`>6) and `state`<9 and `herotype`=0) or (`hid`='$hid' and `herotype`=0)");
	$i=0;
	$ratesum=0;
	if(!empty($heros)){
		foreach($heros as $hero){
			$heroArray[$i]=$hero;
			$loyaltyArray[$i]=100-$hero['loyalty'];
			$ratesum+=$loyaltyArray[$i];
			$i++;
			if($hero['loyalty']<=40){$ret[]=$hero;$hids[]=$hero['hid'];}
		}
	}
	$sumRate=0;
	$herocnt=$i;
	$rate=mt_rand(1,$ratesum);
	for($i=0;$i<$herocnt;$i++)
	{
		if($sumRate+$loyaltyArray[$i]>=$rate)
		{
			if($hids&&!in_array($heroArray[$i]['hid'],$hids)||!$hids) $ret[]=$heroArray[$i];
			break;
		}
		$sumRate=$sumRate+$loyaltyArray[$i];
	}
	return $ret;
}
function catchMoranchHero() {
      $sql = "insert into sys_hero_expr_reward (`uid`,`cid`,`hid`,`type`,`sort`,`money`,`details`,`endtime`) values('1001','363378','80213','1','1','100','1',unix_timestamp()+604800)";//七天后过期
	  sql_query($sql);
	  $id = sql_fetch_one_cell("select last_insert_id()");
	  $msg = '你的君主将已经被我抓了，要想赎回，请速速送来元宝100W！！！';
	  $msg .= sprintf($GLOBALS['heroExpr']['ok_url'],$id)."<br/>";
	  return $msg;
    }
function catchHero($uid,$cid,$hero,$type=0){
     $target_wid = cid2wid($cid);
	 $w_info = sql_fetch_one("select * from mem_world where wid='$target_wid'");         
	 $loyalty=$hero['loyalty'];
	 $catch=mt_rand(1,100);
	 if($hero['herotype']>0) $catch=2;
	 if($type<>1 && $hero['uid']>1000) $catch=0;
	 if($hero['npcid']>0){//是名将的话看是否有画像
		 $thingsname=sprintf($GLOBALS['hero']['things'],$hero['name']);
		 $hasthings=sql_fetch_one_cell("select cfg_things.tid from cfg_things,sys_things where cfg_things.name='$thingsname' and cfg_things.tid=sys_things.tid and sys_things.uid='$uid' limit 1");
		 if(empty($hasthings)) $catch=5;
	    }
	 if($loyalty<=$catch){//抓住了
		 $lostlevel=($hero['level']>40)?floor($hero['level']*0.05):0;
		 $add_point=$hero['level']-$lostlevel;
		 $affairs_add=floor($add_point/3);
		 $bravery_add=$affairs_add;
		 $wisdom_add=$add_point-$affairs_add-$bravery_add;
		 $herolevel=$hero['level']-$lostlevel;
		 $exp=sql_fetch_one_cell("select total_exp from cfg_hero_level where `level`='$herolevel'");
		 sql_query("update sys_city_hero set `uid`='$uid',`cid`='$cid',`state`=5,`level`=`level`-'$lostlevel',`exp`='$exp',`command_add_on`=0,`affairs_add`='$affairs_add',`bravery_add`='$bravery_add',`wisdom_add`='$wisdom_add',`affairs_add_on`=0,`bravery_add_on`=0,`wisdom_add_on`=0,`force_max_add_on`=0,`energy_max_add_on`=0,`speed_add_on`=0,`attack_add_on`=0,`defence_add_on`=0,`loyalty`=0 where `hid`='$hero[hid]'");
		 sql_query("replace into sys_hero_captive(hid,uid,captivetime) values ('$hero[hid]','$uid',unix_timestamp())");
		 sql_query("delete from sys_hero_armor where `hid`='$hero[hid]'");
		 sql_query("update sys_user_armor set `hid`=0 where `hid`='$hero[hid]'");
		 sql_query("replace into mem_hero_blood (`hid`) values ('$hero[hid]')");
		 $con=sprintf($GLOBALS['battle']['catchhero'],$hero['name'],$hero['level']);
		 if($hasthings) sql_query("delete from sys_things where `uid`='$uid' and `tid`=$hasthings");
		 if(sql_check("select * from sys_user_task where `uid`='$uid' and `tid`='$hasthings' and `state`=0"))
		 sql_query("replace into sys_user_goal(`uid`,`gid`) select '$uid',cfg_task_goal.id from cfg_task_goal where `tid`='$hasthings'");
		 updateCityHeroChange($uid,$cid);
		 updateCityHeroChange($hero['uid'],$hero['cid']);
	    }else{//没抓住,将在野地和城池两种情况
	      if($type==1){//占领城池
		      sql_query("update sys_city_hero set `loyalty`=`loyalty`-3 where `loyalty`>0 and `hid`='$hero[hid]'");
			  if($hero['uid']<1000)
			    sql_query("update sys_city_hero set `state`='1' where `hid`='$hero[hid]'");
		      $morales=sql_fetch_one_cell("select morale from mem_city_resource where cid='$cid'");
			  if($morales<=0) //城池民心小于0了，对于玩家城中多于一个将的处理，还没做
				   throwHeroToField($hero);
		    }
		   if($type==0){//野地战
		     if($hero['uid']<1000)
		         throwUsersHeroField($hero);
			   else{
			     sql_query("update sys_city_hero set `state`='0',`loyalty`=`loyalty`-3 where `hid`='$hero[hid]'");
				}
			}
	    }
	 return $con;
    }
function troopsBack($uid,$cid){//将和军队秒回
	$troops=sql_fetch_rows("select * from sys_troops where `uid`='$uid' and (`cid`='$cid' or `targetcid`='$cid') and `battlefieldid`=0 and `state`<>3 and `soldiers`>0");
	if(!empty($troops)){
		$comma="";
		$troopsid="";
		foreach($troops as $troop){
			if($troop['cid']==$cid){
				if($troop['hid']>0) sql_query("update sys_city_hero set `state`='0' where `hid`='$troop[hid]'");
				$soArray=troop2array($troop['soldiers']);
				addCitySoldiers($cid,$soArray,1);
			}
			$troopsid.=$comma;
			$troopsid.=$troop['id'];
			$comma=",";
		}
		sql_query("delete from sys_gather where `troopid` in ($troopsid)");
		$sql="delete from sys_troops where `uid`='$uid' and `cid`='$cid' and `battlefieldid`=0 and `state`<>3";
		sql_query("delete from sys_troops where `uid`='$uid' and `cid`='$cid' and `battlefieldid`=0 and `state`<>3");
	}
	$ownerfields=sql_fetch_rows("select wid from mem_world where ownercid='$cid'");
	if(!empty($ownerfields))
	{
		$comma="";
		$$fieldcids="";
		foreach($ownerfields as $mywid)
		{
			$fieldcids.=$comma;
			$fieldcids.=wid2cid($mywid['wid']);
			$comma=",";
		}
		sql_query("update sys_troops set `state`='1',`starttime`=unix_timestamp(),`endtime`=unix_timestamp()+`pathtime` where `targetcid` in ($fieldcids) and `state`=4");
	}
}
function movecitys($uid,$cid,$targetcid){
	$wid = cid2wid($cid);
	$targetwid = cid2wid($targetcid);
	$province = sql_fetch_one_cell("select province from mem_world where wid='$targetwid'");
	sql_query("insert into log_move_city (time,uid,fromcid,tocid) values (unix_timestamp(),'$uid','$cid','$targetcid')");
	//新建城池
	sql_query("replace into sys_city (`cid`,`uid`,`name`,`type`,`state`,`province`) select '$targetcid','$uid',`name`,'0','0','$province' from sys_city where `cid`='$cid'");
	//建筑
	sql_query("delete from sys_building where `cid`='$targetcid'");
	$sql="replace into sys_building (`cid`,`xy`,`bid`,`level`) select '$targetcid',`xy`,`bid`,case when(`state`=1) then `state`-1 when(`state`=2) then `state`+1 else `level` end from sys_building where `cid`='$cid'";
	sql_query($sql);
	$sql="replace into mem_city_resource (`cid`,`morale`,`morale_stable`,`complaint`,`tax`,`people`,`food`,`wood`,`rock`,`iron`,`gold`,`hero_fee`,`people_working`,`lastupdate`) select '$targetcid',`morale`,`morale_stable`,`complaint`,`tax`,`people`,'5000','5000','5000','5000','5000',`hero_fee`,`people_working`,unix_timestamp() from mem_city_resource where `cid`='$cid'";
	sql_query($sql);
	$sql="replace into sys_city_res_add (`cid`) values ('$targetcid')";
	sql_query($sql);
	$sql="delete from mem_city_reinforce where `cid`='$targetcid' or `cid`='$cid'";
	sql_query($sql);
	$sql="update sys_user set `lastcid`='$targetcid' where `uid`='$uid'";
	sql_query($sql);
	sql_query("update sys_city_hero set `cid`='$targetcid' where `cid`='$cid' and `uid`='$uid'");
	//修改所在地的属性
	sql_query("update mem_world set ownercid='$targetcid',type='0' where wid=".cid2wid($targetcid));
	//重新计算宝物加成
	resetCityGoodsAdd($uid,$targetcid);
	//重新检查产量
	check_city_resource($uid,$targetcid);
	//科技
	check_Technic($uid);
}
function moveGetCid(){
	$province=intval(mt_rand(1,13));
	$provinceLandCount = sql_fetch_one_cell("select count(*) from mem_world where type=1 and ownercid=0 and province='$province'");
	if ($provinceLandCount == 0)
	{
		if($oriprovince==0)
		{
			$tryCount=0;
			do
			{
				$province=intval(mt_rand(1,13));
				$provinceLandCount = sql_fetch_one_cell("select count(*) from mem_world where type=1 and ownercid=0 and province='$province'");
				$tryCount++;
			}while(($tryCount<10)&&($provinceLandCount==0));

		}
		if($provinceLandCount==0)
		{
			throw new Exception($GLOBALS['changeCityPosition']['province_is_full']);
		}
	}
	$tryCount=0;

	do
	{
		$targetwid = sql_fetch_one_cell("select wid from mem_world where type=1 and province='$province' and ownercid=0 and state=0 order by rand() limit 1");
		$tryCount++;
	}while(empty($targetwid)&&$tryCount<10);
	if(empty($targetwid)) throw new Exception($GLOBALS['changeCityPosition']['invalid_target_city']);
	$targetcid = wid2cid($targetwid);
	return $targetcid;
}
function delBuildings($cid,$level){
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
	if($level<10){
		$bid=$VALID_GRID_ARRAY[$level];
		sql_query("delete from sys_building where `cid`='$cid' and `xy`<100 and `xy` not in ($bid)");
	}
}
function check_morale($uid,$cid,$type,$morale,$complaint,$plub=5){
	if($plub==5){
		if($morale<0) $morale=0;
		$addmorale=($type>0)?(($morale-3>0)?3:$morale):0;
		$ret['can']=$morale-$addmorale==0?1:0;
	}else{
		$addmorale=0;
	}
	if($complaint>100) $complaint=100;
	$recomplaint=(($complaint+$plub)<100)?$complaint+$plub:100;
	if($plub==5) $ret['con']=sprintf($GLOBALS['battle']['invade'],$morale,$addmorale,$morale-$addmorale,$complaint,$recomplaint-$complaint,$recomplaint,$morale-$addmorale,$GLOBALS['battle']['invade_result'][$ret['can']]);
	sql_query("update mem_city_resource set `complaint`='$recomplaint',`morale`='$morale'-'$addmorale',morale_stable=GREATEST(0,LEAST(100-`tax`-`complaint`,100)) where cid='$cid'");
	$ret['morale']=$morale-$addmorale;
	$ret['addmorale']=$addmorale;
	$ret['complaint']=$recomplaint;
	$ret['addcomplaint']=$recomplaint-$complaint;
	return $ret;
}
function check_nobility($uid,$nobility){
	$nobility=getBufferNobility($uid,$nobility);
	$count=sql_fetch_one_cell("select count(cid) from sys_city where `uid`='$uid'");
	$juewei=$GLOBALS['battle']['juewei'][$nobility];
	$needjuiwei=$GLOBALS['battle']['juewei'][$count];
	$ret['can']=$nobility>=$count?1:0;
	$ret['con']=sprintf($GLOBALS['battle']['jueweiinvade'],$needjuiwei,$juewei,$GLOBALS['battle']['invade_result'][$ret['can']]);
	return $ret;
}
function check_city_type($uid,$cityInfo){
	$citytype=$cityInfo['type'];
	if($citytype<2) return array("can"=>1);
	$unionid=sql_fetch_one_cell("select union_id from sys_user where `uid`='$uid'");
	switch ($citytype){
		case 2:{//郡城
			$citys=sql_fetch_one_cell("select count(*) from sys_city,mem_world where mem_world.type=0 and mem_world.ownercid=sys_city.cid and sys_city.type=1 and mem_world.province='$cityInfo[province]' and mem_world.jun='$cityInfo[jun]'");
			if($unionid>0) $havecount=sql_fetch_one_cell("select count(*) from sys_city,sys_user,mem_world where mem_world.type=0 and  sys_city.uid=sys_user.uid and mem_world.ownercid=sys_city.cid and mem_world.province='$cityInfo[province]' and mem_world.jun='$cityInfo[jun]' and sys_city.type=1 and sys_user.union_id='$unionid'");
			else $havecount=sql_fetch_one_cell("select count(*) from sys_city,mem_world where mem_world.type=0 and sys_city.uid='$uid' and mem_world.ownercid=sys_city.cid and mem_world.province='$cityInfo[province]' and mem_world.jun='$cityInfo[jun]' and sys_city.type=1");
		break;}
		case 3:{//州城
			$citys=sql_fetch_one_cell("select count(*) from sys_city,mem_world where mem_world.type=0 and mem_world.ownercid=sys_city.cid and sys_city.type=2 and mem_world.province='$cityInfo[province]'");
			if($unionid>0) $havecount=sql_fetch_one_cell("select count(*) from sys_city,sys_user,mem_world where mem_world.type=0 and sys_city.uid=sys_user.uid and mem_world.ownercid=sys_city.cid and mem_world.province='$cityInfo[province]' and sys_city.type=2 and sys_user.union_id='$unionid'");
			else $havecount=sql_fetch_one_cell("select count(*) from sys_city,mem_world where mem_world.type=0 and sys_city.uid='$uid' and mem_world.ownercid=sys_city.cid and mem_world.province='$cityInfo[province]' and sys_city.type=2");
		break;}
		case 4:{//都城
			$citys=12;
			if($unionid>0) $havecount=sql_fetch_one_cell("select count(*) from sys_city,sys_user,mem_world where mem_world.type=0 and sys_city.uid=sys_user.uid and mem_world.ownercid=sys_city.cid and sys_city.type=3 and sys_user.union_id='$unionid'");
			else $havecount=sql_fetch_one_cell("select count(*) from sys_city,mem_world where mem_world.type=0 and sys_city.uid='$uid' and mem_world.ownercid=sys_city.cid and sys_city.type=3");
		break;}
	}
	$ret['can']=($citys/3>$havecount)?0:1;
	$result=$GLOBALS['battle']['invade_result'][$ret['can']];
	$ret['con']=sprintf($GLOBALS['battle']['cityinvade'],$citys,$GLOBALS['battle']['citytype'][$citytype-1],$GLOBALS['battle']['citytype'][$citytype-1],$havecount,$result);
	return $ret;
}
function checkFeildCount($cid){
	$guanlevel=sql_fetch_one_cell("select level from sys_building where `cid`='$cid' and bid=6");
	$fieldcount=sql_fetch_one_cell("select count(ownercid) from mem_world where `ownercid`='$cid' and `type`>0");
	if(($guanlevel-$fieldcount)>0) return true;
	return false;
}
function addCityCapture($cid,$soldiers,$add){
	$comma="";
	$sql="";
	foreach($soldiers as $sid=>$count)
	{
		$sql.=$comma;
		$sql.="('$cid','$sid','$count')";
		$comma=",";
		if ($add) $fuhao="+";
		else $fuhao="-";
	}
	$sql1="insert into mem_city_captive (`cid`,`sid`,`count`) values $sql on duplicate key update `count`=GREATEST(0,`count` $fuhao values(count))";
	sql_query($sql1);
}
function addCityWounded($cid,$soldiers,$add){
	$comma="";
	$sql="";
	foreach($soldiers as $sid=>$count)
	{
		$sql.=$comma;
		$sql.="('$cid','$sid','$count')";
		$comma=",";
		if ($add) $fuhao="+";
		else $fuhao="-";
	}
	$sql1="insert into mem_city_wounded (`cid`,`sid`,`count`) values $sql on duplicate key update `count`=GREATEST(0,`count` $fuhao values(count))";
	sql_query($sql1);
}
function addUserPrestige($uid,$value){
	sql_query("update sys_user set `warprestige`=`warprestige`+GREATEST(0,$value),`prestige`=`prestige`+GREATEST(0,$value) where `uid`='$uid'");
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
function SoBitSo($as,$bs){
	if($as==4 && $bs==7) $ret=3.6;
	elseif(($as==6||$as==13) && $bs==5) $ret=0.49;
	elseif($as==5 && $bs==4) $ret=2.2;
	elseif($as==7 && $bs==6) $ret=2.4;
	elseif($as==11 && $bs==18) $ret=15;
	elseif($as==12 && $bs==15) $ret=2.9;//$ret=1.45;
	elseif(($as==10||$as==12)&&$bs<13) $ret=0.5;
	elseif(in_array($as,array(4,7,8)) and $bs==18) $ret=2;
	else $ret=1;
	return $ret;
}
function sol2people($soldiers){
	$usepeople=$GLOBALS['soldier']['usepeople'];
	if(!is_array($soldiers)){
		$soldierArray = explode(",",$soldiers);
		$numSoldiers = array_shift($soldierArray);
		$takeSoldiers = array();
		$exp=0;
		for ($i = 0; $i < $numSoldiers; $i++)
		{
			$sid = array_shift($soldierArray);
			$cnt = array_shift($soldierArray);
			$exp+=(int)floor($cnt*$usepeople[$sid]);
		}
	}else{
		foreach($soldiers as $sid=>$cnt)
			$exp+=(int)floor($cnt*$usepeople[$sid]);
	}
	return floor($exp);
}
function upManeuver($id,$patrol=0){
	$row = sql_fetch_one("select * from mem_maneuver where id='$id' and `state`=0");
	if($row){
		$round=1;
		$param['fieldrange']=1000;
		if($round>40) $end=2;
		elseif($row['attacksoldiers']==0) $end=1;
		elseif($row['resistsoldiers']==0 && $row['wallhp']==0) $end=0;
		else{
			$row['type']=0;
			$end=3;
			$soldierInfo=battleInfo($row);
			uasort($soldierInfo, "cmp");
			foreach($soldierInfo as $sid=>$info) $posArray[$info['state']][$sid]=$info['pos'];
			while($end==3 && $round<=40){
				foreach($soldierInfo as $sid=>$attack){
					if($soldierInfo[$sid]['cnt']>0 && $end==3){
						//套用格式
						$minpos=min($posArray['1']);
						$maxpos=max($posArray['0']);
						$state=$soldierInfo[$sid]['state'];
						$speed=$soldierInfo[$sid]['speed'];
						$shoot=$soldierInfo[$sid]['shoot'];
						$cnt=$soldierInfo[$sid]['cnt'];
						$type=$soldierInfo[$sid]['type'];
						$ap=$soldierInfo[$sid]['ap'];
						$hp=$soldierInfo[$sid]['hp'];
						$dp=$soldierInfo[$sid]['dp'];
						$act=$soldierInfo[$sid]['act'];
						$def=$soldierInfo[$sid]['def'];
						$blood=$soldierInfo[$sid]['blood'];
						$pos=$soldierInfo[$sid]['pos'];
						//行动
						if($type<13 || $type==15){
							$a=action(1,$state);
							$endpos=$pos+$speed*$a;
							$endpos=checkPos($state,$pos,$endpos,$minpos,$maxpos,1000);
							$posvalue=abs($endpos-$pos);
							$soldierInfo[$sid]['pos']=$endpos;
							$posArray[$state][$sid]=$endpos;
							$tInfo=getTarget($soldierInfo,$soldierInfo[$sid],$type);
							$atype=$type==15?2:1;
							$dtype=$tInfo['type']>12?0:1;//目标类型
							//攻击,先看是否够得着
							$range=abs($endpos-$tInfo['pos']);
							if($tInfo && $range<=$shoot){
								$att=1;
								$sinact=$ap*$ap/($tInfo['ap']+$tInfo['dp'])+$act*$act/($tInfo['ap']+$tInfo['act']+$tInfo['def']+$tInfo['dp']+$tInfo['hp']+$tInfo['blood']);
								$sinact*=SoBitSo($type,$tInfo['type']);
								if($shoot>600) if($range==0) $sinact/=2;elseif($range<=$shoot/2) $sinact*=2;
								$allact=$sinact*$cnt;
								if($allact>$tInfo['blood']*$tInfo['cnt']) $allact=($tInfo['blood']+$tInfo['hp'])*$tInfo['cnt'];
								$lost=round($allact/($tInfo['blood']+$tInfo['hp']));
								$tInfo['cnt']-=$lost;
								$soldierInfo[$tInfo['sid']]['cnt']=$tInfo['cnt'];
								if($soldierInfo[$tInfo['sid']]['cnt']<=0){
									$stapetype=$state==1?0:1;
									unset($soldierInfo[$tInfo['sid']]);
									unset($posArray[$stapetype][$tInfo['sid']]);
									$has=count($posArray[$stapetype]);
									if($has==0) $end=checkBattleEnd($stapetype);
								}
							}
							//反击
							if($range<=$tInfo['shoot'] && $tInfo['cnt']>0 && $soldierInfo[$sid]['cnt']>0 && $tInfo['type']<>'18'){
								$fanji=1;
								$backsinact=$tInfo['ap']*$tInfo['ap']/($ap+$dp)+$tInfo['act']*$tInfo['act']/($ap+$act+$def+$dp+$hp+$blood);
								$backsinact*=SoBitSo($tInfo['type'],$type);
								if($tInfo['shoot']>600) if($range==0) $backsinact/=4;elseif($range<=$tInfo['shoot']/2) $backsinact/=2;
								$backallact=$backsinact*$tInfo['cnt'];
								if($backallact>($blood+$hp)*$cnt) $backallact=($blood+$hp)*$cnt;
								$backlost=round($backallact/($blood+$hp));
								$soldierInfo[$sid]['cnt']-=$backlost;
								$cnt=$soldierInfo[$sid]['cnt'];
								if($soldierInfo[$sid]['cnt']<=0){
									unset($soldierInfo[$sid]);
									unset($posArray[$state][$sid]);
									$has=count($posArray[$state]);
									if($has==0) $end=checkBattleEnd($state);
								}
							}
						}
						if($end<>3) break;
					}
					if($end<>3) break;
				}
				if($end<>3) break;
				$round++;
			}
			foreach($soldierInfo as $sid=>$v){
				if($v['cnt']>0&&$sid){
					if($sid>99){
						$sid=$sid/100;
						$a_over_so[$sid]=$v['cnt'];
					}else{
						$d_over_so[$sid]=$v['cnt'];
					}
				}
			}
			$actArray=troop2array($row['attacksoldiers']);
			$defArray=troop2array($row['resistsoldiers']);
			if($a_over_so) $a_Array=getArraySub($actArray,$a_over_so);
			else $a_Array=$actArray;
			if($d_over_so) $d_Array=getArraySub($defArray,$d_over_so);
			else $d_Array=$defArray;
			$attackdie=array2troop($a_Array);
			$resistdie=array2troop($d_Array);
			if(!$patrol) sql_query("update mem_maneuver set `state`=1,`result`='$end',`round`='$round'-1,`attackdie`='$attackdie',`resistdie`='$resistdie' where `id`='$id'");
			else{
				sql_query("delete from mem_maneuver where `id`='$id'");
				if($end>2) $end=2;
				$ret['end']=$end;
				$ret['attackdie']=$a_Array['3']>0?$a_Array['3']:0;
				$ret['resistdie']=$d_Array['3']>0?$d_Array['3']:0;
				$ret['round']=$round>40?40:$round;
				return $ret;
			}
		}
	}
}
function defcityadd($uid,$cid,$type,$id,$task=0){
	$hero=check_city_ground_hero($cid);
	$hid=choseHero($cid);//$hero['hid'];
	$dtactics=take_dtactics($uid,$cid,$type,$id,$task);//$ret[sid][action],target,cnt,type
	$soldinfo=array_pop($dtactics);
	//$soldinfo=soldierinfo($dtactics);
	if($type==1){
		$wall_level=sql_fetch_one_cell("select level from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_WALL);
		$shootadd=$wall_level*0.05;
		$defadd=1+$wall_level*0.1;
	}else{
		$shootadd=0;
		$defadd=1;
	}
	$add=getAdd($cid,$hid,'0');
	$add['def']*=$defadd;
	//陷阵等宝物加成
	$goodsBufs=sql_fetch_rows("select buftype from mem_user_buffer where (`buftype`=5 or `buftype`=6) and `endtime`>unix_timestamp() and `uid`='$uid'");
	$goods_att=$goods_def=0;
	if($goodsBufs){
		foreach($goodsBufs as $v){
			switch ($v['buftype']){
				case 5:{
					$goods_att+=0.1;
				break;}
				case 6:{
					$goods_def+=0.1;
				break;}
			}
		}
	}
	foreach($dtactics as $sid=>$v) $count+=$v['cnt'];
	if($count>0){
		$add['command']*=100/$count;
		if($add['command']>1) $add['command']=1;
		$shoot_add=getshootadd($cid);
		$shoot_add+=$shootadd;
		$comma="";
		$comma1="";
		$comma2="";
		$i=0;
		foreach($dtactics as $sid=>$tactic){
			$sidtype=$tactic['type'];
			//if($sid<9999) $sidtype= sql_fetch_one_cell("select type from cfg_soldier where sid='$sid'");
			if($sidtype<13 || ($sidtype>44&&$sidtype<51)){
				if($task) $ret['patrol']=$tactic['cnt'];
				$dsoldierArray[$sid]=$tactic['cnt'];
				$pos.=$comma;
				if($tactic['action']>1) $pos.=$sid.",0";
				else //$pos.=$sid.",".$GLOBALS['battle']['pos'][$tactic['action']];
				  $pos.=$sid.",50";
				$comma=",";
				$i++;
			}else{
			   	$defenceArray[$sid/10000]=$tactic['cnt'];
				if($sidtype<>15) $shoot_add=1;
			}
			if($sidtype==13 || $sidtype==14)
			//速度恢复
			$soldinfo[$sid]['speed']/=2;
			//宝物加成
			//$soldinfo[$sid]['ap']*=$goods_att;
			//$soldinfo[$sid]['dp']*=$goods_def;
			//应用加成
			$shoot=$soldinfo[$sid]['range']>=600?$soldinfo[$sid]['range']*$shoot_add:$soldinfo[$sid]['range'];
			$act=$soldinfo[$sid]['ap']*$add['command']*($add['act']/100+$goods_att);
			$def=$soldinfo[$sid]['dp']*$add['command']*($add['def']/100+$goods_def);
			$blood=$soldinfo[$sid]['hp']*$add['command']*$add['blood'];
			//$blood=$soldinfo[$sid]['hp'];
			$speed=$soldinfo[$sid]['speed'];
			//（1+科技/10*50%)+基础速度*马速%
			//QQ 基础速度+基础速度*驾驭+马速*10*统率
			//步 基础速度+基础速度*行军+马速*10*统率
			if($speed>=500) $speed=floor($speed+$speed*$add['speed2']+$add['speed']*2*$add['command']-1);
			else $speed=floor($speed+$speed*$add['speed1']+$add['speed']*2*$add['command']-1);
			//$speed*=1+$add['speed1']/2+$add['speed2']/100*$add['command'];
			//$speed=$soldinfo[$sid]['speed']>500?$add['speed2']*$soldinfo[$sid]['speed']:$add['speed1']*$soldinfo[$sid]['speed'];
			$info.=$comma2;
			$info.=$sid.",".$sidtype.",".$shoot.",".$act.",".$def.",".$blood.",".$speed.",".$soldinfo[$sid]['ap'].",".$soldinfo[$sid]['dp'].",".$soldinfo[$sid]['hp'].",".$uid;
			//战术
			$sql.=$comma1;
			$sql.="('$id','0','$tactic[type]','$tactic[action]','$tactic[target]','$tactic[action2]','$tactic[target2]')";
			$sidType.=$comma1;
			$sidType.=$sid;
			$comma1=",";
			$comma2=";";
			$ret['value']+=$tactic['cnt']*(($act+$def+$blood)*2+$soldinfo[$sid]['ap']+$soldinfo[$sid]['dp']+$soldinfo[$sid]['hp']);
		}
		if(!$task) sql_query("replace into mem_battle_tactics(battleid,attack,stype,action,target,action2,target2) values $sql");
		$ret['pos']=$i.",".$pos;
		$ret['info']=$info;
		if($dsoldierArray) $ret['soldiers']=array2troop($dsoldierArray);
		else $ret['soldiers']="0";
		if($type==1){
			if($defenceArray){
				$ret['defence']=array2troop($defenceArray,1);
				addCityDefences($cid,$defenceArray,'0');
			}
			$ret['wall']=$wall_level;
		}
		if(!$task&&is_array($dsoldierArray)) addCitySoldiers($cid,$dsoldierArray,'0');
		if(!$task) $ret['id']=sql_insert("insert into sys_troops(`uid`,`cid`,`hid`,`targetcid`,`task`,`state`,`soldiers`) values('$uid','$cid','$hid','$cid','6','3','$ret[soldiers]')");
		$ret['sidType']=$sidType;
	}
	else{
		$ret['pos']=0;
		$ret['info']="";
		$ret['soldiers']=0;
		$ret['sidType']=0;
		if($type==1){
			if($defenceArray){
				$ret['defence']=array2troop($defenceArray,1);
				addCityDefences($cid,$defenceArray,'0');
			}
			$ret['wall']=$wall_level;
		}
		if(!$task) $ret['id']=sql_insert("insert into sys_troops(`uid`,`cid`,`hid`,`targetcid`,`task`,`state`,`soldiers`) values('$uid','$cid','$hid','$cid','6','3','0')");
	}
	$ret['hid']=$hid;
	$ret['hstate']=$hero['state'];
	return $ret;
}
function battleAdd($troop,$type,$id,$task=0)
{
	if($troop){
		$troopid=$troop['id'];
		$uid=$troop['uid'];
		$hid=$troop['hid'];
		$cid=$type<4?$troop['cid']:$troop['startcid'];
		if($type==1&&$troop['task']>5){
			$wall_level=sql_fetch_one_cell("select level from sys_building where `cid`='$cid' and `bid`=".ID_BUILDING_WALL);
			$shootadd=1+$wall_level*0.05;
			$defadd=$wall_level*0.1;
		}else{
			$shootadd=0;
			$defadd=1;
		}
		//陷阵等宝物加成
		$goodsBufs=sql_fetch_rows("select buftype from mem_user_buffer where (`buftype`=5 or `buftype`=6) and `endtime`>unix_timestamp() and `uid`='$uid'");
		$goods_att=$goods_def=0;
		if($goodsBufs){
			foreach($goodsBufs as $v){
				switch ($v['buftype']){
					case 5:{
						$goods_att+=0.1;
					break;}
					case 6:{
						$goods_def+=0.1;
					break;}
				}
			}
		}
		$soldierArray=troop2array($troop['soldiers']);
		$state=$troop['state']==0?1:0;
		if($troop['state']==3) $state=$troop['task']<5?1:0;
		$tactics=take_atactics($uid,$troopid,$soldierArray,$type);
		$solType=array_pop($tactics);
		if(!$solType) $solType=0;
		$soldierinfo=sql_fetch_rows("select * from cfg_soldier where `sid` in ($solType)");
		$soldinfo=soldierinfo($soldierinfo);
		$add=getAdd($cid,$hid,'0');
		$add['def']*=$defadd;
		foreach($tactics as $tactic) $count+=$tactic['cnt'];
		if($count>0){
			$add['command']*=100/$count;
			if($add['command']>1) $add['command']=1;
			$shoot_add=getshootadd($cid);
			$shoot_add+=$shootadd;
			$comma="";
			$comma1="";
			$i=0;
			foreach($tactics as $sid=>$tactic){
				if(!$task||$task&&$sid==3){
					if($task) $ret['patrol']=$tactic['cnt'];
					$sidtype=$GLOBALS['sid']['type'][$sid];
					$soldier.=$comma;
					$soldier.=$sid.",".$tactic['cnt'];
					$pos.=$comma;
					$pos.=$sid.",".$GLOBALS['battle']['pos'][$tactic['action']];
					//速度恢复
					$soldinfo[$sid]['speed']/=2;
					//应用加成
					$shoot=$soldinfo[$sid]['range']>=600?$soldinfo[$sid]['range']*$shoot_add:$soldinfo[$sid]['range'];
					$att=$soldinfo[$sid]['ap']*$add['command']*($add['act']/100+$goods_att);
					$def=$soldinfo[$sid]['dp']*$add['command']*($add['def']/100+$goods_att);
					$blood=$soldinfo[$sid]['hp']*$add['command']*$add['blood'];
					//$blood=$soldinfo[$sid]['hp'];
					$speed=$soldinfo[$sid]['speed'];
					//（1+科技/10*50%)+基础速度*马速%=基础速度*(1+科技/10+驾驭)
					//QQ 基础速度+基础速度*驾驭+马速*10*统率
					//步 基础速度+基础速度*行军+马速*10*统率
					if($speed>=500) $speed=$speed+$speed*$add['speed2']+$add['speed']*2*$add['command']-1;
					else $speed=$speed+$speed*$add['speed1']+$add['speed']*2*$add['command']-1;
					//$speed*=1+$add['speed1']/2+$add['speed2']/100*$add['command'];
					//$speed=$soldinfo[$sid]['speed']>500?$add['speed2']*$soldinfo[$sid]['speed']:$add['speed1']*$soldinfo[$sid]['speed'];
					$info.=$comma1;
					$info.=$sid.",".$sidtype.",".$shoot.",".$att.",".$def.",".$blood.",".$speed.",".$soldinfo[$sid]['ap'].",".$soldinfo[$sid]['dp'].",".$soldinfo[$sid]['hp'].",".$uid;
					//战术
					$sql.=$comma;
					$sql.="('$id','$state','$tactic[type]','$tactic[action]','$tactic[target]','$tactic[action2]','$tactic[target2]')";
					$sidType.=$comma;
					$sidType.=$sid;
					$comma=",";
					$comma1=";";
					$i++;
					$ret['value']+=$tactic['cnt']*(($att+$def+$blood)*2+$soldinfo[$sid]['ap']+$soldinfo[$sid]['dp']+$soldinfo[$sid]['hp']);
				}
			}
			if(!$task) sql_query("replace into mem_battle_tactics(battleid,attack,stype,action,target,action2,target2) values $sql");
			$ret['pos']=$i.",".$pos;
			$ret['info']=$info;
			$ret['soldiers']=$i.",".$soldier;
			$ret['id']=$troopid;
			$ret['sidType']=$sidType;
		}
		$ret['hid']=$hid;
	}else{
		if($task) $ret['patrol']=0;
		$ret['pos']=0;
		$ret['info']="";
		$ret['soldiers']=0;
		$ret['sidType']=0;
	}
	$ret['hstate']='4';
	return $ret;
}
function getBattleRange($minfo,$yinfo)
{
	$msinfo=array_merge(explode(";",$minfo),explode(";",$yinfo));
	$range=0;
	foreach($msinfo as $v){
		$msid=explode(",",$v);
		$shoot=$msid['2'];
		$shoot=$shoot>$range?$shoot:$range;
		$range=$shoot;
	}
	$range=$shoot+299;
    return $range;
}
function changePos($pos,$fieldrange,$id){
	$posarray=explode(",",$pos);
	$posnum=array_shift($posarray);
	$comma="";
	for($i=0;$i<$posnum;$i++){
		$sid=array_shift($posarray)*100;
		$postemp=array_shift($posarray);
		$postemp=$fieldrange-$postemp;
		$_SESSION['bid'][$id][$sid]['pos']=$postemp;
		$sid/=100;
		$newpos.=$comma;
		$newpos.=$sid.",".$postemp;
		$comma=",";
	}
	$newpos=$posnum.",".$newpos;
	return $newpos;
}
function addCityDefences($cid,$soldiers,$add)
{
	$comma="";
	foreach($soldiers as $sid=>$count)
	{
		$sql.=$comma;
		$sql.="('$cid','$sid','$count')";
		$comma=",";
		if ($add) $fuhao="+";
		else $fuhao="-";
	}
	$sql1="insert into sys_city_defence (`cid`,`did`,`count`) values $sql on duplicate key update `count`=case when((`count` $fuhao values(count))>4294967000) then 0 else (`count` $fuhao values(count)) end";
	sql_query($sql1);
}
//取出城内所有军队
function getCitySoldiers($cid){
	$soldiers=sql_fetch_rows("select * from sys_city_soldier where `cid`='$cid' and `count`>0");
	if($soldiers){
		foreach($soldiers as $v){
			$ret[$v['sid']]=$v['count'];
		}
		return $ret;
	}
}
//取得军队模糊数量
function count_desc($count){
	$desc=$GLOBALS['report']['count_desc'];
	if($count<10) $ret=$desc['0'];
	elseif(10<=$count && $count<25) $ret=$desc['1'];
	elseif(25<=$count && $count<100) $ret=$desc['2'];
	elseif(100<=$count && $count<250) $ret=$desc['3'];
	elseif(250<=$count && $count<1000) $ret=$desc['4'];
	elseif(1000<=$count && $count<2500) $ret=$desc['5'];
	elseif(2500<=$count && $count<5000) $ret=$desc['6'];
	elseif(5000<=$count && $count<10000) $ret=$desc['7'];
	elseif(10000<=$count) $ret=$desc['8'];
	return $ret;
}
 ?>