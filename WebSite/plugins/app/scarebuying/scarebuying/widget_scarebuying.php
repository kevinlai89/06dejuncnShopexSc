<?php
function widget_scarebuying(&$setting,&$system){
    $appmgr = $system->loadModel('system/appmgr');
    $status=$appmgr->getPluginInfoByident('scarebuying','disabled');
   if($status['disabled']=='false'){
       if (!class_exists('mdl_scare')) {
	      require_once(PLUGIN_DIR.'/app/scarebuying/mdl.scare.php');
       }
       $group_activity=&new mdl_scare();
	   $limit = (intval($setting['limit'])>0)?intval($setting['limit']):6;
			$row=$group_activity->getListbyidtrue($setting['goods_id'],$limit);
            foreach($row as $k=>$v){
                $objGoods = &$system->loadModel('trading/goods');
                $good_value=$objGoods->getFieldById($v['goods_id'],$aField=array('*'));
              
                $good_values['name']=$good_value['name'];
                $row_pic=explode('|',$good_value['thumbnail_pic']);
             
               if(count($row_pic)>1){
                 $good_values['pic_img']=$system->base_url().'/'.$row_pic['0'];
               }else{
                $good_values['pic_img']=$good_value['thumbnail_pic'];
               }
             if(!$good_values['pic_img']){
                 $row_picd=explode('|',$system->getConf('site.default_thumbnail_pic'));
                  $good_values['pic_img']=$system->base_url().'/'.$row_picd['0'];
              }
              if($v['scare_mprice']){
                  $scare_mprice = unserialize($v['scare_mprice']);
                  if($_COOKIE['MLV']){
                      if(isset($scare_mprice[$_COOKIE['MLV']])&&$scare_mprice[$_COOKIE['MLV']]){
                            $v['scare_price'] = $scare_mprice[$_COOKIE['MLV']];
                      }
                  }
              }
                $good_values['or_price']=$good_value['price'];
                $good_values['low_price']=$v['scare_price'];
                $good_values['goods_id']=$v['goods_id'];
                //$good_values['tuangou_width']=$setting['tuangou_width'];
                //$good_values['tuangou_height']=$setting['tuangou_height'];
                $good_values['start_time']=$v['s_time'];
                $good_values['scarebuyingend_time']=$v['e_time'];
                $good_values['url']=$system->base_url().'/plugins/widgets/scarebuying/groupon-bj.png';
                $good_values['js_url']=$system->base_url().'/index.php?action_scarebuying.html';
                if($v['scare_count'] > 0){
                   $good_valuesa['nums']=1;
                }
                if(time()<$v['e_time']){
                     $good_valuesa['end_fou_time']=1;
                }
                
                if($good_valuesa['end_fou_time'] && $good_valuesa['nums']){
                    $good_values['total_scarebuying_state']=1;
                }
                $good_values['scarebuyingtimes']=$v['goods_id'];
				$data[$k]= $good_values;
            }
			 return  $data;
       

   }else{
       return  false;
       
   }
}

    
?>