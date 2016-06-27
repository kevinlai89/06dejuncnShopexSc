<?php
define('CORE_MODEL_DIR',CORE_DIR.
            ((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'/model_v5':'/model'));
require_once(CORE_MODEL_DIR.'/trading/mdl.sale.php');
if (!class_exists('mdl_scare')) {
	require_once('mdl.scare.php');
}
class sale_mdl extends mdl_sale{

	//读取商品
	function _load_g(&$trading, $cart_g) {
		$oMath = &$this->system->loadModel('system/math');
		$product_ids = array();
		foreach($cart_g['cart'] as $strKey => $num) {
			$aRow = explode('-', $strKey);
			if($aRow[1] != '') $product_ids[] = $aRow[1];
		}
		reset($cart_g);
		if(count($product_ids)>0){
			$aProduct = $this->db->select('SELECT p.*,t.setting,g.score,g.brand_id,g.cat_id,g.type_id,g.image_default,g.thumbnail_pic,g.goods_id
                    FROM sdb_products AS p
                    LEFT JOIN sdb_goods AS g ON p.goods_id=g.goods_id
                    LEFT JOIN sdb_goods_type AS t ON g.type_id=t.type_id
                    WHERE p.product_id IN ('.implode(',',$product_ids).')');
		}else{
			$aProduct = array();
		}

		foreach($aProduct as $k=>$p){
			if($this->_mlvid){
				$aMprice = $this->db->selectrow('SELECT price, dis_count FROM sdb_member_lv
                        LEFT JOIN sdb_goods_lv_price ON level_id = member_lv_id AND product_id='.intval($p['product_id']).'
                        WHERE member_lv_id='.intval($this->_mlvid));
				if(floatval($aMprice['dis_count']) <= 0){
					$aMprice['dis_count'] = 1;
				}
			}else{
				$aMprice['dis_count'] = 1;
			}
			$items_g[$p['product_id']]['bn'] = $p['bn']?$p['bn']:($this->system->getConf('system.product.autobn.prefix').str_pad($p['product_id']+$this->system->getConf('system.product.autobn.beginnum',100),$this->system->getConf('system.product.autobn.length'),0,STR_PAD_LEFT));

			$items_g[$p['product_id']]['sale_price'] = $p['price'];

			//限时抢购
			$scareModel=new mdl_scare();
			$scareInfo=$scareModel->getFieldByGoodsId($p['goods_id']);
			//特定时间生效
			if ($scareInfo&&$scareInfo['iflimit']==1&&$scareInfo['e_time']>strtotime('now')&&$scareInfo['s_time']<strtotime('now')&&$scareInfo['is_special_time']==1&&$scareInfo['scare_count']>0) {
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
			//计算限时抢购价
			if ($scareInfo&&$scareInfo['iflimit']==1&&$scareInfo['e_time']>strtotime('now')&&$scareInfo['s_time']<strtotime('now')&&$scareInfo['scare_count']>0) {
				$items_g[$p['product_id']]['name'] = $p['name'].'(限时抢购商品)'.'@|'.$scareInfo['goods_id'].'@|';
				if (!is_null($scareInfo['goodscore'])&&$scareInfo['goodscore']!='') {
					$items_g[$p['product_id']]['limitGoodsScore'] = $scareInfo['goodscore'];
				}
				if ($scareInfo['is_mprice']==1) {
					$scareMprice=unserialize($scareInfo['scare_mprice']);
					$items_g[$p['product_id']]['price']=($scareMprice[$this->_mlvid] ? $scareMprice[$this->_mlvid] : $oMath->getOperationNumber( $oMath->getOperationNumber($scareInfo['scare_price'])*$aMprice['dis_count'] ));
				}else {
					$items_g[$p['product_id']]['price']=$scareInfo['scare_price'];
				}
			}else{
				$items_g[$p['product_id']]['name'] = $p['name'].($p['pdt_desc']?' ('.$p['pdt_desc'].')':'');

				$items_g[$p['product_id']]['price'] = ($aMprice['price'] ? $aMprice['price'] : $oMath->getOperationNumber( $oMath->getOperationNumber($p['price'])*$aMprice['dis_count'] ));
			}
			//end

			$items_g[$p['product_id']]['type_id'] = $p['type_id'];
			$items_g[$p['product_id']]['weight'] = $p['weight'];
			$items_g[$p['product_id']]['store'] = $p['store'];
			$items_g[$p['product_id']]['cost'] = $p['cost'];
			$items_g[$p['product_id']]['addon'] = array_merge(unserialize($p['props']),unserialize($p['setting']));
			$items_g[$p['product_id']]['pdt_desc'] = $p['pdt_desc'];
			$items_g[$p['product_id']]['goods_id'] = $p['goods_id'];
			$items_g[$p['product_id']]['product_id'] = $p['product_id'];
			$items_g[$p['product_id']]['image_default'] = $p['image_default'];
			$items_g[$p['product_id']]['thumbnail_pic'] = $p['thumbnail_pic'];
			$items_g[$p['product_id']]['score'] = $p['score'];
		}
		unset($aProduct);

		$oCur = &$this->system->loadModel('system/cur');
		$aItems = array();
		//循环处理购物车各商品
		foreach($cart_g['cart'] as $strKey => $num){
			$aRow = explode('-', $strKey);
			//如果存在商品id
			if($aRow[0] != '' && $items_g[$aRow[1]]){
				$strName = '';    //初始化显示名称
				$adjPrice = 0;    //初始化配件价格
				$adjCost = 0;     //初始化配件成本价格
				$adjWeight = 0;
				//如果存在配件
				$adjList = array();    //初始化配件购买量数组 array(<配件id>=><购买量>)
				if($aRow[2] != 'na'){
					$aAdj = explode('|', $aRow[2]);    //取配件配置列表,配件格式：配件id_配件组_配件数量|
					$tmpAdjList = array();
					$tmpAdjGrp = array();
					$tmpAdjId = array();    //初始化配件id数组(<product_id>=>array(<id1>,<id2>...))
					$strAdj = '';
					foreach($aAdj as $val){
						$adjItem = explode('_', $val);
						if($adjItem[0]>0 && $adjItem[2]>0){
							$tmpAdjList[] = $adjItem[2];    //设置配件购买量
							$tmpAdjGrp[] = $adjItem[1];        //配件栏位
							$tmpAdjId['product_id'][] = $adjItem[0];    //配件id数组
						}
					}
					if(count($tmpAdjId)){
						$objGoods = &$this->system->loadModel('trading/goods');
						$strAdjunct = $objGoods->getGoodsMemo($aRow[0], 'adjunct');    //取本商品配件栏位定义
						$aAdj = unserialize($strAdjunct);
						$objProduct = &$this->system->loadModel('goods/finderPdt');
						$adjName = $objProduct->getList('product_id, name, price, cost, weight', $tmpAdjId, 0, -1);
						if($adjName){
							foreach($adjName as $val){  //处理各配件
								$aAdjuncts[$val['product_id']] = $val;
							}
							foreach($tmpAdjId['product_id'] as $key => $pid){
								if($aAdj[$tmpAdjGrp[$key]] && $aAdjuncts[$pid]){    //如果存在对应栏位定义 和 商品库中存在这个配件
									//描述文字 = +配件名称(购买量)
									$strName .= '+'.$aAdjuncts[$pid]['name'].'('.$tmpAdjList[$key].')';
									//如果本栏位设定了配件价格的优惠金额
									if($aAdj[$tmpAdjGrp[$key]]['set_price'] == 'minus'){
										//配件总价 += (配件销售价+设定的配件调整额)*配件购买量
										$adjPrice += $oMath->minus( array(
										$aAdjuncts[$pid]['price'],
										$aAdj[$tmpAdjGrp[$key]]['price'] )
										) *
										$tmpAdjList[$key] ;
									}else{
										//否则即为折扣方式
										//配件总价 += 配件销售价*购买量*设定的优惠倍率
										$adjDiscount = abs($aAdj[$tmpAdjGrp[$key]]['price']) ? abs($aAdj[$tmpAdjGrp[$key]]['price']) : 1;
										$adjPrice += $oMath->getOperationNumber( $oMath->getOperationNumber( $aAdjuncts[$pid]['price'] ) * $tmpAdjList[$key] * $adjDiscount );
									}
									$adjList[$pid] += $tmpAdjList[$key];
									$strAdj .= $pid.'_'.$tmpAdjGrp[$key].'_'.$tmpAdjList[$key].'|';
									$adjCost += ($oMath->getOperationNumber( $aAdjuncts[$pid]['cost'] ) * $tmpAdjList[$key]);
									$adjWeight += ($aAdjuncts[$pid]['weight'] * $tmpAdjList[$key]);
								}
							}
						}else{
							$strAdj = 'na';
						}
					}else{
						$strAdj = 'na';
					}
				}else{
					$strAdj = 'na';
				}

				$strKey = $aRow[0].'-'.$aRow[1].'-'.$strAdj;
				$linkKey = $aRow[0].'@'.$aRow[1].'@'.$strAdj;
				//重组货品字符串
				$aGoods = $items_g[$aRow[1]];
				$aGoods['addon']['adjinfo'] = $strAdj;    //= 配件id_配件组序号_配件数量|
				$aGoods['addon']['adjname'] = $strName;    //用于描述文字 +配件名称(购买量)
				$aGoods['adjList'] = $adjList;    //array(<配件id>=><购买量>)
				$aGoods['price'] = $oMath->plus( array( $aGoods['price'] , $adjPrice ) );    //+配件总价
				$aGoods['cost'] = $oMath->plus( array( $aGoods['cost'] , $adjCost ) );    //+配件总成本价
				$aGoods['amount'] = $oMath->getOperationNumber($aGoods['price']) * $num;
				$aGoods['price'] = $oCur->formatNumber($aGoods['price'], false);
				$aGoods['key'] = $strKey;        //存在购物车数组的key
				$aGoods['link_key'] = $linkKey;        //存在购物车数组的key
				$aGoods['enkey'] =  base64_encode($strKey);
				$aGoods['nums'] = $num;
				$aGoods['pmt_id'] = intval($cart_g['pmt'][$aRow[0]]);
				$aGoods['weight'] += $adjWeight;
				switch ($this->system->getConf('point.get_policy')) {
					case 0:
						$aGoods['score'] = 0;
						break;
					case 1:
						$aGoods['score'] = $aGoods['price'] * $this->system->getConf('point.get_rate');
						break;
					default:
						break;
				}
				//限时抢购商品积分
			 	if (isset($aGoods['limitGoodsScore'])) {
			 		$aGoods['score'] = $aGoods['limitGoodsScore'];
			 		unset($aGoods['limitGoodsScore']);
			 	}
				//end
				$aItems[] = $aGoods;

				$trading['totalPrice'] = $oMath->plus( array( $trading['totalPrice'],  $aGoods['amount'] ) );
				$trading['totalGainScore'] += $aGoods['score'] * $num;
				$trading['weight'] += $aGoods['weight'] * $num;
			}
		}

		unset($items_g);
		$trading['products'] = &$aItems;



	}

}
?>