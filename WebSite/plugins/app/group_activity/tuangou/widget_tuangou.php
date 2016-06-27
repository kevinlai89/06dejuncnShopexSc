<?php
function widget_tuangou(&$setting,&$system){
  
    $appmgr = $system->loadModel('system/appmgr');
     $status=$appmgr->getPluginInfoByident('group_activity','disabled');
   if($status['disabled']=='false'){
       $group_activity=&$system->loadModel('plugins/group_activity/group_activity');
          $limit = (intval($setting['limit'])>0)?intval($setting['limit']):6;
            if($row=$group_activity->get_group_byidtrue($setting['goods_id'],$limit)){
                 foreach($row as $k=>$v){
                $objGoods = &$system->loadModel('trading/goods');
                $good_value=$objGoods->getFieldById($v['gid'],$aField=array('*'));
                $group_activity_model = $system->loadModel('plugins/group_activity/group_activity');
                $group_activity_model->chgStateByGid($v['gid']);
                $goodsActivityInfo = $group_activity_model->getOpenActivityByGid($v['gid']);
                $goodsActivityInfo['goodsnum'] = $group_activity_model->getGoodsNum($goodsActivityInfo['act_id']);
                $good_values['goodsnum']=$goodsActivityInfo['goodsnum'];
                $good_values['name']=$good_value['name'];
               // $good_values['pic_img']=$good_value['thumbnail_pic'];
                 $row_pic=explode('|',$good_value['thumbnail_pic']);
             
               if(count($row_pic)>1){
                 $good_values['pic_img']=$system->base_url().$row_pic['0'];
               }else{
                $good_values['pic_img']=$good_value['thumbnail_pic'];
               }
             if(!$good_values['pic_img']){
                 $row_picd=explode('|',$system->getConf('site.default_thumbnail_pic'));
                  $good_values['pic_img']=$system->base_url().$row_picd['0'];
              }
                $good_values['or_price']=$good_value['price'];
                $ext_info=unserialize($v['ext_info']);
                ;
                foreach($ext_info as $kc=>$vc){
                    $group_product_price[$k][]=$vc['price'];
                }
                sort($group_product_price[$k]);
               $good_values['low_price']=$group_product_price[$k][0];
                $good_values['goods_id']=$v['gid'];
                $good_values['tuangou_width']=$setting['tuangou_width'];
                $good_values['tuangou_height']=$setting['tuangou_height'];
                $good_values['start_time']=$v['start_time'];
                $good_values['end_time']=$v['end_time'];
                $good_values['url']=$system->base_url().'plugins/widgets/tuangou/groupon-bj.png';
                $good_values['js_url']=$system->base_url().'index.php?action_group_activity.html';
             $good_values['grouptimes']=time();
                
                $group_product=$group_activity->get_group_nums_list($v['gid']);
                $group_product_freez=0;
                foreach($group_product as $ka=>$va){
                $group_product_freez=$group_product_freez+$va['freez'];
                }
                if(($good_value['store']-$group_product_freez)>0){
         
                     $good_valuesa['nums']=1;
                }
               if(time()<$v['end_time']){
                     $good_valuesa['end_fou_time']=1;
                }
                if(time()<$v['start_time']){
                    $good_values['daojishi_start_time']=date('Y-m-d  H:i:s',$v['start_time']);
                     $good_values['daojishi_start_time_fou']=1;
                }

               
                if($good_valuesa['end_fou_time'] && $good_valuesa['nums']){
                    $good_values['total_group_state']=1;

                }
                $good_valuesaa[$k]=$good_values;
                 }

                return  $good_valuesaa;
            }else{
                return  false;
            }
     
   }else{
       return  false;
       
   }
}

    
?>