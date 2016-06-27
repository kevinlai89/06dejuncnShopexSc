<?php
class app_openid_renren extends app{
    var $ver = 1.7;
    var $name='人人信任登录';
    var $website = 'http://www.shopex.cn';
    var $author = 'shopex';
    var $reqver = '';
    var $app_id = 'renren';
    //var $help='http://www.shopex.cn/help/ShopEx48/help_shopex48-1279180908-12859.html';
    var $help_tip = "开通条件：请仔细查看文档中所需要的资料，准备完善后提交给人人网，人人网会进行相关审核，通过后即可开通。<a href='http://www.shopex.cn/openid/renren.html' target='_blank'>点击查看</a>";

    function ctl_mapper(){
        return array(
            'shop:member:fav'=>'ctl_authorize:fav',
            'shop:member:ajaxAddFav'=>'ctl_authorize:ajaxAddFav',

        );
    }

    function install(){
        if($this->system->getConf('certificate.id')){
           $obj=$this->system->loadModel("plugins/openid_renren/openid_renren_center_send");
           $return=$obj->edit_app_status('open');
           if($return['result']=='succ'){
              $this->copy_file();
              parent::install();
              return true;
           }else{
             echo "启用失败";
              return false;
           }
        }
        
    }
    

    function openid_login($row){
       
        $this->save_userinfo($row);
       
        if($row['email'] ||$row['action_renren-login_html']!='renren_return'){
             return array('redirect_url'=>$this->system->mkUrl("member","fav"));
        }

    }
    
    function save_userinfo($row){
      
      $row['sex']=($row['sex']=='m')?1:0;
      $this->system->setCookie('UNAME',$row['truename']."_".$row['open_id'],null);

     $sql = 'select mm.uname from sdb_members mm left join sdb_trust_login trust on trust.member_id = mm.member_id WHERE trust.show_uname = "'.$row['uname'].'"';

     $trust_login = $this->db->selectrow($sql);
     
      $this->system->setCookie('LOGIN_UNAME',$trust_login['uname'],null);
      
      $member = &$this->system->loadModel('member/member');
      $defalut_cols=array('address'=>'addr','truename'=>'name');
      $row_array=array('nick_name'=>'昵称','credid'=>'身份证','alipay_account'=>'支付宝帐号','company'=>'公司名','qq'=>'QQ','truename'=>'姓名','sex'=>'性别','mobile'=>'手机号码','address'=>'联系地址','birthday'=>'出生日期','member_id'=>'','member_lv_id'=>'');
      $user_insert=$this->array_key_filter($row,$row_array,$defalut_cols);
      $member->thirdLoginInfo($user_insert);
    }

    function array_key_filter(&$array,$keys,$defalut_cols){
    
        $return = array();

        foreach($keys as $k=>$v){
           foreach($array as $k1=>$v1){
                if($k==$k1){
                    if($v==''){
                      $return[$k] = &$array[$k1];
                    }else{
                      foreach($defalut_cols as $k2=>$v2){
                         if($k1==$k2){
                           $return[$defalut_cols[$k2]] = array($keys[$k]=>&$array[$k1]);
                           unset($k);unset($k1);
                         }else{
                           $return[$k] = array($keys[$k]=>&$array[$k1]);
                         }
                       }
                    }
                }
           }
     }

    return $array = $return;
    }

    function openid_logout(){
        $url='index.php?action_renren-passort_logout.html';
        header('Location: '.$url);
        exit;
    }

    function uninstall(){
        $obj=$this->system->loadModel("plugins/openid_renren/openid_renren_center_send");
        $return = $obj->edit_app_status('close');
        if($return['result']=='succ'){
            if(file_exists($receiver_source=BASE_DIR.'/home/xd_receiver.html')){
                    @unlink($receiver_source);
               }
            parent::uninstall();
            return true;
        }else{
            echo "卸载失败";
            return false;
        }
    }

    
    function output_modifiers(){
        return array(
            'shop:product:index'=>'product_modifiers:product_index',
            'shop:member:*'=>'product_modifiers:change_name',
        );
    }
    
    function setting_save(){
      $setting=$_POST['setting'];
      if($setting){
          $app_id = $setting['app.openid_renren.appid'];
          $api_key = $setting['app.openid_renren.apikey'];
          $api_secret = $setting['app.openid_renren.appsecret'];
          $app_title = $setting['app.openid_renren.loginname'];
          $obj=$this->system->loadModel("plugins/openid_renren/openid_renren_center_send");
          $return = $obj->save_api_key($api_key,$api_secret,$app_title,$callback_url='',$app_id);
          if($return['result']=='succ'){
              foreach ($setting as $k=>$v){
                 $this->system->setConf($k,$v);
              }
          }else{
              echo "保存失败";
              exit;
          }
      }    
    }


     function mem_center_menu(&$menu){
      
         if(($_COOKIE['LOGIN_TYPE']=='renren')&&($_COOKIE['LANG']=='123')){
           $menu[7]['items'][] = array('label'=>__('人人授权应用设置'),'link'=>'fav');
         }
     }


    function enable(){
        $obj=$this->system->loadModel("plugins/openid_renren/openid_renren_center_send");
        $return = $obj->edit_app_status('open');
        if($return['result']=='succ'){
           return true;
        }else{
           echo "启用失败";
           exit();
        }
    }

    function disable(){
        $obj=$this->system->loadModel("plugins/openid_renren/openid_renren_center_send");
        $return = $obj->edit_app_status('close');
        if($return['result']=='succ'){
           return true;
        }else{
           echo "禁用失败";
           exit();
        }
    }

    function copy_file(){
        if(file_exists($receiver_source=PLUGIN_DIR.'/app/openid_renren/xd_receiver.html')){
           $tmp_file = tempnam(BASE_DIR.'/','gimg_');
           $xd_receiver=copy($receiver_source,$tmp_file);
           if($xd_receiver!=false){
              rename($tmp_file,BASE_DIR.'/home/xd_receiver.html');
              chmod(BASE_DIR.'/home/xd_receiver.html', 0644);
           }
         @unlink(BASE_DIR.'/'.$tmp_file);
       }
       if(file_exists($bak_file = PLUGIN_DIR.'/app/openid_renren/back_file')&&!file_exists(BASE_DIR.'/statics/accountlogos/trustlogo6.gif')){
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