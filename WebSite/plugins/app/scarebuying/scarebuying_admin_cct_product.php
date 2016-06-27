<?php
require_once(CORE_DIR.'/admin/controller/goods/ctl.product.php');
if (!class_exists('mdl_scare')) {
	require_once('mdl.scare.php');
}
class scarebuying_admin_cct_product extends ctl_product {
	function _editor($type_id){
		  parent::_editor($type_id);
		$this->pagedata['sections']['scareBuying']=array(
		'label'=>__('限时抢购'),
		'options'=>'',
		'file'=>dirname(__FILE__).'/view/admin/product/scareBuying.html'
		);
      
       
	}

	function edit($goods_id){
        $appmgr = $this->system->loadModel('system/appmgr');
        $status=$appmgr->getPluginInfoByident('group_activity','disabled');
        if($status['disabled']=='false'){
            $group_activity=$this->system->loadModel('plugins/group_activity/group_activity');
            if($group_activity->get_group_byid($goods_id)){
                
                $this->pagedata['group_activity']=1;
            
            }

        }
        
		$this->getMlevel();

		$scareModel=new mdl_scare();
		$scareInfo=$scareModel->getFieldByGoodsId($goods_id);
		if ($scareInfo) {
			$scareInfo['s_date']=date('Y-m-d',$scareInfo['s_time']);
			$scareInfo['s_hour_selected']=date('G',$scareInfo['s_time']) ;
			$scareInfo['s_min_selected']=date('i',$scareInfo['s_time']) ;
			$scareInfo['s_hour']=$scareInfo['s_hour_selected'].':'.$scareInfo['s_min_selected'];

			$scareInfo['e_date']=date('Y-m-d',$scareInfo['e_time']);
			$scareInfo['e_hour_selected']=date('G',$scareInfo['e_time']) ;
			$scareInfo['e_min_selected']=date('i',$scareInfo['e_time']) ;

			$scareInfo['e_hour']=$scareInfo['e_hour_selected'].':'.$scareInfo['e_min_selected'];

			$scareInfo['scareMprice']=unserialize($scareInfo['scare_mprice']);

			if ($scareInfo['forenotice_on']==1) {
				$scareInfo['hour_selected']=intval($scareInfo['forenotice_time']/3600);
				$scareInfo['min_selected']=intval($scareInfo['forenotice_time']%3600/60);
				$scareInfo['sec_selected']=intval($scareInfo['forenotice_time']%3600%60);
			}
			if ($scareInfo['is_special_time']==1) {
				$scareInfo['specialTime']=unserialize($scareInfo['special_time_bucket']);
                $scareInfo['specialTime_week']=array('1'=>'每周一','2'=>'每周二','3'=>'每周三','4'=>'每周四','5'=>'每周五','6'=>'每周六','7'=>'每周日');
			}
		}else {
			$scareInfo['showPrice']=1;
		}


		//预告时间/小时
		for ($i=0;$i<24;$i++){
			$scareInfo['forenotice_hour'][$i]['k']=$i;
			$scareInfo['forenotice_hour'][$i]['v']=$i;
		}
		//预告时间/分、秒
		for ($i=0;$i<60;$i++){
			$scareInfo['forenotice_min'][$i]['k']=$i;
			$scareInfo['forenotice_min'][$i]['v']=$i;
		}
		//hour and min
		for ($i=0;$i<24;$i++){
			$scareInfo['select_time'][]=$i.':00';
			$scareInfo['select_time'][]=$i.':30';
		}
           $this->pagedata['group_point']=$this->system->getConf('point.get_policy');
		$this->pagedata['scareInfo']=$scareInfo;
    
		parent::edit($goods_id);
	}

	function addNew(){
		$this->getMlevel();
		//hour and min
		for ($i=0;$i<24;$i++){
			$scareInfo['select_time'][]=$i.':00';
			$scareInfo['select_time'][]=$i.':30';
		}

		//预告时间/小时
		for ($i=0;$i<24;$i++){
			$scareInfo['forenotice_hour'][$i]['k']=$i;
			$scareInfo['forenotice_hour'][$i]['v']=$i;
		}
		//预告时间/分、秒
		for ($i=0;$i<60;$i++){
			$scareInfo['forenotice_min'][$i]['k']=$i;
			$scareInfo['forenotice_min'][$i]['v']=$i;
		}
		$scareInfo['showPrice']='1';
		$scareInfo['is_mprice']='0';
		$this->pagedata['scareInfo']=$scareInfo;
		parent::addNew();
	}

