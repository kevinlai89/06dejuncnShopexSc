<?php
function widget_cfg_qf_countdown(&$system){
	$oCount = $system->loadModel('plugins/countdown');
    return array(
			'cats' => $oCount->getCountdownCats(),
			'fontsize' => array('11','12','14','16','18','20','24','30','36','48','60'),
	);
}
?>