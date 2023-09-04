<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class WorldPlayerCount extends Metric {
	public function getName() : string {
		return "world_player_count";
	}

	public function collect(RegistryInterface $registry) : void {
		$gauge = $registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Count of players per world", ["world", "world_folder"]);
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			$gauge->set(count($world->getPlayers()), [ $world->getDisplayName(), $world->getFolderName() ]);
		}
	}
}
