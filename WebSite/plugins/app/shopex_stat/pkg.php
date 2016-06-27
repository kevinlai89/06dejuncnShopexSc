<?php

$basedir = dirname(dirname(dirname(dirname(__FILE__))));
include $basedir.'/core/func_ext.php';

$cfg = array(
    'tar','v5',
);

$overs = array(
    '/core/include/app.php',
    '/core/include/defined.php',
    '/core/include/shopPage.php',
    
    '/core/admin/controller/system/ctl.appmgr.php',
    '/core/admin/controller/system/ctl.setting.php',
    
    '/core/model/system/mdl.appmgr.php',
);

foreach( $overs as $f ) {
    $f_ = $f;
    mkdir_p(dirname(dirname(__FILE__).'/overwrite'.$f_));
    if (!copy($basedir.$f,dirname(__FILE__).'/overwrite'.$f_)) echo '<br/>failed:'.$f;
    
    // v5
    if ( strpos($f,'/include/') ) {
        $f_ = str_replace('/include/','/include_v5/',$f);
        mkdir_p(dirname(dirname(__FILE__).'/overwrite'.$f_));
        if( !copy($basedir.$f,dirname(__FILE__).'/overwrite'.$f_) ) echo '<br/>failed:'.$f;
    }
    if ( strpos($f,'/model/') ) {
        $f_ = str_replace('/model/','/model_v5/',$f);
        mkdir_p(dirname(dirname(__FILE__).'/overwrite'.$f_));
        if (!copy($basedir.$f,dirname(__FILE__).'/overwrite'.$f_) ) echo '<br/>failed:'.$f;
    }
}


echo '<br/>pkg complete!';

