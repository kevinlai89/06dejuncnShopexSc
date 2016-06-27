<?php
    class ctl_cron extends shopPage{

        function ctl_cron(){
            parent::shopPage();
        }

        function exec_queue(){
            $appmgr = $this->system->loadModel("system/appmgr");
            $queue = unserialize($this->system->getConf("system.crontab_queue"));
            if($queue[$_POST['key']]){
				$method_vcount = explode(':',$_POST['key']);
			    $method_count = count($method_vcount);
				if($method_count == 2){
					$objMatrix = $method_vcount[0];
					$matrix_method = $method_vcount[1];
					$matrix = &$this->system->loadModel($objMatrix);
					if(method_exists($matrix,$matrix_method)){
						$matrix->$matrix_method();// system cron_tab
					}
				}else{
					list($objCtl,$act_method) = $appmgr->get_func($_POST['key']);//app cron_tab
					if(method_exists($objCtl,$act_method)){
						$objCtl->$act_method();
					}
				}
            }

            $goods = $this->system->loadModel("goods/products");
            $good_ids = $goods->update();
        }
    }
?>