<?php
function widget_qf_article(&$setting,&$system){
	$o=$system->loadModel('content/article');
    $setting['colums']=$setting['colums']?$setting['colums']:2;
    $setting['onSelect']=$setting['onSelect']?$setting['onSelect']:0;
    $setting['max_length']=$setting['max_length']?$setting['max_length']:35;
	if($setting['smallPic']!=6)$setting['smallPic'] and $setting['smallPic'] = $system->base_url() . 'statics/icons/' . $setting['smallPic'];
	$output = $system->loadModel('system/frontend');
    if($output->theme){
        $theme_dir = $system->base_url().'themes/'.$output->theme;
    }else{
        $theme_dir = $system->base_url().'themes/'.$system->getConf('system.ui.current_theme');
    }
    $setting['titleImgSrc'] = str_replace('%THEME%',$theme_dir,$setting['titleImgSrc']);	
    if($setting['iftop']=='on'){
        $orderBy = array("1"=>array('iftop','desc',',uptime','asc'),"2"=>array('iftop','desc',',uptime','desc'),"3"=>array('iftop','desc',',view','asc'),"4"=>array('iftop','desc',',view','desc'));
    }else{
        $orderBy = array("1"=>array('uptime','asc'),"2"=>array('uptime','desc'),"3"=>array('view','asc'),"4"=>array('view','desc'));
    }
    if($setting['columNum']>1){
        $return=array();
        for($i=1;$i<=$setting['columNum'];$i++){
            $return[]=$o->getList('title,article_id,iftop,picture,intro,source,view,editor,keywords,uptime',array('node_id'=>$setting['id'.$i],'ifpub'=>1),0,$setting['limit'],$orderBy[$setting['order'.$i]]);
             $setting['id'][$i-1]=$setting['id'.$i];
        }
        return $return;		
    }else{
        return $o->getList('title,article_id,iftop,picture,intro,source,view,editor,keywords,uptime',array('node_id'=>$setting['id1'],'ifpub'=>1),0,$setting['limit'],$orderBy[$setting['order1']]);
    } 	
}
?>
