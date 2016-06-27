<?php
function widget_cfg_qf_tuan($system){
    $o=$system->loadModel('goods/products');
    return $o->orderBy();
}
?>