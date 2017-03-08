<?php

namespace CleaRest\Api\Handlers;

use CleaRest\Api\ContentTypeRegistry;
use CleaRest\Api\Exceptions\RequestException;
use CleaRest\Api\Request;
use CleaRest\Api\Response;

/**
 * Handles Content-Type header converting request and response bodies into expected type
 */
class ContentTypeHandler extends AbstractRequestHandler
{
    /**
     * Before request is processed, convert body from given format into array representation
     * @param Request $request
     * @param Response $response
     * @return bool
     * @throws RequestException
     */
    public function preProcess(Request $request, Response $response)
    {
        $contentType = $request->getHeader('Content-Type');
        $encoder = ContentTypeRegistry::getEncoder($contentType);
        if ($encoder === null) {
            throw new RequestException(
                "Content-Type '$contentType' not allowed",
                RequestException::INVALID_CONTENT_TYPE,
                ['content_type' => $contentType]
            );
        }
        $data = $encoder->decode($request->getBody());
        $request->setBody($data);

        return true;
    }

    /**
     * After response is generated, convert body into expected format
     * @param Request $request
     * @param Response $response
     * @return bool
     * @throws RequestException
     */
    public function postResponse(Request $request, Response $response)
    {
        $contentType = $request->getHeader('Content-Type');
        $encoder = ContentTypeRegistry::getEncoder($contentType);
        if ($encoder === null) {
            throw new RequestException(
                "Content-Type '$contentType' not allowed",
                RequestException::INVALID_CONTENT_TYPE,
                ['content_type' => $contentType]
            );
        }

        $response->setHeader('Content-Type', $encoder->getContentType());
        $data = $encoder->encode($response->getBody());
        $response->setBody($data);

        return true;
    }
}
