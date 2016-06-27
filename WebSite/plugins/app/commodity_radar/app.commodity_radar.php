<?php
class app_commodity_radar extends app{
    var $ver = 1.0;
    var $name = '商品雷达';
    var $outname = '商品雷达';
    var $_app_id = 'commodity_radar';
    var $_col_id = '_commodity_radar';
    var $author = 'shopex';
    var $help_tip = ''; 
    var $html_url = '/app/commodity_radar/view/finder_name.html';

    function ctl_mapper(){
        return array();
    }
    
    function listener(){
        return array();
    }
    
    function crontab_queue(){
        return array();
    }
    
    function output_modifiers(){
        return array(
            'admin:default:index' => 'default_modifiers:index',
            'admin:goods/product:index' => 'default_modifiers:product_list'
        );
    }
    
    function enable(){
        return $this->verify();
    }
    
    function install(){
        $this->verify();
        parent::install();
        return true;
    }

    function verify(){return true;
        if (!class_exists('commodity_radar_public')) require_once 'commodity_radar_public.php';
        $radar= new commodity_radar_public();
        
        if (!$radar->verify_license()){
            //exit('证书无此使用权限，请购买使用正版。');
        }
    }
}
