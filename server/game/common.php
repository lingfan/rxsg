<?php

	require_once("dbinc.php");
	
	$tmpip="";
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 	
		$tmpip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	if (isset($_SERVER['REMOTE_ADDR']))
		$tmpip = $_SERVER['REMOTE_ADDR'];
	if ($tmpip!=""){
		$GLOBALS['rawip'] = trim($tmpip);
		$ips = explode('.',$tmpip);
		$GLOBALS['ip'] = ($ips[3] << 24) + ($ips[2] << 16) + ($ips[1] << 8) + $ips[0];		
		$GLOBALS['sip']=$tmpip;
	}else
		$GLOBALS['ip']=0; 
	

	
	$GLOBALS['now'] = time();
    
    mb_internal_encoding("utf-8");
    
//    六倍速    
//    define('GAME_SPEED_RATE',6);
//    define ('GRID_DISTANCE',10000);

//  单倍速
    define('GAME_SPEED_RATE',10);
    define ('GRID_DISTANCE',6000);
   

    define('NPC_HUANJIN',894);    //黄巾军UID
    define('NPC_UID_END',897);   
    
    
    define('HERO_EXP_RATE',0.1);
    define('HERO_FEE_RATE',20);
    define('NPCHERO_FEE_RATE',100);
    define('HERO_LARGESS_RATE',100);
    define('NPCHERO_LARGESS_RATE',500);
                                   
    
    define ('MERCHANT_MOVE_SPEED',360);
	define ('HONGLU_LEVEL_RATE',10);
									   
	define('ID_BUILDING_FARMLAND',1);	  
	define('ID_BUILDING_WOOD',2);	   
	define('ID_BUILDING_ROCK',3);	   
	define('ID_BUILDING_IRON',4);	   
	define('ID_BUILDING_HOUSE',5);	   
	define('ID_BUILDING_GOVERMENT',6);	
	define('ID_BUILDING_COLLEGE',7);	
	define('ID_BUILDING_GROUND',8);	
	define('ID_BUILDING_ARMY',9);	   
	define('ID_BUILDING_HOTEL',10);	   
	define('ID_BUILDING_OFFICE',11);	   
	define('ID_BUILDING_HONGLU',12);	   
	define('ID_BUILDING_MARKET',13);	   
	define('ID_BUILDING_BLACKSMITH',14);	   
	define('ID_BUILDING_WORKSHOP',15);	   
	define('ID_BUILDING_BARN',16);
	define('ID_BUILDING_STORE',17);
	define('ID_BUILDING_DAK',18);
	define('ID_BUILDING_BALEFIRE',19);
	define('ID_BUILDING_WALL',20);
	
	define('ID_TECHNIC_FOOD',1);
	define('ID_TECHNIC_WOOD',2);
	define('ID_TECHNIC_ROCK',3);
	define('ID_TECHNIC_IRON',4);
	
	define('GLOBAL_TAX_RATE',1);		//税收比例，每个人每小时收多少钱
    define('GLOBAL_GOLD_RATE',1);        //每人每小时生产粮食数量
    define('GLOBAL_FOOD_RATE',10);        //每人每小时生产粮食数量
	define('GLOBAL_WOOD_RATE',10);		//每人每小时生产木材数量
	define('GLOBAL_ROCK_RATE',5);		//每人每小时生产石料数量
	define('GLOBAL_IRON_RATE',4);		//每人每小时生产铁锭数量  
		
    define('GLOBAL_FOOD_MAX_RATE',1000);//资源生产上限比例
	define('GLOBAL_WOOD_MAX_RATE',1000);//资源生产上限比例
	define('GLOBAL_ROCK_MAX_RATE',500);//资源生产上限比例
	define('GLOBAL_IRON_MAX_RATE',400);//资源生产上限比例  
    
    define('FOOD_VALUE',0.1);
    define('WOOD_VALUE',0.1);
    define('ROCK_VALUE',0.2);
    define('IRON_VALUE',0.25);  
    
    define('MAX_USER_NAME',8);
    define('MAX_CITY_NAME',8);
    define('MAX_FLAG_CHAR',1);
    define('MAX_HERO_NAME',4);
    define('MAX_UNION_NAME',8);
	
    define('FOOD_PRICE',0.1);
    define('WOOD_PRICE',0.1);
    define('ROCK_PRICE',0.2);
    define('IRON_PRICE',0.25);
                                     
    define('WT_CITY',0); 
    define('WT_LAND',1); 
    define('WT_DESERT',2); 
    define('WT_FOREST',3); 
    define('WT_GRASS',4); 
    define('WT_HILL',5); 
    define('WT_LAKE',6); 
    define('WT_SWAMP',7);
       
    define('MAX_WORLD_TYPE',8); 
?>