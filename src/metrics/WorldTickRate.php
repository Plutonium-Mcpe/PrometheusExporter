<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class WorldTickRate extends Metric {
	public function getName() : string {
		return "world_tick_rate";
	}

	public function collect(RegistryInterface $registry) : void {
		$gauge = $registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Tick rate time(millisecond) of world", ["world", "world_folder"]);
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			$gauge->set($world->getTickRateTime(), [ $world->getDisplayName(), $world->getFolderName() ]);
		}
	}
}
