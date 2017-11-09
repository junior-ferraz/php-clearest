<?php
namespace CleaRest\Api\Handlers;

use CleaRest\Api\Request;
use CleaRest\Api\Response;
use CleaRest\Validation\ValidationException;

class ValidationExceptionHandler extends AbstractRequestHandler
{
    /**
     * If this method returns true, propagation stops
     *
     * @param \Exception $exception
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public function handleException(\Exception $exception, Request $request, Response $response)
    {
        if (! $exception instanceof ValidationException) {
            return false;
        }

        if (!$exception->isApiVisible()) {
            return false;
        }

        $violations = [];
        foreach ($exception->getViolations() as $v) {
            $violations[] = [
                'message' => $v->getMessage(),
                'field' => $v->getContext()->getFieldName(),
                'value' => $v->getValue(),
            ];
        }

        $response->setStatusCode(400);
        $response->setBody([
            'error' => [
                'message' => "Validation failed"
            ],
            'violations' => $violations
        ]);

        return true;
    }
}
