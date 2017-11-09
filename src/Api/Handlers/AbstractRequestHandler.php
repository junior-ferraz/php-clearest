<?php

namespace CleaRest\Api\Handlers;

use CleaRest\Api\Request;
use CleaRest\Api\RequestHandler;
use CleaRest\Api\Response;

/**
 * This abstract class simply implements the methods of RequestHandler to allow easy extension for handlers
 */
abstract class AbstractRequestHandler implements RequestHandler
{
    public function __construct()
    {

    }

    public function preProcess(Request $request, Response $response)
    {
        return false;
    }

    public function postProcess(&$rawResult, Request $request, Response $response)
    {
        return false;
    }

    public function preResponse(&$convertedResult, Request $request, Response $response)
    {
        return false;
    }

    public function postResponse(Request $request, Response $response)
    {
        return false;
    }

    public function handleException(\Exception $exception, Request $request, Response $response)
    {
        return false;
    }
}
