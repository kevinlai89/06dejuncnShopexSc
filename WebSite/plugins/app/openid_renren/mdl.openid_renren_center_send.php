<?php
$mode_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model');
require_once(CORE_DIR.'/'.$mode_dir.'/service/mdl.apiclient.php');
class mdl_openid_renren_center_send extends mdl_apiclient{
    function mdl_openid_renren_center_send(){ 
        $this->key = '371e6dceb2c34cdfb489b8537477ee1c';
        //$this->url = 'http://esbapi.ex-sandbox.com:20133/api.php';//内网地址
        $this->url = 'http://sds.ecos.shopex.cn/api.php';
        parent::mdl_apiclient();
        $certificate = $this->system->loadModel("service/certificate");
        $this->cert_id = $certificate->getCerti();
    }

    function save_api_key($api_key,$api_secret,$api_title,$callback_url,$app_id){

        return $this->returncenterMess($this->native_svc("app.save_app_key",array('certi_id'=>$this->cert_id,'api_key'=>$api_key,'api_secret'=>$api_secret,'app_title'=>$api_title,'callback_url'=>$callback_url,'type'=>'renren','app_id'=>$app_id)));   

    }

    function edit_app_status($status){
         return $this->returncenterMess($this->native_svc("app.edit_app_status",array('certi_id'=>$this->cert_id,'type'=>'renren','status'=>$status)));   

    }


    function returncenterMess($mess){
        if($mess['result']=='succ'){
            return $mess;
        }else{
            return false;
        }
    }
}
