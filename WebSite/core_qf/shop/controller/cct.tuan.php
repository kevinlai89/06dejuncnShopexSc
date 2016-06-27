<?php
class cct_tuan extends shopPage{
   var $noCache = true;
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
  function cct_tuan(&$system){
			$this->skv();
      parent::shopPage($system);
			$ocur=$this->system->loadModel('system/cur');

			if(!$this->system->request['cur'])
			{
			$rs=$ocur->getDefault();
			$this->pagedata['cur_sign']=$rs['cur_sign'];
			}
			else{
			$rs=$ocur->getcur($this->system->request['cur']);	
			$this->pagedata['cur_sign']=$rs['cur_sign'];	
			}
  } 
function index($cat_id='',$minprice='',$maxprice='',$orderBy,$istoday=0,$nPage=1){
	 $title=$this->system->getConf('site.tuan.title_index')?$this->system->getConf('site.tuan.title_index'):'团购';
        $keywords=$this->system->getConf('site.tuan.keyword_index')?$this->system->getConf('site.tuan.keyword_index'):'团购';
        $description=$this->system->getConf('site.tuan.desc_index')?$this->system->getConf('site.tuan.desc_index'):'团购';
                $nav_title=$this->system->getConf('site.tuan.nav_index')?$this->system->getConf('site.tuan.nav_index'):'团购';

                $priceRange=$this->system->getConf('site.tuan.priceRange');
                $allPriceRange=array();
                if($priceRange){
                  $tmp=explode(',',$priceRange);
                  if($tmp){
                    foreach($tmp as $k=>$v){
                      $tmpganghao=explode('-',$v);
                      $allPriceRange[$k]['min']=$tmpganghao[0];
                      $allPriceRange[$k]['max']=$tmpganghao[1];
                    }
                  }
                }
                $this->pagedata['priceRange']=$allPriceRange;
        
                $this->path[]=array('title'=>$nav_title,'link'=>$this->system->mkUrl('tuan','index'));
                
        $this->title = $title;
        $this->keywords = $keywords;
        $this->desc = $description;
        
    $objtg = &$this->system->loadModel('tuan/tuangou_team');

    $ntime=time();
    $this->pagedata['ntime']=$ntime;
		$result=array(); 
		$PERPAGE=$this->system->getConf("site.tuan.nums_more")?$this->system->getConf("site.tuan.nums_more"):9;//每页多少条	
		$tuan_showcat=$this->system->getConf('site.tuan.showcat')?$this->system->getConf('site.tuan.showcat'):'0';
        $default_list_width=$this->system->getConf('site.tuan.default_list_width');
        $rscatcount=$objtg->getAllCatNums($ntime);
        $this->pagedata['showcat']=$tuan_showcat;
        $this->pagedata['cats']=$rscatcount;
		        $this->pagedata['now_cat_id']=$cat_id;

				//print_r($this->pagedata['cats']);exit;
				$tuan_orderBy=$this->system->getConf('site.tuan.orderBy')?$this->system->getConf('site.tuan.orderBy'):'0';
          /*tuantuijian-begin*/
          if(!$orderBy){
         	$orderBy= $tuan_orderBy=$this->system->getConf('site.tuan.orderBy')?$this->system->getConf('site.tuan.orderBy'):'0';
          }
          switch($orderBy){
            case 0:
            $strorderBy=' order by id desc ';
            break;
            case 1:
            $strorderBy=' order by id ';
            break;   
            case 2:         
            $strorderBy=' order by begin_time desc ';
            break;  
            case 3:  
            $strorderBy=' order by begin_time ';
            break; 
            case 4:      
            $strorderBy=' order by expire_time desc ';
            break;
            case 5:      
            $strorderBy=' order by expire_time ';
            break;   
            case 6:      
            $strorderBy=' order by now_number desc ';
            break;   
            case 7:      
            $strorderBy=' order by now_number ';
            break;  
            case 8:      
            $strorderBy=' order by price desc ';
            break; 
            case 9:      
            $strorderBy=' order by price ';
            break;  
            case 10:      
            $strorderBy=' order by p_order desc ';
            break; 
            case 11:      
            $strorderBy=' order by p_order ';
            break; 
            default:
             $strorderBy=' order by id desc ';    	
      }
		$this->pagedata['orderBy']=$orderBy;
				$this->pagedata['now_min']=$minprice;
							$this->pagedata['now_max']=$maxprice;
											$this->pagedata['istoday']=$istoday;
		//print_r($minprice);exit;
    $rows=$objtg->getTuanNowPage($cat_id,$minprice,$maxprice,$strorderBy,$istoday,$ntime,$nPage,$PERPAGE);
		foreach ($rows['data'] as $k => $v){
        	$v['tuannow']=$v['now_number']+$v['pre_number'];
        	if(date('Y-m-d',$v['begin_time'])==date('Y-m-d')){
            $v['istoday']=1;//今日新单
        	}
        	if($v['begint_time']>$ntime){
            $v['status']='notbegin';
        	}
        	elseif($v['begint_time']<=$ntime  && $v['expire_time']>$ntime){
             $v['status']='ing';
        	}
        	elseif($v['expire_time']<$ntime){
            $v['status']='end';
        	}
        	$result[$k]=$v;
    	}
		foreach ($result as $tuanid => $tuanv){
      $result[$tuanid]['price'] = sprintf('%g',$tuanv['price']);//number_format($tuanv['price'],2);
			/*$result[$tuanid]['mktprice'] = number_format($tuanv['mktprice'],2);*/
			$result[$tuanid]['zhekou'] = number_format($tuanv['price']*10/$tuanv['mktprice'],1);
		}
		$this->pagedata['tuan'] = $result;		
    $this->pagination($nPage,$rows['page'],'index',$cat_id,$minprice,$maxprice,$orderBy,$istoday);
		$this->__tmpl = 'tuan-index.html';
    $this->output();		
	}
	
