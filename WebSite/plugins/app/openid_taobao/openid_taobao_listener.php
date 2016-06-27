<?php
if(!class_exists('shopObject')){
    require(CORE_DIR.'/include/shopObject.php');
}
require_once('mdl.shopex_csm.php');

class openid_taobao_listener {

    function openid_taobao_listener(){
       // parent::shopObject();
        $this->system = &$GLOBALS['system'];
         
    }
     //会员登陆
     /*
      function get_logmember($event_type,$log_member){
          $csm_data=new admin_csm_ctl();
          $member_refer=$this->db->selectrow("select member_refer from sdb_members where member_id='".$log_member['member_id']."'");
          if($member_refer !='local'){
              if($this->db->selectrow("select csm_time from sdb_shopex_csm_login where csm_type='".$member_refer['member_refer']."'")){

                  $this->db->exec("update  sdb_shopex_csm_login set csm_time='".time()."' where csm_type='".$member_refer['member_refer']."'");

              }else{
               
              $this->db->exec("insert into sdb_shopex_csm_login  values('".$member_refer['member_refer']."',0,'".time() ."')");
              $csm_data->index();
              }

          }
      }
      */

      function get_payed($event_type,$log_member){
          $csm_data=new mdl_shopex_csm();
          $csm_data->gettypebyorderid($log_member['order_id']);
          
       


      }


    




  
  }

?>