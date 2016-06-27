<?php
function widget_cfg_qf_brand($system){
	$objCat = &$system->loadModel('goods/productCat');
	$oBrand = &$system->loadModel( "goods/brand" );
	/*按分类加载出品牌*/
	$cats = $objCat->get_cat_list();
	$catbrands = array();
	foreach($cats as $cat){
		if($cat['step'] == '1'){//只处理一级分类
			$brnads = $oBrand->getBrandsByCatId($cat['cat_id']);
			$catbrands[] = array(
				'cat_id'=>$cat['cat_id'],
				'cat_name'=>$cat['cat_name'],
				'brands'=>$brnads
			);
		}
	}

	$brandNotInType = $oBrand->getBrandsNotInType();
	$catbrands[] = array(
		'cat_id'=>'0',
		'cat_name'=>'其它品牌',
		'brands'=>$brandNotInType
	);

    return array(
		'cats'=>$cats,
		'catbrands'=>$catbrands
	);
}
?>