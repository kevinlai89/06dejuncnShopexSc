<?php
    $db['login']=array (
        'columns' =>
            array (
                'csm_type' =>
                array (
                  'type' => 'varchar(20)',
                  'pkey' => true,
                  'required' => true,
                ),
                'csm_serial' =>
                array (
                  'type' => 'int(8)',
                  'required' => true,
                ),
                'csm_time' =>
                array (
                  'type' => 'int(50)',
                  'required' => true,
                ),
            ),
        );
?>