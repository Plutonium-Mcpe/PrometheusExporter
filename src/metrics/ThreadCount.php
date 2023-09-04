<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\utils\Process;
use Prometheus\RegistryInterface;

class ThreadCount extends Metric {
	public function getName() : string {
		return "thread_count";
	}

	public function collect(RegistryInterface $registry) : void {
		$registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "PocketMine Thread count")
			->set(Process::getThreadCount());
	}
}
