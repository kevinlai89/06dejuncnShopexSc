<?php
/* author: http://www.shopextool.cn */
class cct_product extends ctl_product {
	function index($gid,$specImg='',$spec_id='') {
    	$objGoods = $this->system->loadModel('trading/goods');
    	$this->pagedata['qtn_config'] = $objGoods->get_qtn_config();    	
        parent::index($gid,$specImg,$spec_id);
    }

	function greq($goods_id){
		$this->_verifyMember(false);
		if ($this->member['member_id']){
			$oMem = $this->system->loadModel('member/member');
			$this->pagedata['member_data'] = $oMem->getFieldById($this->member['member_id']);
		}
		$this->pagedata['member'] = $this->member;
		$objGoods = $this->system->loadModel('trading/goods');
		$this->pagedata['goods'] = $goods = $objGoods->getGoods($goods_id);		
		$this->pagedata['url_referer'] = $_SERVER['HTTP_REFERER'];	
		if ($goods['qtn_template_id'] && $qtn_template = $objGoods->getQtnTemplate($goods['qtn_template_id'])){
			//echo '<PRE>';var_dump($qtn_template);exit;
			$this->pagedata['qtn_template'] = $qtn_template;
		}
		$this->display('product/greq.html');
	}

	function toGreq($goods_id){
		//echo '<PRE>';var_dump($goods_id, $_POST);exit;
		$title = '客户询价信息';

		$objGoods = $this->system->loadModel('trading/goods');
		if ($goods = $objGoods->getFieldById($goods_id, array('*'))){
			if ($goods['qtn_template_id'] && $qtn_template = $objGoods->getQtnTemplate($goods['qtn_template_id'])){			
				$this->pagedata['qtn_template'] = $qtn_template;
			}
		}
		$this->pagedata['goods'] = $goods;

		if (count($_POST) > 0){
			$this->pagedata['data'] = $_POST;		
			$body = $this->fetch('shop:product/greq_email.html');
		
			$oMsg = $this->system->loadModel('system/messenger');
			$ret = $oMsg->send_email(RECV_GREQ_EMAIL, $title, $body);
		}
		
		$go_url = $_POST['url_referer'] ? $_POST['url_referer'] : $this->system->mkUrl('product', 'index', array($goods_id));
		//var_dump($ret);exit;
		if ($ret){
			$this->splash('success', $go_url, '提交成功!');
		}else{
			$this->splash('failed', $go_url, '提交失败!');
		}	
	}
}