<?php
/**
 * @author chenping
 * @version 1.0
 * @time:2010-3-2 15:51
 * //=================================
 * app.group_activity.php
 * 团购活动插件
 * //=================================
 *
 */
class app_group_activity extends app{
    var $ver=1.1;
    var $name='团购活动';
    var $help='';
    var $author='shopex';

    function install(){
        $modTag = $this->system->loadModel('system/tag');
        $modTag->newTag('团购','order');
       if(!$this->xCopy(dirname(__FILE__).'/tuangou',dirname(dirname(dirname(__FILE__))).'/widgets/tuangou',1)){
         echo '安装失败!';exit;
       }
        
      

        return parent::install();
    }
    function getMenu(&$menu){
        $menu['sale']['items'][10]=array(
            'type'=>'group',
            'label'=>__('团购'),
            'items'=>array(
                array(
                    'type' =>'menu',
                    'label'=>__('团购活动列表'),
                    'link' =>'index.php?ctl=plugins/group_activity&act=group_activity_index',
                ),
                
            ),
        );
    }
    function ctl_mapper(){
       
        $return_array= array(
            //*************************** admin:order *********************************//
            //'admin:order/order:*' => 'admin_ctl_order:*',
            //'admin:order/order:index' => 'admin_ctl_order:index',
            'admin:order/order:delete'=> 'admin_ctl_order:delete',
            // 'admin:order/order:save_col_setting' => 'admin_ctl_order:save_col_setting',
            //'admin:order/order:colsetting'    => 'admin_ctl_order:colsetting',
            //'admin:order/order:toRefund' => 'admin_ctl_order:toRefund',
            //*************************** product *********************************//
            //'shop:product:index' =>    'shop_ctl_product:index',
        
        
            //*************************** cart *********************************//
            //'shop:cart:groupBuyCheckout' => 'shop_ctl_cart:groupBuyCheckout',
            'shop:cart:shipping'         => 'shop_ctl_cart:shipping',
            //'shop:cart:total'              => 'shop_ctl_cart:total',
            //'shop:cart:checkout'         => 'shop_ctl_cart:checkout',
            //*************************** order *********************************//
            //'shop:order:create'          => 'shop_ctl_order:create',
            //            'shop:order:createGroupActOrder' => 'shop_ctl_order:createGroupActOrder',
            //            'shop:order:indexGroup'    => 'shop_ctl_order:indexGroup',
            //            'shop:order:detailGroup' => 'shop_ctl_order:detailGroup',
            //*************************** member *********************************//
            'shop:member:orders'        => 'shop_ctl_member:orders',
            'shop:member:orderdetail'    => 'shop_ctl_member:orderdetail',
            'shop:member:index' => 'shop_ctl_member:index',
            'shop:member:orderpay' => 'shop_ctl_member:orderpay',
            //*************************** admin:messenger *********************************//
            'admin:member/messenger:index' => 'admin_ctl_messenger:index',
            'admin:member/messenger:edTmpl' => 'admin_ctl_messenger:edTmpl',
            'admin:member/messenger:save' => 'admin_ctl_messenger:save',
            //'admin:editor:selectobj'=>'admin_ctl_editor:selectobj',
        
        
        );
        $appmgr = $this->system->loadModel('system/appmgr');
        $status=$appmgr->getPluginInfoByident('scarebuying','disabled');
        if($status['disabled']!='false'){
        $return_array['shop:product:index'] ='shop_ctl_product:index';
        $return_array['shop:cart:total'] ='shop_ctl_cart:total';
        $return_array['shop:cart:checkout'] ='shop_ctl_cart:checkout';
        $return_array['shop:order:create'] ='shop_ctl_order:create';
         }
         return $return_array;
       

    }
     function uninstall(){
     
      if(is_dir(dirname(dirname(dirname(__FILE__))).'/widgets/tuangou')){
     if(!$this->deldir(dirname(dirname(dirname(__FILE__))).'/widgets/tuangou')){
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