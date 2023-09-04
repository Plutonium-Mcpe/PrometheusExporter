<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class PlayerOnlineCount extends Metric {
	public function getName() : string {
		return "player_online_count";
	}

	public function collect(RegistryInterface $registry) : void {
		$registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Count of online players")
			->set(count(Server::getInstance()->getOnlinePlayers()));
	}
}
