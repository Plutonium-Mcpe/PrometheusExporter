<?php

namespace Plutonium\PrometheusExporter\metrics;

use pocketmine\entity\Living;
use pocketmine\entity\object\ItemEntity;
use pocketmine\player\Player;
use pocketmine\Server;
use Prometheus\RegistryInterface;

class WorldEntityCount extends Metric {
	public function getName() : string {
		return "world_entity_count";
	}

	public function collect(RegistryInterface $registry) : void {
		$gauge = $registry
			->getOrRegisterGauge(Metric::PREFIX, $this->getName(), "Count of entities by type", ["world", "world_folder", "type"]);
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
			$labels = [$world->getDisplayName(), $world->getFolderName()];
			$counts = ["player" => 0, "living" => 0, "item" => 0, "other" => 0];
			foreach ($world->getEntities() as $entity) {
				if ($entity instanceof Player) {
					$counts["player"]++;
				} elseif ($entity instanceof Living) {
					$counts["living"]++;
				} elseif ($entity instanceof ItemEntity) {
					$counts["item"]++;
				} else {
					$counts["other"]++;
				}
			}
			foreach ($counts as $type => $count) {
				$gauge->set($count, [...$labels, $type]);
			}
		}
	}
}
