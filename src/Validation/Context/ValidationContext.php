<?php

namespace CleaRest\Validation\Context;

abstract class ValidationContext
{
    /**
     * @var string
     */
    public $class;
    /**
     * @var int
     */
    public $index;

    public function getFieldName()
    {
        $index = ($this->index !== null ? '[' . $this->index . ']' : '');
        if ($this instanceof MethodContext) {
            $field = $this->parameter . $index;
            return $field;
        } elseif ($this instanceof ObjectContext) {
            $field = $this->property . $index;
            if ($this->parent !== null) {
                $parentField = $this->parent->getFieldName();
                return $parentField . "." . $field;
            }
            return $field;
        }
        return null;
    }
}
