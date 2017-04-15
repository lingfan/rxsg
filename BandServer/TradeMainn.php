<?php
if(!isset($_SESSION)){
    session_start();
}
require_once("dbinc.php");
require_once("Bandutils.php");
$passport=$_GET['user_name'];
$userinfo=sql_fetch_one("select uid,name,money from sys_user where passport='$passport'");
$myyuanbao=$userinfo['money'];
$mybaofuhu=sql_fetch_one_cell("select count from sys_goods where uid='$userinfo[uid]' and gid=12157");
$mysjtz=sql_fetch_one_cell("select count from sys_goods where uid='$userinfo[uid]' and gid=8895");
$_SESSION['myuid'] = $userinfo['uid'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <title>玩家交易系统</title>
 <style type="text/css"> 
   table{border-collapse:collapse;border-spacing:0px; width:100%; border:#000 solid 1px;} 
   table td{border:1px solid #000;height:25px; text-align:center; border-left:0px;} 
   table th{ background:#edd3d4; color:#a10333; border:#000 solid 1px; white-space:nowrap; height:21px; border-top:0px;border-left:0px;} 
  .t_left{width:30%; height:auto; float:left;border-top:1px solid #000;border-left:1px solid #000;} 
  .t_r_content{width:100%; height:168px; background:#fff; overflow:auto;font-size:14px;} 
  .cl_freeze{height:140px;overflow:hidden; width:100%;} 
  .t_r{width:69.5%; height:auto; float:left;border-top:1px solid #000; border-right:#000 solid 1px;font-size:16px;}  
  .t_r table{width:516px;} 
  .t_r_title{width:516px;}
  .t_r_t{width:100%; overflow:hidden;font-size:14px;}
  .t_r2{width:102.2%; height:auto; float:left;border-top:1px solid #000; border-right:#000 solid 1px;font-size:16px;}  
  .t_r_title2{width:510px;}
  .t_r_t2{width:100%; overflow:hidden;font-size:14px;} 
  .t_r_content2{width:100%; height:138px; background:#fff; overflow:auto;font-size:14px;}   
  .bordertop{ border-top:0px;} 
  .aa{width:163px; float:left;}
  .aa li{width:100px;height:40px;line-height:28px;background:#00FF33;color:#FF0000;margin:5px 10px;font-size:18px;display:block;text-align:center;text-decoration:none;padding:6px;cursor:pointer;}
  .mydiv1{width:80px;float:left;background:#0022FF;color:#FF0033;margin:5px 10px;font-size:18px;display:block;text-align:center;text-decoration:none;padding:16px;}
  .bb{width:810px; float:left;background:#00FF33;}
 </style>
 <script language="javascript" type="text/javascript"> 
   function CheckHeroTxtIsNum(myid){
      var elementTxt=document.getElementById(myid).value;
      var num=Number(elementTxt);
	  if(num>0){
	     return true ;
        }
	  document.getElementById(myid).focus();
	  document.getElementById(myid).value=10;
	  return false ;
    } 
   function setTab(m,n){
     var tli=document.getElementById("leftmenu"+m).getElementsByTagName("li");
	 var mli=document.getElementById("mcont"+m).getElementsByTagName("ul"); 
	 for(i=0;i<tli.length;i++){ 
	     tli[i].className=i==n?"hover":"";
	     mli[i].style.display=i==n?"block":"none"; 
	    }
    
	}
   function displayimage(){
   	 var rename=document.getElementById("aname").options[document.getElementById("aname").selectedIndex].text;
     document.getElementById("armoname").value=rename;
	 var filename ="http://127.0.0.1/images/armor/"+rename.replace(/[^0-9]/ig,"");
	 document.getElementById("armorpng").src=filename+".png";
	 document.getElementById("armorprice").focus();
	}
   function displaydimage(){
     var rename=document.getElementById("daname").options[document.getElementById("daname").selectedIndex].text;
     var filename ="http://127.0.0.1/images/armor/"+rename.replace(/[^0-9]/ig,"");
	 document.getElementById("darmorpng").src=filename+".png";
	 document.getElementById("bafuf").value=filename+".png";
	 document.getElementById("yuanbao").value=filename+".png";
	 document.getElementById("sjtz").value=filename+".png";	 
    }
   function displayhimage(hersimid){
     var rename=document.getElementById("hname").options[document.getElementById("hname").selectedIndex].text;
     document.getElementById("heroname").value=rename;
	 var myher=hersimid.split(",");
	 var sexid=Number(myher[1]);
	 if(sexid>0){
	     var sexname="男";
	     var filename ="http://127.0.0.1/images/hero/"+"hero_boy_"+Number(myher[2]);
		}
	  else{
	     var filename ="http://127.0.0.1/images/hero/"+"hero_girl_"+Number(myher[2]);
		 var sexname="女";
	    }
	 document.getElementById("heropng").src=filename+".jpg";
	 document.getElementById("heros").value=sexname;
	 document.getElementById("herod").value=Number(myher[3]);
	 document.getElementById("heroc").value=Number(myher[4]);
	 document.getElementById("heroa").value=Number(myher[5]);
	 document.getElementById("herob").value=Number(myher[6]);
	 document.getElementById("herow").value=Number(myher[7]);
	}
   function aa(){ 
     var a=document.getElementById("t_r_content").scrollTop; 
     var b=document.getElementById("t_r_content").scrollLeft; 
      document.getElementById("cl_freeze").scrollTop=a; 
      document.getElementById("t_r_t").scrollLeft=b; 
    } 
   function setselectid(objid,objname,myobj){
     var obj = document.getElementById(myobj); 
	 var chk = document.getElementById(objid);
	 if(chk.checked){ 
        obj.options.add(new Option(objname,objid));
	   }else{
	     var length=obj.length;
         for(var i=0;i<length;i++){ 
             if(objid==obj.options[i].value){
			      obj.options.remove(i); 
				  return true;
			    } 
            } 
	    }
    }
   function setselectids(objid,objname,myobj,s,sm){
     var obj = document.getElementById(myobj);
     var objids=objid+s;
     var chk = document.getElementById(objids);
	 if(chk.checked){
	    sm++;
        var sms=objname+"("+sm+")";
        obj.options.add(new Option(sms,objid));
	   }else{
	     var length=obj.length;
         for(var i=0;i<length;i++){ 
             if(objid==obj.options[i].value){
			      obj.options.remove(i); 
				  return true;
			    } 
            } 
	    }
    }
   function getallselect(myobj){
      var obj = document.getElementById(myobj); 
	  var length=obj.length;
      for(i=0;i<length;i++){
         obj.options[i].selected = true;
        }
    } 
  function deleteCurRow(event){
	  var r;
	  if(document.all){
         r = event.srcElement.parentNode.parentNode;		
		}else{ 
		 r = event.target.parentNode.parentNode; 
		} 
	   r.parentNode.deleteRow(r.rowIndex);
	}
  function TabelinsRow(myTable){
	 var obj=document.getElementById(myTable).insertRow();
	 obj.insertCell().innerHTML = "<td>添加一行	        </td>";
	 obj.insertCell().innerHTML = "<td>2        </td>";
	 obj.insertCell().innerHTML = "<td>3        </td> ";
	 obj.insertCell().innerHTML = "<td><a href='#' onclick='deleteCurRow(event)'>delete current row</a>  </td> ";
	}   
 </script>
</head>
<body> 
  <iframe name="formsubmit" style="display:none;">
  </iframe>
  <div class="aa">
  <ul id="leftmenu0">
   <li class="hover" onclick="setTab(0,0)">出售装备</li>
   <li onclick="setTab(0,1)">购买装备</li>
   <li onclick="setTab(0,2)">升级装备</li>
   <li onclick="setTab(0,3)">出售名将</li>
   <li onclick="setTab(0,4)">购买名将</li>
  </ul>
  </div>
  <br/>
  <form action="TradeFunction.php" method="POST" name="formphp0" target="formsubmit">
  <div id="mcont0" class="bb">  
  <ul class="block" style="display: block">
     <!----出售装备----> <form action="TradeFunction.php" method="POST" name="formphp0" target="formsubmit1">   
	  <font color="#FF0000" size="5">
	   <div style="text-align:center;">-------玩家:[<?  echo $userinfo['name'];?>]装备售出系统-------</div><font color="#FF0000" size="1"><br/></font>
	   <div style="width:226px;float:left;">
	    <div>
         选择装备<input name="armorname" id="armoname" color="#FF0000" type="text"  style="width:100px;" readonly="readonly"><br/>
         <select name="aname" id="aname" size="6" style="width:120px;" onchange="displayimage();">
	      <?
          $armorinfos = sql_fetch_rows("select a.*,c.* from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$userinfo[uid]' and a.hid=0");		
          foreach($armorinfos as $armorinfo){ 
          ?> 
           <option value="<? echo $armorinfo['sid'];?>" >  <? if($armorinfo['image']>0) echo $armorinfo['name'].'('.$armorinfo['image'].')';else echo $armorinfo['name'].'('.$armorinfo['id'].')';?> </option> 
          <? 
           } 
          ?> 
         </select>
         <img name="armorpng" id="armorpng"  style="width:80px;" /><br/><font color="#FF0000" size="1"><br/></font>
         售出价格<input name="armorprice" id="armorprice" color="#FF0000" type="text" style="width:60px;">元宝<br/><font color="#FF0000" size="1"><br/></font>
		 
	     <font color="#FF0000" size="5">是否出售装备</font><input name="sellarmor"  value="是" onclick="TabelinsRow('selltable1')" type="submit"><br/>
		
	    </div>
	   </div> </form> <iframe name="formsubmit1" style="display:block;">
	   <div style="width:516px;float:left;">
	    <div style="text-align:center;">已售装备情况一览表</div>
		 <div>
          <div class="t_r2"> 
           <div class="t_r_t2" id="t_r_t"> 
            <div class="t_r_title2"> 
             <table> 
              <tr> 
               <th width="20%">装备名称</th> 
               <th width="20%">装备品质</th> 
               <th width="20%">强化等级</th> 
               <th width="20%">属性加成</th> 
               <th width="20%">出售价格</th> 
              </tr> 
             </table> 
            </div> 
           </div> 
		   
           <div class="t_r_content2" id="t_r_content" onscroll="aa()">
             
 	   
            <table id="selltable1">           
	         <?
			 $myuid=$_SESSION['myuid']; 
		     $arrmortype=array(1=>"灰装",2=>"白装",3=>"绿装",4=>"蓝装",5=>"紫装",6=>"橙装",7=>"红装");
             $armorinfos = sql_fetch_rows("select a.*,c.* from sys_user_armor_trade a left join cfg_armor c on c.id=a.armorid where a.uid='$myuid' and a.trade=0");	
             if(!empty($armorinfos)) foreach($armorinfos as $armorinfo){ 
             ?> 
             <tr>
              <td width="20%" class="bordertop"><? echo $armorinfo['name'];?></td>
	          <td width="20%" class="bordertop"><? echo $arrmortype[$armorinfo['type']];?></td>
	          <td width="20%" class="bordertop"><? echo $armorinfo['strong_level'];?></td>
	          <td width="20%" class="bordertop"><? echo $armorinfo['sid'];?></td>
		      <td width="20%" class="bordertop"><? echo $armorinfo['sid'];?>
			    <input type="checkbox" name="<? echo $armorinfo['sid'].'s';?>" id="<? echo $armorinfo['sid'].'s';?>" value="<? echo $armorinfo['sid'];?>" onclick="deleteCurRow(event)">
			  </td>
             </tr>
		     <? 
             } 
             ?> 
            </table>
			 </div>
				 
	      </div>
		</div>
	   </div> </iframe>
      </font>
  </ul>
  <ul class="block" style="display: none">
      <!----购买装备----->
      <font color="#FF0000" size="5">&nbsp　　　　　&nbsp可供购买装备一览表</font>
	  <div class="t_r"> 
       <div class="t_r_t" id="t_r_t"> 
        <div class="t_r_title"> 
            <table> 
             <tr> 
               <th width="20%">装备名称</th> 
               <th width="20%">装备品质</th> 
               <th width="20%">强化等级</th> 
               <th width="20%">属性加成</th> 
               <th width="20%">出售价格</th> 
             </tr> 
            </table> 
        </div> 
       </div> 
       <div class="t_r_content" id="t_r_content" onscroll="aa()"> 
         <table>           
	     <?
		 $arrmortype=array(1=>"灰装",2=>"白装",3=>"绿装",4=>"蓝装",5=>"紫装",6=>"橙装",7=>"红装");
         $armorinfos = sql_fetch_rows("select a.*,c.name,c.type from sys_user_armor_trade a left join cfg_armor c on c.id=a.armorid  where uid!='$userinfo[uid]' and trade=0 ");		
         if(!empty($armorinfos)) foreach($armorinfos as $armorinfo){ 
         ?> 
          <tr>
           <td width="20%" class="bordertop"><? echo $armorinfo['name'];?></td>
	       <td width="20%" class="bordertop"><? echo $arrmortype[$armorinfo['type']];?></td>
	       <td width="20%" class="bordertop"><? echo $armorinfo['strong_level'];?></td>
	       <td width="20%" class="bordertop"><? echo $armorinfo['sid'];?></td>
		   <td width="20%" class="bordertop"><? echo $armorinfo['sid'];?><input type="checkbox" name="<? echo $armorinfo['sid'];?>" id="<? echo $armorinfo['sid'];?>" value="<? echo $armorinfo['sid'];?>" onclick="setselectid(this.value,'<? echo $armorinfo['name'];?>','mygoods')"></td>
          </tr>
		 <? 
         } 
         ?> 
         </table>
      </div>
	  <div class="t_r_title"> 
            <table> 
             <tr> 
               <th width="100%"></th>                 
             </tr> 
            </table> 
      </div> 
	 </div>
	 &nbsp&nbsp&nbsp&nbsp　　　　　&nbsp&nbsp&nbsp&nbsp <font color="#FF0000" size="5">已选装备列表</font>
	 <br/>
     <select name="mygoods[]"  multiple="yes" id="mygoods" size="11" style="width:140px;">
	 </select>
	 <br/>
	 <input  name="buyarmor" onclick="getallselect('mygoods')" type="submit" value="购买装备" />
   </ul>
   <ul class="block" style="display: none">
     <!----装备升级----->
     <font color="#FF0000" size="5">&nbsp&nbsp　　　　　　&nbsp&nbsp-------玩家:[<?echo $userinfo['name'];?>]装备升级系统-------<br/></font>
      <div>选择升级装备</div>
	  <div style="float:left;">
      <select name="daname" id="daname" size="6" style="width:140px;" onchange="displaydimage();">
	   <?
	   $armorinfos = sql_fetch_rows("select a.*,c.* from sys_user_armor a left join cfg_armor c on c.id=a.armorid where a.uid='$userinfo[uid]' and c.tieid>0 and a.hid=0");		
       foreach($armorinfos as $armorinfo){ 
       ?> 
         <option value="<? echo $armorinfo['sid'];?>" >  <? if($armorinfo['image']>0) echo $armorinfo['name'].'('.$armorinfo['image'].')';else echo $armorinfo['name'].'('.$armorinfo['id'].')';?> </option> 
       <? 
        } 
       ?> 
      </select>&nbsp;
      <img name="darmorpng" id="darmorpng"  style="width:80px;" />
	  </div>
	  <div style="width:380px;height:60px;float:left;background:#FFF003;color:#FF0033;margin:5px 10px;font-size:18px;display:block;text-align:center;text-decoration:none;padding:16px;">
	    升级保护符<img src="http://127.0.0.1/images/baofuhu.png" /><input name="bafuf" id="bafuf" type="text" style="width:50px;" readonly="readonly"/><input type="checkbox" name="baohufu" id="baohufu" value="510112"><br/>
	    消耗元宝<img src="http://127.0.0.1/images/yuanbao.png" /><input name="yuanbo" id="yuanbo" type="text" style="width:50px;" readonly="readonly"/>&nbsp　&nbsp
		消耗图纸<img src="http://127.0.0.1/images/sjtz.png" /><input name="sjtz" id="sjtz" type="text" style="width:50px;" readonly="readonly"/>
	  </div>	  
	  <div style="clear:both;">
	   <br/>
	   <p align="center"><input  name="makearmor" type="submit" value="升级装备" /></p>
	  </div>
   </ul>
   <ul class="block" style="display: none">
     <!----出售名将----->
      <p align="center"><font color="#FF0000" size="5">-------玩家:[<?  echo $userinfo['name'];?>]名将交易系统-------</font></p>
      <p align="left"><font color="#FF0000" size="5">&nbsp选择交易名将:</font>
       <input name="heroname" id="heroname" color="#FF0000" type="text"  style="width:120px;" readonly="readonly">
       <font color="#FF0000" size="5">&nbsp名将交易价格:
       <input name="heroprice" id="heroprice" color="#FF0000" type="text"  style="width:80px;">(元宝)</font>
       <br/>
      </p>
	  <div>
        <div style="float:left;">
        <select name="hname" id="hname" size="9" style="width:100px;" onchange="displayhimage(this.value);">
	    <?
         $armorinfos = sql_fetch_rows("select * from sys_city_hero where uid='$userinfo[uid]' and state=0 and npcid>0");
         if(!empty($armorinfos)) foreach($armorinfos as $armorinfo){ 
	     ?> 
         <option value="<?echo $armorinfo['hid'].','.$armorinfo['sex'].','.$armorinfo['face'].','.$armorinfo['level'].','.$armorinfo['command_base'].','.$armorinfo['affairs_base'].','.$armorinfo['bravery_base'].','.$armorinfo['wisdom_base'];?>"> <?echo $armorinfo['name'];?> </option> 
         <? 
          } 
         ?> 
         </select>
		 </div>
		 <div class="mydiv1">
         <img name="heropng" id="heropng"  style="width:80px;"/>
		 </div>
		&nbsp&nbsp　　&nbsp 
	    <div class="mydiv1">
		 性别<input name="heros" id="heros" type="text" style="width:30px;" readonly="readonly"/>
		 等级<input name="herod" id="herod" type="text" style="width:30px;" readonly="readonly"/>
	     统率<input name="heroc" id="heroc" type="text" style="width:30px;" readonly="readonly"/>
		</div>
		<div class="mydiv1">
		 内政<input name="heroa" id="heroa" type="text" style="width:30px;" readonly="readonly"/>
		 勇武<input name="herob" id="herob" type="text" style="width:30px;" readonly="readonly"/>
		 智力<input name="herow" id="herow" type="text" style="width:30px;" readonly="readonly"/>		 
	    </div>
	   </div>
	 <input  name="sellhero" type="submit" value="出售名将" />
    </ul>
    <ul class="block" style="display: none">
     <!----购买名将----->
       <font color="#FF0000" size="5">&nbsp　　　　　&nbsp可供购买名将一览表</font>
	   <div class="t_r"> 
        <div class="t_r_t" id="t_r_t"> 
         <div class="t_r_title"> 
            <table> 
             <tr> 
               <th width="12.5%">名将姓名</th> 
               <th width="12.5%">名将性别</th> 
               <th width="12.5%">名将等级</th> 
               <th width="12.5%">名将统率</th> 
               <th width="12.5%">名将内政</th> 
			   <th width="12.5%">名将勇武</th> 
               <th width="12.5%">名将智谋</th>
			   <th width="12.5%">出售价格</th> 
             </tr> 
            </table> 
         </div> 
        </div> 
        <div class="t_r_content" id="t_r_content" onscroll="aa()"> 
         <table>           
	      <?
		  $armorinfos = sql_fetch_rows("select * from sys_city_hero_trade where uid!='$userinfo[uid]' and trade=0 and npcid>0");  
          if(!empty($armorinfos)) foreach($armorinfos as $armorinfo){ 
          ?> 
           <tr>
             <td width="12.5%" class="bordertop"><? echo $armorinfo['name'];?></td>
	         <td width="12.5%" class="bordertop"><? echo $mmsex=$armorinfo['sex']>0?"男":"女";?></td>
	         <td width="12.5%" class="bordertop"><? echo $armorinfo['level'];?></td>
	         <td width="12.5%" class="bordertop"><? echo $armorinfo['command_base'];?></td>
		     <td width="12.5%" class="bordertop"><? echo $armorinfo['affairs_base'];?></td>
	         <td width="12.5%" class="bordertop"><? echo $armorinfo['bravery_base'];?></td>
	         <td width="12.5%" class="bordertop"><? echo $armorinfo['wisdom_base'];?></td>
		     <td width="12.5%" class="bordertop"><? echo $armorinfo['hid'];?><input type="checkbox" name="<? echo $armorinfo['hid'];?>" id="<? echo $armorinfo['hid'];?>" value="<? echo $armorinfo['hid'];?>" onclick="setselectid(this.value,'<? echo $armorinfo['name'];?>','myheros')"></td>
           </tr>
		  <? 
          } 
          ?> 
         </table>
        </div>
	    <div class="t_r_title"> 
            <table> 
             <tr> 
               <th width="100%"></th>                 
             </tr> 
            </table> 
        </div> 
	  </div>
	  &nbsp&nbsp&nbsp&nbsp　　　　　&nbsp&nbsp&nbsp&nbsp <font color="#FF0000" size="5">已选名将列表</font>
	  <br/>
      <select name="myheros[]"  multiple="yes" id="myheros" size="11" style="width:140px;">
	  </select>
	  <br/>
	  <input  name="buyhero" onclick="getallselect('myheros')" type="submit" value="购买名将" />
    </ul> 
   </div>
  </form> 
 </body>
</html>