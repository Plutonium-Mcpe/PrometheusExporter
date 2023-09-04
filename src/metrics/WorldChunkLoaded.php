<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class WorldChunkLoaded extends Metric {
	public function getName() : string {
		return "world_chunk_loaded";
	}

	public function collect(RegistryInterface $registry) : void {
		$gauge = $registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Loaded chunk count", ["world", "world_folder"]);
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			$gauge->set(count($world->getLoadedChunks()), [ $world->getDisplayName(), $world->getFolderName() ]);
		}
	}
}
