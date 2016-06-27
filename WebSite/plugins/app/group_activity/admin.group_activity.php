<?php
/**
 * @author chenping
 * @time: 2010-3-2 15:51
 * //===========================
 * admin.group_activity.php
 * 团购活动后台
 * //===========================
 *
 */
require_once('objectPage.php');
class admin_group_activity extends objectPage {
	var $workground = 'sale';
	var $object     = 'plugins/group_activity';
	var $finder_action_tpl = '';
	var $finder_filter_tpl='';
	var $finder_default_cols='act_id,gid,state,_cmd,limitnum,goodsnum,ordernum,start_time,end_time';
	var $filterUnable = true;
	var $allowImport = false;
	var $allowExport = false;

	function __construct(){
		parent::objectPage();
	}

	/**
	 * 团购列表
	 *
	 */
	function group_activity_index(){
		$this->template_dir=CORE_DIR.'/admin/view/';
		$this->finder_action_tpl = dirname(__FILE__).'/view/admin/group_activity/finder_action.html';
		//如果预购商品数达到最小价格阶梯数 则先自动处理预团购订单
		$this->model->autochangestatues();
		parent::index();
	}
    function get_time(){
        echo time();
    }

	/**
	 * 添加团购活动
	 *
	 */
	function group_activity_add(){
		//邮费优惠
		$actInfo['is_postage'] = array(
		'1' => '达到一定条件免邮费',
		'2' => '无邮费优惠'
		);
		$actInfo['numtype'] = array(
		1 => array(
		'val' =>'2',
		'label' =>'商品总订购数达到'

		),
		0 => array(
		'val' =>'1',
		'label' =>'单笔订单订购数达到'

		)

		);
		$actInfo['act_open'] = array(
		'true' => '开启',
		'false' => '关闭'
		);
		$actInfo['time'] = array(
		'today' => time(),
		'tomorrow' => time()+86400
		);
     $this->pagedata['group_point']=$this->system->getConf('point.get_policy');
		$this->pagedata['actInfo'] = $actInfo;
       $goodsModel = $this->system->loadModel('trading/goods');
		$goodsInfo = $goodsModel->getFieldById($actInfo['gid']);
		$goodsInfo['url'] =  $this->system->realUrl('product','index',array($goodsInfo['goods_id']),null);
		$freez =  $this->model->getFreezStore($goodsInfo['goods_id']);
		$this->pagedata['goodsInfo']=$goodsInfo;
		$this->page(dirname(__FILE__).'/view/admin/group_activity/add.html');
	}

