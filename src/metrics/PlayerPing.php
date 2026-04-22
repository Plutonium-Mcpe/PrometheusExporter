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

		$players = Server::getInstance()->getOnlinePlayers();
		if (count($players) === 0) {
			$gauge->set(0, ["average"]);
			$gauge->set(0, ["max"]);
			return;
		}

		$total = 0;
		$max = 0;
		foreach ($players as $player) {
			$ping = $player->getNetworkSession()->getPing();
			$total += $ping;
			if ($ping > $max) {
				$max = $ping;
			}
		}

		$gauge->set((int) ($total / count($players)), ["average"]);
		$gauge->set($max, ["max"]);
	}
}
