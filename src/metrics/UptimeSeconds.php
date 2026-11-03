<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class UptimeSeconds extends Metric {
	public function getName() : string {
		return "uptime_seconds";
	}

	public function collect(RegistryInterface $registry) : void {
		$registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Server uptime in seconds")
			->set((int) (microtime(true) - Server::getInstance()->getStartTime()));
	}
}
