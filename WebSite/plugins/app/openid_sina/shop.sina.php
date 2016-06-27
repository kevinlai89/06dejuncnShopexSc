<?php
class shop_sina extends ShopPage{
	 var $noCache = true;
     var $oauth_token;
	 var $oauth_token_secret;
     var $consumer_key;
	 var $consumer_secret;

	  function shop_sina(){
		 $this->system = &$GLOBALS['system'];
         $this->db = &$this->system->database();
	  }

      function response(){
        if($_GET['id']){
            $data['tokenId'] = $_GET['id'];
            $data['oauth_verifier'] = $_GET['oauth_verifier'];
            $data['appkey'] = $this->system->getConf('app.openid_sina.appkey');
            $data['appsecret'] = $this->system->getConf('app.openid_sina.appsecret');
            $data['sign'] = $this->get_sign($data);
            $make_url = $this->make_url($data);
            header('Location:http://openid.ecos.shopex.cn/return.php?'.$make_url);
		}else{
            echo '信息丢失,请重新登陆';exit;
		}
      }

        function get_sign($params) {
            $sort_array = array();
            $arg = "";
            $sort_array = $this->arg_sort($params);
            while (list ($key, $val) = each ($sort_array)) {
                $arg.=$key."=".$val."&";
            }

            $token = $this->system->getConf('certificate.token');
            $sign = md5(substr($arg,0,count($arg)-2).$token);//去掉最后一个问号
         
            return $sign;
        }

        function arg_sort($array) {
            ksort($array);
            reset($array);
            return $array;
        }
      
        function make_url($params_url) {
            $arg = "";
            while (list ($key, $val) = each ($params_url)) {
                $arg.=$key."=".urlencode($val)."&";
            }
            $result = substr($arg,0,strlen($arg)-1);
            return $result;
        }

}
?>