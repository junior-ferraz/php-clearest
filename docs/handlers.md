# Request Handlers

In CleaRest the request process has four phases:
 1. Request is processed, fields, arguments and body are put into parameters
 2. Service is called with processed parameters
 3. Result from service is converted into response array
 4. Converted result is set into response's body

In between those, request handlers are called to handle the event.
A request handler implements the interface `CleaRest\Api\RequestHandler`
or extend the class `AbstractRequestHandler`.

Those are the methods and when they are called:
 * **preProcess**: before the request parameters are processed.
 In this moment the request is raw coming from PHP and the response is untouched;
 * **postProcess**: after processing request fields and service called;
 * **preResponse**: after result is converted into array but not yet set into response;
 * **postResponse**: after result is set into response body;
 * **handleException**: when exception happens;

All those methods shall return a boolean. If it returns *true* the propagation stops and handlers registered earlier
are not called for that phase.

# Registering a Handler

Handlers are registered in the router and the last to be registered is the first to be called.
Propagation stops with the first handler to return true for that phase.

## With Yaml configuration

In the router Yaml configuration (described [here](router.md#with-yaml-configuration)) add at the top:
```yaml
Handlers:
    AddDefault: true
    Classes:
        - Full\Qualified\Name\Of\HandlerA
        - Full\Qualified\Name\Of\HandlerB
```
When the node *AddDefault* is set to *true* (which is the default value when omitted) it adds the default handlers
before adding the classes listed bellow. Many of default handlers are quite important for the well functioning of the
API. It's recommended you keep them and only add on top your handlers which return true and stop the propagation.
But if you want to avoid their registration, set this node to *false*.

## With PHP code

Other option is to, in the router instantiation file (*router.php* described [here](router.md#with-php-code)), add the lines:
```php
<?php
$router->addHandler(new HandlerA());
$router->addHandler(new HandlerB());
```
The *Router* constructor has a boolean parameter `$registerDefaultHandlers` default set to *true*, which registers
the default handlers. Many of default handlers are quite important for the well functioning of the API. 
It's recommended you keep them and only add on top your handlers which return true and stop the propagation.
But if you want to avoid their registration, set this parameter to *false*.


## Default handlers

Those are the default handlers and what they do:

 * **ValidationExceptionHandler**: handles *ValidationExceptions* returning *400* responses with violations.
 * **HttpExceptionHandler**: handles *HttpExceptions* setting response status code and body
 * **FallbackExceptionHandler**: handles exceptions not handled by other exception handlers.
  sets response to 500 and adds exception information if environment is dev.
 * **ContentTypeHandler**: decode request body from content-type and encode response body into content-type.
 * **HttpStatusCodeHandler**: sets the status code to *200* or *204* if not yet set.