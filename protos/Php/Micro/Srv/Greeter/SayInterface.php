<?php
# Generated by the protocol buffer compiler (https://github.com/mix-php/grpc). DO NOT EDIT!
# source: greeter.proto

namespace Php\Micro\Srv\Greeter;

use Mix\Grpc;
use Mix\Context\Context;

interface SayInterface extends Grpc\ServiceInterface
{
    // GRPC specific service name.
    public const NAME = "php.micro.srv.greeter.Say";

    /**
    * @param Context $ctx
    * @param Request $in
    * @return Response
    *
    * @throws Grpc\Exception\InvokeException
    */
    public function Hello(Context $ctx, Request $req): Response;
}