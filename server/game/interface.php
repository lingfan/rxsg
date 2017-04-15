<?php     
require_once("./common.php");
require_once("./ActFunc.php");

if (defined("LANG"))  require_once("./lang_".LANG.".php");
else  require_once("./lang.php");

class User 
{ 
	var $_explicitType = "User";
	
	var $username;
	var $score;    
}
class Grid
{
	var $_explicitType = "Grid";
	
	var $bid;       //建筑ID
	var $xy;        //位置   
	var $level;     //                  
	var $title;     //标题
	var $state;	//0代表正常状态,1代表升级中,2代表废墟
	var $upgrade_timeleft;      //升级剩余时间
	var $upgrade_alltimeleft;   //总升级时间
	var $wood_need;             //升下一级需要的木材
	var $rock_need;
	var $iron_need;
	var $food_need;
	var $gold_need;
	var $can_upgrade;           //可否可以升级了
	var $no_upgrade_msg;        //解释一下为什么不能升级
}
class City
{
	var $_explicitType = "City";
	
	var $cid;
	var $name;
	var $wood;
	var $rock;
	var $iron;
	var $food;
	var $gold;
	var $now_people;    //现有人口
	var $max_people;    //人口上限
	var $free_people;   //空闲人口                    
	var $GridArray;	//Grids containinig buildings
}
//建筑的其它前提条件
class UpgradeCondition
{
	var $_explicitType = "UpgradeCondition";
	var $type;		//"前提建筑","前提科技"等建造条件
	var $upgradeNeed;	//需要等级或类型
	var $currentOwn;	//当前数量
	var $canUpgrade;	//是否可以升级
}
//建筑相关的信息,用于建设/拆毁用
class BuildingInfo
{
	var $_explicitType = "BuildingInfo";
	var $bid;			//建筑ID
	var $name;			//名称
	var $description;	//该建筑的描述
	var $levelDescription;//该级别的描述
	var $level;			//目标级别
	var $woodNeed;		//木材需求
	var $rockNeed;		//石料需求
	var $ironNeed;		//铁锭需求
	var $foodNeed;		//粮食需求
	var $goldNeed;		//黄金需求
	var $peopleNeed;	//人口数量需求
	var $upgradeTime;	//建造时间
	var $canUpgrade;	//是否可以建造
	var $conditions;	//array of UpgradeCondition
}

//城内某建筑的当前状态
class BuildingState
{
	var $_explicitType = "BuildingState";
	var $bid;
	var $bname;
	var $inner;
	var $description;
	var $x;
	var $y;
	var $level;
	var $state;
	var $state_starttime;
	var $state_endtime;
	var $state_timeleft;
	var $people_working;
}
//科技当前状态
class TechnicState
{
	var $_explicitType = "TechnicState";
	var $tid;
	var $tname;
	var $description;
	var $levelDescription;
	var $nextLevelDescription;
	var $level;
    var $sharelevel;
	var $cid;
	var $state;
	var $state_endtime;
	var $state_timeleft;
	var $can_upgrade;
	var $woodNeed;
	var $rockNeed;
	var $ironNeed;
	var $foodNeed;
	var $goldNeed;
	var $upgrade_time;
	var $conditions;
}
//城内士兵的当前状态
class ArmySoldierState
{
	var $_explicitType = "ArmySoldierState";
	var $sid;
	var $sname;
	var $count;
	var $description;
	var $hp;
	var $ap;
	var $dp;
	var $range;
	var $speed;
	var $carry;	  
	var $can_draft;  	
	var $food_use;
	var $woodNeed;
	var $rockNeed;
	var $ironNeed;
	var $foodNeed;
	var $goldNeed;
    var $peopleNeed;
	var $draft_time;
	var $conditions;
}
//城内士兵排的队的当前状态
class ArmyDraftState
{
	var $_explicitType = "ArmyDraftState";
	var $qid;
	var $sid;
	var $sname;
	var $count;    
	var $state;
	var $time_left;
	var $accmark;
}

//城内城防的当前状态
class WallDefenceState
{
	var $_explicitType = "WallDefenceState";
	var $did;
	var $dname;
	var $count;
	var $description;
	var $hp;
	var $ap;
	var $dp;
	var $range;
	var $speed;
	var $carry;	  
	var $can_reinforce;
	var $woodNeed;
	var $rockNeed;
	var $ironNeed;
	var $foodNeed;
	var $goldNeed;
	var $areaNeed;
	var $reinforce_time;
	var $conditions;
}
//城内士兵排的队的当前状态
class WallReinforceState
{
	var $_explicitType = "WallReinforceState";
	var $qid;
	var $did;
	var $dname;
	var $count;    
	var $state;
	var $time_left;
	var $accmark;
}

//军队动态
class ReportArmy
{
	var $_explicitType = "ReportArmy";
	var $rid;
	var $task;
	var $state;
	var $from_city;
	var $to_city;
	var $reach_time;
	var $back_time;
	var $hero_name;
	var $solider_list;
}

//敌情警报
class ReportEnemy
{
	var $_explicitType = "ReportEnemy";
	var $rid;
	var $type;
	var $user_name;
	var $from_city;
	var $to_city;
	var $reach_time;
	var $time_left;
}
//公文报告
class ReportResult
{
	var $_explicitType = "ReportResult";
	var $rid;
	var $selected;
	var $title;
	var $content;
	var $from_city;
	var $to_city;
	var $happen_time;
}
//待招募的英雄
class HeroRecruit
{
	var $_explicitType = "HeroRecruit";
	var $id;
	var $hname;
	var $sex;
	var $face;
	var $cid;
	var $level;
	var $affairs_base;
	var $bravery_base;
	var $wisdom_base;
    var $affairs_add;
    var $bravery_add;
    var $wisdom_add;
	var $loyalty;
	var $gold_need;
	var $isActHero;
}

class BattleActiveState
{
	var $_explicitType = "BattleState"; 
	var $battles;
	var $global_tasks;
}
class GlobalTask
{
	var $_explicitType = "GlobalTask";
	var $name;
	var $state;
}
class Battle
{
	var $_explicitType = "Battle";
	var $name;
	var $state; //0: 不显示, 1:问号， 2:显示
	var $description;
	var $condition;
}
class BattleCondition
{
	var $_explicitType = "BattleCondition";
	var $type; //0: 战场, 1: 装备, 2:
	var $description;
	var $ok;
}
 

?>