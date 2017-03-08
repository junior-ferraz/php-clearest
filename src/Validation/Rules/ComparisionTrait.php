<?php
namespace CleaRest\Validation\Rules;

/**
 * Used for validation rules that use comparision operators
 */
trait ComparisionTrait
{
    /**
     * @param mixed $operand1
     * @param string $operator
     * @param mixed $operand2
     * @return bool
     */
    private function compare($operand1, $operator, $operand2)
    {
        switch ($operator) {
            case '>'  : return $operand1 >  $operand2;
            case '>=' : return $operand1 >= $operand2;
            case '<'  : return $operand1 <  $operand2;
            case '<=' : return $operand1 <= $operand2;
            case '='  : return $operand1 == $operand2;
            case '==' : return $operand1 == $operand2;
            case '!=' : return $operand1 != $operand2;
            case '<>' : return $operand1 != $operand2;
        }
        return false;
    }
}