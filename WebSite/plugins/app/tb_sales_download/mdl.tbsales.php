<?php
require_once(CORE_DIR.'/model/trading/mdl.order.php');
class mdl_tbsales extends mdl_order{

    function do_rate_sync($rate_data,$order_id){
        $this->system->call("traderate_download",$rate_data,$order_id);   
    }
}
?>