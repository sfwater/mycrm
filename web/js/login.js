function setfocus()
{
if (document.login.username.value=="")
	document.login.username.focus();
else
	document.login.username.select();
}
function checkform()
{
	if(document.login.account.value=="")
	{
		alert("请输入用户名！");
		document.login.account.focus();
		return false;
	}
	if(document.login.password.value == "")
	{
		alert("请输入密码！");
		document.login.password.focus();
		return false;
	}
	if (document.login.verify.value==""){
       alert ("请输入您的验证码！");
       document.login.verify.focus();
       return(false);
    }
}

function checkbrowser() 
{
  var app=navigator.appname;
  var verstr=navigator.appversion;
  if (app.indexof('netscape') != -1) {
    alert(" 你使用的是netscape浏览器，可能会导致无法使用后台的部分功能。建议您使用 ie6.0 或以上版本。");
  } 
  else if (app.indexof('microsoft') != -1) {
    if (verstr.indexof("msie 3.0")!=-1 || verstr.indexof("msie 4.0") != -1 || verstr.indexof("msie 5.0") != -1 || verstr.indexof("msie 5.1") != -1)
      alert("您的浏览器版本太低，可能会导致无法使用后台的部分功能。建议您使用 ie6.0 或以上版本。");
  }
}

function fleshVerify(src){ 
	//重载验证码
	$('#verifyImg').attr('src', src+'?r='+Math.random());
}