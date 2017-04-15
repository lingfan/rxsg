<html>
<head>
    <meta http-equiv="Content-Type" content="text/html" charset="utf-8">
    <title>风云三国创建NPC势力</title>
    <script language="javascript" type="text/javascript">
function CheckTxtIsNum(myid){
      var elementTxt=document.getElementById(myid).value;
      var num=Number(elementTxt);
	  if(num>0 && num<300){
	     return true ;
        }
	  if(num<1 || num>300){
	     alert("请输入1-300之间的数字！");
	    }else{
         alert("请输入数字！");
	    }
	  document.getElementById(myid).focus();
	  document.getElementById(myid).value=10;
	  return false ;
    }
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
function lTrim(str){
     if (str.charAt(0) == " "){
	     str = str.slice(1);
		 str = lTrim(str); 
	    } 
	 return str;
	}
function CheckTxtIsNull(myid){
      var elementTxt=document.getElementById(myid).value;
	  elementTxt=lTrim(elementTxt);
      if(elementTxt){
	      return true ;
	    }
	  alert("名字不能为空!");
	  document.getElementById(myid).focus()
	  document.getElementById(myid).value=elementTxt;
      return false ;
    }

    </script>
</head>
<body bgcolor="#CCFFCC">
<form id="banddb" name="bandmysgldb" method="post" action="">
    <h1><p align="center"><font color="#FF0000" 　>---------创建NPC势力---------</font></p></h1>
    <p align="center"><font color="#0000FF" align="center" 　>---------注:暂定一个区只能创建一个新势力，如果你已经创建了一个新势力，以下操作将会覆盖你原来的势力！---------</font>
    </p>
    <p align="left"><font color="#FF0000" size="5">势力名称:</font>
        <?php
        require_once("dbinc.php");
        $npcname     = sql_fetch_one_cell("select name from sys_user where uid=897 ");
        $cityid      = sql_fetch_one_cell("select lastcid from sys_user where uid=897 ");
        $npccity     = sql_fetch_one_cell("select name from sys_city where uid=897 and cid='$cityid' ");
        $npcheroname = sql_fetch_one_cell("select name from sys_city_hero where  cid='$cityid' limit 1");
        ?>
        <input name="uidname" type="text" id="c0" style="width:80px;" maxlength="64" onblur="CheckTxtIsNull('c0')"
               value="<?php if(empty($npcname)) echo "风云";else echo $npcname; ?>"/>
        <font color="#FF0000" size="5">主城名称:</font>
        <input name="ciytname" type="text" id="c1" style="width:80px;" maxlength="64" onblur="CheckTxtIsNull('c1')"
               value="<?php if(empty($npccity)) echo "郡城";else echo $npccity; ?>"/>
        <font color="#FF0000" size="5">主城类型:</font>
        <select name="citytypes" id="c2" size="1" style="width:80px;">
            <option value="0">城池</option>
            <option value="1">县城</option>
            <option value="2">郡城</option>
            <option value="3">洲城</option>
        </select>
        <font color="#FF0000" size="5">势力范围:</font>
        <select name="cityprovince" id="c3" size="1" style="width:80px;">
            <?php
            $provinceinfosql = sql_fetch_rows("select * from cfg_province");
            foreach($provinceinfosql as $provinceinfo){
                ?>
                <option value="<?php echo $provinceinfo['id']; ?>">  <?php echo $provinceinfo['name']; ?> </option>
                <?php
            }
            ?>
        </select>
        <font color="#FF0000" size="5">主城兵力:</font>
        <input name="citysolider" type="text" id="n0" style="width:32px;" maxlength="3" onblur="CheckTxtIsNum('n0')"
               value="10"/>
        <font color="#cc0cc0">注:这个兵力是指在原来的基础上增加的百分比。 </font>
        <br/>
        <font color="#FF0000" size="5">主将名称:</font>
        <input name="heronamerr" type="text" id="c4" style="width:80px;" maxlength="64" onblur="CheckTxtIsNull('c4')"
               value="<?php if(empty($npcheroname)) echo "风云";else echo $npcheroname; ?>"/>
        <font color="#FF0000" size="5">主将性别:</font>
        <select name="citysex" id="c5" size="1" style="width:80px;">
            <option value="0">女</option>
            <option value="1">男</option>
        </select>
        <font color="#FF0000" size="5">主将简介:</font>
        <input name="herobreif" type="text" id="c6" style="width:270px;" onblur="CheckTxtIsNull('c6')" value="新势力"/>
        <font color="#FF0000" size="5">主将统率:</font>
        <input name="herocom" type="text" id="n1" style="width:32px;" onblur="CheckHeroTxtIsNum('n1')" value="10"/>
        <font color="#FF0000" size="5">主将内政:</font>
        <input name="heroaff" type="text" id="n2" style="width:32px;" onblur="CheckHeroTxtIsNum('n2')" value="10"/>
        <font color="#FF0000" size="5">主将勇武:</font>
        <input name="herobar" type="text" id="n3" style="width:32px;" onblur="CheckHeroTxtIsNum('n3')" value="10"/>
        <font color="#FF0000" size="5">主将智谋:</font>
        <input name="herowis" type="text" id="n4" style="width:32px;" onblur="CheckHeroTxtIsNum('n4')" value="10"/>
    </p>
    <p align="center">
        <input name="users" id="users" value="------创建NPC君主------" type="submit">
    </p>
    <h1><p align="center"><font color="#FF0000" 　>---------创建NPC将领---------</font></p></h1>
    <p align="center"><font color="#0000FF" align="center" 　>---------注:将领城池类型如果选择野地，将会创建野地将其不会在归属于任何一方！---------</font>
    </p>
    <p align="left">
        <?php
        $cityid    = sql_fetch_one_cell("select lastcid from sys_user where uid=897 ");
        $cityids   = sql_fetch_one_cell("select cid from sys_city where uid=897 and cid!='$cityid' limit 1 ");
        $npccity   = sql_fetch_one_cell("select name from sys_city where uid=897 and cid='$cityids' ");
        $nheroname = sql_fetch_one_cell("select name from sys_city_hero where  cid='$cityids' and uid=897 limit 1");
        ?>
        <font color="#990033" size="5">城池名称:</font>
        <input name="cname" type="text" id="h0" style="width:80px;" maxlength="64" onblur="CheckTxtIsNull('h0')"
               value="<?php if(empty($npccity)) echo "城池";else echo $npccity; ?>"/>
        <font color="#990033" size="5">城池类型:</font>
        <select name="ctypes" id="h1" size="1" style="width:80px;">
            <option value="0">野地</option>
            <option value="1">城池</option>
            <option value="2">县城</option>
            <option value="3">郡城</option>
        </select>
        <font color="#990033" size="5">城池位置:</font>
        <select name="province" id="h2" size="1" style="width:80px;">
            <?php
            $provinceinfosql = sql_fetch_rows("select * from cfg_province");
            foreach($provinceinfosql as $provinceinfo){
                ?>
                <option value="<?php echo $provinceinfo['id']; ?>">  <?php echo $provinceinfo['name']; ?> </option>
                <?php
            }
            ?>
        </select>
        <font color="#990033" size="5">城池归属:</font>
        <select name="provincebrief" id="h3" size="1" style="width:80px;">
            <?php
            $provinceforces = sql_fetch_rows("select * from sys_user where uid <1000 and (flagchar!='' || uid=897)");
            foreach($provinceforces as $provinceforce){
                ?>
                <option value="<?php echo $provinceforce['uid']; ?>">  <?php echo $provinceforce['name']; ?> </option>
                <?php
            }
            ?>
        </select>
        <font color="#990033" size="5">城池兵力:</font>
        <input name="csoldier" type="text" id="s0" style="width:32px;" maxlength="3" onblur="CheckTxtIsNum('s0')"
               value="5"/>
        <font color="#Fc0Fc0">注:这个兵力是指在原来的基础上增加的百分比。 </font>
        <br/>
        <font color="#990033" size="5">将领名称:</font>
        <input name="heron" type="text" id="h4" style="width:80px;" maxlength="64" onblur="CheckTxtIsNull('h4')"
               value="<?php if(empty($nheroname)) echo "将领";else echo $nheroname; ?>"/>
        <font color="#990033" size="5">将领性别:</font>
        <select name="herosex1" id="h5" size="1" style="width:80px;">
            <option value="0">女</option>
            <option value="1">男</option>
        </select>
        <font color="#990033" size="5">将领简介:</font>
        <input name="heronamew" type="text" id="h6" style="width:270px;" onblur="CheckTxtIsNull('h6')" value="野地"/>
        <font color="#990033" size="5">将领统率:</font>
        <input name="heroc" type="text" id="s1" style="width:32px;" onblur="CheckHeroTxtIsNum('s1')" value="10"/>
        <font color="#990033" size="5">将领内政:</font>
        <input name="heroa" type="text" id="s2" style="width:32px;" onblur="CheckHeroTxtIsNum('s2')" value="10"/>
        <font color="#990033" size="5">将领勇武:</font>
        <input name="herob" type="text" id="s3" style="width:32px;" onblur="CheckHeroTxtIsNum('s3')" value="10"/>
        <font color="#990033" size="5">将领智谋:</font>
        <input name="herow" type="text" id="s4" style="width:32px;" onblur="CheckHeroTxtIsNum('s4')" value="10"/>
    </p>
    <p align="center">
        <input name="heros" id="heros" value="------创建NPC将领------" type="submit">
    </p>
