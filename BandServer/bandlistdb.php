<html> 
<head> 
<meta http-equiv="refresh" content="5" charset="utf-8">
<style tyle="text/css">
  div{width:350px;top:10px;}.c1{float:center;}.c2{float:left;}
</style>
</head> 
<body> 
<form id="listdb" name="listdb" method="post" action="">
   <? 
   require_once("dbinc.php");
   $userinfo=sql_fetch_one("select * from banduser where uid='1'");
   $usernum=$userinfo['passport'];
   $userrec=$userinfo['bandnum'];
   if($userrec>0&&$userrec<10000){?>
   <div class="c1">------正在合服中请耐心等待--------</div>
   <div class="c2">本次合服共有玩家:<?echo $usernum;?>现已成功写入:<?echo $userrec;?>个玩家!</div>
   <? } elseif($userrec==10000){?>
      <p align="center"><font color="#FF0000">---本次合区已成功完成！---</font><p>
    <?
	  sql_query("update banduser set passport='0',bandnum='0' where uid='1'");
    }
   
   ?>
</form>
</body>
<html>