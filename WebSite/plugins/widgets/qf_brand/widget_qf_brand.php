<?php
function widget_qf_brand($setting,&$system){
    $brandIds = $setting['brand_id'];
	$oBrand=$system->loadModel('goods/brand');
	$brands = $oBrand->getBrandsById($brandIds);	
	//print_r($brands);
    return $brands;
}
?>