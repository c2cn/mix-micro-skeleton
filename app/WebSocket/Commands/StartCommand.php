<?php

namespace App\WebSocket\Commands;

use Mix\Concurrent\Timer;
use Mix\Micro\Etcd\Configurator;
use Mix\Micro\Etcd\Factory\ServiceFactory;
use Mix\Micro\Etcd\Registry;
use Mix\Helper\ProcessHelper;
use Mix\Log\Logger;
use Mix\Http\Server\Server;
use Mix\Micro\Route\Router;
use Mix\WebSocket\Upgrader;

/**
 * Class StartCommand
 * @package App\WebSocket\Commands
 * @author liu,jian <coder.keda@gmail.com>
 */
abstract class StartCommand
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
     * @var Router
     */
    public $route;

    /**
     * @var Registry
     */
    public $registry;

    /**
     * @var Logger
     */
    public $log;

    /**
     * @var Upgrader
     */
    public $upgrader;

    /**
     * StartCommand constructor.
     */
    public function __construct()
    {
        $this->log      = context()->get('log');
        $this->route    = context()->get('webRoute');
        $this->server   = context()->get(Server::class);
        $this->config   = context()->get(Configurator::class);
        $this->registry = context()->get(Registry::class);
        $this->upgrader = new Upgrader();

        $this->log->withName('WEBSOCKET');
        $handler = new \Monolog\Handler\RotatingFileHandler(sprintf('%s/runtime/logs/websocket.log', app()->basePath), 7);
        $this->log->pushHandler($handler);
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
            $this->upgrader->destroy();
            ProcessHelper::signal([SIGINT, SIGTERM, SIGQUIT], null);
        });
        // 监听配置
        $this->config->listen();
        // 初始化
        $this->init();
        // 启动服务器
        $this->start();
    }

    /**
     * Init
     */
    abstract public function init();

    /**
     * 启动服务器
     * @throws \Swoole\Exception
     * @throws \Exception
     */
    public function start()
    {
        $this->welcome();
        // 注册服务
        $timer = Timer::new();
        $timer->tick(100, function () use ($timer) {
            if (!$this->server->port) {
                return;
            }
            xdefer(function () use ($timer) {
                $timer->clear();
            });
            $serviceFactory = new ServiceFactory();
            $services       = $serviceFactory->createServiceFromWeb(
                $this->server,
                $this->route,
                'php.micro.web'
            );
            $this->log->info(sprintf('Server started [%s:%d]', $this->server->host, $this->server->port));
            foreach ($services as $service) {
                $this->log->info(sprintf('Register service [%s]', $service->getID()));
            }
            $this->registry->register(...$services);
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
        println('Server         Name:      mix-websocket');
        println('System         Name:      ' . strtolower(PHP_OS));
        println("PHP            Version:   {$phpVersion}");
        println("Swoole         Version:   {$swooleVersion}");
        println('Framework      Version:   ' . \Mix::$version);
    }

}
