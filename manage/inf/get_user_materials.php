<?php
//获得铜币使用信息
//参数列表：
//cid:城市id
//许孝敦
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($uid))
    exit("param_not_exist");
if (isset($select)) {
    if (! isset($starttime))
        exit("param_not_exist");
    if (! isset($endtime))
        exit("param_not_exist");
    if (! empty($name)) {
        $ret = sql_fetch_rows("select g.uid as uid,count,from_unixtime(time) as formattime,type,passport,u.name as uname,c.name as gname from log_goods g,sys_user u,cfg_goods c where u.uid='$uid' and g.gid=c.gid and u.uid=g.uid and c.`group`=4 and g.`time` > unix_timestamp($starttime) and g.`time`<unix_timestamp($endtime)+86400 and c.name='$name'");
    } else {
    	$ret = sql_fetch_rows("select g.uid as uid,count,from_unixtime(time) as formattime,type,passport,u.name as uname,c.name as gname from log_goods g,sys_user u,cfg_goods c where u.uid='$uid' and g.gid=c.gid and u.uid=g.uid and c.`group`=4 and g.`time` > unix_timestamp($starttime) and g.`time`<unix_timestamp($endtime)+86400");
    }
} else {
    $ret = sql_fetch_rows("select g.uid as uid,count,from_unixtime(time) as formattime,type,passport,u.name as uname,c.name as gname from log_goods g,sys_user u,cfg_goods c where u.uid='$uid' and g.gid=c.gid and u.uid=g.uid and c.`group`=4 order by time desc");
}
if (empty($ret))
    $ret = 'no data';
?>