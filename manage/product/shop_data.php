<?php
/**
 * @inform 产品接口 -- 日数据恢复
 * @author 许孝敦
 * @param null
 * @return array(today_avg,)
 * @example  
 */
if (! defined("MANAGE_INTERFACE"))
    exit();
function twotoone ($two)
//二维数组转成一维数组
{
    for ($i = 0; $i < count($two); $i ++) {
        for ($j = 0; $j < count($two[$i]); $j ++) {
            $one[] = $two[$i][$j];
        }
    }
    return $one;
}
try {
    //参数判断
    if (! isset($day) || empty($day)) {
        $time = date("Ymd");
        $day = sql_fetch_one_cell("select unix_timestamp('$time')");
    } else {
        if ($day > time()) {
            throw new Exception('date error');
        }
    }
    if (! isset($type) || empty($type)) {
        throw new Exception('type error');
    }
    if (! isset($name) || empty($name)) {
        throw new Exception('name error');
    }
    $array = array('GenExper' , 'CtoP' , 'PtoC' , 'QuickEnd' , 'SmallTrip' , 'MoSoonFinish' , 'HotBag' , 'Proxytask' , 'YuanbaoCons' , 'Treasure' , 'ShijinNews');
    if (in_array($type, $array)) {
        switch ($type) {
            case 'GenExper':
                {
                	$result['ItemName'] = $name;
                	$result['ItemSaleNum'] = 0;
                	$result['ItemSaleMoney'] = 0;
                	$result['buyNum'] = 0;
                	$result['ItemRemainNum'] = 0;
                	$result['ItemNumConsumptionNum'] = sql_fetch_one_cell("select count(*) from log_money where type =120 or type=121 and time > '$starttime' and time<='$endtime'");
                	$result['ItemNumConsumptionPNumber'] = sql_fetch_one_cell("select count(distinct uid) from log_money where type=120 or type=121 and time > '$starttime' and time<='$endtime'");
                	$result['ItemRemairPNumber'] = 0;
                    break;
                }
            case 'CtoP':
                {
                	$result['ItemName'] = $name;
                	$result['ItemSaleNum'] = 0;
                	$result['ItemSaleMoney'] = 0;
                	$result['buyNum'] = 0;
                	$result['ItemRemainNum'] = 0;
                	$result['ItemNumConsumptionNum'] = sql_fetch_one_cell("select count(*) from log_money where type = 50 and time > '$starttime' and time<='$endtime'");
                	$result['ItemNumConsumptionPNumber'] = sql_fetch_one_cell("select count(distinct uid) from log_money where type = 50 and time > '$starttime' and time<='$endtime'");
                	$result['ItemRemairPNumber'] = 0;
                    break;
                }
            case 'PtoC':
                {
                	$result['ItemName'] = $name;
                	$result['ItemSaleNum'] = 0;
                	$result['ItemSaleMoney'] = 0;
                	$result['buyNum'] = 0;
                	$result['ItemRemainNum'] = 0;
                	$result['ItemNumConsumptionNum'] = sql_fetch_one_cell("select count(*) from log_money where type = 51 and time > '$starttime' and time<='$endtime'");
                	$result['ItemNumConsumptionPNumber'] = sql_fetch_one_cell("select count(distinct uid) from log_money where type = 51 and time > '$starttime' and time<='$endtime'");
                	$result['ItemRemairPNumber'] = 0;
                    break;
                }
            case 'QuickEnd':
                {
                	$result['ItemName'] = $name;
                	$result['ItemSaleNum'] = 0;
                	$result['ItemSaleMoney'] = 0;
                	$result['buyNum'] = 0;
                	$result['ItemRemainNum'] = 0;
                	$result['ItemNumConsumptionNum'] = sql_fetch_one_cell("select count(*) from log_money where type = 71 and time > '$starttime' and time<='$endtime'");
                	$result['ItemNumConsumptionPNumber'] = sql_fetch_one_cell("select count(distinct uid) from log_money where type = 71 and time > '$starttime' and time<='$endtime'");
                	$result['ItemRemairPNumber'] = 0;
                    break;
                }
            case 'SmallTrip':
                {
                    $result['ItemName'] = $name;
                    $result['ItemSaleNum'] = sql_fetch_one_cell("select sum(count) from log_shop where shopid='66' and time >'$starttime' and time <='$endtime'");
                    $result['ItemSaleMoney'] = sql_fetch_one_cell("select sum(count*price) from log_shop where shopid='66' and time >'$starttime' and time <='$endtime'");
                    $result['buyNum'] = 0;
                    $result['ItemRemainNum'] = 0;
                    $result['ItemNumConsumptionNum'] = 0;
                    $result['ItemNumConsumptionPNumber'] = 0;
                    $result['ItemRemairPNumber'] = 0;
                    break;
                }
            case 'MoSoonFinish':
                { //墨家立即完成
                    $result['ItemName'] = $name;
                    $result['ItemSaleNum'] = 0;
                    $result['ItemSaleMoney'] = 0;
                    $result['buyNum'] = 0;
                    $result['ItemRemainNum'] = 0;
                    $result['ItemNumConsumptionNum'] = sql_fetch_one_cell("select count(*) from log_money where type = 70 and time > '$starttime' and time<='$endtime'");
                    $result['ItemNumConsumptionPNumber'] = sql_fetch_one_cell("select count(distinct uid) from log_money where type = 70 and time > '$starttime' and time<='$endtime'");
                    $result['ItemRemairPNumber'] = 0;
                    break;
                }
            case 'HotBag':
                { //打包
                    $result['ItemName'] = $name;
                    $result['ItemSaleNum'] = 0;
                    $result['ItemSaleMoney'] = 0;
                    $result['buyNum'] = 0;
                    $result['ItemRemainNum'] = 0;
                    $result['ItemNumConsumptionNum'] = sql_fetch_one_cell("select count(*) from log_money where type = 52 and time > '$starttime' and time<='$endtime'");
                    $result['ItemNumConsumptionPNumber'] = sql_fetch_one_cell("select count(distinct uid) from log_money where type = 52 and time > '$starttime' and time<='$endtime'");
                    $result['ItemRemairPNumber'] = 0;
                    break;
                }
            case 'Proxytask':
                { //委托任务抽税
                    $result['ItemName'] = $name;
                    $result['ItemSaleNum'] = 0;
                    $result['ItemSaleMoney'] = 0;
                    $result['buyNum'] = 0;
                    $result['ItemRemainNum'] = 0;
                    $result['ItemNumConsumptionNum'] = sql_fetch_one_cell("select count(*) from log_money where type = 54 and time > '$starttime' and time<='$endtime'");
                    $result['ItemNumConsumptionPNumber'] = sql_fetch_one_cell("select count(distinct uid) from log_money where type = 54 and time > '$starttime' and time<='$endtime'");
                    $result['ItemRemairPNumber'] = 0;
                    break;
                }
            case 'YuanbaoCons':
                { //休假消耗
                    $result['ItemName'] = $name;
                    $result['ItemSaleNum'] = 0;
                    $result['ItemSaleMoney'] = 0;
                    $result['buyNum'] = 0;
                    $result['ItemRemainNum'] = 0;
                    $result['ItemNumConsumptionNum'] = sql_fetch_one_cell("select count(*) from log_money where type = 75 and time > '$starttime' and time<='$endtime'");
                    $result['ItemNumConsumptionPNumber'] = sql_fetch_one_cell("select count(distinct uid) from log_money where type = 75 and time > '$starttime' and time<='$endtime'");
                    $result['ItemRemairPNumber'] = 0;
                    break;
                }
            case 'Treasure':
                { //鉴定宝藏
                    $result['ItemName'] = $name;
                    $result['ItemSaleNum'] = 0;
                    $result['ItemSaleMoney'] = 0;
                    $result['buyNum'] = 0;
                    $result['ItemRemainNum'] = 0;
                    $result['ItemNumConsumptionNum'] = sql_fetch_one_cell("select count(*) from log_money where type = 53 and time > '$starttime' and time<='$endtime'");
                    $result['ItemNumConsumptionPNumber'] = sql_fetch_one_cell("select count(distinct uid) from log_money where type = 53 and time > '$starttime' and time<='$endtime'");
                    $result['ItemRemairPNumber'] = 0;
                    break;
                }
             case 'ShijinNews':
                { //市井传闻
                    $result['ItemName'] = $name;
                    $result['ItemSaleNum'] = 0;
                    $result['ItemSaleMoney'] = 0;
                    $result['buyNum'] = 0;
                    $result['ItemRemainNum'] = 0;
                    $result['ItemNumConsumptionNum'] = sql_fetch_one_cell("select count(*) from log_money where type = 90 and time > '$starttime' and time<='$endtime'");
                    $result['ItemNumConsumptionPNumber'] = sql_fetch_one_cell("select count(distinct uid) from log_money where type = 90 and time > '$starttime' and time<='$endtime'");
                    $result['ItemRemairPNumber'] = 0;
                    break;
                }
        }
    } else {
        $result['ItemName'] = $name;
        $info = sql_fetch_one("select id,gid from cfg_shop where name='" . $result['ItemName'] . "'");
        if (empty($info))
            throw new Exception('no data');
        $shopid = $info['id'];
        $gid = $info['gid'];
        $result['ItemSaleNum'] = sql_fetch_one_cell("select sum(count) from log_shop where shopid='$shopid' and time >'$starttime' and time <='$endtime'");
        if (empty($result['ItemSaleNum']))
            $result['ItemSaleNum'] = 0;
        $result['ItemSaleMoney'] = sql_fetch_one_cell("select sum(count*price) from log_shop where shopid='$shopid' and time >'$starttime' and time <='$endtime'");
        if (empty($result['ItemSaleMoney']))
            $result['ItemSaleMoney'] = 0;
        $result['buyNum'] = sql_fetch_one_cell("select count(distinct uid) from log_shop where shopid='$shopid' and time >'$starttime' and time <='$endtime'");
        if (empty($result['buyNum']))
            $result['buyNum'] = 0;
        $result['ItemRemainNum'] = sql_fetch_one_cell("select sum(count) from sys_goods where gid='$gid'");
        if (empty($result['ItemRemainNum']))
            $result['ItemRemainNum'] = 0;
        $result['ItemNumConsumptionNum'] = sql_fetch_one_cell("select abs(sum(count)) from log_goods where gid='$gid' and time >'$starttime' and time <='$endtime' and count<0");
        if (empty($result['ItemNumConsumptionNum']))
            $result['ItemNumConsumptionNum'] = 0;
        $result['ItemNumConsumptionPNumber'] = sql_fetch_one_cell("select count(distinct uid) from log_goods where gid='$gid' and time >'$starttime' and time <='$endtime' and count<0");
        if (empty($result['ItemNumConsumptionPNumber']))
            $result['ItemNumConsumptionPNumber'] = 0;
        $result['ItemRemairPNumber'] = sql_fetch_one_cell("select count(distinct uid) from sys_goods where gid='$gid' and count > 0");
        if (empty($result['ItemRemairPNumber']))
            $result['ItemRemairPNumber'] = 0;
    }
    if (! empty($result)) {
        $ret['content']['GameStoreData'] = $result;
    } else {
        $ret['content']['GameStoreData'] = 0;
    }
} catch (exception $e) {
    $ret = array();
    $ret['error'] = $e->getMessage();
}