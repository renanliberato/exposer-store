# renanliberato/exposer-store

Manage your app state in the backend in an API similar to Redux

## Installing

```composer require renanliberato/exposer-store```

## Getting Started

This code snippet uses almost all the available APIs, so take a look and check if you can reproduce it in your app.

```php
$initialState = [
    'name' => 'Renan'
];

$nameReducer = function($state, $action) {
    switch ($action['type']) {
        case 'RENAME':
            $state['name'] = $action['name'];
            return $state;
        default:
            return $state;
    }
};

class LogMiddleware extends \RenanLiberato\ExposerStore\Middlewares\AbstractMiddleware
{
    public function process($action) {
        var_dump($action);

        return $this->next($action);
    }
}

$store = new \RenanLiberato\ExposerStore\Store\Store(
    $initialState,
    [
        'name' => $nameReducer
    ],
    [
        LogMiddleware::class
    ]
);

// This will use a cookie to save and load your app state.
$store->setPersistor(new \RenanLiberato\ExposerStore\Persistors\CookiePersistor('my_app_cookie', 'my_key'));

echo json_encode($store->getState());
// {"name":"Renan"}

$store->action(['type' => 'RENAME', 'name' => 'José']);

echo json_encode($store->getState());
// {"name":"José"}
```
