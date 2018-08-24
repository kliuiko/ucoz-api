# uCoz uAPI for Laravel
This package is a uCoz uAPI module adaptation. The original code is avaliable on [uAPI documentation page](http://api.ucoz.net/ru/manual/module).
Unfortunately only Russian language. :(

## uAPI Documentation
|| Instuction|
|---|---|
| Documentation | http://api.ucoz.net/ru/manual |
| To get uAPI credentials | http://api.ucoz.net/ru/join/reg |

## Instalation
Install package via Composer
```bash
composer require enniosousa/ucoz-api
```
Next, if using Laravel 5, include the service provider within your `config/app.php` file.

```php
'providers' => [
    EnnioSousa\uCozApi\uCozApiServiceProvider::class,
];
```

Publishing config
```bash
php artisan vendor:publish --provider="EnnioSousa\uCozApi\uCozApiServiceProvider"
```

Environment Configuration
```bash
php artisan vendor:publish --provider="EnnioSousa\uCozApi\uCozApiServiceProvider"
```

Environment Configuration (`.env` file on root project)
```env
# http://api.ucoz.net/ru/join/reg
UCOZ_WEBSITE=http://website.ucoz.com.br/
UCOZ_CONSUMER_KEY=
UCOZ_SECRET=
UCOZ_TOKEN=
UCOZ_TOKEN_SECRET=
```

## Usage
```php
$config = []; // overwrite inicial config from 'config/ucoz-api.php'
// You can instace two ways:
$uapi = new \EnnioSousa\uCozApi\uCozApi($config);
// or
$uapi = uAPI();
```

```php
uAPI()->get('/my')->toArray();
uAPI()->get('/my')->toJson();
uAPI()->get('/my')->toObject();
```