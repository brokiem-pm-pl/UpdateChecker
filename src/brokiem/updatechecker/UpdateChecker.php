<?php

declare(strict_types=1);

namespace brokiem\updatechecker;

use pocketmine\Server;

class UpdateChecker {

    public static function checkUpdate(string $plugin_name, Promise $promise, array $options = []): void {
        $plugin = Server::getInstance()->getPluginManager()->getPlugin($plugin_name);

        if ($plugin === null) {
            throw new \RuntimeException("Plugin $plugin_name not found");
        }

        Server::getInstance()->getAsyncPool()->submitTask(new CheckUpdateTask($plugin, $promise, $options));
    }
}