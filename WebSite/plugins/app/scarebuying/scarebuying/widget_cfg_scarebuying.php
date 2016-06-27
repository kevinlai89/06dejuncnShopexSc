<?php
function widget_cfg_scarebuying($system){
     $appmgr = $system->loadModel('system/appmgr');
     $status=$appmgr->getPluginInfoByident('scarebuying','disabled');
    
   if($status['disabled']=='false'){
        $group_activity=$system->loadModel('plugins/scarebuying/scare');
            if($row=$group_activity->getList()){  
                 $objGoods = &$system->loadModel('trading/goods');
               foreach($row as $k=>$v){
                   $good_value=$objGoods->getFieldById($v['goods_id'],$aField=array('*'));
                   $good_values[]=$good_value;
               }
               return $good_values;
            }else{
                return false;
            }
      
       
   }else{
       return false;
   }
}
?>