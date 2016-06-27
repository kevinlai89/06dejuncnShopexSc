<?php
require_once(CORE_INCLUDE_DIR.'/adminPage.php');
class admin_sales_ctl extends adminPage{

    function admin_sales_ctl(){
        parent::adminPage();
        $appmgr = $this->system->loadModel('system/appmgr');
        $tb_api = &$appmgr->load('tb_sales_download');
        $this->tb  = &$tb_api;
    }



    
    function dotaobaorate(){
        if(!$this->system->getConf("app.tb_sales_download.nick")){
            $center = $this->system->loadModel('plugins/tb_sales_download/center_send');
            if($nick = $center->get_tb_nick()){
                $this->system->setConf("app.tb_sales_download.nick",$nick['result_msg'],true);
                $this->pagedata['css_url'] = str_replace("\\","/",$this->system->base_url().substr(dirname(__FILE__),strpos(dirname(__FILE__),'plugins')));
                $this->display("view/sales/dotaobao_rate.html");
            }else{
                $this->display("view/set_nick.html");
            }
        }else{
            $this->pagedata['css_url'] = str_replace("\\","/",$this->system->base_url().substr(dirname(__FILE__),strpos(dirname(__FILE__),'plugins')));
            $this->display("view/sales/dotaobao_rate.html");
        }
    }

    function index(){
        if(isset($_GET['session']) && $_GET['session']){
            $this->save_sess($_GET);
        }
        echo "<script>window.location.href='".$this->system->base_url()."shopadmin/index.php#ctl=plugins/sales_ctl&act=dotaobaorate'</script>";
    }

    function traderate_syn($do_output=false,$page=1){
        $this->system->call("traderate_info_get",$do_output,$page,$this->tb);   
    }


    function sess_timeout(){
        $url = $this->system->base_url().'shopadmin/index.php?ctl=plugins/sales_ctl';
        $this->pagedata['tblogin_url'] =  $this->tb->getTbloginurl($url);
        $this->display("view/sess_timeout.html");
    }

    function save_sess($params){
        $center = $this->system->loadModel('plugins/tb_sales_download/center_send');

        if($center_msg =$center->get_tb_nick()){
            $nick = $center_msg['result_msg'];
        };
        if($params['taobao_user_nick']!=$nick){
            echo '<script>alert("您登录的淘宝帐号和此功能对应的应用配置中的淘宝帐号不一致，请使用此功能相关应用中配置的淘宝帐号进行登录。");</script>';
        }else{
            $status = $this->system->loadModel("system/status");
            $status->set('tb_sess',$params['session']);
            $mess= $center->save_sess($params['session']);
        }
    }

    function makeSign($aParams){
        $ret = '';
        ksort($aParams);
        reset($aParams);
        if(isset($aParams['app_secret']) && $aParams['app_secret']){
            $app_secret = $aParams['app_secret'];
            unset($aParams['app_secret']);
        }
        $ret = $app_secret;
        foreach($aParams as $key=>$value){
            if($key != "sign" && $value)
                $ret .= $key.$value;
        }
        return strtoupper(md5($ret.$app_secret));
    }

    function save_tb_nick(){
        $this->tb->setting_save();
        $this->index();
    }
    
}

















?>
