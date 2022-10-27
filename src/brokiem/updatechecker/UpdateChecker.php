<?php

declare(strict_types=1);

namespace brokiem\updatechecker;

use pocketmine\Server;

final class UpdateChecker {

    public static function checkUpdate(string $plugin_name, string $plugin_version, Promise $promise, array $options = []): void {
        Server::getInstance()->getAsyncPool()->submitTask(new CheckUpdateTask($plugin_name, $plugin_version, $promise, $options));
    }
}