<?php

declare(strict_types=1);

namespace brokiem\updatechecker;

use pocketmine\Server;

class UpdateChecker {

    public static function checkUpdate(string $plugin_name, Promise $promise, array $options = []): void {
        Server::getInstance()->getAsyncPool()->submitTask(new CheckUpdateTask($plugin_name, $promise, $options));
    }
}