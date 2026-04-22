<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class PlayerMaxCount extends Metric {
	public function getName() : string {
		return "player_max_count";
	}

	public function collect(RegistryInterface $registry) : void {
		$registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Maximum player slots configured on the server")
			->set(Server::getInstance()->getMaxPlayers());
	}
}
