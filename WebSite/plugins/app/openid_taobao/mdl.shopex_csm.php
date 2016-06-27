<?php
    $mode_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model');
    require_once(CORE_DIR.'/'.$mode_dir.'/service/mdl.apiclient.php');

    class mdl_shopex_csm extends mdl_apiclient{
        function mdl_shopex_stat(){
             parent::mdl_apiclient();
            return True;
        }
        function get_listbytype($member_refer){       
        $row =$this->db->select("select member_id from sdb_members where member_refer='".$member_refer."'");

       foreach($row as $k=>$v){
          $arr[]=$v['member_id'];
       }
       $arr=implode(',',$arr);
       if($arr){
         $row_order =$this->db->selectrow("select count(*) as __stat_ordercount ,sum(final_amount) as __stat_orderamount from sdb_orders where member_id in (".$arr.") and pay_status='1'");

         if($csm_time=$this->db->selectrow("select csm_time from sdb_openid_taobao_login where csm_type='".$member_refer['member_refer']."'")){
             $row_order['__stat_lastlogin']=$csm_time['csm_time'];

             }else{
                 $row_order['__stat_lastlogin']=time();

             }
            return $row_order;      
       }else{
           return false;
       }
      
    }


    function get_taobao(){       
        $row =$this->db->select("select member_id from sdb_members where member_refer='taobao'");

       foreach($row as $k=>$v){
          $arr[]=$v['member_id'];
       }
       $arr=implode(',',$arr);
       if($arr){
         $row_order =$this->db->selectrow("select count(*) as __stat_ordercount ,sum(final_amount) as __stat_orderamount from sdb_orders where member_id in (".$arr.") and pay_status='1'");

         if($csm_time=$this->db->selectrow("select csm_time from sdb_openid_taobao_login where csm_type='".$member_refer['member_refer']."'")){
             $row_order['__stat_lastlogin']=$csm_time['csm_time'];

             }else{
                 $row_order['__stat_lastlogin']=time();

             }
            return $row_order;      
       }else{
           return false;
       }
      
    }
  function get_alipay(){       
    $row =$this->db->select("select member_id from sdb_members where member_refer='alipay'");

       foreach($row as $k=>$v){
          $arr[]=$v['member_id'];
       }
       $arr=implode(',',$arr);
      if($arr){
         $row_order =$this->db->selectrow("select count(*) as __stat_ordercount ,sum(final_amount) as __stat_orderamount from sdb_orders where member_id in (".$arr.") and pay_status='1'");

         if($csm_time=$this->db->selectrow("select csm_time from sdb_openid_taobao_login where csm_type='".$member_refer['member_refer']."'")){
             $row_order['__stat_logintime']=$csm_time['csm_time'];

             }else{
                 $row_order['__stat_logintime']=time();

             }
            return $row_order;      
       }else{
           return false;
       }
      
    }
 function get_renren(){       
    $row =$this->db->select("select member_id from sdb_members where member_refer='renren'");

       foreach($row as $k=>$v){
          $arr[]=$v['member_id'];
       }
       $arr=implode(',',$arr);
    if($arr){
         $row_order =$this->db->selectrow("select count(*) as __stat_ordercount ,sum(final_amount) as __stat_orderamount from sdb_orders where member_id in (".$arr.") and pay_status='1'");

         if($csm_time=$this->db->selectrow("select csm_time from sdb_openid_taobao_login where csm_type='".$member_refer['member_refer']."'")){
             $row_order['__stat_logintime']=$csm_time['csm_time'];

             }else{
                 $row_order['__stat_logintime']=time();

             }
            return $row_order;      
       }else{
           return false;
       }
    }
 function get_tenpay(){       
    $row =$this->db->select("select member_id from sdb_members where member_refer='tenpay'");

       foreach($row as $k=>$v){
          $arr[]=$v['member_id'];
       }
       $arr=implode(',',$arr);
     if($arr){
         $row_order =$this->db->selectrow("select count(*) as __stat_ordercount ,sum(final_amount) as __stat_orderamount from sdb_orders where member_id in (".$arr.") and pay_status='1'");

         if($csm_time=$this->db->selectrow("select csm_time from sdb_openid_taobao_login where csm_type='".$member_refer['member_refer']."'")){
             $row_order['__stat_logintime']=$csm_time['csm_time'];

             }else{
                 $row_order['__stat_logintime']=time();

             }
            return $row_order;      
       }else{
           return false;
       }
       
    }
  function get_139(){       
    $row =$this->db->select("select member_id from sdb_members where member_refer='139'");

       foreach($row as $k=>$v){
          $arr[]=$v['member_id'];
       }
       $arr=implode(',',$arr);
       if($arr){
         $row_order =$this->db->selectrow("select count(*) as __stat_ordercount ,sum(final_amount) as __stat_orderamount from sdb_orders where member_id in (".$arr.") and pay_status='1'");

         if($csm_time=$this->db->selectrow("select csm_time from sdb_openid_taobao_login where csm_type='".$member_refer['member_refer']."'")){
             $row_order['__stat_logintime']=$csm_time['csm_time'];

             }else{
                 $row_order['__stat_logintime']=time();

             }
            return $row_order;      
       }else{
           return false;
       }
       
    }

   function get_serial(){
        if($get_serial=$this->db->selectrow("select csm_serial from sdb_openid_taobao_login where csm_type='csm'")){
            return $get_serial;

       }else{
           $this->db->exec("insert into sdb_openid_taobao_login values('csm',0,".time().")");
           return 0;

       }



   }
    function up_serial($csm_serial){
        $this->db->exec("update  sdb_openid_taobao_login set csm_serial='".$csm_serial."' where csm_type='csm'");


   }
   function get_refer($orderid){
       $member_id=$this->db->selectrow("select member_id from sdb_orders where order_id='".$orderid."'");
     $member_refer=$this->db->selectrow("select member_refer from sdb_members where member_id='".$member_id['member_id']."'");
     return $member_refer;

   }


    function index(){
        $post_t = $this->system->loadModel("utility/http_client");  
        $certificate = $this->system->loadModel("service/certificate");
      if(!$certificate->getToken()){
          return false;
       }
        $api_data=array();
        $certi_id = $certificate->getCerti();
        $token = $certificate->getToken();
        $taobao_data=$this->get_taobao();
        $alipay_data=$this->get_alipay();
        $renren_data=$this->get_renren();
        $tenpay_data=$this->get_tenpay();
        $data_139=$this->get_139();
        $api_d['alipay']=$alipay_data;
        $api_d['renren']=$renren_data;
        $api_d['tenpay']=$tenpay_data;
        $api_d['139']=$data_139;
        $api_d['taobao']=$taobao_data;
        foreach($api_d as $k=>$v){
         $api_data['__stat_channel']='openid_'.$k;
         if($v['__stat_ordercount']){
         $api_data['__stat_ordercount']=$v['__stat_ordercount'];
         }else{
             $api_data['__stat_ordercount']=0;

         }
         if($v['__stat_orderamount']){
         $api_data['__stat_orderamount']=$v['__stat_orderamount'];
         }else{
             $api_data['__stat_orderamount']=0;

         }
         if($v['__stat_lastlogin']){
         $api_data['__stat_logintime']=$v['__stat_lastlogin'];
         }else{
             $api_data['__stat_logintime']=time();

         }
         $api_data['pid']='sso';
         $api_data['licid']=$certi_id;
        $api_data['version']='1.0';
        $test_serial=$this->get_serial();
        if($test_serial){
            $api_data['serial']=$test_serial['csm_serial']+1;

        }else{
            $api_data['serial']=1;
        }
          $this->up_serial($api_data['serial']);
            $api_data['__stat_shop_name']=$this->system->getConf('system.shopname');
          $api_data['__stat_site_owner']=$this->system->getConf('store.contact');
          $api_data['__stat_contact']=$this->system->getConf('store.contact');
          $api_data['__stat_mobile']=$this->system->getConf('store.mobile')?$this->system->getConf('store.mobile'):'暂无';
          $api_data['__stat_tel']=$this->system->getConf('store.telephone')?$this->system->getConf('store.telephone'):'暂无';
          $api_data['__stat_email']=$this->system->getConf('store.email')?$this->system->getConf('store.email'):'暂无';
          $api_data['__stat_wangwang']=$this->system->getConf('store.wangwang')?$this->system->getConf('store.wangwang'):'暂无';
         $api_data['__stat_qq']=$this->system->getConf('store.qq')?$this->system->getConf('store.qq'):'暂无';
         $api_data['__stat_company']=$this->system->getConf('system.shopname');
       $api_data['__stat_address']=$this->system->getConf('store.province').$this->system->getConf('store.city').$this->system->getConf('store.address');
          $api_data['__stat_postcode']='485none';
          $api_data['__stat_sell_type']=$this->system->getConf('store.sell_type');
          $api_data['__stat_shop_desc']='485none';
          $api_data['__stat_shop_ver']='shopex485';
          $api_data['format']='json';
          $sign=$this-> make_shopex_ac($api_data,$token);
          $api_data['sign']=$sign;
          //  error_log(var_export($api_data,1),3,__FILE__.'.log');
          $url='http://csm.ex-sandbox.com/index.php/api/csm.datastat/exec/';
          $callback=$post_t->post($url,$api_data);
           //error_log(var_export($callback,1),3,__FILE__.'callback.log');
        }
         
          
  
    }

       function make_shopex_ac($post_params,$token){
        ksort($post_params);
       $str = '';
       foreach($post_params as $key=>$value){
        if($key!='sign') {
            $str.=$value;
        }
    }
      $sign=md5($str.$token);
      return $sign;
    
  
}
function get_onlybbyorderid($orderid,$member_refer){
    if($member_refer){
        if($row_order =$this->db->selectrow("select  final_amount as __stat_orderamount from sdb_orders where order_id = '".$orderid."' and pay_status='1'")){
    
     if($csm_time=$this->db->selectrow("select csm_time from sdb_openid_taobao_login where csm_type='".$member_refer."'")){
             $row_order['__stat_lastlogin']=$csm_time['csm_time'];

             }else{
                 $row_order['__stat_lastlogin']=time();

             }
    return  $row_order;
        }else{
            return false;
        }
    }else{
        return false;
    }

}

function gettypebyorderid($orderid){
    $post_t = $this->system->loadModel("utility/http_client");  
    $certificate = $this->system->loadModel("service/certificate");
    if(!$certificate->getToken()){
           return false;
       }
    $api_data=array();
    $certi_id = $certificate->getCerti();
     // error_log(var_export($certi_id,1),3,__FILE__.'certi_id.log');
    $token = $certificate->getToken();
    //$member_id=$this->db->selectrow("select member_id from sdb_orders where order_id='".$orderid."'");
     $member_refer=$this->get_refer($orderid);
    //error_log(var_export($member_refer,1),3,__FILE__.'member_refer.log');

    if($member_refer['member_refer'] !='local'){
        $v=$this->get_onlybbyorderid($orderid,$member_refer['member_refer']);
        if($v){
          $api_data['__stat_channel']='openid_'.$member_refer['member_refer'];
         $api_data['__stat_ordercount']=1;
         
         if($v['__stat_orderamount']){
         $api_data['__stat_orderamount']=$v['__stat_orderamount'];
         }else{
             $api_data['__stat_orderamount']=0;

         }
         if($v['__stat_lastlogin']){
         $api_data['__stat_logintime']=$v['__stat_lastlogin'];
         }else{
             $api_data['__stat_logintime']=time();

         }
         $api_data['pid']='sso';
         $api_data['licid']=$certi_id;
        $api_data['version']='1.0';
        $test_serial=$this->get_serial();
        if($test_serial){
            $api_data['serial']=$test_serial['csm_serial']+1;

        }else{
            $api_data['serial']=1;
        }
          $this->up_serial($api_data['serial']);
         // error_log(var_export($api_data,1),3,__FILE__.'api_data.log');
          $api_data['__stat_shop_name']=$this->system->getConf('system.shopname');
          $api_data['__stat_site_owner']=$this->system->getConf('store.contact');
          $api_data['__stat_contact']=$this->system->getConf('store.contact');
          $api_data['__stat_mobile']=$this->system->getConf('store.mobile')?$this->system->getConf('store.mobile'):'暂无';
          $api_data['__stat_tel']=$this->system->getConf('store.telephone')?$this->system->getConf('store.telephone'):'暂无';
          $api_data['__stat_email']=$this->system->getConf('store.email')?$this->system->getConf('store.email'):'暂无';
          $api_data['__stat_wangwang']=$this->system->getConf('store.wangwang')?$this->system->getConf('store.wangwang'):'暂无';
         $api_data['__stat_qq']=$this->system->getConf('store.qq')?$this->system->getConf('store.qq'):'暂无';
         $api_data['__stat_company']=$this->system->getConf('system.shopname');
       $api_data['__stat_address']=$this->system->getConf('store.province').$this->system->getConf('store.city').$this->system->getConf('store.address');
          $api_data['__stat_postcode']='485none';
          $api_data['__stat_sell_type']=$this->system->getConf('store.sell_type');
          $api_data['__stat_shop_desc']='485none';
          $api_data['__stat_shop_ver']='shopex485';

          $api_data['format']='json';
          $sign=$this-> make_shopex_ac($api_data,$token);
          $api_data['sign']=$sign;
          //$url='http://csm.ex-sandbox.com/index.php/api/csm.datastat/exec/';
          $url='http://csm.shopex.cn/index.php/api/csm.datastat/exec/';
          //error_log(var_export($certi_id,1),3,__FILE__.'certificate.log');
          //error_log(var_export($api_data,1),3,__FILE__.'.log');
          $callback=$post_t->post($url,$api_data);
         // error_log(var_export($callback,1),3,__FILE__.'callback.log');
          
        }
           

    }
}


  
   
   
    }



?>