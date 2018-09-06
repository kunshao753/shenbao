<?php
    $redis_host = explode(':',$_SERVER['SINASRV_REDIS_HOST']);
    $redis_host_r = explode(':',$_SERVER['SINASRV_REDIS_HOST_R']);
    $config['default']['r']['socket_type'] = 'tcp'; //`tcp` or `unix`
    $config['default']['r']['socket'] = ''; // in case of `unix` socket type
    $config['default']['r']['host'] = $redis_host_r[0];
    $config['default']['r']['password'] = NULL;
    $config['default']['r']['port'] = $redis_host_r[1];
    $config['default']['r']['timeout'] = 10;
    $config['default']['r']['auth'] = '';
    
    $config['default']['w']['socket_type'] = 'tcp'; //`tcp` or `unix`
    $config['default']['w']['socket'] = ''; // in case of `unix` socket type
    $config['default']['w']['host'] = $redis_host[0];
    $config['default']['w']['password'] = NULL;
    $config['default']['w']['port'] = $redis_host[1];
    $config['default']['w']['timeout'] = 10;
    $config['default']['w']['auth'] = '';

