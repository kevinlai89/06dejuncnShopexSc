<?php
$include_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'include_v5':'include');
require_once(CORE_DIR.'/'.$include_dir.'/shopPage.php');
class countdown_countdown_ctl extends shopPage{

  	function index($id) {
		$oCountdown = &$this->system->loadModel('plugins/countdown');
		$this->__tmpl=dirname(__FILE__).'/view/index.html';
        $this->title    = '抢购活动列表页';
		$this->path[] = array('title'=>__('抢购活动列表'),'link'=>$this->system->mkUrl('action_countdown','showList'));
        $this->path[] = array('title'=>$result['brand_name']);
		$this->pagedata['css_url'] = str_replace("\\","/",$this->system->base_url().substr(dirname(__FILE__),strpos(dirname(__FILE__),'plugins')));
		$this->pagedata['countdown']=$oCountdown->getCountdownList();
		//print_r($oCountdown->getCountdownList());
		//exit();
     	$this->output();
    }
	
	function checkPrice($goodsId){
		$this->noCache=true;
		$oCountdown = &$this->system->loadModel('plugins/countdown');
		$row = $oCountdown->checkPrice($goodsId);
		//print_r($row);
		echo "success";
	}
}

?>
