<?PHP
define('HOST_ID',10243);
class ctl_etao extends adminPage{

    var $workground ='sale';
     function ctl_etao(){
         parent::adminPage();
     }

    function index(){
        $this->path[] = array('text'=>__('一淘开放搜索'));
        $this->pagedata['etaoname'] = $this->system->getConf('system.etaoname');
        $this->pagedata['etao'] = $this->system->getConf('system.etao');
        $this->pagedata['shop_url'] = $this->system->base_url();
        $this->page('system/etao/etao.html');
    }

    function upload_etao_file(){
            $this->begin('index.php?ctl=sale/etao&act=index');
            if($_FILES['etao_file']&&$_FILES['etao_file']['name']!='etao_domain_verify.txt'){
                $this->end(false,"上传文件名错误");
            }else{
                if($_POST['etao']){
                    $handle = fopen($_FILES['etao_file']['tmp_name'],'r');
                    $contents = fread($handle, filesize ($_FILES['etao_file']['tmp_name']));
                    fclose($handle);
                    $new_handle = fopen(BASE_DIR.'/etao_domain_verify.txt','w');
                    fwrite($new_handle,$contents);
                    fclose($new_handle);
                    $goods = $this->system->loadModel("goods/products");
                    $good_ids = $goods->get_etao_goods_id();
                    foreach($good_ids as $k=>$v){
                        if(strlen($v['name'])>=60){
                            unset($good_ids[$k]);
                        }
                    }
                    $this->create_etao_fullindex_xml($_POST['etaoname'],$good_ids);
                    $this->create_etao_IncrementIndex_xml($_POST['etaoname'],$good_ids);
                    $this->create_etao_SellerCats_xml($_POST['etaoname']);
                    foreach($good_ids as $k=>$v){
                        $this->create_etao_goods_xml($_POST['etaoname'],$v);
                    }
                }
                $this->system->setConf('system.etao',$_POST['etao']);
				$this->system->setConf('system.etao_modify',time());
                $this->system->setConf('system.etaoname',$_POST['etaoname']);
                $this->end(true, __('提交成功'));
            }
    }
        
    function create_etao_fullindex_xml($etaoname,$good_ids){
            $dom = new DomDocument('1.0','utf-8');
            $root = $dom->appendChild($dom->createElement('root'));
            $version = $root->appendChild($dom->createElement('version'));
            $version->appendChild($dom->createTextNode('1.0'));
            $modified = $root->appendChild($dom->createElement('modified'));
            $time = date("Y-n-j H:m:s");
            $modified->appendChild($dom->createTextNode($time));
            $seller_id = $root->appendChild($dom->createElement('seller_id'));
            $seller_id->appendChild($dom->createTextNode($etaoname));
            $cat_url = $root->appendChild($dom->createElement('cat_url'));
            $cat_url->appendChild($dom->createTextNode($this->system->base_url().'etao/SellerCats.xml'));
            $dir = $root->appendChild($dom->createElement('dir'));
            $dir->appendChild($dom->createTextNode($this->system->base_url().'etao/item/'));
            $item_ids = $root->appendChild($dom->createElement('item_ids'));
            foreach($good_ids as $k=>$v){
                $outer_id = $item_ids->appendChild($dom->createElement('outer_id'));
                $outer_id->setAttribute('action','upload');
                $outer_id->appendChild($dom->createTextNode($v['goods_id']));
            }
            $dom->formatOutput = true;
            $test = $dom->saveXML();
            if(!is_dir(MEDIA_DIR.'/etao')){
                mkdir(MEDIA_DIR.'/etao',0777);
            }
            $dom -> save(MEDIA_DIR.'/etao/FullIndex.xml');
   }
        
   function create_etao_IncrementIndex_xml($etaoname,$good_ids){
            $dom = new DomDocument('1.0','utf-8');
            $root = $dom->appendChild($dom->createElement('root'));
            $version = $root->appendChild($dom->createElement('version'));
            $version->appendChild($dom->createTextNode('1.0'));
            $modified = $root->appendChild($dom->createElement('modified'));
            $time = date("Y-n-j H:m:s");
            $modified->appendChild($dom->createTextNode($time));
            $seller_id = $root->appendChild($dom->createElement('seller_id'));
            $seller_id->appendChild($dom->createTextNode($etaoname));
            $cat_url = $root->appendChild($dom->createElement('cat_url'));
            $cat_url->appendChild($dom->createTextNode($this->system->base_url().'etao/SellerCats.xml'));
            $dir = $root->appendChild($dom->createElement('dir'));
            $dir->appendChild($dom->createTextNode($this->system->base_url().'etao/item/'));
            $item_ids = $root->appendChild($dom->createElement('item_ids'));
            foreach($good_ids as $k=>$v){
                $outer_id = $item_ids->appendChild($dom->createElement('outer_id'));
                $outer_id->setAttribute('action','upload');
                $outer_id->appendChild($dom->createTextNode($v['goods_id']));
            }
            $dom->formatOutput = true;
            $test = $dom->saveXML();
            if(!is_dir(MEDIA_DIR.'/etao')){
                mkdir(MEDIA_DIR.'/etao',0777);
            }
            $dom->save(MEDIA_DIR.'/etao/IncrementIndex.xml');
   }

