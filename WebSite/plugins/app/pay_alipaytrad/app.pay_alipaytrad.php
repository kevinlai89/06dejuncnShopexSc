<?php
class app_pay_alipaytrad extends app{
    var $ver = 1.7;
    var $name='支付宝[担保交易]';
    var $website = 'http://www.shopex.cn';
    var $author = 'shopex';
    var $help = '';
    var $type = 'alipaytrad';
    function install(){
        $this->cop_file();
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

    function cop_file(){ //1.7特有函数,如app包更新至最新建议删掉这个方法
		if(file_exists(PLUGIN_DIR.'/app/pay_alipaytrad/upfile/core')){
		  $original = PLUGIN_DIR.'/app/pay_alipaytrad/upfile/core/';

		  if(file_exists($ctl_payment = CORE_DIR.'/admin/controller/trading/ctl.payment.php')){
			   @copy($original.'/admin/controller/trading/ctl.payment.php',$ctl_payment);
		  }
		  if(file_exists($view_payment = CORE_DIR.'/admin/view/payment/pay_new.html')){
			   @copy($original.'/admin/view/payment/pay_new.html',$view_payment);
		  }
		  if(file_exists($model = CORE_DIR.'/model/system/mdl.appmgr.php')){
			   @copy($original.'model/system/mdl.appmgr.php',$model);
		  }
		  if(file_exists($model_v5 = CORE_DIR.'/model_v5/system/mdl.appmgr.php')){
			   @copy($original.'model_v5/system/mdl.appmgr.php',$model_v5);
		  }
		}
	}

}
?>