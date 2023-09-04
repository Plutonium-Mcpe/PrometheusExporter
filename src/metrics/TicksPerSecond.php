<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class TicksPerSecond extends Metric {
	public function getName() : string {
		return "ticks_per_second";
	}

	public function collect(RegistryInterface $registry) : void {
		$registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Indicates how much server lag spikes.")
			->set(Server::getInstance()->getTicksPerSecondAverage());
	}
}
