<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>数据库执行操作</title>
<style>
<!--
body {padding:0;margin:0;font-size:12px;color:#000;background-color:#CCCCCC;}
.AllWrap {width:950px;padding:15px;margin:30px auto;border:1px solid #E0E0E0;background-color:#FFF;min-height:500px;}
-->
</style>
</head>
<body>
<div class="AllWrap">
<?php 
define('RUN_IN','FRONT_END');
ob_start();
if(file_exists('../config/config.php')){
    require('../config/config.php');
    ob_end_clean();

    require(CORE_DIR.'/include_v5/shopCore.php');
    class installCore extends shopCore{
        function run(){}
    };        
    $core = new installCore();
$sql[] = <<<EOT
ALTER IGNORE TABLE `sdb_goods` ADD COLUMN `istuan` enum('true','false') default 'false' AFTER marketable;
EOT;
$sql[] = <<<EOT
ALTER IGNORE TABLE `sdb_orders` ADD COLUMN `istuan` enum('true','false') default 'false' AFTER is_tax;
EOT;
$sql[] = <<<EOT
CREATE TABLE `sdb_tuangou_cat` (
	`cat_id` int(10) not null auto_increment,
	`parent_id` int(10),
	`supplier_id` int(10) unsigned,
	`supplier_cat_id` mediumint(8) unsigned,
	`cat_path` varchar(100) default ',',
	`is_leaf` enum('true','false') not null default 'false',
	`type_id` int(10),
	`cat_name` varchar(100) not null default '',
	`disabled` enum('true','false') not null default 'false',
	`p_order` mediumint(8) unsigned,
	`goods_count` mediumint(8) unsigned,
	`tabs` longtext,
	`finder` longtext,
	`addon` longtext,
	`child_count` mediumint(8) unsigned not null default 0,
	primary key (cat_id),
	INDEX ind_cat_path(`cat_path`),
	INDEX ind_disabled(`disabled`)
)ENGINE = MyISAM DEFAULT CHARACTER SET utf8;
EOT;
$sql[] = <<<EOT
CREATE TABLE `sdb_tuangou_orders` (
	`id` mediumint(8) unsigned not null auto_increment,
	`user_id` mediumint(8) unsigned not null default 0,
	`team_id` mediumint(8) unsigned not null default 0,
	`state` enum('pay','unpay') default 'pay',
	`quantity` mediumint(8) unsigned not null default 0,
	`create_time` int(10) unsigned,
	`disabled` enum('true','false') not null default 'false',
	primary key (id),
	INDEX ind_disabled(`disabled`)
)ENGINE = MyISAM DEFAULT CHARACTER SET utf8;
EOT;
$sql[] = <<<EOT
CREATE TABLE `sdb_tuangou_team` (
	`id` mediumint(8) unsigned not null auto_increment,
	`goods_id` mediumint(8) unsigned not null default 0,
	`cat_id` int(10),
	`name` varchar(255),
	`brief` varchar(255),
	`thumbnail_pic` varchar(255),
	`small_pic` varchar(255),
	`big_pic` varchar(255),
	`price` decimal(20,3),
	`mktprice` decimal(20,3),
	`intro` longtext,
	`per_number` mediumint(8) unsigned not null default 0,
	`min_number` mediumint(8) unsigned not null default 0,
	`max_number` mediumint(8) unsigned not null default 0,
	`now_number` mediumint(8) unsigned not null default 0,
	`now_users` mediumint(8) unsigned not null default 0,
	`pre_number` mediumint(8) unsigned not null default 0,
	`state` enum('none','success','soldout','failure','refund') not null default 'none',
	`conduser` enum('Y','N') not null default 'Y',
	`buyonce` enum('Y','N') not null default 'Y',
	`team_type` varchar(20) not null default 'normal',
	`begin_time` int(10) unsigned,
	`expire_time` int(10) unsigned,
	`close_time` int(10) unsigned,
	`p_order` mediumint(8) unsigned not null default 0,
	`disabled` enum('true','false') not null default 'false',
	primary key (id),
	INDEX ind_disabled(`disabled`)
)ENGINE = MyISAM DEFAULT CHARACTER SET utf8;
EOT;
    $db = $core->database();
    foreach ($sql as $s){
        $ret = $db->exec($s);        
        echo "<BR>".$s."<BR>".($ret ? '<p><font color="#000000">数据库写入成功！</font></p>' : '<p><font color="#FF0000">数据库写入失败，可能你已经安装了本插件或者数据库中存在相同字段！</font></p>');
    }
  
}
?>
</div>
</body>
</html>
