<?php
require_once 'lib/mysql.php';
require_once 'game/agents/DefaultAgentService.php';
require_once 'game/agents/AgentService.php';
require_once 'config/db.php';
//同学网
define ( "TONGXUEWANG", "tongxuewang" );
define ( "IMPLAY", "implay" );
define ( "OFFICIAL", "uuyx" );

class AgentServiceFactory{
	public static function getInstance($uid) {			
		if (defined("PASSTYPE")){
			switch (PASSTYPE) {
				case TONGXUEWANG :			
					require_once 'game/agents/tongxuewang/TongxueService.php';
					$user =sql_fetch_one ( "select passport,name from sys_user where uid='$uid'" ); 					
					if ($user!=null) return new TongxueService($user);
					break;
				case IMPLAY :			
					require_once 'game/agents/implay/ImPlayService.php';
					$user =sql_fetch_one ( "select passport,name from sys_user where uid='$uid'" ); 					
					if ($user!=null) return new ImPlayService($user);
					break;
				case OFFICIAL :			
					require_once 'game/agents/official/OfficialService.php';
					$user =sql_fetch_one ( "select passport,name from sys_user where uid='$uid'" ); 					
					if ($user!=null) return new OfficialService($user);
					break;
				default :
					break;
			}		
		}
		return new DefaultAgentService ( );
	}	
}
?>