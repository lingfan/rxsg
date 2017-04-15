<?php
/**
 * @inform 产品接口 -- 日数据恢复
 * @author 许孝敦
 * @param null
 * @return array(today_avg,)
 * @example  
 */
include 'props_config.php';
if (! defined ( "MANAGE_INTERFACE" ))
	exit ();
if (! isset ( $type ) || empty ( $type )) {
	throw new Exception ( 'type error' );
}
function getData($name, $starttime, $endtime) {
	$info = sql_fetch_one ( "select group_concat(id) id,group_concat(gid) gid from cfg_shop where name in ('$name')" );
	if (empty ( $info ))
		return $result ['error'] = 'no data';
	$shopid = $info ['id'];
	$gid = $info ['gid'];
	$keys = explode ( ",", $gid );
	foreach ( $keys as $v ) {
		$result [$v] = array ();
	}
	$sql = "select b.name ItemName,sum(a.count) ItemSaleNum,sum(a.count* a.price) ItemSaleMoney,count(distinct uid) buyNum,b.gid from log_shop a left join cfg_shop b on a.shopid=b.id where shopid in ($shopid) and time >'$starttime' and time <='$endtime' group by shopid";
	$rows [1] = sql_fetch_rows ( $sql );
	$sql = "select sum(count) ItemRemainNum,sum(case when count>0 then 1 else 0 end) as ItemRemairPNumber,gid from sys_goods where gid in ($gid) group by gid";
	$rows [2] = sql_fetch_rows ( $sql );
	$sql = "select abs(sum(count)) ItemNumConsumptionNum,count(distinct uid) ItemNumConsumptionPNumber,gid from log_goods where gid in ($gid) and count<0 and time >'$starttime' and time <='$endtime' group by gid";
	$rows [3] = sql_fetch_rows ( $sql );
	foreach ( $rows [1] as $key => $value ) {
		$result [$value ['gid']] ['ItemName'] = $value ['ItemName'];
		$result [$value ['gid']] ['ItemSaleMoney'] = ($value ['ItemSaleMoney']);
		$result [$value ['gid']] ['buyNum'] = ($value ['buyNum']);
		$result [$value ['gid']] ['ItemSaleNum'] = ($value ['ItemSaleNum']);
	}
	foreach ( $rows [2] as $key => $value ) {
		$result [$value ['gid']] ['ItemRemainNum'] = ($value ['ItemRemainNum']);
		$result [$value ['gid']] ['ItemRemairPNumber'] = ($value ['ItemRemairPNumber']);
	}
	foreach ( $rows [3] as $key => $value ) {
		$result [$value ['gid']] ['ItemNumConsumptionNum'] = ($value ['ItemNumConsumptionNum']);
		$result [$value ['gid']] ['ItemNumConsumptionPNumber'] = ($value ['ItemNumConsumptionPNumber']);
	}
	foreach ( $result as $key => &$value ) {
		$name = $value ['ItemName'];
		if ($value ['ItemName'] == "") {
			$name = sql_fetch_one_cell ( "select name from cfg_shop where gid='$key' limit 1" );
			if (empty ( $name )) {
				$name = "未知";
			}
		}
		$value ['ItemName'] = $name;
		if ($value ['ItemSaleMoney'] == "") {
			$value ['ItemSaleMoney'] = 0;
		}
		if ($value ['buyNum'] == "") {
			$value ['buyNum'] = 0;
		}
		if ($value ['ItemSaleNum'] == "") {
			$value ['ItemSaleNum'] = 0;
		}
		if ($value ['ItemRemairPNumber'] == "") {
			$value ['ItemRemairPNumber'] = 0;
		}
		if ($value ['ItemNumConsumptionNum'] == "") {
			$value ['ItemNumConsumptionNum'] = 0;
		}
		if ($value ['ItemNumConsumptionPNumber'] == "") {
			$value ['ItemNumConsumptionPNumber'] = 0;
		}
		if ($value ['ItemRemainNum'] == "") {
			$value ['ItemRemainNum'] = 0;
		}
		$ret [] = $value;
	}
	return $ret;
}
$value = implode ( "','", $array [$type] );
$ret = getData ( $value, $starttime, $endtime );
?>