<?php
return array(
                'levels' => array(
                                '1'=>array('plan_name'=>'Free', 'ratlimit'=>5,'ttlmin'=>1,'urlto'=>false, 'emailto'=>false),
                                '2'=>array('plan_name'=>'Paid', 'ratlimit'=>100,'ttlmin'=>1,'urlto'=>false, 'emailto'=>true),
                                '3'=>array('plan_name'=>'Pro', 'ratlimit'=>500,'ttlmin'=>5,'urlto'=>true, 'emailto'=>true),
                                '4'=>array('plan_name'=>'Corp', 'ratlimit'=>1000,'ttlmin'=>5,'urlto'=>true, 'emailto'=>true)
                ),
                'defaults' => array(
                                'crontab'=>'0 0 * * *',
                                'allow'=>86399,
                                'min_allow'=>300
                                )
);

