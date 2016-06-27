<?php
/**
 * Rocky Zhang 2012.6.5
*/

class app_goodsassistant extends app {
    var $ver = '2.0';
    var $name = '商品助理2.0';
    var $author = 'shopex rocky anjiaxin';
    var $help = 'http://www.shopex.cn/help/ekaidian-goodsassistant.html';

    function install(){
       // if ( !$this->backup(dirname(__FILE__).'/overwrite',BASE_DIR) ) {
          //  return false;
        //}        return parent::install();
    }

    function backup($fromdir,$todir,$rewrite=false) {
        if ( !method_exists('app','backup') ) {
            if ( strpos(CORE_INCLUDE_DIR,'/include_v5') > 0 ) {
                $app_file = dirname(__FILE__).'/overwrite/core/include_v5/app.php';
            } else {
                $app_file = dirname(__FILE__).'/overwrite/core/include/app.php';
            }
            if ( !copy($app_file,CORE_INCLUDE_DIR.'/app.php') ) {
                return false;
            }
            header('Location:?ctl=system/appmgr&act=do_install&p[0]='.$this->ident); exit;
        }
        return parent::backup($fromdir,$todir,$rewrite);
    }
}
