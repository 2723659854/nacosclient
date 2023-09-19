<?php

namespace Xiaosongshu\Nacosclient\Process;
use Workerman\Timer;
use Workerman\Worker;
use Xiaosongshu\Nacos\Client;

class ServiceClient
{
    /** 实现原理
     * onworkstart 获取服务配置，然后注册到nacos 然后 添加定时器，每隔10秒向nacos 发送心跳
     */

    /** 配置 */
    protected $config = [];
    /** 客户端 */
    protected static $client = null;
    /** 定时器 */
    protected static $timer = [];
    /** 发送心跳 */
    protected $heart_beat = 10;

    /** 初始化 */
    public function __construct(){
        /** 读取配置 */
        $this->config = config('plugin.xiaosongshu.nacosclient.config')??[];
        /** 设置心跳间隔 */
        if (!empty($this->config['heartbeat'])){
            $this->heart_beat = $this->config['heartbeat'];
        }
    }

    /** 服务开启 */
    public function onWorkerStart(Worker $worker){

        /** 注册服务 */
        $enable = config('plugin.xiaosongshu.nacosclient.app')['enable']??false;
        /** 如果开启了服务注册 */
        if ($enable){
            /** 连接服务器 */
            self::$client = new Client($this->config['host']??'',$this->config['username']??'',$this->config['password']??'');
            /** 读取nacos服务配置 */
            foreach (config('plugin.xiaosongshu.nacosclient.service')??[] as $name => $server){
                $serviceName = $server['serviceName']??null;
                $namespace = $server['namespace']??'public';
                $metedata = $server['metedata']??[];
                if (!$serviceName){
                    throw new \Exception("{$name}服务名为空");
                }
                /** 创建服务 */
                $createService = self::$client->createService($serviceName,$namespace,json_encode($metedata));
                if ($createService['status']==200){
                    /** 创建实例 */
                    foreach ($server['instance'] as $instance){

                        $ip = $instance['ip']??null;
                        $port = $instance['port']??null;
                        $weight = $instance['weight']??1;
                        $healthy = $instance['healthy']??true;
                        $ephemeral = $instance['ephemeral']??false;
                        if (!$ip||!$port){
                            throw new \Exception("缺少实例配置ip或port");
                        }
                        /** 创建实例 */
                        $createInstance = self::$client->createInstance($serviceName,$ip,$port,$namespace,json_encode($metedata),$weight,$healthy,$ephemeral);
                        if ($createInstance['status']==200){
                            /** 创建定时器，发送心跳 */
                            self::$timer[] = Timer::add($this->heart_beat,function ()use($serviceName,$ip,$port,$namespace,$ephemeral,$metedata){
                                $beat = self::$client->sendBeat( $serviceName,  $ip,  $port,  $namespace,  $ephemeral,  json_encode($metedata));
                                var_dump("发送心跳",$beat);
                            },true);
                        }else{
                            throw new \Exception("创建实例失败:".$createInstance['content']);
                        }
                    }
                }else{
                    throw new \Exception("创建服务{$name}失败：".$createService['content']);
                }

            }
        }
    }

    /** 删除定时器 */
    public function onWorkerStop(Worker $worker){
        foreach (self::$timer as $timer){
            if (is_int($timer)) Timer::del($timer);
        }
    }
}