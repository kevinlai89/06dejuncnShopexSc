<?php
class openid_renren_product_modifiers extends pageFactory{
    var $api_key1;
    var $base_url;

    function  product_index(&$content){

        if($api_key = $this->system->getConf('app.openid_renren.apikey')){
           $api_key1 = $api_key;
        }
        $this->base_url=$this->system->base_url();
        $this->api_key1=$api_key1;
/*分享*/
        $product=$this->system->loadModel('plugins/openid_renren');
        preg_match('/.value="(\d+)"/',$content,$match);
        $product_id = $match[1];
        $member_lv = intval($this->system->request['member_lv']);
        $product_info=$product->get_product($product_id,$member_lv);
        $product_image=$product->get_by_goods_id($product_id);
        foreach ($product_image as $key =>$val){
                 $p = strpos($val['small'],'|');
                 if($p!==false){
                     $val['small'] = substr($val['small'],0,$p);
                 }
                 
                 $gimage=$this->system->loadModel('goods/gimage');
                 $small=$gimage->getUrl($val['small']);

                 $img_storager.="'".$small."',";
                 
                 
        }
        $img_storager=substr($img_storager,0,strlen($img_storager)-1);

        if($product_info['brief']){
           $pro_des='商品描述：'.$this->get_desc($product_info['brief'],130).str_repeat('&nbsp;',5);
        }else{
           $pro_des='';
        }
       $description = $pro_desc.' 品  牌：'.$product_info['brand_name']['brand_name'].'&nbsp;&nbsp;&nbsp; 价  格：'.$product_info['price'].'';
       
       $find=array('<p><span>分享到：</span>','</div>','</body>','<a href="#" rel="nofollow" onclick="return false;" class="btn-fav">','<html xmlns="http://www.w3.org/1999/xhtml">');

         $replace[]=$find[0].'&nbsp;<span><xn:share-button type="icon_count_top" label=" ">
      <input type="hidden" name="medium"       value="link"/>  
      <input type="hidden" name="title"        value="'.$product_info['name'].'"/>
      <input type="hidden" name="message"      value=""/>
      <input type="hidden" name="description"      value="'.$description.'"/>
      <input type="hidden" name="image_src"      value="'.'['.$img_storager.']'.'"/>
      </xn:share-button></span>
	  <style>.goodspic .xn_share_top{float:none;}</style>
';

       $replace[]='</div>';

/*收藏*/
       if($_COOKIE['LOGIN_UNAME']){
            $get_authorize=$product->get_auth_value($_COOKIE['LOGIN_UNAME']);
       }
       $gimage=$this->system->loadModel('goods/gimage');
       $product_info['thumbnail_pic']=$gimage->getUrl($product_info['thumbnail_pic']);
       $aa=str_replace(' ','<kongge>',$product_info['name']);
       $title_data='{"feedtype":"'.$aa.'"}';

       $content_data='{"images":[{"src":"'.$product_info['thumbnail_pic'].'","href":"'.$this->base_url.'?product-'.$product_id.'.html"}],"content":"'.$product_info['brief'].'"}';

       $title_data=urlencode($title_data);
       $content_data=urlencode($content_data);
       $certi_id=urlencode($this->system->getConf("certificate.id"));
       $_session_key=urlencode($_REQUEST[$this->api_key1.'_session_key']);
       $params=array();
       $params['template_id']=1;
       //$params['title_data']=$title_data;
       $params['body_data']=$content_data;
       $params['certi_id']=$certi_id;
       $params['session_key']=$_session_key;
       $params['authorize_item']=$get_authorize['authorize_item'];

       
//openid.ecos.shopex.cn



       $replace[]='<script type="text/javascript" src="http://static.connect.renren.com/js/v1.0/FeatureLoader.jsp"></script>
<script type="text/javascript">XN_RequireFeatures(["EXNML"], function()
        {
          XN.Main.init("'.$this->api_key1.'", "/home/xd_receiver.html");

        });
        function sendFeed(){
          new Request.HTML({
           data:"template_id=1&title_data='.$title_data.'&body_data='.$content_data.'&certi_id='.$certi_id.'&authorize_item='.$get_authorize['authorize_item'].'&session_key='.$_session_key.'&sign='.$this->get_sign($params).'",
              onComplete:function(){
                  alert("该商品已同步到人人!");
                  return false;
               }
           }).post("index.php?action_renren-tocollect.html");
         }
      
</script>'.$find[2];

        
        if($get_authorize['authorize_item']==1){
           $replace[]='<a href="#" rel="nofollow" onclick="sendFeed(); return false;" class="btn-fav">';
        }else{
           $replace[]='<a href="#" rel="nofollow" onclick="return false;" class="btn-fav">';
        }

        $replace[]='<html xmlns="http://www.w3.org/1999/xhtml" xmlns:xn="http://www.renren.com/2009/xnml">';

        $content = str_replace($find,$replace,$content);

        return $content;
    }

    function change_name(&$content){
/*登陆用户名*/

         if($_COOKIE['LOGIN_UNAME']){
            $find='<div class="info"> <strong>您好：'.$_COOKIE['LOGIN_UNAME'];
            $replace='<div class="info"> <strong>您好：'.$_COOKIE['UNAME'];
            $content = str_replace($find,$replace,$content);
            return $content;
         }
         return $content;
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

    function get_desc($string, $length = 80, $etc = '...',
                                      $break_words = false, $middle = false)
    {
        if ($length == 0)
            return '';

        if (isset($string{$length+1})) {

            $length -= min($length, strlen($etc));

            if (!$break_words && !$middle) {
                $string = preg_replace('/\s+?(\S+)?$/', '', utftrim(substr($string, 0, $length+1)));
            }
            if(!$middle) {
                return utftrim(substr($string, 0, $length)) . $etc;
            } else {
                return utftrim(substr($string, 0, $length/2)) . $etc . utftrim(substr($string, -$length/2));
            }
        } else {
            return $string;
        }
    }

    function utftrim($str)
    {
        $found = false;
        for($i=0;$i<4&&$i<strlen($str);$i++)
        {
            $ord = ord(substr($str,strlen($str)-$i-1,1));
            if($ord> 192)
            {
                $found = true;
                break;
            }
        }
        if($found)
        {
            if($ord>240)
            {
                if($i==3) return $str;
                else return substr($str,0,strlen($str)-$i-1);
            }
            elseif($ord>224)
            {
                if($i==2) return $str;
                else return substr($str,0,strlen($str)-$i-1);
            }
            else
            {
                if($i==1) return $str;
                else return substr($str,0,strlen($str)-$i-1);
            }
        }
        else return $str;
    }


}
