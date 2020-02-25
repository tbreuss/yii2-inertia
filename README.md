# Inertia.js Yii 2 Adapter

With Inertia you are able to build single-page apps using classic server-side routing and controllers, without building an API. 

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
    'bootstrap' => ['inertia'],
    'components' => [
      'inertia' => [
        'class' => 'tebe\inertia\Inertia'
      ]
    ]  
    ...
];    
```

Extend controllers from tebe\inertia\web\Controller:

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

Implement client using one of the official adapters like Vue.js, React, or Svelte.

Visit [inertiajs.com](https://inertiajs.com/) to learn more.
