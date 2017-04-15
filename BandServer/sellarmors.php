<?php
  require_once("dbinc.php");
  $myuids=$_SESSION['myuid'];  
 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <script src="TradeMain.js" type="text/javascript">     
 </script> 
</head> 
<body>
 <iframe name="ddssformsubmit" style="display:none;">
 </iframe>
 <form action="TradeFunction.php" method="POST" name="formphpsella" id="formphpsella"  target="ddssformsubmit">
   <div style="background:#800F33;color:#FF0000;margin:5px 10px;font-size:20px;text-align:center;">
    <select name="aname" id="aname" size="6" style="width:120px;" onchange="displayimage('aname','armorpng','armorprice');">
     <?
     $armorinfos = sql_fetch_rows("select a.*,c.* from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$myuids' and a.hid=0");		
     foreach($armorinfos as $armorinfo){ 
     ?> 
     <option value="<? echo $armorinfo['sid'];?>" >  <? if($armorinfo['image']>0) echo $armorinfo['name'].'('.$armorinfo['image'].')';else echo $armorinfo['name'].'('.$armorinfo['id'].')';?> </option> 
     <? 
     } 
     ?> 
    </select>   
    <img name="armorpng" id="armorpng"  style="width:80px;height:85px;" />
    </div>
    <div style="background:#00FF33;color:#FF0000;margin:5px 10px;font-size:24px;text-align:center;">售出价格<input name="armorprice" id="armorprice" color="#FF0000" type="text" style="width:60px;">元宝
    </div>
    <div style="background:#00FF33;color:#FF0000;margin:5px 10px;font-size:24px;text-align:center;">是否出售所选装备<input type="submit" name="sellarmor" id="sellarmor" value="是" onclick="getselectarmor('aname','armorprice','formphpsella','iframedisaId','displayarmors.php','surearmorsid')" >
    </div>
	<input type="hidden" name="surearmorsid" id="surearmorsid"> 
   </form>
</body> 
</html> 