	/**
	 * 修改团购活动
	 *
	 * @param unknown_type $act_id
	 */
	function group_activity_edit($act_id){
		//邮费优惠
		$actModel=$this->system->loadModel('plugins/group_activity');
		$actInfo=$actModel->getFieldById($act_id);
		$actInfo['shour']		= date("G:i",$actInfo['start_time']);
		$actInfo['ehour']		= date("G:i",$actInfo['end_time']);
		$actInfo['ext_info']	= unserialize($actInfo['ext_info']);
		$actInfo['postage']		= unserialize($actInfo['postage']);

		$actInfoParam['is_postage'] = array(
		'1' => '达到一定条件免邮费',
		'2' => '无邮费优惠'
		);
		$actInfoParam['numtype'] = array(
		1 => array(
		'val' =>'2',
		'label' =>'商品总订购数达到'

		),
		0 => array(
		'val' =>'1',
		'label' =>'单笔订单订购数达到'

		)

		);
		$actInfoParam['act_open'] = array(
		'true' => '开启',
		'false' => '关闭'
		);
        $this->pagedata['group_point']=$this->system->getConf('point.get_policy');
		$this->pagedata['actInfo']=$actInfo;
		$this->pagedata['actInfoParam']=$actInfoParam;
        
		$goodsModel = $this->system->loadModel('trading/goods');
		$goodsInfo = $goodsModel->getFieldById($actInfo['gid']);
		$goodsInfo['url'] =  $this->system->realUrl('product','index',array($goodsInfo['goods_id']),null);
		$freez =  $this->model->getFreezStore($goodsInfo['goods_id']);
        //print_r($freez);exit;
		$this->pagedata['goodsInfo']=$goodsInfo;
		$this->page(dirname(__FILE__).'/view/admin/group_activity/edit.html');
	}
	/**
	 * 保存团购活动
	 *
	 */
	function group_activity_save(){
        
		$this->template_dir = CORE_DIR.'/admin/view/';
		switch ($_POST['acttype']){
			case 'add':
				$url='index.php?ctl=plugins/group_activity&act=group_activity_add';
				break;
			case 'edit':
				$url='index.php?ctl=plugins/group_activity&act=group_activity_edit&p[0]='.$_POST['act_id'];
				break;
			default:
				$url='index.php?ctl=plugins/group_activity&act=group_activity_index';
		}

		$this->begin($url);
        $appmgr = $this->system->loadModel('system/appmgr');
        $status=$appmgr->getPluginInfoByident('scarebuying','disabled');
        if($status['disabled']=='false'){
            $scarebuying_scare = $this->system->loadModel('plugins/scarebuying/scare');
            $scarebuying_scare_list=$scarebuying_scare->getListbyid($_POST['products']);
            if($scarebuying_scare_list=$scarebuying_scare->getListbyid($_POST['products'])){
               $this->end(false,__('此商品已经参加限时抢购！'));
            }
         
        }
       
        $group_activity_model=$this->system->loadModel('plugins/group_activity');
        if(!$_POST['act_id']){
        $in=$group_activity_model->getProduct($_POST,$message);
		if ($in) {	//已经在其他团购活动中
          
			$this->end(false,__('该商品已参加团购活动'));exit;
		}
        }
        $goodsModel = $this->system->loadModel('trading/goods');
		$goodsInfo = $goodsModel->getFieldById($_POST['products']);
		$goodsInfo['url'] =  $this->system->realUrl('product','index',array($goodsInfo['goods_id']),null);
		$freez =  $this->model->getFreezStore($goodsInfo['goods_id']);
		if(!$goodsInfo['store']){
            if($goodsInfo['store']=='0' && $_POST['limitnum']>'0'){
                $this->end(false,__('限购数量不能大于可购买数量!'));
            }
        }
		if (!isset($_POST['products'])) {
			$this->end(false,__('团购商品不能为空！'));
		}
		if ($_POST['limitnum']>0) {
			foreach ($_POST['nums'] as $nums) {
				if ($nums>$_POST['limitnum']) {
					$this->end(false,__('限购数量不能小于价格阶梯中的最大数量'));
					break;
				}
			}
		}
		$sa=array_count_values($_POST['nums']);
		foreach ($sa as $value) {
			if ($value>1) {
				$this->end(false,__('价格阶梯中的数量重复'));
				break;
			}
		}

		if (!isset($_POST['start_time']) || !isset($_POST['end_time'])) {
			$this->end(false,__('请完善活动的时间范围'));
		}
		$shour		= explode(':',$_POST['shour']);
		$start_time	= $_POST['start_time']+$shour[0]*3600+$shour[1]*60;
		$ehour		= explode(':',$_POST['ehour']);
		$end_time	= $_POST['end_time']+$ehour[0]*3600+$ehour[1]*60;
		if ($start_time>=$end_time) {
			$this->end(false,__('结束时间必须大于开始时间'));
		}

		


		//保存数据
		$data['gid']	= $_POST['products'];
		$data['start_time'] = $start_time;
		$data['end_time']	= $end_time;
		$data['deposit']	= $_POST['deposit'];
		$data['limitnum']	= $_POST['limitnum'];
		$data['score']		= $_POST['score'];
		foreach ($_POST['nums'] as $key=>$value) {
			$ext_info[]=array(
			'num'=>$value,
			'price'=>$_POST['price'][$key],
			);
		}
		$data['ext_info']	= serialize($ext_info);
		$postage	=	array(
		'is_postage' => $_POST['actInfo']['is_postage'],
		'postage_favorable'	=>	$_POST['actInfo']['postage_favorable'],
		'buycount'	=>	$_POST['actInfo']['buycount']
		);
		$data['postage']	=	serialize($postage);
		$data['intro']		= $_POST['intro'];
		$data['act_open']	= $_POST['actInfo']['act_open'];
		$data['act_id']		= $_POST['act_id'];
		if ($statue=$group_activity_model->adminsetState($data)) {
            if(!$_POST['limitnum'] || $goodsInfo['store']=='0'){
			$data['state']	=	'3';
            }else{
                $data['state']	=	$statue;
            }
		}
        
		$act_id=$group_activity_model->save($data);
		if (!$act_id) {
			$this->end(false,__('保存失败'));
		}else {
			$this->end_only();
             $this->page(dirname(__FILE__).'/view/admin/group_activity/success.html');
            
		}
	}

