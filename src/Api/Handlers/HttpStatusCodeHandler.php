<?php

namespace CleaRest\Api\Handlers;


use CleaRest\Api\Request;
use CleaRest\Api\Response;

/**
 * Define Http Status Code for undefined responses
 */
class HttpStatusCodeHandler extends AbstractRequestHandler
{
    /**
     * After response is generated, if no code was given sets it to 200 or 204 depending on the body
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public function postResponse(Request $request, Response $response)
    {
        if ($response->getStatusCode()) {
            return false;
        }

        if ($response->getBody() === null) {
            $response->setStatusCode(204); // OK, no response
        } else {
            $response->setStatusCode(200); // OK
        }
        return false;
    }
}

