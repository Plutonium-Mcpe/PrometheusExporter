<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class TickUsage extends Metric {
	public function getName() : string {
		return "tick_usage";
	}

	public function collect(RegistryInterface $registry) : void {
		$registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "How much tick using")
			->set(Server::getInstance()->getTickUsageAverage());
	}
}
