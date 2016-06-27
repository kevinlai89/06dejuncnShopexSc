<?php
class messenger_sms{

    var $name             = '手机短信'; //名称
    var $iconclass        = "sysiconBtn sms"; //操作区图标
    var $name_show        = '发短信'; //列表页操作区名称
    var $version          = '$ver$'; //版本
    var $updateUrl        = false;  //新版本检查地址
    var $isHtml           = false; //是否html消息
    var $hasTitle         = false; //是否有标题
//    var $maxtitlelength = 300; //最多字符
    var $maxtime          = 300; //发送超时时间 ,单位:秒
    var $maxbodylength    = 300; //最多字符
    var $allowMultiTarget = false; //是否允许多目标
//  var $targetSplit      = ','; //多目标分隔符
    var $withoutQueue     = false;
    var $dataname         = 'mobile';
    var $sms_service_ip   = '124.74.193.222';
    var $sms_service      = 'http://idx.sms.shopex.cn/service.php';

    function messenger_sms(){
        $this->system = &$GLOBALS['system'];
        $this->net=&$this->system->loadModel('utility/http_client');
        $this->sms=&$this->system->loadModel('system/sms');
    }

    function send($to,$message,$config,$sms_type){
        //if(!$this->checkL()){
        //    return 'license error';
        //}
        $smsRes = $this->sms->getSmsInfo();
        if ($smsRes['res'] == 'fail'){
            return $smsRes['info'];
            //$smsRes['info']['month_residual'];//当前可用短信条数
            //$smsRes['info']['all_residual'];//总剩余条数
        }
        if($smsRes['info']['month_residual']<=0){
            //return '短信账户信息可用短息条数不够';
        }
        if(strpos($message,'|use_reply') ===true){
            $message = str_replace('|use_reply','',$message);
            $this->use_reply = 1;
        }
        $this->content = $message;
        $this->mobile = $to;
        $result=$this->send_info($result,$this->ex_type,$this->version,$sms_type);
        return $result;
    }
    function send_info($url,$ex_type,$version,$sms_type=false){
        $content['phones']   = $this->mobile;
        $content['content'] = $this->content;
        if(is_array(explode(',',$this->mobile))){
            $send_type='fan-out';
        }else{
            $send_type='notice';
        }
        //error_log(var_export($content,1)."\r\n",3,__FILE__.'.log');
        if($this->use_reply ==1){
           $reply = '|'.$this->use_reply;
        }else{
           $reply = '';
        }
        $result = $this->sms->send('['.json_encode($content).']'.$reply,'sms.send',$send_type);
        if($result['res'] == 'fail'){
            return $result['info']; //发送失败
        }else{
            return true;//发送成功
        }
    }
    function checkL(){
        //error_log(var_export($this->getCerti(),1)."\r\n",3,__FILE__.'getCerti.log');
        //error_log(var_export($this->getToken(),1)."\r\n",3,__FILE__.'getToken.log');
        if(!$this->getCerti() || !$this->getToken()){
            return false;
        }else{
            return true;
        }
    }
    function apply(){
        /**
        $certi_id    license_id
        $token        手机私钥
        **/
        $submit_str['certi_id'] = $this->getCerti();
        $submit_str['ac'] = md5($this->getCerti().$this->getToken());
        $submit_str['version']=$this->version;
        $results = $this->net->post($this->sms_service,$submit_str);
        $result = explode('|',$results);
        if($result[0] == '0'){
            return $result[1];
        }
        if($result[0] == '1'){
            return false;
        }
        if($result[0] == '2'){
            return false;
        }
    }


    //获取证书ID，此ID在安装网店时，在登陆企业账号后，由中心返回
    function getCerti(){
        if($this->system->getConf('certificate.id')){
            return $this->system->getConf('certificate.id');
        }else{
            return false;
        }
    }
    function getEntId(){
        if($this->system->getConf('enterprise.ent_id')){
            return $this->system->getConf('enterprise.ent_id');
        }else{
            return false;
        }
    }
    //获取签名串
    function getent_ac(){
        if($this->system->getConf('enterprise.ent_ac')){
            return $this->system->getConf('enterprise.ent_ac');
        }else{
            return false;
        }
    }
    function getToken(){
        if($this->system->getConf('certificate.token')){
            return $this->system->getConf('certificate.token');
        }else{
            return false;
        }
    }
    function msg($index){
        $aMsg=array(
            '200'=>'true',
            '1'=>'Security check can not pass!',
            '2'=>'Phone number format is not correct.',
            '3'=>'Lack of content or content coding error.',
            '4'=>'Lack of balance.',
            '5'=>'Information packets over limited.',
            '6'=>'You must recharge before write message!',
            '901'=>'Write sms_log error!',
            '902'=>'Write sms_API error!'
            );
        return $aMsg[$index];
    }

    /**
     * ready
     * 可选方法，准备发送时触发
     *
     * @param mixed $config
     * @access public
     * @return void
     */
    function ready($config){
        $this->url = $this->apply($this->sms_service,$this->version);
        return $this->url;
    }

    /**
     * finish
     * 可选方法，结束发送时触发
     *
     * @param mixed $config
     * @access public
     * @return void
     */
    function finish($config){}
    function extraVars(){
        #$this->system->setConf('enterprise.ent_id','121109000490');
        #$this->system->setConf('certificate.id','121109000490');
        #$this->system->setConf('enterprise.ent_ac',md5('a7758314820ShopEXUser'));
        //登陆邮箱phlv@163.com 密码a7758314820 企业ID121109000490
        //$getSmsInfo=$this->sms->getSmsInfo();
        //error_log(var_export($getSmsInfo,1)."\r\n",3,__FILE__.'getSmsInfo.log');
        return array('outgoingOptions'=>$this->sms->getSmsUrl('sms','accountsList'));
    }

}

?>

