<?php                          

require_once("./dbinc.php");
require_once("./BattleFunc.php");

//type是指取数据类型

                          
class BattleCommand
{
	function sendCommand($param)
	{
		
        $ret = array(0=>1);
        $uid = array_shift($param);
        $cid = array_shift($param);
        $sid = array_shift($param);
        $type = array_shift($param);
        $ret[] = $type;
		try
		{      
		   
            checkUserAuth($uid,$sid);
            
            $serverState = sql_fetch_one_cell("select value from mem_state where state=2");
		    if ($serverState !=1){
		    	throw new Exception("server_is_updating");
		    }
		    
            if ($type == 0)
            {
                $ret[] = getCityInfoRes($uid,$cid);
                sql_query("update sys_online set `lastupdate`=unix_timestamp() where uid='$uid'");
            }      
			return $ret;
           
		}
		catch(Exception $e)
		{
			$ret = array(0=>0);
			$ret[] = $e->getMessage();   
			return $ret;
		}
	}
}          


 
?>