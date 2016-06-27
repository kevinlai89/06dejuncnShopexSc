<?php
function widget_qf_countdown(&$setting, &$system){
	$limit = (intval($setting['showcolumn'])>0)?intval($setting['showcolumn']):1;//��ʾ��Ʒ��
	$shownotstart = $setting['shownotstart']=="on";//�Ƿ���ʾδ��ʼ����Ʒ
	$showovertime = $setting['showovertime']=="on";//�Ƿ���ʾʱ���������Ʒ
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