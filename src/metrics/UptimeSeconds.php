<?php

namespace Plutonium\PrometheusExporter\metrics;

use Prometheus\RegistryInterface;

class UptimeSeconds extends Metric {
	private float $startTime;

	public function __construct() {
		$this->startTime = microtime(true);
	}

	public function getName() : string {
		return "uptime_seconds";
	}

	public function collect(RegistryInterface $registry) : void {
		$registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Server uptime in seconds since plugin load")
			->set((int) (microtime(true) - $this->startTime));
	}
}
