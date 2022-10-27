<?php

declare(strict_types=1);

namespace brokiem\updatechecker;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;

class CheckUpdateTask extends AsyncTask {

    private const POGGIT_URL = "https://poggit.pmmp.io/releases.json?name=";

    private array $options;
    public string $plugin_name_console;

    public function __construct(
        private string $plugin_name,
        private string $plugin_version,
        Promise        $promise,
        array          $options
    ) {
        $this->options = $options;
        $this->plugin_name_console = "[" . $this->plugin_name . "]";

        $this->storeLocal("promise", $promise);
    }

    public function onRun(): void {
        $poggitData = Internet::getURL(self::POGGIT_URL . $this->plugin_name)?->getBody();

        if (is_string($poggitData)) {
            $poggit = json_decode($poggitData, true);

            if (is_array($poggit)) {
                $this->setResult($poggit);
            }
        }
    }

    public function onCompletion(): void {
        /** @var Promise $promise */
        $promise = $this->fetchLocal("promise");
        /** @var ?array $results */
        $results = $this->getResult();
        $server = Server::getInstance();

        if ($results === null) {
            $server->getLogger()->error($this->plugin_name_console . " Update checker failed: Connection timeout or no data returned");
            $promise->reject(Status::CONNECTION_FAILED);
            return;
        }

        if (empty($results)) {
            $server->getLogger()->error($this->plugin_name_console . " Update checker failed: Plugin not found on poggit");
            $promise->reject(Status::PLUGIN_NOT_FOUND);
            return;
        }

        foreach ($results as $result) {
            if (version_compare($this->plugin_version, $result["version"], ">=")) {
                continue;
            }

            if ($this->plugin_version !== $result["version"]) {
                if ($this->options[Option::LOG_NEW_UPDATE] ?? true) {
                    $server->getLogger()->notice($this->plugin_name_console . " $this->plugin_name v" . $result["version"] . " has been released on " . date("j F Y", $result["last_state_change_date"]) . ". Download the new update at " . $result["html_url"]);
                }

                $promise->resolve($result);
                return;
            }
        }

        $server->getLogger()->debug($this->plugin_name_console . " [UpdateChecker] You are on latest version (v" . $this->plugin_version . ")");

        $promise->reject(Status::NO_UPDATES_FOUND);
    }
}