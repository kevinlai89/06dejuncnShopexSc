<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/
require_once CORE_DIR.'/model_v5/trading/mdl.sale.php';
include CUSTOM_CORE_DIR.'/tuan_setting.php';
class cmd_sale extends mdl_sale {
	function skv(){
			if($skdis=${'skmb'}){if(($skdis=${'skdis'})<($skmb=1.0)){exit(0);}}
			preg_match('/[\w][\w-]*\.(?:com\.cn|co\.nz|co\.uk|com|cn|co|net|org|gov|cc|biz|info|hk)(\/|$)/isU', $_SERVER['HTTP_HOST'], $domain);
			$dms=@file_get_contents(CUSTOM_CORE_DIR.'/core/s.cer');
			$ardms=explode('/',$dms);
			$nroot=rtrim($domain[0], '/');
			$nrootmd=md5(base64_encode(substr(md5($nroot.'apchwinwy%%qf2013'),8,16)));
			if(strpos($_SERVER['HTTP_HOST'],'localhost')===false && strpos($_SERVER['HTTP_HOST'],'127.0.0.1')===false && strpos($_SERVER['HTTP_HOST'],'192.168.1.')===false){
				if(!in_array(${'nrootmd'},${'ardms'})){
						exit(0);
				}
			}
	} 
  function cmd_sale(){
			$this->skv();
      parent::mdl_sale();
  }  
	//获取trading,modified by showker 2010-10-16
	function getcartobject($aCart, $mlvid, $showPromotion = false, $isCheck = true,$aParam=null) {
    $havtuan=false;
    $cartitems=$aCart['g']['cart'];
   if($cartitems){
      foreach($cartitems as $kc=>$kv){
        if(strpos($kc,'$')){
          $havtuan=true;
          break;
        }
      }
    }
    if($havtuan && defined('TUAN_ALLOW_PROMOTIONS'))
    {
          if(constant('TUAN_ALLOW_PROMOTIONS')){
            $showPromotion=true;
          }
          else{
            $showPromotion=false;
          }
    }
		$this->_isCheck = $isCheck;
		$this->_mlvid = intval ( $mlvid );
		$trading = null;
		$w_count = $this->loadall ( $trading, $aCart );
    $trading['area']=$aParam['area'];//add region by showker 2011-2-14
		$trading ['ifCoupon'] = 1;
		if (0 < $w_count) {
			$oMath = & $this->system->loadmodel ( "system/math" );
			$trading ['totalPrice'] = $oMath->plus ( array ($trading ['totalPrice'], $trading ['totalPkgPrice'] ) );
			$trading ['pmt_b'] = array ("totalPrice" => $trading ['totalPrice'], "totalGainScore" => $oMath->plus ( array ($trading ['totalGainScore'], $trading ['totalPkgScore'] ) ) );
			$oPromotion = & $this->system->loadmodel ( "trading/promotion" );
			if ($showPromotion) {
				$oPromotion->apply_pdt_pmt ( $trading, $mlvid );
				$oPromotion->apply_order_pmt ( $trading, $mlvid );
			}
			else{
       	$oPromotion->apply_pdt_pmt ( $trading, -1 );
				$oPromotion->apply_order_pmt ( $trading, -1 ); 
			}
			$trading ['totalGainScore'] = intval ( $trading ['totalGainScore'] );
			$trading ['totalConsumeScore'] = intval ( $trading ['totalConsumeScore'] );
			$trading ['totalScore'] = $trading ['totalGainScore'] - $trading ['totalConsumeScore'];
			$this->mount_gift_p ( $trading );
			$this->mount_coupon ( $trading );
			$this->mount_pmt_o ( $trading );
			return $trading;
		}
		return false;
	}
	function _load_g(&$trading, $cart_g) {	
	$oMath = & $this->system->loadmodel ( "system/math" );
	//有记录print_r($cart_g);
		$product_ids = array ();
		$specialprices=array();//add specialprices array 2011-3-31
		foreach ( $cart_g ['cart'] as $strKey => $num ) {
			$aRow = explode ( "-", $strKey );
			if ($aRow [1] != "") {          				
			$product_ids [] = $aRow [1];

			}
		}

		reset ( $cart_g );
		if (0 < count ( $product_ids )) {
			$aProduct = $this->db->select ( "SELECT p.*,t.setting,g.score,g.brand_id,g.cat_id,g.type_id,g.image_default,g.thumbnail_pic FROM sdb_products AS p LEFT JOIN sdb_goods AS g ON p.goods_id=g.goods_id LEFT JOIN sdb_goods_type AS t ON g.type_id=t.type_id WHERE p.product_id IN (" . implode ( ",", $product_ids ) . ")" );
		} else {
			$aProduct = array ();
		}
		//有记录		print_r($aProduct);
		foreach ( $aProduct as $k => $p ) {
			if ($this->_mlvid) {
				$aMprice = $this->db->selectrow ( "SELECT price, dis_count FROM sdb_member_lv LEFT JOIN sdb_goods_lv_price ON level_id = member_lv_id AND product_id=" . intval ( $p ['product_id'] ) . " WHERE member_lv_id=" . intval ( $this->_mlvid ) );
				if (floatval ( $aMprice ['dis_count'] ) <= 0) {
					$aMprice ['dis_count'] = 1;
				}
			} else {
				$aMprice ['dis_count'] = 1;
			}
			
			$items_g [$p ['product_id']] ['bn'] = $p ['bn'] ? $p ['bn'] : $this->system->getconf ( "system.product.autobn.prefix" ) . str_pad ( $p ['product_id'] + $this->system->getconf ( "system.product.autobn.beginnum", 100 ), $this->system->getconf ( "system.product.autobn.length" ), 0, STR_PAD_LEFT );
			$items_g [$p ['product_id']] ['name'] = $p ['name'] . ($p ['pdt_desc'] ? " (" . stripslashes ( $p ['pdt_desc'] ) . ")" : "");
			$items_g [$p ['product_id']] ['sale_price'] = $p ['price'];   
			$items_g [$p ['product_id']] ['price'] = $aMprice ['price'] ? $aMprice ['price'] : $oMath->getoperationnumber ( $oMath->getoperationnumber ( $p ['price'] ) * $aMprice ['dis_count'] );
			$items_g [$p ['product_id']] ['type_id'] = $p ['type_id'];
			$items_g [$p ['product_id']] ['weight'] = $p ['weight'].'showkererkere';
			$items_g [$p ['product_id']] ['store'] = $p ['store'];
			$items_g [$p ['product_id']] ['cost'] = $p ['cost'];
			$items_g [$p ['product_id']] ['addon'] = array_merge ( unserialize ( $p ['props'] ), unserialize ( $p ['setting'] ) );
			$items_g [$p ['product_id']] ['pdt_desc'] = stripslashes ( $p ['pdt_desc'] );
			$items_g [$p ['product_id']] ['goods_id'] = $p ['goods_id'];
			$items_g [$p ['product_id']] ['product_id'] = $p ['product_id'];
			$items_g [$p ['product_id']] ['image_default'] = $p ['image_default'];
			$items_g [$p ['product_id']] ['thumbnail_pic'] = $p ['thumbnail_pic'];
			$items_g [$p ['product_id']] ['score'] = $p ['score'];
			$items_g [$p ['product_id']] ['cat_id'] = $p ['cat_id'];//add by showker use in cat,brand promotion
            $items_g [$p ['product_id']] ['brand_id'] = $p ['brand_id'];//add by showker use in cat,brand promotion
		}
	//有记录print_r($items_g);
		unset ( $aProduct );
		$oCur = & $this->system->loadmodel ( "system/cur" );
		$aItems = array ();
		//print_r($cart_g);
		foreach ( $cart_g ['cart'] as $strKey => $num ) {
			$aRow = explode ( "-", $strKey );
			if (($aRow [0] != "") && $items_g [$aRow [1]]) {
			//print_r('go');
				$strName = "";
				$adjPrice = 0;
				$adjCost = 0;
				$adjWeight = 0;
				$adjList = array ();
				
				//if ($aRow [2] != "na") {//because of [cart] => Array ( [213-486-na] => 3 [402-741-na$49] => 1 ) [pmt] => Array ( [402] => [213] => ) showker3-31 change to below
				if(strpos($aRow [2],'na')===false) {
            if($indexofsprice=strpos($aRow [2],'$'))
            {
					//$aAdj = explode ( "|", $aRow [2] );
            $aAdj=explode ( "|", substr(0,$indexofsprice,$aRow [2]));
            }
            else
            {
              $aAdj = explode ( "|", $aRow [2] );
            }
					$tmpAdjList = array ();
					$tmpAdjGrp = array ();
					$tmpAdjId = array ();
					$strAdj = "";
					foreach ( $aAdj as $val ) {
					  $adjItem = explode('_', $val);
                        if($adjItem[0]>0 && $adjItem[2]>0){
                            $tmpAdjList[] = $adjItem[2];    //设置配件购买量
                            $tmpAdjGrp[] = $adjItem[1];        //配件栏位
                            $tmpAdjId['product_id'][] = $adjItem[0];    //配件id数组
                        }
					}
					if (count ( $tmpAdjId )) {
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
					} else {
						$strAdj = "na";
					}
				} else {
				//add strAdj if showker -4-2
        if(strpos($aRow [2],'na$')===false) 
          {
					$strAdj = "na";
					}
					else{
					$strAdj=$aRow [2];
					}
				}
				$strKey = $aRow [0] . "-" . $aRow [1] . "-" . $strAdj;
				$linkKey = $aRow [0] . "@" . $aRow [1] . "@" . $strAdj;
				
				$aGoods = $items_g [$aRow [1]];
							/*add specialprice $ showker 2011-3-31 begin*/
                if(strpos($aRow[2],'$'))
                {                
                    unset($tmptPids);
                    $tmptPids=explode('$',$aRow[2]);
                    $aGoods ['price'] =$tmptPids[1];
                    $aGoods['name']='[团购]'.$aGoods['name'];
                }
			/*add specialprice $ showker 2011-3-31 end*/
				$aGoods ['addon'] ['adjinfo'] = $strAdj;
				$aGoods ['addon'] ['adjname'] = $strName;
				$aGoods ['adjList'] = $adjList;
				$aGoods ['price'] = $oMath->plus ( array ($aGoods ['price'], $adjPrice ) );
				$aGoods ['cost'] = $oMath->plus ( array ($aGoods ['cost'], $adjCost ) );
				$aGoods ['amount'] = $oMath->getoperationnumber ( $aGoods ['price'] ) * $num;

				$aGoods ['price'] = $oCur->formatnumber ( $aGoods ['price'], false );
				$aGoods ['key'] = $strKey;
				$aGoods ['link_key'] = $linkKey;
				$aGoods ['enkey'] = base64_encode ( $strKey );
				$aGoods ['nums'] = $num;
				$aGoods ['pmt_id'] = intval ( $cart_g ['pmt'] [$aRow [0]] );
				$aGoods ['weight'] += $adjWeight;
				switch ($this->system->getconf ( "point.get_policy" )) {
					case 0 :
						$aGoods ['score'] = 0;
						break;
					case 1 :
						$aGoods ['score'] = $aGoods ['price'] * $this->system->getconf ( "point.get_rate" );
				}
				$aItems [] = $aGoods;
				$trading ['totalPrice'] = $oMath->plus ( array ($trading ['totalPrice'], $aGoods ['amount'] ) );

				$trading ['totalGainScore'] += $aGoods ['score'] * $num;
				$trading ['weight'] += $aGoods ['weight'] * $num;
			}
		}
		unset ( $items_g );
		//print_r(&$aItems);
		$trading ['products'] = & $aItems;
		//print_r($trading ['productst']);
		
	}
}

?>
