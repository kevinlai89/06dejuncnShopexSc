<?php
function widget_qf_flash(&$setting,&$system){
    $output = &$system->loadModel('system/frontend');
    if($theme=$output->theme){
        $theme_dir = $system->base_url().'themes/'.$theme;
    }else{
        $theme_dir = $system->base_url().'themes/'.$system->getConf('system.ui.current_theme');
    }
	$image_dir = $system->base_url().'plugins/widgets/qf_flash/images/';
	$imgs = array();
	$type = $setting['type'];
	$total = 0;
	$btns = $type == 3 ? $setting['btn'][2] : $setting['btn'][$type];

	if( $type == 3 ){
		$btns['img'] = $setting['btn'][3]['img'];
		$btns['imgcurr'] = $setting['btn'][3]['imgcurr'];
	}

	foreach($setting['duceflash'] as $key => $value){
		if($value['pic']){
			$value['pic'] = str_replace('%THEME%', $theme_dir, $value['pic']);
			@preg_match('/^%(_?[\w]+)%(.+)?/i', $value["url"], $target);
			if( $target ){
				$value["target"] = $target[1];
				$value["url"] = $target[2];
			}else{
				$value["target"] = '';
			}
			$imgs[] = '{img:"'.$value['pic'].'",href:"'.$value["url"].'",title:"'.str_replace('"','&quot;',$value['alt']).'",target:"'.$value["target"].'"}';
			$total++;
		}
	}

	switch($type){
		case 0:
			$btns['w'] = $setting['width']-$btns['is']*2;
			$btn = 'left:'.$btns['is'].'px;top:'.($setting['height']-$btns['h']-$btns['is']).'px;width:'.$btns['w'].'px;height:'.$btns['h'].'px;white-space:nowrap;';
			$btns['w'] -= ($total - 1)*$btns['s'];
			$btnmar = ($btns['s'] ? 'left:'.$btns['s'] : '');
			break;
		case 1:
			$btn = 'right:'.$btns['is'].'px;top:'.($setting['height']-$btns['h']-$btns['is']).'px;height:'.$btns['h'].'px;';
			$btnmar = ($btns['s'] ? 'left:'.$btns['s'] : '');
			break;
		case 2:
		case 3:
			$btn = ($type==2 ? 'left:' : 'right:').$btns['is'].'px;top:'.$btns['is'].'px;width:'.$btns['w'].'px;';
			$btnmar = ($btns['s'] ? 'top:'.$btns['s'] : '');
			break;
	}

	$aem = ($margin=floor(($btns['h']-21)/2))>0?'margin:'.($type==0&&($btns['img']!='none'||$btns['imgcurr']!='none')?$margin-1:$margin).'px 0;':'';
	$aem .= 'text-align:center;line-height:20px;line-height:24px\9;_line-height:21px;';

	$btns['img'] = preg_match('/^type/i', $btns['img']) ? $image_dir.$btns['img'] : str_replace('%THEME%', $theme_dir, $btns['img']);
	$btns['imgcurr'] = preg_match('/^type/i', $btns['imgcurr']) ? $image_dir.$btns['imgcurr'] : str_replace('%THEME%', $theme_dir, $btns['imgcurr']);

	$btns['pos'] = intval($btns['pos']);
	if($type>0){
		$btns['img'] = $btns['img'] && $btns['img'] != 'none' ? 'background:url('.$btns['img'].') no-repeat;' : '';
		$btns['imgcurr'] = $btns['imgcurr'] ? 'background:'.($btns['imgcurr']=='none'?$btns['imgcurr']:'url('.$btns['imgcurr'].') no-repeat').';' : '';
	}else{
		$btns['posx'] = (in_array($btns['posx'], array('center','right')) ? $btns['posx'] : '0').($btns['pos'] ? ' 0' : ' bottom');
		$btns['img'] = $btns['img'] && $btns['img'] != 'none' ? 'background:url('.$btns['img'].') no-repeat '.$btns['posx'].';' : '';
		$btns['imgcurr'] = $btns['imgcurr'] ? 'background:'.($btns['imgcurr']=='none'?$btns['imgcurr']:'url('.$btns['imgcurr'].') no-repeat '.$btns['posx']).';' : '';
	}
	$btns['pos'] = $btns['img'] || $btns['imgcurr'] ? $btns['pos'] : 0;	

	$setting['imgs'] = implode(',', $imgs);
	$setting['imgw'] = $setting['imgw'] ? $setting['imgw'] : $setting['width'];
	$setting['imgh'] = $setting['imgh'] ? $setting['imgh'] : $setting['height'];
	$setting['dones'] = $setting['done']*1000;
	$setting['bgimg'] = $setting['bgimg'] ? str_replace('%THEME%', $theme_dir, $setting['bgimg']) : $image_dir.'loading.gif';
	$setting['css'] = array(
		'body' => 'width:'.$setting['width'].'px;height:'.$setting['height'].'px;'.
			($setting['border']>0?'border:'.$setting['border'].'px solid '.$setting['bordercolor'].';' : '').
			'background:'.($setting['bgcolor'] ? '#F0F0F0': $setting['bgcolor']).' url('.$setting['bgimg'].') no-repeat center;',
		'bgimg' => $setting['bgimg'],
		'imgout' => 'width:'.$setting['imgw'].'px;height:'.$setting['imgh'].'px;',
		'btn' => $btn,
		'li' => 'background:'.$btns['color'].';height:'.$btns['h'].'px;'.
			($btns['alpha1']>0&&$btns['alpha1']<100 ? 'filter:alpha(opacity='.$btns['alpha1'].');opacity:'.($btns['alpha1']/100) : '').';',
		'licurr' => 'background:'.$btns['hover'].';'.
			($btns['alpha2']>=0&&$btns['alpha2']<=100 ? 'filter:alpha(opacity='.$btns['alpha2'].');opacity:'.($btns['alpha2']/100) : '').';',
		'a' => 'text-decoration:none;color:'.$btns['fncolor'].';height:'.$btns['h'].'px;'.$btns['img'],
		'acurr' => 'color:'.$btns['fnhover'].';'.$btns['imgcurr'],
		'aem' => 'padding:2px 5px;display:block;height:17px;'.$aem.'overflow:hidden;text-decoration:none;'.($type!=1 && $btns['fs']>0 ? 'font-size:'.$btns['fs'].'px;' : ''),
	);
	$setting['loadtype'] = intval($setting['loadtype']);

	$btn = array();
	$btn[] = 'w:'.$btns['w'];
	$btn[] = 'h:'.$btns['h'];
	$btn[] = 'mar:"'.$btnmar.'"';
	$btn[] = 'pos:'.$btns['pos'];
	$setting['btns'] = implode(',', $btn);

	if(!$type){
		$btns['w1'] = round($btns['w']/$total);
		$btns['w2'] = $btns['w']-($total-1)*$btns['w1'];
	}else{
		$btns['w1'] = $btns['w2'] = $btns['w'];
	}
	$btns['mar'] = $btnmar;
	$setting['bt'] = $btns;

    return $setting;
}
?>
