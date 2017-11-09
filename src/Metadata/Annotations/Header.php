<?php

namespace CleaRest\Metadata\Annotations;

use CleaRest\Metadata\Annotation;

/**
 * This annotation signs that a parameter value should come from a header, when called via a router. Example:
 *
 * @header My-Header $token
 * @param string $token;
 * public function doSomething($token)
 */
class Header extends Annotation
{
    public $name;

    public $parameter;

    public function __construct($name, $parameter)
    {
        $this->name = $name;
        $this->parameter = str_replace('$', '', $parameter);
    }
}

