<?php

namespace Xiaosongshu\Nacosclient\Process;

use support\Log;
use Workerman\Timer;
use Workerman\Worker;
use Xiaosongshu\Nacos\Client;
use Xiaosongshu\Nacosclient\ServiceClient;

class ServiceRegister
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
    public function __construct()
    {
        /** 读取配置 */
        $this->config = config('plugin.xiaosongshu.nacosclient.config') ?? [];
        /** 设置心跳间隔 */
        if (!empty($this->config['heartbeat'])) {
            $this->heart_beat = $this->config['heartbeat'];
        }
    }

    /** 服务开启 */
    public function onWorkerStart(Worker $worker)
    {
        /** 注册服务 */
        $enable = config('plugin.xiaosongshu.nacosclient.app')['enable'] ?? false;
        /** 如果开启了服务注册 */
        if ($enable) {
            /** 连接服务器 */
            self::$client = new Client($this->config['host'] ?? '', $this->config['username'] ?? '', $this->config['password'] ?? '');
            /** 读取nacos服务配置 */
            foreach ($this->config['service'] ?? [] as $name => $server) {

                $serviceName = $server['serviceName'] ?? null;
                $namespace   = $server['namespace'] ?? 'public';
                $metadata    = $server['metadata'];
                if (!$serviceName) {
                    throw new \Exception("{$name}服务名为空");
                }
                $metadata =json_encode($metadata);
                try {
                    /** 创建服务 */
                  self::$client->createService($serviceName, $namespace, $metadata);
                } catch (\Exception $exception) { }
                /** 创建实例 */
                foreach ($server['instance'] as $instance) {

                    $ip        = $instance['ip'] ?? null;
                    $port      = $instance['port'] ?? null;
                    $weight    = $instance['weight'] ?? 1;
                    $healthy   = $instance['healthy'] ?? true;
                    $ephemeral = $instance['ephemeral'] ?? false;
                    if (!$ip || !$port) {
                        throw new \Exception("缺少实例配置ip或port");
                    }
                    /** 创建实例 */
                    $createInstance = self::$client->createInstance($serviceName, $ip, $port, $namespace, $metadata, $weight, $healthy, $ephemeral);
                    if ($createInstance['status']==200){
                        /** 创建定时器，发送心跳 */
                        self::$timer[] = Timer::add($this->heart_beat, function () use ($serviceName, $ip, $port, $namespace, $ephemeral, $metadata) {
                            /** 检查这个服务状态:发送的数据和正常请求区别开 */
                            $check = ServiceClient::request($ip,$port,'',[],'-10000');
                            /** 只要有返回值，就说明正常 ，这个可能不合理，暂时先这么弄吧 */
                            if ($check){
                                /** 发送心跳 */
                                self::$client->sendBeat($serviceName, $ip, $port, $namespace, $ephemeral, $metadata);
                            }

                        }, false);
                    }

                }
            }
        }
    }

    /** 删除定时器 */
    public function onWorkerStop(Worker $worker)
    {
        foreach (self::$timer as $timer) {
            if (is_int($timer)) Timer::del($timer);
        }
    }
}