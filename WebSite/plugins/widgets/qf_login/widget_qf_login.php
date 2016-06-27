<?php
function widget_qf_login(&$setting, &$system){
	if($setting['tpl_index'] == 'infobar.html'){
		if($_COOKIE['UNAME']){
			$logout = '<a href="'.$system->mkUrl('passport','logout',array()).'">'.$setting['logout'].'</a>';
			$s = str_replace('{username}','<cite>'.$_COOKIE['UNAME'].'</cite>',$setting['logged']);
		}else{
			$login='<a class="login" href="'.$system->mkUrl('passport','login',array()).'">'.$setting['login'].'</a>';
			$register = '<a class="signup" href="'.$system->mkUrl('passport','signup',array()).'">'.$setting['signup'].'</a>';
			$lost = '<a href="'.$system->mkUrl('passport','lost',array()).'">'.$setting['lost'].'</a>';
			$s = $setting['logon'];
		}
		return $s ? str_replace(
			array('{username}', '{logout}', '{login}', '{register}', '{lost}'), 
			array('<cite>'.$_COOKIE['loginName'].'</cite>', $logout, $login, $register, $lost), 
			$s) : '';
	}
	if($setting['tpl_index'] == 'floatbar.html'){
		$retrun['target'] = ' target="_blank"';
		if($_COOKIE['UNAME']){
			$retrun['prompt'] = '<span class="fl"><strong>'.$_COOKIE['UNAME'].'</strong></span><span class="fr"><a href="'.$system->mkUrl('member','index',array()).'">去'.$setting['account'].'首页&nbsp;&gt;</a></span>';
			if($system->request['action']['controller'] == 'member'){
				$retrun['target'] = '';
			}
			$retrun['login'] = true; 
		}else{
			$retrun['prompt'] = '<span class="fl">您好，请登录</span><span class="fr"><a href="'.$system->mkUrl('passport','login',array()).'" class="btn-login">'.$setting['alogin'].'</a></span>';
		}
		return $retrun;
	}
	return 'setting failed';
    /*
	$aMember = $system->request['member'];
    $appmgr = $system->loadModel('system/appmgr');
    $login_plugin = $appmgr->getloginplug();
    foreach($login_plugin as $key =>$value){
        $object = $appmgr->instance_loginplug($value);
        if(method_exists($object,'getWidgetsHtml')){
            $aMember['login_content'][] = $object->getWidgetsHtml();
        }
    }
    if($appmgr->openid_loglist()){
        $aMember['open_id_open'] = true;
    }
    $aMember['valideCode'] = $system->getConf('site.login_valide');
    return $aMember;
	*/
}
?>
