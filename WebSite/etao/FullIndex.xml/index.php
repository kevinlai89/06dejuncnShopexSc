<?php
ob_start();
if(file_exists('../../config/config.php')){
    require('../../config/config.php');
    ob_end_clean();
$handle=file_get_contents(MEDIA_DIR.'/etao/FullIndex.xml');
header('Content-type: application/xhtml+xml');
echo $handle;
}else header('Location: install/');
?>
