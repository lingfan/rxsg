<?php
/**
 * @author 许孝敦
 * @模块 运营数据 --月登陆人数
 * @功能 获得当前服务器月登陆人数
 */
if (! defined ( "MANAGE_INTERFACE" ))
	exit ();
if (! isset ( $startday )) {
	exit ( "param_not_exist" );
}
$ret[$startday] = sql_fetch_one_cell("select count(distinct(uid)) as lcount from log_login where from_unixtime(time,'%Y-%m') between '".$startday."' and '".$startday."' group by from_unixtime(time,'%Y-%m')");
?>