	/**
	 *输出页面 
	 */
	function page($view){
		$this->template_dir = CORE_DIR.'/admin/view/';
		parent::page($view);
	}
	function cell_editor(){
		$this->template_dir = CORE_DIR.'/admin/view/';
		parent::cell_editor();
	}
	function colsetting(){
		$this->template_dir = CORE_DIR.'/admin/view/';
		parent::colsetting();
	}

	/**
	 * 判断货品是否已经在其他团购活动中
	 *
	 */
	function productInActivity(){
		$in=$this->model->getProduct($_POST,$message);
		if ($in) {	//已经在其他团购活动中
			echo $message;
		}else {		//不在其他团购活动中
			echo 0;
		}
	}
	/**
	 * 活动详细信息
	 *
	 */
	function _detail(){
		return array(
		'detail_info'=>array('label'=>__('基本信息'),'tpl'=>dirname(__FILE__).'/view/admin/group_activity/detail_info.html'),
		);
	}
	function detail_info($act_id){
        
		$this->template_dir = CORE_DIR.'/admin/view/';
		$actInfo = $this->model->getFieldById($act_id);
		$actInfo['ext_info'] = unserialize($actInfo['ext_info']);
		$actInfo['postage']	=  unserialize($actInfo['postage']);
		$actInfo['state_desc'] =	$this->model->getstate($actInfo['state']);
		$actInfo['goodsnum'] = $this->model->getGoodsNum($act_id);
		$actInfo['ordernum'] = $this->model->getOrderNum($act_id);
		$actInfo['current_price'] = $this->model->getCurrentPrice($act_id);
		$this->pagedata['actInfo'] = $actInfo;
       
		$goodsModel = $this->system->loadModel('trading/goods');
		$goodsInfo  = $goodsModel->getFieldById($actInfo['gid']);
		$goodsInfo['url'] =  $this->system->realUrl('product','index',array($goodsInfo['goods_id']),null);
       
		$this->pagedata['goodsInfo'] = $goodsInfo;

	}

	function closeAct($act_id){
		$this->template_dir = CORE_DIR.'/admin/view/';
		$this->begin('index.php?ctl=plugins/group_activity&act=detail&p[0]='.$act_id.'&p[1]=detail_info');
		$this->model->doClose($act_id);
		$this->end(true,__('活动已关闭'));

	}

	function openAct($act_id){
		$this->template_dir = CORE_DIR.'/admin/view/';
		$this->begin('index.php?ctl=plugins/group_activity&act=detail&p[0]='.$act_id.'&p[1]=detail_info');
		$actInfo=$this->model->getFieldById($act_id);
		$actInfo['goodsnum']=$this->model->getGoodsNum($act_id);
		$state=$this->model->setState($actInfo);
     
		$state=2;
     
		$this->model->doOpen($act_id,$state);
		$this->end(true,__('活动已开启'));
	}

