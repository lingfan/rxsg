<?php
//幕僚的统计功能
require_once("./interface.php");
require_once("./utils.php");
require_once("./UnionFunc.php");
define("ACHIVE_PAGE_CPP",5);
function getOverviewStat($uid,$param){
	$ret=array();
	$uid=array_shift($param);
	$uid=intval($uid);
	//总成就点数
	$ret[]=sql_fetch_one_cell("select achivement_point from sys_user where uid=$uid");
	//最近两项成就
	$ret[]=sql_fetch_rows("SELECT b.id,b.name,b.content,b.point,CASE WHEN time IS NULL THEN '--' ELSE time END AS achieveGetTime,image FROM sys_user_achivement a,cfg_achivement b WHERE a.achivement_id=b.id AND uid=$uid ORDER BY TIME DESC LIMIT 2");
	//成就完成个数统计
	$totalCnt=sql_fetch_rows("SELECT `group`,b.name AS group_name,COUNT(0) AS total_count,0 AS finish_count FROM cfg_achivement a,cfg_achivement_group b WHERE a.group=b.id AND state=1 GROUP BY `group`");
	$finishCnt=sql_fetch_simple_map("SELECT b.group,COUNT(0) AS finish_count FROM sys_user_achivement a,cfg_achivement b WHERE b.state=1 AND a.achivement_id=b.id AND uid=$uid GROUP BY b.group",'group','finish_count');
	foreach($totalCnt as &$item) {
		$item["finish_count"]=$finishCnt[$item["group"]];
		if(empty($item["finish_count"])){
			$item["finish_count"]=0;
		}
	}
	$ret[]=$totalCnt;
	//print_r($ret);
	return $ret;
}

function getAchivementsByGroup($uid,$param) {
	$ret=array();
	$uid=intval(array_shift($param));
	$group=intval(array_shift($param));
	$subgroup=intval(array_shift($param));
	$type=intval(array_shift($param));
	$page=intval(array_shift($param));
	$start=$page*ACHIVE_PAGE_CPP;
	
	$open_sql="0";
	if (sql_check("select state from sys_user where uid=$uid and state in (0,2)")) $open_sql=$open_sql.",1"; //脱离新手保护
	if (1==sql_fetch_one_cell("select value from mem_state where state=5")) $open_sql=$open_sql.",2"; //黄巾之乱完成
	$open_sql=" and b.open_time in ($open_sql)";
	if($type==1){
		$ret[]=sql_fetch_one_cell("SELECT count(*) FROM sys_user_achivement a,cfg_achivement b WHERE b.state=1 AND a.achivement_id=b.id AND uid='$uid' AND `group`='$group' AND sub_group='$subgroup' $open_sql");
		$ret[]=sql_fetch_rows("SELECT b.id,b.name,b.content,b.point,image,time as achieveGetTime  FROM sys_user_achivement a,cfg_achivement b WHERE b.state=1 AND a.achivement_id=b.id AND uid='$uid' AND `group`='$group' AND sub_group='$subgroup' $open_sql ORDER BY time DESC LIMIT $start, ".ACHIVE_PAGE_CPP); 
	}else if($type==2){
		$ret[]=sql_fetch_one_cell("SELECT count(*)FROM cfg_achivement b LEFT JOIN sys_user_achivement a ON a.achivement_id=b.id AND uid=$uid WHERE state=1 AND `group`=$group AND uid IS null $open_sql");
		$ret[]=sql_fetch_rows("SELECT id,name,content,point,image,'--' as achieveGetTime FROM cfg_achivement b LEFT JOIN sys_user_achivement a ON a.achivement_id=b.id AND uid=$uid WHERE state=1 AND `group`=$group AND uid IS null $open_sql ORDER BY b.id  LIMIT $start, ".ACHIVE_PAGE_CPP); 
	}else if($type==0){
		$ret[]=sql_fetch_one_cell("SELECT count(*) FROM cfg_achivement b WHERE state=1 AND `group`=$group $open_sql");
		$ret[]=sql_fetch_rows("SELECT id,name,content,point,image,CASE WHEN time IS NULL THEN '--' ELSE time END AS achieveGetTime FROM cfg_achivement b LEFT JOIN sys_user_achivement a ON a.achivement_id=b.id  AND uid=$uid WHERE state=1 AND `group`=$group $open_sql ORDER BY time DESC,b.id ASC LIMIT $start, ".ACHIVE_PAGE_CPP); 
		
	}
	return $ret;
}

function getAchivementDetail($uid,$param) {
	$ret=array();
	$uid=intval(array_shift($param));
	$aid=intval(array_shift($param));
	$archiveInfo=sql_fetch_one("SELECT id,name,content,todo,image,type,sql_current_value,target_value FROM cfg_achivement WHERE id='$aid'");
	$ret[]=$archiveInfo;
	$progress=array();
	$sql="";
	if(sql_check("select 1 from sys_user_achivement where uid=$uid and achivement_id=$aid")){
		//$ret[]=1;
		if($archiveInfo["type"]==2){
			$tmp=array();
			$tmp["targetValue"]=$archiveInfo["target_value"];
			$tmp["userValue"]=$archiveInfo["target_value"];
			$progress[]=$tmp;
		}else if($archiveInfo["type"]==3){
			$progress=sql_fetch_rows("SELECT content,1 as isDone FROM cfg_achivement_goal a,cfg_achivement_goal_mapping b WHERE a.id=b.achivement_goal_id AND achivement_id=$aid");
		}
		$ret[]=$progress;
	}else{
		//$ret[]=0;
		if($archiveInfo["type"]==2){
			$tmp=array();
			$tmp["targetValue"]=$archiveInfo["target_value"];
			$sql=sprintf($archiveInfo["sql_current_value"],$uid);
			$tmp["userValue"]=sql_fetch_one_cell($sql);
			$progress[]=$tmp;
		}else if($archiveInfo["type"]==3){
			$goals=sql_fetch_rows("SELECT content,a.sql_check_goal FROM cfg_achivement_goal a,cfg_achivement_goal_mapping b WHERE a.id=b.achivement_goal_id AND achivement_id=$aid");
			foreach($goals as $goal) {
				$tmp=array();
				$tmp["content"]=$goal["content"];
				$sql=sprintf($goal["sql_check_goal"],$uid);
				$tmp["isDone"]=sql_check($sql);
				$progress[]=$tmp;
			}
		}
		$ret[]=$progress;
	}
	$ret[]=sql_fetch_one_cell("SELECT COUNT(0) FROM sys_user_achivement WHERE achivement_id='$aid'");
	$ret[0]["sql_current_value"]="";
	return $ret;
}
 
//$param= array();
//$param[]=1063;
//getOverviewStat(1063,$param);

?>