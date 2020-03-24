<?php

namespace App\Web\Controllers;

use App\Common\Helpers\ResponseHelper;
use Mix\Http\Message\Response;
use Mix\Http\Message\ServerRequest;

/**
 * Class ProfileController
 * @package App\Web\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class ProfileController
{

    /**
     * FileController constructor.
     * @param ServerRequest $request
     * @param Response $response
     */
    public function __construct(ServerRequest $request, Response $response)
    {
    }

    /**
     * Index
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function index(ServerRequest $request, Response $response)
    {
        // 调用rpc获取用户信息
        // ...

        // 渲染数据
        $data = [
            'id'      => $request->getAttribute('id'),
            'name'    => '小明',
            'age'     => 18,
            'friends' => ['小红', '小花', '小飞'],
        ];
        return ResponseHelper::view($response, 'profile.index', $data);
    }

}
