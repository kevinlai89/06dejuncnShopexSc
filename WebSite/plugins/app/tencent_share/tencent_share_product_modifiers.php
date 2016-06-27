<?php
class tencent_share_product_modifiers extends pageFactory{

    function product_index(&$content){

        $api_key = $this->system->getConf('app.tencent_share.apikey');
        $base_url=$this->system->base_url();
/*分享*/
        $product=$this->system->loadModel('plugins/tencent_share');
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

                 $img_storager.=$small.'|';
                 
                 
        }
        $img_storager=substr($img_storager,0,strlen($img_storager)-1);

        if($product_info['brief']){
           $pro_des='商品描述：'.$this->get_desc($product_info['brief'],130).str_repeat('&nbsp;',5);
        }else{
           $pro_des='';
        }
       $description = $pro_desc.' 品  牌：'.$product_info['brand_name']['brand_name'].'&nbsp;&nbsp;&nbsp; 价  格：'.$product_info['price'].'';
       
       $find=array('<p><span>分享到：</span>');

         $replace[]=$find[0].'&nbsp;<a href="javascript:void(0)" onclick="postToWb();return false;" style="height:16px;font-size:12px;line-height:16px;"><img src="http://v.t.qq.com/share/images/s/weiboicon16.png" align="absmiddle" border="0" alt="转播到腾讯微博" /></a><script type="text/javascript">
	function postToWb(){
		var _t = encodeURI(\''.$product_info['name'].'\');
		var _url = encodeURIComponent(document.location);
		var _appkey = encodeURI("'.$api_key.'");
		var _pic = encodeURI(\''.$img_storager.'\');
		var _site = \''.$base_url.'\';
		var _u = \'http://v.t.qq.com/share/share.php?url=\'+_url+\'&appkey=\'+_appkey+\'&site=\'+_site+\'&pic=\'+_pic+\'&title=\'+_t;
		window.open( _u,\'\', \'width=700, height=680, top=0, left=0, toolbar=no, menubar=no, scrollbars=no, location=yes, resizable=no, status=no\' );
	}
</script>';

        $content = str_replace($find,$replace,$content);

		return $content;
    }

    function get_desc($string, $length = 80, $etc = '...', $break_words = false, $middle = false)
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
