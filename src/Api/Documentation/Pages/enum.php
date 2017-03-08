<?php
/** @var \CleaRest\Api\Documentation\Generator $this */
$enum = $this->getEnumValues();

if ($enum === null) {
    print "No class found for this Enum";
    return;
}

print '<table class="enum-values">';
foreach ($enum as $value => $description) {
    print '<tr>';
    print '<td class="value">' . $value . '</td>';
    print '<td class="description">' . $description . '</td>';
    print '</tr>';
}
print '</table>';