	function toAdd(){
       
		$data = $_POST['goods'];
		$data['spec_desc'] = urldecode( $data['spec_desc'] );
		//        $data['spec_desc'] = addslashes_array($data['spec_desc']);
		$data['params'] = stripslashes_array($data['params']);

		if(!$data['goods_id']) unset($data['goods_id']);

		switch($_GET['but']){
			case 3:
				if($data['goods_id']){
					$but_type = 'edit';
					$url_href = 'index.php?ctl=goods/product&act=edit&p[0]='.$data['goods_id'];
				}else{
					$but_type = 'new';
					$url_href = 'index.php?ctl=goods/product&act=index';
				}
				break;
			case 1:
				//$url_href = 'index.php?ctl=goods/product&act=addNew&p[0]='.$data['cat_id'].'&p[1]='.$data['type_id'].'&p[2]='.$data['brand_id'];
				$url_href = 'index.php?ctl=goods/product&act=addNew&p[0]=&p[1]=';
				break;
			default:
				$url_href = 'index.php?ctl=goods/product&act=index';
				break;
		}
		$this->begin($url_href);
        
        if(count(explode('.',$_POST['scareInfo']['goodscore']))>=2){
            $this->end(false,__('积分请用整数'));
			exit;
        }
		if( is_array($_POST['bn']) ){
			foreach( $_POST['bn'] as $aTmpBnk => $aTmpBn ){
				$_POST['bn'][$aTmpBnk] = trim( $aTmpBn );
			}
		}
		$data['bn'] = trim( $data['bn'] );
		$data['product_bn'] = trim($data['product_bn'] );

		$image_file = $data['image_file'];
		unset($data['image_file']);
		$udfimg = $data['udfimg'];
		unset($data['udfimg']);
		$data['marketable'] = $data['marketable'] ? $data['marketable'] : false;
		$data['adjunct'] = $_POST['adjunct'];
		if(count($_POST['price'])>0){    //开启规格 多货品
			foreach($_POST['vars'] as $vark=>$varv){
				$data['spec'][$vark] = $varv;
			}
			$data['spec'] = serialize($data['spec']);
			$sameProFlag = array();
			foreach($_POST['price'] as $k => $price){    //设置销售多货品销售价等价格
				$data['price'] = $data['price']?min($price,$data['price']):$price;    //取最小商品价格
				$data['cost'] = $data['cost']?min($_POST['cost'][$k],$data['cost']):$_POST['cost'][$k];
				$data['weight'] = $data['weight']?min($_POST['weight'][$k],$data['weight']):$_POST['weight'][$k];

				if(!$_POST['mktprice'][$k]){ //没有市场价
					$oMath = &$this->system->loadModel('system/math');
					if($this->system->getConf('site.show_mark_price')){
						if($this->system->getConf('site.market_price') == '1')
						$_POST['mktprice'][$k] = $this->system->getConf('site.market_rate')*$oMath->getOperationNumber($price);
						if($this->system->getConf('site.market_price') == '2')
						$_POST['mktprice'][$k] = $this->system->getConf('site.market_rate')+$oMath->getOperationNumber($price);
					}
				}

				$products[$k]['price'] = $price;
				$products[$k]['bn'] = $_POST['bn'][$k];
				$products[$k]['store'] = (trim($_POST['store'][$k]) === '' ? null : intval($_POST['store'][$k]));
				$products[$k]['alert'] = $_POST['alert'][$k];
				$products[$k]['cost'] = $_POST['cost'][$k];
				$products[$k]['weight'] = $_POST['weight'][$k];
				$products[$k]['mktprice'] = $_POST['mktprice'][$k];
				$products[$k]['store_place'] = $_POST['store_place'][$k];
				$products[$k]['marketable'] = $_POST['marketable'][$k];

				//数量
				$store+=$products[$k]['store'];
				//end
				$data['mktprice'] = $data['mktprice']?min($_POST['mktprice'][$k],$data['mktprice']):$_POST['mktprice'][$k];

				$newSpecI = 0;
				$proSpecFlag = '';
				foreach($_POST['vars'] as $i=>$v){

					$products[$k]['props']['spec'][$i] = urldecode(trim($_POST['val'][$i][$k]));        //array('规格(颜色)序号'=>'规格值(红色)')
					$products[$k]['props']['spec_private_value_id'][$i] = trim($_POST['pSpecId'][$i][$k]);
					$products[$k]['props']['spec_value_id'][$i] = trim($_POST['specVId'][$i][$k]);
					if( trim($products[$k]['props']['spec'][$i]) === '' ){
						trigger_error(__('请为所有货品定义规格值'),E_USER_ERROR);
						$this->end(false,__('请为所有货品定义规格值'));
						exit;
					}
					$proSpecFlag .= $products[$k]['props']['spec_private_value_id'][$i].'_';
				}
				if( in_array( $proSpecFlag, $sameProFlag ) ){
					trigger_error(__('不能添加相同规格货品'),E_USER_ERROR);
					$this->end(false,__('不能添加相同规格货品'));
					exit;
				}
				$sameProFlag[$k] = $proSpecFlag;
				reset($proSpecFlag);

				reset($_POST['vars'],$_POST['pSpecId']);
				$products[$k]['pdt_desc'] = implode('、', $products[$k]['props']['spec']);    //物品描述
				$products[$k]['pdt_desc'] = addslashes_array($products[$k]['pdt_desc']);

				foreach($_POST['idata'] as $i=>$v){
					$products[$k]['props']['idata'][$i] = $v[$k];
				}

				//设置会员价格
				if(is_array($_POST['mprice']))
				foreach($_POST['mprice'] as $levelid => $rows){
					$products[$k]['mprice'][$levelid] = floatval($rows[$k]);
				}
			}
			unset( $sameProFlag );
			$data['products'] = &$products;
		}else{
			if(!$data['mktprice']){
				$oMath = &$this->system->loadModel('system/math');
				if($this->system->getConf('site.show_mark_price')){
					if($this->system->getConf('site.market_price') == '1')
					$data['mktprice'] = $this->system->getConf('site.market_rate')* $oMath->getOperationNumber( $data['price'] );
					if($this->system->getConf('site.market_price') == '2')
					$data['mktprice'] = $this->system->getConf('site.market_rate')+$oMath->getOperationNumber( $data['price'] );
				}
			}
			$data['props']['idata'] = $_POST['idata'];

			//数量
			$store=$data['store'];
			//end
		}

		$objGoods = &$this->system->loadModel('trading/goods');
		foreach($products as $k => $p){
			if(empty($p['bn'])) continue;
			if($objGoods->checkProductBn($p['bn'], $data['goods_id'])){
				trigger_error(__('您所填写的货号已被使用，请检查！'),E_USER_ERROR);
				$this->end(false,__('您所填写的货号已被使用，请检查！'));
				exit;
			}
			$aBn[] = $p['bn'];
		}

		if(!empty($data['product_bn'])){
			if($objGoods->checkProductBn($data['product_bn'], $data['goods_id'])){
				trigger_error(__('您所填写的货号已被使用，请检查！'),E_USER_ERROR);
				$this->end(false,__('您所填写的货号已被使用，请检查！'));
				exit;
			}
		}

		if(count($aBn) > count(array_unique($aBn))){
			trigger_error(__('您所填写的货号已被使用，请检查！'),E_USER_ERROR);
			$this->end(false,__('您所填写的货号已被使用，请检查！'));
			exit;
		}

		if(!$data['type_id']){
			$objCat = &$this->system->loadModel('goods/productCat');
			$aCat = $objCat->getFieldById($data['cat_id'], array('type_id'));
			$data['type_id'] = $aCat['type_id'];
		}

		//限时抢购
		if ($_POST['goods']['iflimit']==1) {
            if($store){
			if ($store<$_POST['scareInfo']['scare_count']) {
				trigger_error(__('您所填写的限购数量超出库存，请检查！'),E_USER_ERROR);
				$this->end(false,__('您所填写的限购数量超出库存，请检查！'));
				exit;
            }
			}
			if (!empty($_POST['scareInfo']['s_hour'])) {
				if (!preg_match('/^[0-1]?[0-9]|2[0-3]:[0-5][0-9]$/',$_POST['scareInfo']['s_hour'])) {
					trigger_error(__('请正确填写时间格式！'),E_USER_ERROR);
					$this->end(false,__('请正确填写时间格式！'));
					exit;
				}
				$_POST['scareInfo']['s_hour']=explode(':',$_POST['scareInfo']['s_hour']);
			}
			if (!empty($_POST['scareInfo']['e_hour'])) {
				if (!preg_match('/^[0-1]?[0-9]|2[0-3]:[0-5][0-9]$/',$_POST['scareInfo']['e_hour'])) {
					trigger_error(__('请正确填写时间格式！'),E_USER_ERROR);
					$this->end(false,__('请正确填写时间格式！'));
					exit;
				}
				$_POST['scareInfo']['e_hour']=explode(':',$_POST['scareInfo']['e_hour']);
			}

			$data['s_time']=strtotime($_POST['scareInfo']['s_date'])+intval($_POST['scareInfo']['s_hour'][0])*3600+intval($_POST['scareInfo']['s_hour'][1])*60;
			$data['e_time']=strtotime($_POST['scareInfo']['e_date'])+intval($_POST['scareInfo']['e_hour'][0])*3600+intval($_POST['scareInfo']['e_hour'][1])*60;

			if ($data['s_time']>=$data['e_time']) {
				trigger_error(__('您所填写的结束时间小于开始时间，请检查！'),E_USER_ERROR);
				$this->end(false,__('您所填写的结束时间小于开始时间，请检查！'));
				exit;
			}

			$special_time_bucket=array();
			if ($_POST['scareInfo']['is_special_time']==1) {
				$_POST['scareInfo']['specialTime']=array_unique($_POST['scareInfo']['specialTime']);
				foreach ($_POST['specialTime'] as $key=>$value) {

					if ($value=='1|-1|-1') {
						trigger_error(__('所填写的时间格式有误，请检查！'),E_USER_ERROR);
						$this->end(false,__('所填写的时间格式有误，请检查！'));
						exit;
					}
					$special_time=explode('|',$value);
					if (!preg_match('/^[0-1]?[0-9]|2[0-3]:[0-5][0-9]$/',$special_time[1])){
						trigger_error(__('所填写的时间格式有误，请检查！'),E_USER_ERROR);
						$this->end(false,__('所填写的时间格式有误，请检查！'));
						exit;
					}
					if (!preg_match('/^[0-1]?[0-9]|2[0-3]:[0-5][0-9]$/',$special_time[2])){
						trigger_error(__('所填写的时间格式有误，请检查！'),E_USER_ERROR);
						$this->end(false,__('所填写的时间格式有误，请检查！'));
						exit;
					}
					$special_stime=explode(':',$special_time[1]);
					$special_etime=explode(':',$special_time[2]);
					$special_stime=intval($special_stime[0])*3600+intval($special_stime[1])*60;
					$special_etime=intval($special_etime[0])*3600+intval($special_etime[1])*60;
					if ($special_stime>=$special_etime) {
						trigger_error(__('生效结束时间必须大于生效开始时间！'),E_USER_ERROR);
						$this->end(false,__('生效结束时间必须大于生效开始时间！'));
						exit;
					}
					$special_time_bucket[$key]['week']=$special_time[0];
					$special_time_bucket[$key]['sohour']=$special_time[1];
					$special_time_bucket[$key]['sthour']=$special_stime;
					$special_time_bucket[$key]['eohour']=$special_time[2];
					$special_time_bucket[$key]['ethour']=$special_etime;
					$special_time_bucket[$key]['original']=$value;
					//$special_time_bucket[]=$special_time[0].'|'.$special_stime.'|'.$special_etime;
				}

			}

		}else {
			$data['s_time']=0;
			$data['e_time']=0;
		}

		//end

		if(!($gid = $objGoods->save($data))){
			$this->end(false,__('保存失败，请重试！'));
			exit;
		}
		//限时抢购
		$scareModel=new mdl_scare();
		if ($_POST['goods']['iflimit']==1) {
			$scareData=$_POST['scareInfo'];
			$scareData['goods_id']=$gid;
			$scareData['s_time']=$data['s_time'];
			$scareData['e_time']=$data['e_time'];
			$scareData['iflimit']=$_POST['goods']['iflimit'];

			$scareData['special_time_bucket']=serialize($special_time_bucket);
			unset($pt);
			if ($scareData['forenotice_on']==1) {
				$scareData['forenotice_time']=intval($scareData['forenotice_hour'])*3600+intval($scareData['forenotice_min'])*60+intval($scareData['forenotice_sec']);
			}else {
				$scareData['forenotice_time']=0;
			}

			if ($scareData['is_mprice']==1) {
                $memberLevel = &$this->system->loadModel('member/level');
                $memberLevel_scare=$memberLevel->getList('member_lv_id,name,dis_count,name');
				foreach ($_POST['scareMprice'] as $mlevel=>$mprice) {
					$scareData['scare_mprice'][$mlevel]=$mprice;
                    if(!$scareData['scare_mprice'][$mlevel]){
                       
        foreach( $memberLevel_scare as $level){
            if($mlevel==$level['member_lv_id']){
                $level['dis_count'] = ($level['dis_count']>0 ? $level['dis_count'] : 1);
                $scareData['scare_mprice'][$mlevel]=$level['dis_count']*$_POST['scareInfo']['scare_price'];
            }
        }
                    }
				}
               
				$scareData['scare_mprice']=($scareData['scare_mprice']) ? serialize($scareData['scare_mprice']) : 'null';
			}
            $scareData['count'] = $scareData['scare_count'];
			$scareModel->save($scareData);
		}else{
			$scareModel->delByGoodsId($gid);
		}
		//end

		$scheduled = array();
		$now = time();
		foreach($_POST['scheduled'] as $time=>$action){
			if($time>$now){
				$scheduled[] = array('tasktime'=>$time,'action'=>$action);
			}
		}
		$objGoods->set_auto_task($gid,$scheduled);

		$keywords = array();
		foreach( $objGoods->getKeywords($gid) as $keywordvalue )
		$keywords[] = $keywordvalue['keyword'];
		$keyword = implode('|', $keywords);

		if($keyword != $_POST['keywords']['keyword']){
			$objGoods->deleteKeywords($gid);
			if( $_POST['keywords']['keyword'] )
			$objGoods->addKeywords($gid, explode('|',$_POST['keywords']['keyword']) );
		}

		//处理商品图片
		$gimage= &$this->system->loadModel('goods/gimage');
		$gimage->saveImage($data['goods_id'], $data['db_thumbnail_pic'], $_POST['image_default'], $image_file, $udfimg, $_FILES);


		//相关商品
		foreach($_POST['linkid'] as $k => $id){
			$aLink[] = array('goods_1' => $data['goods_id'], 'goods_2' => $id, 'manual' => $_POST['linktype'][$id], 'rate' => 100);
		}
		$objProduct = &$this->system->loadModel('goods/products');
		$objProduct->toInsertLink($data['goods_id'], $aLink);

		/*
		* tag独立处理
		//处理TAG
		$objTag = &$this->system->loadModel('system/tag');
		$objTag->removeObjTag($data['goods_id']);
		foreach(space_split($_POST['tags']) as $tagName){
		$tagName = trim($tagName);
		if($tagName){
		if(!($tagid = $objTag->getTagByName('goods', $tagName))){
		$tagid = $objTag->newTag($tagName, 'goods');
		}
		$objTag->addTag($tagid, $data['goods_id']);
		}
		}
		*/

		$oSupplier = $this->system->loadModel('distribution/supplier');

		if( $_POST['supplier_id'] ){
			$newBn = array();
			if( isset($_POST['bn']) && is_array($_POST['bn']) ){
				foreach( $_POST['bn'] as $nbnk => $nbnv ){
					$newBn[$_POST['old_bn'][$nbnk]] = $nbnv;
					unset($_POST['source_bn'][$_POST['old_bn'][$nbnk]]);
				}
			}else{
				$newBn[$_POST['old_bn']] = $_POST['goods']['product_bn'];
				unset($_POST['src_bn']);
			}
			$oSupplier->updateSupplierPdtBn($newBn,$_POST['source_bn'],$_POST['supplier_id']);
		}

		if( isset($_POST['commandType']) ){
			if( in_array($_POST['commandType'], array('4','5','6') ) ){
				$oSupplier->updateSyncStatus($_POST['command_id'],$_POST['supplier_id'],'done');
			}
		}

		$oseo = &$this->system->loadModel('system/seo');
		$aData=array(
		'keywords'=>$_POST['goods']['seo']['meta_keywords'],
		'descript'=>$_POST['goods']['seo']['meta_description'],
		'title'=>$_POST['goods']['seo']['seo_title']
		);
		$oseo->set_seo('goods',$gid,$aData);
		//###
		if($but_type == 'new'){
			$this->end(true,__('保存成功').'<input type=hidden id="g_id" value='.$gid.'>','index.php?ctl=goods/product&act=edit&p[0]='.$gid);
		}else{
			if($_GET['but'] == 1){
				$this->end(true,__('保存成功').'<input type=hidden id="g_id" value='.$gid.'>',$url_href.$gid);
			}else{
				$this->end(true,__('保存成功').'<input type=hidden id="g_id" value='.$gid.'>');
			}
		}
	}

