// JScript 文件
//测试
function dd() 
{ 
   TestAjax.HelloWorld(function(result) {alert(result); } );
}     
function GetParentByTagName(ctrl,tagName,depth){var obj=ctrl;var num=0;while((obj=obj.parentElement)!=null){if(obj.tagName.toLowerCase()==tagName){num++;if(num==depth)return obj}}return null}
function GetBrother(ctrl,tagName,typeName){var parent=ctrl.parentElement;var list=parent.getElementsByTagName(tagName);var n=0;for(n=0;n<list.length;n++){if(list[n].type.toLowerCase()==typeName){return list[n]}}return null}
function GetBrother2(ctrl,tagName){var parent=ctrl.parentElement;var list=parent.getElementsByTagName(tagName);return list[0];}
function deleteRows(src,rowidx1,rowidx2){var tb=src;var i=0;var maxidx=tb.rows.length-1;for(i=maxidx;i>=0;i--){if(i<rowidx1||i>rowidx2){tb.deleteRow(i)}}}
function deleteCurrentRow(ctrl1)
{
	var parent=GetParentByTagName(ctrl1,"tr",1);
	if( parent!=null )
		parent.style.display='none';
}
/*
Atlas在页面用JavaScript调用WebService方法的参数

WebService Class Name.WebMethod Name
(
      Method Parameters, 
    onMethodComplete, 
    onMethodTimeout, 
    onMethodError, 
    onMethodAborted,
    userContext,
    timeoutInterval,
    priority,
    useGetMethod,
); 

注意：1、Web Method中如果多个参数则用逗号隔开；
        2、Atlas调用的WebService里的所有参数必须按照顺序排列；
参数说明如下： 

onMethodComplete：指定当该方法顺利完成并返回时被触发的回调函数名，一般情况下您应该总是指定这个方法。 
onMethodTimeout,：指定当该方法执行超时时被触发的函数名。 
onMethodError：指定当该方法在执行中遇到异常时被触发的函数名。 
onMethodAborted：制定当该方法执行期间被用户取消时被触发的函数名。 
userContext：用户上下文对象，在上述四个函数中都可以访问到。 
timeoutInterval：设定超时的时间限制，单位毫秒，默认值好像为90000。一般情况下不需要更改。 
priority：设定该方法的执行优先级。该优先级将被用于批量AJAX操作（将在下一篇中提到）中。 
useGetMethod：是否采用HTTP GET来发送请求，默认为false。 
     上述这八个属性的顺序必须按照指定的来。但有时候我们只需要指定顺序靠后的某个参数，就不得不同时书写前面的参数。为此，Atlas特意为我们提供了另一种调用方法，将上述八个参数以dictionary的形式传给该方法。例如当我们只需要onMethodComplete和timeoutInterval参数时，可以这样写： 

WebService Class Name.WebMethod Name
  (
    Method Parameters, 
      {
        onMethodComplete: completeHandler, 
        timeoutInterval: 10000
      }
);

*/

