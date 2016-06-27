<?php
function widget_cfg_tuangou($system){
     $appmgr = $system->loadModel('system/appmgr');
     $status=$appmgr->getPluginInfoByident('group_activity','disabled');
    
   if($status['disabled']=='false'){
        $group_activity=&$system->loadModel('plugins/group_activity/group_activity');
            if($row=$group_activity->get_group_list()){
              
                 $objGoods = &$system->loadModel('trading/goods');
               foreach($row as $k=>$v){
                   $good_value=$objGoods->getFieldById($v['gid'],$aField=array('*'));
                   $good_values[]=$good_value;
               }
              
               return array_reverse($good_values);
            }else{
                return false;
            }
      
       
   }else{
       return false;
   }
}
?>