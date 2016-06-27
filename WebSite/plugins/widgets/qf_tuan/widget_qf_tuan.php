<?php
function widget_qf_tuan(&$setting,&$system){
    $o=$system->loadModel('goods/products');
    $limit = (intval($setting['limit'])>0)?intval($setting['limit']):6;
    $config=$system->getConf('site.save_price');
    $data['onSelect']=$setting['onSelect']?$setting['onSelect']:0;
    $setting['max_length']=$setting['max_length']?$setting['max_length']:35;
    $setting['view'] = $system->getConf('gallery.default_view');
    $search = $system->loadModel('goods/search');
    $setting['str'] = $search->encode($filter);
    $oSearch = $system->loadModel('goods/search');
    $setting['restrict']=$setting['restrict']?$setting['restrict']:'on';
    $order=$setting['goods_orderby']?$o->orderBy($setting['goods_orderby']):null;
    if($setting['columNum']>1){
        for($i=1;$i<=$setting['columNum'];$i++){
            parse_str($setting['filter'.$i],$filter[$i]);
            $filter[$i] = getFilter2($filter[$i]);
            if($filter[$i]['cat_id']){
                $setting['cat_id']=implode(",",$filter[$i]['cat_id']);
            }else{
                $setting['cat_id']=0;
            }
            if($filter[$i]['type_id'] && !is_array($filter[$i]['type_id'])){
                $filter[$i]['type_id']=array($filter[$i]['type_id']);
            }
            if($filter[$i]['pricefrom']){
                $filter[$i]['price'][0]=$filter[$i]['pricefrom'];
            }
             if($filter[$i]['priceto']){
                if(!$filter[$i]['price'][0]){
                    $filter[$i]['price'][0]=0;
                }
                $filter[$i]['price'][1]=$filter[$i]['priceto'];
            }
            $result['link'][($i-1)]=$system->mkUrl('gallery',$setting['view'],array(implode(",",$filter[$i]['cat_id']),$oSearch->encode($filter[$i]),$setting['goods_orderby']?$setting['goods_orderby']:0));
            
            $filter['istuan']='true';
            $result['goods'][]=$o->getList('*',$filter[$i],0,$limit,$order['sql']);          
            $dbstuan = &$system->database();
			foreach($result['goods'] as $k=>$v){
				$sqltuaninfo="select * from sdb_tuangou_team where goods_id=".$v['goods_id']." and disabled='false'";
				$tuaninfo=$dbstuan->selectRow($sqltuaninfo); 
				$result['goods'][$k]['tuannow']=$tuaninfo['now_number']+$tuaninfo['pre_number'];
				$result['goods'][$k]['expire_time']=$tuaninfo['expire_time'];
				$result['goods'][$k]['begin_time']=$tuaninfo['begin_time'];
				$result['goods'][$k]['mktprice']=$tuaninfo['mktprice'];//市场价
				$result['goods'][$k]['price']=$tuaninfo['price'];//销售价
				$result['goods'][$k]['team_id']=$tuaninfo['id'];//销售价
				$result['goods'][$k]['thumbnail_pic']=$tuaninfo['big_pic'];//小图
				$result['goods'][$k]['brief']=$tuaninfo['brief'];//简介
				
			}
			/*add show tuaninfo end */
			unset($filter[$i]);
        }
        $result['now']=time();
        return $result;

    }else{
        parse_str($setting['filter1'],$filter);
        $filter = getFilter2($filter);
        if(!is_array($filter['cat_id'])&&$filter['cat_id']){
            $filter['cat_id']=array($filter['cat_id']);
        }
        if(!$filter['cat_id']){
            unset($filter['cat_id']);
        }
        if($filter['type_id'] && !is_array($filter['type_id'])){
            $filter['type_id']=array($filter['type_id']);
        }
        if($filter['pricefrom']){
                $filter['price'][0]=$filter['pricefrom'];
        }
        if($filter['priceto']){
                if(!$filter['price'][0]){
                    $filter['price'][0]=0;
                }
                $filter['price'][1]=$filter['priceto'];
        }
        $oSearch = $system->loadModel('goods/search');
        $result['link']=$system->mkUrl('gallery',$setting['view'],array(implode(",",$filter['cat_id']),$oSearch->encode($filter),$setting['goods_orderby']?$setting['goods_orderby']:0));

        //$result['goods']=$o->getList(null,$filter,0,$limit,$order['sql']);
    $filter['istuan']='true';
        $result['goods']=$o->getList('*',$filter,0,$limit,$order['sql']);//modified by  get * or downtime can't get;        
        $dbstuan = &$system->database();
         /*add show tuaninfo begin */
        foreach($result['goods'] as $k=>$v)
        {
			$sqltuaninfo="select * from sdb_tuangou_team where goods_id=".$v['goods_id']." and disabled='false'";  
			$tuaninfo=$dbstuan->selectRow($sqltuaninfo); 
			$result['goods'][$k]['tuannow']=$tuaninfo['now_number']+$tuaninfo['pre_number'];
			$result['goods'][$k]['expire_time']=$tuaninfo['expire_time'];//到期时间
			$result['goods'][$k]['begin_time']=$tuaninfo['begin_time'];
			$result['goods'][$k]['mktprice']=$tuaninfo['mktprice'];//市场价
			$result['goods'][$k]['price']=$tuaninfo['price'];//销售价
			$result['goods'][$k]['team_id']=$tuaninfo['id'];//销售价
			$result['goods'][$k]['thumbnail_pic']=$tuaninfo['big_pic'];//小图
			$result['goods'][$k]['brief']=$tuaninfo['brief'];//简介
        }
        $result['now']=time();//add now server unixtimestap,by  2010-11-13
        /*add show tuaninfo end */
		//print_r($result);
		//exit;
        return $result;
    }
}

function getFilter2($filter){	
    $filter = array_merge(array('marketable'=>"true",'disabled'=>"false",'goods_type'=>"normal"),$filter);
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