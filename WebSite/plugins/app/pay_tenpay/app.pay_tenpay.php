<?php
class app_pay_tenpay extends app{
    var $ver = 1.4;
    var $name='腾讯财付通[即时到账]';
    var $website = 'http://www.shopex.cn';
    var $author = 'shopex';
    var $help = '';
    var $type = 'tenpay';
    function install(){
        parent::install();
        return true;
    }

    function uninstall(){
        $this->db->exec('delete from sdb_payment_cfg where pay_type ="'.$this->type.'"');
        return parent::uninstall();
    }

    function ctl_mapper(){
        return array(

        );
    }

}
?>