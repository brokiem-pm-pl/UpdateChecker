<?php

declare(strict_types=1);

namespace brokiem\uc\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;

class CheckUpdate extends AsyncTask {

    private const POGGIT_URL = "https://poggit.pmmp.io/releases.json?name=";
    /** @var string */
    private $version;
    /** @var string */
    private $name;

    public function __construct(string $ver, string $name) {
        $this->version = $ver;
        $this->name = $name;
    }

    public function onRun(): void {
        $poggitData = Internet::getURL(self::POGGIT_URL . $this->name);

        if ($poggitData) {
            $poggit = json_decode($poggitData, true);

            if (is_array($poggit)) {
                $version = "";
                $date = "";
                $updateUrl = "";

                foreach ($poggit as $pog) {
                    if (version_compare($this->version, $pog["version"], ">=")) {
                        continue;
                    }

                    $version = $pog["version"];
                    $date = $pog["last_state_change_date"];
                    $updateUrl = $pog["html_url"];
                }

                $this->setResult([$version, $date, $updateUrl]);
            }
        }
    }

    public function onCompletion(Server $server): void {
        $plugin = $server->getPluginManager()->getPlugin($this->name);

        if ($plugin === null) {
            return;
        }

        if ($this->getResult() === null) {
            $server->getLogger()->debug("[$this->name] Async update check failed!");
            return;
        }

        [$latestVersion, $updateDateUnix, $updateUrl] = $this->getResult();

        if ($latestVersion != "" || $updateDateUnix != null || $updateUrl !== "") {
            $updateDate = date("j F Y", (int)$updateDateUnix);

            if ($this->version !== $latestVersion) {
                $server->getLogger()->notice("[$this->name] $this->name v$latestVersion has been released on $updateDate. Download the new update at $updateUrl");
            }
        }
    }
}