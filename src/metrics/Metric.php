<?php

namespace Plutonium\PrometheusExporter\metrics;

use Prometheus\RegistryInterface;

abstract class Metric {
	public const PREFIX = "pocketmine";

	public abstract function getName() : string;

	public abstract function collect(RegistryInterface $registry) : void;

	public function postCollect() : void {
	}
}
