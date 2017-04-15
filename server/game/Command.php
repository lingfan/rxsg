<?php
require_once("interface.php");
require_once("utils.php");
                      
require_once("DefenceFunc.php");
require_once("SoldierFunc.php");
require_once("TechnicFunc.php");
require_once("MarketFunc.php");
require_once("MailFunc.php");
require_once("WorldFunc.php");
require_once("FriendFunc.php");
require_once("GroundFunc.php");
require_once("BuildingFunc.php");  
require_once("UserFunc.php");
require_once("CityFunc.php");
require_once("ReportFunc.php");
require_once("TaskFunc.php");
require_once("UnionFunc.php");
require_once("ShopFunc.php");
require_once("RankFunc.php");
require_once("TrickFunc.php");
require_once("BufferFunc.php");
require_once("HeroFunc.php");
require_once("TroopFunc.php");
require_once("ArmorFunc.php");
require_once("StatFunc.php");
require_once("BattleFunc.php");
require_once("AdultFunc.php");
require_once("EquipmentFunc.php");
require_once("NaoZhong.php");
require_once("GuideFunc.php");
require_once("RewardFunc.php");
require_once("BarnFunc.php");
require_once("LotteryFunc.php");
require_once("BattleNetFunc.php");
require_once("AchivementFunc.php");
require_once("VipFunc.php");
require_once("CityMergeFunc.php");
require_once("ShaChangFunc.php");
require_once("HeroSkillFunc.php");
require_once("PKFunc.php");
require_once("LuoyangFunc.php");
require_once("AssembleKuaFu.php");
require_once("MarrySystemFunc.php");
require_once("WorkShop.php");

class Command
{
	function sendCommand($param)
	{
        $ret = array(0=>1);
        $uid = array_shift($param);
        $sid = array_shift($param);  
        $type = array_shift($param);
        $ret[] = $type; 
        $cid = 0;    
		try
		{              
           
            checkUserAuth($uid,$sid);     
                                                                                          
			if ($type == 1)	//dialog type has receiver
			{
                $cid = array_shift($param);
				$receiverID = array_shift($param);
				$receiverParam = array_shift($param);
                $ttype = array_shift($param);
				$ret[] = $cid;
				$ret[] = $receiverID;
				$ret[] = $receiverParam;
                $ret[] = $ttype;
                checkCityOwner($cid,$uid);
                $commandFunc = array_shift($param);
                if (function_exists($commandFunc))
                {
                    //$ret[] = $commandFunc($uid,$cid,$param);
                    if(in_array($commandFunc, $GLOBALS['openfunc'])){
                		//$starttime = microtime_float();
                		if (! newLockUser ( $uid,$commandFunc))
						 	throw new Exception ( $GLOBALS ['pacifyPeople'] ['server_busy'] );
                		$ret[] = $commandFunc($uid,$cid,$param);
                		newUnlockUser($uid,$commandFunc);	
                		/*$endtime = microtime_float();
                		$executeTimeInfo=$commandFunc . "," . (int)(($endtime-$starttime)*1000) . "," . time();
						@file_put_contents("executeTime.csv",$executeTimeInfo."\n",FILE_APPEND);*/
                	}
                	else{
                		 throw new Exception($commandFunc.$GLOBALS['sendCommand']['command_exception']);
                	}
                }
                else
                {
                    throw new Exception($commandFunc.$GLOBALS['sendCommand']['command_not_found']);
                }
			}
			else if ($type == 0) //city type
			{
                $cid = array_shift($param);
				$ret[] = $cid;
				$ret_type = array_shift($param);
				$ret[] = $ret_type;
			}

            else if ($type == 2)    //global
            {
                     $ret[] = array_shift($param);
                
                $commandFunc = array_shift($param);
                $tmpCommandFunc = $commandFunc;
                $ret[] = $commandFunc;
                    
                if (function_exists($commandFunc))
                {
                	if(in_array($commandFunc, $GLOBALS['openfunc'])){
                		 if (! newLockUser ( $uid,$commandFunc ))
						 	throw new Exception ( $GLOBALS ['pacifyPeople'] ['server_busy'] );
                		 $ret[] = $commandFunc($uid,$param);
                		 newUnlockUser($uid,$commandFunc);	                		 
                	}
                	else{                		                		          		     
                		 throw new Exception($commandFunc.$GLOBALS['sendCommand']['command_exception']);
                	}
                }
                else                
                {   
                	/******call WizardFunc begin***********/             	
                	//if (defined("WIZARD_ENABLE") && WIZARD_ENABLE===true)
                		if (defined("WIZARD_ENABLE") )
                	{                
	                	require_once("WizardFunc.php");	                	              
                		 $retmsg = executeCommand($uid,$param);                		  
                		 if (!empty($retmsg)){
                		 	if (strstr($retmsg,"#help") || strstr($retmsg,"#openBox") ){ 
                		 		$funret=array($retmsg);
                		 		$ret[]=$funret;
                		 		return $ret;
                		 	}
                		 	throw new Exception($retmsg);	
                		 }
                	}
                	/********call WizardFunc end*********/
                     	require_once("WizardFunc.php");	                	              
                		 $retmsg = executeCommand($uid,$param);                		  
                		 if (!empty($retmsg)){
                		 	if (strstr($retmsg,"#help") || strstr($retmsg,"#openBox") ){ 
                		 		$funret=array($retmsg);
                		 		$ret[]=$funret;
                		 		return $ret;
                		 	}
                		 	throw new Exception($retmsg);	
                		 }
                    //throw new Exception($commandFunc.$GLOBALS['sendCommand']['command_not_found']);
                }
            }
//			sql_query("insert into dbg_command (`uid`,`count`) values ('$uid',1) on duplicate key update `count`=`count`+1");
			return $ret;
		}
		catch(Exception $e)
		{
			$ret = array(0=>0);
			$ret[] = $e->getMessage();
            unlockUser($uid);
			return $ret;
		}
	}
}          
/*                                          
$test = new Command();
$param = array();
$param[] = 181;	       
$param[] = 30304;           
$param[] = 2;           
$param[] = "user";      
$param[] = "useGoods";
$param[] = "16";                              
$test->sendCommand($param);

printf("forget to delete Command.php's test code");
      */
           
       
 
?>