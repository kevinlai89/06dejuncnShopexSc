<?php
function widget_qf_widefocus2(&$setting,&$system){
    $output = &$system->loadModel('system/frontend');
    if($theme=$output->theme){
        $theme_dir = $system->base_url().'themes/'.$theme;
    }else{
        $theme_dir = $system->base_url().'themes/'.$system->getConf('system.ui.current_theme');
    }
    foreach($setting['focus'] as $focus){
        $focus['imgurl'] = str_replace('%THEME%',$theme_dir,$focus['imgurl']);
        $data[] = $focus;
    }
    return $data;

}