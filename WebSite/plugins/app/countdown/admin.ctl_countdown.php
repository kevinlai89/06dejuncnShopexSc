<?php
$include_dir =  ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'include_v5':'include');
require_once(CORE_DIR.'/'.$include_dir.'/objectPage.php');
class admin_ctl_countdown extends objectPage{
	var $object = 'plugins/countdown';
    var $finder_default_cols = '_cmd,cat,start_time,end_time';
    var $workground = 'sale';
    var $filterUnable = true;
	
    function admin_ctl_countdown(){
		
        parent::objectPage();
		$this->finder_action_tpl = dirname(__FILE__).'/view/finder_action.html';
		$appmgr = $this->system->loadModel('system/appmgr');
        $tb_api = &$appmgr->load('countdown');
        $this->tb  = &$tb_api;

       
    }
	function export(){
        $this->template_dir = CORE_DIR.'/admin/view/';
        parent::export();
    }
	function colsetting(){
        $this->template_dir = CORE_DIR.'/admin/view/';
        parent::colsetting();
    }
	function detail($object_id,$func=null){
          $this->template_dir = CORE_DIR.'/admin/view/';
          parent::detail($object_id,$func=null);
    }
    function index(){
	
            $this->template_dir = CORE_DIR.'/admin/view/';
            parent::index();
    }
	function showAddCountdown($catId){
		$this->template_dir = CORE_DIR.'/admin/view/';
		$this->path[] = array('text'=>__('抢购活动内容页'));
        $oCount = &$this->system->loadModel('plugins/countdown');
       
        if ($catId) {
            $this->pagedata['countdown'] = $oCount->getCountdownById($catId);
            foreach($oCount->getCountdownGoods($catId) as $rows){//商品列表
                $aId[] = $rows['goods_id'];
                $aNum[$rows['goods_id']] = array('countdown_num'=>$rows['countdown_num'], 'countdown_price'=>$rows['countdown_price'], 'limit_num'=>$rows['limit_num'], 'limit_start_time'=>$rows['limit_start_time'], 'limit_end_time'=>$rows['limit_end_time']);
            }
			$this->pagedata['countdown']['goods_id'] = $aId;
			$this->pagedata['countdown']['moreinfo'] = $aNum;
        } else {
            //$this->pagedata['countdown']['cat_id'] = $aType[0][0]['cat_id'];
            $this->pagedata['countdown']['shop_iffb'] = 1;
            $this->pagedata['countdown']['ifrecommend'] = 1;
            $this->pagedata['countdown']['limit_num'] = 1;
         
        }
		//$this->pagedata['countdown_items_view'] = dirname(__FILE__)."/view/countdown_items.html";
	 	$this->display(dirname(__FILE__)."/view/addCountdown.html");
	}

	function addCountdown(){
		$this->template_dir = CORE_DIR.'/admin/view/';
        $this->begin('index.php?ctl=plugins/ctl_countdown&act=index');
    	$spike= $this->system->loadModel('plugins/countdown');
        if($spike->save($_POST)){
            $this->end(true,__('抢购活动添加成功！'));
        }else{
            $this->end(false,__('抢购活动添加失败！'));

        }
		
    }
    
}

















?>
