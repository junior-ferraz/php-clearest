<?php
namespace CleaRest\Api\Documentation;

use CleaRest\Api\Data\PlainObject;
use CleaRest\Api\Data\ScalarConverter;
use CleaRest\Api\Data\UploadedFile;
use CleaRest\Api\Exceptions\RequestException;
use CleaRest\Api\Network\HttpException;
use CleaRest\Api\Network\HttpRequest;
use CleaRest\Api\Router;
use CleaRest\Capabilities\CapabilitiesFactory;
use CleaRest\Capabilities\CapabilityException;
use CleaRest\Metadata\Annotations\Assert;
use CleaRest\Metadata\Annotations\Body;
use CleaRest\Metadata\Annotations\Header;
use CleaRest\Metadata\Annotations\Throws;
use CleaRest\Metadata\Descriptors\Method;
use CleaRest\Metadata\Descriptors\Parameter;
use CleaRest\Metadata\Descriptors\Type;
use CleaRest\Metadata\Descriptors\Value;
use CleaRest\Metadata\MetadataStorage;
use CleaRest\Metadata\Parsers\TokenParser;

/**
 * This class help joining the metadata for generating an API documentation
 */
class Generator
{
    private $title = 'API Documentation';

    private $scripts = [
        __DIR__ . '/Pages/assets/js/jquery-3.1.1.min.js',
        __DIR__ . '/Pages/assets/js/jquery.balloon.min.js',
        __DIR__ . '/Pages/assets/js/documentation.js'
    ];

    private $cssFiles = [
        __DIR__ . '/Pages/assets/css/layout.css'
    ];

    private $renderingPlainObject = [];

    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Renders documentation page
     */
    public function render()
    {
        $render = isset($_REQUEST['render']) ? $_REQUEST['render'] : 'layout';
        switch ($render) {
            case 'enum':
                include 'Pages/enum.php';
                break;
            case 'layout':
            default:
                include 'Pages/layout.php';
        }
    }

    /**
     * Sets header title
     * @param string $tile
     */
    public function setTitle($tile)
    {
        $this->title = $tile;
    }

    /**
     * Adds JavaScript file to page
     * @param string $path
     */
    public function addScript($path)
    {
        $this->scripts[] = $path;
    }

    /**
     * Adds CSS file to page
     * @param string $path
     */
    public function addCssFile($path)
    {
        $this->cssFiles[] = $path;
    }

    /**
     * Returns all JavaScript files added
     * @return string[]
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * Returns all CSS files added
     * @return string[]
     */
    public function getCssFiles()
    {
        return $this->cssFiles;
    }

    /**
     * Returns headers expected for $method
     * @param Method $method
     * @return string[]
     */
    private function getRequestHeaders(Method $method)
    {
        $headers = [];
        foreach ($method->annotations as $annotation) {
            if ($annotation instanceof Header) {
                $headers[$annotation->name] = $method->parameters[$annotation->parameter]->description;
            }
        }
        return $headers;
    }

    /**
     * Returns response errors expected for $method
     * @param Method $method
     * @return array
     */
    private function getResponseErrors(Method $method)
    {
        $hasHeader = false;
        $errors = [
            [
                'code' => 400,
                'description' => 'One or more fields validation failed'
            ]
        ];
        foreach ($method->annotations as $annotation) {
            if ($annotation instanceof Assert) {
                $capability = CapabilitiesFactory::get($annotation->capability, new HttpRequest());
                $exception = CapabilityException::fromCapability($capability);
                $errors[] = [
                    'code' => $exception->getHttpCode(),
                    'description' => $exception->getMessage(),
                ];
            } elseif ($annotation instanceof Throws) {
                $reflector = new \ReflectionClass($annotation->class);
                $code = is_numeric($annotation->code)
                    ? intval($annotation->code)
                    : $reflector->getConstant($annotation->code);
                $exception = $reflector->newInstance('', $code);
                if ($exception instanceof HttpException) {
                    $errors[] = [
                        'code' => $exception->getHttpCode(),
                        'description' => $annotation->description,
                    ];
                }
            } elseif ($annotation instanceof Header && !$hasHeader) {
                if (!$method->parameters[$annotation->parameter]->hasDefault) {
                    $ex = new RequestException('', RequestException::MISSING_HEADER);
                    $errors[] = [
                        'code' => $ex->getHttpCode(),
                        'description' => "Mandatory header missing"
                    ];
                    $hasHeader = true;
                }
            }
        }
        return $errors;
    }

    /**
     * Returns fields expected for $method
     * @param Method $method
     * @return array
     */
    private function getFields(Method $method)
    {
        $hasSuperBody = false;
        foreach ((array)$method->annotations as $annotation) {
            if ($annotation instanceof Body) {
                if ($annotation->parameter === '*') {
                    $hasSuperBody = true;
                    break;
                }
            }
        }

        if ($hasSuperBody) {
            return [];
        }

        $excludedFields = [];
        foreach ($method->annotations as $annotation) {
            if ($annotation instanceof Body || $annotation instanceof Header) {
                $excludedFields[] = $annotation->parameter;
            }
        }
        $fields = [];
        foreach ($method->parameters as $parameter) {
            if (!in_array($parameter->name, $excludedFields)) {
                if ($this->isTypePlainObject($parameter->type)) {
                    $fields = array_merge(
                        $fields,
                        $this->getFieldsFromPlainObject($parameter->type->name, $parameter->name)
                    );
                } else {
                    $fields[] = $this->getFieldInfo($parameter);
                }
            }
        }
        return $fields;
    }

