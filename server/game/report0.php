<?php  
if (!defined('PATH_SEPARATOR')) {if (substr(PHP_OS, 0, 3) == 'WIN') define('PATH_SEPARATOR', ';'); else define('PATH_SEPARATOR', ':');}	              
//设置根目录绝对路径到include_path,简化path的使用
ini_set('include_path',ini_get('include_path').PATH_SEPARATOR.realpath("../"));
require_once("./common.php"); 
require_once("./global.php");  
if (isset($_GET['id'])&&isset($_GET['uid'])&&isset($_GET['sid']))
{
    header('Content-Type: text/html; charset=utf-8');

	$id = intval ($_GET['id']);
	$type=strval($_GET['type']);
	$uid = intval($_GET['uid']);
	$sid = intval($_GET['sid']);
	$ip = $GLOBALS['ip'];
    try
    {
        checkUserAuth($uid,$sid);
    }
    catch(Exception $e)
    {
    	echo $GLOBALS['report']['connection_drop'];
        exit;
    }
    /*
//    if (!sql_check("select * from sys_sessions where uid='$uid' and sid='$sid' and ip='$ip'"))
    if (!sql_check("select * from sys_sessions where uid='$uid' and sid='$sid'"))
    {
        echo "你已经掉线，请重新登录。";
        exit;
    }
     */
    
    if($type=="report")
    {
    	$tablename="sys_report";
		$report = sql_fetch_one("select `id`,`uid`,from_unixtime(`time`,'%Y%m%d') as day from $tablename where uid='$uid' and id='".$id."'");
    }
    else if($type=="union_report")
    {
    	$tablename="sys_union_report";
    	$unionid=sql_fetch_one_cell("select `union_id` from `sys_user` where `uid`='$uid'");
    	$report=sql_fetch_one("select `id`,`unionid`,from_unixtime(`time`,'%Y%m%d') as day from $tablename where `id`='$id' and `unionid`='$unionid'");
    }
    
	if (empty($report))
	{
		echo $GLOBALS['report']['cant_operate_others'];
   		exit;
	}      
    $day = $report['day'];
    $id = $report['id'];
    $path = "./".$type."_data/".$day;
    $cachefile = $path."/".$id.".html";
    
    if (file_exists($cachefile))
    {
        $content = file_get_contents($cachefile);
        if ($content === FALSE) $content = sql_fetch_one_cell("select content from $tablename where id=".$id);
    }
    else
    {
	    $content = sql_fetch_one_cell("select content from $tablename where id=".$id);  
        chdir("./".$type."_data");
        if (!is_dir($day))
        {
            mkdir($day);
        }
        chdir("../");
        $ret = file_put_contents($cachefile,$content);
        if (!($ret === FALSE))
        {
        	if($type=="report")
        	{
        		sql_query("update $tablename set `read`=1, content='' where id='$id'");
        	}
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<style type="text/css">
<!--
body {
	background-color: #17292B;
	font-size:9pt;
}
body,td,th {
	color: #FFFFFF;
}
-->
</style>
<style type="text/css">
<!--
.NormalText {
	font-size: 12px;
	font-weight: normal;
	color: #FFFFFF;
}
.WinGreen {
	font-size: 14px;
	font-weight: bold;
	color: #00FF00;
	background:#225A5D
}
.LoseRed {
	font-size: 14px;
	color: #CC3333;
	font-weight: bold;
	background:#225A5D
}
.TitleBlueWhite {
	font-size: 14px;
	color: #FFFFFF;
	font-weight: bold;
	background:#225A5D
}
.TitleRedWhite {
	font-size: 14px;
	color: #FFFFFF;
	font-weight: bold;
	background:#5D2522
}
.TitleListWhite {
	font-size: 14px;
	color: #FFFFFF;
	font-weight: bold;
	background:#17292B
}
.NameBlue {
	font-size: 12px;
	font-weight: normal;
	color: #00D8FF
}
.TitleBattleYellow {
	font-size: 12px;
	font-weight: bold;
	color: #FFD200;
	background:#2D2414
}
.TextArmyCount {
	font-size: 12px;
	font-weight: normal;
	color: #FFFFFF;
	background:#000000
}
-->
</style>
<STYLE> 
html {   
scrollbar-arrow-color: #000000;  
scrollbar-base-color: #17292B;  
scrollbar-dark-shadow-color: #000000;  
scrollbar-track-color: #1A2B2F;  
scrollbar-face-color: #17292B;  
scrollbar-shadow-color: #000000;  
scrollbar-highlight-color: #000000;  
scrollbar-3d-light-color: #17292B;  
}  
</STYLE> 
</head>
<body>
<?php
	echo $content;  
?>           
</body>
</html>