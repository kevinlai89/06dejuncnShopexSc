<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/
require_once CORE_DIR.'/model_v5/trading/mdl.cart.php';
class cmd_cart extends mdl_cart
{
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
  function cmd_cart(){
			$this->skv();
      parent::mdl_cart();
  } 
      function checkoutInfo(&$aCart, &$aMember, $aParam=null) {
        $sale = &$this->system->loadModel('trading/sale');
        //$trading = $sale->getCartObject($aCart,$aMember['member_lv_id'],true);
        $trading = $sale->getCartObject($aCart,$aMember['member_lv_id'],true,true,$aParam);//mod by showker from orgin add one param to send area post 2011-2-15
        $trading['total_amount'] = $trading['totalPrice'];
        if($aParam['shipping_id']){
            $shipping = &$this->system->loadModel('trading/delivery');
            $aShip = $shipping->getDlTypeByArea($aParam['area'], 0, $aParam['shipping_id']);
            if($trading['exemptFreight'] == 1){
                $trading['cost_freight'] = 0;
            }else{
                $trading['cost_freight'] = cal_fee($aShip[0]['expressions'],$trading['weight'],$trading['pmt_b']['totalPrice'],$aShip[0]['price']);
            }
            $trading['shipping_id'] = $aParam['shipping_id'];
            if($aParam['is_protect'] == 'true' && $aShip[0]['protect']){
                $trading['is_protect'] = 1;
                $trading['cost_protect'] = max($aShip[0]['protect_rate']*$trading['totalPrice'], $aShip[0]['minprice']);
            }
            $trading['total_amount'] += $trading['cost_freight']+$trading['cost_protect'];
        }
        if($this->system->getConf('site.trigger_tax')){
            $trading['is_tax'] = 1;
            if(isset($aParam['is_tax']) && $aParam['is_tax'] == 'true'){
                $trading['tax_checked'] = 'checked';
                $trading['cost_tax'] = $trading['totalPrice'] * $this->system->getConf('site.tax_ratio');
                $trading['total_amount'] += $trading['cost_tax'];
            }
            $trading['tax_rate'] = $this->system->getConf('site.tax_ratio');
        }

        if($aParam['payment']){
            $payment = &$this->system->loadModel('trading/payment');
            $aPay = $payment->getPaymentById($aParam['payment']);
            $config=unserialize($aPay['config']);
            if ($config['method']<>2)
                $trading['cost_payment'] = $aPay['fee'] * $trading['total_amount'];
            else
                $trading['cost_payment'] = $config['fee'];
            $trading['total_amount'] += $trading['cost_payment'];
        }

        $trading['score_g'] = $trading['pmt_b']['totalGainScore'];
        $trading['pmt_amount'] = $trading['pmt_b']['totalPrice'] - $trading['totalPrice'];
        $trading['member_id'] = $aMember['member_id'];

        $order = &$this->system->loadModel('trading/order');
        $newNum = $order->getOrderDecimal($trading['total_amount']);
        $trading['discount'] = $trading['total_amount'] - $newNum;
        $trading['total_amount'] = $newNum;
        $oCur = &$this->system->loadModel('system/cur');
        $currency = $oCur->getcur($aParam['cur']);
        if($currency['cur_code']){
            $trading['cur_rate'] = $currency['cur_rate'];
        }else{
            $trading['cur_rate'] = 1;
        }
        $trading['final_amount'] = $newNum * $trading['cur_rate'];
        $trading['cur_sign'] = $currency['cur_sign'];
        $trading['cur_display'] = $this->system->request['cur'];
        $trading['cur_code'] = $currency['cur_code'];
        return $trading;
    }
     function setFastBuy( $objType = "g", $aParams )
				{       
              
																               // print_r($aParams);exit;

								if ( !$this->_checkStore( $aParams['pid'], 1 ) )
								{
												$this->setError( 10001 );
												trigger_error( __( "库存不足" ), E_USER_NOTICE );
												return false;
								}

								if($aParams['adj']!='na'){
								//add if by showker origin not have if..3-31
                    $aAdj = explode( "|", $aParams['adj'] );
                    foreach ( $aAdj as $val )
                    {
													$adjItem = explode('_', $val);
													if($adjItem[0]>0 && $adjItem[2]>0){
															if(!$this->_checkStore($adjItem[0], $adjItem[2])){
																	$this->setError(10001);
																	trigger_error(__('配件库存不足'),E_USER_NOTICE);
																	return false;
															}
													}

                    }
								}

								if ( $objType == "g" )
								{       
                        //add if aParams['specialprice'] by showker 2011-3-31begin*/

                        //add if aParams['specialprice'] by showker 2011-3-31begin*/

                        if(isset($aParams['specialprice']) && $aParams['specialprice']>=0){
                         if(isset($aParams['tuan']) && intval($aParams['tuan']) && !empty($aParams['conduser'])){
                            $cartKey = $aParams['gid']."-".$aParams['pid']."-".$aParams['adj']."$".$aParams['specialprice']."$".intval($aParams['tuan'])."$".$aParams['conduser'];//通过$隔开
                          }
                          else
                          {
                            $cartKey = $aParams['gid']."-".$aParams['pid']."-".$aParams['adj']."$".$aParams['specialprice'];//通过$隔开 
                          }
                        }
                        else
                        {
                        $cartKey = $aParams['gid']."-".$aParams['pid']."-".$aParams['adj'];
                        }
                        //add if aParams['specialprice'] by showker 2011-3-31end*/
												$aCart['g']['cart'][$cartKey] = $aParams['num'];
												if ( 0 < $aParams['pmtid'] )
												{
																$aCart['pmt'][$aParams['gid']] = $aParams['pmtid'];
												}
								}

								return $aCart;
				}
    function addToCart( $objType = "g", &$aParams, $quantity = 1 )
				{
                															//print_r($aParams);exit;
								switch ( $objType )
								{
								case "g" :
												if ( 0 < $aParams['gid'] && 0 < $aParams['pid'] && 0 < $quantity )
												{
                                //add if aParams['specialprice'] by showker 2011-3-31begin*/

                                if(isset($aParams['specialprice']) && $aParams['specialprice']>=0){
                                 if(isset($aParams['tuan']) && intval($aParams['tuan']) && !empty($aParams['conduser'])){
                                    $cartKey = $aParams['gid']."-".$aParams['pid']."-".$aParams['adj']."$".$aParams['specialprice']."$".intval($aParams['tuan'])."$".$aParams['conduser'];//通过$隔开
                                  }
                                  else
                                  {
                                    $cartKey = $aParams['gid']."-".$aParams['pid']."-".$aParams['adj']."$".$aParams['specialprice'];//通过$隔开 
                                  }
                                }
                                else
                                {
                                  $cartKey = $aParams['gid']."-".$aParams['pid']."-".$aParams['adj'];
																}
                                $aCart = $this->getCart( "g" );
															//	print_r($cartKey);exit;
																if ( isset( $aCart['cart'][$cartKey] ) )
																{
																				$aCart['cart'][$cartKey] += $quantity;
																				$buyStatus = 1;
																}
																else
																{
																				$aCart['cart'][$cartKey] = $quantity;
																				$buyStatus = 0;
																}
																
																if ( 0 < $aParams['pmtid'] )
																{
																				$aCart['pmt'][$aParams['gid']] = $aParams['pmtid'];
																}
																$objGoods = $this->system->loadModel( "trading/goods" );
																$aGoods = $objGoods->getMarketableById( $aParams['gid'] );
																if ( $aGoods['marketable'] == "false" )
																{

																				$this->setError( 10001 );
																				trigger_error( __( "该货品已经下架" ), E_USER_NOTICE );
																				return false;
																}
																if ( !$this->_checkStore( $aParams['pid'], $aCart['cart'][$cartKey] ) )
																{
																
				
																				if ( $buyStatus == 0 )
																				{
																								$this->setError( 10001 );
																								trigger_error( __( "库存不足" ), E_USER_NOTICE );
																								return false;
																				}
																				else
																				{
																								return "notify";
																				}
																				exit( );
																}
																//if ( $stradj != "na" ) mod by showker origin stradj is empty
																if ( $aParams['adj'] != "na" )
																{
																				$aAdj = explode( "|", $aParams['adj'] );
																				foreach($aAdj as $val){
																						$adjItem = explode('_', $val);
																						if($adjItem[0]>0 && $adjItem[2]>0){
																								if(!$this->_checkStore($adjItem[0], $adjItem[2]*$aCart['cart'][$cartKey])){
																										$this->setError(10001);
																										trigger_error(__('配件库存不足'),E_USER_NOTICE);
																										return false;
																								}
																						}
																				}
																}

																return $this->save( "g", $aCart );
												}
												else
												{
																$this->setError( 10001 );
																trigger_error( __( "参数错误!" ), E_USER_NOTICE );
																return false;
												}
								case "p" :
												if ( $aParams['pkgid'] )
												{
																$aCart = $this->getCart( "p" );
																$aCart[$aParams['pkgid']]['num'] += $quantity;
																if ( !$this->_checkGoodsStore( $aParams['pkgid'], $aCart[$aParams['pkgid']]['num'] ) )
																{
																				$this->setError( "10000" );
																				trigger_error( __( "捆绑商品数量不足" ), E_USER_ERROR );
																				return false;
																}
																return $this->save( "p", $aCart );
												}
												else
												{
																$this->setError( 10001 );
																trigger_error( __( "参数错误!" ), E_USER_NOTICE );
																return false;
												}
								case "f" :
												if ( $aParams['gift_id'] )
												{
																$aCart = $this->getCart( "f" );
																$aCart[$aParams['gift_id']]['num'] += $quantity;
																return $this->save( "f", $aCart );
												}
												else
												{
																$this->setError( 10001 );
																trigger_error( __( "参数错误!" ), E_USER_NOTICE );
																return false;
												}
								case "c" :
												if ( is_array( $aParams ) && count( $aParams == 1 ) )
												{
																foreach ( $aParams as $k => $c )
																{
																				$cart_c[$k] = array(
																								"type" => $c['type'],
																								"pmt_id" => $c['pmt_id']
																				);
																}
																return $this->save( "c", $cart_c );
												}
												else
												{
																$this->setError( 10001 );
																trigger_error( __( "参数错误!" ), E_USER_NOTICE );
																return false;
												}
								}
				}
			public function _getCart( $sCookie = "" )
				{
								$aCart = array( );
								if ( !$sCookie )
								{
												if ( $this->memberLogin )
												{
																$oMember =& $this->system->loadModel( "member/member" );
																$sCookie = $oMember->getCart( $this->memInfo['member_id'] );
												}
												else
												{
																$sCookie = $_COOKIE[$this->cookiesName];
												}
								}
								//print_r($sCookie );exit;//有
								$aType = explode( "@", $sCookie );
								unset( $aType[0] );
								foreach ( $aType as $sType )
								{
												if ( !empty( $sType ) )
												{
                                //print_r($sType);exit;
                                                                $sType=substr_replace($sType,"*",1,1);
                                                                $aItems = explode( "*", $sType );
																//$aItems = explode( ".", $sType );
																$sCurObj = $aItems[0];
																$sItem = $aItems[1];
															//	print_r($sItem );exit;
																switch ( $sCurObj )
																{
																case "g" :
																				$aTmp = null;
																				$aSplit = explode( ";", $sItem );
																				$sCart = $aSplit[0];
																				$sPmt = $aSplit[1];
																				if ( !empty( $sCart ) )
																				{
																								$aRow = explode( ",", $sCart );
																								foreach ( $aRow as $sRow )
																								{
																												$aTmp = explode( "-", $sRow );
																												$aCart['g']['cart'][$aTmp[0]."-".$aTmp[1]."-".$aTmp[2]] = $aTmp[3];
																								}
																								$aRow = explode( ",", $sPmt );
																								foreach ( $aRow as $sRow )
																								{
																												$aTmp = explode( "-", $sRow );
																												if ( $aTmp[0] )
																												{
																																$aCart['g']['pmt'][$aTmp[0]] = $aTmp[1];
																												}
																								}
																				}
																				else
																				{
																								$aCart['g']['cart'] = array( );
																				}																		
																				break;
																case "p" :
																				$aTmp = null;
																				$aRow = explode( ",", $sItem );
																				foreach ( $aRow as $sRow )
																				{
																								$aTmp = explode( "-", $sRow );
																								$aCart['p'][$aTmp[0]]['num'] = $aTmp[1];
																				}
																				break;
																case "f" :
																				$aTmp = null;
																				$aRow = explode( ",", $sItem );
																				foreach ( $aRow as $sRow )
																				{
																								$aTmp = explode( "-", $sRow );
																								$aCart['f'][$aTmp[0]]['num'] = $aTmp[1];
																				}
																				break;
																case "c" :
																				$aTmp = null;
																				$aRow = explode( ",", $sItem );
																				foreach ( $aRow as $sRow )
																				{
																								$aTmp = explode( "-", $sItem );
																								$aCart['c'][$aTmp[0]]['pmt_id'] = $aTmp[1];
																								switch ( $aTmp[2] )
																								{
																								case "o" :
																												$aTmp[2] = "order";
																												break;
																								case "g" :
																												$aTmp[2] = "goods";
																												break;
																								}
																								$aCart['c'][$aTmp[0]]['type'] = $aTmp[2];
																				}
																				break;
																}
												}
								}
								return $aCart;
				}
}

?>
