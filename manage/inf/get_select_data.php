<?php
/**
 * @author 方鸿鹏
 * @method 查询查看-sql查询 执行sql select语句
 * @param $select select语句
 * @return 
 */

if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($select)) {
    exit("param_not_exist");
}

$data = array();
if(!empty($select)){
	$data = sql_fetch_rows($select);
}
$ret['error'] = mysql_errno();
$ret['data'] = $data;
?>