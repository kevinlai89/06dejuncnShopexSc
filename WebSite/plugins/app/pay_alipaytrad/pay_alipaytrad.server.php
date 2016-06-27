<?php
require_once('paymentPlugin.php');
class pay_alipaytrad extends paymentPlugin{
    function pay_alipaytrad_callback($in,&$paymentId,&$money,&$message,&$tradeno){
        ksort($in);
        foreach($in as $k =>$v){
            if($k!='sign'&&$k!='sign_type'&&$k!='gOo'){
                if($mac=="")
                    $mac = "{$k}={$v}";
                else
                    $mac .= "&{$k}={$v}";
            }
        }  
        $paymentId = $in['out_trade_no'];
        $tradeno = $in['trade_no'];
        $money = $in['total_fee'];
        $ikey = $this->getConf($paymentId,"PrivateKey");
        if(trim($ikey) == '') $ikey = 'afsvq2mqwc7j0i69uzvukqexrzd0jq6h';
        if (md5($mac.$ikey)==$in['sign']){
            if($in['trade_status']=="WAIT_SELLER_SEND_GOODS"){
                if ($this->getConf($in['out_trade_no'],'member_id')){
                    echo "success";
                    return PAY_PROGRESS;
                }else{
                    echo "fail";
                    return PAY_FAILED;
                }
            }
			elseif($in['trade_status']=="WAIT_BUYER_CONFIRM_GOODS" || $in['trade_status']=="WAIT_BUYER_PAY" || $in['trade_status']=="TRADE_CLOSED"){
				echo "success";
				return PAY_IGNORE;
			}
			elseif($in['trade_status']=="TRADE_FINISHED"){
                echo "success";
                return PAY_SUCCESS;
            }
        }else{
            echo "fail";
            return PAY_ERROR;
        } 
    }
}
?>
