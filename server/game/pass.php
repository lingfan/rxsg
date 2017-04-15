<?php
require_once("./dbinc.php");
$file = "/bloodwar/pass.js";
if(!file_exists($file)) {
	echo $file." not exists!\n";
}
$handle=fopen($file,'r');
if (empty($handle)) {
	echo $file." cannot open!\n";
}
echo "begin......\n";
sql_query("drop table if exists `cfg_pass`");
sql_query("CREATE TABLE `cfg_pass` (`name` varchar(255) NOT NULL,`value` text default NULL,PRIMARY KEY  (`name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

$name=$value=null;
$flag = 0;
while (!feof($handle)) {
	$line = fgets($handle);
	$line = trim($line);
	if (strpos($line,'/')!== false && strpos($line,'/')== 0) {
		continue;
	}
	if (stripos($line,'function')!==false && stripos($line,'{') <= strlen($line)+5) {
		$line = trim($line,'function');
		$line = trim($line);
		$line = trim($line,'{');
		$line = trim($line,'()');
		$line = trim($line,')');
		$line = trim($line,'(');
		$line = trim($line);
		$name = $line;
	}
	if (stripos($line,'return')!==false) {
		$line = trim($line,'return');
		$line = trim($line);
		$line = trim($line,'"');
		$line = trim($line,'";');
		$value = addslashes($line);
		$flag = 2;
	}
	if ($flag == 2) {
		sql_query("insert into cfg_pass(name,value) values('$name','$value')");
		$flag=0;
		$name=$value=null;
	}
}

fclose($handle);
sql_query("insert into cfg_pass(`name`,`value`) values('getReportUrl','server/game/report.php?')");
echo "finish,ok!\n";
