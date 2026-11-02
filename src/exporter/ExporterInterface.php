<?php

namespace Plutonium\PrometheusExporter\exporter;

use const Plutonium\COMPOSER_AUTOLOADER_PATH;
use Plutonium\PrometheusExporter\PrometheusExporter;
use pocketmine\scheduler\ClosureTask;

class ExporterInterface {
	private const DEFAULT_REFRESH_INTERVAL = 5;
	private Exporter $exporter;

	public function __construct(
		private PrometheusExporter $plugin
	) {
		$this->exporter = new Exporter(
			COMPOSER_AUTOLOADER_PATH,
			$this->plugin->getConfig()->get("host", $this->plugin->getServer()->getIp()),
			$this->plugin->getConfig()->get("port", 9655),
			$this->plugin->getServer()->getLogger()
		);
	}

	public function start() : void {
		$this->publish();
		$this->exporter->startAndWait();

		$this->plugin->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () : void {
			$this->publish();
		}), $this->getRefreshInterval() * 20);
	}

	public function stop() : void {
		if ($this->exporter->isStarted()) {
			$this->exporter->quit();
		}
	}

	private function publish() : void {
		$this->exporter->setSnapshot($this->plugin->getMetricsManager()->render());
	}

	private function getRefreshInterval() : int {
		$interval = $this->plugin->getConfig()->get("refresh-interval", self::DEFAULT_REFRESH_INTERVAL);
		if (!is_int($interval) || $interval < 1) {
			$this->plugin->getLogger()->warning("Invalid refresh-interval, using " . self::DEFAULT_REFRESH_INTERVAL . " seconds instead");

			return self::DEFAULT_REFRESH_INTERVAL;
		}

		return $interval;
	}
}
