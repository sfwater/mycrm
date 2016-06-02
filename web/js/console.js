var CONSOLE;
/**
 * 普通ajax表单提交
 * @param {Object} form
 * @param {Object} callback
 * @param {String} confirmMsg 提示确认信息
 */
function validateCallback(form, callback, confirmMsg) {
	var $form = $(form);

	if (!$form.valid()) {
		return false;
	}
	
	var _submitFn = function(){
		$.ajax({
			type: form.method || 'POST',
			url:$form.attr("action"),
			data:$form.serializeArray(),
			dataType:"json",
			cache: false,
			success: callback || DWZ.ajaxDone,
			error: DWZ.ajaxError
		});
	}
	
	if (confirmMsg) {
		alertMsg.confirm(confirmMsg, {okCall: _submitFn});
	} else {
		_submitFn();
	}
	
	return false;
}
/**
 * 带文件上传的ajax表单提交
 * @param {Object} form
 * @param {Object} callback
 */
function iframeCallback(form, callback){
	var $form = $(form), $iframe = $("#callbackframe");
	if(!$form.valid()) {return false;}

	if ($iframe.size() == 0) {
		$iframe = $("<iframe id='callbackframe' name='callbackframe' src='about:blank' style='display:none'></iframe>").appendTo("body");
	}
	if(!form.ajax) {
		$form.append('<input type="hidden" name="ajax" value="1" />');
	}
	form.target = "callbackframe";
	
	_iframeResponse($iframe[0], callback || DWZ.ajaxDone);
}
function _iframeResponse(iframe, callback){
	var $iframe = $(iframe), $document = $(document);
	
	$document.trigger("ajaxStart");
	
	$iframe.bind("load", function(event){
		$iframe.unbind("load");
		$document.trigger("ajaxStop");
		
		if (iframe.src == "javascript:'%3Chtml%3E%3C/html%3E';" || // For Safari
			iframe.src == "javascript:'<html></html>';") { // For FF, IE
			return;
		}

		var doc = iframe.contentDocument || iframe.document;

		// fixing Opera 9.26,10.00
		if (doc.readyState && doc.readyState != 'complete') return; 
		// fixing Opera 9.64
		if (doc.body && doc.body.innerHTML == "false") return;
	   
		var response;
		
		if (doc.XMLDocument) {
			// response is a xml document Internet Explorer property
			response = doc.XMLDocument;
		} else if (doc.body){
			try{
				response = $iframe.contents().find("body").text();
				response = jQuery.parseJSON(response);
			} catch (e){ // response is html document or plain text
				response = doc.body.innerHTML;
			}
		} else {
			// response is a xml document
			response = doc;
		}
		
		callback(response);
	});
}
(function($){
	$.setRegional = function(key, value){
		if (!$.regional) $.regional = {};
		$.regional[key] = value;
	};

	CONSOLE = {
		regPlugins: [], // [function($parent){} ...] 
		// sbar: show sidebar
		keyCode: {
			ENTER: 13, ESC: 27, END: 35, HOME: 36,
			SHIFT: 16, TAB: 9,
			LEFT: 37, RIGHT: 39, UP: 38, DOWN: 40,
			DELETE: 46, BACKSPACE:8
		},
		container:{},
		titleHeader:{},
		subHeaderId:null,
		eventType: {
			pageClear:"pageClear",	// 用于重新ajaxLoad、关闭nabTab, 关闭dialog时，去除xheditor等需要特殊处理的资源
			resizeGrid:"resizeGrid"	// 用于窗口或dialog大小调整
		},
		isOverAxis: function(x, reference, size) {
			//Determines when x coordinate is over "b" element axis
			return (x > reference) && (x < (reference + size));
		},
		isOver: function(y, x, top, left, height, width) {
			//Determines when x, y coordinates is over "b" element
			return this.isOverAxis(y, top, height) && this.isOverAxis(x, left, width);
		},
		
		pageInfo: {pageNum:"pageNum", numPerPage:"numPerPage", orderField:"orderField", orderDirection:"orderDirection"},
		statusCode: {ok:200, error:500, timeout:502},
		keys: {statusCode:"statusCode", message:"message"},
		ui:{
			sbar:true,
			hideMode:'display' //navTab组件切换的隐藏方式，支持的值有’display’，’offsets’负数偏移位置的值，默认值为’display’
		},
		frag:{}, //page fragment
		_msg:{}, //alert message
		_set:{
			loginUrl:"", //session timeout
			loginTitle:"", //if loginTitle open a login dialog
			debug:false
		},
		msg:function(key, args){
			var _format = function(str,args) {
				args = args || [];
				var result = str || "";
				for (var i = 0; i < args.length; i++){
					result = result.replace(new RegExp("\\{" + i + "\\}", "g"), args[i]);
				}
				return result;
			}
			return _format(this._msg[key], args);
		},
		debug:function(msg){
			if (this._set.debug) {
				if (typeof(console) != "undefined") console.log(msg);
				else alert(msg);
			}
		},
		loadLogin:function(){
			if ($.pdialog && CONSOLE._set.loginTitle) {
				$.pdialog.open(CONSOLE._set.loginUrl, "login", CONSOLE._set.loginTitle, {mask:true,width:520,height:260});
			} else {
				window.location = CONSOLE._set.loginUrl;
			}
		},
		
		/*
		 * json to string
		 */
		obj2str:function(o) {
			var r = [];
			if(typeof o =="string") return "\""+o.replace(/([\'\"\\])/g,"\\$1").replace(/(\n)/g,"\\n").replace(/(\r)/g,"\\r").replace(/(\t)/g,"\\t")+"\"";
			if(typeof o == "object"){
				if(!o.sort){
					for(var i in o)
						r.push(i+":"+CONSOLE.obj2str(o[i]));
					if(!!document.all && !/^\n?function\s*toString\(\)\s*\{\n?\s*\[native code\]\n?\s*\}\n?\s*$/.test(o.toString)){
						r.push("toString:"+o.toString.toString());
					}
					r="{"+r.join()+"}"
				}else{
					for(var i =0;i<o.length;i++) {
						r.push(CONSOLE.obj2str(o[i]));
					}
					r="["+r.join()+"]"
				}
				return r;
			}
			return o.toString();
		},
		jsonEval:function(data) {
			try{
				if ($.type(data) == 'string')
					return eval('(' + data + ')');
				else return data;
			} catch (e){
				return {};
			}
		},
		ajaxError:function(xhr, ajaxOptions, thrownError){
			CONSOLE.hideLoading();
			if (alertMsg) {
				alertMsg.error("<div>Http status: " + xhr.status + " " + xhr.statusText + "</div>" 
					+ "<div>ajaxOptions: "+ajaxOptions + "</div>"
					+ "<div>thrownError: "+thrownError + "</div>"
					+ "<div>"+xhr.responseText+"</div>");
			} else {
				alert("Http status: " + xhr.status + " " + xhr.statusText + "\najaxOptions: " + ajaxOptions + "\nthrownError:"+thrownError + "\n" +xhr.responseText);
			}
		},
		ajaxTimeout: function(){
			CONSOLE.hideLoading();
			if (alertMsg) {
				alertMsg.error($.regional.messages.timeout);
			} else {
				alert($.regional.messages.timeout);
			}
		},
		ajaxDone:function(json){
			CONSOLE.hideLoading();
			if(json[CONSOLE.keys.statusCode] == CONSOLE.statusCode.error) {
				if(json[CONSOLE.keys.message] && alertMsg) alertMsg.error(json[CONSOLE.keys.message]);
			} else if (json[CONSOLE.keys.statusCode] == CONSOLE.statusCode.timeout) {
				if(alertMsg) alertMsg.error(json[CONSOLE.keys.message] || CONSOLE.msg("sessionTimout"), {okCall:CONSOLE.loadLogin});
				else CONSOLE.loadLogin();
			} else if (json[CONSOLE.keys.statusCode] == CONSOLE.statusCode.ok){
				if(json[CONSOLE.keys.message] && alertMsg) alertMsg.correct(json[CONSOLE.keys.message]);
			};
		},
		open:function(url,options){
			var $this = this;
			this.container.ajaxUrl({
				type:options.type, url:url, data:options.data, callback:function(response, target){
					//设置CONSOLE标题
					$this.titleHeader.html(options.name+'<small>'+options.title+'</small>');
					//设置内容标题
					target.find($this.subHeaderId).text(options.name);
					if( $.isFunction(options.callback) )
						options.callback(response);
				}
			});
		},
		showLoading: function(){
			this.loader.show();
		},
		hideLoading: function(){
			this.loader.hide();
		},
		init:function(pageFrag, options){
			var op = $.extend({
					loginUrl:"login.html", loginTitle:null, callback:null, 
					debug:false,containerId:"#consoleContainer",headerId:"#consoleHeader",subHeaderId:".subHeader",
					loaderId:'.loading',
					statusCode:{}, keys:{}
				}, options);
			this._set.loginUrl = op.loginUrl;
			this._set.loginTitle = op.loginTitle;
			this._set.debug = op.debug;
			this.container = $(op.containerId);
			this.titleHeader = $(op.headerId);
			this.subHeaderId = op.subHeaderId;
			this.loader = $(op.loaderId);
			$.extend(CONSOLE.statusCode, op.statusCode);
			$.extend(CONSOLE.keys, op.keys);
			$.extend(CONSOLE.pageInfo, op.pageInfo);
			$.extend(CONSOLE.ui, op.ui);
			
			var _doc = $(document);
			if (!_doc.isBind(CONSOLE.eventType.pageClear)) {
				_doc.bind(CONSOLE.eventType.pageClear, function(event){
					var box = event.target;
					if ($.fn.xheditor) {
						$("textarea.editor", box).xheditor(false);
					}
				});
			}

			_doc.initUI();
			alertMsg.init();
		}
	};
	function initUI(_box){
		var $p = $(_box || document);

		// navTab
		$("a[target=navTab]", $p).each(function(){
			$(this).click(function(event){
				var $this = $(this);
				var title = $this.attr("title") || $this.text();
				var tabid = $this.attr("rel") || "_blank";
				var fresh = eval($this.attr("fresh") || "true");
				var external = eval($this.attr("external") || "false");
				var method = $this.attr("method") || "GET";
				var url = unescape($this.attr("href"));
				var name = $this.text() || "";
				CONSOLE.open(url,{title:title, fresh:fresh, external:external, type:method, name:name});

				event.preventDefault();
			});
		});

		//dialogs
		$("a[target=dialog]", $p).each(function(){
			$(this).click(function(event){
				var $this = $(this);
				var title = $this.attr("title") || $this.text();
				var rel = $this.attr("rel") || "_blank";
				var options = {};
				var w = $this.attr("width");
				var h = $this.attr("height");
				if (w) options.width = w;
				if (h) options.height = h;
				options.max = eval($this.attr("max") || "false");
				options.mask = eval($this.attr("mask") || "false");
				options.maxable = eval($this.attr("maxable") || "true");
				options.minable = eval($this.attr("minable") || "true");
				options.fresh = eval($this.attr("fresh") || "true");
				options.resizable = eval($this.attr("resizable") || "true");
				options.drawable = eval($this.attr("drawable") || "true");
				options.close = eval($this.attr("close") || "");
				options.param = $this.attr("param") || "";

				var url = unescape($this.attr("href")).replaceTmById($(event.target).parents(".unitBox:first"));
				CONSOLE.debug(url);
				if (!url.isFinishedTm()) {
					alertMsg.error($this.attr("warn") || CONSOLE.msg("alertSelectMsg"));
					return false;
				}
				$.pdialog.open(url, rel, title, options);
				
				return false;
			});
		});
		$("a[target=ajax]", $p).each(function(){
			$(this).click(function(event){
				var $this = $(this);
				var rel = $this.attr("rel");
				if (rel) {
					var $rel = $("#"+rel);
					$rel.loadUrl($this.attr("href"), {}, function(){
						$rel.find("[layoutH]").layoutH();
					});
				}

				event.preventDefault();
			});
		});

		//required
		$('label.required',$p).each(function(){
			$(this).text($(this).text()+'*');
		});


		//validate 
		$("form.required-validate", $p).validate({
			highlight:function(element,errorClass,validClass){
				$(element).parent().addClass(errorClass);
			},
			unhighlight:function(element,errorClass,validClass){
				$(element).parent().removeClass(errorClass);
			},
			submitHandler: function(form){
				var $form = $(form);
				var confirmMsg = $form.attr('confirm');
				var callback = $form.attr('onsuccess');

				var	_callback = function(json){
						$('#btnSubmit',$form).button('reset');
						if( callback ){
							CONSOLE.hideLoading();
							callback(json);
						}
						else{
							CONSOLE.ajaxDone(json);
						}
					};
				var _errorCallback = function(xhr, ajaxOptions, thrownError){
					$('#btnSubmit', $form).button('reset');
					CONSOLE.ajaxError(xhr, ajaxOptions, thrownError);
				}
				var _submitFn = function(){
					$.ajax({
						type: form.method || 'POST',
						url:$form.attr("action"),
						data:$form.serializeArray(),
						dataType:"json",
						cache: false,
						beforeSend: function(){
							$form.find('#btnSubmit').button('loading');
							CONSOLE.showLoading();
						},
						success: _callback,
						error: _errorCallback
					});
				}
				
				if (confirmMsg) {
					alertMsg.confirm(confirmMsg, {okCall: _submitFn});
				} else {
					_submitFn();
				}
	
				return false;
			},
			errorClass:'has-error'
		});
		//表单重置
		$("form.required-validate", $p).find('#btnReset').click(function(){
			$(this).parents('form.required-validate')[0].reset();
		});

		//datepicker
		$('.datepicker',$p).datepicker().on('changeDate',function(ev){
			$(this).datepicker('hide');
		});


		//搜索表单
		$("form.searchForm", $p).submit(function(){
			return false;
		});
	}
	var alertMsg = {
		_boxId: "#alertMsgBox",
		_bgId: "#alertBackground",
		_closeTimer: null,

		_types: {error:"error", info:"info", warn:"warn", correct:"correct", confirm:"confirm"},

		_getTitle: function(key){
			return $.regional.alertMsg.title[key];
		},

		_keydownOk: function(event){
			if (event.keyCode == DWZ.keyCode.ENTER) event.data.target.trigger("click");
			return false;
		},
		_keydownEsc: function(event){
			if (event.keyCode == DWZ.keyCode.ESC) event.data.target.trigger("click");
		},
		/**
		 * 
		 * @param {Object} type
		 * @param {Object} msg
		 * @param {Object} buttons [button1, button2]
		 */
		_open: function(type, msg, buttons, options){
			var modal = this.modal;
		  	this.modalTitle.text(type);
			this.modalBody.html(msg);
			this.modalFooter.find('button').remove();

			$(buttons).each(function(){
				var button = $('<button type="button" class="'+this.css+'">'+this.name+'</button>');
				var callback = this.call;
				button.click(function(){
					if( $.isFunction(callback) ){
						callback();
					}
					alertMsg.close();
				});
				modal.find('.modal-footer').append(button);
			});
			this.modal.modal(options);
		},
		init: function(){
			this.modal = $(this._boxId);
			this.modalTitle = this.modal.find('.modal-title');
			this.modalBody = this.modal.find('.modal-body');
			this.modalFooter = this.modal.find('.modal-footer');
		},
		close: function(){
			this.modal.modal('hide');
		},
		error: function(msg, options) {
			this._alert(this._types.error, msg, options);
		},
		info: function(msg, options) {
			this._alert(this._types.info, msg, options);
		},
		warn: function(msg, options) {
			this._alert(this._types.warn, msg, options);
		},
		correct: function(msg, options) {
			this._alert(this._types.correct, msg, options);
		},
		_alert: function(type, msg, options) {
			var op = {okName:$.regional.alertMsg.butMsg.close, okCall:null, modalOption:{backdrop:'static'}};
			$.extend(op, options);
			var buttons = [
				{name:op.okName, call: op.okCall, keyCode:CONSOLE.keyCode.ENTER, css:'btn btn-default'}
			];
			this._open(type, msg, buttons, op.modalOption);
		},
		/**
		 * 
		 * @param {Object} msg
		 * @param {Object} options {okName, okCal, cancelName, cancelCall}
		 */
		confirm: function(msg, options) {
			var op = {okName:$.regional.alertMsg.butMsg.ok, okCall:null, cancelName:$.regional.alertMsg.butMsg.cancel, cancelCall:null};
			$.extend(op, options);
			var buttons = [
				{name:op.okName, call: op.okCall, keyCode:DWZ.keyCode.ENTER, css:'btn btn-primary'},
				{name:op.cancelName, call: op.cancelCall, keyCode:DWZ.keyCode.ESC, css:'btn btn-default'}
			];
			this._open(this._types.confirm, msg, buttons, options.modalOption);
		}
	};

	$.fn.extend({
		/**
		 * @param {Object} op: {type:GET/POST, url:ajax请求地址, data:ajax请求参数列表, callback:回调函数 }
		 */
		ajaxUrl: function(op){
			var $this = $(this);

			$.ajax({
				type: op.type || 'GET',
				url: op.url,
				data: op.data,
				cache: false,
				success: function(response){
					CONSOLE.hideLoading();
					var json = CONSOLE.jsonEval(response);
					
					if (json[CONSOLE.keys.statusCode]==CONSOLE.statusCode.error){
						if (json[CONSOLE.keys.message]) alertMsg.error(json[CONSOLE.keys.message]);
					} else {
						$this.html(response).initUI();
						if ($.isFunction(op.callback)) op.callback(response, $this);
					}
					
					if (json[CONSOLE.keys.statusCode]==CONSOLE.statusCode.timeout){
						if ($.pdialog) $.pdialog.checkTimeout();
						if (navTab) navTab.checkTimeout();
	
						alertMsg.error(json[CONSOLE.keys.message] || CONSOLE.msg("sessionTimout"), {okCall:function(){
							CONSOLE.loadLogin();
						}});
					} 
					
				},
				beforeSend: function(){
					CONSOLE.showLoading();
				},
				error: CONSOLE.ajaxError,
				timeout: CONSOLE.ajaxTimeout,
				statusCode: {
					503: function(xhr, ajaxOptions, thrownError) {
						alert(CONSOLE.msg("statusCode_503") || thrownError);
					}
				}
			});
		},
		loadUrl: function(url,data,callback){
			$(this).ajaxUrl({url:url, data:data, callback:callback});
		},
		initUI: function(){
			return this.each(function(){
				if($.isFunction(initUI)) initUI(this);
			});
		},
		isTag:function(tn) {
			if(!tn) return false;
			return $(this)[0].tagName.toLowerCase() == tn?true:false;
		},
		/**
		 * 判断当前元素是否已经绑定某个事件
		 * @param {Object} type
		 */
		isBind:function(type) {
			var _events = $(this).data("events");
			return _events && type && _events[type];
		},
		/**
		 * 输出firebug日志
		 * @param {Object} msg
		 */
		log:function(msg){
			return this.each(function(){
				if (console) console.log("%s: %o", msg, this);
			});
		}
	});
	
	/**
	 * 扩展String方法
	 */
	$.extend(String.prototype, {
		isPositiveInteger:function(){
			return (new RegExp(/^[1-9]\d*$/).test(this));
		},
		isInteger:function(){
			return (new RegExp(/^\d+$/).test(this));
		},
		isNumber: function(value, element) {
			return (new RegExp(/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/).test(this));
		},
		trim:function(){
			return this.replace(/(^\s*)|(\s*$)|\r|\n/g, "");
		},
		startsWith:function (pattern){
			return this.indexOf(pattern) === 0;
		},
		endsWith:function(pattern) {
			var d = this.length - pattern.length;
			return d >= 0 && this.lastIndexOf(pattern) === d;
		},
		replaceSuffix:function(index){
			return this.replace(/\[[0-9]+\]/,'['+index+']').replace('#index#',index);
		},
		trans:function(){
			return this.replace(/&lt;/g, '<').replace(/&gt;/g,'>').replace(/&quot;/g, '"');
		},
		encodeTXT: function(){
			return (this).replaceAll('&', '&amp;').replaceAll("<","&lt;").replaceAll(">", "&gt;").replaceAll(" ", "&nbsp;");
		},
		replaceAll:function(os, ns){
			return this.replace(new RegExp(os,"gm"),ns);
		},
		replaceTm:function($data){
			if (!$data) return this;
			return this.replace(RegExp("({[A-Za-z_]+[A-Za-z0-9_]*})","g"), function($1){
				return $data[$1.replace(/[{}]+/g, "")];
			});
		},
		replaceTmById:function(_box){
			var $parent = _box || $(document);
			return this.replace(RegExp("({[A-Za-z_]+[A-Za-z0-9_]*})","g"), function($1){
				var $input = $parent.find("#"+$1.replace(/[{}]+/g, ""));
				return $input.val() ? $input.val() : $1;
			});
		},
		isFinishedTm:function(){
			return !(new RegExp("{[A-Za-z_]+[A-Za-z0-9_]*}").test(this)); 
		},
		skipChar:function(ch) {
			if (!this || this.length===0) {return '';}
			if (this.charAt(0)===ch) {return this.substring(1).skipChar(ch);}
			return this;
		},
		isValidPwd:function() {
			return (new RegExp(/^([_]|[a-zA-Z0-9]){6,32}$/).test(this)); 
		},
		isValidMail:function(){
			return(new RegExp(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/).test(this.trim()));
		},
		isSpaces:function() {
			for(var i=0; i<this.length; i+=1) {
				var ch = this.charAt(i);
				if (ch!=' '&& ch!="\n" && ch!="\t" && ch!="\r") {return false;}
			}
			return true;
		},
		isPhone:function() {
			return (new RegExp(/(^([0-9]{3,4}[-])?\d{3,8}(-\d{1,6})?$)|(^\([0-9]{3,4}\)\d{3,8}(\(\d{1,6}\))?$)|(^\d{3,8}$)/).test(this));
		},
		isUrl:function(){
			return (new RegExp(/^[a-zA-z]+:\/\/([a-zA-Z0-9\-\.]+)([-\w .\/?%&=:]*)$/).test(this));
		},
		isExternalUrl:function(){
			return this.isUrl() && this.indexOf("://"+document.domain) == -1;
		}
	});
})(jQuery);