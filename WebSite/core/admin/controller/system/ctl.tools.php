<?php
class ctl_tools extends adminPage{

    var $workground ='tools';

    function ctl_tools(){
        parent::adminPage();
        $this->path = array(array('text'=>__('工具箱')));
    }

    function welcome(){
		
        $this->page('system/tools/welcome.html');
    }
    function _modified($src,$key){
        if(isset($src[$key]) && ($src[$key]!=$this->system->getConf($key))){
            return true;
        }else{
            return false;
        }
    }
    function footEdit(){
        $this->path[] = array('text'=>__('网页底部信息'));
        $this->pagedata['footEdit'] = $this->system->getConf('system.foot_edit');
        $this->page('system/tools/footEdit.html');

    }
    function saveFoot(){
        if($this->system->setConf('system.foot_edit',$_POST['footEdit'])){
            $this->splash('success','index.php?ctl=system/tools&act=footEdit',__('保存成功'));
        }
    }
    function errorpage($code){
        $this->path[] = array('text'=>__('系统错误页内容'));
        $templete='errorpage.html';
        switch($code){
            case '404':
                $this->pagedata['pagename'] = __('无法找到页面');
                $this->pagedata['code'] = '404';
                $this->pagedata['errorpage'] = $this->system->getConf('errorpage.p404');
                break;
            case '500':
                $this->pagedata['pagename'] = __('系统发生错误');
                $this->pagedata['code'] = '500';
                $this->pagedata['errorpage'] = $this->system->getConf('errorpage.p500');
                break;
            case 'searchempty':
                $this->pagedata['pagename'] = __('搜索为空时显示内容');
                $this->pagedata['code'] = 'searchempty';
                $this->pagedata['errorpage'] = $this->system->getConf('errorpage.searchempty');
                $templete='searchempty.html';
                break;
        }
        $this->page('system/tools/'.$templete);
    }

    function saveErrorPage(){
        switch($_POST['code']){
            case '404':
                $this->system->setConf('errorpage.p404',$_POST['errorpage']);
                break;
            case '500':
                $this->system->setConf('errorpage.p500',$_POST['errorpage']);
                file_put_contents(HOME_DIR.'/upload/error500.html',$_POST['errorpage']);
                break;
            case 'searchempty':
                $this->system->setConf('errorpage.searchempty',$_POST['errorpage']);
                break;
        }
        $this->splash('success','index.php?ctl=system/tools&act=errorpage&p[0]='.$_POST['code'],__('当前页面保存成功'));
    }

	function account_setting(){
		$this->workground ='';
		if($this->system->getConf('p_certificate.paipai_account')){
		    $is_bind = true;
		}else{
		    $is_bind = false;
		}
        $this->pagedata['paipai_account'] = $this->system->getConf('p_certificate.paipai_account');
        $this->pagedata['paipai_ptoken'] = $this->system->getConf('p_certificate.paipai_ptoken');
		$this->pagedata['is_bind'] = $is_bind;
        $this->page('system/tools/paipai_setting.html');
	}

	function save_account_setting(){
		$this->workground ='';
		$matrix = &$this->system->loadModel('system/matrix');
        $result = $matrix->paipai_apply_bind($_POST['paipai_account'],$_POST['paipai_account_confirm']);

		if($result){
		   $appmgr = &$this->system->loadModel('system/appmgr');
		   $appmgr->register_crontab_queue('system/matrix:run_system_queue');
           $this->splash('success','index.php?ctl=system/tools&act=account_setting',__('配置成功'));
		}else{
           $this->splash('failed','index.php?ctl=system/tools&act=account_setting',__('配置失败'));
		}
	}

}
?>
