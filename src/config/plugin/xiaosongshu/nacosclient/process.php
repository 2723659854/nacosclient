<?php

return [

    /** 服务注册 */
    'service-provider-for-nacos' => [
        /** 处理器 */
        'handler' => \Xiaosongshu\Nacosclient\Process\ServiceRegister::class,
        /** 进程数 */
        'count' => 1
    ]
];