	function recycle(){
       
		if ($result=$this->model->validateGroupOrder($_POST) ) {
			echo "已有订单的团购活动不能删除";exit;
		}
		parent::recycle();
	}
	/**
	 * 得到商品信息
	 *
	 */
	function getGoodsInfo(){
		if (!$_POST['gid']) {
			echo 'invalid';
			exit;
		}
		$goodsModel = $this->system->loadModel('trading/goods');
		$goodsInfo = $goodsModel->getFieldById($_POST['gid']);
       
         if($goodsInfo){
		$goodsInfo['url'] =  $this->system->realUrl('product','index',array($goodsInfo['goods_id']),null);
		$freez =  $this->model->getFreezStore($goodsInfo['goods_id']);
		$goodsInfo['store'] = $goodsInfo['store']-$freez;
        
		echo json_encode($goodsInfo);
         }
      

        
	}
	/**
	 * 宣布成功
	 *
	 * @param unknown_type $act_id
	 */
	function declare_success($act_id){
       
		//将预订单转为正式订单,并重新计算金额
		$this->template_dir = CORE_DIR.'/admin/view/';
		$this->begin('index.php?ctl=plugins/group_activity&act=group_activity_index');
		$actState = $this->model->getFieldById($act_id,array('state','end_time','act_open'));
		if (!$actState['act_open']) {
			$this->end(false,__('活动未开启'));
		}
		if ($actState['state']!=4) {
			$this->end(false,__('不能对该活动再次编辑或活动未结束'));
		}
		//判断是否有订单

		$actInfo=array(
		'state'=>3,
		'act_id'=>$act_id
		);
		if ($actState['end_time']>time()) {
			$actInfo['end_time']=time();
		}
		//if ($this->model->save($actInfo)) {
		if ($this->model->getOrderNum($act_id)>=0) {
			//得到当前价格
			$param['current_price']=$this->model->getCurrentPrice($act_id);
			$param['act_id'] = $act_id;
			//得到该活动下的所有订单
			$param['order']=$this->model->getOrderByActId($act_id);
			$result=$this->model->doDeclareSuccess($param);
		}else {
			$result=false;
		}
        // print_r($actInfo);exit;

		if ($result) {
			$this->model->save($actInfo);
			$this->end($result);
		}else {
			$this->end($result,__('未付款的订单已作废'));
		}

	}
	/**
	 * 宣布失败
	 *
	 * @param unknown_type $act_id
	 */
	function declare_fail($act_id){
		$this->template_dir = CORE_DIR.'/admin/view/';
		$this->begin('index.php?ctl=plugins/group_activity&act=group_activity_index');
		$actState = $this->model->getFieldById($act_id,array('state','end_time','act_open'));
		if (!$actState['act_open']) {
			$this->end(false,__('活动未开启'));
		}
		if ($actState['state']!=4) {
			$this->end(false,__('不能对该活动再次编辑或活动未结束'));
		}
		$actInfo=array(
		'state'=>5,
		'act_id'=>$act_id
		);
		if ($actState['end_time']>time()) {
			$actInfo['end_time']=time();
		}
		//if ($this->model->save($actInfo)) {
		if ($this->model->getOrderNum($act_id)) {
			$param['order']=$this->model->getOrderByActId($act_id);
			$param['act_id'] = $act_id;
			if ($_POST['handRefund']=='false') {//自动退款到预付款
				$result=$this->model->doDeclareFail($param);
			}elseif ($_POST['handRefund']=='true'){//手动退款
				$this->model->DeclareFailToSendMail($param);
				$result=true;
			}

		}
		//}else {
		//$result=false;
		//}
		if ($result) {
			$this->model->save($actInfo);
			$this->end($result);
		}else {
			$this->end($result,__('操作失败'));
		}
	}


}