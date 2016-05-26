var CONSOLE;
(function($){
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
			if (alertMsg) {
				alertMsg.error("<div>Http status: " + xhr.status + " " + xhr.statusText + "</div>" 
					+ "<div>ajaxOptions: "+ajaxOptions + "</div>"
					+ "<div>thrownError: "+thrownError + "</div>"
					+ "<div>"+xhr.responseText+"</div>");
			} else {
				alert("Http status: " + xhr.status + " " + xhr.statusText + "\najaxOptions: " + ajaxOptions + "\nthrownError:"+thrownError + "\n" +xhr.responseText);
			}
		},
		ajaxDone:function(json){
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
			this.container.ajaxUrl({
				type:options.type, url:url, data:options.data, callback:function(){
					if( $.isFunction(options.callback) )
						options.callback();
				}
			});
		},
		init:function(pageFrag, options){
			var op = $.extend({
					loginUrl:"login.html", loginTitle:null, callback:null, debug:false,containerId:"#mainContainer", 
					statusCode:{}, keys:{}
				}, options);
			this._set.loginUrl = op.loginUrl;
			this._set.loginTitle = op.loginTitle;
			this._set.debug = op.debug;
			this.container = $(op.containerId);
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
				CONSOLE.open(url,{title:title, fresh:fresh, external:external, type:method});

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
	}


	$.fn.extend({
		/**
		 * @param {Object} op: {type:GET/POST, url:ajax请求地址, data:ajax请求参数列表, callback:回调函数 }
		 */
		ajaxUrl: function(op){
			var $this = $(this);

			$this.trigger(CONSOLE.eventType.pageClear);
			
			$.ajax({
				type: op.type || 'GET',
				url: op.url,
				data: op.data,
				cache: false,
				success: function(response){
					var json = CONSOLE.jsonEval(response);
					
					if (json[CONSOLE.keys.statusCode]==CONSOLE.statusCode.error){
						if (json[CONSOLE.keys.message]) alertMsg.error(json[CONSOLE.keys.message]);
					} else {
						$this.html(response).initUI();
						if ($.isFunction(op.callback)) op.callback(response);
					}
					
					if (json[CONSOLE.keys.statusCode]==CONSOLE.statusCode.timeout){
						if ($.pdialog) $.pdialog.checkTimeout();
						if (navTab) navTab.checkTimeout();
	
						alertMsg.error(json[CONSOLE.keys.message] || CONSOLE.msg("sessionTimout"), {okCall:function(){
							CONSOLE.loadLogin();
						}});
					} 
					
				},
				error: CONSOLE.ajaxError,
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