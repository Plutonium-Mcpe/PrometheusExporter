<?php

namespace Plutonium\PrometheusExporter\timings;

use Plutonium\PrometheusExporter\metrics\Metric;
use pocketmine\timings\TimingsHandler;

class Timing {
	private const PREFIX = "PrometheusExporter - ";
	private const GROUP = "PrometheusExporter";
	public static TimingsHandler $metricsHandlerTimer;
	public static TimingsHandler $collectResultTimer;
	public static TimingsHandler $renderResultTimer;

	/** @var TimingsHandler[] $exporterTimingsMap */
	private static array $metricsCollecterTimingsMap = [];

	/** @var TimingsHandler[] $metricsPostCollectCollecterTimingsMap */
	private static array $metricsPostCollectCollecterTimingsMap = [];

	public static function init() : void {
		TimingsHandler::setEnabled();

		self::$metricsHandlerTimer = new TimingsHandler(self::PREFIX . "Metrics handler", group: self::GROUP);
		self::$collectResultTimer = new TimingsHandler(self::PREFIX . "Collect result", self::$metricsHandlerTimer, self::GROUP);
		self::$renderResultTimer = new TimingsHandler(self::PREFIX . "Render result", self::$metricsHandlerTimer, self::GROUP);
	}

	public static function getMetricsCollecterTimings(Metric $metric) : TimingsHandler {
		$name = $metric->getName();
		if (!isset(self::$metricsCollecterTimingsMap[$name])) {
			self::$metricsCollecterTimingsMap[$name] = new TimingsHandler(self::PREFIX . "Collect result - " . $name, self::$collectResultTimer, self::GROUP);
		}

		return self::$metricsCollecterTimingsMap[$name];
	}

	public static function getMetricsPostCollectCollecterTimings(Metric $metric) : TimingsHandler {
		$name = $metric->getName();
		if (!isset(self::$metricsPostCollectCollecterTimingsMap[$name])) {
			self::$metricsPostCollectCollecterTimingsMap[$name] = new TimingsHandler(self::PREFIX . "Post collect - " . $name, self::$collectResultTimer, self::GROUP);
		}

		return self::$metricsPostCollectCollecterTimingsMap[$name];
	}
}
