<?php

namespace App\Web\Controllers;

use App\Common\Helpers\ResponseHelper;
use App\Web\Forms\FileForm;
use Mix\Http\Message\ServerRequest;
use Mix\Http\Message\Response;

/**
 * Class FileController
 * @package App\Web\Controllers
 * @author liu,jian <coder.keda@gmail.com>
 */
class FileController
{

    /**
     * Upload
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function upload(ServerRequest $request, Response $response)
    {
        // 使用表单验证器
        $form = new FileForm($request->getAttributes(), $request->getUploadedFiles());
        $form->setScenario('upload');
        if (!$form->validate()) {
            $content = ['code' => 1, 'message' => 'FAILED', 'data' => $form->getErrors()];
            return ResponseHelper::json($response, $content);
        }

        // 保存文件
        // 微服务中文件应该保存到云服务或者自行搭建的文件服务器
        if ($form->file) {
            $targetPath = app()->basePath . '/runtime/uploads/' . date('Ymd') . '/' . $form->file->getClientFilename();
            $form->file->moveTo($targetPath);
        }

        // 响应
        $content = ['code' => 0, 'message' => 'OK'];
        return ResponseHelper::json($response, $content);
    }

}
