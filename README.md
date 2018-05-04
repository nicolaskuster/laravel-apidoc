# Generate memorable passwords in a Laravel app
Generate a postman collection and a markdown file of all your routes. To map HTTP-Bodys an validation rules to the output you have to use FormRequests.

## Installation
You can install the package via composer:
```bash
composer require nicolaskuster/laravel-apidoc
```

You can publish the config-file with:
```bash
php artisan vendor:publish --provider="Nicolaskuster\ApiDoc\Providers\ApiDocServiceProvider"
```

### Register the Service Provider in `app/Providers/AppServiceProvider.php`
```php
    public function register()
    {
        if ($this->app->environment() === 'local') {
            $this->app->register(\Nicolaskuster\ApiDoc\Providers\ApiDocServiceProvider::class);
        }
    }
```
## Usage
```bash
php artisan api:doc
```