    function create_etao_SellerCats_xml($etaoname){
            $dom = new DomDocument('1.0','utf-8');
            $root = $dom->appendChild($dom->createElement('root'));
            $version = $root->appendChild($dom->createElement('version'));
            $version->appendChild($dom->createTextNode('1.0'));
            $modified = $root->appendChild($dom->createElement('modified'));
            $time = date("Y-n-j H:m:s");
            $modified->appendChild($dom->createTextNode($time));
            $seller_id = $root->appendChild($dom->createElement('seller_id'));
            $seller_id->appendChild($dom->createTextNode($etaoname));
            $seller_cats = $root->appendChild($dom->createElement('seller_cats'));
            $goods_cats = $this->system->loadModel("goods/productCat");
            $cat_ids = $goods_cats->get_etao_goods_cat();
            foreach($cat_ids as $k=>$v){
                $cat = $seller_cats->appendChild($dom->createElement('cat'));
                $scid = $cat->appendChild($dom->createElement('scid'));
                $scid->appendChild($dom->createTextNode($v['cat_id']));
                $name = $cat->appendChild($dom->createElement('name'));
                $name->appendChild($dom->createTextNode($v['cat_name']));
                $catids = $goods_cats->get_etao_goods_cats($v['cat_id']);
                if($catids){
                   $p_cats = $cat->appendChild($dom->createElement('cats'));
                   foreach($catids as $kk=>$vv){
                      $cats = $p_cats->appendChild($dom->createElement('cat'));
                      $scids = $cats->appendChild($dom->createElement('scid'));
                      $scids->appendChild($dom->createTextNode($vv['cat_id']));
                      $names = $cats->appendChild($dom->createElement('name'));
                      $names->appendChild($dom->createTextNode($vv['cat_name']));
                   }
                   unset($vv['cat_id']);unset($vv['cat_name']);
               }
            }
            $dom->formatOutput = true;
            $test = $dom->saveXML();
            if(!is_dir(MEDIA_DIR.'/etao')){
                mkdir(MEDIA_DIR.'/etao',0777);
            }
            $dom -> save(MEDIA_DIR.'/etao/SellerCats.xml');
        }
        
        function create_etao_goods_xml($etaoname,$good_info){
            $dom = new DomDocument('1.0','utf-8');
            $item = $dom->appendChild($dom->createElement('item'));
            $seller_id = $item->appendChild($dom->createElement('seller_id'));
            $seller_id->appendChild($dom->createTextNode($etaoname));
            $outer_id = $item->appendChild($dom->createElement('outer_id'));
            $outer_id->appendChild($dom->createTextNode($good_info['goods_id']));
            $title = $item->appendChild($dom->createElement('title'));
            $title->appendChild($dom->createTextNode($good_info['name']));
            $type = $item->appendChild($dom->createElement('type'));
            $type->appendChild($dom->createTextNode('fixed'));

            $available = $item->appendChild($dom->createElement('available'));
               if($good_info['store']!=0||$good_info['store']==null){
                    $store = 1;
               }else{
                    $store = 0;
               }
            $available->appendChild($dom->createTextNode($store));

            $price = $item->appendChild($dom->createElement('price'));
            $goods_price = sprintf("%.2f",$good_info['price']);
            $price->appendChild($dom->createTextNode($goods_price));

            $desc = $item->appendChild($dom->createElement('desc'));
            $desc->appendChild($dom->createTextNode($good_info['brief']));


            $image = $item->appendChild($dom->createElement('image'));
            $big_pic = explode('|',$good_info['big_pic']);

            $big_pic[0] = $this->system->base_url().$big_pic[0];
            $image->appendChild($dom->createTextNode($big_pic[0]));
            $scids = $item->appendChild($dom->createElement('scids'));
            $scids->appendChild($dom->createTextNode($good_info['cat_id']));
            $post_fee = $item->appendChild($dom->createElement('post_fee'));
            $post_fee->appendChild($dom->createTextNode('0'));
            $showcase = $item->appendChild($dom->createElement('showcase'));
            $showcase->appendChild($dom->createTextNode('true'));
            $href = $item->appendChild($dom->createElement('href'));
            $href->appendChild($dom->createTextNode($this->system->base_url().'?product-'.$good_info['goods_id'].'.html'));
            $dom->formatOutput = true;
            $test = $dom->saveXML();

            if(!is_dir(BASE_DIR.'/etao/item/'.$good_info['goods_id'])){
                mkdir(BASE_DIR.'/etao/item/'.$good_info['goods_id'],0777);
            }
            $dom -> save(BASE_DIR.'/etao/item/'.$good_info['goods_id'].'/'.$good_info['goods_id'].'.xml');
    }
}
