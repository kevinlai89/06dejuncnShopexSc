<?php
/**
 * state状态说明：
 * 1：未开始（当前时间未到活动开始时间）
 * 2：进行中（当前时间在活动开始时间和结束时间区间内）
 * 3：已结束（成功）（当前时间在活动结束时间后or已预订商品数达到限购数量，并且预订商品数量达到或超过最小优惠价格阶梯要求）
 * 4：已结束，待处理（当前时间在活动结束时间后or已预订商品数达到限购数量,并且预订商品数量未达到最小优惠价格阶梯要求）
 * 5:已结束（失败）（团购订单作废）
 */
$db['purchase']=array(
	'columns' =>array (
	    'act_id'=>array(
	    	'type'=>'mediumint(8)',
	    	'extra'=>'auto_increment',
	    	'pkey'=>'true',
	    	'label'=>__('序号'),
	    	//'hidden'=>true,
	    ),
	    'gid'=>array(
	    	'type'=>'object:goods/products',
	    	'required'=>true,
	    	'label'=>__('活动商品名称'),
	    	'editable'=>false,
			'locked' => 1,
	    ),
		'start_time'=>array(
			'type'=>'time',
			'label'=>__('开始时间'),
			'editable' => false,
		),
		'end_time'=>array(
			'type'=>'time',
			'label'=>__('结束时间'),
			'editable' => false,
		),
		'deposit'=>array(
			'type'=>'money',
			'default'=>'0',
			'required' => true,
			'filtertype'=>'number',
			'label'=>__('保证金'),
		),
		'limitnum'=>array(
			'type'=>'mediumint(8)',
			'label'=>__('限购数量'),
			'default'=>'0',
			'editable'=>false,
			'filtertype'=>'number',
		),
		'score'=>array(
			'type'=>'mediumint(8)',
			'label'=>__('积分'),
			'default'=>'0',
			'editable' => true,
			'filtertype'=>'number',
		),
		'ext_info'=>array(
			'type'=>'longtext',
			'default'=>'null',
			'label'=>__('价格阶梯'),
			'editable'=>false,
			'hidden'=>true,
		),
		'postage'=>array(
			'type'=>'text',
			'default'=>'null',
			'label'=>__('邮费优惠'),
			'editable'=>false,
			'hidden'=>true,
		),
		'intro'=>array(
			'type'=>'longtext',
			'default'=>'null',
			'label'=>__('团购说明'),
			'editable' => false,
			'hidden'=>true,
			'filtertype'=>'normal',
		
		),
		'state'=>array(
			'type'=>array(
				1=>__('未开始'),
				2=>__('进行中'),
				3=>__('已结束（成功）'),
				4=>__('已结束，待处理'),
				5=>__('已结束（失败）')
			),
			'default'=>'1',
			'label'=>__('活动状态'),
			'editable'=>false,
		),
		'act_open'=>array(
			'type' => 'bool',
			'default' => 'false',
			'editable' => false,
			'required' => false,
		),
		'disabled' =>array (
			'type' => 'bool',
			'default' => 'false',
			'editable' => false,
			'required'=>false,
		)
	),
	
	'index' =>
        array (
        'act_index' =>
            array (
                'columns' =>
                array (
                    0 => 'act_id',
                ),
            ),
    ),
);