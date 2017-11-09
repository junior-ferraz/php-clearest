<?php
namespace CleaRest\Api;

use CleaRest\Api\Data\UploadedFile;
use CleaRest\Api\Data\ValueConverter;
use CleaRest\Api\Exceptions\RequestException;
use CleaRest\FrameworkException;
use CleaRest\Metadata\Annotations\Body;
use CleaRest\Metadata\Annotations\Header;
use CleaRest\Metadata\Descriptors\Method as MethodMetadata;
use CleaRest\Metadata\MetadataStorage as MetadataLoader;
use CleaRest\Services\ServicesFactory;

class ServiceController
{
    /**
     * @var Router
     */
    private $parentRouter;
    /**
     * @var string
     */
    private $service;

    /**
     * @var string
     */
    private $method;

    /**
     * @var int|string
     */
    private $version;

    /**
     * @var MethodMetadata
     */
    private $metadata;

    /**
     * @param Router $parentRouter
     * @param string $service
     * @param string $method
     * @param int|string $version
     * @throws FrameworkException
     */
    public function __construct(Router $parentRouter, $service, $method, $version = null)
    {
        $this->parentRouter = $parentRouter;
        $this->service = $service;
        $this->method = $method;
        $this->version = $version;

        $class = MetadataLoader::getClassMetadata($this->service);
        if (!isset($class->methods[$this->method])) {
            throw new FrameworkException("No metadata found for method $service:$method");
        }

        $this->metadata = $class->methods[$this->method];
    }

    /**
     * Creates service instance, prepares request fields for arguments, calls method and converts result
     * @param Request $request
     * @param Response $response
     */
    public function call(Request $request, Response $response)
    {
        foreach ($this->parentRouter->getHandlers() as $handler) {
            if ($handler->preProcess($request, $response)) break;
        }

        $instance = ServicesFactory::getServiceInstance($this->service, $request, $this->version);
        $result = call_user_func_array(
            [$instance, $this->method],
            $this->getArguments($request)
        );

        foreach ($this->parentRouter->getHandlers() as $handler) {
            if ($handler->postProcess($result, $request, $response)) break;
        }

        $result = ValueConverter::convertResponseValue($result);

        foreach ($this->parentRouter->getHandlers() as $handler) {
            if ($handler->preResponse($result, $request, $response)) break;
        }

        $response->setBody($result);

        foreach ($this->parentRouter->getHandlers() as $handler) {
            if ($handler->postResponse($request, $response)) break;
        }
    }

    /**
     * Returns service arguments based on $request
     * @param Request $request
     * @return mixed[]
     * @throws RequestException
     */
    private function getArguments(Request $request)
    {
        $this->setAdditionalFields($request);

        $args = [];
        foreach ($this->metadata->parameters as $parameter) {
            // is it a file ?
            if ($parameter->type->name === UploadedFile::class) {
                $file = UploadedFile::getFromField($parameter->name);
                if ($file === null) {
                    if (!$parameter->hasDefault) {
                        throw new RequestException(
                            "Mandatory field {$parameter->name} missing",
                            RequestException::MISSING_FIELD,
                            ['field' => $parameter->name]
                        );
                    }
                    $args[] = $parameter->default;
                    continue;
                }
                if (is_array($file) && !$parameter->type->isArray) {
                    throw new RequestException(
                        "A single file was expected for field {$parameter->name}",
                        RequestException::INVALID_FIELD,
                        ['field' => $parameter->name]
                    );
                }
                if (!is_array($file) && $parameter->type->isArray) {
                    throw new RequestException(
                        "An array of files was expected for field {$parameter->name}",
                        RequestException::INVALID_FIELD,
                        ['field' => $parameter->name]
                    );
                }
                $args[] = $file;
                continue;
            }
            // is it in request ?
            if ($request->hasField($parameter->name)) {
                $value = $request->getField($parameter->name);
                $convertedValue = ValueConverter::convertRequestValue($value, $parameter->type, $parameter->name);
                if ($convertedValue === null) {
                    throw new RequestException(
                        "Cannot convert field {$parameter->name}. Invalid type given.",
                        RequestException::INVALID_FIELD,
                        ['field' => $parameter->name]
                    );
                }
                $args[] = $convertedValue;
            } else {
                // does it have a default value?
                if (!$parameter->hasDefault) {
                    throw new RequestException(
                        "Mandatory field {$parameter->name} missing",
                        RequestException::MISSING_FIELD,
                        ['field' => $parameter->name]
                    );
                }
                $args[] = $parameter->default;
            }
        }

        return $args;
    }

    /**
     * Some fields can come from the body or headers. This function sets them correctly.
     * @param Request $request
     * @throws RequestException
     */
    private function setAdditionalFields(Request $request)
    {
        $bodyFound = false;
        foreach ($this->metadata->annotations as $annotation) {
            if ($annotation instanceof Body) {
                $bodyFound = true;
                $body = $request->getBody();
                if ($annotation->parameter == '*') {
                    if (!is_array($body)) {
                        throw new RequestException("Invalid body content", RequestException::INVALID_BODY);
                    }
                    foreach ($body as $field => $value) {
                        $request->setField($field, $value);
                    }
                } else {
                    $isMandatory = !$this->metadata->parameters[$annotation->parameter]->hasDefault;
                    if ($body === null && $isMandatory) {
                        throw new RequestException("Invalid body content", RequestException::INVALID_BODY);
                    }
                    $request->setField($annotation->parameter, $body);
                }
            } elseif ($annotation instanceof Header) {
                $header = $request->getHeader($annotation->name);
                $isMandatory = !$this->metadata->parameters[$annotation->parameter]->hasDefault;
                if ($header !== null) {
                    $request->setField($annotation->parameter, $header);
                } elseif ($isMandatory) {
                    throw new RequestException(
                        "Mandatory header {$annotation->name} missing",
                        RequestException::MISSING_HEADER,
                        ['header' => $annotation->name]
                    );
                }
            }
        }
        if (!$bodyFound) {
            $body = $request->getBody();
            if (!empty($body) && is_array($body)) {
                foreach ($body as $field => $value) {
                    $request->setField($field, $value);
                }
            }
        }
    }

}