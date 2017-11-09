<?php
namespace CleaRest\Api\Handlers;

use CleaRest\Api\Request;
use CleaRest\Api\Response;
use CleaRest\Environments\LiveEnvironment;

/**
 * Handles any exception
 */
class FallbackExceptionHandler extends AbstractRequestHandler
{

    /**
     * It always returns true because the FallbackExceptionHandler is the last one to be called,
     * so, no propagation should be continued
     * @param \Exception $exception
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public function handleException(\Exception $exception, Request $request, Response $response)
    {
        $body = [
            'error' => [
                'message' => 'Internal Server Error',
            ]
        ];

        if (!LiveEnvironment::isCurrent()) {
            $body['exception'] = $this->getExceptionArray($exception);
        }

        $response->setStatusCode(500);
        $response->setBody($body);

        return true;
    }

    private function getExceptionArray(\Exception $ex)
    {
        $array = [
            'type' => get_class($ex),
            'message' => $ex->getMessage(),
            'code' => $ex->getCode(),
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
            'trace' => $ex->getTrace(),
        ];

        if ($ex->getPrevious()) {
            $array['previous'] = $this->getExceptionArray($ex->getPrevious());
        }

        return $array;
    }
}
