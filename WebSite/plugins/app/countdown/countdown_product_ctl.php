<?php
if(!class_exists('ctl_product')){
    require(CORE_DIR.'/shop/controller/ctl.product.php');
}
class countdown_product_ctl extends ctl_product{

    function countdown_product_ctl(){
        parent::ctl_product();
        $this->system = &$GLOBALS['system'];
    }
    //�����Ʒ���������ã���������������¼۸�Ϊ�����ۣ����û����ָ�ԭ��
    function goodscountdowninfo($gid,$specImg='',$spec_id=''){

		$oCountdown = &$this->system->loadModel('plugins/countdown');
		//print_r($oCountdown->checkPrice($gid));
		//exit();
		if($row = $oCountdown->checkPrice($gid)){
			$this->noCache=true;
			$this->pagedata['iscountdown'] = true;
			$this->pagedata['countdown'] = $row;
		}
		
        $objGoods = $this->system->loadModel('trading/goods');
        $this->pagedata['qtn_config'] = $objGoods->get_qtn_config();        
        
        $this->pagedata['_MAIN_'] = 'product/index.html';
        parent::index($gid,$specImg,$spec_id);
    }
}
?>