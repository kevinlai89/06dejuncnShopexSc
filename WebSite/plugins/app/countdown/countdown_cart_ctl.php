<?php
if(!class_exists('ctl_cart')){
    require(CORE_DIR.'/shop/controller/ctl.cart.php');
}
class countdown_cart_ctl extends ctl_cart{

    function countdown_cart_ctl(){
        parent::ctl_cart();
        $this->system = &$GLOBALS['system'];
    }
    //�����Ʒ���������ã���������������¼۸�Ϊ�����ۣ����û����ָ�ԭ��
    function goodscountdowninfo($gid=0, $pid=0, $stradj='', $pmtid=0, $num=1){
		$aParams = $this->objCart->getParams($_POST['goods'],$gid, $pid, $stradj, $pmtid);
		$oCountdown = &$this->system->loadModel('plugins/countdown');
		
		if($row = $oCountdown->checkPrice($aParams['gid'])){
			$this->noCache=true;
			$this->pagedata['iscountdown'] = true;
			$this->pagedata['countdown'] = $row;
		}
		
        parent::addGoodsToCart($gid, $pid, $stradj, $pmtid, $num);
    }
}
?>