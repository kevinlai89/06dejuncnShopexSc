<?php
function widget_member($setting,&$system){
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
        $aMember = trust_login_list($system);
        $aMember['open_id_open'] = true; 
    }
    $aMember['valideCode'] = $system->getConf('site.login_valide');
    return $aMember;
}

    function trust_login_list($system){
        $appmgr = $system->loadModel('system/appmgr');
            $trust_tag = array(
                'taobao'=>'trustlogo1',
                'alipay'=>'trustlogo2',
                'tenpay'=>'trustlogo3',
                'renren'=>'trustlogo4',
                '139'=>'trustlogo5',
                'sina'=>'trustlogo6',
                'msn'=>'trustlogo7',
                'fastlogin'=>'trustlogo8',
            );

            $openid_logo = $appmgr->openid_logo();
            foreach($trust_tag as $tt =>$tv){
                foreach($openid_logo as $k =>$v){
                      $open = substr($openid_logo[$k]['plugin_ident'],7);
                     if($tt == $open){
                        $openid_logo[$trust_tag[$open]]['plugin_name'] = $open;
                        $openid_logo[$trust_tag[$open]]['href'] = TRUST_LOGIN_URL.'?open='.$open.'&certi_id='.$system->getConf('certificate.id').'&callback_url='.$system->base_url().'&local_type=direct';

                        unset($openid_logo[$k]);
                        unset($trust_tag[$open]);
                     }else{
                         if($trust_tag[$tt] != ''){
                            $openid_logo[$trust_tag[$tt]]['plugin_name'] = $tt;
                            $openid_logo[$trust_tag[$tt]]['href'] = '';
                         }
                     }
                }
            }
            $login['openid_login'] = $openid_logo;
        return $login;
    }

?>
