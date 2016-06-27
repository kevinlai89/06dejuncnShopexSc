<?php
$db['authorize'] = array (
    'columns' =>
    array (
        'member_id' =>
        array (
            'type' => 'varchar(30)',
			'pkey' => true,
            'required' => true,
        ),
        'authorize_item' =>
        array (
            'type' =>'longtext',
            'required' => true,
        )
    )
);