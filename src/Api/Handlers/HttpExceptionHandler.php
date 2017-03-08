<?php
namespace CleaRest\Api\Handlers;

use CleaRest\Api\Network\HttpException;
use CleaRest\Api\Request;
use CleaRest\Api\Response;

/**
 * Handles only HttpExceptions
 */
class HttpExceptionHandler extends AbstractRequestHandler
{
    /**
     * @param \Exception $exception
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public function handleException(\Exception $exception, Request $request, Response $response)
    {
        if (! $exception instanceof HttpException) {
            return false;
        }

        $response->setStatusCode($exception->getHttpCode());
        $response->setBody([
            'error' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ],
            'data' => $exception->getData()
        ]);
        return true;
    }
}
