<?php

// This file is the entry point for your Openwhisk function (also called action)

use App\Kernel;
use Sam\Openwhisk\Bridge\Symfony\RequestFactory;
use Sam\Openwhisk\Bridge\Symfony\ResponseFactory;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

function handle_request(Request $request) : Response
{
    if (!isset($_SERVER['APP_ENV'])) {
        (new Dotenv())->load(__DIR__.'/.env');
    }

    if ($_SERVER['APP_DEBUG'] ?? false) {
        umask(0000);

        Debug::enable();
    }

    $kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', $_SERVER['APP_DEBUG'] ?? false);
    $response = $kernel->handle($request);

    $kernel->terminate($request, $response);

    return $response;
}

function main(array $args) : array
{
    try {
        $request = RequestFactory::fromArgs($args);
        $response = handle_request($request);

        return ResponseFactory::fromHttpFoundationResponse($response);
    } catch (\Throwable $e) {
        return ResponseFactory::fromException($e);
    }
}
