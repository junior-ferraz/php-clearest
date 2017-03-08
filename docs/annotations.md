# Annotations

CleaRest is a annotation based framework, so annotations are included in the objects metadata.
You can add custom annotations to the framework and retrieve them after generation with `MetadataStorage`
([click here](metadata.md#loading-metadata) to know how to retrieve metadata).

## How to add a custom annotation

First you must implement a class that extends `CleaRest\Metadata\Annotation`.
See the example bellow:
```php
<?php
use CleaRest\Metadata\Annotation;

class Email extends Annotation
{
    /**
     * @var string
     */
    public $firstName;
    /**
     * @var string
     */
    public $lastName;
    /**
     * @var string
     */
    public $address;
    
    public function __construct($firstName, $lastName, $address)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->address = $address;
    }
}
```

Then register it in the `Annotation` class:
```php
<?php
\CleaRest\Metadata\Annotation::register('email', Email::class);
```

To override a default annotation you just have to register it with the same name.

## Default Annotations

Those are the annotations provided by CleaRest.

### @alias

Defines the alias of a *Capability*.
If you want to know how to create a capability [click here](capabilities.md)
```
 @alias <name>
```

### @assert

Used on methods for asserting a user has a *Capability* or on a class for conditional injection.
If you want to know more about this [click here](capabilities.md)
```
 @assert <capability>
```

### @body

Defines the parameter in a service call that will receive the content from the request body.
```
 @body $parameter
```
If you use `*` as argument for this annotation, you sign that all parameters are expected to be found in the
request body. That means the body is expected to contain properties with names as the service parameters.

Take a look [here](objects.md#as-request-body) to see how to do it.

### @enum

Defines the class or interface which contains all the enum values as constants.
It can be used on services methods or plain object properties.
```
 @enum <class|interface> [$parameter]
```
Read more about it [here](validations.md#enum-annotation)

### @header

Indicates that a parameter shall receive the value present in a header.
```
 @header <name> $parameter
```
To know how to use it [click here](other-features.md#header-annotation)

### @inject

Indicates a property is a dependecy and should have its value injected.
```
 @inject [version]
```
To read more about the *Dependency Injection Mechanism* [click here](di.md)

### @param

Native PHP annotation, indicates the type of a parameter
```
 @param <type> $parameter [<description>]
```
This annotation is mandatory for services.

### @throws

Native PHP annotation, indicates the exceptions that can be thrown by the method
```
 @throws <type> <code> [<description>]
```
This annotation allows the auto generated documentation to be better described

### @return

Native PHP annotation, indicates the type returned by a method
```
 @return <type>
```

### @validate

Defines a validation to be done on a parameter or object property.
```
 @validate [$parameter] <rule> [<parameters>]
```
To learn how to use this annotation, [click here](validations.md)

### @var

Native PHP annotation, indicates the type of a property
```
 @var <type>
```
This annotation is mandatory for plain objects.

### @version

Defines the implementation version of a service interface
```
 @version <number> [<name>]
```
This annotation is mandatory for service classes.
If you want to know more about the *Dependency Injection Mechanism* [click here](di.md)