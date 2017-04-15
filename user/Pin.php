<?php
/*=============作者:An QQ:233355455===============
  =============时间:2010-04-11 21:00==============
  =============功能:蜀门注册程序验证码生成=======================
  =============注:请使用者尊重作者,请勿删除此版权,此注册程序仿官方完美注册,默认赠送钻石:1W=============

  =======/dy天地英雄(200909183) 增加注册权限 ===== bbs.game138.net ===============
*/
session_start();
header("Content-Tpye: image/gif");
$im=@imagecreate(42,22);
if($im)
{
$background_color=imagecolorallocate($im,255,255,255);//背景颜色
$imagefill=imagecolorallocate($im,230,230,230);//随机填充颜色
imagefill($im,0,0,$imagefill);//填充颜色
$text_color=imagecolorallocate($im,0,0,255);//文字颜色;
$Pin="";
for($i=0;$i<=3;$i++)
{
$Pin.=rand(0,9);
}
$_SESSION['Pin']=$Pin;
imagestring($im,5,5,2,$Pin,$text_color);
$line_color=imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));//直线颜色横;
$line_color1=imagecolorallocate($im,rand(0,255),rand(0,255),rand(0,255));//直线颜色竖;
for($i=0;$i<=1;$i++)
{
imageline($im,0,rand(0,23),75,rand(0,23),$line_color);
imageline($im,rand(0,75),0,rand(0,75),23,$line_color1);
}
imagegif($im);
}else{
die("创建验证码失败!");
}
?>