<?php

//十进制转字符串
function BIntDecToStr($A)
{
  while ($A>0)
  {
    $Result=Chr(bcmod($A, 256)).$Result;
    $A=bcdiv($A, 256);
  }
  return $Result;
}

//字符串转十进制
function BIntStrToDec($A)
{
  for ($i=0; $i<StrLen($A); $i++)
  {
    $T="1";
    for ($j=$i+1; $j<StrLen($A); $j++) $T=bcmul($T, "256");
    $Result=bcadd($Result, bcmul(Ord($A[$i]), $T));
  }
  return $Result;
}

//十进制转十六进制
function BIntDecToHex($A)
{
  while ($A>0)
  {
    $Result=base_convert( bcmod($A, 16), 10, 16 ).$Result;
    $A=bcdiv($A, 16);
  }
  return $Result;
}

//十六进制转十进制
function BIntHexToDec($A)
{
  for ($i=0; $i<StrLen($A); $i++)
  {
    $T="1";
    for ($j=$i+1; $j<StrLen($A); $j++) $T=bcmul($T, "16");
    $Result=bcadd($Result, bcmul(base_convert($A[$i], 16, 10), $T));
  }
  return $Result;
}

//十进制转三十二进制
function BIntDecToBase32($A)
{
  while ($A>0)
  {
    $Result=base_convert( bcmod($A, 32), 10, 32 ).$Result;
    $A=bcdiv($A, 32);
  }
  return $Result;
}

//三十二进制转十进制
function BIntBase32ToDec($A)
{
  for ($i=0; $i<StrLen($A); $i++)
  {
    $T="1";
    for ($j=$i+1; $j<StrLen($A); $j++) $T=bcmul($T, "32");
    $Result=bcadd($Result, bcmul(base_convert($A[$i], 32, 10), $T));
  }
  return $Result;
}

//十进制转六十四进制
function BIntDecToBase64($A)
{
  return base64_encode( BIntDecToStr($A) );
}

//六十四进制转十进制
function BIntBase64ToDec($A)
{
  return BIntStrToDec( base64_decode($A) );
}

//十进制转二进制
function BIntDecToBin($A)
{
  while ($A>0)
  {
    $Result=bcmod($A, 2).$Result;
    $A=bcdiv($A, 2);
  }
  return $Result;
}

//十六进制转二进制
function BIntHexToBin($A)
{
  $Result="";
  $Len=StrLen($A);
  for ($i=0; $i<$Len; $i++)
  {
    $T=base_convert($A[$i], 16, 2);
    if ($i>0)
    {
      $n=StrLen($T);
      if ($n==1) $T="000".$T;
      elseif ($n==2) $T="00".$T;
      elseif ($n==3) $T="0".$T;
    }
    $Result=$Result.$T;
  }
  return $Result;
}

//六十四进制转二进制
function BIntBase64ToBin($A)
{
  return BIntDecToBin( BIntBase64ToDec($A) );
}

//加密解密都用此函数 ($X,$N为十进制字符串, $E为二进制字符串.)
function RSA($X, $E, $N)
{
  $Result='1';
  for ($i=StrLen($E)-1; $i>=0; $i--)
  {
    if ($E[$i]=='1') $Result=bcmod(bcmul($Result, $X), $N);
    $X=bcmod(bcmul($X, $X), $N);
  }
  return $Result;
}

function getModulus(){
	return "58024268307503479100769500999819173248689182936484215289637368994338961188521";  //十进制字符串
} 
function encryptByRSA($srcText){	
	$PrivateKey="52646957920621651098365933570061604776739153687055641877341901693015168327923";  //十进制字符串	
	return RSA($srcText, BIntDecToBin($PrivateKey), getModulus());
}
function decryptByRSA($ecryptedText){
	$PublicKey ="79451";  //十进制字符串	
	return RSA($ecryptedText, BIntDecToBin($PublicKey), getModulus());
}

 
?>