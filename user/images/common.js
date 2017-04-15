// JScript 文件

function ovcatitem(obj){obj.style.backgroundColor='#ddf8e1';obj.parentElement.style.backgroundColor='#ddf8e1'}
function outcatitem(obj){obj.style.backgroundColor='';obj.parentElement.style.backgroundColor=''}
function ovtr(obj){obj.setAttribute('_bk',obj.style.backgroundColor);obj.style.backgroundColor='#c5e0f2'}
function outr(obj){obj.style.backgroundColor=obj.getAttribute('_bk')}
//加载验证码
function loadimg(obj1)
{
    obj1.src='/randcode.aspx?rnd='+Math.random();
}
function ClearSelectOptions(ctrl)
{
	while( ctrl.options.length>0 )
		ctrl.removeChild(ctrl.options[0]);	    
}
function GetArrayFromString(src,splitchar)
{
	var arr=src.split(splitchar);
	var retarr=new Array();	
	for(var i=0;i<arr.length/2;i++)
	{
		if( i*2<arr.length-1 )
		{
			retarr[i]=new Array(arr[i*2],arr[i*2+1]);
		}
	}
	return retarr;
}
function PopWin(url,name,width,height)
{
	window.open(url,name,"width=" + width + ",height=" + height + ",left=100,top=100,scrollbars=yes");
}
function GetBrotherByTag(ctrl,tagName,part_id){	var parent=ctrl.parentElement;while(parent!=null){var list=parent.getElementsByTagName(tagName);var n=0;for(n=0;n<list.length;n++){if(list[n].id.indexOf(part_id)>=0)return list[n]}parent=parent.parentElement}return null}
function SelectAllGridCheckbox(ctrl,ColId)
{
    var table=null;
	var n=0;
    table=GetParentByTagName(ctrl,'table',1);
    if(table!=null)
    {
		var list=GetAllInputOfTableCol(table,'checkbox',ColId);

		if( list!=null)
		{
			for(n=0;n<list.length;n++)
			{
				list[n].checked=ctrl.checked;
			}
		}
	}

}
function GetParentByTagName(ctrl,tagName,depth)
{
	var obj=ctrl;
	var num=0;
	while( (obj=obj.parentElement)!=null )
	{
		if(obj.tagName.toLowerCase()==tagName)
		{
			num++;
			if( num==depth )
			return obj;
		}
	}
	return null;
}
function GetInputOfCell(cell,typeName,repeatnum)
{
	if( cell==null) return null;
   var arr=cell.getElementsByTagName('input');  
   var num=0;
   var i=0;
   for(i=0;i<arr.length;i++)
   {
        if( arr[i].type.toLowerCase()==typeName )
        {			
            num++;
            if( num==repeatnum) return arr[i];
        }        
   }
   return null;
}
function GetAllInputOfTableCol(table,typeName,ColId)
{
	var _arr=new Array();
	var num=0;
	var n=0;
    if(table!=null)
    {
	    for(n=0;n<table.rows.length;n++)
	    {			
	        if( table.rows[n].cells.length<=ColId )
			{
				if( _arr.length>0 )
					return _arr;
	            return null;
			}
		    var  cell=table.rows[n].childNodes[ColId];		

		    var  obj=GetInputOfCell(cell,typeName,1);
		    if(obj!=null)
			{				
				_arr[num++]=obj;
			}
	    }
	}

	return _arr;
}function  GetCheckedNum(ctrl,TableId,ColID)
{
    var table=null;
	var table=GetBrotherByTag(ctrl,"table",TableId);
	var n=0;
    if(table!=null)
    {
        var obj=GetAllInputOfTableCol(table,"checkbox",ColID);
        var num=0;
		if( obj!=null  )
		{		    
			for(n=0;n<obj.length;n++)
			{	
				if( obj[n].checked )
				    num++;
			}
		}
		return num;
    }
    return 0;
}
function SetGridCtrlValue2(ctrl,TableId,SrcColId,SrcType,DesColID,DesType,AddRate)
{
    var table=null;
	var table=GetBrotherByTag(ctrl,"table",TableId);
	var n=0;
    if(table!=null)
    {
		var src=GetAllInputOfTableCol(table,SrcType,SrcColId);
		var des=GetAllInputOfTableCol(table,DesType,DesColID);

		if( src!=null && des!=null )
		{		    
			for(n=0;n<des.length;n++)
			{							    
				des[n].value=parseFloat(src[n].value)*parseFloat(AddRate);
				if( isNaN(des[n].value) )
				    des[n].value=0;
			}
		}		
	}    
}

/*行编辑*/
function SetRowEditMode(ctrl,colidlist)
{
	return DoSetRowEditMode(ctrl,colidlist,50);
}


function DoSetRowEditMode(ctrl,colidlist,maxlen)
{	
	ctrl.style.display='none';var colidarr=colidlist.split(",");var table=null;var n=0;
	var cell=GetParentByTagName(ctrl,'td',1);
	var newbt=document.createElement("input");
	newbt.type="button";
	newbt.name="_saverow";	
	newbt.value="保存";
	newbt.onclick=function(){eval(ctrl.href);};
	//	newbt.onclick=function(){ NewAjaxFunc().SetValue('modify_card_type',this));

	cell.appendChild(newbt);

	var newbt2=document.createElement("input");
	newbt2.type="button";
	newbt2.name="_canceledit";	
	newbt2.value="取消";
	newbt2.onclick=function(){ResetRow2(ctrl,newbt,newbt2,colidlist);};
	cell.appendChild(newbt2);

	var row=GetParentByTagName(ctrl,'tr',1);
	var rowindex=row.rowIndex;
    var table=GetParentByTagName(ctrl,'table',1);

    if(table!=null)
    {
		var i=0;
		for(i=0;i<colidarr.length;i++)
		{
		    if( colidarr[i]=='' ) continue;
			var cell=table.rows[rowindex].cells[ parseInt(colidarr[i]) ];
			cell.setAttribute("bak",cell.innerText);
			var newobj=document.createElement("input"); 
			newobj.type="text";
			newobj.name="_col"+colidarr[i];
			var dval=Trim(cell.innerText)
			newobj.size=20;
			if(dval.length2()>0 && dval.length2()<=maxlen)
			{
			    newobj.size=dval.length2();
			}
			newobj.value=dval;
			cell.innerText="";
			cell.appendChild(newobj);

		}
	}

	
	return false;
}
function  Trim(str)
{
    return  str.replace(/^\s*(.*?)[\s\n]*$/g,'$1');
}
String.prototype.length2 = function() 
{
    var cArr = this.match(/[^x00-xff]/ig);
    return (this.length + (cArr == null ? 0 : cArr.length)); 
} 
function ResetRow2(ctrl,newbt,newbt2,colidlist){var colidarr=colidlist.split(",");var table=null;var n=0;var cell=GetParentByTagName(ctrl,'td',1);var row=GetParentByTagName(ctrl,'tr',1);var rowindex=row.rowIndex;table=GetParentByTagName(ctrl,'table',1);if(table!=null){var i=0;for(i=0;i<colidarr.length;i++){var _cell=table.rows[rowindex].cells[parseInt(colidarr[i])];var obj=_cell.ownerDocument.getElementById('idcol'+i);if(obj!=null){_cell.removeChild(obj)}_cell.style.wordBreak="break-all";_cell.innerText=_cell.getAttribute("bak")}}ctrl.style.display='';cell.removeChild(newbt);cell.removeChild(newbt2)}