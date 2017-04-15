<?php
/*=============作者:An QQ:233355455===============
  =============时间:2010-04-11 21:00==============
  =============功能:蜀门注册程序mysql操作类=======================
  =============注:请使用者尊重作者,请勿删除此版权,此注册程序仿官方完美注册,默认赠送钻石:1W=============

  =======/dy天地英雄(200909183) 增加注册权限 ===== bbs.game138.net ===============
*/
class mysql_class
{
var $conn;
private $server;
private $uid;
private $pwd;
private $datebase;
// mysql的 地址 用户名 密码 数据库
        function mysql_class($server,$uid,$pwd,$datebase)
       {
        $this->server=$server;
        $this->uid=$uid;
        $this->pwd=$pwd;				
        $this->datebase=$datebase;
        $this->conn=@mysql_connect($this->server,$this->uid,$this->pwd) or die("连接到MySQL失败 !!");
		@mysql_select_db($this->datebase,$this->conn) or die ("选择数据库失败 !!");
		@mysql_query("set names 'gb2312'",$this->conn);
       }
	   function selectdb($datebase)
	   {
	   @mysql_select_db($datebase,$this->conn) or die ("选择数据库失败 !!");
	   @mysql_query("set names 'gb2312'",$this->conn);
	   }
	   //$sql: celect 类型的SQL语句；
	   function queryrs($sql)
	   {
	   $rs=mysql_query($sql,$this->conn);
	       if($rs)
		   {
		   return $rs;
		   }else{
		   return NULL;
		   }	   
	   }
       //$sql: celect 类型的SQL语句；
	   function queryrow($sql)
	   {
	   $rs=mysql_query($sql,$this->conn);	   
	      if($rs)
		  {
		   return mysql_fetch_array($rs);
		  }else{
		   return NULL;
		  }
	   }
       //$sql: celect 类型的SQL语句；
	   function queryobject($sql)
	   {
	   $rs=mysql_query($sql,$this->conn);	   
	      if($rs)
		  {
		   return mysql_fetch_object($rs);
		  }else{
		   return NULL;
		  }
	   }
       //$sql: celect 类型的SQL语句；
	   function queryone($sql)
	   {
	   $rs=mysql_query($sql,$this->conn);	   
	      if($rs)
		  {
		   $arr=mysql_fetch_array($rs,MYSQL_NUM);
		   return $arr[0];
		  }else{
		   return NULL;
		  }
	   }	   
       //$sql:非select语句；
	   function querysql($sql,$hasid=false)
	   {
	   $rs=mysql_query($sql,$this->conn);
	      if($rs)
		  {
		    if($hasid)
			{
		    $sql=trim(strtolower($sql));
		        if(substr($sql,0,6)=="insert")
		       {
			    return mysql_insert_id($this->conn);
		       }else{
			    return mysql_affected_rows($this->conn);
		       }			
			}else{
			return mysql_affected_rows($this->conn);			
			}
		  }else{
		  return -1;
		  }
	   }
	   //$rs: $rs是记录集对象；
	   function fetcharray($rs)
	   {
	      if($rs)
		  {
		      return mysql_fetch_array($rs);
		  }else{
		      return NULL;
		  }
	   }
	   //$rs: $rs是记录集对象;	   
	   function fetchobject($rs)
	   {
	      if($rs)
		  {
		      return mysql_fetch_object($rs);
		  }else{
		      return NULL;
		  }
	   }
	   //getAllNum用于返回$rs对应的记录集对象中的记录总数
	   function getAllNum($rs)
	   {
	   	  if($rs)
		  {
			 return mysql_num_rows($rs);
		  }else{
		  	return 0;
		  }
	   }	   
   }	   	   
?>