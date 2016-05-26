(function($){
	var adminConsole = {
		open: function(url, options){ 
			$.ajax({
				type: options.method || 'GET',
				url: url,
				data: options.data,
				cache: false,
				success: function(response){
					var json = DWZ.jsonEval(response);
					
					if (json[DWZ.keys.statusCode]==DWZ.statusCode.error){
						if (json[DWZ.keys.message]) alertMsg.error(json[DWZ.keys.message]);
					} else {
						$this.html(response).initUI();
						if ($.isFunction(op.callback)) op.callback(response);
					}
					
					if (json[DWZ.keys.statusCode]==DWZ.statusCode.timeout){
						if ($.pdialog) $.pdialog.checkTimeout();
						if (navTab) navTab.checkTimeout();
	
						alertMsg.error(json[DWZ.keys.message] || DWZ.msg("sessionTimout"), {okCall:function(){
							DWZ.loadLogin();
						}});
					} 
					
				},
				error: DWZ.ajaxError,
				statusCode: {
					503: function(xhr, ajaxOptions, thrownError) {
						alert(DWZ.msg("statusCode_503") || thrownError);
					}
				}
			});
		},
		initUI: function(){
			// navTab
			$("a[target=navTab]").each(function(){
				$(this).click(function(event){
					var $this = $(this);
					var title = $this.attr("title") || $this.text();
					var tabid = $this.attr("rel") || "_blank";
					var fresh = eval($this.attr("fresh") || "true");
					var external = eval($this.attr("external") || "false");
					var method = $this.attr("method") || "GET";
					var url = unescape($this.attr("href"));
					adminConsole.open(url,{title:title, fresh:fresh, external:external, method:method});

					event.preventDefault();
				});
			});	
		}	
	};

})(jQuery);