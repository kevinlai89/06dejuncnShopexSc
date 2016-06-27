<?php
function widget_qf_goodscat3(&$setting, &$system, $env){
	$setting['view'] = $system->getConf('gallery.default_view');
	$setting['showIndex'] = ($system->request['query'] == "index.html");//首页
	switch($system->request['action']['controller']){
		case 'comment':
		case 'product': $curcatid = intval($system->controller->pagedata['goods']['cat_id']); break; //产品详细页
		default: $curcatid = intval($system->request['action']['args'][0]);
	}
	$setting['curid'] = $curcatid;
	$setting['depth'] = 1; //级别显示模式，1表示显示全部分类；>2表示自动判断或指定分类时显示该分类下的子分类;
	$goodsCat = array();

	$o = $system->loadModel('goods/productCat');

	if(($brandGroup = qf_get_goodsCats($goodsCat, $setting, $o)) && ($lasttime = @filemtime(MEDIA_DIR."/brand_list.data"))){
		$timestamp = time();
		$file = MEDIA_DIR."/qf_brand_list.data";
		$data = $system->qf_brand_list ? $system->qf_brand_list : json_decode(file_get_contents($file), true);
		$getcache = $data[0] != $lasttime || (__ADMIN__ == 'admin' && $setting['refresh']) || !intval($setting['cachelife']) || intval($system->getConf('site.qf_brand_cachelife')) < $timestamp ? false : true;
		
		if(!$getcache){
			$b = $system->loadModel('goods/brand');
			$brands = array();
			foreach((array)$b->getAll() as $v){
				$brands[$v['brand_id']] = $v;
			}
		}
		$cache = array();
		foreach($brandGroup as $catid => $v){
			if($getcache){
				$goodsCat[$catid]['brands'] = (array)$data[1][$catid];
			}else{
				foreach(qf_getBrand($v, $o) as $a => $k){
					if(!$k['brand_id']) continue;
					$cache[$catid][] = array(
						'id' => $k['brand_id'],
						'b_id' => 'b,'.$k['brand_id'],
						'name' => $brands[$k['brand_id']]['brand_name'],
						'plus' => $k['brand_cat'],
						'brand_logo' => $brands[$k['brand_id']]['brand_logo'],
					);
					$goodsCat[$catid]['brands'] = $cache[$catid];
				}
			}
		}

		$system->qf_brand_list = array($lasttime, !$getcache ? $cache : $data[1]);		
		if(!$getcache){
			$system->setConf('site.qf_brand_cachelife', $timestamp + intval($setting['cachelife'])*3600);
			file_put_contents($file, json_encode($system->qf_brand_list));
		}
	}
	
	$top_pid = $setting['curTopid'];
	$top2_pid = $setting['curTop2id'];

	if($setting['catShowType'] == 1 && $setting['qf_tpl'] != 'ppjd.html'){//自动判断当前分类，显示其子分类
		if($top_pid){
			$setting['depth'] = 2;
			$goodsCat = $top2_pid && $goodsCat[$top_pid]['sub'][$top2_pid]['sub'] && ($setting['depth'] = 3) ? array($top2_pid => $goodsCat[$top_pid]['sub'][$top2_pid]) : array($top_pid => $goodsCat[$top_pid]);
		}
	}

	if($setting['qf_tpl'] == 'ppjd.html'){
		qf_filterShowCat($goodsCat, $setting, $system);
	}

	if($setting['custommenus'] && is_array($setting['custommenus'])){
		$thisurl = $system->request['query'];
		foreach($setting['custommenus'] as $k=>$cust){
			if(strpos($cust['url'], $thisurl)){
				$setting['custommenus'][$k]['iscurrent'] = true;
			}
		}
	}
	
	foreach($setting['resetcat'] as $key => $cat){
		if($cat['name']){
			$goodsCat[$key]['name'] = $cat['name'];
		}
		if($cat['link']){
			$goodsCat[$key]['url'] = $cat['link'];
		}
		$thisurl = $system->request['query'];
		if(strpos($cat['link'], $thisurl)){
			$goodsCat[$key]['curr'] = 'current';
		}
	}
	return $goodsCat;

}

