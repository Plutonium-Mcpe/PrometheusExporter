<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\utils\Process;
use Prometheus\RegistryInterface;

class MemoryHeapBytes extends Metric {
	public function getName() : string {
		return "memory_heap_bytes";
	}

	public function collect(RegistryInterface $registry) : void {
		$registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Memory usage of heap")
			->set(Process::getRealMemoryUsage()[0]);
	}
}
