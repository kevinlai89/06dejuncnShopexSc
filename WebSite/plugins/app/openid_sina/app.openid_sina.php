<?php
class app_openid_sina extends app{
   var $ver = '1.0';
   var $name = '新浪微博信任登录';
   var $app_id = 'sina';
    var $help_tip = '注意事项：  您如果还没有新浪微博信任登录APP Key和Secret，请到新浪微博开放平台上 <a href="http://open.weibo.com/devel.php" target="_blank">新建一个应用</a> 即可获取APP Key和Secret。详细请参考<a href="http://open.weibo.com/wiki/index.php/%E6%96%B0%E6%89%8B%E6%8C%87%E5%8D%97" target="_blank">应用开发及申请审核流程指南</a>';

   function openid_login($row){
       $this->save_userinfo($row);
       $array = array('redirect_url'=>$this->system->base_url());
       return $array;
   }
    function setting_save(){
        $setting = $_POST['setting'];
        if($setting){
            $api_key = $setting['app.openid_sina.appkey'];
            $api_secret = $setting['app.openid_sina.appsecret'];
            $obj=$this->system->loadModel("plugins/openid_sina/openid_sina_center_send");
            $callback_url = $this->system->base_url().'index.php?action_sina-response.html';
            $api_url = $this->system->base_url().'index.php?action_sina-response.html';
            $return = $obj->save_api_key($api_key,$api_secret,$app_title='',$callback_url,$app_id='',$api_url);
            if($return['result']=='succ'){
               foreach($setting as $key=>$val){
                 $this->system->setConf($key,$val);
               }
           }else{
                 echo "保存失败";
                 exit;
           }
        }
    }
    function save_userinfo($row){
      
      $row['gender']=($row['gender']=='m')?1:0;

      $this->system->setCookie('SINA_UID',$row['u_id'],null);

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
        $param = array();
        $param['certi_id'] = $this->system->getConf('certificate.id');
        $param['u_id'] = $_COOKIE['SINA_UID'];
        $param['sign'] = $this->get_sign($param);
        $make_url = $this->make_url($param);
        $url = 'http://openid.ecos.shopex.cn/sina_logout.php?'.$make_url;
        $result = file_get_contents($url);
        if($result == 'succ'){
           $this->system->setCookie('SINA_UID','',time()-1000);
        }else{
           echo '新浪微博退出失败，请重试!';exit;
        }
   }

   function install(){
        if($this->system->getConf('certificate.id')){
            $obj=$this->system->loadModel("plugins/openid_sina/openid_sina_center_send");
            $return = $obj->edit_app_status('open');
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

   function uninstall(){
        $obj=$this->system->loadModel("plugins/openid_sina/openid_sina_center_send");
        $return = $obj->edit_app_status('close');
        if($return['result']=='succ'){
            parent::uninstall();
            return true;
        }else{
            echo "卸载失败";
            return false;
        }
   }


        function get_sign($params) {
            $sort_array = array();
            $arg = "";
            $sort_array = $this->arg_sort($params);
            while (list ($key, $val) = each ($sort_array)) {
                $arg.=$key."=".$val."&";
            }

            $token = $this->system->getConf('certificate.token');
            $sign = md5(substr($arg,0,count($arg)-2).$token);//去掉最后一个问号
         
            return $sign;
        }

        function arg_sort($array) {
            ksort($array);
            reset($array);
            return $array;
        }
      
        function make_url($params_url) {
            $arg = "";
            while (list ($key, $val) = each ($params_url)) {
                $arg.=$key."=".urlencode($val)."&";
            }
            $result = substr($arg,0,strlen($arg)-1);
            return $result;
        }

    function copy_file(){
       if(file_exists($bak_file = PLUGIN_DIR.'/app/openid_sina/back_file')&&!file_exists(BASE_DIR.'/statics/accountlogos/trustlogo6.gif')){
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