function qf_getBrand($cat_id, $o){
	if($cat_id){
		return $o->db->select("SELECT COUNT(brand_id) as brand_cat,brand_id From sdb_goods where cat_id IN(".implode(',',$cat_id).") AND marketable='true' AND disabled='false' AND brand_id IS NOT NULL GROUP By brand_id");
	}
	return array();
}

//取所有分类列表
function qf_get_goodsCats(&$goodsCat, &$setting, $o){	
	$setting['curTopid'] = $setting['curTop2id'] = 0;

	$brandGroup = array();
	$data = $o->getTreeList();

	//统计所有分类下的商品数量
	if($setting['ifCatCounter']){
		$allCatGoodsCount = $o->getAllCatGoodsCount($data);
	}

    for($i=0; $i<count($data); $i++){
        $cat_path = $data[$i]['cat_path'];
        $cat_name = $data[$i]['cat_name'];
        $cat_id = $data[$i]['cat_id'];
        if(empty($cat_path) or $cat_path==","){
            $goodsCat[$cat_id]['label'] = $cat_name;    
            $goodsCat[$cat_id]['cat_id'] = $cat_id;
			$goodsCat[$cat_id]['goods_count'] = $allCatGoodsCount[$cat_id];
			if($setting['ShowBrand'] == 'on') $brandGroup[$cat_id][] = $cat_id;
			if($setting['curid'] == $cat_id){
				$goodsCat[$cat_id]['curr'] = 'current';
				$setting['curTopid'] = $cat_id;
			}
        }
    }

    for($i=0; $i<count($data); $i++){
        $cat_path = $data[$i]['cat_path'];
        $cat_name = $data[$i]['cat_name'];
        $cat_id = $data[$i]['cat_id'];
        $parent_id = $data[$i]['pid'];

        if(trim($cat_path) == ','){
            $count=0;
        }else{
            $count = count(explode(',',$cat_path));
        }
        if($count == 2){//第二层
            $c_1 = intval($parent_id);
            $c_2 = intval($cat_id); 
            $goodsCat[$c_1]['sub'][$c_2]['label'] = $cat_name;
            $goodsCat[$c_1]['sub'][$c_2]['cat_id'] = $cat_id;
			$goodsCat[$c_1]['sub'][$c_2]['goods_count'] = $allCatGoodsCount[$c_2];
			if($setting['ShowBrand'] == 'on') $brandGroup[$c_1][$c_2] = $c_2;			
			if($setting['curid'] == $c_2){
				$goodsCat[$c_1]['curr'] = 'current';
				$goodsCat[$c_1]['sub'][$c_2]['curr'] = 'current1';
				$setting['curTopid'] = $c_1;
				$setting['curTop2id'] = $c_2;
			}
        }
        if($count == 3){//第三层
            $tmp = explode(',',$cat_path);
            $c_1 = intval($tmp[0]);
            $c_2 = intval($tmp[1]);
            $c_3 = intval($cat_id);
            $goodsCat[$c_1]['sub'][$c_2]['sub'][$c_3]['label'] = $cat_name;
            $goodsCat[$c_1]['sub'][$c_2]['sub'][$c_3]['cat_id'] = $cat_id;
			$goodsCat[$c_1]['sub'][$c_2]['sub'][$c_3]['goods_count'] = $allCatGoodsCount[$c_3];
			if($setting['ShowBrand'] == 'on') {
				$brandGroup[$c_1][$c_2] = $c_2;
				$brandGroup[$c_1][$c_3] = $c_3;
			}
			if($setting['curid'] == $c_3){
				$goodsCat[$c_1]['curr'] = 'current';
				$goodsCat[$c_1]['sub'][$c_2]['curr'] ='current1';
				$goodsCat[$c_1]['sub'][$c_2]['sub'][$c_3]['curr'] = 'current2';
				$setting['curTopid'] = $c_1;
				$setting['curTop2id'] = $c_2;
			}
        }
    }

	return $brandGroup;
}

