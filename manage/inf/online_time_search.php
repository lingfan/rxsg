<?php
	//玩家在线时间搜索
	//参数列表：
	//info:
	//startday开始时间
	//endday结束时间
	//name君主名
	//passport通行证
	//search_type搜索类型
	//
	//返回玩家在线时间
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($info)) {exit("param_not_exist");}
	
	if (empty($info['startday'])) $info['startday']=0;
	if (empty($info['endday'])) $info['endday']=date('Ymd');
	$starttime = strftime( "%Y-%m-%d",strtotime($info['startday']));
	$endtime = strftime( "%Y-%m-%d",strtotime($info['endday']));
	$starttable = "log_user_".$starttime;
	$endtable = "log_user_".$endtime;
	$tablelist = sql_fetch_rows("show tables like 'log_user_%-%-%'",'bloodwarlog');
	
	if (empty($tablelist)){
		$ret[]=0;		
	}
	else {
		$tablelist2 = array();
		foreach ($tablelist as $row){
			$tablelist2[]=$row['Tables_in_bloodwarlog (log_user_%-%-%)'];
		}
	
		if ($info['search_type']=='accuracy'){
			if ($info['passport']!=''){
				if ($starttable==$endtable){
					$ret[]=1;
					if (in_array($starttable,$tablelist2)){
						$ret[] = sql_fetch_one("select * from `$starttable` where passport = '$info[passport]'",'bloodwarlog');
					}
				}
				else {
					$ret[]=2;
					if (in_array($endtable,$tablelist2)){
						$ret[] = sql_fetch_one("select * from `$endtable` where passport = '$info[passport]'",'bloodwarlog');
						if (in_array($starttable,$tablelist2)){
							$ret[] = sql_fetch_one("select * from `$starttable` where passport = '$info[passport]'",'bloodwarlog');						
						}
					}				
				}
			}
			elseif ($info['name']!='') {
				if ($starttable==$endtable){
					$ret[]=1;
					if (in_array($starttable,$tablelist2)){					
						$ret[] = sql_fetch_one("select * from `$starttable` where name = '$info[name]'",'bloodwarlog');
					}
				}
				else {
					$ret[]=2;
					if (in_array($endtable,$tablelist2)){					
						$ret[] = sql_fetch_one("select * from `$endtable` where name = '$info[name]'",'bloodwarlog');
						if (in_array($starttable,$tablelist2)){
							$ret[] = sql_fetch_one("select * from `$starttable` where name = '$info[name]'",'bloodwarlog');						
						}
					}				
				}			
			}
			else {
				if ($starttable==$endtable){
					$ret[]=3;
					if (in_array($starttable,$tablelist2)){					
						$ret[] = sql_fetch_rows("select * from `$starttable`",'bloodwarlog');
					}
				}
				else {
					$ret[]=4;				
					if (in_array($endtable,$tablelist2)){					
						$ret[] = sql_fetch_rows("select * from `$endtable` ",'bloodwarlog');
						if (in_array($starttable,$tablelist2)){
							$ret[] = sql_fetch_rows("select * from `$starttable` ",'bloodwarlog');						
						}
					}				
				}
			}		
		}
		elseif ($info['search_type']=='blur'){
			if ($info['passport']!=''){
				if ($starttable==$endtable){				
					$ret[]=3;
					if (in_array($starttable,$tablelist2)){					
						$ret[] = sql_fetch_rows("select * from `$starttable` where passport like '%$info[passport]%'",'bloodwarlog');
					}
				}
				else {				
					$ret[]=4;
					if (in_array($endtable,$tablelist2)){					
						$ret[] = sql_fetch_rows("select * from `$endtable` where passport like '%$info[passport]%'",'bloodwarlog');
						if (in_array($starttable,$tablelist2)){
							$ret[] = sql_fetch_rows("select * from `$starttable` where passport like '%$info[passport]%'",'bloodwarlog');						
						}
					}				
				}
			}
			elseif ($info['name']!='') {
				if ($starttable==$endtable){
					$ret[]=3;				
					if (in_array($starttable,$tablelist2)){					
						$ret[] = sql_fetch_rows("select * from `$starttable` where name like '%$info[name]%'",'bloodwarlog');
					}
				}
				else {
					$ret[]=4;
					if (in_array($endtable,$tablelist2)){
						$ret[] = sql_fetch_rows("select * from `$endtable` where name like '%$info[name]%'",'bloodwarlog');
						if (in_array($starttable,$tablelist2)){
							$ret[] = sql_fetch_rows("select * from `$starttable` where name like '%$info[name]%'",'bloodwarlog');						
						}
					}				
				}
			}
			else {
				if ($starttable==$endtable){
					$ret[]=3;				
					if (in_array($starttable,$tablelist2)){					
						$ret[] = sql_fetch_rows("select * from `$starttable`",'bloodwarlog');
					}
				}
				else {			
					$ret[]=4;	
					if (in_array($endtable,$tablelist2)){					
						$ret[] = sql_fetch_rows("select * from `$endtable` ",'bloodwarlog');
						if (in_array($starttable,$tablelist2)){
							$ret[] = sql_fetch_rows("select * from `$starttable` ",'bloodwarlog');						
						}
					}
				}
			}
		}
	}
	
?>