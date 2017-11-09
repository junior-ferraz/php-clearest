<?php
namespace CleaRest\Validation\Rules;

return [
    '<' => SmallerThan::class,
    '<=' => SmallerThanOrEquals::class,
    '>' => GraterThan::class,
    '>=' => GraterThanOrEquals::class,
    'length' => Length::class,
    'matches' => Matches::class,
    'notNull' => NotNull::class,
    'notEmpty' => NotEmpty::class,
    'required' => NotEmpty::class,
];