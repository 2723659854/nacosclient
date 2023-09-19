<?php

return [
    /** nacos 服务地址 */
    'host'=>'http://192.168.4.110:8848',
    /** 账户 */
    'username'=>'nacos',
    /** 密码 */
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
            'metadata'=>['method'=>'/demo','param'=>'name,pass','id'=>''],
            /** 实例列表 */
            'instance'=>[
                [
                    /** IP */
                    'ip'=>'192.168.4.110',
                    /** 端口 */
                    'port'=>'9000',
                    /** 权重 */
                    'weight'=>99,
                    /** 健康状态 */
                    'healthy'=>false,
                    /** 是否临时实例 */
                    'ephemeral'=>false,
                ],
                [
                    /** IP */
                    'ip'=>'192.168.4.110',
                    /** 端口 */
                    'port'=>'9504',
                    /** 权重 */
                    'weight'=>92,
                    /** 健康状态 */
                    'healthy'=>false,
                    /** 是否临时实例 */
                    'ephemeral'=>false,
                ]
            ]
        ]
    ]
];