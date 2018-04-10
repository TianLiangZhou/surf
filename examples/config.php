<?php
/**
 * Created by PhpStorm.
 * User: zhoutianliang
 * Date: 2018/3/1
 * Time: 15:17
 */

return [
    'host' => '0.0.0.0',
    'port' => 9527,
    'setting' => [
        //'dispatch_mode' => 3,
        'reactor_num' => 8,
        'worker_num' => 10,
        /**
        'max_request' => '',
        'max_conn' => '',
        'task_worker_num' => '',
        'task_ipc_mode' => '',
        'task_max_request' => '',
        'task_tmpdir' => '',
        'dispatch_mode' => '',
        'dispatch_func' => '',
        'message_queue_key' => '',
        'daemonize' => '',
        'backlog' => '',
        'log_file' => '',
        'log_level' => '',
        'heartbeat_check_interval' => '',
        'heartbeat_idle_time' => '',
        'open_eof_check' => '',
        'open_eof_split' => '',
        'package_eof' => '',
        'open_length_check' => '',
        'package_length_type' => '',
        'package_length_func' => '',
        'package_max_length' => '',
        'open_cpu_affinity' => '',
        'cpu_affinity_ignore' => '',
        'open_tcp_nodelay' => '',
        'tcp_defer_accept' => '',
        'ssl_cert_file' => '',
        'ssl_method' => '',
        'ssl_ciphers' => '',
        'user' => '',
        'group' => '',
        'chroot' => '',
        'pid_file' => '',
        'pipe_buffer_size' => '',
        'buffer_output_size' => '',
        'socket_buffer_size' => '',
        'enable_unsafe_event' => '',
        'discard_timeout_request' => '',
        'enable_reuse_port' => '',
        'ssl_ciphers' => '',
        'enable_delay_receive' => '',
        'open_http_protocol' => '',
        'open_http2_protocol' => '',
        'open_websocket_protocol' => '',
        'open_mqtt_protocol' => '',
        'reload_async' => '',
        'tcp_fastopen' => '',
        'request_slowlog_file' => '',
         */
    ],
    'database' => [
        'default' => [
            'driver' => 'mysql',
            'host'   => 'localhost',
            'database' => 'test',
            'username' => 'root',
            'password' => "123456",
            'options'  => [],
        ],
    ],
    'pool' => [
        'interval' => 10000,
        'database' => [
            'default' => [
                'start_number' => 10
            ],
        ],
    ],
];