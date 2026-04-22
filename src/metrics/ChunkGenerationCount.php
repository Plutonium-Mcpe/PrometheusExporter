<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\event\Listener;
use pocketmine\event\world\ChunkPopulateEvent;
use pocketmine\plugin\Plugin;
use Prometheus\RegistryInterface;

class ChunkGenerationCount extends Metric implements Listener {
	private int $count = 0;
	private int $reported = 0;

	public function getName() : string {
		return "chunk_generation_count";
	}

	public function onRegister(Plugin $plugin) : void {
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	public function onChunkPopulate(ChunkPopulateEvent $event) : void {
		$this->count++;
	}

	public function collect(RegistryInterface $registry) : void {
		$counter = $registry->getOrRegisterCounter(Metric::PREFIX, $this->getName(), "Total chunks generated since server start");
		$delta = $this->count - $this->reported;
		if ($delta > 0) {
			$counter->incBy($delta);
			$this->reported = $this->count;
		}
	}
}
