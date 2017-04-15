var yyyy;
var mm;
var dd;
var birthday;
var sex;
var IdCardErrorMsg='';
function getYear(){
    return yyyy;
}
function getMonth(){
    return mm;
}
function getDate(){
    return dd;
}
function getBirthday(){
    return birthday;
}
function getSex(){
    return sex;
}
function getAge(){

    var mm=getMonth();
    if (mm<10) mm=mm.substring(1,2);

    //return Math.floor((parseInt(_getYear())*12+parseInt(_getMonth())-parseInt(getYear())*12-parseInt(mm))/12);

    
    // 此处不能信任用户电脑的时间,须从服务器上获取.
    var  date1=new  Date();//取得当前日期

    var curFullYear = date1.getFullYear();
    var curMonth = date1.getMonth();
    
    return Math.floor((parseInt(curFullYear)*12+parseInt(curMonth)-parseInt(getYear())*12-parseInt(mm))/12);
}
//判断是否大龄,男50,女40
function isBigAge(){
    if (parseInt(getAge())>=40 && parseInt(getSex())==2)
        return "1";
    if (parseInt(getAge())>=50 && parseInt(getSex())==1)
        return "1";
    return "0";
}

/**
*判断身份证号码格式函数
*公民身份号码是特征组合码，
*排列顺序从左至右依次为：六位数字地址码，八位数字出生日期码，三位数字顺序码和一位数字校验码
*/
function isChinaIDCard(StrNo){   
	  StrNo = StrNo.toString();
	  if(StrNo.length == 15){        
	   if(!isValidDate("19"+StrNo.substr(6,2),StrNo.substr(8,2),StrNo.substr(10,2))){return false;}      
	  }else if(StrNo.length == 18){     
	   if (!isValidDate(StrNo.substr(6,4),StrNo.substr(10,2),StrNo.substr(12,2))){return false;}   
	   }else{   
	   IdCardErrorMsg =kg + msgerr +("输入的身份证号码必须为15位或者18位！");   
	   return false;
	   }
	  
	  if (StrNo.length==18)   
	  {   
	var a,b,c   
	if (!isNumber(StrNo.substr(0,17))){IdCardErrorMsg =kg + msgerr +("身份证号码错误,前17位不能含有英文字母！");return false;}   
	a=parseInt(StrNo.substr(0,1))*7+parseInt(StrNo.substr(1,1))*9+parseInt(StrNo.substr(2,1))*10;   
	a=a+parseInt(StrNo.substr(3,1))*5+parseInt(StrNo.substr(4,1))*8+parseInt(StrNo.substr(5,1))*4;   
	a=a+parseInt(StrNo.substr(6,1))*2+parseInt(StrNo.substr(7,1))*1+parseInt(StrNo.substr(8,1))*6;     
	a=a+parseInt(StrNo.substr(9,1))*3+parseInt(StrNo.substr(10,1))*7+parseInt(StrNo.substr(11,1))*9;     
	a=a+parseInt(StrNo.substr(12,1))*10+parseInt(StrNo.substr(13,1))*5+parseInt(StrNo.substr(14,1))*8;     
	a=a+parseInt(StrNo.substr(15,1))*4+parseInt(StrNo.substr(16,1))*2;   
	b=a%11;   
	if (b==2)   //最后一位为校验位   
	{   
	  c=StrNo.substr(17,1).toUpperCase();   //转为大写X   
	}   
	else   
	{   
	  c=parseInt(StrNo.substr(17,1));   
	}   
	switch(b)   
	{   

	  case 0: if ( c!=1 ) {IdCardErrorMsg =kg + msgerr +("请填写真实的身份证号");return false;}break;   
	  case 1: if ( c!=0 ) {IdCardErrorMsg =kg + msgerr +("请填写真实的身份证号");return false;}break;   
	  case 2: if ( c!="X") {IdCardErrorMsg =kg + msgerr +("请填写真实的身份证号");return false;}break;   
	  case 3: if ( c!=9 ) {IdCardErrorMsg =kg + msgerr +("请填写真实的身份证号");return false;}break;   
	  case 4: if ( c!=8 ) {IdCardErrorMsg =kg + msgerr +("请填写真实的身份证号");return false;}break;   
	  case 5: if ( c!=7 ) {IdCardErrorMsg =kg + msgerr +("请填写真实的身份证号");return false;}break;   
	  case 6: if ( c!=6 ) {IdCardErrorMsg =kg + msgerr +("请填写真实的身份证号");return false;}break;   
	  case 7: if ( c!=5 ) {IdCardErrorMsg =kg + msgerr +("请填写真实的身份证号");return false;}break;   
	  case 8: if ( c!=4 ) {IdCardErrorMsg =kg + msgerr +("请填写真实的身份证号");return false;}break;   
	  case 9: if ( c!=3 ) {IdCardErrorMsg =kg + msgerr +("请填写真实的身份证号");return false;}break;   
	  case 10: if ( c!=2 ){IdCardErrorMsg =kg + msgerr +("请填写真实的身份证号");return false;}  
	}   
	  } else {//15位身份证号   
	if (!isNumber(StrNo)) {IdCardErrorMsg =kg + msgerr +("身份证号码错误,前15位不能含有英文字母！");return false;}     
	  }  
	  return true;

}   
    
  function isValidDate(iY, iM, iD) {
	   if (iY>2200 || iY<1900 || !isNumber(iY)){
				IdCardErrorMsg =kg + msgerr +("输入身份证号,年度"+iY+"非法！");
				return false;
			}
	   if (iM>12 || iM<=0 || !isNumber(iM)){
				IdCardErrorMsg =kg + msgerr +("输入身份证号,月份"+iM+"非法！");
				return false;
			}
	   if (iD>31 || iD<=0 || !isNumber(iD)){
				IdCardErrorMsg =kg + msgerr +("输入身份证号,日期"+iD+"非法！");
				return false;
			}
	  return true;
  }  



