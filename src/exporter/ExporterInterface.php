<?php

namespace Plutonium\PrometheusExporter\Exporter;

use const Plutonium\COMPOSER_AUTOLOADER_PATH;
use Plutonium\PrometheusExporter\PrometheusExporter;

class ExporterInterface {
	private Exporter $exporter;

	public function __construct(
		private PrometheusExporter $plugin
	) {
		$notifier = $this->plugin->getServer()->getTickSleeper()->addNotifier(function () {
			$this->handleRequest();
		});

		$this->exporter = new Exporter(
			COMPOSER_AUTOLOADER_PATH,
			$this->plugin->getConfig()->get("host", $this->plugin->getServer()->getIp()),
			$this->plugin->getConfig()->get("port", 9655),
			$this->plugin->getServer()->getLogger(),
			$notifier
		);
	}

	public function start() : void {
		$this->exporter->startAndWait();
	}

	private function handleRequest() : void {
		$this->exporter->sendResponse($this->plugin->getMetricsManager()->render());
	}
}
