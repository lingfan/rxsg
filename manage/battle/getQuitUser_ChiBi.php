<?php
/**
 * @author 方鸿鹏
 * @method 跨服战场-战场参与人数
 * @param 
 * @return
 * array{
 * 0=>从战场正常开启，一直参与到战场正常结束的玩家人数
 * 1=>在赤壁之战结束前，玩家主动退出战场的人数
 * } 
 */

if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($startday)) {
    exit("param_not_exist");
}
if (! isset($endday)) {
    exit("param_not_exist");
}

//在赤壁之战结束前，玩家主动退出战场的人数
$num = sql_fetch_rows("select from_unixtime(time-(time+8*3600)%86400,'%Y-%m-%d') as day, count(*) as count
					   from log_chibi_quit 
					   where flag=1 and time>=unix_timestamp('$startday') 
					   and time<unix_timestamp('$endday')+86400 group by day",'chibinet');
$sql_error = mysql_error();
if(!empty($num)&&empty($sql_error)){
	$ret=$num;
}
else{
	$ret='on data';
}
?>