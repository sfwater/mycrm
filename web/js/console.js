var CONSOLE;
var _lookup = {currentGroup:"", suffix:"", $target:null, pk:"id"};
var _util = {
	_lookupPrefix: function(key){
		var strDot = _lookup.currentGroup ? "." : "";
		return _lookup.currentGroup + strDot + key + _lookup.suffix;
	},
	lookupPk: function(key){
		return this._lookupPrefix(key);
	},
	lookupField: function(key){
		return this.lookupPk(key);
	}
};
function ajaxTodo(url, callback){
	var $callback = callback || CONSOLE.ajaxDone;
	if (! $.isFunction($callback)) $callback = eval('(' + callback + ')');
	$.ajax({
		type:'DELETE',
		url:url,
		dataType:"json",
		cache: false,
		success: $callback,
		error: CONSOLE.ajaxError
	});
}
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
			success: callback || CONSOLE.ajaxDone,
			error: CONSOLE.ajaxError
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
* 对话框保存成功
*/
function dialogCallback(json){
	CONSOLE.reload();
	CONSOLE.ajaxDone(json);
	$.pdialog.closeCurrent();
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
	
	_iframeResponse($iframe[0], callback || CONSOLE.ajaxDone);
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
/**
 * 处理navTab中的分页和排序
 * targetType: navTab 或 dialog
 * rel: 可选 用于局部刷新div id号
 * data: pagerForm参数 {pageNum:"n", numPerPage:"n", orderField:"xxx", orderDirection:""}
 * callback: 加载完成回调函数
 */
function consolePageBreak(options){
	var op = $.extend({ rel:"", data:{pageNum:"", orderField:"", orderDirection:""}, callback:null}, options);
	var $box = op.rel == "" ? CONSOLE.getCurrentPanel() : $(op.rel);
	var form = _getPagerForm($box, op.data);

	var callback = function(response){
		if( $.isFunction(op.callback) ){
			op.callback(response);
		}
		var _callback = $(form).attr('onsuccess');
		if( $.isFunction(_callback) ){
			_callback(response);
		}
	}

	if (form) {
		$box.ajaxUrl({type:$(form).attr("method"), url:$(form).attr("action"), data: $(form).serializeArray(), callback:callback});
	}
}
/**
 * 处理dialog中的分页和排序
 * targetType: dialog
 * rel: 可选 用于局部刷新div id号
 * data: pagerForm参数 {pageNum:"n", numPerPage:"n", orderField:"xxx", orderDirection:""}
 * callback: 加载完成回调函数
 */
function dialogPageBreak(options){
	var op = $.extend({ rel:"", data:{pageNum:"", orderField:"", orderDirection:""}, callback:null}, options);
	var $box = op.rel == "" ? $.pdialog.getCurrentBody() : $(op.rel);
	var form = _getPagerForm($box, op.data);

	var callback = function(response){
		if( $.isFunction(op.callback) ){
			op.callback(response);
		}
		var _callback = $(form).attr('onsuccess');
		if( $.isFunction(_callback) ){
			_callback(response);
		}
	}

	if (form) {
		$box.ajaxUrl({type:$(form).attr("method"), url:$(form).attr("action"), data: $(form).serializeArray(), callback:callback});
	}
}

/**
 * 
 * @param {Object} args {pageNum:"",numPerPage:"",orderField:"",orderDirection:""}
 * @param String formId 分页表单选择器，非必填项默认值是 "pagerForm"
 */
function _getPagerForm($parent, args) {
	var form = $("form.searchForm", $parent).get(0);

	if (form && args) {
		if (args["pageNum"]) form[CONSOLE.pageInfo.pageNum].value = args["pageNum"];
		if (args["orderField"]) form[CONSOLE.pageInfo.orderField].value = args["orderField"];
		if (args["orderDirection"] && form[CONSOLE.pageInfo.orderDirection]) form[CONSOLE.pageInfo.orderDirection].value = args["orderDirection"];
	}
	
	return form;
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
		
		pageInfo: {pageNum:"page", orderField:"orderField", orderDirection:"orderDirection"},
		statusCode: {ok:200, error:500, timeout:502},
		keys: {statusCode:"statusCode", message:"message"},
		ui:{
			sbar:true,
			hideMode:'display' //navTab组件切换的隐藏方式，支持的值有’display’，’offsets’负数偏移位置的值，默认值为’display’
		},
		frag:{}, //page fragment
		_msg:{
			alertSelectMsg:'请至少选中一条数据！',
			alertSelectHrefMsg: '没有设置处理网址',
		}, //alert message
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
				json = CONSOLE.jsonEval(xhr.responseText);
				alertMsg.error(json[CONSOLE.keys.message]);
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
		reload:function(){
			this.container.reload();
		},
		getCurrentPanel: function(){
			return this.container;
		},
		showLoading: function(){
			// this.loader.show();
			layer.load(2);
		},
		hideLoading: function(){
			// this.loader.hide();
			layer.closeAll('loading');
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
				var callback = $this.attr("callback");
				CONSOLE.open(url,{title:title, fresh:fresh, external:external, type:method, name:name, callback:callback});
				$("a[target=navTab]", $p).removeClass("active");
				$this.addClass("active");
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
				var type = $this.attr("method") || "POST";
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
				options.type = type;
				options.size = eval($this.attr("size")) || BootstrapDialog.SIZE_NORMAL;
				options.buttons = eval($this.attr('hide-buttons'));

				var url = unescape($this.attr("href"));
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
				var callback = eval($form.attr('onsuccess'));

				var	_callback = function(json){
						$('#btnSubmit',$form).button('reset');
						if( callback ){
							CONSOLE.hideLoading();
							callback(json,$form);
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
			var $this = $(this);
			var targetType = $(this).attr("targetType") || "navTab";

			if( targetType == "navTab" )
				consolePageBreak({data:{pageNum:1}});
			else
				dialogPageBreak({data:{pageNum:1}});
			return false;
		});

		//分页
		$(".pagination a", $p).click(function(ev){
			var page = $(this).attr("page-index");
			var targetType = $(this).attr("targetType") || "navTab";

			if( targetType == "navTab" ){
				consolePageBreak({data:{pageNum:page}});
			}
			else{
				dialogPageBreak({data:{pageNum:page}});
			}
			ev.preventDefault();
		});
		$(".pagination-goto button").click(function(){
			var page = parseInt($("#gotoPageNo").val());
			var targetType = $(this).attr("targetType") || "navTab";
			if( page > 0 ){
				if( targetType == "navTab" )
					consolePageBreak({data:{pageNum:page}});
				else
					dialogPageBreak({data:{pageNum:page}});
			}
		});
		//排序
		$("th.sortable", $p).each(function(){
			var $this = $(this);
			var form = _getPagerForm($p,null);
			var direction = form[CONSOLE.pageInfo.orderDirection].value;
			var orderField = form[CONSOLE.pageInfo.orderField].value;

			if( orderField == $this.attr("order-field") ){
				var icon = $('<span></span>');
				$this.append(icon);
				if( direction == "desc" ){
					icon.addClass("icon-arrow-down");
				}
				else{
					icon.addClass("icon-arrow-up");
				}
				$this.data("direction",direction);
			}

			return $this.click(function(){
				var direction = $this.data("direction") || 'asc';
				var field = $this.attr("order-field");
				consolePageBreak({data:{orderField:field, orderDirection:direction == "asc" ? "desc":"asc"}});				
			});
		});

		$(":button.checkbox-all, :checkbox.checkbox-all", $p).checkboxCtrl($p);
		$(":button[target=selectedTodo], a[target=selectedTodo]", $p).selectedTodo($p);
		$('[data-toggle="tooltip"]').tooltip();
		$('input:file').each(function(){
			var $this = $(this);
			var showPreview = eval($this.attr('showPreview') || "false");
			var allowedExtensions = eval($this.attr('allowed') || "['jpg','gif','png']");
			var uploadUrl = $this.attr('uploadUrl') || '/admin/upload';
			var uploadExtraData = $this.attr('uploadExtraData') || {};
			$this.fileinput({
				showPreview:showPreview, 
				allowedExtensions:allowedExtensions,
				language:'zh',
				uploadUrl:uploadUrl,
				uploadExtraData: uploadExtraData,
			});
			$this.on("fileuploaded",function(event, data, previewId, index){
				var response = data.response;	
				$this.parents('.file-input').siblings("input:hidden").val(response.data.webPath);
			});
		});
		if ($.fn.lookup) $("a[lookupGroup],button[lookupGroup]", $p).lookup();
		if ($.fn.multLookup) $("[multLookup]:button", $p).multLookup();
		if ($.fn.ajaxTodo) $("a[target=ajaxTodo]", $p).ajaxTodo();
	}
	var alertMsg = {
		_closeTimer: null,

		_types: {error:"发生错误!", info:"提示信息", warn:"警告!", correct:"操作成功", confirm:"请您确认？！"},

		_getTitle: function(key){
			return $.regional.alertMsg.title[key];
		},

		_keydownOk: function(event){
			if (event.keyCode == CONSOLE.keyCode.ENTER) event.data.target.trigger("click");
			return false;
		},
		_keydownEsc: function(event){
			if (event.keyCode == CONSOLE.keyCode.ESC) event.data.target.trigger("click");
		},
		/**
		 * 
		 * @param {Object} type
		 * @param {Object} msg
		 * @param {Object} buttons [button1, button2]
		 */
		_open: function(type, msg, buttons, options){
			BootstrapDialog.show({
                type: options.type,
                title: type,
                message: msg,
                buttons: buttons
            });
		},
		init: function(){
		},
		close: function(){
		},
		error: function(msg, options) {
			this._alert(this._types.error, msg, $.extend(options,{type:BootstrapDialog.TYPE_DANGER}));
		},
		info: function(msg, options) {
			this._alert(this._types.info, msg, $.extend(options,{type:BootstrapDialog.TYPE_INFO}));
		},
		warn: function(msg, options) {
			this._alert(this._types.warn, msg, $.extend(options,{type:BootstrapDialog.TYPE_WARNING}));
		},
		correct: function(msg, options) {
			this._alert(this._types.correct, msg, $.extend(options,{type:BootstrapDialog.TYPE_SUCCESS}));
		},
		_alert: function(type, msg, options) {
			var op = $.extend({okName:$.regional.alertMsg.butMsg.ok, okCall:null, type:BootstrapDialog.TYPE_DEFAULT}, options);
	        BootstrapDialog.alert({
	            title: type,
	            message: msg,
	            type: op.type, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
	            closable: true, // <-- Default value is false
	            draggable: false, // <-- Default value is false
	            buttonLabel: op.okName, // <-- Default value is 'OK',
	            callback: function(result) {
	            	if( $.isFunction(op.okCall) ){
	            		op.okCall(result);
	            	}
	            }
	        });
		},
		/**
		 * 
		 * @param {Object} msg
		 * @param {Object} options {okName, okCal, cancelName, cancelCall}
		 */
		confirm: function(msg, options) {
			var op = $.extend({
				okName:$.regional.alertMsg.butMsg.ok, 
				okCall:null, 
				cancelName:$.regional.alertMsg.butMsg.cancel, 
				cancelCall:null, 
				type: BootstrapDialog.TYPE_WARNING}, 
				options);
	        BootstrapDialog.confirm({
	            title: this._types.confirm,
	            message: msg,
	            type: op.type, // <-- Default value is BootstrapDialog.TYPE_PRIMARY
	            closable: true, // <-- Default value is false
	            draggable: true, // <-- Default value is false
	            btnCancelLabel:op.cancelName, // <-- Default value is 'Cancel',
	            btnOKLabel: op.okName, // <-- Default value is 'OK',
	            btnOKClass: 'btn-warning', // <-- If you didn't specify it, dialog type will be used,
	            callback: function(result) {
	                if(result) {
	                	$.isFunction(op.okCall) && op.okCall();
	                }else {
	                	$.isFunction(op.cancelCall) && op.cancelCall();
	                }
	            }
	        });
		}
	};
	$.fn.extend({
		checkboxCtrl: function(parent){
			return this.each(function(){
				var $trigger = $(this);
				$trigger.click(function(){
					var group = $trigger.attr("group");
					if ($trigger.is(":checkbox")) {
						var type = $trigger.is(":checked") ? "all" : "none";
						if (group) $.checkbox.select(group, type, parent);
					} else {
						if (group) $.checkbox.select(group, $trigger.attr("selectType") || "all", parent);
					}
					
				});
			});
		}
	});
	$.checkbox = {
		selectAll: function(_name, _parent){
			this.select(_name, "all", _parent);
		},
		unSelectAll: function(_name, _parent){
			this.select(_name, "none", _parent);
		},
		selectInvert: function(_name, _parent){
			this.select(_name, "invert", _parent);
		},
		select: function(_name, _type, _parent){
			$parent = $(_parent || document);
			$checkboxLi = $parent.find(":checkbox[name='"+_name+"']");
			switch(_type){
				case "invert":
					$checkboxLi.each(function(){
						$checkbox = $(this);
						$checkbox.prop('checked', !$checkbox.is(":checked"));
						$checkbox.attr('checked', !$checkbox.is(":checked"));
					});
					break;
				case "none":
					$checkboxLi.prop('checked', false);
					$checkboxLi.attr('checked', false);
					break;
				default:
					$checkboxLi.prop('checked', true);
					$checkboxLi.attr('checked', true);
					break;
			}
		}
	};
	$.extend({
		bringBackSuggest: function(args){
			var $box = _lookup['$target'].parents(".unitBox:first");
			$box.find(":input").each(function(){
				var $input = $(this), inputName = $input.attr("id"), acceptName = $input.attr("accept");
				for (var key in args) {
					var name = (_lookup.pk == key) ? _util.lookupPk(key) : _util.lookupField(key);

					if (name == inputName || name == acceptName) {
						$input.val(args[key]);
						break;
					}
				}
			});
		},
		bringBack: function(args){
			$.bringBackSuggest(args);
			$.pdialog.closeCurrent();
			if( typeof(bringBackCallback) == "function" ){
				bringBackCallback(args);
			}
		}
	});
	$.fn.extend({
		ajaxTodo:function(){
			return this.each(function(){
				var $this = $(this);
				$this.click(function(event){
					var url = unescape($this.attr("href"));
					if (!url.isFinishedTm()) {
						alertMsg.error($this.attr("warn") || CONSOLE.msg("alertSelectMsg"));
						return false;
					}
					var title = $this.attr("title");
					if (title) {
						alertMsg.confirm(title, {
							okCall: function(){
								ajaxTodo(url, $this.attr("callback"));
							}
						});
					} else {
						ajaxTodo(url, $this.attr("callback"));
					}
					event.preventDefault();
				});
			});
		},
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
					$this.data("url",op.url);
					$this.data("options", op);
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
		reload: function(){
			var url = $(this).data("url");
			var options = $(this).data("options");
			if( options )
				this.loadUrl(url, options.data, options.callback);
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
		},
		pagerForm: function(options){
			var op = $.extend({pagerForm$:"#pagerForm", parentBox:document}, options);
			var frag = '<input type="hidden" name="#name#" value="#value#" />';
			return this.each(function(){
				var $searchForm = $(this), $pagerForm = $(op.pagerForm$, op.parentBox);
				var actionUrl = $pagerForm.attr("action").replaceAll("#rel#", $searchForm.attr("action"));
				$pagerForm.attr("action", actionUrl);
				$searchForm.find(":input").each(function(){
					var $input = $(this), name = $input.attr("name");
					if (name && (!$input.is(":checkbox,:radio") || $input.is(":checked"))){
						if ($pagerForm.find(":input[name='"+name+"']").length == 0) {
							var inputFrag = frag.replaceAll("#name#", name).replaceAll("#value#", $input.val());
							$pagerForm.append(inputFrag);
						}
					}
				});
			});
		},
		selectedTodo: function(parent){
			var $parent = parent ? parent : CONSOLE.container;	
			function _getIds(selectedIds, rel){
				var ids = "";
				var $box = rel == undefined ? $parent : $(rel);
				$box.find("input:checked").filter("[name='"+selectedIds+"']").each(function(i){
					var val = $(this).val();
					ids += i==0 ? val : ","+val;
				});
				return ids;
			}
			return this.each(function(){
				var $this = $(this);
				var selectedIds = $this.attr('group') || "ids[]";
				var postType = $this.attr("postType") || "map";
				var action = $this.attr("action");


				$this.click(function(){
					var rel = $this.attr("rel");
					var ids = _getIds(selectedIds, rel);
					var href= $this.attr('href');
					var $box = rel == undefined ? $parent : $(rel);
					var reload = $this.attr("reload") || "true";
					if (!ids) {
						alertMsg.error($this.attr("warn") || CONSOLE.msg("alertSelectMsg"));
						return false;
					}
					if (!href) {
						alertMsg.error(CONSOLE.msg("alertSelectHrefMsg"));
						return false;
					}
					if( !action ){
						alertMsg.error(CONSOLE.msg("selectActionMsg"));
						return false;
					}

					
					var _callback = $this.attr("callback") || CONSOLE.ajaxDone;
					var method = $this.attr("method") || "DELETE";
					if (! $.isFunction(_callback)) _callback = eval('(' + _callback + ')');
					function _doPost(){
						$.ajax({
							type:method, url:href, dataType:'json', cache: false,
							data: function(){
								if (postType == 'map'){
									var data = $.map(ids.split(','), function(val, i) {
										return {name: selectedIds, value: val};
									});
									data.push({name:'action', value:action});
									return data;
								} else {
									var _data = {};
									_data[selectedIds] = ids;
									_data.action = action;
									return _data;
								}
							}(),
							success: function(response){
								if( reload == "true" )
									$box.reload();
								_callback(response);
							},
							error: CONSOLE.ajaxError
						});
					}
					var title = $this.attr("title");
					if (title) {
						alertMsg.confirm(title, {okCall: _doPost});
					} else {
						_doPost();
					}
					return false;
				});
				
			});
		},
		lookup: function(){
			return this.each(function(){
				var $this = $(this), options = {mask:true, 
					width:$this.attr('width')||820, height:$this.attr('height')||400,
					maxable:eval($this.attr("maxable") || "true"),
					resizable:eval($this.attr("resizable") || "true"),
					size: BootstrapDialog.SIZE_WIDE,
					buttons:[]
				};
				$this.click(function(event){
					event.preventDefault();
					_lookup = $.extend(_lookup, {
						currentGroup: $this.attr("lookupGroup") || "",
						suffix: $this.attr("suffix") || "",
						$target: $this,
						pk: $this.attr("lookupPk") || "id"
					});
					
					var url = unescape($this.attr("href"));
					if (!url.isFinishedTm()) {
						alertMsg.error($this.attr("warn") || CONSOLE.msg("alertSelectMsg"));
						return false;
					}
					
					$.pdialog.open(url, "_blank", $this.attr("title") || $this.text(), options);
					return false;
				});
			});
		},
		multLookup: function(){
			return this.each(function(){
				var $this = $(this), args={};
				$this.click(function(event){
					var $unitBox = $this.parents(".modal-body:first");
					$unitBox.find("[name='"+$this.attr("multLookup")+"']").filter(":checked").each(function(){
						var _args = CONSOLE.jsonEval($(this).val());
						for (var key in _args) {
							var value = args[key] ? args[key]+"," : "";
							args[key] = value + _args[key];
						}
					});

					if ($.isEmptyObject(args)) {
						alertMsg.error($this.attr("warn") || CONSOLE.msg("alertSelectMsg"));
						return false;
					}
					$.bringBack(args);
				});
			});
		},
		suggest: function(){
			var op = {suggest$:"#suggest", suggestShadow$: "#suggestShadow"};
			var selectedIndex = -1;
			return this.each(function(){
				var $input = $(this).attr('autocomplete', 'off').keydown(function(event){
					if (event.keyCode == CONSOLE.keyCode.ENTER && $(op.suggest$).is(':visible')) return false; //屏蔽回车提交
				});
				
				var suggestFields=$input.attr('suggestFields').split(",");
				
				function _show(event){
					var offset = $input.offset();
					var iTop = offset.top+this.offsetHeight;
					var $suggest = $(op.suggest$);
					if ($suggest.size() == 0) $suggest = $('<div id="suggest"></div>').appendTo($('body'));

					$suggest.css({
						left:offset.left+'px',
						top:iTop+'px'
					}).show();
					
					_lookup = $.extend(_lookup, {
						currentGroup: $input.attr("lookupGroup") || "",
						suffix: $input.attr("suffix") || "",
						$target: $input,
						pk: $input.attr("lookupPk") || "id"
					});

					var url = unescape($input.attr("suggestUrl")).replaceTmById($(event.target).parents(".unitBox:first"));
					if (!url.isFinishedTm()) {
						alertMsg.error($input.attr("warn") || CONSOLE.msg("alertSelectMsg"));
						return false;
					}
					
					var postData = {};
					postData[$input.attr("postField")||"inputValue"] = $input.val();

					$.ajax({
						global:false,
						type:'POST', dataType:"json", url:url, cache: false,
						data: postData,
						success: function(response){
							if (!response) return;
							var html = '';

							$.each(response, function(i){
								var liAttr = '', liLabel = '';
								
								for (var i=0; i<suggestFields.length; i++){
									var str = this[suggestFields[i]];
									if (str) {
										if (liLabel) liLabel += '-';
										liLabel += str;
									}
								}
								for (var key in this) {
									if (liAttr) liAttr += ',';
									liAttr += key+":'"+this[key]+"'";
								}
								html += '<li lookupAttrs="'+liAttr+'">' + liLabel + '</li>';
							});
							
							var $lis = $suggest.html('<ul>'+html+'</ul>').find("li");
							$lis.hoverClass("selected").click(function(){
								_select($(this));
							});
							if ($lis.size() == 1 && event.keyCode != CONSOLE.keyCode.BACKSPACE) {
								_select($lis.eq(0));
							} else if ($lis.size() == 0){
								var jsonStr = "";
								for (var i=0; i<suggestFields.length; i++){
									if (_util.lookupField(suggestFields[i]) == event.target.name) {
										break;
									}
									if (jsonStr) jsonStr += ',';
									jsonStr += suggestFields[i]+":''";
								}
								jsonStr = "{"+_lookup.pk+":''," + jsonStr +"}";
								$.bringBackSuggest(CONSOLE.jsonEval(jsonStr));
							}
						},
						error: function(){
							$suggest.html('');
						}
					});

					$(document).bind("click", _close);
					return false;
				}
				function _select($item){
					var jsonStr = "{"+ $item.attr('lookupAttrs') +"}";
					
					$.bringBackSuggest(CONSOLE.jsonEval(jsonStr));
				}
				function _close(){
					$(op.suggest$).html('').hide();
					selectedIndex = -1;
					$(document).unbind("click", _close);
				}
				
				$input.focus(_show).click(false).keyup(function(event){
					var $items = $(op.suggest$).find("li");
					switch(event.keyCode){
						case CONSOLE.keyCode.ESC:
						case CONSOLE.keyCode.TAB:
						case CONSOLE.keyCode.SHIFT:
						case CONSOLE.keyCode.HOME:
						case CONSOLE.keyCode.END:
						case CONSOLE.keyCode.LEFT:
						case CONSOLE.keyCode.RIGHT:
							break;
						case CONSOLE.keyCode.ENTER:
							_close();
							break;
						case CONSOLE.keyCode.DOWN:
							if (selectedIndex >= $items.size()-1) selectedIndex = -1;
							else selectedIndex++;
							break;
						case CONSOLE.keyCode.UP:
							if (selectedIndex < 0) selectedIndex = $items.size()-1;
							else selectedIndex--;
							break;
						default:
							_show(event);
					}
					$items.removeClass("selected");
					if (selectedIndex>=0) {
						var $item = $items.eq(selectedIndex).addClass("selected");
						_select($item);
					}
				});
			});
		}
	});
	$.pdialog = {
		_op:{callback:null,height:300, width:580, minH:40, minW:50, total:20, max:false, mask:false, resizable:true, drawable:true, maxable:true,minable:true,fresh:true,type:"POST",size:BootstrapDialog.SIZE_NORMAL},
		_current:null,
		_zIndex:42,
		_prev:null,
		getCurrent:function(){
			return this._current;
		},
		getPrev:function(){
			return this._prev;
		},
		getCurrentBody:function(){
			return this._current.getModalBody();
		},
		getPrevBody: function(){
			return this._prev == null ? null : this._prev.getModalBody();
		},
		//打开一个层
		open:function(url, dlgid, title, options) {
			var op = $.extend({},$.pdialog._op, options);
			var $this = this;
			var buttons = op.buttons || [
		            {
		                label: $.regional.alertMsg.butMsg.ok,
		                cssClass: 'btn-primary',
		                action: function(dialog){
		                	var body = dialog.getModalBody();
		                	var form = body.find('form.required-validate');
		                	if( form ){
		                		form.submit();
		                	}
		                	else{
			                	dialog.close();
		                	}
		                }
		            },
		            {
		                label: $.regional.alertMsg.butMsg.cancel,
		                action: function(dialog){
		                    dialog.close();
		                }
		            }
	            ];
	        BootstrapDialog.show({
	            title: title,
	            draggable: true,
	            size: op.size,
	            onshown: function(dialog){
	            	//备份上一个
	            	$this._prev = $this._current;
	            	$this._current = dialog;
	               	var $body = dialog.getModalBody();
	            	$body.data(dlgid);
	            	$body.ajaxUrl({url:url, data:null, callback:options.callback, type:options.type});
	            },
	            onhidden: function(dialog){
	            	//还原上一个
	            	if( $this._current == dialog ){
	            		$this._current = $this._prev;
	            		$this._prev = null;
	            	}
	            },
	            cssClass: 'middel-dialog',
	            buttons: buttons 
	        });
		},

		close:function(dialog) {
			dialog.close();
		},
		closeCurrent:function(){
			this.close($.pdialog._current);
		},
		reload: function(){
			this.getCurrentBody().reload();
		},
		reloadPrev: function(){
			this.getPrevBody() != null && this.getPrevBody().reload();
		},
	};
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