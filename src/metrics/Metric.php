<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\plugin\Plugin;
use Prometheus\RegistryInterface;

abstract class Metric {
	public const PREFIX = "pocketmine";

	public abstract function getName() : string;

	public abstract function collect(RegistryInterface $registry) : void;

	public function isSnapshot() : bool {
		return false;
	}

	public function postCollect() : void {
	}

	public function onRegister(Plugin $plugin) : void {
	}
}
