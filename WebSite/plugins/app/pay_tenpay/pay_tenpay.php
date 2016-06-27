<?php
require('paymentPlugin.php');
class pay_tenpay extends paymentPlugin{

    var $name = '腾讯财付通[即时到账]';//腾讯财付通
    var $logo = 'TENPAY';
    var $version = 20120405;
    var $charset = 'gb2312';
    var $applyUrl = 'http://union.tenpay.com/mch/mch_register_b2c.shtml';//即时到帐
    //var $applyUrlAgain = 'https://www.tenpay.com/mchhelper/mch_register_c2c.shtml';//担保
    //var $submitUrl = 'http://portal.tenpay.com/cfbiportal/cgi-bin/cfbiin.cgi'
    
    var $submitUrl = 'https://www.tenpay.com/cgi-bin/v1.0/pay_gate.cgi'; // 新即时到帐接口;
   // var $submitUrl = 'http://service.tenpay.com/cgi-bin/v3.0/payservice.cgi'; // 旧即时到帐接口;
    var $submitButton = 'http://img.alipay.com/pimg/button_alipaybutton_o_a.gif'; ##需要完善的地方
    var $supportCurrency =  array("CNY"=>"1");
    var $supportArea =  array("AREA_CNY");
    var $desc = '财付通是腾讯公司为促进中国电子商务的发展需要，满足互联网用户价值需求，针对网上交易安全而精心推出的一系列服务。';
    var $intro = '财付通是腾讯公司于2005年9月正式推出专业在线支付平台，致力于为互联网用户和企业提供安全、便捷、专业的在线支付服务。<br>财付通构建全新的综合支付平台，业务覆盖B2B、B2C和C2C各领域，提供卓越的网上支付及清算服务。<br>财付通先后荣膺2006年电子支付平台十佳奖、2006年最佳便捷支付奖、2006年中国电子支付最具增长潜力平台奖和2007年最具竞争力电子支付企业奖等奖项，并于2007年首创获得“国家电子商务专项基金”资金支持。<!--<br><br><font color="red">本接口需点击以下链接(二选一)进行在线签约后方可使用。</font>-->';
    var $applyProp = array("postmethod"=>"get","sp_suggestuser"=>"2289480");//代理注册参数组
    var $orderby = 2;
    var $head_charset='gb2312';
    function toSubmit($payment){
        $merId = $this->getConf($payment['M_OrderId'], 'member_id');
        $ikey = $this->getConf($payment['M_OrderId'], 'PrivateKey');
        $payment['M_Currency'] = "1";    //$order->M_Currency = "1";

        $orderdate = date("Ymd",$payment['M_Time']);    //$order->M_Time
        $payment['M_Amount'] = number_format($payment['M_Amount'],$this->system->getConf('site.decimal_digit'),".","")*100;
        $v_orderid = $merId.$orderdate.substr($payment['M_OrderId'],-10);
        $subject = $payment['M_OrderNO'];
        $spbill_create_ip = remote_addr();
        $bank_type = $payment['payExtend']['bankId']?$payment['payExtend']['bankId']:0;
        $charset = $this->system->loadModel('utility/charset');
        $desc = $charset->utf2local($subject,'zh');
        $sp_billno = $charset->utf2local($subject,'zh');
        $str="cmdno=1&date=".$orderdate."&bargainor_id=".$merId."&transaction_id=".$v_orderid."&sp_billno=".$sp_billno."&total_fee=".$payment['M_Amount']."&fee_type=".$payment['M_Currency']."&return_url=".$this->callbackUrl."&attach=".$payment['M_OrderId']."&spbill_create_ip=".$spbill_create_ip."&key=".$ikey;
        $md5string=strtoupper(md5($str));
        $return["cmdno"] = "1";
        $return["date"] = $orderdate;
        $return["bank_type"] = $bank_type;
        $return["desc"] = $subject;
        $return["purchaser_id"] = "";
        $return["bargainor_id"] = $merId;
        $return["transaction_id"] = $v_orderid;//$payment['M_OrderId'];
        $return["sp_billno"] = $payment['M_OrderNO'];   //$order->M_OrderNO;
        $return["total_fee"] = $payment['M_Amount'];    //$order->M_Amount;
        $return["fee_type"] =  $payment['M_Currency'];  //$order->M_Currency;
        $return["return_url"] = $this->callbackUrl;
        $return["attach"] = $payment['M_OrderId'];
        $return["spbill_create_ip"] = $spbill_create_ip;
        $return["sign"] = $md5string;
        return $return;
    }

    function callback($in,&$paymentId,&$money,&$message,&$tradeno){
        $cmdno=$in["cmdno"];
        $pay_result=$in["pay_result"];
        $pay_info=$in["pay_info"];
        $date=$in["date"];
        $bargainor_id=$in["bargainor_id"];
        $transaction_id=$in["transaction_id"];
        $sp_billno=$in["sp_billno"];
        $total_fee=$in["total_fee"];
        $fee_type=$in["fee_type"];
        $attach=$in["attach"];
        $sign=$in["sign"];
        $mac ="";
        $v_orderid = substr($v_order_no,-6);
        $ikey = $this->getConf($in['attach'], 'PrivateKey');
        $str = "cmdno=1&pay_result=".$in['pay_result']."&date=".$date."&transaction_id=".$transaction_id."&sp_billno=".$sp_billno."&total_fee=".$total_fee."&fee_type=".$fee_type."&attach=".$attach."&key=".$ikey;
        $md5mac=strtoupper(md5($str));
        $paymentId=$in['attach'];
        $money=intval($total_fee)/100;
        $tradeno = $in['transaction_id'];

        if($md5mac!=$sign){
           $message = '签名认证失败,请立即与商店管理员联系';
            return PAY_ERROR;
        }else{
            if($pay_result==0){
                return PAY_SUCCESS;
            }else{
                $message = '支付失败,请立即与商店管理员联系'.$pay_info;
                return PAY_FAILED;
            }
        }
    }