//校验身份证号码
function CheckIdCardValue(idCard){
		var idcardchkret= isChinaIDCard(idCard);
		if( !idcardchkret) return false;

    var id=idCard;
    var id_length=id.length;

    if (id_length==0){
        IdCardErrorMsg =kg + msgerr + "请输入身份证号码!";
        return false;
    }

    if (id_length!=15 && id_length!=18){
        IdCardErrorMsg =kg + msgerr +"身份证号长度应为15位或18位！";
        return false;
    }

    if (id_length==15){

        yyyy="19"+id.substring(6,8);
        mm=id.substring(8,10);
        dd=id.substring(10,12);

        if (mm>12 || mm<=0){
            IdCardErrorMsg =kg + msgerr +("输入身份证号,月份非法！");
            return false;
        }

        if (dd>31 || dd<=0){
            IdCardErrorMsg =kg + msgerr +("输入身份证号,日期非法！");
            return false;
        }

        birthday=yyyy+ "-" +mm+ "-" +dd;

        if ("13579".indexOf(id.substring(14,15))!=-1){
            sex="1";
        }else{
            sex="2";
        }
    }else if (id_length==18){
        if (id.indexOf("X") > 0 && id.indexOf("X")!=17 || id.indexOf("x")>0 && id.indexOf("x")!=17){
            IdCardErrorMsg =kg + msgerr + ("身份证中\"X\"输入位置不正确！");
            return false;
        }

        yyyy=id.substring(6,10);
        if (yyyy>2200 || yyyy<1900){
            IdCardErrorMsg =kg + msgerr +("输入身份证号,年度非法！");
            return false;
        }

        mm=id.substring(10,12);
        if (mm>12 || mm<=0){
            IdCardErrorMsg =kg + msgerr + ("输入身份证号,月份非法！");
            return false;
        }

        dd=id.substring(12,14);
        if (dd>31 || dd<=0){
            IdCardErrorMsg =kg + msgerr +("输入身份证号,日期非法！");
            return false;
        }

        if (id.charAt(17)=="x" || id.charAt(17)=="X")
        {
            if ("x"!=GetVerifyBit(id) && "X"!=GetVerifyBit(id)){
                IdCardErrorMsg =kg + msgerr + ("身份证校验错误，请检查！");
                return false;
            }

        }else{
            if (id.charAt(17)!=GetVerifyBit(id)){
                IdCardErrorMsg =kg + msgerr +("身份证校验错误，请检查！");
                return false;
            }
        }

        birthday=id.substring(6,10) + "-" + id.substring(10,12) + "-" + id.substring(12,14);
        if ("13579".indexOf(id.substring(16,17)) > -1){
            sex="1";
        }else{
            sex="2";
        }
    }

    return true;
}
//15位转18位中,计算校验位即最后一位
function GetVerifyBit(id){
    var result;
    var nNum=eval(id.charAt(0)*7+id.charAt(1)*9+id.charAt(2)*10+id.charAt(3)*5+id.charAt(4)*8+id.charAt(5)*4+id.charAt(6)*2+id.charAt(7)*1+id.charAt(8)*6+id.charAt(9)*3+id.charAt(10)*7+id.charAt(11)*9+id.charAt(12)*10+id.charAt(13)*5+id.charAt(14)*8+id.charAt(15)*4+id.charAt(16)*2);
    nNum=nNum%11;
    switch (nNum) {
       case 0 :
          result="1";
          break;
       case 1 :
          result="0";
          break;
       case 2 :
          result="X";
          break;
       case 3 :
          result="9";
          break;
       case 4 :
          result="8";
          break;
       case 5 :
          result="7";
          break;
       case 6 :
          result="6";
          break;
       case 7 :
          result="5";
          break;
       case 8 :
          result="4";
          break;
       case 9 :
          result="3";
          break;
       case 10 :
          result="2";
          break;
    }
    //document.write(result);
    return result;
}
//15位转18位
function Get18(idCard){
	 if (CheckValue(idCard)){
	  var id = idCard;
	  var id18=id;
	  if (id.length==0){
	   	alert("请输入15位身份证号！");
	    return false;
	  }
	  if (id.length==15){
	   if (id.substring(6,8)>20){
	    	id18=id.substring(0,6)+"19"+id.substring(6,15);
	   }else{
	    	id18=id.substring(0,6)+"20"+id.substring(6,15);
	   }
	
	   	id18=id18+GetVerifyBit(id18);
	  }
	
	  return id18;
	 }else{
	  return false;
	 }
}

