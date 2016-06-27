<?php
require_once(CORE_DIR.'/shop/controller/ctl.member.php');
class openid_renren_ctl_authorize extends ctl_member{
    var $_key;
    var $customer_template_type;
     function openid_renren_ctl_authorize(){
         parent::ctl_member();
         $this->system=&$GLOBALS['system'];
         $this->db=$this->system->database();
         if($api_key = $this->system->getConf('app.openid_renren.apikey')){
           $this->_key = $api_key;
        }
     }

/**/
     function fav(){
        $this->customer_template_type = 'member';
        $authorize=$this->system->loadModel('plugins/openid_renren');
        $row=$authorize->get_auth_value($_COOKIE['LOGIN_UNAME']);
        $this->pagedata['feed_publish'] =$row['authorize_item'];
        $this->pagedata['member_uname'] =$_COOKIE['LOGIN_UNAME'];
        $this->pagedata['app_key'] =$this->_key;
        $this->output_fav();
     }

     function output_fav(){
        
        if($GLOBALS['runtime']['member_lv']){
            $oLevel = &$this->system->loadModel('member/level');
            $aLevel = $oLevel->getFieldById($GLOBALS['runtime']['member_lv']);
            if($aLevel['disabled']=='false'){
                $this->member['levelname'] = $aLevel['name'];
            }
        }
        
        $oSex = &$this->system->loadModel('member/member');
        $aSex = $oSex->getFieldById($this->member['member_id']);

        $this->pagedata['member'] = $this->member;
        $this->pagedata['sex'] = $aSex['sex'];
        $this->pagedata['cpmenu'] = $this->map;
        $this->pagedata['current'] = $this->_action;

        $this->pagedata['_PAGE_']=PLUGIN_DIR.'/app/openid_renren/view/fav.html';
        
        $this->pagedata['_MAIN_'] = 'member/main.html';
        parent::output();
     }

}

