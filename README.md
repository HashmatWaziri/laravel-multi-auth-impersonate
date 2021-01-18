# laravel-multi-auth-impersonate

[![Latest Version on Packagist](https://img.shields.io/packagist/v/HashmatWaziri/laravel-multi-auth-impersonate.svg?style=flat-square)](https://packagist.org/packages/HashmatWaziri/laravel-multi-auth-impersonate)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/HashmatWaziri/laravel-multi-auth-impersonate/run-tests?label=tests)](https://github.com/HashmatWaziri/laravel-multi-auth-impersonate/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/HashmatWaziri/laravel-multi-auth-impersonate.svg?style=flat-square)](https://packagist.org/packages/HashmatWaziri/laravel-multi-auth-impersonate)





## Requirements
- Php > 7.3 or 8

## Installation

You can install the package via composer:

```bash
composer require HashmatWaziri/laravel-multi-auth-impersonate
```



You can publish the config file with:
```bash
php artisan vendor:publish --provider="HashmatWaziri\LaravelMultiAuthImpersonate\LaravelMultiAuthImpersonateServiceProvider" --tag="multiAuthImpersonate"
```

This is the contents of the published config file:

```php
return [
 /**
     * The session key used to store the original user id.
     */
    'session_key' => 'impersonated_by',

    /**
     * The session key used to stored the original user guard.
     */
    'session_guard' => 'impersonator_guard',

    /**
     * The session key used to stored what guard is impersonator using.
     */
    'session_guard_using' => 'impersonator_guard_using',

    /**
     * The default impersonator guard used.
     */
    'default_impersonator_guard' => 'web',
];
```

### Redirect URLs

**take Redirect** : when impersonating another user, you can add the method  `takeRedirectTo()` to your model which is being impersonated:

example:
```php
  class User extends Authenticatable implements MustVerifyEmail
{

    use Notifiable,Impersonate;



    public static function takeRedirectTo(){

        return url('/after-login');
    }

}
```


**leave Redirect** : when an impersonator ( the one who impersonated or logged in as another user) is leaving the impersonation, you can add the method  `leaveRedirectTo()` to your model:

example:
```php
  class User extends Authenticatable implements MustVerifyEmail
{

    use Notifiable,Impersonate;



    public static function leaveRedirectTo(){

        return url('/dashboard');
    }

}
```


## Usage


Impersonate a user:
```php
$other_user = App\Student::find(1);
Auth::user()->impersonate($other_user);
// You're now logged as the $other_user
```

Leave impersonation:
```php
Auth::user()->leaveImpersonation();
// You're now logged as your original user.
```

### Using the built-in controller

In your routes file, under web middleware, you must call the `impersonate` route macro.

```php
Route::impersonate();
```

Alternatively, you can execute this macro with your `RouteServiceProvider`.

```php
namespace App\Providers;

class RouteServiceProvider extends ServiceProvider
{
    public function map() {
	// here you can supply an array of guards ex ['web','employee','etc']
        Route::middleware('web')->group(function (Router $router) {
            $router->multiAuthImpersonate();
        });
    }
}
```

```php
// Where $id is the ID of the user you want impersonate
route('impersonate', $id)

// You should also add `guardName`
route('impersonate', ['id' => $id, 'guardName' => 'admin'])

// Generate an URL to leave current impersonation
route('impersonate.leave')

```
### Defining impersonation authorization

By default all users can **impersonate** an user.  
You need to add the method `canImpersonate()` to your guard model:
example:
```php
  class User extends Authenticatable implements MustVerifyEmail
{

    use Notifiable,Impersonate;


//    /**
//     * @return bool
//     */
    public function canImpersonate()
    {

        return true ;

    }

}
```

By default all users can **be impersonated**.  
You need to add the method `canBeImpersonated()` to your guard model to extend this behavior:

```php
    /**
     * @return bool
     */
    public function canBeImpersonated()
    {
        // For example
        return $this->can_be_impersonated == 1;
    }
```

### Using your own strategy

- Getting the manager:
```php
// With the app helper
app('impersonate')
// Dependency Injection
public function impersonate(ImpersonateManager $manager, $user_id) { /* ... */ }
```

- Working with the manager:

```php

$manager = app('impersonate');

// Find an user by its ID
$manager->findUserById($id);

// TRUE if your are impersonating an user.
$manager->isImpersonating();

// Impersonate an user. Pass the original user and the user you want to impersonate
$manager->take($from, $to);

// Leave current impersonation
$manager->leave();

// Get the impersonator ID
$manager->getImpersonatorId();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [HashmatWaziri](https://github.com/HashmatWaziri)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
