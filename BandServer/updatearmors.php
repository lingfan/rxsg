<?php
  require_once("dbinc.php");
  $myuids=$_SESSION['myuid'];
  $mybaofuhu=sql_fetch_one_cell("select count from sys_goods where uid='$myuids' and gid=12157");//得到保护符数量
  $mysjtz=sql_fetch_one_cell("select count from sys_goods where uid='$myuids' and gid=8895");//得到升级图纸数量 
  $mysjbaoshi=sql_fetch_one_cell("select count from sys_goods where uid='$myuids' and gid=8896");//得到升级图纸数量 
 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <script src="TradeMain.js" type="text/javascript">     
 </script> 
</head> 
<body>
 <iframe name="upformsubmit" style="display:none;">
 </iframe>
 <form action="TradeFunction.php" method="POST" name="formphpupdate" id="formphpupdate"  target="upformsubmit">
  <div style="background:#FFF003;color:#FF0000;height:220px;margin:-5px -5px;font-size:22px;">
    <div style="background:#00FF33;color:#FF0000;">选择升级装备</div>
	  <div style="float:left;height:130px;">
      <select name="updaname" id="updaname" size="6" style="width:140px;" onchange="displayupdimage('updaname','updarmorpng','updateasid');">
	   <?
	   $armorinfos = sql_fetch_rows("select a.*,c.* from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$myuids' and a.hid=0");		
       foreach($armorinfos as $armorinfo){ 
       ?> 
         <option value="<? echo $armorinfo['sid'].";".$mybaofuhu.";".$mysjbaoshi.";".$mysjtz;?>" >  <? if($armorinfo['image']>0) echo $armorinfo['name'].'('.$armorinfo['image'].')';else echo $armorinfo['name'].'('.$armorinfo['id'].')';?> </option> 
       <? 
        } 
       ?> 
      </select>
      <img name="updarmorpng" id="updarmorpng"  style="width:100px;height:90px;" />
        <table>    
          <tr style="background:#99CC99;color:#FF0000;margin:5px 10px;font-size:12px;">
            <td width="245px" class="bordertop">1-3品质的装备->袁绍->冰封->冰魄->龙渊->白虎->朱雀->青龙</td>	        
          </tr>
		  <tr style="background:#33CC99;color:#FF0000;margin:5px 10px;font-size:12px;">
		    <td width="245px" class="bordertop">4-7品质的装备->曹操->冰封->冰魄->龙渊->白虎->朱雀->青龙</td>	        
          </tr>
		  <tr style="background:#00CCCC;color:#FF0000;margin:5px 10px;font-size:12px;">
             <td width="245px" class="bordertop">战场装备->冰封->冰魄->龙渊->白虎->朱雀->青龙 </td>	        
          </tr>		
        </table>	  
	  </div>
	  <div style="width:420px;height:130px;float:left;color:#990033;font-size:18px;display:block;text-align:center;text-decoration:none;padding:16px;">
	    升级保护符<img src="http://127.0.0.1/images/baofuhuz.png" style="width:64px;height:64px;" /><input name="bafuf" id="bafuf" type="text" style="width:50px;" value="<?echo $mybaofuhu;?>" readonly="readonly"/>
		           <input type="checkbox" name="baohufu" id="baohufu" ><br/>
	    消耗宝石<img src="http://127.0.0.1/images/sjbaoshi.png" style="width:64px;height:64px;" /><input name="yuanbo" id="yuanbo" type="text" style="width:40px;" value="<?echo $mysjbaoshi;?>" readonly="readonly"/>
		消耗图纸<img src="http://127.0.0.1/images/sjtz.png" style="width:64px;height:64px;" /><input name="sjtz" id="sjtz" type="text" style="width:40px;" value="<?echo $mysjtz;?>" readonly="readonly"/>
	  </div>	  
	  <div style="clear:both;text-align:center;">	   
	   <input  name="makearmor" type="submit" value="升级装备" onclick="getupdateselectarmor()" />	
       <input type="hidden" name="updateasid"  id="updateasid">		   
	  </div>
	</div>
   </form>
</body> 
</html> 