function	CheckAjaxResult_Bool(res)
{
	if( res=="-2" ){ alert('此用户类型无权操作');return false }
	else if( res=="-1" ) { alert('此用户无此操作权限');return false }
	else if( res=="0" ) { alert('操作失败');return false }
	else if( res=="1" ) return true;
	else
		alert(res);
	return false;
}
function	CheckAjaxResult_Int(res)
{
	try
	{
		var retint=parseInt(res);
		if( retint==-2 ){ alert('此用户类型无权操作');return false }
		else if( retint==-1 ) { alert('此用户无此操作权限');return false }
		else if( res==0 ) { alert('操作失败');return false }
		else
			return true;
	}
	catch (err)
	{
		return false;
	}
}
function	CheckAjaxResult_DataList(res)
{
	try
	{
		if( res==null || res=="" ){ return false; }
		return true;
	}
	catch (err)
	{
		return false;
	}
}
function NewAjaxFunc()
{
	return new AjaxFunc();
}
var ajaxtimeout=30000;
function AjaxFunc()
{

	this.SetValue=function(optname,ctrl1)
	{		
		this.ClearParam();
		CurrentParam.push(optname,ctrl1);
		var lbl=GetBrother2(ctrl1,'span');
		if( lbl!=null )
			lbl.innerHTML="<font color=blue>处理中...</font>";
		WebFunctions.CheckBox_ChangeStatus(optname,ctrl1.getAttribute("_Param"),ctrl1.getAttribute("_Param2"),this.SetValue_Callback,this.OnAjaxTimeOut,this.OnAjaxError,this.OnAjaxAborted,this,ajaxtimeout);
	}

	this.SetValue_Callback=function(result, userContext, methodName)
	{
		if( ! CheckAjaxResult_Bool(result) ) return false;
		var opt=CurrentParam[0];
		var chkbox=CurrentParam[1];
		alert('操作成功');
	}
	/****************************表格内部checkbox:UpdateStatus更新状态标志********************************/
	this.UpdS=function(optname,checkbox1)
	{		
		this.ClearParam();
		CurrentParam.push(optname,checkbox1);
		var lbl=GetBrother2(checkbox1,'span');
		lbl.innerHTML="<font color=blue>处理中...</font>";
		WebFunctions.CheckBox_ChangeStatus(optname,checkbox1.getAttribute("_Param"),checkbox1.checked?"1":"0",this.UpdS_Callback,this.OnAjaxTimeOut,this.OnAjaxError,this.OnAjaxAborted,this,ajaxtimeout);
	}

	this.UpdS_Callback=function(result, userContext, methodName)
	{
		if( ! CheckAjaxResult_Bool(result) ) return false;
		var opt=CurrentParam[0];
		var chkbox=CurrentParam[1];
		var lbl=GetBrother2(chkbox,'span');
		//if( opt=="card_type_list" )
		//	lbl.innerHTML=chkbox.checked?"已开通":"<font color=red>已禁用</font>";
		//else
		lbl.innerHTML=chkbox.checked?"已开通":"<font color=red>已禁用</font>";

	}
	/************************从表格中删除一条记录************************************/
	this.DelTBRow=function(optname,ctrl1)
	{
		if( confirm('您确认要删除这条记录吗？')==false ) return;
		this.ClearParam();
		CurrentParam.push(optname,ctrl1);
		WebFunctions.GridView_DeleteRow(optname,ctrl1.getAttribute("_Param"),this.DelTBRow_Callback,this.OnAjaxTimeOut,this.OnAjaxError,this.OnAjaxAborted,this,ajaxtimeout);
		
	}
	this.DelTBRow_Callback=function(result, userContext, methodName)
	{
		if( ! CheckAjaxResult_Bool(result) ) return false;
		var opt=CurrentParam[0];
		var ctrl1=CurrentParam[1];
		deleteCurrentRow(ctrl1);		
	}
	/************************获取点卡对应的面值************************************/
	this.GetObjectValue=function(optname,ctrl1,callbackfunc)
	{
		if( ctrl1.value=="-1" ){ callbackfunc("-1");return;}
		this.ClearParam();
		CurrentParam.push(optname,ctrl1,callbackfunc);
		WebFunctions.GetObjectValue(optname,ctrl1.value,this.GetObjectValue_Callback,this.OnAjaxTimeOut,this.OnAjaxError,this.OnAjaxAborted,this,ajaxtimeout);		
	}
	this.GetObjectValue_Callback=function(result, userContext, methodName)
	{
		if( !CheckAjaxResult_Int(result) ) return false;
		var opt=CurrentParam[0];
		var ctrl1=CurrentParam[1];
		var callbackfunc=CurrentParam[2];
		callbackfunc( parseInt(result) );
		//alert(callbackfunc);
		//ctrl1
	}
	/*************************获取数据列表*****************************/
	this.GetDataList=function(optname,ctrl1,callbackfunc)
	{
		if( ctrl1.value=="0" ){ callbackfunc("");return;}
		this.ClearParam();
		CurrentParam.push(optname,ctrl1,callbackfunc);

		WebFunctions.GetDataList(optname,ctrl1.value,this.GetDataList_Callback,this.OnAjaxTimeOut,this.OnAjaxError,this.OnAjaxAborted,this,ajaxtimeout);		
	}
	this.GetDataList_Callback=function(result, userContext, methodName)
	{
		var opt=CurrentParam[0];
		var ctrl1=CurrentParam[1];
		var callbackfunc=CurrentParam[2];
		callbackfunc( result );
		//alert(callbackfunc);
		//ctrl1
	}
	/****************************检测用户输入********************************/
	this.CheckUserInput=function(optname,inputctrl,failfunc,successfunc)
	{		
		this.ClearParam();
		CurrentParam.push(optname,inputctrl,failfunc,successfunc);
		WebFunctions.CheckUserInput(optname,inputctrl.value,this.CheckUserInput_Callback,this.OnAjaxTimeOut,this.OnAjaxError,this.OnAjaxAborted,this,ajaxtimeout);
	}

	this.CheckUserInput_Callback=function(result, userContext, methodName)
	{
		var opt=CurrentParam[0];
		var inputctrl=CurrentParam[1];
		var failfunc=CurrentParam[2];
		var successfunc=CurrentParam[3];

		if( result=="0" )
		{//
			if( failfunc!=null )
				failfunc();
		}
		else
		{
			if( successfunc!=null )
				successfunc();
		}
	}
	/************************************************************/
	var CurrentParam=[]; 

	this.OnAjaxTimeOut=function(result, userContext) 
	{
		alert('操作超时，请重试');
	}
	this.OnAjaxError=function(result,response, userContext) 
	{
		alert('操作异常，请重试');
	}
	this.OnAjaxAborted=function(result, userContext)
	{
		alert('操作已取消');
	}
	this.DefaultMethodComplete=function(result, userContext, methodName) 
	{
	}
	this.ClearParam=function()
	{
		CurrentParam.splice(0,CurrentParam.length);
	}	
}