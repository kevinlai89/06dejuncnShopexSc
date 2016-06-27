<?php
function widget_cfg_goods($system){
    $o=$system->loadModel('goods/products');
    $data['order'] = $o->orderBy();
    $data['tag_status'] = $system->getConf('site.tag_status');
    return $data;
}
?>