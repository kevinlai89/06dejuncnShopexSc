<?php
function widget_qf_menu($setting,&$system){
    define('IN_SHOP',true);
	$rewrite = intval( $system->getconf("system.seo.emuStatic" ) );
	$curUrl = $system->request['base_url'].($rewrite ? "" : "?").$system->request['query'];//当前菜单
	
    $sitemap = &$system->loadModel('content/sitemap');
    $result=$sitemap->getMap(1);
    $setting['max_leng']=$setting['max_leng']?$setting['max_leng']:7;
    $setting['showinfo']=$setting['showinfo']?$setting['showinfo']:"更多";
    
	//标记当前菜单
	$result = menu_flagCurrentMenu($result, $curUrl);
    return $result;
}

function menu_flagCurrentMenu($data, $curUrl){
	$isIn = false;//找到？
	foreach($data as $k => $v){
		if($v['link']==$curUrl){
			$isIn = true;
			$data[$k]['isCurrent'] = true;
		}
	}
	if(!$isIn){
		$data[0]['isCurrent'] = true;
	}
	return $data;
}
?>
