<?php

namespace App\Web\Commands;

/**
 * Class IndexCommand
 * @package App\Web\Commands
 */
class IndexCommand extends StartCommand
{

    /**
     * Init
     */
    public function init()
    {
        // 路由配置
        $this->router
            ->rule('/index', [
                [\App\Web\Controllers\IndexController::class, 'index'],
                'middleware' => [\App\Web\Middleware\ActionMiddleware::class],
            ])
            ->parse();
    }

}