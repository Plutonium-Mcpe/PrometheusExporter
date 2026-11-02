<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\Server;
use Prometheus\RegistryInterface;

class PlayerPing extends Metric {
	public function getName() : string {
		return "player_ping_ms";
	}

	public function collect(RegistryInterface $registry) : void {
		$gauge = $registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Player ping in milliseconds", ["aggregation"]);

		$pings = [];
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			$ping = $player->getNetworkSession()->getPing();
			if ($ping !== null) {
				$pings[] = $ping;
			}
		}

		$gauge->set($pings === [] ? 0 : (int) (array_sum($pings) / count($pings)), ["average"]);
		$gauge->set($pings === [] ? 0 : max($pings), ["max"]);
	}
}
