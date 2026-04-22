<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\plugin\Plugin;
use Prometheus\RegistryInterface;

abstract class Metric {
	public const PREFIX = "pocketmine";

	public abstract function getName() : string;

	public abstract function collect(RegistryInterface $registry) : void;

	public function postCollect() : void {
	}

	public function onRegister(Plugin $plugin) : void {
	}
}
