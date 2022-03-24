<?php

declare(strict_types=1);

namespace brokiem\updatechecker;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;

class CheckUpdateTask extends AsyncTask {

    private const POGGIT_URL = "https://poggit.pmmp.io/releases.json?name=";

    private array $options;

    public function __construct(private string $plugin_name, Promise $promise, array $options) {
        $plugin = Server::getInstance()->getPluginManager()->getPlugin($plugin_name);

        if ($plugin === null) {
            throw new \RuntimeException("Plugin $plugin_name not found");
        }

        $this->options = $options;

        $this->storeLocal("plugin", $plugin);
        $this->storeLocal("promise", $promise);
    }

    public function onRun(): void {
        $poggitData = Internet::getURL(self::POGGIT_URL . $this->plugin_name)?->getBody();

        if (is_string($poggitData)) {
            $poggit = json_decode($poggitData, true);

            if (is_array($poggit) and !empty($poggit)) {
                $this->setResult($poggit);
            }
        }
    }

    public function onCompletion(): void {
        /** @var Promise $promise */
        $promise = $this->fetchLocal("promise");
        /** @var Plugin $plugin */
        $plugin = $this->fetchLocal("plugin");
        /** @var ?array $results */
        $results = $this->getResult();

        if ($results === null) {
            $promise->reject(Status::CONNECTION_FAILED);
            return;
        }

        foreach ($results as $result) {
            if (version_compare($plugin->getDescription()->getVersion(), $result["version"], ">=")) {
                continue;
            }

            if ($plugin->getDescription()->getVersion() !== $result["version"]) {
                if ($this->options[Option::LOG_NEW_UPDATE] ?? true) {
                    $plugin->getLogger()->notice("$this->plugin_name v" . $result["version"] . " has been released on " . date("j F Y", $result["last_state_change_date"]) . ". Download the new update at " . $result["html_url"]);
                }

                $promise->resolve($result);
                return;
            }
        }

        $promise->reject(Status::NO_UPDATES_FOUND);
    }
}