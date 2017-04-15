(function(){
	var a={},b={};
    AP.widget.Validator=function()
		{this.options={
			          formId:"",itemClass:"fm-item",notifyClass:"fm-explain",
                      errorClass:"fm-error",tipsClass:"fm-tips",checkOnBlur:true,
				      onSubmit:true,stopSubmit:false,onSuccess:null,onFail:null,
				      ruleType:"name",userValidate:null,userDefine:null,
				      loadClass:"loading-text",checkNull:false,unitBytes:false};
		this.cache={access:false,tipfield:[]};
		this.options.rules={};
		this.items={};
		this.init.apply(this,arguments)};
	AP.widget.Validator.prototype={init:function(d)
		{L.augmentObject(this.options,d,true);
	     this.setAccess(this.options.onSubmit);
		 AP.cache.tester=this.options.rules;
		 window.tester=function(){
			 for(var e=[],g=0;g<arguments.length;g++){
				   var f=arguments[g];
			       if(AP.cache.tester.hasOwnProperty(f)){
					   delete AP.cache.tester[f];
					   e=e.concat(D.query("*[name="+f+"]")).concat(D.query("*[id="+f+"]"))
						   }
					   }
			log(e.length+" elements found: ");
			log(e);E.removeListener(e)};
		this.validate()},
		setAccess:function(d){
			if(d===true){
				for(var e=0,g=E.getListeners(this.options.formId,"submit");g&&e>-1;)
					{E.removeListener(this.options.formId,"submit");e++}
				E.on(this.options.formId,"submit",this.plugin.stopEvent)}
				},
		getExplain:function(d){
					var e=D.getAncestorByClassName(d,this.options.itemClass);
					return D.query("."+this.options.notifyClass,e)[0]||Element.create("div",{"class":this.options.notifyClass,appendTo:d.parentNode})},
		showExplain:function(d,e){
						var g=e.parentNode;
						D.removeClass(g,"fm-error");
						D.removeClass(g,"fm-hover");
						D.addClass(g,"fm-focus");
						["input","textarea"].contains(e.tagName.toLowerCase())&&e.select();
						try{
							if(this.getExplain(e).innerHTML.trim().length!=0){
								e.getAttribute("data-explain").trim().length==0&&D.addClass(this.getExplain(e),"fn-hide");
								this.getExplain(e).innerHTML=e.getAttribute("data-explain")}}
						catch(f){}},
		hoverIn:function(d,e){
						var g=e.parentNode;
						D.hasClass(g,"fm-error")||D.hasClass(g,"fm-focus")||D.addClass(g,"fm-hover")},
		hoverOut:function(d,e){
							D.removeClass(e.parentNode,"fm-hover")},
		getError:function(d){
						var e=1,g=b[d]||b.email;
						if(g!=null){for(;arguments[e];)g=g.replace(/%s/,arguments[e++]);return g}},
		showError:function(d){
						var e=D.query("."+this.options.notifyClass,this.parentNode)[0];
						D.addClass(this.parentNode,this.options.errorClass);
						D.removeClass(e,"fn-hide");
						e.innerHTML=d},

		getParentItem:function(d){for(d=d.parentNode;d.nodeType==1&&d!==document&&!D.hasClass(d,this.options.itemClass);)d=d.parentNode;return d},
		validateProcess:function(d,e){
							var g=e[0],
								r=this.getParentItem(g);
								D.removeClass(r,"fm-focus")},

		clearFocus:function(){D.removeClass(D.query(".fm-item",D.get(this.options.formId)),"fm-focus")},
		plugin:{maxLen:function(d,e){if(e[0].value.length>=_rule.maxLength)e[0].value=e[0].value.substring(0,_rule.maxLength)},
		stopEvent:function(d){E.preventDefault(d)},
		tracker:function(d){Tracker.click(d)}},
		validate:function(){
						for(i in this.options.rules){
							var d=this.options.ruleType==="name"?D.query("*[name="+i+"]")[0]:D.get(i),e=this.options.rules[i];
							if(!(d==null||d=="undefined")){
								d.getAttribute("data-explain")===null&&d.setAttribute("data-explain",this.getExplain(d).innerHTML.replace(/(\"|\')/g,"$1"));
								if(e.tips){
									this.cache.tipfield.push(d);
									if(d.value.trimAll().length===0){
										D.addClass(d,"fn-tips");
										d.value=e.tips}d.setAttribute("data-tips",e.tips);
										E.on(d,"focus",function(){D.removeClass(this,"fn-tips");if(this.value===this.getAttribute("data-tips"))this.value=""},"",d);	E.on(d,"blur",function(){if(this.value.trimAll().length===0){this.value=this.getAttribute("data-tips");D.addClass(this,"fn-tips")}},"",d)}E.addListener(d,"focus",this.showExplain,d,this);
										E.addListener(d,"mouseover",this.hoverIn,d,this);
										E.addListener(d,"mouseout",this.hoverOut,d,this);
										if(this.options.checkOnBlur){
											var g=typeof e.checkNull==="undefined"?this.options.checkNull:e.checkNull;
											E.addBlurListener(d,this.validateProcess,[d,null,g],this)}
										if(d.tagName.toUpperCase()=="TEXTAREA"&&(e.isLimit||0)){
											E.on(d,"keydown",this.plugin.maxLen,[d,e],this);
											E.on(d,"keyup",function(f,k){
												try{if(k.value.length>=e.maxLength)k.value=k.value.substring(0,this.options.rules[i].maxLength)}
												catch(n){}},d,this)
										}
									}
								}E.on(this.options.formId,"submit",this.onSubmitProcess,this,this)
							}
			}})();


E.onDOMReady(function(){
try{AP.util.inputHack()}
catch(b){}

 });
