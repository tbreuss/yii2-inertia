# Inertia.js Yii 2 Adapter

This is the Yii 2 server-side adapter for [Inertia](https://inertiajs.com).

With Inertia you are able to build single-page apps using classic server-side routing and controllers, without building an API. 

To use Inertia you need both a server-side adapter as well as a client-side adapter.
 
Be sure to follow the installation instructions for the [client-side framework](https://inertiajs.com/client-side-setup) you use.

## Demo

<https://pingcrm-yii2.tebe.ch>

## Installation

Composer require dependency:

```sh
composer require tebe/yii2-inertia:dev-master
```

Edit `config/web.php`:

```php
<?php
return [
    ...
    'bootstrap' => ['inertia']
    ...
    'components' => [
        'inertia' => [
            'class' => 'tebe\inertia\Inertia'
        ],
        'request' => [
            'cookieValidationKey' => '<cookie_validation_key>',
            'enableCsrfValidation' => false,
            'enableCsrfCookie' => false
        ]      
    ]
    ...
];   
```

## Controllers

Your backend controllers should extend from `tebe\inertia\web\Controller`.
Instead of the render method within your actions you should use the `inertia` method. 

```php
<?php

namespace app\controllers;

use tebe\inertia\web\Controller;

class DemoController extends Controller
{
    public function actionIndex()
    {
        $params = [
            'data' => [],
            'links' => []
        ];
        return $this->inertia('demo/index', $params);
    }
}
```

## Routing

Use your Yii server-side routes as usual. There is nothing special.

## CSRF protection

Yii's CSRF protection is not optimized for Axios.
Axios is the HTTP library that Inertia uses under the hood.

The easiest way to implement CSRF protection is using the customized Request component. 
Simply edit `config/web.php` file:
 
 ```php
 <?php
 return [
     'components' => [
         'request' => [
             'class' => 'tebe\inertia\web\Request',             
             'cookieValidationKey' => '<cookie_validation_key>'
         ]      
     ]
 ];   
 ```

Please see the [security page](https://inertiajs.com/security) for more details.

## Client-side setup

To use Inertia you need to setup your client-side framework. 
This primarily includes updating your main JavaScript file to boot the Inertia app. 
Please see the [client-side setup page](https://inertiajs.com/client-side-setup) for more details.

## More about Inertia

Visit [inertiajs.com](https://inertiajs.com/) to learn more.
