<?php
	include_once CORE_DIR.'/shop/controller/ctl.brand.php';

	class cct_brand extends ctl_brand {
		function index($brand_id, $page=1) {
			if($this->system->getConf('site.tag_status')){
						$this->pagedata['tag_status'] = true; 
					}
			parent::index($brand_id, $page=1);
		}
	}