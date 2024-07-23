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
composer require tebe/yii2-inertia
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
            'rootElementId' => 'app' // optional per https://inertiajs.com/client-side-setup#defining-a-root-element
        ],
        'request' => [
            'cookieValidationKey' => '<cookie_validation_key>',
            'enableCsrfValidation' => false,
            'enableCsrfCookie' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]            
        ]      
    ]
    ...
];   
```

Note that CSRF protection is disabled.

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

Use your Yii server-side routes as usual. 
There is nothing special.

## CSRF protection

Axios is the HTTP library that Inertia uses under the hood.
Yii's CSRF protection is not optimized for Axios.

The easiest way to implement CSRF protection is using the customized `tebe\inertia\web\Request` component. 
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

### Shared data

The Yii 2 adapter provides a way to preassign shared data for each request. 
This is typically done outside of your controllers. 
Shared data will be automatically merged with the page props provided in your controller.

Massive assignment of shared data:  

```php
<?php

$shared = [
    'user' => [
        'id' => $this->getUser()->id,
        'first_name' => $this->getUser()->firstName,
        'last_name' => $this->getUser()->lastName,
    ],
    'flash' => $this->getFlashMessages(),
    'errors' => $this->getFormErrors(),
    'filters' => $this->getGridFilters()
];
Yii::$app->get('inertia')->share($shared);
```

Shared data for one key:

```php
<?php

$user = [
    'id' => $this->getUser()->id,
    'first_name' => $this->getUser()->firstName,
    'last_name' => $this->getUser()->lastName
];
Yii::$app->get('inertia')->share('user', $user);
```

A good strategy when using shared data outside of your controllers is to implement an action filter.

```php
<?php

namespace app\components;

use yii\base\ActionFilter;

class SharedDataFilter extends ActionFilter
{
    public function beforeAction()
    {
        $shared = [
            'user' => $this->getUser(),
            'flash' => $this->getFlashMessages(),
            'errors' => $this->getFormErrors()
        ];
        Yii::$app->get('inertia')->share($shared);
        return parent::beforeAction($action);
    }
}    
```

And then use this action filter as a behaviour in your controller.

```php
<?php

namespace app\controllers;

use app\components\SharedDataFilter;
use tebe\inertia\web\Controller;

class ContactController extends Controller
{
    public function behaviors()
    {
        return [
            [
                'class' => SharedDataFilter::class
            ]
        ];
    }
    
    public function actionIndex()
    {
        // your action code
    }
}
```

Please see the [shared data page](https://inertiajs.com/shared-data) for more details.

## Client-side setup

To use Inertia you need to setup your client-side framework. 
This primarily includes updating your main JavaScript file to boot the Inertia app. 
Please see the [client-side setup page](https://inertiajs.com/client-side-setup) for more details.

## More about Inertia

Visit [inertiajs.com](https://inertiajs.com/) to learn more.
