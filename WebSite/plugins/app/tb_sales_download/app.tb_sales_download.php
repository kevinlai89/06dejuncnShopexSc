<?php
class app_tb_sales_download extends app{
    var $ver = 1.3;
    var $name='下载淘宝销售记录';
    var $website = 'http://www.shopex.cn';
    var $author = 'shopex';
    var $depend = array('taobao_goods'=>'同步管理淘宝商品','tb_order_ctl'=>'同步处理淘宝订单');
    var $help = 'http://www.shopex.cn/help/ShopEx48/help_shopex48-1264415044-12132.html';
    var $uninstall = 'false';

  function ctl_mapper(){
     return array(
        'shop:product:index'=>'product_ctl:index',
        'shop:product:selllog'=>'product_ctl:selllog',

     );
  }

    /*function install(){
		 $logs = $this->db->selectrow('select * from sdb_sell_logs');
         if(!array_key_exists('message',$logs)){
            $sql ='alter table `sdb_sell_logs` add message varchar(200) default "" ';
			$this->db->exec($sql);

		}
         parent::install();
	}*/

    function getMenu(&$menu){
        $menu['tools']['items']['tb_sell']= array('type'=>'group',
                'label'=>__('下载淘宝销售记录'),
                'items'=>array(
                    array('type'=>'menu',
                        'label'=>__('下载记录和评价'),
                        'link'=>'index.php?ctl=plugins/sales_ctl&act=dotaobaorate'
                    ),
               )
        );
    }


     
    function getContents($params,$session=false,$method='get',$no_red=false){
        $center = $this->system->loadModel('plugins/tb_sales_download/center_send');
        $tb_api_mess = $center->getTbAppInfo();
        $params = array_merge($params,$tb_api_mess['result_msg']);
        return $this->system->call('tb_mess_send',$params,$session,$method,$no_red);
    }

    function getTbloginurl($url){
        $center = $this->system->loadModel('plugins/tb_sales_download/center_send');
        if($center_msg =$center->getTbAppInfo()){
            $tbs_params['app_key'] = $center_msg['result_msg']['app_key'];
            $tbs_params['app_secret'] = $center_msg['result_msg']['app_secret'];
        };
        $tbs_params['sign_method'] = "md5";
        $tbs_params['timestamp'] = date('Y-m-d H:i:s',time());
        $tbs_params['target'] = substr(($url?$url:$this->system->base_url().'shopadmin/index.php?ctl=plugins/order_ctl'),7);
        $login_url = "http://container.api.taobao.com/container/identify";

        foreach($tbs_params as $key=>$value){
            if($key!='app_secret')
                $ps_s[] = $key."=".$value;
        }
        $ps_s[]="sign=".$this->makeSign($tbs_params);
        $tb_url = $login_url.'?'.implode("&",$ps_s);
        return  $tb_url;
    }
        
    function timeout(){
        echo 'fail';
        exit;
    }


    function setting_load(){
        $center = $this->system->loadModel('plugins/tb_sales_download/center_send');
        $mess = $center->get_tb_nick();
        if($mess){
            $nick = $mess['result_msg'];
            $this->system->setConf('app.tb_sales_download.nick',$nick,true);
        }
    }

    function setting_save(){
        $center = $this->system->loadModel('plugins/tb_sales_download/center_send');
        $setting = $_POST['setting'];
        if(!$center->open_servies()){
            echo '服务开通失败';
            exit;
        }
        if($center->set_tb_nick($setting['app.tb_sales_download.nick'])){
            foreach($setting as $key=>$val){
                $this->system->setConf($key,$val);
            }    
        }else{
            echo '无法绑定该用户';
        }
    }

    function makeSign($aParams){
        $ret = '';
        ksort($aParams);
        reset($aParams);
        if(isset($aParams['app_secret']) && $aParams['app_secret']){
            $app_secret = $aParams['app_secret'];
            unset($aParams['app_secret']);
        }
        $ret = $app_secret;
        foreach($aParams as $key=>$value){
            if($key != "sign" && $value)
                $ret .= $key.$value;
        }
        return strtoupper(md5($ret.$app_secret));
    }

}