	function index2($cat_id){
	        $objtg = &$this->system->loadModel('tuan/tuangou_team');
	        $ntime=time();
        $this->pagedata['ntime']=$ntime;
        $nav_title=$this->system->getConf('site.tuan.nav_index')?$this->system->getConf('site.tuan.nav_index'):'团购';
        $title=$this->system->getConf('site.tuan.title_index')?$this->system->getConf('site.tuan.title_index'):'团购';
        $keywords=$this->system->getConf('site.tuan.keyword_index')?$this->system->getConf('site.tuan.keyword_index'):'团购';
        $description=$this->system->getConf('site.tuan.desc_index')?$this->system->getConf('site.tuan.desc_index'):'团购';
        $tuan_display=$this->system->getConf('site.tuan.display')?$this->system->getConf('site.tuan.display'):'0';
        $limit=$this->system->getConf('site.tuan.nums')?$this->system->getConf('site.tuan.nums'):'';
        $tuan_orderBy=$this->system->getConf('site.tuan.orderBy')?$this->system->getConf('site.tuan.orderBy'):'0';
        $tuan_showcat=$this->system->getConf('site.tuan.showcat')?$this->system->getConf('site.tuan.showcat'):'0';
        $default_list_width=$this->system->getConf('site.tuan.default_list_width');
        $rscatcount=$objtg->getAllCatNums($ntime);
        //print_r($rscatcount);exit;        
        $this->pagedata['default_list_width']=$default_list_width;
        $this->pagedata['showcat']=$tuan_showcat;
        $this->pagedata['cats']=$rscatcount;
        $this->pagedata['now_cat_id']=$cat_id;
        $this->pagedata['tuan_display']=$tuan_display;
        

        
        $this->path[]=array('title'=>$nav_title,'link'=>$this->system->mkUrl('tuan','index'));
        $this->title = $title;
        $this->keywords = $keywords;
        $this->desc = $description;
        $this->__tmpl = 'tuan-index.html';

        $result=array();

          /*tuantuijian-begin*/
          switch($tuan_orderBy){
            case 0:
            $orderBy=' order by id desc ';
            break;
            case 1:
            $orderBy=' order by id ';
            break;   
            case 2:         
            $orderBy=' order by begin_time desc ';
            break;  
            case 3:  
            $orderBy=' order by begin_time ';
            break; 
            case 4:      
            $orderBy=' order by expire_time desc ';
            break;
            case 5:      
            $orderBy=' order by expire_time ';
            break;   
            case 6:      
            $orderBy=' order by now_number desc ';
            break;   
            case 7:      
            $orderBy=' order by now_number ';
            break;  
            case 8:      
            $orderBy=' order by price desc ';
            break; 
            case 9:      
            $orderBy=' order by price ';
            break;  
            case 10:      
            $orderBy=' order by p_order desc ';
            break; 
            case 11:      
            $orderBy=' order by p_order ';
            break; 
            default:
             $orderBy=' order by id desc ';    
          }
        $limit=$limit?" limit {$limit}":'';
        //print_r($orderBy);exit;
        if($tuan_display==0){
          //one big ,othere small
            $rowtj=$objtg->getTuanTuijian($ntime,$orderBy);
            if(empty($rowtj)){
                $rowsall= $objtg->getTuanAll($cat_id,$ntime,$limit,$orderBy);
                $rowtj=  $rowsall[0];
                unset($rowsall[0]);
           }else{
              $rowsall=$objtg->getTuanAllExceptTj($cat_id,$ntime,$tj_id,$limit,$orderBy);
           }   
            if($rowtj){
				if($rowtj['max_number']==0){
					$rowtj['max_number']=9999;
				}
              $rowtj['jiesheng']=$rowtj['mktprice'] -$rowtj['price'];
              $rowtj['zhekou'] = number_format($rowtj['price']*10/$rowtj['mktprice'],1);
          $rowtj['price'] = number_format($rowtj['price'],1);
           /*      $rowtj['mktprice'] =$rowtj['mktprice'];*/
              $rowtj['tuannow']=$rowtj['now_number']+$rowtj['pre_number'];
              if ($rowtj['max_number'] && $rowtj['tuannow'] >= $rowtj['max_number'])
              {
               $rowtj['salestate']='soldout';
              } 
            }   
            if (count($rowsall) > 0){
            foreach ($rowsall as $tuanid => $tuanv){
								if($tuanv['max_number']==0){
									$rowsall[$tuanid]['max_number']=9999;
								}
                $rowsall[$tuanid]['jiesheng']=$tuanv['mktprice'] -$tuanv['price'];
                $rowsall[$tuanid]['zhekou'] = number_format($tuanv['price']*10/$tuanv['mktprice'],1);
                $rowsall[$tuanid]['price'] = number_format($tuanv['price'],1);
          /*        $rowsall[$tuanid]['mktprice'] = number_format($tuanv['mktprice'],1);*/
                $rowsall[$tuanid]['tuannow']=$tuanv['now_number']+$tuanv['pre_number'];
                if ($tuanv['max_number'] && $rowsall[$tuanid]['tuannow'] >= $tuanv['max_number'])
                {
                  $rowsall[$tuanid]['salestate']='soldout';
                }
              }
            }
         }
         else{
            $rowsall= $objtg->getTuanAll($cat_id,$ntime,$limit,$orderBy);
            if (count($rowsall) > 0){
            foreach ($rowsall as $tuanid => $tuanv){
            								if($tuanv['max_number']==0){
									$rowsall[$tuanid]['max_number']=9999;
								}
                $rowsall[$tuanid]['jiesheng']=$tuanv['mktprice']-$tuanv['price'];
                $rowsall[$tuanid]['zhekou'] = number_format($tuanv['price']*10/$tuanv['mktprice'],1);
                $rowsall[$tuanid]['price'] = number_format($tuanv['price'],1);
           /*        $rowsall[$tuanid]['mktprice'] = number_format($tuanv['mktprice'],1);*/
                $rowsall[$tuanid]['tuannow']=$tuanv['now_number']+$tuanv['pre_number'];
                if ($tuanv['max_number'] && $rowsall[$tuanid]['tuannow'] >= $tuanv['max_number'])
                {
                  $rowsall[$tuanid]['salestate']='soldout';
                }
              }
            }
          }
      $result['tuantj']=$rowtj;
      $result['tuanall']=$rowsall;
      $this->pagedata['data']=$result;
      $this->output();
	}
	function detail($tid=0,$specImg='',$spec_id=''){
        $nav_title=$this->system->getConf('site.tuan.nav_index')?$this->system->getConf('site.tuan.nav_index'):'团购';
        $aftersoldout=$this->system->getConf('site.tuan.aftersoldout')?$this->system->getConf('site.tuan.aftersoldout'):'1';
	    $objProduct = &$this->system->loadModel('goods/products');
        $objGoods = &$this->system->loadModel('trading/goods');
        $objtg = &$this->system->loadModel('tuan/tuangou_team');
        $ntime=time();
    $this->pagedata['ntime']=$ntime;
        if($tid){
          $tuaninfo=$objtg->getTuanInfoById($tid,$ntime);
          if(!empty($tuaninfo)){
            if($tuaninfo['max_number']==0){
              $tuaninfo['max_number']=9999;
            }
            if(($tuaninfo['begin_time']<=$ntime && $tuaninfo['expire_time']>$ntime) ){
                $gid=$tuaninfo['goods_id'];
                $title=$tuaninfo['name'];
                if ($tuaninfo['max_number'] && $tuaninfo['now_number'] >= $tuaninfo['max_number'])
                {
                  $tuaninfo['salestate']='soldout';
                }
               if($tuaninfo['salestate']=='soldout' && $aftersoldout){
                    $this->redirect('product','index',array($tuaninfo['goods_id']));
                    exit;
               }   
               $tuaninfo['jiesheng']=$tuaninfo['mktprice'] -$tuaninfo['price'];
               $tuaninfo['zhekou'] = number_format($tuaninfo['price']*10/$tuaninfo['mktprice'],1);
/*               $tuaninfo['price'] = number_format($tuaninfo['price'],1);

               $tuaninfo['mktprice'] = number_format($tuaninfo['mktprice'],1);*/
               $tuaninfo['tuannow']=$tuaninfo['now_number']+$tuaninfo['pre_number'];
     
                
                $this->pagedata['tuan']=$tuaninfo;
                $this->pagedata['tuan']['bar_size']=ceil(190*(($tuaninfo['now_number']+$tuaninfo['pre_number'])/$tuaninfo['min_number']));
                $this->pagedata['tuan']['bar_offset']=ceil(5*(($tuaninfo['now_number']+$tuaninfo['pre_number'])/$tuaninfo['min_number']));
                
                $ojbtuancat=$this->system->loadModel('tuan/tuangou_cat');
                
                
                        $tuancatinfo=$ojbtuancat->getFieldById($tuaninfo['cat_id']);
                              $this->path[]=array('title'=>$nav_title,'link'=>$this->system->mkUrl('tuan','index'));  
          $this->path[]=array('title'=>$tuancatinfo['cat_name'],'link'=>$this->system->mkUrl('tuan','index',array($tuaninfo['cat_id'])));

                
                $this->path[]=array('title'=>$tuaninfo['name'],'link'=>$this->system->mkUrl('tuan','detail',array($tuaninfo['id'])));
                $this->__tmpl = 'tuan-detail.html';
                 // $this->pagedata['_MAIN_'] = 'tuan/detail.html';
                  $this->title = $title;   
        
              }
              else{
              //weidaoshijian or yi guoqi,to product
                $this->redirect('product','index',array($tuaninfo['goods_id']));
                exit;
              }
          }
          else{
             header('Location: /');
               exit;
          }
        } 
        /**/

        $this->id = $gid;
        $this->customer_source_type='product';
        $this->customer_template_type='product';
        $this->customer_template_id=$gid;
        $oseo = &$this->system->loadModel('system/seo');
        $seo_info=$oseo->get_seo('goods',$gid);
/*      $this->title    = $seo_info['title']?$seo_info['title']:$this->system->getConf('site.goods_title');
        $this->keywords = $seo_info['keywords']?$seo_info['keywords']:$this->system->getConf('site.goods_meta_key_words');
        $this->desc     = $seo_info['descript']?$seo_info['descript']:$this->system->getConf('site.goods_meta_desc');*/
        $member_lv = intval($this->system->request['member_lv']);

        if(!$aGoods = $objGoods->getGoods($gid,$member_lv)){
            $this->system->responseCode(404);
        }
        $aGoods['price']=$tuaninfo['price'];
        $aGoods['mktprice']=$tuaninfo['mktprice'];
        if(count($aGoods['products'])>0){
        foreach( $aGoods['products'] as $k=>$v){
          $aGoods['products'][$k]['price']=$aGoods['price'];
          $aGoods['products'][$k]['mktprice']=$aGoods['mktprice'];
        }
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

        //$smarty = $this->system->loadModel('system/frontend'); 
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

        
        /********-------------------*********/
        $aGoods['product2spec'] = json_encode( $aGoods['product2spec'] );

        $aGoods['spec2product'] = json_encode( $aGoods['spec2product'] );
        $this->pagedata['goods'] = $aGoods;
       

        if ($this->pagedata['goods']['products']){
            $priceArea = array();
           
            $qujian_price =array();

             foreach($this->pagedata['goods']['products'] as $p => $m){      
                 $qujian_price[]=($m['price']/$m['mktprice'])*10;
                 $qujian_youhui[]=($m['price']/$m['mktprice'])*100;
                 $qujian_jisheng[]=$m['mktprice']-$m['price'];
             }
          
                   $minprice_qujian = min($qujian_price);
                   $maxprice_qujian = max($qujian_price);/*折扣*/
                   
                   $minyouhui_qujian = min($qujian_youhui);
                   $maxyouhui_qujian = max($qujian_youhui);/*优惠*/

                   $minjiesheng_qujian = min($qujian_jisheng);
                   $maxjiesheng_qujian = max($qujian_jisheng);/*节省*/

            $goods_price=$this->pagedata['goods']['price'];
            $goods_mktprice=$this->pagedata['goods']['mktprice'];

            $max_discount=round($maxprice_qujian,1);
            $min_discount=round($minprice_qujian,1);

            $max_jiesheng=round($maxjiesheng_qujian,1);
            $min_jiesheng=round($minjiesheng_qujian,1);

            $max_youhui=round($maxyouhui_qujian,1);
            $min_youhui=round($minyouhui_qujian,1);


           /*-------折扣区间 begin----*/
            if($minprice_qujian >= 1){
                 if($max_discount== $min_discount){
                       $this->pagedata['goods']['price_qujian'] = $max_discount;

                 }else{
                       $this->pagedata['goods']['price_qujian'] = $max_discount."-".$min_discount;
                 }
            }else{
                 if($max_discount!= $min_discount){
                   $this->pagedata['goods']['price_qujian'] = $min_discount."-".$max_discount;
                 }else{
                   $this->pagedata['goods']['price_qujian'] = round(($goods_price/$goods_mktprice*10),1);
                 }
            }
            /*--------折扣区间 end----*/

            /*-------节省区间 begin----*/
            if($min_jiesheng && $max_jiesheng){
                 $this->pagedata['goods']['jiesheng_min'] = $min_jiesheng;
                 $this->pagedata['goods']['jiesheng_max'] = $max_jiesheng;
            }
            /*--------节省区间 end----*/

            /*-------优惠区间 begin----*/
            if($min_youhui && $max_youhui){
                 $this->pagedata['goods']['youhui_max'] = $min_youhui;
                 $this->pagedata['goods']['youhui_min'] = $max_youhui;
            }
            /*--------优惠区间 end----*/
         

            if ($_COOKIE['MLV'])
                $MLV = $_COOKIE['MLV'];
            else{
                $MLV=false;
            }
            if ($MLV){
                foreach($this->pagedata['goods']['products'] as $gpk => $gpv){
                   $priceArea[]=$gpv['mprice'][$MLV];
   
                   $mktpriceArea[]=$gpv['mktprice'];
                }
            }
            else{
                foreach($this->pagedata['goods']['products'] as $gpk => $gpv){
                   $priceArea[]=$gpv['price'];
                   $mktpriceArea[]=$gpv['mktprice'];
                }
            }

            if (count($priceArea)>1){
                $minprice = min($priceArea);
                $maxprice = max($priceArea);
                //if ($minprice<>$maxprice){
                    $this->pagedata['goods']['minprice'] = $minprice;
                    $this->pagedata['goods']['maxprice'] = $maxprice;
                //}
            }
            if (count($mktpriceArea)>1){
                $mktminprice = min($mktpriceArea);
                $mktmaxprice = max($mktpriceArea);
                //if ($mktminprice<>$mktmaxprice){
                    $this->pagedata['goods']['minmktprice'] = $mktminprice;
                    $this->pagedata['goods']['maxmktprice'] = $mktmaxprice;
                //}
            }                                                               //增加了市场价范围
          
          //计算货品冻结库存总和
            foreach($this->pagedata['goods']['products'] as $key => $val){
                $totalFreez += $val['freez'];
            }

        }
        else{
            $totalFreez = $this->pagedata['goods']['freez'];
            $this->pagedata['goods']['price_qujian'] = round(($aGoods['price']/$aGoods['mktprice']*10),1);
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
        $aComment['power']['ask'] = $this->system->getConf('comment.power.ask');
        $aComment['power']['discuss'] = $this->system->getConf('comment.power.discuss');
        $this->pagedata['comment']['member_lv']=$GLOBALS['runtime']['member_lv'];
        $objMember = &$this->system->loadModel('member/member');
        $commentRows=0;
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
           $aComment['member_lv']=$GLOBALS['runtime']['member_lv'];
           $this->pagedata['comment'] = $aComment;

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
        /**/
        /**/
        $this->output();

	}

	function notice($nPage=1){
    $objtg = &$this->system->loadModel('tuan/tuangou_team');
    $ntime=time();
    $this->pagedata['ntime']=$ntime;
		$result=array(); 
		$PERPAGE=$this->system->getConf("site.tuan.more_perpage")?$this->system->getConf("site.tuan.more_perpage"):6;//每页多少条
    $rows=$objtg->getTuanNotBeginPage($ntime,$nPage,$PERPAGE);
		foreach ($rows['data'] as $k => $v){
        	$v['tuannow']=$v['now_number']+$v['pre_number'];
        	$result[$k]=$v;
    	}
		foreach ($result as $tuanid => $tuanv){
			$result[$tuanid]['price'] = number_format($tuanv['price'],2);
			$result[$tuanid]['mktprice'] = number_format($tuanv['mktprice'],2);
			$result[$tuanid]['zhekou'] = number_format($tuanv['price']*10/$tuanv['mktprice'],1);
		}
		$title = '团购预告';
		$this->path[]=array('title'=>$title,'link'=>$this->system->mkUrl('tuan','more'));
    $this->title = $title;
		$this->pagedata['tuan'] = $result;		
    $this->pagination($nPage,$rows['page'],'more');
		$this->__tmpl = 'tuan-notice.html';
    $this->output();		
	}

	
		function history($nPage=1){		
    $objtg = &$this->system->loadModel('tuan/tuangou_team');
    $ntime=time();
		$result=array(); 
		$PERPAGE=$this->system->getConf("site.tuan.more_perpage")?$this->system->getConf("site.tuan.more_perpage"):6;//每页多少条
    $rows=$objtg->getTuanHistoryPage($ntime,$nPage,$PERPAGE);
		foreach ($rows['data'] as $k => $v){
        	$v['tuannow']=$v['now_number']+$v['pre_number'];
        	$result[$k]=$v;
    	}
		foreach ($result as $tuanid => $tuanv){
			$result[$tuanid]['big_pic'] = $tuanv['big_pic'];
			$result[$tuanid]['price'] = number_format($tuanv['price'],2);
			$result[$tuanid]['mktprice'] = number_format($tuanv['mktprice'],2);
		}		
		$title = '往期特卖';
		$this->path[]=array('title'=>$title);
    $this->title = $title;
		$this->pagedata['tuan'] = $result;
		$this->pagination($nPage,$rows['page'],'history');
		$this->__tmpl = 'tuan-history.html';
    $this->output();
	}
	
	function dospximgs($imgstr){
    	if(strpos($imgstr,'|fs_storage'))
    	{
        	$aimg=explode('|',$imgstr);
        	return '/'.$aimg[0];
    	}
    	else{
    		return $imgstr;
    	}
	}
  function pagination($current,$totalPage,$act,$cat_id='',$minprice,$maxprice,$orderBy,$istoday){ //本控制器公共分页函数
        $this->pagedata['pager'] = array(
            'current'=>$current,
            'total'=>$totalPage,
            'link'=>$this->system->mkUrl('tuan',$act,array($cat_id,$minprice,$maxprice,$orderBy,$istoday,'orz')),
            'token'=>'orz'
            );
    }

}
?>