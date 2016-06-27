<?php
require_once(CORE_DIR.'/model_v5/system/mdl.template.php');
class cmd_template extends mdl_template{
	function skv(){
	}  
  function cmd_template(){
			$this->skv();
      parent::mdl_template();
  } 
    function getname(){
        $ctl = array(
            'index'=>__('首页'),
            'gallery'=>__('商品列表页'),
            'product'=>__('商品详细页'),
            'comment'=>__('商品评论/咨询页'),
            'article'=>__('文章页'),
            'article'=>__('文章列表页'),
            'gift'=>__('赠品页'),
            'package'=>__('捆绑商品页'),
            'brandlist'=>__('品牌专区页'),
            'brand'=>__('品牌商品展示页'),
            'cart'=>__('购物车页'),
            'search'=>__('高级搜索页'),
            'passport'=>__('注册/登录页'),
            'member'=>__('会员中心页'),
            'page'=>__('站点栏目单独页'),
            'order_detail'=>__('订单详细页'),
            'order_index'=>__('订单确认页'),
            'default'=>__('默认页'),
              "tuan" => __( "团购页" ),
        );
        return $ctl;
    }
    
}


?>