function qf_filterShowCat(&$goodsCat, $setting, $system)
{
	$tpl = substr($setting['qf_tpl'], 0, -5);
	$timestamp = time();
	$file = MEDIA_DIR."/qf_goodscat_".$tpl.".data";
	$data = intval($setting['cachelife']) ? json_decode(file_get_contents($file), true) : array();
	$getcache = (__ADMIN__ == 'admin' && $setting['refresh']) || $data[0] < $timestamp ? false : true;
	$endData = array();

	if($getcache){
		$endData = $data[1];
	}else{
		if($tpl == 'ppjd'){
			$o = $system->loadModel('goods/products');			
			/*===================qf_custom develop on 20111019 start===================*/
		  	$art_o=$system->loadModel('content/article');
		 	$artlst_o=$system->loadModel('content/article');
		  	$artlist=$artlst_o->getCategorys();
			/*===================qf_custom develop on 20111019 end===================*/
			
			foreach($goodsCat as $good_id => $cat){
				$filter = array('cat_id' => array($cat['cat_id']));
				if($cat['sub']){
					foreach($cat['sub'] as $cat1){
						$filter['cat_id'][] = $cat1['cat_id'];
						if($cat1['sub']){
							$filter['cat_id'] = array_merge($filter['cat_id'], array_keys($cat1['sub']));
						}
					}
				}
				$order = $setting['showGoodsOrderBy'][$good_id] ? $o->orderBy($setting['showGoodsOrderBy'][$good_id]) : null;
				$limit = $setting['showGoodsLimit'][$good_id] ? $setting['showGoodsLimit'][$good_id] : 10;
				foreach($o->getList(null, qf_getFilter($filter),0, $limit, $order['sql']) as $row){
					$endData[$good_id]['goods'][] = array(
						'goods_id' => $row['goods_id'],
						'name' => $row['name'],
						'thumbnail_pic' => $row['thumbnail_pic'],
						'price' => $row['price'],
						'mkprice' => $row['mktprice'],
					);
				}
/*===================qf_custom develop on 20111019 start===================*/
        $art_orderType=array('pubtime','DESC');
        $limit = $setting['showArticlesNum'][$cat['cat_id']] ? $setting['showArticlesNum'][$cat['cat_id']] : 10;
        $artcont=$art_o->getList('title,article_id',array('node_id'=>intval($setting['showArticlesCats'][$cat['cat_id']]),'ifpub'=>1,'disabled'=>'false'),0,$limit,$art_orderType);
				foreach($artcont as $art_row){
          $endData[$good_id]['art_cat_nm']=$artlist[$setting['showArticlesCats'][$cat['cat_id']]];
					$endData[$good_id]['art_cat_id']=$setting['showArticlesCats'][$cat['cat_id']];
          $endData[$good_id]['article'][] = array(
						'article_id' => $art_row['article_id'],
						'title' => $art_row['title'],
						);
				}
/*===================qf_custom develop on 20111019 end===================*/
			}
		}

		file_put_contents($file, json_encode($endData));
	}

	foreach($endData as $goods_id => $list){
		if($list && isset($goodsCat[$goods_id])){
			foreach($list as $key => $d) $goodsCat[$goods_id][$key] = $d;
		}
	}

	return;

}

function qf_getFilter($filter){
    $filter = array_merge(array('marketable'=>"true",'disabled'=>"false",'goods_type'=>"normal"), $filter);
    if($GLOBALS['runtime']['member_lv']){
        $filter['mlevel'] = $GLOBALS['runtime']['member_lv'];
    }
    if($filter['props']){
        foreach($filter['props'] as $k=>$v){
            $filter['p_'.$k]=$v[0];
        }
    }
    return $filter;
}

?>
