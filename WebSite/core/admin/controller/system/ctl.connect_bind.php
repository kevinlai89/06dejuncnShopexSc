<?php
require_once('objectPage.php');
class ctl_connect_bind extends objectPage{

    var $workground ='setting';
    var $object = 'system/connect_bind';
    var $canRemove = false;
    var $deleteAble = false;
    var $allowImport = false;
    var $allowExport = false;
    var $noRecycle = true;
    var $finder_action_tpl = 'system/connect_bind/finder_action.html';
    var $finder_default_cols = 'shop_name,to_node_id,to_node_type,bind_status';
    var $filterUnable = true;

    function ctl_connect_bind(){
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
        return array('show_detail'=>array('label'=>__('详细信息'),'tpl'=>'system/connect_bind/request_detail.html'));
    }
    
   function show_detail($bind_id){
        $detail_info = &$this->system->loadModel('system/connect_bind');
        $res = $detail_info->getQueById($bind_id);
        $this->pagedata['res'] = $res;
   }


   function bind_index(){
      //内网 http://sws.ex-sandbox.com/
      //外网 http://www.matrix.ecos.shopex.cn/
      $this->bind();
      $this->singlepage('system/connect_bind/bind.html');
      
   }

    function bind_check(){
      $this->bind('accept');
      $this->singlepage('system/connect_bind/bind.html');
   }

   function bind($method='apply', $app_id='shopex_b2c'){
        $bind_url = 'http://www.matrix.ecos.shopex.cn/';
        //内网 http://sws.ex-sandbox.com/
        //外网 http://www.matrix.ecos.shopex.cn/
        $api_url=$this->system->base_url().'api.php';
        $callback=$this->system->base_url().'shopadmin/index.php?ctl=receive_matrix&act=get_apply_node_bind_result';
        $certi = $this->system->getConf('certificate.id');
        $token = $this->system->getConf('certificate.token');
        $node_id = $this->system->getConf('certificate.node_id');
        $sess_id = $this->system->sess_id;
        $apply['certi_id'] = $certi;
        $apply['node_idnode_id'] = $node_id;
        $apply['sess_id'] = $sess_id;
        $str   = '';
        ksort($apply);
        foreach($apply as $key => $value){
            $str.=$value;
         
        }
      
        $apply['certi_ac'] = md5($str.$token);
        if ($method == 'apply')
        {
            if ($apply['node_idnode_id']){

                $this->pagedata['_PAGE_CONTENT'] = '<iframe align="middle" width="100%" height="100%" src='.$bind_url.'?source=apply&certi_id='.$apply['certi_id'].'&node_id='.$apply['node_idnode_id'].'&certi_ac='.$apply['certi_ac'].'&callback='.$callback.'&api_url='.$api_url.'&sess_id='.$apply['sess_id'].' ></iframe>';
            }else{
                $this->pagedata['_PAGE_CONTENT'] = '<iframe align="middle" width="100%" height="100%" src="' . $bind_url . '?source=apply&certi_id='.$apply['certi_id'].'&sess_id='.$apply['sess_id'].'&certi_ac='.$apply['certi_ac'].'&callback=' . $callback . '&api_url=' . $api_url .'&node_type" ></iframe>';
            }

        }elseif ($method == 'accept'){
            if ($apply['node_idnode_id']){
                $this->pagedata['_PAGE_CONTENT'] = '<iframe align="middle" width="100%" height="100%" src="' . $bind_url . '?source=accept&certi_id='.$apply['certi_id'].'&node_id=' . $apply['node_idnode_id'] . '&sess_id='.$apply['sess_id'].'&certi_ac='.$apply['certi_ac'].'&callback=' . $callback . '&api_url='.$api_url.'" ></iframe>';

            }else{
                $this->pagedata['_PAGE_CONTENT'] = '<iframe align="middle" width="100%" height="100%" src="' . $bind_url . '?source=accept&certi_id='.$apply['certi_id'].'&sess_id='.$apply['sess_id'].'&certi_ac='.$apply['certi_ac'].'&callback=' . $callback . '&api_url='.$api_url.'" ></iframe>';
         }
        }else{
            $this->pagedata['_PAGE_CONTENT'] = "";
        }
        
    }




}
?>
