<?php

declare(strict_types=1);

namespace brokiem\updatechecker;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;

class CheckUpdateTask extends AsyncTask {

    private const POGGIT_URL = "https://poggit.pmmp.io/releases.json?name=";

    private string $plugin_name;
    private string $plugin_version;
    private array $options;

    public function __construct(private Plugin $plugin, Promise $promise, array $options) {
        $this->plugin_name = $plugin->getDescription()->getName();
        $this->plugin_version = $plugin->getDescription()->getVersion();
        $this->options = $options;

        $this->storeLocal("plugin", $plugin);
        $this->storeLocal("promise", $promise);
    }

    public function onRun(): void {
        $poggitData = Internet::getURL(self::POGGIT_URL . $this->plugin_name)?->getBody();

        if (is_string($poggitData)) {
            $poggit = json_decode($poggitData, true);

            if (is_array($poggit)) {
                foreach ($poggit as $pog) {
                    if (version_compare($this->plugin_version, $pog["version"], ">=")) {
                        continue;
                    }

                    $this->setResult($pog);
                }
            }
        }
    }

    public function onCompletion(): void {
        /** @var Promise $promise */
        $promise = $this->fetchLocal("promise");
        /** @var ?array $result */
        $result = $this->getResult();

        if ($result === null) {
            $promise->reject(Status::CONNECTION_FAILED);
            return;
        }

        if ($this->plugin_version !== $result["version"]) {
            if ($this->options[Option::LOG_NEW_UPDATE] ?? true) {
                Server::getInstance()->getLogger()->notice("$this->plugin_name v" . $result["version"] . " has been released on " . date("j F Y", $result["last_state_change_date"]) . ". Download the new update at " . $result["html_url"]);
            }

            $promise->resolve($result);
            return;
        }

        $promise->reject(Status::NO_UPDATES_FOUND);
    }
}