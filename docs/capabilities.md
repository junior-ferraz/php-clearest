# Capabilities

Capabilities are used in assert conditions that can be checked all over the application.
Capabilities are useful to:
 * Avoid a method to be called if user does not have specific capability
 * Define what implementation should be instantiated for a service
 * Change the workflow depending on conditions
 
Suppose there is a capability called `IsBetaUser` which is true when the user is registered in the beta pool.

Those are the cases when capabilities can be used:

### 1. During method calls
By using the annotation `@assert` on the method's doc comment.
```php
/**
 * @assert IsBetaUser
 */
public function exclusiveAction();
```
When user is not in the pool, a `CleaRest\Capabilities\CapabilityException` will be thrown.
If this capability is not caught, it will be returned to the client as a *403 (Forbidden)* .
         
### 2. In a condition

By using the `CleaRest\Services\Util\Capabilities` service as a dependency for your service.
```php
<?php
use CleaRest\Services\BaseService;
use CleaRest\Services\Util\Capabilities;

/**
 * @version 1 
 */
class MyServiceImplementation extends BaseService implements MyService
{
    /**
     * @inject
     * @var Capabilities 
     */
    protected $capabilities;
    
    public function myMethod()
    {
        if ($this->capabilities->check('IsBetaUser')) {
            // do something only beta users can
        }
    }
}
```
    
### 3. As a dependency injection criteria

By using the annotation `@assert` on the class' doc comment.
```php
<?php
use CleaRest\Services\BaseService;
use CleaRest\Services\Service;

interface MessageSearch extends Service
{
    /**
    * @param string $text
    * @return Message[]
    */
    public function search($text);
}

/**
 * @version 1 
 */
class MySqlMessageSearch extends BaseService implements MessageSearch
{
    public function search($text)
    {
        // search in mysql
    }
}

/**
 * @version 2
 * @assert IsBetaUser
 */
class ElasticMessageSearch extends BaseService implements MessageSearch
{
    public function search($text)
    {
        // search in Elastic
    }
}
```
In the example above, only beta users will use the *ElasticMessageSearch*, all the others will
receive an instance of *MySqlMessageSearch* instead.
 

## How to write a Capability

Capabilities are a special type of service that implement the interface `CleaRest\Capabilities\Capability`.
For convenience there is a abstract implementation `CleaRest\Capabilities\BaseCapability` 
which implements basic methods leaving only the *check* method to be implemented.

Bellow you can see the example of *IsBetaUser* capability:
```php
<?php
namespace MyBakery\Capabilities;

use CleaRest\Capabilities\BaseCapability;

/**
 * @alias IsBetaUser
 */
class BetaUserCapability extends BaseCapability
{
    public function check()
    {
        // do logic to check if user is in the beta pool
        return false;
    }
}
```
Since a capability is a service, you can inject any other service as dependency to use.
If you don't know how to inject dependencies, take a look [here](di.md).

    **Note**: don't forget to run the metadata generator before running the code.
    If you don't know how to do it, [click here](metadata.md).*

When this capability fails while beeing called on a method, a `CleaRest\Capabilities\CapabilityException`
will be thrown and, if not caught, will be returned to the client as a *403 (Forbidden)* with this body:
```json
{
  "error": {
    "message": "Forbidden. User has no capability IsBetaUser",
    "code": 3
  },
  "data": null
}
```
If you want a custom error message and status code, you have to simply override the methods *getErrorMessage*
and *getErrorCode* from `BaseCapability`. See the example extended:
```php
<?php
use CleaRest\Capabilities\BaseCapability;
use CleaRest\Capabilities\CapabilityException;

/**
 * @alias IsBetaUser
 */
class BetaUserCapability extends BaseCapability
{
    public function check()
    {
        // do logic to check if user is in the beta pool
        return false;
    }

    public function getErrorCode()
    {
        return CapabilityException::UNAUTHORIZED;
    }

    public function getErrorMessage()
    {
        return "User is not in beta pool";
    }
}
```
This will return a *401 (Unauthorized)* response with this body:
```json
{
  "error": {
    "message": "User is not in beta pool",
    "code": 3
  },
  "data": null
}
```