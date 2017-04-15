<?php
require_once("./common.php");
if($loginType == 0){
    $pass = sql_fetch_one("select * from test_passport where passport='$passport'");
    if(strlen($password) < 4) throw new Exception("密码不能少于4个字符！");
    if(!$pass){
        throw new Exception("错误的帐号！请在首页注册！");
    }

    if($pass['password'] <> $password && !$passsucc) throw new Exception("密码不正确！");
    $passsucc = true;
}
?>