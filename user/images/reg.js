var M_STR={UPPER:"ABCDEFGHIJKLMNOPQRSTUVWXYZ",LOWER:"abcdefghijklmnopqrstuvwxyz",NUMBER:"0123456789",CHARACTER:"!@#$%^&*?_~"};

AP.pk.pa.acctStatusCheck=new AP.Class({
	initialize:function(a){
			if(D.query(".fm-explain",this.container).length>0)this.explain=D.query(".fm-explain",this.container)[0];
			else{
				a=Element.create("div",{innerHTML:"","class":"fm-explain"});
			    D.insertAfter(a,this.options.inputEl);
			    this.explain=a}
			},
			check:function(a){
			this.options.showChecking&&this.checking();
			this.sendRequest(a)}
			});
var checkCustomQuestion=function(){
	if(D.get("secure_q"))D.get("secure_q").options[D.get("secure_q").selectedIndex].text==""?D.removeClass(D.get("secure_q-custom").parentNode,"fn-hide"):D.addClass(D.get("secure_q-custom").parentNode,"fn-hide")},
	checkPasstype=function(){	if(D.get("passtype"))D.get("passtype").options[D.get("passtype").selectedIndex].text==""?D.removeClass(D.query(".fm-explain",D.get("passtype").parentNode)[0],"fn-hide"):D.addClass(D.query(".fm-explain",D.get("passtype").parentNode)[0],"fn-hide")};
	if(D.get("form1")&&D.get("acctstatus-container")&&D.query("input",D.get("acctstatus-container")).length>0)
var acctStatusCheck=new AP.pk.pa.acctStatusCheck;

E.onDOMReady(function(){
					  D.get("form1")&&new AP.widget.Validator(
						  {onSubmit:true,rules:{
						                     _user_realname:{required:true},
											 _user_idcard:{required:true},
											 _user_email:{required:true},
											 _user_email2:{required:true},
									         _user_passwd:{required:true},
											"_user_passwd2":{required:true},
											_user_superpwd:{required:true},
					  					 "_user_superpwd2":{required:true}
											}
						  });

					 D.query(".fm-part").forEach(function(a){
						E.on(D.query("input",a),"focus",function(){D.addClass(a,"fn-bgc-blue")});
					    E.on(D.query("input",a),"blur",function(){D.removeClass(a,"fn-bgc-blue")})
						})
				});
