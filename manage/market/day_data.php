<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
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
$regtime = sql_fetch_one_cell("select regtime from sys_user where uid=1001");
if ($day < $regtime) {
    throw new Exception('date error');
}
switch ($type) {
    case 'onlinetime':
        {
            $today = date("Y-m-d", $day);
            $yesterday = date("Y-m-d", $day- 86400);
            $tab_yesterday = "log_user_$yesterday";
            $tab_taday = "log_user_$today";
            $tables = sql_fetch_rows("show tables like 'log_user_%'", 'bloodwarlog');
            foreach ($tables as $value) {
                $table[] = $value['Tables_in_bloodwarlog (log_user_%)'];
            }
            if (in_array($tab_yesterday, $table) && in_array($tab_taday, $table)) {
            	$sql = "select today.passport,today.onlinetime-yesterday.onlinetime as onlinetime from `$tab_taday` today left join `$tab_yesterday` yesterday on today.uid = yesterday.uid where today.onlinetime-yesterday.onlinetime> 0";
                $result = sql_fetch_rows($sql, 'bloodwarlog');
            } else {
                $result = 0;
            }
            if (! empty($result)) {
                $ret['content']['onlinetime'] = $result;
            } else {
                $ret['content']['onlinetime'] = 0;
            }
            break;
        }
}
?>