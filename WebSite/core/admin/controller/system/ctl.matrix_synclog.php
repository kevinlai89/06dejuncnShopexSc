<?php
require_once('objectPage.php');
class ctl_matrix_synclog extends objectPage{

    var $workground ='setting';
    var $object = 'system/matrix_synclog';
    var $canRemove = false;
    var $deleteAble = false;
    var $allowImport = false;
    var $allowExport = false;
    var $noRecycle = true;
    var $finder_default_cols = '_cmd,queue_id,message,msg_id,send_num,mess_queue_id,status';
    var $filterUnable = true;

    function ctl_matrix_synclog(){
        parent::objectPage();
        $this->path = array(array('text'=>__('绑定关系/日志管理')));
    }


    function index(){
      parent::index();
    }

    function colsetting(){
         return parent::colsetting();
   }

    function _detail(){
        return array('show_detail'=>array('label'=>__('详细信息'),'tpl'=>'system/matrix_synclog/request_detail.html'));
    }
    
   function show_detail($order_id){
        $detail_info = &$this->system->loadModel('system/matrix_synclog');
        $res = $detail_info->getQueById($order_id);
        $res['shop_name'] = $this->system->getConf('p_certificate.paipai_account');
        $res['data_info'] = $res['data'];
        $this->pagedata['res'] = $res;
   }

   function run_queue($order_id){

      if($order_id){
        $this->begin('index.php?ctl=system/matrix_synclog&act=index');
		$db = $this->system->database();
        $queue = $db->select('select data from sdb_synclog where mess_queue_id ="'.$order_id.'"');

        $matrix = $this->system->loadModel('system/matrix');
         foreach ($queue as $k=>$v){
           $param = unserialize($v['data']);
           $return = $matrix->to_matrix($param,'paipai');

           $data = json_decode($return,true);

           if($data['rsp']=='running'){
             $queue['send_num'] = $v['send_num']+1;
             $sql = "update sdb_synclog set status='0',send_num = '".$queue['send_num']."' where mess_queue_id='".$order_id."'";

             $db->exec($sql);
           }
         }
         $this->end(true,'已发送');
      }
   }

    function _views(){
        return array(
            __('全部')=>"",
            __('订单')=>array('event_name'=>'0'),
            __('商品')=>array('event_name'=>'1'),
            __('商品分类')=>array('event_name'=>'2'),
            __('品牌')=>array('event_name'=>'3'),
            __('规格')=>array('event_name'=>'4'),
            __('规格值')=>array('event_name'=>'5'),

        );
    }




}
?>
