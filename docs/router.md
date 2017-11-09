# Router

The Router is the starting point of an API request.
Services are bound to the router via routes and methods.

## Binding services to the a Router

A router has many routes and each route can have many methods. Each method is bound to a service method.
There is two ways to configure those routes and methods: with a Yaml configuration or accessing the router instance
directly from code.

### With Yaml configuration

Create a .yml file and put it somewhere of your convenience. This file should have this structure:
```yaml
Routes:
    my/route:
      GET: Full\Qualified\Name\Of\ServiceInterface::methodName
      POST: Full\Qualified\Name\Of\ServiceInterface::anotherMethod
      PUT: Full\Qualified\Name\Of\AnotherServiceInterface::methodName
    # URL parameter shall start with $ sign. 
    # The same parameter in the service's method will get its value
    my/route/with/$parameter/in/url:
      GET: Full\Qualified\Name\Of\WhateverServiceInterface::whateverMethod
    another/route:
      # You can also define a method this way
      POST:
        Service: Full\Qualified\Name\Of\ServiceInterface
        Method: methodName
        # To enforce a specific implementation version to be used:
        Version: 2
```

Then, in a public accessible directory of your server (htdocs or so) write the api index file.
For sake of organization it's recomended that this folder is in your project folder under *public/api*.

Example: *public/api/index.php*
```php
<?php
// Include Composer vendor autoloader
include '../../vendor/autoload.php';

// Start the framework
\CleaRest\Framework::start();

// Create the router based on the yml configuration
$router = \CleaRest\Api\RouterBuilder::createFromFile('router.yml');

$request = new CleaRest\Api\Network\HttpRequest();
$response = new CleaRest\Api\Network\HttpResponse();

// Process request
$router->process($request, $response);
// Render response
$response->render();
```

### With PHP code

If you prefer, you can call directly the `$router` to add the routes and methods.
Here is the Yaml example above written in a PHP code form:

Example: *public/api/router.php*
```php
<?php
$router = new \CleaRest\Api\Router();

$router->getRoute('my/route')
    ->addMethod('GET', Full\Qualified\Name\Of\ServiceInterface::class, 'methodName')
    ->addMethod('POST', Full\Qualified\Name\Of\ServiceInterface::class, 'anotherMethod')
    ->addMethod('PUT', Full\Qualified\Name\Of\AnotherServiceInterface::class, 'methodName');

$router->getRoute('my/route/with/$parameter/in/url')
    ->addMethod('GET', Full\Qualified\Name\Of\WhateverServiceInterface::class, 'whateverMethod');

$router->getRoute('another/route')
    ->addMethod('POST', Full\Qualified\Name\Of\ServiceInterface::class, 'methodName', 2);

return $router;
```

Example: *public/api/index.php*
```php
<?php
// Include Composer vendor autoloader
include '../../vendor/autoload.php';

// Start the framework
\CleaRest\Framework::start();

// Create the router based on the yml configuration
$router = require 'router.php';

$request = new CleaRest\Api\Network\HttpRequest();
$response = new CleaRest\Api\Network\HttpResponse();

// Process request
$router->process($request, $response);
// Render response
$response->render();
```

## Router Exceptions

A `CleaRest\Api\RouterException` can be thrown in two cases:
 * **Route not found**:
 
 No route matched request URI. If this exception is not caught it will be returned to the client 
 as a response *404 (Not Found)* with this body:
 
 ```json
    {
        "error": {
            "message": "Route not found",
            "code": 1
    },
        "data": {
            "route": "/unknown/route"
        }
    }
 ```
 * **Method not allowed**:
 
 No current request method is not added to route. If this exception is not caught it will be returned to the client 
 as a response *404 (Not Found)* with this body:
 
 ```json
    {
    "error": {
        "message": "Method not allowed",
        "code": 2
    },
    "data": {
        "route": "/known/route",
        "method": "METHOD"
    }
    }
 ```