<?php
class ctl_receive_matrix{
   function ctl_receive_matrix(){
       $this->system = &$GLOBALS['system'];
   }
   function get_apply_node_bind_result(){
      $data = $_POST;
      unset($data['ac']);
      $connect_bind = &$this->system->loadModel('system/connect_bind');
      $connect_bind->update_bind($data);
   }

    function update_callback_status(){
		error_log(var_export($_POST,1),3,'c:/update_callback_status.txt');
      //$data = json_decode($_POST['data'],true);
      $sign_data['date'] = $_POST['date'];
      $sign_data['res'] = $_POST['res'];
      $sign_data['rsp'] = $_POST['rsp'];
      $sign_data['msg_id'] = $_POST['msg_id'];
      $sign_data['data'] = $_POST['data'];
      $sign = $this->get_matrix_sign($sign_data,$this->system->getConf('certificate.token'));
      if($sign == $_POST['sign']){
		  $type = $_GET['type'];
          $matrix_synclog = &$this->system->loadModel('system/matrix_synclog');
          $matrix_synclog->update_synclog($_POST,$type);
	  }
	}
}

    function get_matrix_sign($params,$token){

        return strtoupper(md5(strtoupper(md5($this->assemble($params))).$token));
    }

    function assemble($params) 
    {

        if(!is_array($params))  return null;
        ksort($params);

        $sign = '';
        foreach($params AS $key=>$val){
            $sign .= $key . (is_array($val) ? $this->assemble($val) : $val);
        }

        return $sign;
    }

?>