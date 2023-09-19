<?php

return [
    'host'=>'http://127.0.0.1:8848',
    'username'=>'nacos',
    'password'=>'nacos',

    /** 心跳检测间隔 */
    'heartbeat' => 5,

    /** 需要注册的服务 */
    'service'=>[
        /** 服务名称 */
        'test'=>[
            /** 服务名 */
            'serviceName'=>'mother',
            /** 命名空间 */
            'namespace'=>'public',
            /** 元数据 */
            'metadata'=>[],
            /** 实例列表 */
            'instance'=>[
                [
                    /** IP */
                    'ip'=>'192.168.4.110',
                    /** 端口 */
                    'port'=>'8000',
                    /** 权重 */
                    'weight'=>99,
                    /** 健康状态 */
                    'healthy'=>true,
                    /** 是否临时实例 */
                    'ephemeral'=>false,
                ]
            ]
        ]
    ]
];