## How to use

```php
// use promise for poggit json data
$promise = new Promise()
$options = [\brokiem\updatechecker\Option::LOG_NEW_UPDATE => true, \brokiem\updatechecker\Option::LOG => false]

\brokiem\updatechecker\UpdateChecker::checkUpdate("YourPluginName", $promise, $options);

$promise->then(function($poggit_data) {
    var_dump($poggit_data);
})->catch(function($error) {
    switch ($error) {
        case \brokiem\updatechecker\Status::CONNECTION_FAILED:
            $this->getLogger()->error("Update checker error: Connection timeout");
            break;
        case \brokiem\updatechecker\Status::NO_UPDATES_FOUND:
            $this->getLogger()->debug("This plugin is on latest version");
            break;
    }
});
```