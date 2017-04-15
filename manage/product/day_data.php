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
    //	$regtime = sql_fetch_one_cell("select regtime from sys_user where uid=1001");
    //	if($day < $regtime){
    //		throw new Exception('date error');
    //	}
    switch ($type) {
        case 'day_JoinNum':
            {
                /*************战场参与人数*************/
                $sql = "select count(1) as count,from_serverid as server from (select distinct uid,left(from_unixtime(unix_timestamp(jointime)-3*3600),11) day from bak_sys_user_battle_state where unix_timestamp(jointime) > '$starttime' and unix_timestamp(jointime) <= '$endtime') b left join sys_user a on a.uid=b.uid group by a.from_serverid";
                $result = sql_fetch_rows($sql, 'battlenet');
                if (! empty($result)) {
                    $ret['content']['GameJoinNum'] = $result;
                } else {
                    $ret['content']['GameJoinNum'] = 0;
                }
                break;
            }
        case 'day_NewNum':
            {
                //战场新增人数
                //$sql = "select count(new.uid),new.from_serverid as server from (select uid,from_serverid from sys_user where regtime > unix_timestamp('$starttime') and regtime <= unix_timestamp('$starttime') and state = 0) new left join (select uid,from_serverid from sys_user where regtime < unix_timestamp('$endtime') and state =0) old on new.uid=old.uid where old.uid is null group by server";
                $sql = "select count(unew.uid) as `count`,unew.from_serverid as `server` from (select uid,from_serverid from sys_user where regtime > '$starttime' and regtime <= '$endtime' and state = 0) unew left join (select uid,from_serverid from sys_user where regtime < unix_timestamp('$starttime') and state =0) uold on unew.uid=uold.uid where uold.uid is null group by server";
                $result = sql_fetch_rows($sql, 'battlenet');
                if (! empty($result)) {
                    $ret['content']['GameNewNum'] = $result;
                } else {
                    $ret['content']['GameNewNum'] = 0;
                }
                break;
            }
        case 'day_SActiveNum':
            {
                //每周完成5场跨服战场
                $sql = "select count(1) as count,a.from_serverid as server from (select count(*) as count,uid from bak_sys_user_battle_state where quittime > '$starttime' and quittime <= '$endtime' group by uid having count>5) b left join sys_user a on a.uid=b.uid group by a.from_serverid";
                $result = sql_fetch_rows($sql, 'battlenet');
                if (! empty($result)) {
                    $ret['content']['GameSActiveNum'] = $result;
                } else {
                    $ret['content']['GameSActiveNum'] = 0;
                }
                break;
            }
        case 'day_BActiveNum':
            {
                //每周完成10场跨服战场
                $sql = "select count(1) as count,a.from_serverid as server from (select count(*) as count,uid from bak_sys_user_battle_state where quittime > '$starttime' and quittime <= '$endtime' group by uid having count>10) b left join sys_user a on a.uid=b.uid group by a.from_serverid";
                $result = sql_fetch_rows($sql, 'battlenet');
                if (! empty($result)) {
                    $ret['content']['GameBActiveNum'] = $result;
                } else {
                    $ret['content']['GameBActiveNum'] = 0;
                }
                break;
            }
        case 'day_TimeInterval':
            {
                //时间区间参与人数
                $sql = "select count(distinct uid) as `count` from bak_sys_user_battle_state where unix_timestamp(jointime) > '$starttime' and unix_timestamp(jointime) < '$endtime'";
                $result = sql_fetch_one_cell($sql, 'battlenet');
                if (! empty($result)) {
                    $ret['content']['PlayersNum'] = $result;
                } else {
                    $ret['content']['PlayersNum'] = 0;
                }
                break;
            }
        case 'day_TimeIntervalServerOpen':
            {
                //时间区间参战场开启次数
                $sql = "select count(*) as count from sys_user_battle_field where starttime > '$starttime' and starttime <= '$starttime'";
                $result = sql_fetch_one_cell($sql, 'battlenet');
                if (! empty($result)) {
                    $ret['content']['BattleOpenTimes'] = $result;
                } else {
                    $ret['content']['BattleOpenTimes'] = 0;
                }
                break;
            }
        case 'day_PointPlayerNum':
            {
                //玩家积分区间
                $sql = "select count(*) as count from sys_user where battle_score>$startPoint and battle_score<=$endPoint";
                $result = sql_fetch_one_cell($sql,'battlenet');
                if (! empty($result)) {
                    $ret['content']['PointPlayersNum'] = $result;
                } else {
                    $ret['content']['PointPlayersNum'] = 0;
                }
                break;
            }
        case 'day_EquipmentPlayerNum':
            {
                //装备兑换人数
                $sql = "select count(distinct uid) as count from log_goods where gid>31000 and gid<31015 and time>'$starttime' and time<='$endtime'";
                $result = sql_fetch_one_cell($sql);
                if (! empty($result)) {
                    $ret['content']['ExchangePlayeNum'] = $result;
                } else {
                    $ret['content']['ExchangePlayeNum'] = 0;
                }
                break;
            }
        case 'day_EquipmentNum':
            {
                //装备兑换数量
                $sql = "select sum(count) as count from log_goods where gid>31000 and gid<31015 and time>'$starttime' and time<='$endtime'";
                $result = sql_fetch_one_cell($sql);
                if (! empty($result)) {
                    $ret['content']['ExchangeNum'] = $result;
                } else {
                    $ret['content']['ExchangeNum'] = 0;
                }
                break;
            }
      case 'day_PayLog':
            {
                //充值详单
                $sql = "select passport,type,money,time from pay_log where time>'$starttime' and time<='$endtime'+86400";
                $result = sql_fetch_rows($sql);
                if (! empty($result)) {
                    $ret['content']['PayLog'] = $result;
                } else {
                    $ret['content']['PayLog'] = 0;
                }
                break;
            }
        case 'day_CanJoin':
            {
                //符合进入跨服战场条件的总人数
                sql_query("set group_concat_max_len=9999999");
                $sql = "select group_concat(distinct uid) from log_login where time>'$starttime' and time<='$endtime'";
                $uids = sql_fetch_one_cell($sql);
                if(!empty($uids)){
                $sql = "select count(*) count from sys_user where regtime<='$starttime'+86400 and nobility>=5 and state=0 and uid in ($uids)";
                $result = sql_fetch_one_cell($sql);
                }else{
                $result=0;
                }
               
                if (! empty($result)) {
                    $ret['content']['CanJoinNum'] = $result;
                } else {
                    $ret['content']['CanJoinNum'] = 0;
                }
                break;
            }
           case 'day_Lost':
            {
                //符合进入条件的总人数
                sql_query('set group_concat_max_len=9999999','battlenet');
                $sql = "select group_concat(uid) from bak_sys_user_battle_state where unix_timestamp('jointime') <='$endtime'+86400*7 and unix_timestamp('jointime')>'$endtime'";
                $uids = sql_fetch_one_cell($sql,'battlenet');
               	if(!empty($uids)){
                $sql="select count(*) count,from_serverid from sys_user where regtime>'$starttime' and regtime<='$endtime' and uid not in ($uids) group by from_serverid";
                $result = sql_fetch_rows($sql,'battlenet');
                }else{
                	$result=0;
                }
                if (! empty($result)) {
                    $ret['content']['LostNum'] = $result;
                } else {
                    $ret['content']['LostNum'] = 0;
                }
                break;
            }
    }
} catch (exception $e) {
    $ret = array();
    $ret['error'] = $e->getMessage();
}