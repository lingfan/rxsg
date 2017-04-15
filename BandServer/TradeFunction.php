<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
</html>
<?php
if(!isset($_SESSION)){
  session_start();
}
  require_once("dbinc.php");
  $myuid=$_SESSION['myuid'];//买方uid 
  $mycid=sql_fetch_one_cell("select lastcid from sys_user where uid='$myuid'"); 
  if(isset($_POST['sellarmor'])){//===========================出售装备代码
	  $armorprices=getstrisnum($_POST['armorprice']);
	  $armorsid= $_POST['surearmorsid'];
	  if($armorprices==0 || empty($armorsid)) exit;
      $sainfo = sql_fetch_one_cell("select sid from sys_user_armor where uid='$myuid' and sid='$armorsid'");	
      if(empty($sainfo)){echo '<script type="text/javascript">alert("装备已经售出!");</script>';exit;}	
	  sql_query("insert into  sys_user_armor_trade (`sid`,`uid`,`armorid`,`hp`,`hp_max`,`price`,`strong_level`,`strong_value`,`embed_pearls`,`embed_holes`,`trade`,`active_special`,`strong_times`,`combine_level`,`best_quality`)
	        (select sid,uid,armorid,hp,hp_max,$armorprices,strong_level,strong_value,embed_pearls,embed_holes,0,active_special,strong_times,combine_level,best_quality from sys_user_armor where uid='$myuid' and sid='$armorsid')");	
	  sql_query("delete from  sys_user_armor where uid='$myuid' and sid='$armorsid'");	
	  exit;
	}else if(isset($_POST['buysarmor'])){//============================购买装备代码
	  if(empty($_POST['surearmorprice']) || empty($_POST['surebuyarmorsid'])){ echo '<script type="text/javascript">alert("装备待售中!");</script>';  exit;}
	  $buyarmirsid = $_POST['surebuyarmorsid'];
	  $buyarmirprice = $_POST['surearmorprice'];
	  $buyinfos=sql_fetch_one("select money,lastcid from sys_user where uid='$myuid'");
	  $mymoneys=$buyinfos['money'];      	  
	  if($mymoneys-$buyarmirprice<0){echo "<script type='text/javascript'>alert('剩余:'+'$mymoneys'+'(元宝)!'+'无法购买!');</script>";	exit;}
      $selluid = sql_fetch_one_cell("select uid from sys_user_armor_trade where sid='$buyarmirsid'");      	  
      if(empty($selluid)){echo '<script type="text/javascript">alert("该装备已经售出!");</script>';exit;}
	  sql_query("insert into sys_user_armor(`sid`,`uid`,`armorid`,`hp`,`hp_max`,`hid`,`strong_level`,`strong_value`,`embed_pearls`,`embed_holes`,`active_special`,`strong_times`,`combine_level`,`best_quality`)
	     (select sid,$myuid,armorid,hp,hp_max,0,strong_level,strong_value,embed_pearls,embed_holes,active_special,strong_times,combine_level,best_quality from sys_user_armor_trade where sid='$buyarmirsid')");	
	  sql_query("delete from  sys_user_armor_trade where sid='$buyarmirsid'");
	  sql_query("update sys_user set `money` ='$mymoneys' - '$buyarmirprice' where uid='$myuid' ");//扣钱	 
      $selllcid=sql_fetch_one_cell("select lastcid from sys_user where uid='$selluid'");
      if(empty($selllcid)) exit;	  
      sql_query("update sys_user set `money` = money + '$buyarmirprice'  where uid='$selluid' ");      
	  exit;
	}else if(isset($_POST['cancelsell'])){//==============================取消出售的装备
	  $cancelsellsid=$_POST['cancelsell'];
	  $cainfo = sql_fetch_one_cell("select sid from sys_user_armor_trade where uid='$myuid' and sid='$cancelsellsid'");	
      if(empty($cainfo)){echo '<script type="text/javascript">alert("已经取消!");</script>';exit;}
	  sql_query("insert into sys_user_armor(`sid`,`uid`,`armorid`,`hp`,`hp_max`,`hid`,`strong_level`,`strong_value`,`embed_pearls`,`embed_holes`,`active_special`,`strong_times`,`combine_level`,`best_quality`)
	     (select sid,uid,armorid,hp,hp_max,0,strong_level,strong_value,embed_pearls,embed_holes,active_special,strong_times,combine_level,best_quality from sys_user_armor_trade where uid='$myuid' and sid='$cancelsellsid')");	
	  sql_query("delete from  sys_user_armor_trade where uid='$myuid' and sid='$cancelsellsid'");
	  exit;
	}else if(isset($_POST['makearmor'])){//===============================升级装备代码
	  $upasid=$_POST['updateasid'];      //==其它装备1-3品质的->袁绍->冰封->冰魄->龙渊->白虎->朱雀->青龙
	  $upabasi=$_POST['yuanbo'];         //==其它装备4及以上品质->曹操->冰封->冰魄->龙渊->白虎->朱雀->青龙
	  $upasjtz=$_POST['sjtz'];           //==战场装备->冰封->冰魄->龙渊->白虎->朱雀->青龙
	  $upabahf=$_POST['bafuf'];
	  $upabahfis=$_POST['baohufu'];
      if(empty($upasid)) exit;
	  $armorinfo=sql_fetch_one("select a.*,c.* from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$myuid' and a.sid='$upasid' ");
	  if(empty($armorinfo)) exit;
	  if($armorinfo['tieid']==12007){echo '<script type="text/javascript">alert("装备已经无法升级了!");</script>';exit;}
	  $mybaofuhu=sql_fetch_one_cell("select count from sys_goods where uid='$myuid' and gid=12157");//得到保护符数量
      $mysjtz=sql_fetch_one_cell("select count from sys_goods where uid='$myuid' and gid=8895");//得到升级图纸数量 
      $mysjbaoshi=sql_fetch_one_cell("select count from sys_goods where uid='$myuid' and gid=8896");//得到升级图纸数量 
      if(empty($mysjtz) || $mysjtz==0) exit;
	  if(empty($mysjbaoshi) || $mysjbaoshi==0) exit;
	  if($upabahfis==1 && $upabahf==0) exit;
      $updaterate=mt_rand(35,200);	  
      if($upabahfis==1){//使用了保护符
	     $updaterate=mt_rand(1,40);
	     sql_query("update sys_goods set count='$upabahf' where uid='$myuid' and gid=12157");//扣除保护符一个  
	    }
      sql_query("update sys_goods set count='$upasjtz' where uid='$myuid' and gid=8895");//扣除保护符一个  
      sql_query("update sys_goods set count='$upabasi' where uid='$myuid' and gid=8896");//扣除保护符一个
      $endrate=mt_rand(3,55);
      if($updaterate>$endrate){
	     $endrate=mt_rand(0,1);
 	     if($upabahfis==1 || $endrate==1) echo '<script type="text/javascript">alert("装备升级失败，该装备无损！");;</script>'; 
		  else {
		      echo '<script type="text/javascript">alert("装备升级失败，该装备将报废！为了装备无损请使用保护符！");</script>';
			  sql_query("delete from  sys_user_armor where sid='$upasid'");
			}	
		 exit;
		}  
      if($armorinfo['type']<4 && $armorinfo['bid']==0 && $armorinfo['tieid']==0){//转成袁绍套装
	      $osid=$armorinfo['part'];
		  $num=0;
	      $msid=array(1=>20014,2=>20015,3=>20016,4=>20017,5=>20018,6=>20019,7=>20020,8=>20021,9=>20022,10=>20024,11=>20026,12=>20027);
	      if($osid==9 || $osid==10){
		      $num=mt_rand(0,1);
		    } 
	      $osid=$msid[$osid]+$num;
		  sql_query("update sys_user_armor set armorid='$osid' where uid='$myuid' and sid='$upasid' ");
		  exit;
		} 
	  if($armorinfo['type']>3 && $armorinfo['bid']==0 && $armorinfo['tieid']==0){//转成曹操套装
	      $osid=$armorinfo['part'];
		  $num=0;
	      $msid=array(1=>20028,2=>20029,3=>20030,4=>20031,5=>20032,6=>20033,7=>20034,8=>20035,9=>20036,10=>20038,11=>20040,12=>20041);	   
	      if($osid==9 || $osid==10){
		      $num=mt_rand(0,1);
		    } 
	      $osid=$msid[$osid]+$num;
		  sql_query("update sys_user_armor set armorid='$osid' where uid='$myuid' and sid='$upasid' ");
		  exit;	
		} 
	  if($armorinfo['bid']>0){//转成冰封套装
	      $osid=$armorinfo['part'];
		  $num=0;
	      $msid=array(1=>53001,2=>53002,3=>53003,4=>53004,5=>53005,6=>53006,7=>53007,8=>53008,9=>53009,10=>53011,11=>53013,12=>53016);	   
	      if($osid==9 || $osid==10){
		      $num=mt_rand(0,1);
		    }else if($osid==11) $num=mt_rand(0,2);
	      $osid=$msid[$osid]+$num;
		  sql_query("update sys_user_armor set armorid='$osid' where uid='$myuid' and sid='$upasid' ");
		  exit;			
		} 
	  if($armorinfo['tieid']>0){
	     switch($armorinfo['tieid']){
		     case 12003:{
			   $osid=$armorinfo['armorid']-37000;
			   sql_query("update sys_user_armor set armorid='$osid',embed_holes ='0,0,0,0,4',hp_max ='300',hp='3000' where sid='$upasid' and uid='$myuid' limit 1");
			   break;}//冰封->冰魄
		     case 12005:{			        
	           $msid=array(1=>53029,2=>53030,3=>53031,4=>53032,5=>53033,6=>53034,7=>53035,8=>53036,9=>53037,10=>53038,11=>53039,12=>53040);	   
	           $osid=$msid[$armorinfo['part']];
			   sql_query("update sys_user_armor set armorid='$osid',embed_holes ='0,0,0,0,0',hp_max ='600',hp='6000' where sid='$upasid' and uid='$myuid' limit 1");
			   break;}//冰魄->龙渊
			 case 12006:{
			   $osid=$armorinfo['armorid']+12;
			   sql_query("update sys_user_armor set armorid='$osid',embed_holes ='0,0,0,0,0',hp_max ='900',hp='9000' where sid='$upasid' and uid='$myuid' limit 1");
			   break;}//龙渊->白虎
		     default: break;
			}
		}	  
	  echo '<script type="text/javascript">alert("成功升级!");</script>';
	  exit;	
	}else if(isset($_POST['sellhero'])){//===============================出售将领代码
	   $sellhprice=getstrisnum($_POST['sellhprice']);
	   $sellhhid=$_POST['sureherohids'];
	   if($sellhprice==0 || empty($sellhhid)) exit;
	   $shinfo = sql_fetch_one("select * from sys_city_hero where uid='$myuid' and hid='$sellhhid'");
       if(empty($shinfo)){echo '<script type="text/javascript">alert("将领已经售出!");</script>';exit;}
       sql_query("insert into sys_city_hero_trade (hid,uid,name,npcid,sex,face,cid,state,level,exp,command_base,affairs_base,bravery_base,wisdom_base,price)
         values ('$shinfo[hid]','$myuid','$shinfo[name]','$shinfo[npcid]','$shinfo[sex]','$shinfo[face]','$shinfo[cid]',0,'$shinfo[level]','$shinfo[exp]','$shinfo[command_base]','$shinfo[affairs_base]','$shinfo[bravery_base]','$shinfo[wisdom_base]','$sellhprice')");	
       sql_query("delete from  sys_city_hero where uid='$myuid' and hid='$sellhhid'");  
	   sql_query("delete from  mem_hero_blood where hid='$sellhhid'");  
       sql_query("update sys_user_armor set hid=0  where uid='$myuid' and hid='$sellhhid'"); //穿了装备的要脱装备  
	   exit;	  
	}else if(isset($_POST['sureheroid'])){//=====================================取消出售的将领
	  $cancelsellhid=$_POST['sureheroid'];
	  $cancelhid = sql_fetch_one_cell("select hid from sys_city_hero_trade where uid='$myuid' and hid='$cancelsellhid'");	
      if(empty($cancelhid)){echo '<script type="text/javascript">alert("已经取消!");</script>';exit;}	  
	  sql_query("insert into  sys_city_hero(`hid`,`uid`,`name`,`npcid`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`command_base`,`command_add_on`,`affairs_base`,`bravery_base`,`wisdom_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`affairs_add_on`,`bravery_add_on`,`wisdom_add_on`,`force_max_add_on`,`energy_max_add_on`,`speed_add_on`,`attack_add_on`,`defence_add_on`,`loyalty`,`herotype`,`hero_health`)
	     (select hid,uid,name,npcid,sex,face,cid,0,level,exp,command_base,command_add_on,affairs_base,bravery_base,wisdom_base,affairs_add,bravery_add,wisdom_add,affairs_add_on,bravery_add_on,wisdom_add_on,force_max_add_on,energy_max_add_on,speed_add_on,attack_add_on,defence_add_on,70,0,0 from sys_city_hero_trade where uid='$myuid' and hid='$cancelhid')");	
	 addheroblood($cancelhid);
	 updateCityHeroChanges($myuid,$mycid);
	 sql_query("delete from  sys_city_hero_trade where uid='$myuid' and hid='$cancelhid'");	 
	 exit;
	}else if(isset($_POST['buyhero'])){//=================================购买将领代码
	  if(empty($_POST['sureheroprice']) || empty($_POST['surebuyheroid'])){ echo '<script type="text/javascript">alert("名将待售中!");</script>';  exit;}
	  $heroshid=$_POST['surebuyheroid'];
	  $herosprice=$_POST['sureheroprice'];
	  $buyinfos=sql_fetch_one("select money,lastcid from sys_user where uid='$myuid'");
	  $mymoneys=$buyinfos['money'];
	  $mylastcid=$buyinfos['lastcid'];
	  if($mymoneys-$herosprice<0){echo "<script type='text/javascript'>alert('剩余:'+'$mymoneys'+'(元宝)!'+'无法购买!');</script>";	exit;}
	  $selluid = sql_fetch_one_cell("select uid from sys_city_hero_trade where hid='$heroshid'");
	  if(empty($selluid)){echo '<script type="text/javascript">alert("该将领已经售出!");</script>';exit;}
	  sql_query("insert into  sys_city_hero(`hid`,`uid`,`name`,`npcid`,`sex`,`face`,`cid`,`state`,`level`,`exp`,`command_base`,`command_add_on`,`affairs_base`,`bravery_base`,`wisdom_base`,`affairs_add`,`bravery_add`,`wisdom_add`,`affairs_add_on`,`bravery_add_on`,`wisdom_add_on`,`force_max_add_on`,`energy_max_add_on`,`speed_add_on`,`attack_add_on`,`defence_add_on`,`loyalty`,`herotype`,`hero_health`)
	     (select hid,$myuid,name,npcid,sex,face,$mylastcid,0,level,exp,command_base,command_add_on,affairs_base,bravery_base,wisdom_base,affairs_add,bravery_add,wisdom_add,affairs_add_on,bravery_add_on,wisdom_add_on,force_max_add_on,energy_max_add_on,speed_add_on,attack_add_on,defence_add_on,70,0,0 from sys_city_hero_trade where uid='$selluid' and hid='$heroshid')");	
	  sql_query("delete from  sys_city_hero_trade where  uid='$selluid' and hid='$heroshid'");
	  addheroblood($heroshid);
	  updateCityHeroChanges($myuid,$mycid);
	  sql_query("update sys_user set `money`='$mymoneys' - $herosprice where uid='$myuid' ");//扣钱	
      if($selluid>1000) 	  
         sql_query("update sys_user set `money` = money + '$herosprice'  where uid='$selluid' ");	  
	  exit;	  
	}  
	exit;
  function getstrisnum($var){
      if(is_numeric($var)){
	      $text = (string)$var;
		  if($text[0]==0 && $text[1]!='.') return 0;
	      if((float)$var != (int)$var ){             
			 return (float)$var;
            }   
          else{
		     $textlen = strlen($text);
             for($i=0;$i<$textlen;$i++){
			      if($text[$i]=='.') return 0;
                }
             return (int)$var;
            }
        } 
	  return 0; 
    }
   function updateCityHeroChanges($uid,$cid){
	 $hero_fee=sql_fetch_one_cell("select sum(level*20+(GREATEST(affairs_base+affairs_add-90,0)+GREATEST(bravery_base+bravery_add-90,0)+GREATEST(wisdom_base+wisdom_add-90,0))*50) from sys_city_hero where herotype!=1000 and cid='$cid' and uid='$uid' and state!=5 and state!=6 and state!=9");
	 $hero_fee = $hero_fee+1-1;//为防止在解雇最后一个将领时出现null值,这里将null设为0
	 sql_query("update mem_city_resource set hero_fee='$hero_fee'  where cid='$cid'");
    }
 function addheroblood($herohid){
      $tmpHero = sql_fetch_one("select * from sys_city_hero where hid='$herohid'");
	  $forcemax=100+floor($tmpHero['level']/5)+floor(($tmpHero['bravery_base']+$tmpHero['bravery_add'])/3);
      $energymax=100+floor($tmpHero['level']/5)+floor(($tmpHero['wisdom_base']+$tmpHero['wisdom_add'])/3);
	  $force=100+floor($tmpHero['level']/5);
	  $energy=100+floor($tmpHero['level']/5);
      sql_query("insert into mem_hero_blood (`hid`,`force`,`force_max`,`energy`,`energy_max`) values ('$herohid','$force','$forcemax','$energy','$energymax')");
	}