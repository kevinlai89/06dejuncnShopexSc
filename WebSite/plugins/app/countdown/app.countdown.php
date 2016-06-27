<?php 
class app_countdown extends app{
    var $ver = 1.0;
    var $name='抢购插件 by 清风设计工作室';
    var $website = 'http://www.hnqss.com/';
    var $author = '老曹';
    var $reqver = 'http://www.hnqss.com/';
    var $help = 'http://www.hnqss.com/';

    function ctl_mapper(){
        return array(
			'shop:product:index' => 'product_ctl:goodscountdowninfo',
			'shop:cart:addGoodsToCart' => 'cart_ctl:goodscountdowninfo',
			'shop:action_countdown:*' => 'countdown_ctl:*',
        );
    }

	function listener(){
        return array(
            'trading/order:create' =>'listener:countdownorderlog',
        );
    }

    function install(){
        return parent::install();
    }

	function uninstall(){
		return parent::uninstall();
	}

    function getMenu(&$menu){
		$menu['sale']['items'][]= array_unshift(
			$menu['sale']['items'],array(
				'type'=>'group',
				'label'=>__('★限时抢购'),
				'items'=>array(
					array(
						"type"=>'menu',
						'label'=>__('抢购活动列表'),
						'link'=>'index.php?ctl=plugins/ctl_countdown&act=index'
					)
				)
			)
		);
    }

	
}