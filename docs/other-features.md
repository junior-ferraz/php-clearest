# Other features

CleaRest has some other handy features you can see bellow.

## File Upload

To use uploaded files, simply annotate a parameter from your service with type `CleaRest\Api\Data\UploadedFile`.
The field name will be the same as the parameter name. For example:
```php
<?php
use CleaRest\Api\Data\UploadedFile;
use CleaRest\Services\Service;

interface ProfileService extends Service
{
    /**
     * @param UploadedFile $picture
     */
    public function setProfilePicture(UploadedFile $picture);
    
    /**
     * @param UploadedFile[] $pictures 
     */
    public function addPicturesToAlbum(array $pictures);
}
```
As you can see in the example above, both single files or array of files are allowed.
This field will be marked on the auto generated documentation with a special type *File*

## Header annotation

Whenever you want to get the value from a header, instead of manipulating the request directly you can simply
use the annotation `@header` instead. This annotation must be declared in a service's method and has this syntax:
```
@header HEADER_NAME $parameter
```
See the example bellow:
```php
<?php
/**
 * @header SpecialOption $option
 * @param string $option 
 */
public function doSomething($option = null);
```
The parameter `$option` will get the value from header `SpecialOption` or *null* if the header is not set.
But if the parameter `$option` did not have a default value set in the method's signature
it would throw a *RequestException* that would be returned to the client with a *412 (Precondition failed)*:
```json
{
  "error": {
    "message": "Mandatory header SpecialOption missing",
    "code": 2
  },
  "data": {
    "header": "SpecialOption"
  }
}
```

## Content-Type encoders

CleaRest has implemented content-type encoders for:
 * application/json
 * application/xml
 * application/x-www-form-urlencoded
 * multipart/form-data
 
If you want to use any other content-type you must implement your own encoder.
This encoder must extend from `CleaRest\Api\ContentTypeEncoder` and be registered in the *ContentTypeRegistry*:
```php
CleaRest\Api\ContentTypeRegistry::register(new MyOwnEncoder());
```

## Session service

CleaRest provide a Session service that can be added as a dependency to your service.
To use it, add a dependency for `\CleaRest\Services\Util\Session` as the example bellow:
```php
<?php
use CleaRest\Services\BaseService;

class MyServiceImpl extends BaseService implements MyService
{
    /**
     * @inject
     * @var \CleaRest\Services\Util\Session
     */
    protected $session;
}
```
If you want to implement a custom version of session, simply implement the interface with a `@version` annotation
with number 2 or higher. This will automatically update the usages of session.

## Environments

Often it's necessary to know which environment you are in to decide upon logging or any action environment dependent.
For this purpose there is the class `CleaRest\Environments\Environment`.

This is how a environment condition should be made:
```php
<?php
use CleaRest\Environments\LiveEnvironment;
use CleaRest\Environments\DevEnvironment;

if (LiveEnvironment::isCurrent()) {
    // do something only possible in live environment
}
if (DevEnvironment::isCurrent()) {
    // do something only possible in dev environment
}
```
In your environment specific bootstrap you can set the environment instance.
Here is an example how to set the environment to *live* one:
```php
<?php
\CleaRest\Environments\Environment::setCurrent(new \CleaRest\Environments\LiveEnvironment());
```

There are three types of environments available in `CleaRest\Environments`:
 * LiveEnvironment
 * StageEnvironment
 * DevEnvironment

But you can implement any other environment type by simply extending the `CleaRest\Environments\Environment` class:
```php
<?php
use CleaRest\Environments\Environment;

class IntegrationEnvironment extends Environment
{
    
}
// then somewhere...
Environment::setCurrent(new IntegrationEnvironment());
```