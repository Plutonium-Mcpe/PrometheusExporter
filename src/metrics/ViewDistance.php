<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class ViewDistance extends Metric {
	public function getName() : string {
		return "view_distance";
	}

	public function isSnapshot() : bool {
		return true;
	}

	public function collect(RegistryInterface $registry) : void {
		$gauge = $registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "View distance repartition", ["size"]);
		$viewDistances = [];
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			$view = $player->getViewDistance();
			$viewDistances[$view] = ($viewDistances[$view] ?? 0) + 1;
		}
		foreach ($viewDistances as $size => $count) {
			$gauge->set($count, [(string) $size]);
		}
	}
}
