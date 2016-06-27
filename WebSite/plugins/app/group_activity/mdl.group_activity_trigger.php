<?php
/**
 * @author chenping
 * @version mdl.group_activity_trigger.php 2010-4-8 11:35:54 $
 * @package group_activity
 * @uses mdl_trigger
 *
 */
if (!class_exists('mdl_trigger')) {
	require_once(CORE_DIR.'/'.((!defined('SHOP_DEVELOPER') || !constant('SHOP_DEVELOPER')) && version_compare(PHP_VERSION,'5.0','>=')?'model_v5':'model').'/system/mdl.trigger.php');
}
class mdl_group_activity_trigger extends mdl_trigger{

	function __construct(){
		parent::mdl_trigger();
	}
	/**
     * object_fire_event
     * 执行对象事件
     * $this->system->messenger调用app/group_activity下的模型 
     *
     * @param mixed $action
     * @param mixed $object
     * @param mixed $member_id
     * @param mixed $target
     * @access public
     * @return void
     */
	function object_fire_event($action , &$object, $member_id,&$target){
		//ob_start();'system.event_listener'
		if(false===strpos($action,':')){
			$trigger_event = $target->modelName.':'.$action;
			$modelName = $target->modelName;
		}else{
			$trigger_event = $action;
			list($modelName,$action) = explode(':',$action);
		}
		$type = $target->typeName;
		//$this->system->messenger = &$this->system->loadModel('system/messenger');
		$this->system->messenger  = $this->system->loadModel('plugins/group_activity/group_activity_messenger');
		
		$this->system->_msgList = $this->system->messenger->actions();
		if($this->system->_msgList[$type.'-'.$action]){
			$this->system->messenger->actionSend($type.'-'.$action,$object,$member_id);
		}
		if(defined('DISABLE_TRIGGER') && DISABLE_TRIGGER){
			return true;
		}else{

			$all_triggers = $this->db->select('select trigger_define from sdb_triggers where trigger_event="'.$trigger_event.'" and active="true" and disabled="false" order by trigger_order desc');

			if($all_triggers){
				$events = $target->events();
				if (!$events){
					$instance=$this->system->loadModel($modelName);
					$events = $instance->events();
				}else{
					$instance = $target;
				}

				$object['_event_date_'] = time();
				$object['ip'] = remote_addr();
				foreach($all_triggers as $trigger){
					$trigger = unserialize($trigger['trigger_define']);

					if($this->__test_role($trigger['filter_mode'],$trigger['filter'],$object,$events[$action]['params'],$instance)){
						$this->__call_actions($trigger['actions'],$object);
					}
				}

			}


			$appmgr = &$this->system->loadModel('system/appmgr');
			$data=array_merge((array)$this->listeners['*'],
			(array)$this->listeners[$target->modelName.':*'],
			(array)$this->listeners[$target->modelName.':'.$action]);
			foreach($data as $func){
				list($mod,$func) = $appmgr->get_func($func);
				if($func)$mod->$func($action,$object);
			}
			return true;
		}

	}

}
