webman的nacos微服务管理客户端
### 安装
```bash 
composer require xiaosongshu/nacosclient
```
#### 开启微服务管理
config/plugin/xiaosongshu/nacosclient/app.php 设置enable=true
#### 配置需要管理的服务
config/plugin/xiaosongshu/nacosclient/config.php<br>
配置nacos服务地址，用户名和密码。以及需要管理的服务。
```php 
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
            'metadata'=>['method'=>'/api/login/login','param'=>'name,pass','id'=>''],
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
                ],
                [
                    /** IP */
                    'ip'=>'192.168.4.110',
                    /** 端口 */
                    'port'=>'9504',
                    /** 权重 */
                    'weight'=>92,
                    /** 健康状态 */
                    'healthy'=>true,
                    /** 是否临时实例 */
                    'ephemeral'=>false,
                ]
            ]
        ]
    ]
];
```
#### 客户端调用微服务 client.php
```php 
<?php

use Xiaosongshu\Nacosclient\ServiceClient;

/** 获取客户端 */
$client  = ServiceClient::getClient();

/** 获取空间内提供的服务 */
$serverList = $client->getServiceList('public');

/** 获取服务详情 */
$serverDetail = $client->getServiceDetail('mother','public');

/** 获取健康的实例列表 */
$instanceList = $client->getInstanceList('mother','public',true);

/** 获取实例详情 */
$instanceDetail = $client->getInstanceDetail('mother',true,'192.168.4.110','9504');

# 可以根据实例的详情使用request方法发送json-rpc请求调用对应的服务,你也可以自己构建json-rpc请求
ServiceClient::request('192.168.4.110','9504','/demo',['name'=>'tom','pass'=>123456],rand(1000,9999));
```
运行客户端 php client.php
#### 模拟服务端 server.php
```php
<?php
$socket = \stream_socket_server("tcp://0.0.0.0:9000", $errno, $errstr);
if (!$socket) {
    echo "$errstr ($errno)<br />\n";
} else {
    while ($conn = stream_socket_accept($socket,1,$clientIp)) {
        $content ='';
        $flag=true;
        while ($flag){
            $string = fgets($conn ,1024);
            if (strlen($string)==0){

                fwrite($conn,  "pong\n");
                fclose($conn);
                $flag=false;
                var_dump("数据为空，关闭客户端连接",date('Y-m-d H:i:s'));
            }else{
                if (strlen($string)<1023){
                    var_dump("接受数据完成，关闭客户端");
                    $flag = false;
                }
                $content = $content . $string;
                fwrite($conn, ($content) . "\n");
                fclose($conn);
            }
        }
    }
    fclose($socket);
}


```
开启服务端 php server.php

#### 附：一键搭建docker服务
```bash 
docker run --name nacos -e MODE=standalone --env NACOS_AUTH_ENABLE=true -p 8848:8848 31181:31181 -d nacos/nacos-server:1.3.1
```
默认账号：nacos,默认密码：nacos <br>
访问地址：http://127.0.0.1:8848/nacos/

#### 联系作者
email:2723659854@qq.com
