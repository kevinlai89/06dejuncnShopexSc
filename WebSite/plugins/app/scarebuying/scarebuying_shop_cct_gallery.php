<?php
require_once(CORE_DIR.'/shop/controller/ctl.gallery.php');
if (!class_exists('mdl_scare')) {
    require_once('mdl.scare.php');
}
class scarebuying_shop_cct_gallery extends ctl_gallery {

    //    function grid($view='grid',$cat_id='',$urlFilter=null,$orderBy=0,$tab=null,$page=1,$cat_type=null){
    //        $this->index($view,$cat_id,$urlFilter,$orderBy,$tab,$page,$cat_type);
    //    }
    function index($view,$cat_id='',$urlFilter=null,$orderBy=0,$tab=null,$page=1,$cat_type=null) {
        if($orderBy==5 || $orderBy==6){
            $this->noCache = true;
        }
        if($cat_type){
            $this->type='virtualcat';
            $this->cat_type=$cat_type;
            $virtualCat=&$this->system->loadModel('goods/virtualcat');
            $vcat=$virtualCat->instance($cat_type);
            //print_r($vcat);exit;
            parse_str($vcat['filter'],$type_filter);

        }


        $this->customer_template_type='gallery';
        $this->customer_template_id=$cat_id;

        $urlFilter=htmlspecialchars(urldecode($urlFilter));

        if($cat_id == '_ANY_'){
            unset($cat_id);
        }
        if($cat_id){
            $cat_id=explode(",",$cat_id);
            foreach($cat_id as $k=>$v){
                if($v) $cat_id[$k]=intval($v);
            }
            $this->id = implode(",",$cat_id);
        }
        if( !$cat_id  ){
            $cat_id = array('');
            $this->id = '';
        }
        //{{{初始化操作
        $pageLimit = $this->system->getConf('gallery.display.listnum');
        $this->pagedata['pdtPic']=array('width'=>100,'heigth'=>100);
        //        $orderBy = (!$orderBy)?3:$orderBy;
        $this->pagedata['args'] = array($this->id,urlencode($urlFilter),$orderBy,$tab,$page,$cat_type);
        $this->pagedata['curView'] = $view;
        $productCat = &$this->system->loadModel('goods/productCat');
        if($cat_type){
            $this->pagedata['childnode'] = $virtualCat->getCatParentById($cat_type,$view);

        }else{
            $this->pagedata['childnode'] = $productCat->getCatParentById($cat_id,$view);

        }
        $brandGroup=&$this->system->loadModel('goods/brand');
        $objGoods = &$this->system->loadModel('goods/products');
        $brandResult=$brandGroup->getBrandGroup($cat_id);
        $this->productCat = &$productCat;
        $cat = $productCat->get($cat_id,$view,$type_filter['type_id']);//echo "<pre>";print_r($cat);exit;
        if( !in_array($view,$cat['setting']['list_tpl'])){
            header('Location: '.$this->system->mkUrl('gallery',current($cat['setting']['list_tpl']),$this->pagedata['args']),true,301);
        }

        if($cat_type){
            $vcat['addon'] = unserialize($vcat['addon']);
            if(trim($vcat['addon']['meta']['keywords'])){
                $this->keywords = trim($vcat['addon']['meta']['keywords']);
            }
            if(trim($vcat['addon']['meta']['description'])){
                $this->desc = trim($vcat['addon']['meta']['description']);
            }
            if(trim($vcat['addon']['meta']['title'])){
                $this->title = trim($vcat['addon']['meta']['title']);
            }
        }else{
            if(trim($cat['addon'])){
                $cat['addon'] = unserialize($cat['addon']);
                if(trim($cat['addon']['meta']['keywords'])){
                    $this->keywords = trim($cat['addon']['meta']['keywords']);
                }
                if(trim($cat['addon']['meta']['description'])){
                    $this->desc = trim($cat['addon']['meta']['description']);
                }
                if(trim($cat['addon']['meta']['title'])){
                    $this->title = trim($cat['addon']['meta']['title']);
                }
            }
        }

        if($this->system->getConf('system.seo.noindex_catalog'))
        $this->header .= '<meta name="robots" content="noindex,noarchive,follow" />';

        $searchtools = &$this->system->loadModel('goods/search');
        $path =array();
        $filter = $searchtools->decode($urlFilter,$path,$cat);
		$GLOBALS['search_result'] = $filter['name'][0];
        $this->filter = &$filter;
		if($GLOBALS['search_result']){
			$this->title = $GLOBALS['search_result'].'__{ENV_shopname}';
		}
        /*if(!$cat_type){
        $this->title = $cat['cat_name'];
        }*/

        if(is_array($filter)){
            $filter=array_merge(array('cat_id'=>$cat_id,'marketable'=>'true'),$filter);
            if( ($filter['cat_id'][0] === '' || $filter['cat_id'][0] === null ) && !isset( $filter['cat_id'][1] ) )
            unset($filter['cat_id']);
            if( ($filter['tag'][0] === '' || $filter['tag'][0] === null ) && !isset( $filter['tag'][1] ) )
            unset($filter['tag']);
            if( ($filter['brand_id'][0] ==='' || $filter['brand_id'][0] === null) && !isset( $filter['brand_id'][1] ))
            unset($filter['brand_id']);
        }else{
            $filter = array('cat_id'=>$cat_id,'marketable'=>'true');
        }
        //--------获取类型关联的规格
        if ($vcat['type_id']){
            $type_id = $vcat['type_id'];
        }else{
            $type=$productCat->getFieldById($this->id,array('type_id'));
            $type_id=$type['type_id'];
        }
        $gType = &$this->system->loadModel('goods/gtype');
        $SpecList = $gType->getSpec($type_id,1);



        //--------
        foreach($path as $p){
            $arg = unserialize(serialize($this->pagedata['args']));
            $arg[1] = $p['str'];
            $title = array();
            if(is_numeric($p['type'])){
                $cat_tmp = $productCat->get($cat_id,$view,$filter['type_id'][0]);
                foreach($p['data'] as $i){
                    $name = $cat_tmp['props'][$p['type']]['options'][$i];
                    $title[] = $name?$name:$i;
                    $tip = $cat_tmp['props'][$p['type']]['name'];
                }
            }elseif($p['type']=='brand_id'){
                $brand = array();

                foreach($cat['brand'] as $b){
                    $brand[$b['brand_id']] = $b['brand_name'];
                }
                foreach($p['data'] as $i){
                    $title[] = $brand[$i];
                    $tip = __("品牌");
                }
                unset($brand);
            }elseif(substr($p['type'],0,2)=='s_'){
                $spec = array();
                foreach($p['data'] as $spk => $spv){
                    $tmp=explode(",",$spv);
                    $tip = $SpecList[$tmp[0]]['name'];
                    $title[]=$SpecList[$tmp[0]]['spec_value'][$tmp[1]]['spec_value'];
                }
                $curSpec[$tmp[0]]=$tmp[1];
            }
            $title = implode(',',$title);
            if($title){
                //$this->title=' '.$title;
                $this->path[] = array('title'=>" ".$title,'link'=>$this->system->mkUrl('gallery',$view,$arg),'tips'=>$tip);
            }
        }


        //-----------
        if($this->system->getConf('system.seo.noindex_catalog'))
        $this->header .= '<meta name="robots" content="noindex,noarchive,follow" />';

        $filter['cat_id'] = $cat_id;
        $filter['goods_type'] = 'normal';
        $filter['marketable'] = 'true';
        //-----查找当前类别子类别的关联类型ID
        if ($urlFilter){
            if($vcat['type_id']){
                //$filter['type_id']=$vcat['type_id'];
                $filter['type_id']=null;

            }
        }
        //--------
        $this->pagedata['tabs'] = $cat['tabs'];
        $this->pagedata['cat_id'] = implode(",",$cat_id);
        $this->pagedata['views'] = $cat['setting']['list_tpl'];

        $this->pagedata['orderBy'] = $objGoods->orderBy();
        if($cat['tabs'][$tab]){
            parse_str($cat['tabs'][$tab]['filter'],$_filter);
            $filter = array_merge($filter,$_filter);
        }
        if($GLOBALS['runtime']['member_lv']){
            $filter['mlevel'] = $GLOBALS['runtime']['member_lv'];
        }
        if(!isset($this->pagedata['orderBy'][$orderBy])){
            $this->system->error(404);
        }else{
            $orderby = $this->pagedata['orderBy'][$orderBy]['sql'];
        }

        foreach($brandResult as $v=>$k){
            $brand_count[$k['brand_id']]['plus']=$k['brand_cat'];
        }
        $selector = array();
        $search = array();

        if((!is_array($cat_id) && $cat_id) || $cat_id[0] || $cat_type){
            $goods_relate=$objGoods->getList("*",$filter,0,-1);
        }
        if ($goods_relate){
            unset($tmpSpecValue);
            foreach($goods_relate as $grk => $grv){
                if ($grv['spec_desc']){
                    $tmpSdesc=unserialize($grv['spec_desc']);
                    if(is_array($tmpSdesc)){
                        foreach($tmpSdesc as $tsk => $tsv){
                            foreach($tsv as $tk => $tv){
                                if (!in_array($tv['spec_value_id'],$tmpSpecValue))
                                $tmpSpecValue[]=$tv['spec_value_id'];
                            }
                        }
                    }
                }
            }
        }
        /***********************/
        if ($SpecList){
            if ($curSpec)
            $curSpecKey=array_keys($curSpec);
            foreach($SpecList as $spk => $spv){
                $selected=0;
                /*
                $existsSV=0;
                foreach($spv['spec_value'] as $spvk => $spvv){
                if (!in_array($spvk,$tmpSpecValue))
                unset($spv['spec_value'][$spvk]);
                else
                $existsSV=1;
                }
                if ($existsSV){*/
                if ($curSpecKey&&in_array($spk,$curSpecKey)){
                    $spv['spec_value'][$curSpec[$spk]]['selected']=true;
                    $selected=1;
                }
                if ($spv['spec_style']=="select"){ //下拉
                    $SpecSelList[$spk] = $spv;
                    if ($selected)
                    $SpecSelList[$spk]['selected'] = true;
                }
                elseif ($spv['spec_style']=="flat"){
                    $SpecFlatList[$spk] = $spv;
                    if ($selected)
                    $SpecFlatList[$spk]['selected'] = true;
                }
                //}
            }
        }
        $this->pagedata['SpecFlatList'] = $SpecFlatList;
        $this->pagedata['specimagewidth'] = $this->system->getConf('spec.image.width');
        $this->pagedata['specimageheight'] = $this->system->getConf('spec.image.height');
        /************************/
        if (is_array($cat['brand'])){
            foreach($cat['brand'] as $bk => $bv){
                $bCount=0;
                $brand = array('name'=>__('品牌'),'value'=>array_flip($filter['brand_id']));
                foreach($goods_relate as $gk => $gv){
                    if ($gv['brand_id']){
                        if ($gv['brand_id']==$bv['brand_id']){
                            $bCount++;
                        }
                    }
                }
                if ($bCount>0){
                    $tmpOp[$bv['brand_id']]=$bv['brand_name']."<span class='num'>(".$bCount.")</span>";
                }
            }
            $brand['options'] = $tmpOp;
            $selector['brand_id'] = $brand;
        }

        foreach($cat['props'] as $prop_id=>$prop){
            if($prop['search']=='select'){
                $prop['options'] = array_merge($prop['options']);
                $prop['value'] = $filter['p_'.$prop_id][0];
                $searchSelect[$prop_id] = $prop;
            }elseif($prop['search']=='input'){
                $prop['value'] = ($filter['p_'.$prop_id][0]);
                $searchInput[$prop_id] = $prop;
            }elseif($prop['search']=='nav'){
                $prop['value'] = array_flip($filter['p_'.$prop_id]);
                $plugadd=array();

                foreach($goods_relate as $k=>$v){

                    if($v["p_".$prop_id]!=null){

                        if($plugadd[$v["p_".$prop_id]]){
                            $plugadd[$v["p_".$prop_id]]=$plugadd[$v["p_".$prop_id]]+1;
                        }else{
                            $plugadd[$v["p_".$prop_id]]=1;
                        }
                    }
                    $aFilter['goods_id'][] = $v['goods_id'];    //当前的商品结果集
                }
                $navselector=0;
                foreach($prop['options'] as $q=>$e){
                    if($plugadd[$q]){
                        $prop['options'][$q]=$prop['options'][$q]."<span class='num'>(".$plugadd[$q].")</span>";
                        if (!$navselector)
                        $navselector=1;
                    }else{
                        unset($prop['options'][$q]);
                    }
                }
                $selector[$prop_id] = $prop;
            }
        }
        if ($navselector){
            $nsvcount=0;
            $noshow=0;
            foreach($selector as $sk => $sv){
                if ($sv['value']){
                    $nsvcount++;
                }
                if (is_numeric($sk)&&!$sv['show']){
                    $noshow++;
                }
            }
            if ($nsvcount==intval(count($selector)-$noshow))
            $navselector=0;
        }
        foreach($cat['spec'] as $spec_id=>$spec_name){
            $sId['spec_id'][] = $spec_id;
        }

        $cat['ordernum'] = $cat['ordernum']?$cat['ordernum']:array(''=>2);
        if ($cat['ordernum']){
            if ($selector){
                foreach($selector as $key => $val){
                    if(!in_array($key,$cat['ordernum'])&&$val){
                        $selectorExd[$key]=$val;
                    }
                }
            }
        }

        $selector['ordernum'] = $cat['ordernum'];
        $objGoods->appendCols .= ',big_pic';/*appendCols big_pic update 2009年9月25日13:46:45*/
        //$aProduct = $objGoods->getList(null,$filter,$pageLimit*($page-1),$pageLimit,$orderby);

        if($type_filter['type_id'])$filter['type_id'][] = $type_filter['type_id'];
           /* 修复前台开启价格区间搜索 无法搜索到多规格商品价格 begin*/
          if(empty($g['pdt_desc'])||empty($g['goods_id'])){
              if(is_array($filter['price'])){
                 $db = $this->system->database();
                 $p_filter = $filter;
                 unset($p_filter['goods_type']);
                 $filter_goods = $db->select("select DISTINCT goods_id from sdb_products where price >= ".$p_filter['price'][0]." AND price <= ".$p_filter['price'][1]." AND disabled = 'false' AND marketable='true'");
                 foreach($filter_goods as $k=>$v){
                     $f_goods[] = $v['goods_id'];
                 }
                 $filter_tmp['goods_id'] = $f_goods;
                 $aProduct = $objGoods->getList(null,$filter_tmp,$pageLimit*($page-1),$pageLimit,$orderby);
                 $count = $objGoods->count($filter_tmp);
              }else{
                 $aProduct = $objGoods->getList(null,$filter,$pageLimit*($page-1),$pageLimit,$orderby);
                 $count = $objGoods->count($filter);
              }
            /* 修复前台开启价格区间搜索 无法搜索到多规格商品价格 end*/
          }else{
               $shanxuan=$objGoods->filter_getList($g);/*前台搜索商品规格筛选*/
               if($shanxuan[0]['goods_id']){ 
                 $aProduct = $objGoods->getList(null,$filter,$pageLimit*($page-1),$pageLimit,$orderby);
                 $count = $objGoods->count($filter);
               }
          }

        //限时抢购
        $scareModel=new mdl_scare();
        foreach ($aProduct as $k=>$p_scare){
            $scareInfo=$scareModel->getFieldByGoodsId($p_scare['goods_id']);
            $fscareInfo=$scareInfo;
        
            if ($fscareInfo&&(strtotime('now')<$fscareInfo['s_time'])&&(strtotime('now')+$fscareInfo['forenotice_time'])>$fscareInfo['s_time']&&($fscareInfo['forenotice_on']==1)) {
            $aProduct[$k]['scareInfo_forenotice']['forenotice_time']=$this->tran_time($fscareInfo['s_time'],strtotime('now'));
            $aProduct[$k]['scareInfo_forenotice']['forenotice_key']=1;
            if ($fscareInfo['is_mprice']==1&&$_COOKIE['MLV']) {
                $scareMprice=unserialize($fscareInfo['scare_mprice']);
                $fscareInfo['scare_price']=$scareMprice[$_COOKIE['MLV']];
            }
            $aProduct[$k]['scareInfo_forenotice']['price']=$fscareInfo['scare_price'];
        }
       
            if ($scareInfo&&$scareInfo['iflimit']==1&&$scareInfo['e_time']>strtotime('now')&&$scareInfo['s_time']<strtotime('now')&&$scareInfo['scare_count']>0) {
                if ($scareInfo['is_mprice']&&$_COOKIE['MLV']) {
                    $scareMprice=unserialize($scareInfo['scare_mprice']);
                    $scareInfo['scare_price']=$scareMprice[$_COOKIE['MLV']];
                }
                //特定时间生效
                if ($scareInfo['is_special_time']==1) {
                    $specialTime=unserialize($scareInfo['special_time_bucket']);
                    foreach ($specialTime as $key=>$value) {
                        if ($value['week']==date('N')) {
                            $today_special_time[]=$value;//今天的限时抢购
                        }
                    }
                    if ($today_special_time) {
                        //求得最先开始的时间
                        foreach ($today_special_time as $key=>$tTime){
                            //过滤过时时间段
                            if (time()>=(strtotime(date('y-m-d'))+$tTime['ethour'])) {
                                continue;
                            }
                            if (time()>=(strtotime(date('Y-m-d'))+$tTime['sthour'])&&time()<(strtotime(date('Y-m-d'))+$tTime['ethour'])) {
                                if (!isset($today_min_stime)) {
                                    $today_min_stime=$today_special_time[$key]['sthour'];
                                    $today_min_stime_key=$key;
                                }
                                //$today_min_stime_key=($today_min_stime>$tTime['sthour']) ? $key : $initializeKey ;
                                if ($today_min_stime>$tTime['sthour']) {
                                    $today_min_stime=$tTime['sthour'];
                                    $today_min_stime_key=$key;
                                }
                            }

                        }
                        //$scareInfo['time']=strtotime(date('Y-m-d'))+$today_special_time[$today_min_stime_key]['ethour']-strtotime('now');
                        if (!isset($today_min_stime_key)) {
                            unset($scareInfo);
                        }
                    }else {
                        unset($scareInfo);
                    }
                }
                 if(ceil($scareInfo['scare_price'])==0){
             unset($scareInfo['scare_price']);
         }
                $aProduct[$k]['scareInfo']=$scareInfo;
            }
        }
        //end
         //  print_r($aProduct);exit;

        //$count = $objGoods->count($filter);
        $this->pagedata['mask_webslice'] = $this->system->getConf('system.ui.webslice')?' hslice':null;
        $this->pagedata['searchInput'] = &$searchInput;
        $this->pagedata['selectorExd'] = $selectorExd;
        $this->cat_id = $cat_id;
        $this->_plugins['function']['selector'] = array(&$this,'_selector');
        $this->pagedata['pager'] = array(
        'current'=>$page,
        'total'=>ceil($count/$pageLimit),
        'link'=>$this->system->mkUrl('gallery',$view,array(implode(',',$cat_id),urlencode($p['str']),$orderBy,$tab,($tmp = time()),$cat_type)),
        'token'=>$tmp);
        
        if($page != 1 && $page > $this->pagedata['pager']['total']){
            $this->system->error(404);
        }
        if(!$count){
            $this->pagedata['emtpy_info']=$this->system->getConf('errorpage.searchempty');
        }
        $objImage = &$this->system->loadModel('goods/gimage');
        $this->pagedata['searchtotal']=$count;
        if(is_array($aProduct) && count($aProduct) > 0){
            $objGoods->getSparePrice($aProduct, $GLOBALS['runtime']['member_lv']);
            if($this->system->getConf('site.show_mark_price')){
                $setting['mktprice'] = $this->system->getConf('site.market_price');
            }else{
                $setting['mktprice'] =0;
            }
            $setting['saveprice'] = $this->system->getConf('site.save_price');
            $setting['buytarget'] = $this->system->getConf('site.buy.target');
            $this->pagedata['setting'] = $setting;
            $this->pagedata['products'] = &$aProduct;
        }
        //print_r($this->pagedata['products']);exit;
        if($GLOBALS['runtime']['member_lv']<0){
            $this->pagedata['LOGIN'] = 'nologin';
        }
        if($SpecSelList){
            $this->pagedata['SpecSelList'] = $SpecSelList;
        }
        if($searchSelect){
            $this->pagedata['searchSelect'] = &$searchSelect;
        }
        $this->pagedata['selector'] = &$selector;
        $this->pagedata['cat_type'] = $cat_type;
        $this->pagedata['search_array'] = implode("+",$GLOBALS['search_array']);

        //$this->pagedata['_PDT_LST_TPL'] = 'file:'.$cat['tpl'];
        //$this->pagedata['_PDT_LST_TPL'] = 'file:D:\www\485_2\src\plugins\app\scareBuying\view\shop\gallery\type\list.html';
        //var_dump($this->pagedata['_PDT_LST_TPL']);exit;
        $s_view=($view=='index') ? 'list' : $view;
        $this->pagedata['_PDT_LST_TPL'] ='file:'.dirname(__FILE__).'/view/shop/gallery/type/'.$s_view.'.html';
        //var_dump(dirname(__FILE__).'/view/shop/gallery/type/'.$view.'.html');exit;
        //$this->pagedata['_MAIN_'] = 'gallery/index.html';

        $this->pagedata['_MAIN_'] = dirname(__FILE__).'/view/shop/gallery/index.html';
      
       $this->pagedata['localhostpic_url']=dirname('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);

        $this->getGlobal($this->seoTag,$this->pagedata);
        $this->output();
    }

function tran_time($t,$now){
        //$time['h']=intval($t/3600);
        //$time['m']=intval($t%3600/60);
        //$time['s']=$t%3600%60;
        //return $time;
        return date('Y@m@d@G@i@s',$t);
    }
}