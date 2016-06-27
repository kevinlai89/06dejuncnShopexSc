<?php
    $mode_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model');
    require_once(CORE_DIR.'/'.$mode_dir.'/service/mdl.apiclient.php');

    class mdl_shopex_stat extends mdl_apiclient{
        function mdl_shopex_stat(){
             parent::mdl_apiclient();
            return True;
        }

        function get_certi_token(){
            return True;
        }
          function get_value($certi_id){
             $status_key='ISOPEN'.$certi_id;
        if($row =$this->db->selectrow("select * from sdb_status where status_key='".$status_key."' and date_affect='0000-00-00'")){
            return true;
        }else{
            return false;
        }
    }
    function add_status($certi_id){
             $status_key='ISOPEN'.$certi_id;
       $sql = "INSERT INTO sdb_status(status_key,date_affect,status_value,last_update) VALUES ( '".$status_key."','0000-00-00','0',".time()." )";
         $this->db->exec($sql);
        return true;
   }
    function get($certi_id){
             $status_key='ISOPEN'.$certi_id;
        if($row =  $this->db->selectrow("select * from sdb_status where status_key='".$status_key."'")){
            return $row['status_value'];
        }else{
            return false;
        }
    }
     function state_update($certi_id){
             $status_key='ISOPEN'.$certi_id;
             
        $sql = "update sdb_status set status_value='1' where status_key='".$status_key."'" ;
        $this->db->exec($sql);
    }
     function state_uninstall($certi_id){
             $status_key='ISOPEN'.$certi_id;
             
        $sql = "update sdb_status set status_value='0' where status_key='".$status_key."'" ;
        $this->db->exec($sql);
    }
   
    }



?>