    /**
     * Returns Json representation of body expected for request
     * @param Method $method
     * @return null|string
     */
    private function getRequestBody(Method $method)
    {
        /** @var Parameter $bodyParameter */
        $bodyParameter = null;
        foreach ((array)$method->annotations as $annotation) {
            if ($annotation instanceof Body) {
                if ($annotation->parameter !== '*') {
                    $bodyParameter = $method->parameters[$annotation->parameter];
                } else {
                    $bodyParameter = '*';
                }
                break;
            }
        }

        if ($bodyParameter instanceof Parameter) {
            return $this->getObjectStructure($bodyParameter);
        } elseif ($bodyParameter === '*') {
            $properties = [];
            foreach ($method->parameters as $parameter) {
                $properties[] = '<span class="property" title="' . $parameter->description . '">'
                    . $parameter->name . '</span>: '
                    . trim($this->getObjectStructure($parameter, 1));
            }
            $body = "{\n   " . implode(",\n   ", $properties) . "\n}";
            return $body;
        }
        return null;
    }

    /**
     * Returns Json representation of body returned in response
     * @param Method $method
     * @return null|string
     */
    private function getResponseBody(Method $method) {
        if ($method->return === null) {
            return null;
        }
        $parameter = new Parameter();
        $parameter->type = $method->return;
        return $this->getObjectStructure($parameter);
    }

    /**
     * Returns field representation of object properties
     * @param string $className
     * @param string $parentProperty
     * @return array
     */
    private function getFieldsFromPlainObject($className, $parentProperty)
    {
        if (isset($this->renderingPlainObject[$className])) {
            return [[
                'name' => $parentProperty,
                'type' => substr($className, strrpos($className, '\\')+1),
                'text' => null,
            ]];
        }

        $this->renderingPlainObject[$className] = true;
        $metadata = MetadataStorage::getClassMetadata($className);
        $fields = [];
        foreach ($metadata->properties as $property) {
            $fieldName = $parentProperty . '[' . $property->name . "]";
            if ($this->isTypePlainObject($property->type)) {
                $fields = array_merge(
                    $fields,
                    $this->getFieldsFromPlainObject($property->type->name, $fieldName)
                );
            } else {
                $fields[] = $this->getFieldInfo($property, $parentProperty);
            }
        }
        unset($this->renderingPlainObject[$className]);

        return $fields;
    }

    /**
     * Returns field information
     * @param Value $value
     * @param null $parentField
     * @return array
     */
    private function getFieldInfo(Value $value, $parentField = null)
    {
        $enum = '';
        if ($value->enum !== null) {
            $type = substr($value->enum, strrpos($value->enum, '\\')+1);
            $enum = '<span class="enum" data-class="' . $value->enum . '">' . $type . '</span>:';
        }

        $typeName = $value->type->name;
        if ($typeName == UploadedFile::class) {
            $typeName = '<span class="file">File</span>';
        } elseif ($typeName == \DateTime::class) {
            $typeName = '<span class="date" ' .
                'title="DateTime string in format: ' . ScalarConverter::getDateFormat() .
                '">DateTime</span>:string';
        }

        if ($value->hasDefault) {
            $valueSet = "optional, default = " . ($value->default === null ? "null" : ScalarConverter::toString($value->default));
        } else {
            $valueSet = "mandatory";
        }

        return [
            'name' => $parentField !== null ? $parentField . '[' . $value->name . "]" : $value->name,
            'type' => $enum . $typeName . ($value->type->isArray ? '[]' : ''),
            'text' => "($valueSet) " . $value->description,
        ];
    }

    /**
     * Returns JSON representation for metdata;
     * @param Value $value
     * @param int $level
     * @return mixed|string
     */
    private function getObjectStructure(Value $value, $level = 0)
    {
        $type = is_array($value->type) ? array_pop($value->type) : $value->type;
        if (!$this->isTypePlainObject($type)) {
            $field = $this->getFieldInfo($value);
            return $field['type'];
        }

        if (isset($this->renderingPlainObject[$type->name])) {
            return $type->isArray ? "[{}]" : "{}";
        }

        $this->renderingPlainObject[$type->name] = true;

        $strLevel    = str_repeat(' ', 3*$level);
        $strSubLevel = str_repeat(' ', 3*($level+1));

        $metadata = MetadataStorage::getClassMetadata($type->name);
        $structure = [];
        foreach ($metadata->properties as $property) {
            $structure[] = $strSubLevel
                . '<span class="property" title="' . $property->description . '">'
                . $property->name . '</span>: '
                . trim($this->getObjectStructure($property, $level+1));
        }

        $html = "{<br/>" . implode(",<br/>", $structure) . "<br/>" . $strLevel . "}";
        if ($type->isArray) {
            $html = "[$html]";
        }
        $html = $strLevel . $html;

        unset($this->renderingPlainObject[$type->name]);

        return $html;
    }

    /**
     * Checks if given $type is a PlainObject
     * @param Type $type
     * @return bool
     */
    private function isTypePlainObject(Type $type)
    {
        return is_subclass_of($type->name, PlainObject::class);
    }

    /**
     * Returns constants in $class
     * @return array|null
     */
    private function getEnumValues()
    {
        $class = $_REQUEST['class'];
        if (!class_exists($class) && !interface_exists($class)) {
            return null;
        }

        $reflection = new \ReflectionClass($class);
        $constDocs = TokenParser::getConstantDocs($reflection);
        $enum = [];
        foreach ($reflection->getConstants() as $name => $value) {
            $enum[$value] = $constDocs[$name] ?: $name;
        }
        return $enum;
    }

}