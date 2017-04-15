<?php                          

require_once("./dbinc.php");
require_once("./global.php");

//type是指取数据类型
//0:玩家数据+资源数据+警报
//1:将领
//2:军队
//3:城防
                          
class CityCommand
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
		    if ($serverState !=1)
		    {
		    	/*if($serverState==0)
        		{
        			throw new Exception(sql_fetch_one_cell("select content from sys_announce where id=2"));
        		}
        		else if($serverState==2)
        		{
        			throw new Exception(sql_fetch_one_cell("select content from sys_announce where id=3"));
        		}
        		else*/
		        {
		        	throw new Exception("server_is_updating");
		        }
		    }
		    
            if ($type == 0)
            {
                $ret[] = getCityInfoRes($uid,$cid);
                sql_query("update sys_online set `lastupdate`=unix_timestamp() where uid='$uid'");
            }      
            else if ($type == 1)
            {
                $ret[] = getCityInfoHero($uid,$cid);
            }                              
            else if ($type == 2)
            {
                $ret[] = getCityInfoArmy($uid,$cid);
            }                                    
            else if ($type == 3)
            {
                $ret[] = getCityInfoDefence($uid,$cid);
            }
            else if ($type==4)
            {
            	$serverTime=array();
            	$serverTime[]= sql_fetch_one_cell("select unix_timestamp()");
            	$ret[]=$serverTime;
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