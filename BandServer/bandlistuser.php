<html> 
<head> 
<meta http-equiv="refresh" content="5" charset="utf-8">
</head> 
<body > 
<form id="listdb" name="listdb" method="post" action="">
 <? 
   require_once("dbinc.php");
   $deluserinfo =sql_fetch_rows("select * from banduser where state=1 and uid>1");
   if(!empty($deluserinfo)){
 ?>
     <table border="1" cellpadding="0" cellspacing="0" bordercolor="#FF0000" width="505" align="center"> 
	 <tr>
    	<td align="center" class="TitleRedWhite" colspan="3"><h3>你将要删除下列玩家帐号</h3></td>
  	</tr>
	<tr>
	        <td align="center" valign="middle" class="TitleBlueWhite">玩家账号</td>
	        <td align="center" valign="middle" class="TitleBlueWhite">君主名称</td>
	        <td align="center" valign="middle" class="TitleBlueWhite">所在区号</td>
      </tr>
 <?
	 foreach ($deluserinfo as $userinfo){//逐行获取结果集中的记录，得到数组row,数组row的下标对应着数据库中的字段值
         $olduid = $userinfo['passport']; //合服的uid
		 $name =$userinfo['name']; //合服后君主名
		 $bandnum = $userinfo['bandnum'].'区';
?>
	     <tr>
	        <td align="center" valign="middle" class="TitleBlueWhite"><?echo $olduid;?></td>
	        <td align="center" valign="middle" class="TitleBlueWhite"><?echo $name;?></td>
	        <td align="center" valign="middle" class="TitleBlueWhite"><?echo $bandnum;?></td>
      </tr>
     <?	} ?>
	 </table>
	 <p align="center">
       <input name="banddel"  value="开始删除" type="submit">
     </p> 
 <?	}     ?>
</form>
</body>
<html>
<?php
if(isset($_POST['banddel'])){
	  require_once("dbinc.php");
	  require_once("BandServer.php");
	  delBandSameUser();
	}
?>