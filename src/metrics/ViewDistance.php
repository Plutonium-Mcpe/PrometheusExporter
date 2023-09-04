<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class ViewDistance extends Metric {
	private static array $viewDistances = [];

	public function getName() : string {
		return "view_distance";
	}

	public function collect(RegistryInterface $registry) : void {
		$gauge = $registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "View distance repartition", ["size"]);
		foreach (self::$viewDistances as $size => $count) {
			self::$viewDistances[$size] = 0;
		}
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			$view = $player->getViewDistance();
			if (!isset(self::$viewDistances[$view])) {
				self::$viewDistances[$view] = 0;
			}
			self::$viewDistances[$view]++;
		}
		foreach (self::$viewDistances as $size => $count) {
			$gauge->set($count, [$size]);
		}
	}
}
