<?php
class app_openid_taobao extends app{
    var $ver = 1.8;
    var $name='淘宝信任登录';
    var $website = 'http://www.shopex.cn';
    var $author = 'shopex';
    var $app_id = 'taobao';
   var $help_tip = "<a href='http://api.renren.com' target='_blank'>淘宝免登申请方法</a>";

    function install(){
      if($this->system->getConf('certificate.id')){
         $obj=$this->system->loadModel("plugins/openid_taobao/openid_taobao_center_send");
         $obj->save_api_key();
           $return = $obj->edit_app_status("open");
         if($return['result']=='succ'){
           parent::install();
           return true;
         }else{
              echo "启用失败";
           return false;
         }
      }    
    }

     function ctl_mapper(){
        return array(
          'shop:passport:other_login_verify' => 'lismember:get_times',
       );
    }

    function listener(){
        return array(
           //'member/account:trustlogin'=>'listener:get_logmember',
           'trading/order:payed'=>'listener:get_payed',
        );
    }

    function openid_login($row){

       $row['addr'] = $this->get_addr($row);
       
       $this->save_userinfo($row);
    }
   
   function get_addr($addr){
   
       $obj=$this->system->loadModel("plugins/openid_taobao/openid_taobao_center_send");
       $return = $obj->get_taobao_addr($addr['open_id']);

       if($return['result']=='succ'){
             return $return['result_msg'];
      }
      
   }
    
    function save_userinfo($row){
     
     $addr = &$this->system->loadModel("plugins/openid_taobao/openid_taobao_model");

    $get_addr=json_decode($row['addr'],true);
    
     if(count($get_addr)!=0){
        if(is_array($get_addr)){
          $addr->save_tb_addr($row['member_id'],$get_addr);
        }
     }
    unset($row['addr']);

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
    function uninstall(){
      
      $obj=$this->system->loadModel("plugins/openid_taobao/openid_taobao_center_send");
        $return = $obj->edit_app_status("close");
      if($return['result']=='succ'){
         parent::uninstall();
           return true;
      }else{
            echo "卸载失败";
         return false;
      }
    }

   function enable(){
      $obj=$this->system->loadModel("plugins/openid_taobao/openid_taobao_center_send");
        $return = $obj->edit_app_status("open");
        if($return['result']=='succ'){
           return true;
        }else{
           echo "启用失败";
          return false;
      }
    }

   function disable(){
      $obj=$this->system->loadModel("plugins/openid_taobao/openid_taobao_center_send");
        $return = $obj->edit_app_status("close");
        if($return['result']=='succ'){
           return true;
        }else{
           echo "禁用失败";
         return false;
      }
    }
    
  
}
