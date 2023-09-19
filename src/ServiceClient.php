<?php

namespace Xiaosongshu\Nacosclient;

use Xiaosongshu\Nacos\Client;

class ServiceClient
{
    /** 配置 */
    protected static $config = [];
    /** 客户端 */
    protected static $client = null;

    /** 获取客户端 */
    public static function getClient()
    {
        if (!self::$client){
            self::$config = config('plugin.xiaosongshu.nacosclient.config') ?? [];
            self::$client = new Client(self::$config['host'] ?? '', self::$config['username'] ?? '', self::$config['password'] ?? '');
        }
        return self::$client;
    }

    /**
     * 请求对端服务
     * @param $ip
     * @param $port
     * @param $method
     * @param $param
     * @return string
     */
    public static function request($ip,$port,$method,$param,$id){
        /** 拼接请求地址 */
        $host = 'tcp://'.$ip.':'.$port;
        /** 创建一个客户端，指定为tcp连接 */
        $client = stream_socket_client($host);
        /** 需要发送的数据 */
        $request = [
            'jsonrpc'=>'2.0',
            'method'  => $method,
            'params'    => $param,
            'id'=>$id
        ];
        /** 给对端发送数据 */
        fwrite($client,  json_encode($request)."\n"  );
        /** 使用循环的方式获取tcp返回的数据 */
        $str='';
        /** 设置每次获取的数据包长度 */
        $length=1024;
        /** 获取标识符 */
        $flag=true;
        while ($flag){
            /** 读取指定长度的内容 */
            $result = fgets($client, $length);
            /** 将获取到的值累计上去 */
            $str=$str.$result;
            /** 实际获取到的数据的长度总比设定值小1，如果实际长度小于了这个长度，则说明接收数据完成 */
            if (strlen($result)<($length-1)){
                $flag=false;
            }
        }
        fclose($client);
        return $str;
    }

}