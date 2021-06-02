<?php

declare(strict_types=1);

namespace brokiem\uc;

use brokiem\uc\task\CheckUpdate;
use pocketmine\Server;

class UpdateChecker {

    public static function checkUpdate(string $plName, string $plVer): void {
        $plugin = Server::getInstance()->getPluginManager()->getPlugin($plName);

        if ($plugin === null) {
            return;
        }

        Server::getInstance()->getAsyncPool()->submitTask(new CheckUpdate($plName, $plVer));
    }
}