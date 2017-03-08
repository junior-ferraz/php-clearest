<?php

namespace CleaRest\Api;

/**
 * Implement this interface and add class to Router so it's called when events happen
 */
interface RequestHandler
{
    /**
     * RequestHandlers must have a constructor without parameters
     */
    public function __construct();

    /**
     * This function is called before the request is processed and service called.
     * Return true to stop propagation to other handlers.
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public function preProcess(Request $request, Response $response);

    /**
     * This function is called after request being processed and service called.
     * The service result is given in $rawResult and can be modified before its converted into array structure.
     * Return true to stop propagation to other handlers.
     * @param mixed $rawResult
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function postProcess(&$rawResult, Request $request, Response $response);

    /**
     * This function is called after returned value is converted into array structure.
     * Return true to stop propagation to other handlers.
     * @param mixed $convertedResult
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function preResponse(&$convertedResult, Request $request, Response $response);

    /**
     * This function is called after response body is set.
     * Return true to stop propagation to other handlers.
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function postResponse(Request $request, Response $response);

    /**
     * This function is called when exception happens.
     * Return true to stop propagation to other handlers.
     * @param \Exception $exception
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public function handleException(\Exception $exception, Request $request, Response $response);
}

