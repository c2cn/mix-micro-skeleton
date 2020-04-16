<?php

namespace App\Api\Commands;

use App\Api\Route\Router;
use Mix\Concurrent\Timer;
use Mix\Etcd\Configurator;
use Mix\Etcd\Factory\ServiceBundleFactory;
use Mix\Etcd\Registry;
use Mix\Helper\ProcessHelper;
use Mix\Http\Server\Server;
use Mix\Log\Logger;

/**
 * Class StartCommand
 * @package App\Api\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
class StartCommand
{

    /**
     * @var Server
     */
    public $server;

    /**
     * @var Configurator
     */
    public $config;

    /**
     * @var Registry
     */
    public $registry;

    /**
     * @var Logger
     */
    public $log;

    /**
     * @var Router
     */
    public $route;

    /**
     * StartCommand constructor.
     */
    public function __construct()
    {
        $this->log      = context()->get('log');
        $this->route    = context()->get('apiRoute');
        $this->server   = context()->get(Server::class);
        $this->config   = context()->get(Configurator::class);
        $this->registry = context()->get(Registry::class);

        $this->log->withName('API');
        $this->log->pushHandler(context()->get('apiRotatingFileHandler'));
    }

    /**
     * 主函数
     * @throws \Swoole\Exception
     */
    public function main()
    {
        // 捕获信号
        ProcessHelper::signal([SIGINT, SIGTERM, SIGQUIT], function ($signal) {
            $this->log->info('Received signal [{signal}]', ['signal' => $signal]);
            $this->log->info('Server shutdown');
            $this->registry->close();
            $this->config->close();
            $this->server->shutdown();
            ProcessHelper::signal([SIGINT, SIGTERM, SIGQUIT], null);
        });
        // 监听配置
        $this->config->listen();
        // 启动服务器
        $this->start();
    }

    /**
     * 启动服务器
     * @throws \Swoole\Exception
     * @throws \Exception
     */
    public function start()
    {
        $this->welcome();
        // 服务注册
        $timer = Timer::new();
        $timer->tick(100, function () use ($timer) {
            if (!$this->server->port) {
                return;
            }
            $this->log->info(sprintf('Server started [%s:%d]', $this->server->host, $this->server->port));
            $serviceBundleFactory = new ServiceBundleFactory();
            $serviceBundle        = $serviceBundleFactory->createServiceBundleFromAPI(
                $this->server,
                $this->route,
                'php.micro.api'
            );
            $this->registry->register($serviceBundle);
            $timer->clear();
        });
        // 启动
        $this->server->start($this->route);
    }

    /**
     * 欢迎信息
     */
    protected function welcome()
    {
        $phpVersion    = PHP_VERSION;
        $swooleVersion = swoole_version();
        echo <<<EOL
                              ____
 ______ ___ _____ ___   _____  / /_ _____
  / __ `__ \/ /\ \/ /__ / __ \/ __ \/ __ \
 / / / / / / / /\ \/ _ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\  / .___/_/ /_/ .___/
                     /_/         /_/


EOL;
        println('Server         Name:      mix-api');
        println('System         Name:      ' . strtolower(PHP_OS));
        println("PHP            Version:   {$phpVersion}");
        println("Swoole         Version:   {$swooleVersion}");
        println('Framework      Version:   ' . \Mix::$version);
    }

}
