<?php
function widget_cfg_qf_goodscat3( $system )
{
				$o1 = $system->loadModel( "goods/virtualcat" );
				$o2 = $system->loadModel( "goods/productCat" );
				//$modTag = $system->loadModel("system/tag");
				$objGoods = $system->loadModel("goods/products");
				$data = $o2->getTreeList( );
				$i = 0;
				for ( ;	$i < count( $data );	++$i	)
				{
								$cat_path = $data[$i]['cat_path'];
								$cat_name = $data[$i]['cat_name'];
								$cat_id = $data[$i]['cat_id'];
								if ( empty( $cat_path ) || $cat_path == "," )
								{
												$myData[$cat_id]['label'] = $cat_name;
												$myData[$cat_id]['cat_id'] = $cat_id;
								}
				}
				$i = 0;
				for ( ;	$i < count( $data );	++$i	)
				{
								$cat_path = $data[$i]['cat_path'];
								$cat_name = $data[$i]['cat_name'];
								$cat_id = $data[$i]['cat_id'];
								$parent_id = $data[$i]['pid'];
								if ( trim( $cat_path ) == "," )
								{
												$count = 0;
								}
								else
								{
												$count = count( explode( ",", $cat_path ) );
								}
								if ( $count == 2 )
								{
												$c_1 = intval( $parent_id );
												$c_2 = intval( $cat_id );
												$myData[$c_1]['sub'][$c_2]['label'] = $cat_name;
												$myData[$c_1]['sub'][$c_2]['cat_id'] = $cat_id;
								}
								if ( $count == 3 )
								{
												$tmp = explode( ",", $cat_path );
												$c_1 = intval( $tmp[0] );
												$c_2 = intval( $tmp[1] );
												$c_3 = intval( $cat_id );
												$myData[$c_1]['sub'][$c_2]['sub'][$c_3]['label'] = $cat_name;
												$myData[$c_1]['sub'][$c_2]['sub'][$c_3]['cat_id'] = $cat_id;
								}
				}
				//qf_comment on 20111019 return array("cats" => $myData, "tags" => $modTag->tagList( "goods" ), 'vcats' =>  $o1->getMapTree(0,''), 'orderBy'=>$objGoods->orderBy());
				/*===================qf_custom develop on 20111019 end===================*/
				$art_o=$system->loadModel('content/article');
				$artlist=$art_o->getCategorys();//获取文章分类 Array ( [100] => 最新公告 [101] => 促销信息 ) 
				for($j=0;$j<count($artlist);$j++){
				   $art_cat[$j]['art_cat_id']=key($artlist);
				   $art_cat[$j]['art_cat_nm']=$artlist[key($artlist)];
				   next($artlist);
				}

				$result=array("cats" => $myData,/*"tags" => $modTag->tagList( "goods" ), */'vcats' =>  $o1->getMapTree(0,''),'orderBy'=>$objGoods->orderBy(),'art_cats'=>$art_cat);
				return $result;
				//print_r($result);return;
				/*===================qf_custom develop on 20111019 end===================*/
}
?>
