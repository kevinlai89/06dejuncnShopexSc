<?php
class app_tencent_share extends app{
    var $ver = 1.1;
    var $name='腾讯微博一键分享';
    var $website = 'http://www.shopex.cn';
    var $author = 'shopex';
    var $reqver = '';
    var $app_id = 'tencent_share';
    var $help_tip = '注意事项：  您如果有一键分享API Key和Secret，请填写，没有<a href="http://open.t.qq.com/" target="_blank">请申请</a>。如不填写，则默认使用shopex的一键分享API Key和Secret。详细请参考<a href="http://open.t.qq.com/resource.php?i=0,2" target="_blank">申请开发者身份流程</a>';

    function install(){
        if($this->system->getConf('certificate.id')){
            if($this->system->getConf('app.tencent_share.apikey') == ''){
                 $this->system->setConf('app.tencent_share.apikey','36f6e95453c7472b879fdb1fbe765385');
                 $this->system->setConf('app.tencent_share.appsecret','b06605154f048b25b01abd224bb8fd7d');
            }
            $this->copy_file();
            parent::install();
            return true;
        }
        
    }
    

    function uninstall(){
            parent::uninstall();
            return true;
    }

    
    function output_modifiers(){
        return array(
            'shop:product:index'=>'product_modifiers:product_index',
        );
    }
    
    function setting_save(){
      $setting=$_POST['setting'];
      if($setting){
              foreach ($setting as $k=>$v){
                 $this->system->setConf($k,$v);
              }
      }
    }

    function enable(){
           return true;
    }

    function disable(){
           return true;
    }

    function copy_file(){
       if(file_exists($bak_file = PLUGIN_DIR.'/app/tencent_share/back_file')&&!file_exists(BASE_DIR.'/statics/accountlogos/trustlogo6.gif')){
           $wsf = PLUGIN_DIR.'/widgets/member/bar.html';
           @copy($bak_file.'/bar.html',$wsf);

           $loginsf = CORE_DIR.'/shop/view/passport/index/login.html';
           @copy($bak_file.'/login.html',$loginsf);

           $receiver_source6 = $bak_file.'/trustlogo6.gif';
           $tmp_file6 = tempnam(BASE_DIR.'/','image_');
           $xd_receiver6=copy($receiver_source6,$tmp_file6);
           if($xd_receiver6!=false){
                rename($tmp_file6,BASE_DIR.'/statics/accountlogos/trustlogo6.gif');
                chmod(BASE_DIR.'/statics/accountlogos/trustlogo6.gif', 0644);
           }
           @unlink(BASE_DIR.'/'.$tmp_file6);

           $receiver_source_small6 = $bak_file.'/trustlogo6_small.gif';
           $tmp_file_small6 = tempnam(BASE_DIR.'/','image_');
           $xd_receiver_small6=copy($receiver_source_small6,$tmp_file_small6);
           if($xd_receiver_small6!=false){
                rename($tmp_file_small6,BASE_DIR.'/statics/accountlogos/trustlogo6_small.gif');
                chmod(BASE_DIR.'/statics/accountlogos/trustlogo6_small.gif', 0644);
           }
           @unlink(BASE_DIR.'/'.$tmp_file_small6);

           $gsf = CORE_DIR.'/shop/view/product/goodspics.html';
           @copy($bak_file.'/goodspics.html',$gsf);
       }
    }

}