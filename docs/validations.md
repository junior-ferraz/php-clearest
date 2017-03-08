# Validations

CleaRest supports automatic validations for your convenience. 
This removes the need to test data validation in UnitTests, since you can delegate this layer to the framework.

## @validate annotation

To validate method's arguments or object properties you simply have to add the annotation `@validate` for it.
This annotation has the following sintax:
```
@validate [<parameter>] <rule> [<arguments> ...]
```
Here is an example of *Service* and *PlainObject* with validated properties.

See the use of `@validate` on a method's parameter. Consider the file *src/MyBakery/Products/ProductShelf.php*:
```php
<?php
namespace MyBakery\Products;

use CleaRest\Services\Service;

interface ProductShelf extends Service
{
    /**
     * @validate $quantity > 0
     * @param Product $product
     * @param int $quantity
     */
    public function addProduct(Product $product, $quantity);
}
```
Now consider the file *src/MyBakery/Products/Product.php*.
```php
<?php

namespace MyBakery\Products;

use CleaRest\Api\Data\PlainObject;

class Product implements PlainObject
{
    /**
     * @validate length > 5
     * @var string
     */
    public $name;

    /**
     * @enum ProductType
     * @var int
     */
    public $type;

    /**
     * @validate > 0
     * @var float
     */
    public $price;

    /**
     * @var \DateTime
     */
    public $expiration;
}
```

In PlainObjects, the validated parameter is the property where the annotation is.
Therefore the first argument for this annotation can be ignored.

Note also the annotation `@enum` which is a special validation annotation.
See how the enum *src/MyBakery/Products/ProductType.php* would look like.
```php
<?php
namespace MyBakery\Products;


interface ProductType
{
    const BEVERAGES = 1;
    const FOOD = 2;
    const OTHER = 3;
}
```

If you implement a class for *ProductShelf*, bind it to a route `POST /products` 
(if you don't know how to do it, [click here](router.md)) and execute a call with the body bellow ...
```json
{
	"quantity": -10,
	"product": {
		"name": "bla",
		"type": 4,
		"price": -1
	}
}
```
... a `CleaRest\Validation\ValidationException` will be thrown on the service call.
This exception, if not caught, will return a *400 (Bad Request)* status response with the following body:
```json
{
  "error": {
    "message": "Validation failed"
  },
  "violations": [
    {
      "message": "String length is not > 5",
      "field": "product.name",
      "value": "bla"
    },
    {
      "message": "Value does not belong to enum",
      "field": "product.type",
      "value": 4
    },
    {
      "message": "Given value is not grater than 0",
      "field": "product.price",
      "value": -1
    },
    {
      "message": "Given value is not grater than 0",
      "field": "quantity",
      "value": -10
    }
  ]
}
```

## @enum annotation

Often we have arguments that accept only a set of values defined.
Modern languages have *Enums* for those cases, but PHP does not offer such structure.
To work around this limitation, CleaRest has the annotation `@enum` that can be set to a method parameter or an object property.

Suppose you have a *TranslationService* which returns the text based on a key and a language:
```php
<?php
/**
 * this interface plays the enum role 
 */
interface Language
{
    const ENGLISH = 'en';
    const GERMAN = 'de';
    const SPANISH = 'es';
    const FRENCH = 'fr';
}
```

By adding an `@enum` annotation to the interface method, the framework takes care of the validation by itself.
```php
use CleaRest\Services\Service;

interface TranslationService extends Service
{
    /**
     * Returns text based on key and language (by default English)
     *
     * @enum Language $lang
     * @param string $key translation key
     * @param string $lang language dictionary
     * @return string
     */
    public function getText($key, $lang = Language::ENGLISH);
}
```
A request with `$lang = pt` will return the response `400` (Bad Request) with this body:
```json
{
  "error": {
    "message": "Validation failed"
  },
  "violations": [
    {
      "message": "Value does not belong to enum",
      "field": "lang",
      "value": "pt"
    }
  ]
}
```

## Validation Rules

Validation rules are the second argument in a `@validate` annotation and define what rule will be checked against
the value coming from the parameter assigned to it.

The framework provides a list of standard validation rules, as you can se following, but at any time you can create
your own validation rules and register it to be used.

### Standard ones

CleaRest provides the following list of validation rules ready for use:
 * `@validate $param NotNull`: succeed if *$param* not null
 * `@validate $param NotEmpty`: succeed if *$param* not empty
 * `@validate $param required`: succeed if *$param* not null or empty
 * `@validate $param < value`: succeed if *$param* smaller than *value*
 * `@validate $param <= value`: succeed if *$param* smaller than or equals to *value*
 * `@validate $param > value`: succeed if *$param* grater than *value*
 * `@validate $param >= value`: succeed if *$param* grater than or equals to *value*
 * `@validate $param length operator value`: returns the comparison between the string length of $param and *value* considering *operator* (<, <=, >, >=, ==)
 * `@validate $param matches regex`: succeed if *$param* matches *regex*

### Implement a custom validation rule

It's very easy to implement customs validation rules. 
You must only implement the interface `CleaRest\Validation\ValidationRule` 
and register the rule in the `CleaRest\Validation\Validator`

Suppose we want to check if the property *expiration* date from *Product* is in the future (product still consumable).
We can create the rule bellow (e.g in file *src/MyBakery/Validations/YetToComeRule*):
```php
<?php
namespace MyBakery\Validations;

use CleaRest\Validation\ValidationRule;

class YetToComeRule implements ValidationRule
{
    /**
     * @param mixed $value
     * @param array $args
     * @return bool
     */
    public function validate($value, array $args)
    {
        if (! $value instanceof \DateTime) {
            return false;
        }

        // In the future, grater than now
        return $value > new \DateTime();
    }

    /**
     * @param mixed $value
     * @param array $args
     * @return string
     */
    public function getErrorMessage($value, array $args)
    {
        return "Date is not in the future";
    }
}
```
Then you just need to register it in the *Validator*. 
Put it somewhere in your bootstrap or create a include file for registration if you have many rules.
```php
\CleaRest\Validation\Validator::registerRule('YetToCome', new \MyBakery\Validations\YetToComeRule());
```

Done that, if you make a call with `product[expiration]=2012-12-21T00:00:00+0000`, 
you wil get a *400* with this violation:
```json
{
  "error": {
    "message": "Validation failed"
  },
  "violations": [
  {
      "message": "Date is not in the future",
      "field": "product.expiration",
      "value": {
        "date": "2012-12-21 00:00:00.000000",
        "timezone_type": 1,
        "timezone": "+00:00"
      }
    }
  ]
}
```