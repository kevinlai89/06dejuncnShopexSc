<?php
if(!class_exists('shopObject')){
    require(CORE_DIR.'/include/shopObject.php');
}

require_once('mdl.shopex_csm.php');
class openid_taobao_lismember extends shopObject{

    function openid_taobao_lismember(){
        parent::shopObject();
        $this->system = &$GLOBALS['system'];
    }

     function get_times(){
         $csm_data=new mdl_shopex_csm();         

        $sign = $_GET['sign'];
        if($_GET['cb']&&$_GET['call_back']){
            $cb = $_GET['cb'];
            $call_back = $_GET['call_back'];
            unset($_GET['cb']);
            unset($_GET['call_back']);
        }

        unset($_GET['passport-other_login_verify_html']);
        unset($_GET['passport-1-other_login_verify_html']);
        unset($_GET['sign']);
        $make_sign = $this->get_ce_sign($_GET,$this->system->getConf('certificate.token'));
        if($make_sign!=$sign){
            echo "sign is error";
            exit;
        }
        $account = $this->system->loadModel('member/account');
        $result_m = $account->createotherlogin($_GET);
        if($result_m['redirect_url']){
            echo "<script>window.location=decodeURIComponent('".$result_m['redirect_url']."');</script>";
            
        }else{
            if($_GET['open_type'] !='local'){
               
              if($this->db->selectrow("select csm_time from sdb_openid_taobao_login where csm_type='".$_GET['open_type']."'")){
                  

                  $this->db->exec("update  sdb_openid_taobao_login set csm_time='".time()."' where csm_type='".$_GET['open_type']."'");

              }else{
               
              $this->db->exec("insert into sdb_openid_taobao_login  values('".$_GET['open_type']."',0,'".time() ."')");
               
              //$csm_data->index();
          
              }

          }

            if($cb==1){
                echo "<script>window.location=decodeURIComponent('".$call_back."')</script>";
                exit;
            }else{
                echo "<script>(location.hash)?window.location=decodeURIComponent(location.hash).substr(1):location.reload();</script>";
            }
            //echo "<script>(location.hash)?window.location=decodeURIComponent(location.hash).substr(1):location.reload();</script>";
        }
	
       
    }

     function get_ce_sign($params,$token){
        $arg="";
        ksort($params);
        reset($params);
        while (list ($key, $val) = each ($params)) {
            $arg.=$key."=".urlencode($val)."&";
        }
        $sign = md5(substr($arg,0,count($arg)-2).$token);//去掉最后一个问号
        return $sign;
    }
}
?>