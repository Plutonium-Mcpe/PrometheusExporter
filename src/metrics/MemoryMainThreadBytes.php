<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\utils\Process;
use Prometheus\RegistryInterface;

class MemoryMainThreadBytes extends Metric {
	public function getName() : string {
		return "memory_main_thread_bytes";
	}

	public function collect(RegistryInterface $registry) : void {
		$registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Memory usage of main thread")
			->set(Process::getMemoryUsage());
	}
}
