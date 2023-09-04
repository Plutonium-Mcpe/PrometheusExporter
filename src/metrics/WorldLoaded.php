<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class WorldLoaded extends Metric {
	public function getName() : string {
		return "world_loaded";
	}

	public function collect(RegistryInterface $registry) : void {
		$gauge = $registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Currently loaded world", ["world", "world_folder"]);
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			$gauge->set(1, [ $world->getDisplayName(), $world->getFolderName() ]);
		}
	}
}