    function getfields(){
        $fields = array(
            'member_id'=>array(
                    'label'=>'客户号',
                    'type'=>'string'
            ),
            'PrivateKey'=>array(
                    'label'=>'私钥',
                    'type'=>'string'
            ),
            'authtype'=>array(
                'label'=>'商家支付模式',
                'type'=>'select',
                'options'=>array('0'=>'套餐包量商家','1'=>'单笔支付商家')
            )
        );
        $aVersion=$this->system->version();
        $version=$aVersion['app'].".".$aVersion['rev'];
        if($version > '4.8.5.51730'){
            $fields['ConnectType'] = array(
                'label'=>'顾客付款类型',
                'type'=>'radio',
                'options'=>array('0'=>'财付通'),
                'extendcontent'=>array(
                    array(
                        "property"=>array(
                            "type"=>"checkbox",//后台显示方式
                            "name"=>"bankId",
                            "size"=>6,
                            "extconId"=>"bankShow",
                            "display"=>1,
                            "fronttype"=>"radio", //前台显示方式
                            "frontsize"=>6,
                            "frontname"=>"showbank"
                        ),
                        "value"=>array(
                            array("value"=>"0","imgname"=>"bank_tenpay.gif","name"=>"财付通账户支付"),
                            array("value"=>"1001","imgname"=>"bank_cmb.gif","name"=>"招商银行借记卡"),
                            array("value"=>"1002","imgname"=>"bank_icbc.gif","name"=>"中国工商银行"),
                            array("value"=>"1003","imgname"=>"bank_ccb.gif","name"=>"中国建设银行"),
                            array("value"=>"1004","imgname"=>"bank_spdb.gif","name"=>"上海浦东发展银行"),
                            array("value"=>"1005","imgname"=>"bank_abc.gif","name"=>"中国农业银行"),
                            array("value"=>"1006","imgname"=>"bank_cmbc.gif","name"=>"中国民生银行"),
                            array("value"=>"1008","imgname"=>"bank_sdb.gif","name"=>"深圳发展银行"),
                            array("value"=>"1009","imgname"=>"bank_cib.gif","name"=>"兴业银行"),
                            array("value"=>"1010","imgname"=>"bank_pab.gif","name"=>"平安银行"),
                            array("value"=>"1020","imgname"=>"bank_bcom.gif","name"=>"交通银行"),
                            array("value"=>"1021","imgname"=>"bank_citic.gif","name"=>"中信银行"),
                            array("value"=>"1022","imgname"=>"bank_ceb.gif","name"=>"中国光大银行"),
                            array("value"=>"1024","imgname"=>"bank_shanghai.gif","name"=>"上海银行"),
                            array("value"=>"1025","imgname"=>"bank_hxb.gif","name"=>"华夏银行"),
                            array("value"=>"1027","imgname"=>"bank_gdb.gif","name"=>"广东发展银行"),
                            array("value"=>"1028","imgname"=>"bank_post.gif","name"=>"中国邮政储蓄银行"),
                            array("value"=>"1038","imgname"=>"bank_cmb.gif","name"=>"招商银行信用卡"),
                            array("value"=>"1032","imgname"=>"bank_bob.gif","name"=>"北京银行"),
                            array("value"=>"1033","imgname"=>"bank_unpay.gif","name"=>"网汇通"),
                            array("value"=>"1052","imgname"=>"bank_boc.gif","name"=>"中国银行")
                        )
                    )
                )
            );
        }
        return $fields;
    }
    function applyForm($agentfield){
      //$tmp_form.='<a href="javascript:void(0)" onclick="document.applyForm.submit()">立即注册即时到帐帐户</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="javascript:void(0)" onclick="document.applyFormAgain.submit()">立即注册担保帐户</a>';
      //$tmp_form= '<a href="javascript:void(0)" onclick="document.applyForm.submit()">立即申请财付通<font color="red"><b>套餐</b></font>即时账户(适合大商家)</a><br>';
      /*$tmp_form.='';
      $tmp_tc_form="<form name='applyForm' method='".$agentfield['postmethod']."' action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
      $tmp_db_form="<form name='applyFormAgain' method='".$agentfield['postmethod']."'  action='http://top.shopex.cn/recordpayagent.php' target='_blank'>";
      foreach($agentfield as $key => $val){
          if ($key == "payagentkey"){
              $tmp_tc_form.="<input type='hidden' name='".$key."' value='".$val."JSDZ'>";
              $tmp_db_form.="<input type='hidden' name='".$key."' value='".$val."JSDZ'>";
          }
          else {
              $tmp_tc_form.="<input type='hidden' name='".$key."' value='".$val."'>";
              if ($key=="sp_suggestuser")
                  $val="1202822001";
              $tmp_db_form.="<input type='hidden' name='".$key."' value='".$val."'>";
          }
      }
      $tmp_tc_form.="</form>";
      $tmp_db_form.="</form>";
      $tmp_form.=$tmp_tc_form.$tmp_db_form;*/
      return $tmp_form;
   }
}
?>