	function update(){
		//if($_POST['goods']['iflimit']==1){
		$this->getMlevel();
		//限时抢购
		$scareInfo=$_POST['scareInfo'];
		$scareInfo['iflimit']=$_POST['goods']['iflimit'];
		$scareInfo['scareMprice']=$_POST['scareMprice'];
		//hour and min
		for ($i=0;$i<24;$i++){
			$scareInfo['select_time'][]=$i.':00';
			$scareInfo['select_time'][]=$i.':30';
		}
		
		foreach ($_POST['specialTime'] as $key=>$value){
			$special_time=explode('|',$value);
			$special_time_bucket[$key]['week']=$special_time[0];
			$special_time_bucket[$key]['sohour']=$special_time[1];
			$special_time_bucket[$key]['eohour']=$special_time[2];
			$special_time_bucket[$key]['original']=$value;
		}
		$scareInfo['specialTime']=$special_time_bucket;
		//预告时间/小时
		for ($i=0;$i<24;$i++){
			$forenotice_hour[$i]['k']=$i;
			$forenotice_hour[$i]['v']=$i;
		}
		$scareInfo['forenotice_hour']=$forenotice_hour;
		//预告时间/分、秒
		for ($i=0;$i<60;$i++){
			$forenotice_min[$i]['k']=$i;
			$forenotice_min[$i]['v']=$i;
		}
		$scareInfo['forenotice_min']=$forenotice_min;

		$scareInfo['hour_selected']=$_POST['scareInfo']['forenotice_hour'];
		$scareInfo['min_selected']=$_POST['scareInfo']['forenotice_min'];
		$scareInfo['sec_selected']=$_POST['scareInfo']['forenotice_sec'];
		//		$scareInfo['s_hour_selected']=$scareInfo['s_hour'] ;
		//		$scareInfo['s_min_selected']=$scareInfo['s_min'] ;
		//		$scareInfo['s_sec_selected']=$scareInfo['s_sec'] ;

		//		$scareInfo['e_hour_selected']=$scareInfo['e_hour'] ;
		//		$scareInfo['e_min_selected']=$scareInfo['e_min'] ;
		//		$scareInfo['e_sec_selected']=$scareInfo['e_sec'] ;

		$this->pagedata['scareInfo']=$scareInfo;
		//}
		//end
		parent::update();
	}

	function getMlevel(){
		$memberLevel = &$this->system->loadModel('member/level');
		$mlevelInfo=$memberLevel->getList('member_lv_id,name');
		$this->pagedata['mlevelInfo']=$mlevelInfo;
	}




}