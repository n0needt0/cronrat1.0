<?php

return array(
'driver' => 'smtp',
'host' => 'smtp.gmail.com',
'port' => 587,
'from' => array('address' => 'ops@cronrat.com', 'name' => 'Ops Cronrat'),
'encryption' => 'tls',
'username' => 'ops@cronrat.com',
'password' => 'mus1kpus1k',
'sendmail' => '/usr/sbin/sendmail -bs',
'pretend' => false,
);