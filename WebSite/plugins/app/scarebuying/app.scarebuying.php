<?php
class app_scarebuying extends app {
    var $ver=1.50;//2011-8-5 由1.1升级为1.50  50表示svn版本 by zhangxuehui
    var $author='shopex';
    var $name='高级限时抢购';

    function install(){
        if(!$this->xCopy(dirname(__FILE__).'/scarebuying',dirname(dirname(dirname(__FILE__))).'/widgets/scarebuying',1)){
            echo '安装失败!';exit;
        }
        return parent::install();

    }
    function ctl_mapper(){
        //$map=$this->system->setConf('system.ctlmap','');var_dump($map);exit;
        return array(
            //admin:product
            'admin:goods/product:edit'=>'admin_cct_product:edit',
            'admin:goods/product:addNew'=>'admin_cct_product:addNew',
            'admin:goods/product:toAdd'=>'admin_cct_product:toAdd',
            'admin:goods/product:update'=>'admin_cct_product:update',


            //shop:gallery
            'shop:product:index'=>'shop_cct_product:index',
            'shop:gallery:index'=>'shop_cct_gallery:index',
            'shop:gallery:*'=>'shop_cct_gallery:*',  //监控所有方法
            'shop:gallery:grid'=>'shop_cct_gallery:grid',
            'shop:gallery:list'=>'shop_cct_gallery:list',

            //shop:cart
            'shop:cart:*'=>'shop_cct_cart:*',
            'shop:cart:checkout'=>'shop_cct_cart:checkout',
            'shop:cart:updateCart'=>'shop_cct_cart:updateCart',
            'shop:cart:addGoodsToCart'=>'shop_cct_cart:addGoodsToCart',
            'shop:cart:total'=>'shop_cct_cart:total',
            'shop:cart:index'=>'shop_cct_cart:index',
            'shop:cart:removeCart'=>'shop_cct_cart:removeCart',

            //shop:paycenter 限时抢购付款bug
            'shop:paycenter:order'=>'shop_cct_paycenter:order',
            //shop:order
            'shop:order:*'=>'shop_cct_order:*',
            'shop:order:create'=>'shop_cct_order:create',

            //admin:order
            'admin:order/order:cancel'=>'admin_cct_order:cancel',
            'admin:order/order:recycle'=>'admin_cct_order:recycle',

        );
    }


    function output_modifiers(){
        return array(
            //'shop:product:index'=>'shop_cct_product:product_index',
            //限时抢购于系统关联预展库存 2011-7-5 by zhangxuehui
            'admin:order/order:toDelivery'=>'admin_cct_order_m:toDelivery',
        );
    }
    //限时抢购于系统关联预展库存 2011-7-5 by zhangxuehui
    function listener(){
            return array(
               // 'trading/order:payed'=>'event_handler:payed',
        );
    
    }
    function uninstall(){

        if(is_dir(dirname(dirname(dirname(__FILE__))).'/widgets/scarebuying')){
            if(!$this->deldir(dirname(dirname(dirname(__FILE__))).'/widgets/scarebuying')){
                echo '卸载失败';exit;
            }
        }
        parent::uninstall();
        return true;

    }

    function xCopy($source, $destination, $child){
        if(!is_dir($source)){
            echo("Error:the $source is not a direction!");
            return 0;
        }
        if(!is_dir($destination)){
            mkdir($destination,0777);
        }
        $handle=dir($source);
        while($entry=$handle->read()) {
            if(($entry!=".")&&($entry!="..")){
                if(is_dir($source."/".$entry)){
                    if($child)    $this->xCopy($source."/".$entry,$destination."/".$entry,$child);
                }else{
                    copy($source."/".$entry,$destination."/".$entry);
                }
            }
        }

        return true;
    }

    function deldir($dir) {
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->deldir($fullpath);
                }
            }
        }

        closedir($dh);

        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}
