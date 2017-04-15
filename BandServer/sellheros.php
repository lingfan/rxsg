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
 <iframe name="sformsubmitf12" style="display:none;">  
 </iframe>
 <form action="TradeFunction.php" method="POST" name="formphpsellhs" id="formphpsellhs"  target="sformsubmitf12">
   <div style="background:#800F33;color:#FF0000;margin:5px 10px;font-size:20px;text-align:center;">
    <select name="sellheroname" id="sellheroname" size="6" style="width:120px;" onchange="displayheroimages('sellheroname','herojpg','sellhprice');">
     <?
     $armorinfos = sql_fetch_rows("select * from sys_city_hero where uid='$myuids' and state=0 and npcid>0");		
     foreach($armorinfos as $armorinfo){ 
     ?> 
     <option value="<? echo $armorinfo['hid'].';'.$armorinfo['sex'].';'.$armorinfo['face'];?>" ><? echo $armorinfo['name'];?> </option> 
     <? 
     } 
     ?> 
    </select>   
    <img name="herojpg" id="herojpg"  style="width:80px;height:85px;" />
    </div>
    <div style="background:#00FF33;color:#FF0000;margin:5px 10px;font-size:24px;text-align:center;">售出价格<input name="sellhprice" id="sellhprice" color="#FF0000" type="text" style="width:60px;">元宝
    </div>
    <div style="background:#00FF33;color:#FF0000;margin:5px 10px;font-size:24px;text-align:center;">是否出售所选将领<input type="submit" name="sellhero" id="sellhero" value="是" onclick="getselectherohid('sellheroname','sellhprice','formphpsellhs','iframedisheroId','displayheros.php','sureherohids')" >
    </div>
	<input type="hidden" name="sureherohids" id="sureherohids"> 
   </form>
</body> 
</html> 