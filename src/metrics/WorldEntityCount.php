<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class WorldEntityCount extends Metric {
	public function getName() : string {
		return "world_entity_count";
	}

	public function collect(RegistryInterface $registry) : void {
		$gauge = $registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Count of entity", ["world", "world_folder"]);
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			$gauge->set(count($world->getEntities()), [ $world->getDisplayName(), $world->getFolderName() ]);
		}
	}
}
