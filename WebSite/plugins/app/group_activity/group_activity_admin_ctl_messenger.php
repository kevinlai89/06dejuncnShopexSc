<?php
/**
 * @author chenping
 * @version  group_activity_admin_ctl_messenger.php 2010-4-8 10:00:00 $
 * @package group_activity
 * @uses ctl_messenger
 *
 */
if (!class_exists('ctl_messenger')) {
	require_once(CORE_DIR.'/admin/controller/member/ctl.messenger.php');
}
class group_activity_admin_ctl_messenger  extends ctl_messenger {

	function __construct(){
		parent::ctl_messenger();
	}
	/**
	 * $messenger调用app/group_activity下的模型
	 *
	 */
	function index(){
		$this->path[] = array('text'=>__('邮件短信配置'));

		//$messenger = &$this->system->loadModel('system/messenger');
		$messenger = $this->system->loadModel('plugins/group_activity/group_activity_messenger');

		$action = $messenger->actions();
		foreach($action as $act=>$info){
			$list = $messenger->getSenders($act);
			foreach($list as $msg){
				$this->pagedata['call'][$act][$msg] = true;
			}
		}

		$this->pagedata['actions'] = $action;
		$this->_show('messenger/index.html');
	}

	function page($view){
		$this->template_dir = CORE_DIR.'/admin/view/';
		parent::page($view);
	}
	/**
	 * $messenger调用app/group_activity下的模型
	 *
	 * @param unknown_type $action
	 * @param unknown_type $msg
	 */
	function edTmpl($action,$msg){

		//$messenger = &$this->system->loadModel('system/messenger');
		$messenger = $this->system->loadModel('plugins/group_activity/group_activity_messenger');

		$info = $messenger->getParams($msg);

		if($this->pagedata['hasTitle'] = $info['hasTitle']){
			$this->pagedata['title'] = $messenger->loadTitle($action,$msg);
		}

		$this->pagedata['body'] = $messenger->loadTmpl($action,$msg);
		$this->pagedata['type'] = $info['isHtml']?'html':'textarea';
		$this->pagedata['messenger'] = $msg;
		$this->pagedata['action'] = $action;

		$actions = $messenger->actions();
		$this->pagedata['varmap'] = $actions[$action]['varmap'];
		$this->pagedata['action_desc'] = $actions[$action]['label'];
		$this->pagedata['msg_desc'] = $info['name'];

		$this->page('messenger/edtmpl.html');
	}

	/**
	 * $messenger 调用 app/group_activity下的模型
	 *
	 */
	function save(){
		//$messenger = &$this->system->loadModel('system/messenger');
		$messenger = $this->system->loadModel('plugins/group_activity/group_activity_messenger');
		if ($messenger->saveActions($_POST['actdo'])) {
			$this->splash('success', 'index.php?ctl=member/messenger&act=index');
		}else{
			$this->splash('failed','index.php?ctl=member/messenger&act=index');
		}
	}


}
?>
