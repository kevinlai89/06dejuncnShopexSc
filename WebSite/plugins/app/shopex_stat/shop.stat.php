<?php
$mode_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'include_v5':'include');
require_once(CORE_DIR.'/'.$mode_dir.'/shopPage.php');
require_once('mdl.shopex_stat.php');
class shop_stat extends shopPage{
    
     function shop_stat(){
         parent::shopPage();
    }
   function index(){
          $certificate_state = new mdl_shopex_stat();
           $certificate = $this->system->loadModel("service/certificate");
          $resflash=$this->system->base_url().'shopadmin/index.php#ctl=plugins/stat_ctl&act=index';
          $certi_id = $certificate->getCerti();
        $certificate_state->state_update($certi_id);
        $html=<<<EOF
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>生意经开通成功</title>
<style type="text/css">
body{font-family:Verdana, Geneva, sans-serif;padding:10px;}
.success { width:100%;  background:#E6EFC2; border:1px solid #C6D880; margin-bottom:14px;}
.success-info{ width:100%; padding:0 0 0 6px; font-size:14px; color:#264409; font-weight:bold;line-height:23px;}
.success-info img{ margin:2px 6px 0;}
</style>
</head>

<body>
<div class="success">
 <div class="success-info">
    <table>
        <tr>
            <td valign="top">
                <!--<img src="success-icon.gif" width="22" height="23" />-->
             </td>
            <td valign="top">
                恭喜你，开通成功！
            </td>
        </tr>
    </table>
 </div>
 <div style="text-align:center;font-size:12px;padding:10px" > <a id="refresh_url" href="$resflash" target="_top">立即刷新状态&raquo;</a></div>
</div>
</body>
</html>
EOF;
echo $html;
       
   }


}
?>