<?php
$db['scare_buying']=array(
	'columns' =>array (
	    'id'=>
	    array(
	    	'type'=>'mediumint(8)',
	    	'extra'=>'auto_increment',
	    	'pkey'=>'true',
	    ),
		'goods_id' =>
		array (
			'type' => 'mediumint(8)',
			'default'=>'0',
		),
		's_time' =>
		array (
			'type' =>'int(10)',
			'default'=>'0',
		),
		'e_time'=>
		array(
			'type'=>'int(10)',
			'default'=>'0',
		),
		'scare_count'=>
		array(
			'type'=>'int(10)',
			'default'=>'0',
		),
        'count'=>
		array(
			'type'=>'int(10)',
			'default'=>'0',
		),
		'scare_price'=>
		array(
			'type'=>'decimal(10,3)',
			'default'=>'0',
		),
		'scare_mprice'=>
		array(
			'type'=>'longtext',
			'default'=>'null',
		),
		'showPrice'=>
		array(
			'type'=>'tinyint(1)',
			'default'=>'1',
		),
		'is_mprice'=>
		array(
			'type'=>'tinyint(1)',
			'default'=>'0',
		
		),
		'iflimit'=>
		array(
			'type'=>'tinyint(1)',
			'default'=>'0',
		),
		
		'disabled' =>
		array (
			'type' => 'bool',
			'default' => 'false',
		),
		'is_special_time'=>array(
			'type'=>'tinyint(1)',
			'default'=>'0',
		),
		'special_time_bucket'=>array(
			'type'=>'longtext',
			'default'=>'null',
		),
		'forenotice_on'=>array(
			'type'=>'tinyint(1)',
			'default'=>'0'
		),
		'forenotice_time'=>array(
			'type'=>'varchar(20)',
			'default'=>'null',
		),
		'buycountlimit'=>array(
			'type'=>'varchar(10)',
			'default'=>'null',
		),
		'goodscore'=>array(
			'type'=>'varchar(10)',
			'default'=>'null',
		),
	),
	
	'index' =>
        array (
        'ind_goods_id' =>
            array (
                'columns' =>
                array (
                    0 => 'goods_id',
                ),
            ),
    ),
);