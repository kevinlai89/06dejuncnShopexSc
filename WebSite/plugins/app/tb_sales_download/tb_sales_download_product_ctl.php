<?php
if(!class_exists('ctl_product')){
   require_once(CORE_DIR.'/shop/controller/order/ctl.product.php');
}
class tb_sales_download_product_ctl extends ctl_product{

   function index($gid,$specImg='',$spec_id=''){
        $objGoods = $this->system->loadModel('trading/goods');
        $this->pagedata['qtn_config'] = $objGoods->get_qtn_config();        

        $this->pagedata['_MAIN_'] = dirname(__FILE__).'/view/product/index.html';
       //$this->__tmpl= dirname(__FILE__).'/view/product/index.html';
       parent::index($gid,$specImg='',$spec_id='');
   }
   function selllog($gid,$nPage){
        $this->pagedata['_MAIN_'] = dirname(__FILE__).'/view/product/selllog.html';
        parent::selllog($gid,$nPage);

   }
}