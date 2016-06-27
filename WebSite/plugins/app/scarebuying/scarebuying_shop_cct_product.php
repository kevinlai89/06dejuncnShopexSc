<?php
require_once(CORE_DIR.'/shop/controller/ctl.product.php');
if (!class_exists('mdl_scare')) {
	require_once('mdl.scare.php');
}

class scarebuying_shop_cct_product extends ctl_product {

	//	function scareBuying_shop_cct_product(){
	//		parent::ctl_product();
	//		$this->template_dir = CORE_DIR.'/shop/view/';
	//	}
	//	function index($gid,$specImg='',$spec_id=''){
	//		parent::index($gid,$specImg,$spec_id);
	//	}
	function index($gid,$specImg='',$spec_id=''){
        
       $appmgr = $this->system->loadModel('system/appmgr');
        $status=$appmgr->getPluginInfoByident('group_activity','disabled');
        if($status['disabled']=='false'){
       $group_activity_model_test = $this->system->loadModel('plugins/group_activity/group_activity');
		$group_activity_model_test->chgStateByGid($gid);
		$goodsActivityInfo_test = $group_activity_model_test->getOpenActivityByGid($gid);
        }
      
        if($goodsActivityInfo_test){
         require_once(PLUGIN_DIR.'/app/group_activity/group_activity_shop_ctl_product.php');
         $group_activity_ctl_product=new group_activity_shop_ctl_product();
         $group_activity_ctl_product->index($gid,$specImg,$spec_id);
        }else{
           
		//限时抢购
		$scareModel=new mdl_scare();
		$scareInfo=$scareModel->getFieldByGoodsId($gid);
        //print_r($scareInfo);exit;
        
		$fscareInfo=$scareInfo;
		if ($scareInfo&&$scareInfo['iflimit']==1&&$scareInfo['e_time']>strtotime('now')&&$scareInfo['s_time']<strtotime('now')&&$scareInfo['scare_count']>0) {
			if ($scareInfo['is_mprice']==1&&$_COOKIE['MLV']) {
				$scareMprice=unserialize($scareInfo['scare_mprice']);
             
				$scareInfo['scare_price']=$scareMprice[$_COOKIE['MLV']];
                $this->pagedata['scareInfo_forenotice']['price']=$scareInfo['scare_price'];
			}
          

          
			
            
			$scareInfo['time']=$this->tran_time($scareInfo['e_time'],strtotime('now'));
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
						if (time()>=(strtotime(date('y-m-d'))+$tTime['ethour'])) {
							continue;
						}
						if (time()>=(strtotime(date('y-m-d'))+$tTime['sthour'])&&time()<(strtotime(date('y-m-d'))+$tTime['ethour'])) {
							if (!isset($today_min_stime)) {
								$today_min_stime=$today_special_time[$key]['sthour'];
								$today_min_stime_key=$key;
							}
							if ($today_min_stime>$tTime['sthour']) {
								$today_min_stime=$tTime['sthour'];
								$today_min_stime_key=$key;
							}
							//$today_min_stime=($today_min_stime>$tTime['sthour']) ? $tTime['sthour'] : $today_min_stime;
							//$today_min_stime_key=($today_min_stime>$tTime['sthour']) ? $key : $today_min_stime_key ;
						}
						if (time()<(strtotime(date('y-m-d'))+$tTime['sthour'])) {
							if (!isset($today_min_stime)) {
								$today_min_stime=$today_special_time[$key]['sthour'];
							}
							$today_min_stime=($today_min_stime>$tTime['sthour']) ? $tTime['sthour'] : $today_min_stime ;
						}

					}
					if (isset($today_min_stime_key)) {
						$scareInfo['time']=$this->tran_time((strtotime(date('Y-m-d'))+$today_special_time[$today_min_stime_key]['ethour']),strtotime('now'));
					}else {
						unset($scareInfo);
					}
				}else {
					unset($scareInfo);
				}
			}
         if(ceil($scareInfo['scare_price'])==0){
             unset($scareInfo['scare_price']);
         }
			$this->pagedata['scareInfo']=$scareInfo;
		}

		if ($today_min_stime&&!isset($today_min_stime_key)) {
			$fscareInfo['s_time']=strtotime(date('Y-m-d'))+$today_min_stime;
		}
      $this->pagedata['js_url']='http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/index.php?action_scarebuying.html';

		//限时抢购倒计时开始
		if ($fscareInfo&&(strtotime('now')<$fscareInfo['s_time'])&&(strtotime('now')+$fscareInfo['forenotice_time'])>$fscareInfo['s_time']&&($fscareInfo['forenotice_on']==1)) {
			$this->pagedata['scareInfo_forenotice']['forenotice_time']=$fscareInfo['s_time'];
			$this->pagedata['scareInfo_forenotice']['forenotice_key']=1;
			if ($fscareInfo['is_mprice']==1&&$_COOKIE['MLV']) {
				$scareMprice=unserialize($fscareInfo['scare_mprice']);
				$fscareInfo['scare_price']=$scareMprice[$_COOKIE['MLV']];
			}
			$this->pagedata['scareInfo_forenotice']['price']=$fscareInfo['scare_price'];
		}
       
        
        
		unset($fscareInfo);
         
		//end
		$this->pagedata['_MAIN_']=dirname(__FILE__).'/view/shop/product/index.html';
		//parent::index($gid,$specImg,$spec_id);
		$this->id = $gid;
		$this->customer_source_type='product';
		$this->customer_template_type='product';
		$this->customer_template_id=$gid;
		$oseo = &$this->system->loadModel('system/seo');
		$seo_info=$oseo->get_seo('goods',$gid);
		$this->title    = $seo_info['title']?$seo_info['title']:$this->system->getConf('site.goods_title');
		$this->keywords = $seo_info['keywords']?$seo_info['keywords']:$this->system->getConf('site.goods_meta_key_words');
		$this->desc     = $seo_info['descript']?$seo_info['descript']:$this->system->getConf('site.goods_meta_desc');

		$member_lv = intval($this->system->request['member_lv']);
		$objProduct = &$this->system->loadModel('goods/products');
		$objGoods = &$this->system->loadModel('trading/goods');
		if(!$aGoods = $objGoods->getGoods($gid,$member_lv)){
			$this->system->responseCode(404);
		}

		if($aGoods['goods_type'] == 'bind'){    //如果捆绑商品跳转到捆绑列表
			$this->redirect('package','index');
			exit;
		}
		if(!$aGoods || $aGoods['disabled'] == 'true' || (empty($aGoods['products']) && empty($aGoods['product_id']))){
			$this->system->error(404);
			exit;
		}

		$objCat = &$this->system->loadModel('goods/productCat');
		$aCat = $objCat->getFieldById($aGoods['cat_id'], array('cat_name','addon'));
		$aCat['addon'] = unserialize($aCat['addon']);
		if($aGoods['seo']['meta_keywords']){
			if(empty($this->keyWords))
			$this->keyWords = $aGoods['seo']['meta_keywords'];
		}else{
			if(trim($aCat['addon']['meta']['keywords'])){
				$this->keyWords = trim($aCat['addon']['meta']['keywords']);
			}
		}
		if($aGoods['seo']['meta_description']){
			$this->metaDesc = $aGoods['seo']['meta_description'];
		}else{
			if(trim($aCat['addon']['meta']['description'])){
				$this->metaDesc = trim($aCat['addon']['meta']['description']);
			}
		}
		$tTitle=(empty($aGoods['seo']['seo_title']) ? $aGoods['name'] : $aGoods['seo']['seo_title']).(empty($aCat['cat_name'])?"":" - ".$aCat['cat_name']);
		if(empty($this->title))
		$this->title = $tTitle;
		$objPdtFinder = &$this->system->loadModel('goods/finderPdt');
		foreach($aGoods['adjunct'] as $key => $rows){    //loop group
			if($rows['set_price'] == 'minus'){
				$cols = 'product_id,goods_id,name, pdt_desc, store, freez, price, price-'.intval($rows['price']).' AS adjprice';
			}else{
				$cols = 'product_id,goods_id,name, pdt_desc, store, freez, price, price*'.($rows['price']?$rows['price']:1).' AS adjprice';
			}
			if($rows['type'] == 'goods'){
				if(!$rows['items']['product_id']) $rows['items']['product_id'] = array(-1);
				$arr = $rows['items'];
			}else{
				parse_str($rows['items'].'&dis_goods[]='.$gid, $arr);
			}
			if($aAdj = $objPdtFinder->getList($cols, $arr, 0, -1)){
				$aAdjGid = array();
				foreach($aAdj as $item){
					$aAdjGid['goods_id'][] = $item['goods_id'];
				}
				if(!empty($aAdjGid)){
					foreach($objProduct->getList('marketable,disabled',$aAdjGid,0,1000) as $item){
						$aAdjGid[$item['goods_id']] = $item;
					}
					foreach($aAdj as $k => $item){
						$aAdj[$k]['marketable'] = $aAdjGid[$item['goods_id']]['marketable'];
						$aAdj[$k]['disabled'] = $aAdjGid[$item['goods_id']]['disabled'];
					}
				}
				$aGoods['adjunct'][$key]['items'] = $aAdj;
			}else{
				unset($aGoods['adjunct'][$key]);
			}
		}

		$this->_plugins['function']['selector'] = array(&$this,'_selector');

		//初始化货品

		if(!empty($aGoods['products'])){
			foreach($aGoods['products'] as $key => $products){
				$a = array();
				foreach($products['props']['spec'] as $k=>$v){
					$a[] = trim($k).':'.trim($v);
				}
				$aGoods['products'][$key]['params_tr'] = implode('-',$a);
				$aPdtIds[] = $products['product_id'];
				if($aGoods['price'] > $products['price']){
					$aGoods['price'] = $products['price'];//前台默认进来显示商品的最小价格
				}
			}
		}else{
			$aPdtIds[] = $aGoods['product_id'];
		}
		if($this->system->getConf('site.show_mark_price')){
			$aGoods['setting']['mktprice'] = $this->system->getConf('site.market_price');
		}else{
			$aGoods['setting']['mktprice'] = 0;
		}
		$aGoods['setting']['saveprice'] = $this->system->getConf('site.save_price');
		$aGoods['setting']['buytarget'] = $this->system->getConf('site.buy.target');
		$aGoods['setting']['score'] = $this->system->getConf('point.get_policy');
		$aGoods['setting']['scorerate'] = $this->system->getConf('point.get_rate');
		if($aGoods['setting']['score'] == 1){
			$aGoods['score'] = intval($aGoods['price'] * $aGoods['setting']['scorerate']);
		}

		/*--------------规格关联商品图片--------------*/
		if (!empty($specImg)){
			$tmpImgAry=explode("_",$specImg);
			if (is_array($tmpImgAry)){
				foreach($tmpImgAry as $key => $val){
					$tImgAry = explode("@",$val);
					if (is_array($tImgAry)){
						$spec[$tImgAry[0]]=$val;
						$imageGroup[]=substr($tImgAry[1],0,strpos($tImgAry[1],"|"));
						$imageGstr .= substr($tImgAry[1],0,strpos($tImgAry[1],"|")).",";
						$spec_value_id = substr($tImgAry[1],strpos($tImgAry[1],"|")+1);
						if ($aGoods['specVdesc'][$tImgAry[0]]['value'][$spec_value_id]['spec_value'])
						$specValue[]=$aGoods['specVdesc'][$tImgAry[0]]['value'][$spec_value_id]['spec_value'];
						if ($aGoods['FlatSpec']&&array_key_exists($tImgAry[0],$aGoods['FlatSpec']))
						$aGoods['FlatSpec'][$tImgAry[0]]['value'][$spec_value_id]['selected']=true;
						if ($aGoods['SelSpec']&&array_key_exists($tImgAry[0],$aGoods['SelSpec']))
						$aGoods['SelSpec'][$tImgAry[0]]['value'][$spec_value_id]['selected']=true;
					}
				}
				if ($imageGstr){
					$imageGstr=substr($imageGstr,0,-1);
				}
			}

			/****************设置规格链接地址**********************/
			if (is_array($aGoods['specVdesc'])){
				foreach($aGoods['specVdesc'] as $gk => $gv){
					if (is_array($gv['value'])){
						foreach($gv['value'] as $gkk => $gvv){
							if(is_array($spec)){
								$specId = substr($gvv['spec_goods_images'],0,strpos($gvv['spec_goods_images'],"@"));
								foreach($spec as $sk => $sv){
									if ($specId != $sk){
										$aGoods['specVdesc'][$gk]['value'][$gkk]['spec_goods_images'].="_".$sv;
										if ($aGoods['FlatSpec']&&array_key_exists($gk,$aGoods['FlatSpec'])){
											$aGoods['FlatSpec'][$gk]['value'][$gkk]['spec_goods_images'].="_".$sv;
										}
										if ($aGoods['SelSpec']&&array_key_exists($gk,$aGoods['SelSpec'])){
											$aGoods['SelSpec'][$gk]['value'][$gkk]['spec_goods_images'].="_".$sv;
										}
									}
								}
							}
						}
					}
				}
			}

			/*************************************/
			//页面提示所选规格名称
			$this->pagedata['SelectSpecValue'] = array('value'=>implode("、",$specValue),'selected'=>1);
		}
		else{
			if (is_array($aGoods['FlatSpec'])&&count($aGoods['FlatSpec'])>0){
				foreach($aGoods['FlatSpec'] as $agk => $agv){
					$specValue[]=$agv['name'];
				}
			}
			if (is_array($aGoods['SelSpec'])&&count($aGoods['SelSpec'])>0){
				foreach($aGoods['SelSpec'] as $agk => $agv){
					$specValue[]=$agv['name'];
				}
			}
			$this->pagedata['SelectSpecValue'] = array('value'=>implode("、",$specValue),'selected'=>0);
		}


		$this->pagedata['specShowItems'] =$specValue;
		/*--------------*/
		//$gImages=$this->goodspics($gid,$imageGroup,$imageGstr);
		if (is_array($gImages)&&count($gImages)>0){
			$this->pagedata['images']['gimages'] = $gImages;
		}
		else{
			/*-------------商品图片--------------*/
			$gimage = &$this->system->loadModel('goods/gimage');
			$this->pagedata['images']['gimages'] = $gimage->get_by_goods_id($gid);
			/*----------------- 8< --------------*/
		}



		//限时抢购
		if ($this->pagedata['scareInfo']) {
			foreach ($aGoods['product2spec'] as $k=>$ps) {
				$aGoods['product2spec'][$k]['store']=$this->pagedata['scareInfo']['scare_count'];
				//$aGoods['product2spec'][$k]['price']=$this->pagedata['scareInfo']['scare_price'];
			}
		}

		//end

		/********-------------------*********/
		$aGoods['product2spec'] = json_encode( $aGoods['product2spec'] );

		$aGoods['spec2product'] = json_encode( $aGoods['spec2product'] );
		$this->pagedata['goods'] = $aGoods;

		if ($this->pagedata['goods']['products']){
			$priceArea = array();

			if ($_COOKIE['MLV'])
			$MLV = $_COOKIE['MLV'];
			else{
				$level=&$this->system->loadModel('member/level');
				$MLV=$level->getDefauleLv();
			}
			if ($MLV){
				foreach($this->pagedata['goods']['products'] as $gpk => $gpv){
					$priceArea[]=$gpv['mprice'][$MLV];
					$mktpriceArea[]=$gpv['mktprice'];
				}
				if (count($priceArea)>1){
					$minprice = min($priceArea);
					$maxprice = max($priceArea);
					if ($minprice<>$maxprice){
						$this->pagedata['goods']['minprice'] = $minprice;
						$this->pagedata['goods']['maxprice'] = $maxprice;
					}
				}
				if (count($mktpriceArea)>1){
					$mktminprice = min($mktpriceArea);
					$mktmaxprice = max($mktpriceArea);
					if ($mktminprice<>$mktmaxprice){
						$this->pagedata['goods']['minmktprice'] = $mktminprice;
						$this->pagedata['goods']['maxmktprice'] = $mktmaxprice;

					}
				}                                                               //增加了市场价范围
			}
			//计算货品冻结库存总和
			foreach($this->pagedata['goods']['products'] as $key => $val){
				$totalFreez += $val['freez'];
			}

		}
		else{
			$totalFreez = $this->pagedata['goods']['freez'];
		}
		$mLevelList = $objProduct->getProductLevel($aPdtIds);
		$this->pagedata['mLevel'] = $mLevelList;

		/**** begin 商品品牌 ****/
		if($this->pagedata['goods']['brand_id'] > 0){
			$brandObj = &$this->system->loadModel('goods/brand');
			$aBrand = $brandObj->getFieldById($this->pagedata['goods']['brand_id'], array('brand_name'));
		}
		$this->pagedata['goods']['brand_name'] = $aBrand['brand_name'];
		/**** begin 商品品牌 ****/

		/**** begin 商品评论 ****/
		$aComment['switch']['ask'] = $this->system->getConf('comment.switch.ask');
		$aComment['switch']['discuss'] = $this->system->getConf('comment.switch.discuss');
		foreach($aComment['switch'] as $item => $switchStatus){
			if($switchStatus == 'on'){
				$objComment= &$this->system->loadModel('comment/comment');
				$commentList = $objComment->getGoodsIndexComments($gid, $item);
				$aComment['list'][$item] = $commentList['data'];
				$aComment[$item.'Count'] = $commentList['total'];
				$aId = array();
				if ($commentList['total']){
					foreach($aComment['list'][$item] as $rows){
						$aId[] = $rows['comment_id'];
					}
					if(count($aId)) $aReply = $objComment->getCommentsReply($aId, true);
					reset($aComment['list'][$item]);
					foreach($aComment['list'][$item] as $key => $rows){
						foreach($aReply as $rkey => $rrows){
							if($rows['comment_id'] == $rrows['for_comment_id']){
								$aComment['list'][$item][$key]['items'][] = $aReply[$rkey];
							}
						}
						reset($aReply);
					}
				}else{
					$aComment['null_notice'][$item] = $this->system->getConf('comment.null_notice.'.$item);;
				}
			}
		}
		$this->pagedata['comment'] = $aComment;
		/**** end 商品评论 ****/

		/**** begin 相关商品 ****/
		$aLinkId['goods_id'] = array();
		foreach($objGoods->getLinkList($gid) as $rows){
			if($rows['goods_1']==$gid) $aLinkId['goods_id'][] = $rows['goods_2'];
			else $aLinkId['goods_id'][] = $rows['goods_1'];
		}
		if(count($aLinkId['goods_id'])>0){
			$aLinkId['marketable'] = 'true';
			$objProduct = &$this->system->loadModel('goods/products');
			$this->pagedata['goods']['link'] = $objProduct->getList('*',$aLinkId,0,500);
			$this->pagedata['goods']['link_count'] = $objProduct->count($aLinkId);
		}
		/**** end 相关商品 ****/

		//更多商品促销活动
		$PRICE = $this->pagedata['goods']['price'];//todo 此处PRICE 为会员价格,需要统一接口
		$oPromotion = &$this->system->loadModel('trading/promotion');
		$aPmt = $oPromotion->getGoodsPromotion($gid, $this->pagedata['goods']['cat_id'], $this->pagedata['goods']['brand_id'], $member_lv);

		if ($aPmt){
			$this->pagedata['goods']['pmt_id'] = $aPmt['pmt_id'];
			$arr= $oPromotion->getPromotionList($aPmt['pmta_id']);
			$MLV = $_COOKIE['MLV'];
			foreach($arr as $keys=>$vals)
			{
				$arr[$keys]['pmt_solution']=unserialize($arr[$keys]['pmt_solution']);
				if(!in_array($MLV,$arr[$keys]['pmt_solution']['condition'][0][1]))
				unset($arr[$keys]);

			}
			$this->pagedata['promotions']=$arr;
			$aTrading = array (
			'price' => $this->pagedata['goods']['price'],
			'score' => $this->pagedata['goods']['score'],
			'gift'  => array(),
			'coupon' => array()
			);
			$oPromotion->apply_single_pdt_pmt($aTrading, unserialize($aPmt['pmt_solution']),$member_lv);
			$oGift = &$this->system->loadModel('trading/gift');
			if (!empty($aTrading['gift'])) {
				$this->pagedata['gift'] = $oGift->getGiftByIds($aTrading['gift']);
			}
			$oCoupon = &$this->system->loadModel('trading/coupon');
			if (!empty($aTrading['coupon'])) {
				$this->pagedata['coupon'] = $oCoupon->getCouponByIds($aTrading['coupon']);
			}
			$this->pagedata['trading'] = $aTrading;
		}
		$oPackage = &$this->system->loadModel('trading/package');
		if (!empty($aPdtIds)) {
			$aPkgList = $oPackage->findPmtPkg($aPdtIds);
			foreach($aPkgList as $k => $row){
				$aPkgList[$k]['items'] = $oPackage->getPackageProducts($row['goods_id']);
			}
			$this->pagedata['package'] = $aPkgList;
		}
		if($GLOBALS['runtime']['member_lv']<0){
			$this->pagedata['login'] = 'nologin';
		}
		$cur = &$this->system->loadModel('system/cur');
		$this->pagedata['readingGlass'] = $this->system->getConf('site.reading_glass');
		$this->pagedata['readingGlassWidth'] = $this->system->getConf('site.reading_glass_width');
		$this->pagedata['readingGlassHeight'] = $this->system->getConf('site.reading_glass_height');

		$sellLogList = $objProduct->getGoodsSellLogList($gid,0,$this->system->getConf('selllog.display.listnum'));
		$sellLogSetting['display'] = array(
		'switch'=>$this->system->getConf('selllog.display.switch') ,
		'limit'=>$this->system->getConf('selllog.display.limit') ,
		'listnum'=>$this->system->getConf('selllog.display.listnum')
		);

		$this->pagedata['goods']['product_freez'] = $totalFreez;
		$this->pagedata['sellLog'] = $sellLogSetting;
		$this->pagedata['sellLogList'] = $sellLogList;
		$this->pagedata['money_format'] = json_encode($cur->getFormat($this->system->request['cur']));
		$this->pagedata['askshow'] = $this->system->getConf('comment.verifyCode.ask');
		$this->pagedata['goodsBnShow'] = $this->system->getConf('goodsbn.display.switch');
		$this->pagedata['discussshow'] = $this->system->getConf('comment.verifyCode.discuss');
		$this->pagedata['showStorage'] = intval($this->system->getConf('site.show_storage'));
		$this->pagedata['specimagewidth'] = $this->system->getConf('spec.image.width');
		$this->pagedata['specimageheight'] = $this->system->getConf('spec.image.height');
		$this->pagedata['goodsproplink'] = $this->system->getConf('goodsprop.display.switch');
		$this->pagedata['goodspropposition'] = $this->system->getConf('goodsprop.display.position');
		$this->getGlobal($this->seoTag,$this->pagedata);

		$GLOBALS['pageinfo']['goods'] = &$GLOBALS['runtime']['goods_name'];
		$GLOBALS['pageinfo']['brand'] = &$GLOBALS['runtime']['brand'];
		$GLOBALS['pageinfo']['gcat'] = &$GLOBALS['runtime']['goods_cat'];
        
		$this->output();
        }




	}
	function product_index(&$content){

	}

	function tran_time($t,$now){
		//$time['h']=intval($t/3600);
		//$time['m']=intval($t%3600/60);
		//$time['s']=$t%3600%60;
		//return $time;
		return date('Y@m@d@G@i@s',$t);
	}

}