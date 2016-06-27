   <?php
$mode_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'include_v5':'include');
require_once(CORE_DIR.'/'.$mode_dir.'/adminPage.php');
require_once('mdl.shopex_stat.php');
class admin_stat_ctl extends adminPage{

    function admin_stat_ctl(){
         parent::adminPage();
    }
 
    function index(){
        $certificate = $this->system->loadModel("service/certificate");  
          $certificate_state=new mdl_shopex_stat();

         
      if(!$certificate->getToken()){
           echo "<p style='height:50px;border-color:red;background:#FBE7DC;text-align:center;font-size:15px;'>
           LISSENCE不正确,请检查您的证书是否正确！
            </p>";
           exit;
       }
        $certi_id = $certificate->getCerti();
        $token = $certificate->getToken();
        $get_statues=$certificate_state->get_value($certi_id);
         if(!$get_statues){
             $certificate_state->add_status($certi_id);
         }
        $sign = md5($certi_id.$token);
        $callbackurl=urlencode($this->system->base_url().'index.php?action_stat-index.html');
        $shoex_stat_webUrl = "http://stats.shopex.cn/?site_id=".$certi_id."&sign=".$sign."";
         $this->pagedata['certi_id']=$certi_id;
         $this->pagedata['sign']=$sign;
         $this->pagedata['stats_url'] = "http://stats.shopex.cn/";
         $this->pagedata['callback_url']=$callbackurl;
        $this->pagedata['shoex_stat_webUrl'] = $shoex_stat_webUrl;
        $state_statu=$certificate_state->get($certi_id);
        if($state_statu){
            $this->display('file:'.$this->template_dir.'view/showstats.html');
              
        }else{
               $this->display('file:'.$this->template_dir.'view/index.html');
        }
        
    }



}

















?>
