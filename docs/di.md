# Dependency Injection

CleaRest provides an automatic dependency injection based on annotations.
Suppose you have a *PictureService* that returns the profile picture URL for given profile ID,
and this service is a dependency for a *ProfileService*, which returns a *Profile* Object:

```php
<?php
use CleaRest\Services\Service;
use CleaRest\Api\Data\PlainObject;

class Profile extends PlainObject
{
    /**
     * @var string 
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $picture;
}

interface ProfileService extends Service
{
    /**
     * Returns profile
     * @param string $id
     * @return Profile
     */
    public function getProfile($id);
}

interface PictureService extends Service
{
    /**
     * Returns profile picture URL
     * @param string $profileId
     * @return string
     */
    public function getProfilePictureUrl($profileId);
}
```

## How to consume a service as a dependency

This is how dependency declaration in the implementation of *ProfileService* would look like:
```php
<?php
use CleaRest\Services\BaseService;
/**
 * @version 1 
 */
class ProfileServiceImpl extends BaseService implements ProfileService
{
    /**
     * To define a service as a dependency you must create a protected or public attribute
     * with the type of the service interface and annotated with @inject 
     * @inject 
     * @var ProfilePicture   
     */
    protected $pictureService;
    
    public function getProfile($id)
    {
        /**
         * Logic to load profile 
         * @var Profile $profile 
         */
        $profile = $this->loadProfile($id);
        
        // Calling dependency
        $profile->picture = $this->pictureService->getProfilePictureUrl($id);
    }
}
```

If you have more than one implementation of the requested service dependency, the highest version will be taken,
unless there is a condition set for it (see [conditional injection](#conditional-injection) bellow).
But if you want a specific version, just declare its number or name as the first argument for the `@inject` annotation:
```
@inject <version-number|name>
```

## How to consume a service outside a service

If you need your service outside of a service or API router, you can get an instance of it with the `ServiceFactory`:
```php
<?php
use CleaRest\Services\ServicesFactory;
$request = new \CleaRest\Api\Network\HttpRequest();
$instance = ServicesFactory::getServiceInstance(\My\Service\Interface\Name::class, $request);
```

The factory method has a third parameter `$version`. By default, if not declared, the highest version is taken.
If you want a specific version, declare this parameter with the implementation version number or name.

## Conditional injection

By default the `ServiceFactory` will instantiate the latest version implemented for a service.
But if you want the factory to return a specific version depending on conditions related to the request,
you can use [Capabilities](capabilities.md) for that.

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

In the example above, whenever it's required an instance of `MessageSearch`,
 if user has "IsBetaUser" capability, the `ElasticMessageSearch` will be instantiated,
otherwise the `MySqlMesageSearch` will be taken.

The `ServiceFactory` will go from the latest to the oldest version checking for its capability (if defined),
until it finds one version that fits.

To know more about how to create capabilities [click here](capabilities.md#how-to-write-a-capability)
