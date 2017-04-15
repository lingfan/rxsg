<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html" charset="utf-8">
<title>风云三国合服设置</title>
<script language="javascript" type="text/javascript">
function CheckTxtIsNum(myid){
      var elementTxt=document.getElementById(myid).value;
      var num=Number(elementTxt);
	  if(num>0){
	     return true ;
        }
	  alert("请输入大于0的数字!");
	  document.getElementById(myid).focus();
	  document.getElementById(myid).value=1;
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
	  document.getElementById(myid).value="rxsg";
      return false ;
    }
</script>
</head> 
<body> 
<form id="banddb" name="bandmysgldb" method="post" action="">
<p align="center">------请输入要合区的数据库名称--------</p>
  <p align="center"><font color="#FF0000">数据库名称：</font>
  <input id="dbname" name="dbname:" value="rxsg" onblur="CheckTxtIsNull('dbname')" type="text">
  </p>
  <p align="center"><font color="#FF0000">本次合的区：</font>
  <input id="dbnum" name="dbnum:" value="1" onblur="CheckTxtIsNum('dbnum')" type="text">
</p>
<p align="center">
  <input name="bandstar"  value="开始合区" type="submit">
  </p>
</form>
</body>
</div>
<?php
 if(isset($_POST['bandstar'])){
	  $userinfo =array();
	  $ok=false;
      foreach($_POST as $key => $val) $userinfo[]=trim($val);
	  $BandNum = $userinfo[1];
	  $BandDb = $userinfo[0];
      if(preg_match("/^\d*$/",$BandNum)) $ok=true; 
	  if(!empty($BandDb) && $ok){
	      require_once("dbinc.php");
		  require_once("BandServer.php");
		  if (!sql_check("select 1 from banduser where uid='1'")){
				sql_query("insert into banduser(uid,name,passport,bandnum,state) values(1,'123',0,1,0)");
			}
		  set_time_limit(0);
		  $mst=Get_BandUserInfo($BandDb,$BandNum);
		  if($mst){
		     sql_query("update banduser set passport='0',bandnum='10000' where uid='1'");
		    }else{
		 ?>
		         <p align="center">------合区失败------</p>;
		     <? }
		}else{?>
	      <p align="center">------请重新输入数据：------</p>;
	 <? }
	}
?> 