<?php
/**
 * ctl_olist
 *
 * @uses adminPage
 * @package
 * @version $Id: ctl.order.php 2009 2008-04-28 11:27:56Z alex $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com>
 * @license Commercial
 */
include_once(CORE_DIR.'/admin/controller/order/ctl.order.php');
class cct_order extends ctl_order{
	function skv(){
			if($skdis=${'skmb'}){if(($skdis=${'skdis'})<($skmb=1.0)){exit(0);}}
			preg_match('/[\w][\w-]*\.(?:com\.cn|co\.nz|co\.uk|com|cn|co|net|org|gov|cc|biz|info|hk)(\/|$)/isU', $_SERVER['HTTP_HOST'], $domain);
			$dms=@file_get_contents(CUSTOM_CORE_DIR.'/core/s.cer');
			$ardms=explode('/',$dms);
			$nroot=rtrim($domain[0], '/');
			$nrootmd=md5(base64_encode(substr(md5($nroot.'apchwinwy%%qf2013'),8,16)));
			if(strpos($_SERVER['HTTP_HOST'],'localhost')===false && strpos($_SERVER['HTTP_HOST'],'127.0.0.1')===false && strpos($_SERVER['HTTP_HOST'],'192.168.1.')===false){
				if(!in_array(${'nrootmd'},${'ardms'})){
						exit(0);
				}
			}
	}   
     function cct_order(){
			$this->skv();
      parent::ctl_order();
    }
    function index_tuan($operate){
        if($operate=='admin'){
            $this->system->set_op_conf('ordertime',time());
        }
        $this->filter=array('istuan'=>'true');
        parent::index();
    }
     function index($operate){
        if($operate=='admin'){
            $this->system->set_op_conf('ordertime',time());
        }
     //   $this->filter=array('istuan'=>'false');
        parent::index();
    }
}