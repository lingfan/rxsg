function showmysure(objid,sidsure,parentid,displayparent,event){
     var chk = document.getElementById(objid);
	 if(chk.checked){         
         if (window.confirm("确定要取消")){
              document.getElementById(sidsure).submit();
              deleteCurRow(event);			  
			  parent.document.getElementById(parentid).src=displayparent;
			  return true;
            }
		   else{
             chk.checked=false;
            }
        }
	  return false;
	}
function partRefresh(parentid,displayparent){
      parent.document.getElementById(parentid).src=displayparent;
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
function setTab(m,n){
     var tli=window.parent.document.getElementById("leftmenu"+m).getElementsByTagName("li");
	 var mli=window.parent.document.getElementById("mcont"+m).getElementsByTagName("ul"); 
	 for(i=0;i<tli.length;i++){ 
	     tli[i].className=i==n?"hover":"";
	     mli[i].style.display=i==n?"block":"none";		
	    }    
	}
function displayimage(obj1,obj2,obj3){
   	 var rename=document.getElementById(obj1).options[document.getElementById(obj1).selectedIndex].text;
     var filename ="http://127.0.0.1/images/armor/"+rename.replace(/[^0-9]/ig,"");
	 document.getElementById(obj2).src=filename+".png";
	 document.getElementById(obj3).focus();
	}
function displayimages(obj1,obj2,obj3,obj4,obj5,obj6){
     var arobj=document.getElementById(obj1); 
     var index=arobj.selectedIndex;
   	 var rename=arobj.options[index].text;
     var filename ="http://127.0.0.1/images/armor/"+rename.replace(/[^0-9]/ig,"");
	 document.getElementById(obj2).src=filename+".png";
	 rename=arobj.options[index].value;
	 var myarmorinfo=rename.split(";");
	 document.getElementById(obj3).value=myarmorinfo[1];
	 document.getElementById(obj4).rows[0].cells[0].innerHTML = myarmorinfo[2];
	 document.getElementById(obj4).rows[0].cells[1].innerHTML = myarmorinfo[3];
	 document.getElementById(obj4).rows[0].cells[2].innerHTML = myarmorinfo[4];
	 document.getElementById(obj4).rows[0].cells[3].innerHTML = myarmorinfo[5];
	 document.getElementById(obj4).rows[0].cells[4].innerHTML = myarmorinfo[1];
	 var myattr=myarmorinfo[6].split(",");	
	 index=myattr[0];
     rename="";	
     var atm=0;	 
	 for(var i=0;i<index;i++){
	   atm=i*2+1;
	   var attrid=myattr[atm];
	   var attrvaule=myattr[atm+1];
	   if(attrid==1) rename=rename+"统帅";else if(attrid==2) rename=rename+"内政";else if(attrid==3) rename=rename+"勇武";else if(attrid==4) rename=rename+"智谋";
	   else if(attrid==5) rename=rename+"体力";else if(attrid==6) rename=rename+"精力";else if(attrid==7) rename=rename+"生命";else if(attrid==8) rename=rename+"攻击";
	   else if(attrid==9) rename=rename+"防御";else if(attrid==10) rename=rename+"射程";else if(attrid==11) rename=rename+"速度";else if(attrid==11) rename=rename+"负重";
	   rename=rename+":<font color='#FF0F03' size='3'>"+attrvaule+"</font>";	   
	 }
	 var getsmons=Number(parent.document.getElementById("mytotaleybao").value);
	 document.getElementById(obj5).innerHTML ="<font color='#FF0000' size='5'>"+myarmorinfo[2]+"基础属性:</font><br/>"+ rename+"<br/>剩余元宝:"+getsmons;
	}
function displayupdimage(obj1,obj2,obj3){
     var rename=document.getElementById(obj1).options[document.getElementById(obj1).selectedIndex].text;
	 var usid=document.getElementById(obj1).options[document.getElementById(obj1).selectedIndex].value.split(";");
     var filename ="http://127.0.0.1/images/armor/"+rename.replace(/[^0-9]/ig,"");
	 document.getElementById(obj2).src=filename+".png";
   	 document.getElementById(obj3).value=usid[0];	
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
     var getsmons=Number(parent.document.getElementById("mytotaleybao").value);
	 document.getElementById(obj5).innerHTML ="<font color='#FF0000' size='5'>"+myarmorinfo[2]+"基础属性:</font><br/>"+ rename+"<br/>剩余元宝:"+getsmons;	 
	}
function displayheroimage(hprin,heriamges,heropri,buyhertabe,totalms,mymos){
     var heroname=document.getElementById(hprin).options[document.getElementById(hprin).selectedIndex].text;
     var heroinfos=document.getElementById(hprin).options[document.getElementById(hprin).selectedIndex].value;
     var myhers=heroinfos.split(";");
	 var sexid=Number(myhers[1]);
	 if(sexid>0){
	     var sexname="男";
	     var filename ="http://127.0.0.1/images/hero/"+"hero_boy_"+Number(myhers[2]);
		}
	  else{
	     var filename ="http://127.0.0.1/images/hero/"+"hero_girl_"+Number(myhers[2]);
		 var sexname="女";
	    }
	 document.getElementById(heriamges).src=filename+".jpg";
     document.getElementById(heropri).value=myhers[8];	 
	 document.getElementById(buyhertabe).rows[0].cells[0].innerHTML = heroname;
	 document.getElementById(buyhertabe).rows[0].cells[1].innerHTML = sexname;
	 document.getElementById(buyhertabe).rows[0].cells[2].innerHTML = myhers[3];
	 document.getElementById(buyhertabe).rows[0].cells[3].innerHTML = myhers[4];
	 document.getElementById(buyhertabe).rows[0].cells[4].innerHTML = myhers[5];
     document.getElementById(buyhertabe).rows[0].cells[5].innerHTML = myhers[6];
	 document.getElementById(buyhertabe).rows[0].cells[6].innerHTML = myhers[7];
	 document.getElementById(buyhertabe).rows[0].cells[7].innerHTML = myhers[8]; 
     var getsmons=Number(parent.document.getElementById("mytotaleybao").value);
	 document.getElementById(totalms).innerHTML ="<font color='#FF0000' size='5'>剩余元宝:</font>"+getsmons;	 
	}
function displayheroimages(hprin,heriamges,herprice){
     var heroname=document.getElementById(hprin).options[document.getElementById(hprin).selectedIndex].text;
     var heroinfos=document.getElementById(hprin).options[document.getElementById(hprin).selectedIndex].value;
     var myhers=heroinfos.split(";");
	 var sexid=Number(myhers[1]);
	 var myface=Number(myhers[2])>0?Number(myhers[2]):1;
	 if(sexid>0){
	     var sexname="男";
	     var filename ="http://127.0.0.1/images/hero/"+"hero_boy_"+myface;
		}
	  else{
	     var filename ="http://127.0.0.1/images/hero/"+"hero_girl_"+myface;
		 var sexname="女";
	    }
	 document.getElementById(heriamges).src=filename+".jpg"; 
     document.getElementById(herprice).focus();     
	}
function aa(trcentent,trt){ 
      //var a=document.getElementById(trcentent).scrollTop; 
      var b=document.getElementById(trcentent).scrollLeft; 
      //document.getElementById(trfreeze).scrollTop=a; 
      document.getElementById(trt).scrollLeft=b; 
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
	 obj.insertCell().innerHTML = "<td><a href='#' onclick='deleteCurRow(event)'>delete current row</a></td>";
	}
function removeselectOne(mySelect){ 
     var obj=document.getElementById(mySelect); 
     var index=obj.selectedIndex; 
     obj.options.remove(index); 
    }
function getselectarmor(objsid,objprice,objsure,parentidmm,displayparentmm,hidvalues){
     var sidobj=document.getElementById(objsid); 	 
     var index=sidobj.selectedIndex;
     if(index<0) {alert("请选择要出售的装备！");return false;}	 
     var price=document.getElementById(objprice).value.length;
	 if(price==0) {alert("请输入价格！");return false;}
	 var price=document.getElementById(objprice).value;
	 if (isNaN(price)){
         alert("请输入有效数字!");return false;
		}
     var sidstrs=price;
	 var sidslen=sidstrs.length;
     var sidnum=Number(sidstrs);
     if(sidnum.toString().length<sidslen){alert("请输入有效数字!");return false;}
     if(window.confirm("确定出售")){
	       document.getElementById(hidvalues).value=sidobj.options[index].value;           
           sidobj.options.remove(index);
           document.getElementById(objsure).submit(); 
           parent.document.getElementById(parentidmm).src=displayparentmm; 		   
		   window.parent.frames[1].location.reload();         	   
		}
	  return false;
	}
function getselectherohid(objsid,objprice,objsure,parentidmm,displayparentmm,hidvalues){
     var sidobj=document.getElementById(objsid); 	 
     var index=sidobj.selectedIndex;
     if(index<0) {alert("请选择要出售的将领！");return false;}    
	 var price=document.getElementById(objprice).value.length;
	 if(price==0) {alert("请输入价格！");return false;}
	 var price=document.getElementById(objprice).value;
	 if (isNaN(price)){
         alert("请输入有效数字!");return false;
		}
     var sidstrs=price;
	 var sidslen=sidstrs.length;
     var sidnum=Number(sidstrs);
     if(sidnum.toString().length<sidslen){alert("请输入有效数字!");return false;}
      if(window.confirm("确定出售")){
	       var hidindex=sidobj.options[index].value.split(";"); 		   
           document.getElementById(hidvalues).value=hidindex[0];      
           sidobj.options.remove(index); 
           document.getElementById(objsure).submit();		   
		   parent.document.getElementById(parentidmm).src=displayparentmm;
		   window.parent.frames[5].location.reload();           		   
		}
	  return false;
    }	
function getbuyselectarmor(objsid,objprice,objarmorsid,parentidmm,displayparentmm,buyybao){
     var myyuanbaonum=Number(parent.document.getElementById("mytotaleybao").value);
	 if(myyuanbaonum<=0) {alert("剩余元宝不足，无法购买选定物件!");return false;}
     var sidobj=document.getElementById(objsid); 	 
     var index=sidobj.selectedIndex;
     if(index<0) {alert("请选择要购买的装备！");return false;} 
     var rename=sidobj.options[index].value;
	 var myarmorinfo=rename.split(";");
     var price=myarmorinfo[1];	 
     var pricelen=price.length;	 
	 if(price==0) return false;	
     var yuanbaos=Number(price);	
     var getyuanbbb=myyuanbaonum-yuanbaos;    
     if(getyuanbbb<0){alert("剩余元宝不足，无法购买选定物件!");return false;}         
     if(window.confirm("确定购买")){
	       parent.document.getElementById("mytotaleybao").value=getyuanbbb;	 
	       document.getElementById(buyybao).value=yuanbaos;
           document.getElementById(objprice).value=price;
           document.getElementById(objarmorsid).value=myarmorinfo[0];		   
           sidobj.options.remove(index);
           document.getElementById(parentidmm).src=displayparentmm; 
		}   
	  return false;
	}
function getbuyselecthero(objsid,objprice,objarmorsid,parentidmm,displayparentmm,buyybao){
     var myyuanbaonum=Number(parent.document.getElementById("mytotaleybao").value);
	 if(myyuanbaonum<=0) {alert("剩余元宝不足，无法购买选定物件!");return false;}
     var sidobj=document.getElementById(objsid); 	 
     var index=sidobj.selectedIndex;
     if(index<0) {alert("请选择要购买的名将！");return false;} 
     var rename=sidobj.options[index].value;
	 var myarmorinfo=rename.split(";");
     var price=myarmorinfo[8];	 
     var pricelen=price.length;	 
	 if(price==0) return false;	
     var yuanbaos=Number(price);	
     var getyuanbbb=myyuanbaonum-yuanbaos;      
     if(getyuanbbb<0){alert("剩余元宝不足，无法购买选定名将!");return false;}     	 
     if(window.confirm("确定购买")){
	       parent.document.getElementById("mytotaleybao").value=getyuanbbb;           	   
	       document.getElementById(buyybao).value=yuanbaos;
           document.getElementById(objprice).value=price;
           document.getElementById(objarmorsid).value=myarmorinfo[0];		   
           sidobj.options.remove(index);
           document.getElementById(parentidmm).src=displayparentmm; 
		}   
	  return false;	  
    }
function getupdateselectarmor(){
     var usid=document.getElementById("updateasid").value.length;
     if(usid<1) {alert("请选择要升级的装备!");return false;}
     var chk = document.getElementById("baohufu");
     if(chk.checked==false){
	     chk.value=0;	  
	    }	  
     if(window.confirm("确定升级")){ 
           var bashinum=document.getElementById("yuanbo").value;           
           var sjtznums=document.getElementById("sjtz").value;
		   var bahufnum=document.getElementById("bafuf").value;
		   if(bashinum<1 || sjtznums<1){alert("升级宝石或升级图纸数量不足！");return false;}
		   if(chk.checked){		    
			  if(bahufnum<1) {alert("升级保护符不足！");return false;}
			  chk.value=1;
			  document.getElementById("bafuf").value=bahufnum-1;
			}
           document.getElementById("yuanbo").value=bashinum-1;           
           document.getElementById("sjtz").value=sjtznums-1;    
		}   
	  return false;
	}