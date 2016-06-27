<?php
include_once("mail.inc.php");
if(isset($_POST['q1']) && strlen(trim($_POST['q1']))>0){
if(isset($_POST['q2']) && strlen(trim($_POST['q2']))>0){
if(isset($_POST['q3']) && strlen(trim($_POST['q3']))>0){
if(isset($_POST['q4']) && strlen(trim($_POST['q5']))>0){ 
if(isset($_POST['q4']) && strlen(trim($_POST['q6']))>0){ 
if(isset($_POST['q4']) && strlen(trim($_POST['q7']))>0){ 

$smtp = new smtp("smtp.sina.com",25,true,"sicheng890@sina.com","sicheng8","sicheng890@sina.com");//发件人信箱信息
$smtp->debug = false;//是否显示发送的调试信息 FALSE or TRUE
$mailto="service@kbw99.com,marketing@ec-sourcing.com,".$_POST["q8"]."";//收件人信箱
$mailsubject="【推荐客户】:".$_POST["q5"]."";//邮件标题
$mailfrom="发件人";//发件人
$mailbody=$mailbody."亲爱的管理员，以下是注册客户的申请表单：<br>";//邮件内容
$mailbody="客户名称:".$_POST["q1"]."<br>";
$mailbody=$mailbody."客户联系人:".$_POST["q2"]."<br>";
$mailbody=$mailbody."客户电话:".$_POST["q3"]."<br>";
$mailbody=$mailbody."客户邮箱:".$_POST["q4"]."<br>";
$mailbody=$mailbody."项目内容:".$_POST["q5"]."<br>";
$mailbody=$mailbody."推荐人姓名:".$_POST["q6"]."<br>";
$mailbody=$mailbody."推荐人电话:".$_POST["q7"]."<br>";
$mailbody=$mailbody."推荐人接收账单邮箱：".$_POST["q8"]."<br><br><br>思诚资源<br>www.ec-sourcing.com<br>服务热线：0769-81582258";
//其他的表单项目以此类推
$mailtype 		= 	"HTML";//邮件格式（HTML/TXT）,TXT为文本邮件
$mailsubject 	= 	'=?UTF-8?B?'.base64_encode($mailsubject).'?=';//邮件主题
$mailfrom  	= 	'=?UTF-8?B?'.base64_encode($mailfrom).'?=';//发件人
$smtp->sendmail($mailto, $mailfrom, $mailsubject, $mailbody, $mailtype);
echo "<script language=\"JavaScript\">alert(\"发送成功！.\");</script>"; exit();
}
else{echo "<script language=\"JavaScript\">alert(\"请填推荐人电话！.\");</script>"; exit();}
}
else{echo "<script language=\"JavaScript\">alert(\"请填写推荐人！.\");</script>"; exit();}
}
else{echo "<script language=\"JavaScript\">alert(\"请填写项目内容！.\");</script>"; exit();}
}
else{echo "<script language=\"JavaScript\">alert(\"请填写客户电话！.\");</script>"; exit();}
}
else{echo "<script language=\"JavaScript\">alert(\"请填写客户联系人！.\");</script>"; exit();}
}
else{echo "<script language=\"JavaScript\">alert(\"请填写推荐客户名称全称！.\");</script>"; exit();}//判表单是否为空
?>