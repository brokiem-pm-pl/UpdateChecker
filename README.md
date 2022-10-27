## How to use

```php
// use promise for poggit json data
$promise = new Promise();
$options = [\brokiem\updatechecker\Option::LOG_NEW_UPDATE => true]

\brokiem\updatechecker\UpdateChecker::checkUpdate("PluginName", "PluginVersion", $promise, $options);

$promise->then(function($poggit_data) {
    // result: poggit json data
    var_dump($poggit_data);
})->catch(function($error) {
    // this virion will automatically log the error, so you don't need to log it manually
    switch ($error) {
        case \brokiem\updatechecker\Status::CONNECTION_FAILED:
            // do stuff if connection failed
            break;
        case \brokiem\updatechecker\Status::NO_UPDATES_FOUND:
            // do stuff if no updates found
            break;
        case \brokiem\updatechecker\Status::PLUGIN_NOT_FOUND:
            // do stuff if plugin not found on poggit
            break;
    }
});
```