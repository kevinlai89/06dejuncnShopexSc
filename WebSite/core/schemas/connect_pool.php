<?php
    $db['connect_pool']=array (
        'columns' =>
            array (
                'id' =>
                array (
                  'type' => 'number',
                  'required' => true,
                  'pkey' => true,
                  'extra' => 'auto_increment',
                  'editable' => false,
                ),
                'task_id' =>
                array (
                    'width' => 110,
                    'type' =>'varchar(50)',
                    'required' => true,
                    'default' =>0,
                    'hidden'=>true,
                ),
                'date_time' =>
                array (
                    'width' => 110,
                    'type' =>'time',
                    'required' => true,
                    'default' =>'0',
                    'hidden'=>true,
                ),
            ),
            'index' =>
              array (
                'ind_task_id' =>
                array (
                  'columns' =>
                  array (
                    0 => 'task_id',
                  ),
                ),
            ),
        );
?>