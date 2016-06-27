<?php
function widget_qf_countdown(&$setting, &$system){
	$limit = (intval($setting['showcolumn'])>0)?intval($setting['showcolumn']):1;//显示商品数
	$shownotstart = $setting['shownotstart']=="on";//是否显示未开始的商品
	$showovertime = $setting['showovertime']=="on";//是否显示时间结束的商品
	$cat_id = $setting['countdowncat']?$setting['countdowncat']:0;
	$oCount = $system->loadModel('plugins/countdown');
	$catInfo = $oCount->getCountdownById($cat_id);
	$catGoods = $oCount->getCountdownGoods($cat_id, $limit, $shownotstart, $showovertime);
	$data[] = array('catinfo'=>$catInfo, 'goodslist'=>$catGoods);
	//echo "======================= ".$GLOBALS['runtime']['member_lv']." -----";
	//print_r($data);
	return $data;
}
?>