</form>
</body>
</html>
<?php
require_once("dbinc.php");
require_once("BandServer.php");
$userinfo   = array();
$conentinfo = array();
$taginfo    = array(
    'name',
    'cityname',
    'citytype',
    'province',
    'soldiers',
    'heroname',
    'herosex',
    'herobrief',
    'command',
    'affairs',
    'bravery',
    'wisdom',
    'uid'
);
$typeid     = 0;
$heroinfo   = '';
foreach($_POST as $key => $val) $userinfo[] = trim($val);
if(isset($_POST['users'])){
    $i      = 0;
    $typeid = 1;
    while($i < 12){
        $conentinfo[$taginfo[$i]] = $userinfo[$i];
        $i++;
    }
    $conentinfo['uid'] = 897;
    $heroinfo          = CreatNPCForce($conentinfo, $typeid);
    if(!empty($heroinfo)){
        $msg = '<table aligen="center" width="605" border="1" cellpadding="0" cellspacing="0" bgcolor="#FFccFF">' . '<tr><td>将领名称</td><td>等级</td><td>统率</td><td>内政</td><td>勇武</td><td>智谋</td><td>所在城池</td><td></tr>';
        $msg .= '<tr><td>' . $heroinfo['name'] . '</td><td>' . $heroinfo['level'] . '</td><td>' . $heroinfo['command_base'] . '</td><td>' . $heroinfo['affairs_base'] . '</td><td>' . $heroinfo['bravery_base'] . '</td><td>' . $heroinfo['wisdom_base'] . '</td><td>' . $heroinfo['cid'] . '</td><td></tr>';
        $msg .= '</table>';
        ?>
        <p align="center">
            <?php echo $msg; ?>
        </p>
        <?php
    }else{
        ?>
        <p align="center">
            <font color="#FF0000" 　>---------创建失败---------</font>
        </p>
        <?php
    }
}else if(isset($_POST['heros'])){
    $i = 12;
    $b = 11;
    while($i < 24){
        if($i == 15){
            $conentinfo['uid'] = $userinfo[$i];
            $b += 1;
        }else{
            $conentinfo[$taginfo[$i - $b]] = $userinfo[$i];
        }
        $i++;
    }
    $heroinfo = CreatNPCForce($conentinfo, $typeid);
    if(!empty($heroinfo)){
        $msg = '<table aligen="center" width="605" border="1" cellpadding="0" cellspacing="0" bgcolor="#FFccFF">' . '<tr><td>将领名称</td><td>等级</td><td>统率</td><td>内政</td><td>勇武</td><td>智谋</td><td>所在城池</td><td></tr>';
        $msg .= '<tr><td>' . $heroinfo['name'] . '</td><td>' . $heroinfo['level'] . '</td><td>' . $heroinfo['command_base'] . '</td><td>' . $heroinfo['affairs_base'] . '</td><td>' . $heroinfo['bravery_base'] . '</td><td>' . $heroinfo['wisdom_base'] . '</td><td>' . $heroinfo['cid'] . '</td><td></tr>';
        $msg .= '</table>';
        ?>
        <p align="center">
            <?php echo $msg; ?>
        </p>
        <?php
    }else{
        ?>
        <p align="center">
            <font color="#FF0000" 　>---------创建失败---------</font>
        </p>
        <?php
    }
}
?>